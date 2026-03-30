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
        // Enhanced validation with custom messages
        $validator = Validator::make($request->all(), [
            'food_name_and_description' => [
                'required',
                'string',
                'min:5',
                'max:500',
                'regex:/[a-zA-Z]/', // Must contain at least one letter
            ],
            'alternate_common_names' => 'nullable|string|max:300',
            'energy_kcal' => [
                'required',
                'numeric',
                'min:0',
                'max:9999.99',
                'regex:/^\d+(\.\d{1,2})?$/', // Max 2 decimal places
            ],
            'nutrition_tags' => 'nullable|string|max:200',
        ], [
            'food_name_and_description.required' => 'Food name and description is required.',
            'food_name_and_description.min' => 'Please provide a more detailed description (minimum 5 characters).',
            'food_name_and_description.max' => 'Description is too long (maximum 500 characters).',
            'food_name_and_description.regex' => 'Food name must contain at least one letter.',
            'energy_kcal.required' => 'Energy value is required.',
            'energy_kcal.numeric' => 'Energy must be a valid number.',
            'energy_kcal.min' => 'Energy value cannot be negative.',
            'energy_kcal.max' => 'Energy value exceeds maximum (9999.99 kcal).',
            'energy_kcal.regex' => 'Energy value can have maximum 2 decimal places.',
            'alternate_common_names.max' => 'Alternate names are too long (maximum 300 characters).',
            'nutrition_tags.max' => 'Nutrition tags are too long (maximum 200 characters).',
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

        // Additional validation: Check if tags count exceeds limit
        if ($request->nutrition_tags) {
            $tagsArray = array_filter(array_map('trim', explode(',', $request->nutrition_tags)));
            if (count($tagsArray) > 20) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum 20 nutrition tags allowed.'
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'Maximum 20 nutrition tags allowed.')
                    ->withInput();
            }
        }

        try {
            // Sanitize input data
            $sanitizedData = [
                'food_name_and_description' => trim($request->food_name_and_description),
                'energy_kcal' => round((float)$request->energy_kcal, 2),
            ];

            // Sanitize optional fields
            if ($request->alternate_common_names) {
                $alternateArray = array_filter(array_map('trim', explode(',', $request->alternate_common_names)));
                $sanitizedData['alternate_common_names'] = implode(', ', $alternateArray);
            } else {
                $sanitizedData['alternate_common_names'] = null;
            }

            if ($request->nutrition_tags) {
                $tagsArray = array_filter(array_map('trim', explode(',', $request->nutrition_tags)));
                $sanitizedData['nutrition_tags'] = implode(', ', $tagsArray);
            } else {
                $sanitizedData['nutrition_tags'] = null;
            }

            // Check for duplicate in existing foods using sanitized data
            $existsInFoods = Food::where('food_name_and_description', 'LIKE', '%' . $sanitizedData['food_name_and_description'] . '%')
                ->exists();

            if ($existsInFoods) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A similar food item already exists in the database. Please check the existing foods before requesting.'
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'A similar food item already exists in the database.')
                    ->withInput();
            }

            // Check for duplicate in pending requests using sanitized data
            $existsInRequests = FoodRequest::where('status', 'pending')
                ->where('food_name_and_description', 'LIKE', '%' . $sanitizedData['food_name_and_description'] . '%')
                ->exists();

            if ($existsInRequests) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A similar food request is already pending review. Please wait for it to be processed.'
                    ], 422);
                }
                return redirect()->back()
                    ->with('error', 'A similar food request is already pending review.')
                    ->withInput();
            }

            DB::beginTransaction();

            // Create food request with sanitized data
            $foodRequest = FoodRequest::create([
                'requested_by' => Auth::id(),
                'food_name_and_description' => $sanitizedData['food_name_and_description'],
                'alternate_common_names' => $sanitizedData['alternate_common_names'],
                'energy_kcal' => $sanitizedData['energy_kcal'],
                'nutrition_tags' => $sanitizedData['nutrition_tags'],
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
        try {
            $foodRequest = FoodRequest::with(['requester', 'reviewer'])->findOrFail($id);
            
            // Check authorization
            $user = Auth::user();
            if ($user->role->role_name === 'Nutritionist' && $foodRequest->requested_by !== $user->user_id) {
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access.'
                    ], 403);
                }
                abort(403, 'Unauthorized access.');
            }

            // Return JSON for AJAX requests
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'id' => $foodRequest->id,
                    'food_name_and_description' => $foodRequest->food_name_and_description,
                    'alternate_common_names' => $foodRequest->alternate_common_names,
                    'energy_kcal' => $foodRequest->energy_kcal,
                    'nutrition_tags' => $foodRequest->nutrition_tags,
                    'status' => $foodRequest->status,
                    'admin_notes' => $foodRequest->admin_notes,
                    'review_notes' => $foodRequest->admin_notes, // Alias for frontend
                    'created_at' => $foodRequest->created_at,
                    'reviewed_at' => $foodRequest->reviewed_at,
                    'requester' => $foodRequest->requester ? [
                        'id' => $foodRequest->requester->user_id,
                        'name' => $foodRequest->requester->first_name . ' ' . $foodRequest->requester->last_name,
                    ] : null,
                    'reviewer' => $foodRequest->reviewer ? [
                        'id' => $foodRequest->reviewer->user_id,
                        'name' => $foodRequest->reviewer->first_name . ' ' . $foodRequest->reviewer->last_name,
                    ] : null,
                ]);
            }

            // Return view for direct access
            if ($user->role->role_name === 'Admin') {
                return view('admin.food-requests', compact('foodRequest'));
            } else {
                // Redirect nutritionists to the unified foods page with requests view
                return redirect()->route('nutritionist.foods', ['view' => 'requests']);
            }
        } catch (\Exception $e) {
            Log::error('Error fetching food request: ' . $e->getMessage());
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching request details: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error fetching request details.');
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
            
            // Check if already processed
            if ($foodRequest->status !== 'pending') {
                DB::rollBack();
                return redirect()->route('admin.food-requests.index')
                    ->with('error', 'This request has already been processed.');
            }

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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food request approved and added to database!',
                    'data' => ['food' => $food, 'request' => $foodRequest]
                ]);
            }

            return redirect()->route('admin.food-requests.index')
                ->with('success', 'Food request approved and added to database!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving food request: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while approving the food request.'
                ], 500);
            }
            
            return redirect()->route('admin.food-requests.index')
                ->with('error', 'An error occurred while approving the food request.');
        }
    }

    /**
     * Batch approve food requests (Admin)
     */
    public function batchApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:food_requests,id',
            'admin_notes' => 'nullable|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $ids = $request->input('ids');
            $approvedCount = 0;
            $skipped = 0;

            foreach ($ids as $id) {
                $foodRequest = FoodRequest::find($id);
                
                if ($foodRequest && $foodRequest->status === 'pending') {
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
                        'admin_notes' => $request->admin_notes ?? 'Batch approved',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                    ]);

                    // Log the action
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'approve',
                        'table_name' => 'food_requests',
                        'record_id' => $foodRequest->id,
                        'description' => 'Batch approved food request: ' . $food->food_name_and_description,
                    ]);
                    
                    $approvedCount++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $message = "Successfully approved {$approvedCount} request(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} request(s) were skipped (already processed).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'approved' => $approvedCount,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch approving food requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving requests.'
            ], 500);
        }
    }

    /**
     * Batch reject food requests (Admin)
     */
    public function batchReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:food_requests,id',
            'admin_notes' => 'required|string|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $ids = $request->input('ids');
            $rejectedCount = 0;
            $skipped = 0;

            foreach ($ids as $id) {
                $foodRequest = FoodRequest::find($id);
                
                if ($foodRequest && $foodRequest->status === 'pending') {
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
                        'description' => 'Batch rejected food request: ' . $foodRequest->food_name_and_description,
                    ]);
                    
                    $rejectedCount++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            $message = "Successfully rejected {$rejectedCount} request(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} request(s) were skipped (already processed).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'rejected' => $rejectedCount,
                'skipped' => $skipped
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch rejecting food requests: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting requests.'
            ], 500);
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
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $foodRequest = FoodRequest::findOrFail($id);
            
            // Check if already processed
            if ($foodRequest->status !== 'pending') {
                DB::rollBack();
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This request has already been processed.'
                    ], 422);
                }
                return redirect()->route('admin.food-requests.index')
                    ->with('error', 'This request has already been processed.');
            }

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

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food request rejected.',
                    'data' => $foodRequest
                ]);
            }

            return redirect()->route('admin.food-requests.index')
                ->with('success', 'Food request rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting food request: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while rejecting the food request.'
                ], 500);
            }
            
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
