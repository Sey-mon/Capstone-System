<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\FoodRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    /**
     * Display a listing of foods (Admin)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tag = $request->input('tag');
        $perPage = $request->input('per_page', 15);

        $foods = Food::query()
            ->search($search)
            ->withTag($tag)
            ->orderBy('food_name_and_description', 'asc')
            ->paginate($perPage);

        // Get unique tags for filter
        $allTags = $this->getAllUniqueTags();

        return view('admin.foods', compact('foods', 'allTags', 'search', 'tag'));
    }

    /**
     * Show the form for creating a new food (Admin)
     */
    public function create()
    {
        return view('admin.foods.create');
    }

    /**
     * Store a newly created food in database (Admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_name_and_description' => 'required|string|max:5000',
            'alternate_common_names' => 'nullable|string|max:5000',
            'energy_kcal' => 'required|numeric|min:0',
            'nutrition_tags' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $food = Food::create([
                'food_name_and_description' => $request->food_name_and_description,
                'alternate_common_names' => $request->alternate_common_names,
                'energy_kcal' => $request->energy_kcal,
                'nutrition_tags' => $request->nutrition_tags,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'foods',
                'record_id' => $food->food_id,
                'description' => 'Created new food: ' . $food->food_name_and_description,
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food item created successfully!',
                    'data' => $food
                ]);
            }

            return redirect()->route('admin.foods.index')
                ->with('success', 'Food item created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating food: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while creating the food item.'
                ], 500);
            }
            
            return redirect()->route('admin.foods.index')
                ->with('error', 'An error occurred while creating the food item.')
                ->withInput();
        }
    }

    /**
     * Display the specified food (Admin)
     */
    public function show($id)
    {
        $food = Food::findOrFail($id);
        return response()->json($food);
    }

    /**
     * Show the form for editing the specified food (Admin)
     */
    public function edit($id)
    {
        $food = Food::findOrFail($id);
        return view('admin.foods.edit', compact('food'));
    }

    /**
     * Update the specified food in database (Admin)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'food_name_and_description' => 'required|string|max:5000',
            'alternate_common_names' => 'nullable|string|max:5000',
            'energy_kcal' => 'required|numeric|min:0',
            'nutrition_tags' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $food = Food::findOrFail($id);
            
            $oldData = $food->toArray();
            
            $food->update([
                'food_name_and_description' => $request->food_name_and_description,
                'alternate_common_names' => $request->alternate_common_names,
                'energy_kcal' => $request->energy_kcal,
                'nutrition_tags' => $request->nutrition_tags,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'foods',
                'record_id' => $food->food_id,
                'old_values' => json_encode($oldData),
                'new_values' => json_encode($food->toArray()),
                'description' => 'Updated food: ' . $food->food_name_and_description,
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food item updated successfully!',
                    'data' => $food
                ]);
            }

            return redirect()->route('admin.foods.index')
                ->with('success', 'Food item updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating food: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the food item.'
                ], 500);
            }
            
            return redirect()->route('admin.foods.index')
                ->with('error', 'An error occurred while updating the food item.')
                ->withInput();
        }
    }

    /**
     * Remove the specified food from database (Admin)
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $food = Food::findOrFail($id);
            $foodName = $food->food_name_and_description;

            // Log the action before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'foods',
                'record_id' => $food->food_id,
                'old_values' => json_encode($food->toArray()),
                'description' => 'Deleted food: ' . $foodName,
            ]);

            $food->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Food item deleted successfully!'
                ]);
            }

            return redirect()->route('admin.foods.index')
                ->with('success', 'Food item deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting food: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the food item.'
                ], 500);
            }
            
            return redirect()->route('admin.foods.index')
                ->with('error', 'An error occurred while deleting the food item.');
        }
    }

    /**
     * Batch delete foods (Admin)
     */
    public function batchDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:foods,food_id'
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
            $deletedCount = 0;

            foreach ($ids as $id) {
                $food = Food::find($id);
                if ($food) {
                    // Log the action
                    AuditLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'delete',
                        'table_name' => 'foods',
                        'record_id' => $food->food_id,
                        'old_values' => json_encode($food->toArray()),
                        'description' => 'Batch deleted food: ' . $food->food_name_and_description,
                    ]);
                    
                    $food->delete();
                    $deletedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} food items."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch deleting foods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting food items.'
            ], 500);
        }
    }

    /**
     * Import foods from CSV (Admin)
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Skip header row
            $header = fgetcsv($handle);
            
            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                try {
                    if (count($row) >= 4) {
                        Food::create([
                            'food_name_and_description' => $row[0] ?? null,
                            'alternate_common_names' => $row[1] ?? null,
                            'energy_kcal' => is_numeric($row[2]) ? floatval($row[2]) : null,
                            'nutrition_tags' => $row[3] ?? null,
                        ]);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Row error: ' . implode(',', $row);
                }
            }

            fclose($handle);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'import',
                'table_name' => 'foods',
                'description' => "Imported {$imported} food items from CSV",
            ]);

            DB::commit();

            $message = "Successfully imported {$imported} food items.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " rows had errors.";
            }

            return redirect()->route('admin.foods.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing foods: ' . $e->getMessage());
            return redirect()->route('admin.foods.index')
                ->with('error', 'An error occurred while importing food items.');
        }
    }

    /**
     * Export foods to CSV (Admin)
     */
    public function export()
    {
        try {
            $foods = Food::orderBy('food_name_and_description')->get();

            $filename = 'foods_export_' . date('Y-m-d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($foods) {
                $file = fopen('php://output', 'w');
                
                // Add header row
                fputcsv($file, ['Food Name & Description', 'Alternate Common Names', 'Energy (kcal)', 'Nutrition Tags']);
                
                // Add data rows
                foreach ($foods as $food) {
                    fputcsv($file, [
                        $food->food_name_and_description,
                        $food->alternate_common_names,
                        $food->energy_kcal,
                        $food->nutrition_tags,
                    ]);
                }
                
                fclose($file);
            };

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'export',
                'table_name' => 'foods',
                'description' => 'Exported ' . $foods->count() . ' food items to CSV',
            ]);

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error exporting foods: ' . $e->getMessage());
            return redirect()->route('admin.foods.index')
                ->with('error', 'An error occurred while exporting food items.');
        }
    }

    /**
     * Get all unique nutrition tags
     */
    private function getAllUniqueTags()
    {
        try {
            $allTags = Food::whereNotNull('nutrition_tags')
                ->where('nutrition_tags', '!=', '')
                ->pluck('nutrition_tags')
                ->flatMap(function ($tags) {
                    return array_filter(array_map('trim', explode(',', $tags)));
                })
                ->unique()
                ->sort()
                ->values();

            return $allTags;
        } catch (\Exception $e) {
            Log::error('Error getting tags: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Search foods (AJAX for API/LLM)
     */
    public function search(Request $request)
    {
        $search = $request->input('q', '');
        $limit = $request->input('limit', 50);

        $foods = Food::search($search)
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $foods,
            'count' => $foods->count(),
        ]);
    }

    /**
     * Check if a food already exists in the database (for duplicate prevention)
     */
    public function checkDuplicate(Request $request)
    {
        $name = $request->input('name', '');
        $excludeId = $request->input('exclude_id'); // For edit operations
        
        if (strlen($name) < 3) {
            return response()->json([
                'exists' => false,
                'duplicates' => [],
                'message' => 'Search term too short'
            ]);
        }

        // Check in existing foods table with similarity search
        $foodsQuery = Food::where(function($query) use ($name) {
                $query->where('food_name_and_description', 'LIKE', '%' . $name . '%')
                      ->orWhere('alternate_common_names', 'LIKE', '%' . $name . '%');
            });
        
        if ($excludeId) {
            $foodsQuery->where('food_id', '!=', $excludeId);
        }
        
        $duplicateFoods = $foodsQuery->limit(5)->get(['food_id', 'food_name_and_description', 'alternate_common_names']);

        // Check in pending food requests
        $duplicateRequests = FoodRequest::where('status', 'pending')
            ->where(function($query) use ($name) {
                $query->where('food_name_and_description', 'LIKE', '%' . $name . '%')
                      ->orWhere('alternate_common_names', 'LIKE', '%' . $name . '%');
            })
            ->limit(5)
            ->get(['id', 'food_name_and_description', 'alternate_common_names']);

        $exists = $duplicateFoods->count() > 0 || $duplicateRequests->count() > 0;

        return response()->json([
            'exists' => $exists,
            'in_database' => $duplicateFoods->count() > 0,
            'in_pending_requests' => $duplicateRequests->count() > 0,
            'duplicates' => [
                'foods' => $duplicateFoods,
                'requests' => $duplicateRequests
            ],
            'message' => $exists 
                ? 'Found ' . ($duplicateFoods->count() + $duplicateRequests->count()) . ' similar item(s)' 
                : 'Food name is available'
        ]);
    }

    /**
     * Quick add food (simplified form for faster entry)
     */
    public function quickAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_name_and_description' => 'required|string|max:5000',
            'energy_kcal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $food = Food::create([
                'food_name_and_description' => $request->food_name_and_description,
                'energy_kcal' => $request->energy_kcal,
                'alternate_common_names' => $request->alternate_common_names,
                'nutrition_tags' => $request->nutrition_tags,
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'foods',
                'record_id' => $food->food_id,
                'description' => 'Quick added food: ' . $food->food_name_and_description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Food added successfully!',
                'data' => $food
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error quick adding food: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the food item.'
            ], 500);
        }
    }
}

