<?php

namespace App\Http\Controllers;

use App\Models\FoodRequest;
use App\Models\Food;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FoodRequestController extends Controller
{
    /**
     * Display food requests (Admin - all, Nutritionist - own)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');

        $query = FoodRequest::with(['requester', 'reviewer']);

        // Nutritionists only see their own requests
        if ($user->role->role_name === 'Nutritionist') {
            $query->where('requested_by', $user->user_id);
        }

        // Filter by status if provided
        if ($status) {
            $query->where('status', $status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate stats based on user role
        if ($user->role->role_name === 'Nutritionist') {
            $stats = [
                'pending' => FoodRequest::pending()->where('requested_by', $user->user_id)->count(),
                'approved' => FoodRequest::approved()->where('requested_by', $user->user_id)->count(),
                'rejected' => FoodRequest::rejected()->where('requested_by', $user->user_id)->count(),
            ];
        } else {
            $stats = [
                'pending' => FoodRequest::pending()->count(),
                'approved' => FoodRequest::approved()->count(),
                'rejected' => FoodRequest::rejected()->count(),
            ];
        }

        if ($user->role->role_name === 'Admin') {
            return view('admin.food-requests', compact('requests', 'stats', 'status'));
        } else {
            // Redirect nutritionists to the unified foods page with requests view
            $statusParam = $status ? '&status=' . $status : '';
            return redirect()->route('nutritionist.foods') . '?view=requests' . $statusParam;
        }
    }

    /**
     * Show the form for creating a new food request (Nutritionist)
     */
    public function create()
    {
        // Redirect nutritionists to the unified foods page
        // They can use the SweetAlert2 modal to create requests
        return redirect()->route('nutritionist.foods');
    }

    /**
     * Store a newly created food request (Nutritionist)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_name_and_description' => 'required|string|max:5000',
            'alternate_common_names' => 'nullable|string|max:5000',
            'energy_kcal' => 'nullable|numeric|min:0',
            'nutrition_tags' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $foodRequest = FoodRequest::create([
                'requested_by' => Auth::id(),
                'food_name_and_description' => $request->food_name_and_description,
                'alternate_common_names' => $request->alternate_common_names,
                'energy_kcal' => $request->energy_kcal,
                'nutrition_tags' => $request->nutrition_tags,
                'status' => 'pending',
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'food_requests',
                'record_id' => $foodRequest->id,
                'description' => 'Created food request: ' . $foodRequest->food_name_and_description,
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food request submitted successfully! Admin will review it soon.',
                    'data' => $foodRequest
                ]);
            }

            return redirect()->route('nutritionist.food-requests.index')
                ->with('success', 'Food request submitted successfully! Admin will review it soon.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating food request: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while submitting the food request.'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'An error occurred while submitting the food request.')
                ->withInput();
        }
    }

    /**
     * Display the specified food request
     */
    public function show($id)
    {
        $foodRequest = FoodRequest::with(['requester', 'reviewer'])->findOrFail($id);
        
        // Check authorization
        $user = Auth::user();
        if ($user->role->role_name === 'Nutritionist' && $foodRequest->requested_by !== $user->user_id) {
            abort(403, 'Unauthorized access.');
        }

        // Return JSON for AJAX requests (admin view details)
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($foodRequest);
        }

        // Return view for direct access
        if ($user->role->role_name === 'Admin') {
            return view('admin.food-requests', compact('foodRequest'));
        } else {
            // Redirect nutritionists to the unified foods page with requests view
            return redirect()->route('nutritionist.foods') . '?view=requests';
        }
    }

    /**
     * Approve a food request and add to foods database (Admin)
     */
    public function approve(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $foodRequest = FoodRequest::findOrFail($id);

            // Create the food entry
            $food = Food::create([
                'food_name_and_description' => $foodRequest->food_name_and_description,
                'alternate_common_names' => $foodRequest->alternate_common_names,
                'energy_kcal' => $foodRequest->energy_kcal,
                'nutrition_tags' => $foodRequest->nutrition_tags,
            ]);

            // Update request status
            $foodRequest->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'approve',
                'table_name' => 'food_requests',
                'record_id' => $foodRequest->id,
                'description' => 'Approved food request and created food: ' . $food->food_name_and_description,
            ]);

            DB::commit();

            return redirect()->route('admin.food-requests.index')
                ->with('success', 'Food request approved and added to database!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving food request: ' . $e->getMessage());
            return redirect()->route('admin.food-requests.index')
                ->with('error', 'An error occurred while approving the food request.');
        }
    }

    /**
     * Reject a food request (Admin)
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'admin_notes' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $foodRequest = FoodRequest::findOrFail($id);

            $foodRequest->update([
                'status' => 'rejected',
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'reject',
                'table_name' => 'food_requests',
                'record_id' => $foodRequest->id,
                'description' => 'Rejected food request: ' . $foodRequest->food_name_and_description,
            ]);

            DB::commit();

            return redirect()->route('admin.food-requests.index')
                ->with('success', 'Food request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting food request: ' . $e->getMessage());
            return redirect()->route('admin.food-requests.index')
                ->with('error', 'An error occurred while rejecting the food request.');
        }
    }

    /**
     * Delete a food request (Admin or own request if pending)
     */
    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $foodRequest = FoodRequest::findOrFail($id);
            
            // Check authorization
            $user = Auth::user();
            if ($user->role->role_name === 'Nutritionist') {
                if ($foodRequest->requested_by !== $user->user_id || $foodRequest->status !== 'pending') {
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'You can only delete your own pending requests.'
                        ], 403);
                    }
                    abort(403, 'You can only delete your own pending requests.');
                }
            }

            $description = $foodRequest->food_name_and_description;

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'food_requests',
                'record_id' => $foodRequest->id,
                'old_values' => json_encode($foodRequest->toArray()),
                'description' => 'Deleted food request: ' . $description,
            ]);

            $foodRequest->delete();

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food request cancelled successfully!'
                ]);
            }

            if ($user->role->role_name === 'Admin') {
                return redirect()->route('admin.food-requests.index')
                    ->with('success', 'Food request deleted successfully!');
            } else {
                return redirect()->route('nutritionist.food-requests.index')
                    ->with('success', 'Food request deleted successfully!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting food request: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the food request.'
                ], 500);
            }
            
            // Determine where to redirect based on role
            $redirectRoute = Auth::user()->role->role_name === 'Admin' 
                ? 'admin.food-requests.index' 
                : 'nutritionist.food-requests.index';
                
            return redirect()->route($redirectRoute)
                ->with('error', 'An error occurred while deleting the food request.');
        }
    }
}
