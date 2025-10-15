<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Assessment;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\ItemCategory;
use App\Models\Barangay;
use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Cache dashboard stats for 5 minutes to improve performance
        $stats = cache()->remember('admin_dashboard_stats', 300, function () {
            return [
                'total_users' => User::count(),
                'total_patients' => Patient::count(),
                'total_assessments' => Assessment::count(),
                'total_inventory_items' => InventoryItem::count(),
                'total_knowledge_articles' => \App\Models\KnowledgeBase::count(),
                'new_articles_this_month' => \App\Models\KnowledgeBase::whereMonth('added_at', now()->month)
                    ->whereYear('added_at', now()->year)
                    ->count(),
                'total_kb_categories' => 1, // Placeholder for future categories feature
                'pending_nutritionist_applications' => User::whereHas('role', function($query) {
                        $query->where('role_name', 'Nutritionist');
                })->where('is_active', false)->count(),
                'recent_transactions' => InventoryTransaction::with(['user', 'inventoryItem'])
                    ->latest()
                    ->take(5)
                    ->get(),
                'recent_audit_logs' => AuditLog::with('user')
                    ->latest()
                    ->take(10)
                    ->get(),
            ];
        });

        // Use the extracted method for barangay data processing
        $barangays = $this->getBarangayPatientData();

        return view('admin.dashboard', compact('stats', 'barangays'));
    }

    /**
     * Get map data for admin dashboard
     */
    public function getMapData()
    {
        try {
            // Get patients with barangay information
            $patients = Patient::with(['barangay'])
                ->whereHas('barangay', function($query) {
                    $query->whereNotNull('latitude')
                          ->whereNotNull('longitude');
                })
                ->take(50) // Limit for performance
                ->get();

            // Get recent assessments with patient and location data
            $assessments = Assessment::with(['patient.barangay'])
                ->whereHas('patient.barangay', function($query) {
                    $query->whereNotNull('latitude')
                          ->whereNotNull('longitude');
                })
                ->latest()
                ->take(30) // Limit for performance
                ->get();

            // Use the extracted method for barangay data processing
            $barangays = $this->getBarangayPatientData(true, true);

            $mapData = [
                'patients' => $patients->map(function($patient) {
                    return [
                        'id' => $patient->patient_id,
                        'name' => $patient->first_name . ' ' . $patient->last_name,
                        'barangay' => $patient->barangay->name ?? 'Unknown',
                        'lat' => (float) $patient->barangay->latitude,
                        'lng' => (float) $patient->barangay->longitude,
                        'status' => $patient->current_status ?? 'Active',
                        'age_months' => $patient->age_months,
                        'sex' => $patient->sex
                    ];
                }),
                'assessments' => $assessments->map(function($assessment) {
                    return [
                        'id' => $assessment->assessment_id,
                        'patient_name' => $assessment->patient ? 
                            $assessment->patient->first_name . ' ' . $assessment->patient->last_name : 'Unknown',
                        'lat' => $assessment->patient && $assessment->patient->barangay ? 
                            (float) $assessment->patient->barangay->latitude : null,
                        'lng' => $assessment->patient && $assessment->patient->barangay ? 
                            (float) $assessment->patient->barangay->longitude : null,
                        'date' => $assessment->created_at->format('Y-m-d'),
                        'status' => $assessment->status ?? 'Completed'
                    ];
                })->filter(function($assessment) {
                    return $assessment['lat'] !== null && $assessment['lng'] !== null;
                }),
                'barangays' => $barangays
            ];

            return response()->json([
                'success' => true,
                'data' => $mapData
            ]);

        } catch (\Exception $e) {
            Log::error('Map data error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading map data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show users management
     */
    public function users()
    {
        $query = User::with('role');
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('contact_number', 'like', "%$search%") ;
            });
        }
        if (request('role')) {
            $query->where('role_id', request('role'));
        }
        if (request('status') !== null && request('status') !== '') {
            $query->where('is_active', request('status'));
        }
        $users = $query->paginate(10)->appends(request()->query());
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Show patients management
     */
    public function patients()
    {
    $patients = Patient::with(['parent', 'nutritionist', 'barangay'])->orderBy('created_at', 'desc')->paginate(15);
        $barangays = Barangay::all();
        $nutritionists = User::where('role_id', function($query) {
                $query->select('role_id')->from('roles')->where('role_name', 'Nutritionist');
        })->get();
        $parents = User::where('role_id', function($query) {
            $query->select('role_id')->from('roles')->where('role_name', 'Parent');
        })->get();
        
        return view('admin.patients', compact('patients', 'barangays', 'nutritionists', 'parents'));
    }

    /**
     * Store a new patient (Admin)
     */
    public function storePatient(Request $request)
    {
        $request->validate($this->getPatientValidationRules());

        try {
            $patient = Patient::create([
                'parent_id' => $request->parent_id,
                'nutritionist_id' => $request->nutritionist_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'barangay_id' => $request->barangay_id,
                'contact_number' => $request->contact_number,
                'age_months' => $request->age_months,
                'sex' => $request->sex,
                'date_of_admission' => $request->date_of_admission,
                'total_household_adults' => $request->total_household_adults ?? 0,
                'total_household_children' => $request->total_household_children ?? 0,
                'total_household_twins' => $request->total_household_twins ?? 0,
                'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
                'weight_kg' => $request->weight_kg,
                'height_cm' => $request->height_cm,
                'weight_for_age' => $request->weight_for_age,
                'height_for_age' => $request->height_for_age,
                'bmi_for_age' => $request->bmi_for_age,
                'breastfeeding' => $request->breastfeeding,
                'other_medical_problems' => $request->other_medical_problems,
                'edema' => $request->edema,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient added successfully!',
                'patient' => $patient->load(['parent', 'nutritionist', 'barangay'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient data for editing (Admin)
     */
    public function getPatient($id)
    {
        return $this->getRecord(Patient::class, $id, ['parent', 'nutritionist', 'barangay']);
    }

    /**
     * Update patient (Admin)
     */
    public function updatePatient(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.'
            ], 404);
        }
        $request->validate($this->getPatientValidationRules(true));
        $patient->update([
            'parent_id' => $request->parent_id,
            'nutritionist_id' => $request->nutritionist_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'barangay_id' => $request->barangay_id,
            'contact_number' => $request->contact_number,
            'age_months' => $request->age_months,
            'sex' => $request->sex,
            'date_of_admission' => $request->date_of_admission,
            'total_household_adults' => $request->total_household_adults ?? 0,
            'total_household_children' => $request->total_household_children ?? 0,
            'total_household_twins' => $request->total_household_twins ?? 0,
            'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'weight_for_age' => $request->weight_for_age,
            'height_for_age' => $request->height_for_age,
            'bmi_for_age' => $request->bmi_for_age,
            'breastfeeding' => $request->breastfeeding,
            'other_medical_problems' => $request->other_medical_problems,
            'edema' => $request->edema,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully!',
            'patient' => $patient->load(['parent', 'nutritionist', 'barangay'])
        ]);
    }

    /**
     * Delete patient (Admin)
     */
    public function deletePatient($id)
    {
        try {
            $patient = Patient::findOrFail($id);

            // Check if patient has any assessments
            $assessmentCount = $patient->assessments()->count();
            if ($assessmentCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete patient. This patient has ' . $assessmentCount . ' assessment(s) associated with them.'
                ], 400);
            }

            $patient->delete();

            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show assessments management
     */
    public function assessments()
    {
        $assessments = Assessment::with(['patient.barangay'])->paginate(15);
        return view('admin.assessments', compact('assessments'));
    }

    /**
     * Show inventory management
     */
    public function inventory()
    {
        $items = InventoryItem::with(['category', 'inventoryTransactions'])
            ->paginate(15);
        $categories = ItemCategory::all();
        $patients = Patient::select('patient_id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();
        return view('admin.inventory', compact('items', 'categories', 'patients'));
    }

    /**
     * Store a new inventory item
     */
    public function storeInventoryItem(Request $request)
    {
        $request->validate($this->getInventoryValidationRules());

        try {
            DB::beginTransaction();

            $item = InventoryItem::create([
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'quantity' => $request->quantity,
                'expiry_date' => $request->expiry_date,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'inventory_create',
                'description' => "Created new inventory item: {$item->item_name} (Qty: {$item->quantity} {$item->unit})",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item added successfully!',
                'item' => $item->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an inventory item
     */
    public function updateInventoryItem(Request $request, $id)
    {
        $request->validate($this->getInventoryValidationRules(true));

        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $oldValues = $item->toArray();

            $item->update([
                'item_name' => $request->item_name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'quantity' => $request->quantity,
                'expiry_date' => $request->expiry_date,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'inventory_update',
                'description' => "Updated inventory item: {$item->item_name} (New qty: {$item->quantity} {$item->unit})",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item updated successfully!',
                'item' => $item->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete an inventory item
     */
    public function deleteInventoryItem($id)
    {
        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $itemName = $item->item_name;

            // Check if item has transactions
            if ($item->inventoryTransactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete item with existing transactions. Archive it instead.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'inventory_delete',
                'description' => "Deleted inventory item: {$itemName} (Had {$item->quantity} {$item->unit})",
            ]);

            $item->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inventory item deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete inventory item. Please try again.'
            ], 500);
        }
    }

    /**
     * Get inventory item details for editing
     */
    public function getInventoryItem($id)
    {
        return $this->getRecord(InventoryItem::class, $id, ['category']);
    }

    /**
     * Show reports
     */
    public function reports()
    {
        $reports = [
            'monthly_assessments' => Assessment::whereMonth('created_at', now()->month)->count(),
            'low_stock_items' => InventoryItem::where('quantity', '<', 10)->count(),
            'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'total_patients' => Patient::count(),
            'total_inventory_value' => InventoryItem::all()->sum(function($item) {
                return $item->quantity * $item->unit_cost;
            }),
            'inventory_by_category' => $this->getInventoryByCategory(),
            'recent_activities' => $this->getRecentActivities(),
            'patient_distribution' => $this->getPatientDistribution(),
            'monthly_progress' => $this->getMonthlyProgress(),
        ];

        return view('admin.reports', compact('reports'));
    }

    /**
     * Generate User Activity Report
     */
    public function generateUserActivityReport()
    {
        $users = User::with('role')->get();
        $assessments = Assessment::with(['user', 'patient'])->get();
        
        $report_data = [
            'total_users' => $users->count(),
            'active_users_30_days' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'users_by_role' => $users->filter(function($user) {
                return $user->role !== null;
            })->groupBy('role.name')->map->count(),
            'recent_assessments' => $assessments->take(10)->map(function($assessment) {
                return [
                    'id' => $assessment->assessment_id,
                    'patient' => $assessment->patient ? [
                        'first_name' => $assessment->patient->first_name,
                        'last_name' => $assessment->patient->last_name
                    ] : null,
                    'user' => $assessment->user ? ['name' => $assessment->user->name] : null,
                    'created_at' => $assessment->created_at
                ];
            }),
            'assessments_by_user' => $assessments->filter(function($assessment) {
                return $assessment->user !== null;
            })->groupBy('nutritionist_id')->map->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $report_data,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Generate Inventory Report
     */
    public function generateInventoryReport()
    {
        $inventory_items = InventoryItem::with('category')->get();
        $transactions = InventoryTransaction::with('item', 'user')->latest()->take(50)->get();
        
        $low_stock_items = $inventory_items->where('quantity', '<', 10)->map(function($item) {
            return [
                'item_name' => $item->item_name,
                'category' => [
                    'category_name' => $item->category ? $item->category->name : null
                ],
                'quantity' => $item->quantity,
                'minimum_stock' => property_exists($item, 'minimum_stock') ? $item->minimum_stock : 10,
                'unit' => $item->unit,
                'unit_cost' => $item->unit_cost
            ];
        })->values()->all();

        $report_data = [
            'total_items' => $inventory_items->count(),
            // 'total_value' removed
            'low_stock_items' => $low_stock_items,
            'items_by_category' => $inventory_items->groupBy('category.name')->map->count(),
            'recent_transactions' => $transactions,
            'stock_levels' => $inventory_items->map(function($item) {
                return [
                    'item_name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'status' => $item->quantity < 10 ? 'Low' : ($item->quantity < 50 ? 'Medium' : 'Good')
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $report_data,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }



    /**
     * Generate Low Stock Alert Report
     */
    public function generateLowStockReport()
    {
        $low_stock_items = InventoryItem::with('category')
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->get();
        
        $report_data = [
            'critical_items' => $low_stock_items->where('quantity', '<', 5)->values(),
            'low_items' => $low_stock_items->where('quantity', '>=', 5)->values(),
            'total_affected_value' => $low_stock_items->sum(function($item) {
                return $item->quantity * $item->unit_cost;
            }),
            'categories_affected' => $low_stock_items->groupBy('category.name')->map->count(),
            'recommendations' => $this->getRestockRecommendations($low_stock_items),
        ];

        return response()->json([
            'success' => true,
            'data' => $report_data,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }



    /**
     * Get inventory by category
     */
    private function getInventoryByCategory()
    {
        return ItemCategory::withCount('inventoryItems')
            ->get()
            ->pluck('inventory_items_count', 'name')
            ->toArray();
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = collect();
        
        // Recent assessments
        $recent_assessments = Assessment::with(['user', 'patient'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($assessment) {
                return [
                    'type' => 'assessment',
                    'description' => "Assessment for " . ($assessment->patient ? "{$assessment->patient->first_name} {$assessment->patient->last_name}" : "Unknown Patient"),
                    'user' => $assessment->user ? $assessment->user->name : 'Unknown User',
                    'time' => $assessment->created_at,
                ];
            });
        
        // Recent inventory transactions
        $recent_transactions = InventoryTransaction::with(['user', 'item'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($transaction) {
                return [
                    'type' => 'inventory',
                    'description' => "{$transaction->transaction_type} - " . ($transaction->item ? $transaction->item->name : "Unknown Item") . " (Qty: {$transaction->quantity})",
                    'user' => $transaction->user ? $transaction->user->name : 'Unknown User',
                    'time' => $transaction->created_at,
                ];
            });
        
        return $activities->merge($recent_assessments)
            ->merge($recent_transactions)
            ->sortByDesc('time')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * Get restock recommendations
     */
    private function getRestockRecommendations($low_stock_items)
    {
        return $low_stock_items->map(function($item) {
            $usage_rate = InventoryTransaction::where('item_id', $item->id)
                ->where('transaction_type', 'Out')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('quantity');
            
            $recommended_quantity = max(50, $usage_rate * 2); // At least 50 or 2 months usage
            
            return [
                'item' => $item->name,
                'current_stock' => $item->quantity,
                'recommended_order' => $recommended_quantity,
                'estimated_cost' => $recommended_quantity * $item->unit_cost,
                'urgency' => $item->quantity < 5 ? 'Critical' : 'Medium'
            ];
        })->toArray();
    }

    /**
     * Show system management page
     */
    public function systemManagement()
    {
        $categories = ItemCategory::withCount('inventoryItems')->paginate(10, ['*'], 'categories_page');
        $barangays = Barangay::withCount('patients')->paginate(10, ['*'], 'barangays_page');
        
        return view('admin.system-management', compact('categories', 'barangays'));
    }

    /**
     * Store a new item category
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:item_categories,category_name',
        ]);

        try {
            DB::beginTransaction();

            $category = ItemCategory::create([
                'category_name' => $request->category_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => null,
                'new_values' => json_encode($category->toArray()),
                'description' => "Created new item category: {$category->category_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category added successfully!',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add category. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an item category
     */
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255|unique:item_categories,category_name,' . $id . ',category_id',
        ]);

        try {
            DB::beginTransaction();

            $category = ItemCategory::findOrFail($id);
            $oldValues = $category->toArray();

            $category->update([
                'category_name' => $request->category_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($category->toArray()),
                'description' => "Updated item category: {$category->category_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete an item category
     */
    public function deleteCategory($id)
    {
        try {
            DB::beginTransaction();

            $category = ItemCategory::findOrFail($id);
            $categoryName = $category->category_name;

            // Check if category has inventory items
            if ($category->inventoryItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing inventory items.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'item_categories',
                'record_id' => $category->category_id,
                'old_values' => json_encode($category->toArray()),
                'new_values' => null,
                'description' => "Deleted item category: {$categoryName}",
            ]);

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category. Please try again.'
            ], 500);
        }
    }

    /**
     * Store a new barangay
     */
    public function storeBarangay(Request $request)
    {
        $request->validate([
            'barangay_name' => 'required|string|max:255|unique:barangays,barangay_name',
        ]);

        try {
            DB::beginTransaction();

            $barangay = Barangay::create([
                'barangay_name' => $request->barangay_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'create',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => null,
                'new_values' => json_encode($barangay->toArray()),
                'description' => "Created new barangay: {$barangay->barangay_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay added successfully!',
                'barangay' => $barangay
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Update a barangay
     */
    public function updateBarangay(Request $request, $id)
    {
        $request->validate([
            'barangay_name' => 'required|string|max:255|unique:barangays,barangay_name,' . $id . ',barangay_id',
        ]);

        try {
            DB::beginTransaction();

            $barangay = Barangay::findOrFail($id);
            $oldValues = $barangay->toArray();

            $barangay->update([
                'barangay_name' => $request->barangay_name,
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'update',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($barangay->toArray()),
                'description' => "Updated barangay: {$barangay->barangay_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay updated successfully!',
                'barangay' => $barangay
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a barangay
     */
    public function deleteBarangay($id)
    {
        try {
            DB::beginTransaction();

            $barangay = Barangay::findOrFail($id);
            $barangayName = $barangay->barangay_name;

            // Check if barangay has patients
            if ($barangay->patients()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete barangay with existing patients.'
                ], 422);
            }

            // Log the activity before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'delete',
                'table_name' => 'barangays',
                'record_id' => $barangay->barangay_id,
                'old_values' => json_encode($barangay->toArray()),
                'new_values' => null,
                'description' => "Deleted barangay: {$barangayName}",
            ]);

            $barangay->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barangay deleted successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete barangay. Please try again.'
            ], 500);
        }
    }

    /**
     * Get category details for editing
     */
    public function getCategory($id)
    {
        return $this->getRecord(ItemCategory::class, $id);
    }

    /**
     * Get barangay details for editing
     */
    public function getBarangay($id)
    {
        return $this->getRecord(Barangay::class, $id);
    }

    // ========== USER MANAGEMENT CRUD METHODS ==========

    /**
     * Store a new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,NULL,user_id,deleted_at,NULL',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,role_id',
            'contact_number' => 'nullable|string|max:15',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'contact_number' => $request->contact_number,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Created new user: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user
     */
    public function getUser($id)
    {
        return $this->getRecord(User::class, $id, ['role']);
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($user->user_id, 'user_id')
                    ->whereNull('deleted_at')
            ],
            'role_id' => 'required|exists:roles,role_id',
            'contact_number' => 'nullable|string|max:15',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $user->toArray();

            $updateData = [
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'contact_number' => $request->contact_number,
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $user->is_active,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Updated user: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            DB::beginTransaction();

            $userName = "{$user->first_name} {$user->last_name}";

            // Log the action before deletion
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Soft deleted user: {$userName}",
            ]);

            // Soft delete the user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted user
     */
    public function restoreUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            if (!$user->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not deleted.'
                ], 400);
            }

            DB::beginTransaction();

            $userName = "{$user->first_name} {$user->last_name}";

            // Log the action before restoration
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'RESTORE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Restored user: {$userName}",
            ]);

            // Restore the user
            $user->restore();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users including soft-deleted ones
     */
    public function getUsersWithTrashed()
    {
        try {
            $users = User::withTrashed()->with('role')->paginate(15);
            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a user account
     */
    public function activateUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent activating/deactivating the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            if ($user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already active.'
                ], 400);
            }

            DB::beginTransaction();

            $user->update(['is_active' => true]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'ACTIVATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Activated user account: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a user account
     */
    public function deactivateUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent activating/deactivating the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already inactive.'
                ], 400);
            }

            DB::beginTransaction();

            $user->update(['is_active' => false]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DEACTIVATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Deactivated user account: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending nutritionist applications
     */
    public function getPendingNutritionistApplications()
    {
        $pendingApplications = User::with('role')
            ->whereHas('role', function($query) {
                $query->where('role_name', 'Nutritionist');
            })
            ->where('is_active', false)
            ->get();

        return response()->json([
            'success' => true,
            'applications' => $pendingApplications
        ]);
    }

    /**
     * Approve nutritionist application
     */
    public function approveNutritionist(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Check if user is a nutritionist and inactive
            if ($user->role->role_name !== 'Nutritionist' || $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application or already approved.'
                ], 400);
            }

            // Generate a new temporary password for the nutritionist
            $tempPassword = 'nutri_' . substr(md5(time() . $user->email), 0, 8);

            // Activate the user account
            $user->update([
                'is_active' => true,
                'password' => Hash::make($tempPassword)
            ]);

            // Log the approval
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'APPROVE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Approved nutritionist application for: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            // In a real application, you would send an email to the nutritionist here
            // with their login credentials and welcome information

            return response()->json([
                'success' => true,
                'message' => 'Nutritionist application approved successfully.',
                'temp_password' => $tempPassword // In production, this should be sent via email
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject nutritionist application
     */
    public function rejectNutritionist(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Check if user is a nutritionist and inactive
            if ($user->role->role_name !== 'Nutritionist' || $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application or already processed.'
                ], 400);
            }

            $userName = "{$user->first_name} {$user->last_name}";

            // Log the rejection
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REJECT',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Rejected nutritionist application for: {$userName}. Reason: {$request->reason}",
            ]);

            // Delete the application
            $user->delete();

            DB::commit();

            // In a real application, you would send an email to the applicant here
            // with the rejection reason

            return response()->json([
                'success' => true,
                'message' => 'Nutritionist application rejected successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stock In - Add inventory to existing item
     */
    public function stockIn(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $oldQuantity = $item->quantity;
            $newQuantity = $oldQuantity + $request->quantity;

            // Update item quantity
            $item->update(['quantity' => $newQuantity]);

            // Create transaction record
            InventoryTransaction::create([
                'item_id' => $item->item_id,
                'user_id' => Auth::id(),
                'patient_id' => null, // Stock in doesn't involve patients
                'transaction_type' => 'In',
                'quantity' => $request->quantity,
                'transaction_date' => now(),
                'remarks' => $request->remarks ?? "Stock in - Added {$request->quantity} {$item->unit}(s)",
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'stock_in',
                'description' => "Stock in for {$item->item_name}: Added {$request->quantity} {$item->unit}(s). Total: {$oldQuantity} → {$newQuantity}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully added {$request->quantity} {$item->unit}(s) to {$item->item_name}",
                'item' => $item->load('category'),
                'new_quantity' => $newQuantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process stock in. Please try again.'
            ], 500);
        }
    }

    /**
     * Stock Out - Remove inventory from existing item
     */
    public function stockOut(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'patient_id' => 'nullable|exists:patients,patient_id',
            'remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $item = InventoryItem::findOrFail($id);
            $oldQuantity = $item->quantity;

            // Check if there's enough stock
            if ($oldQuantity < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$oldQuantity} {$item->unit}(s), Requested: {$request->quantity} {$item->unit}(s)"
                ], 400);
            }

            $newQuantity = $oldQuantity - $request->quantity;

            // Update item quantity
            $item->update(['quantity' => $newQuantity]);

            // Create transaction record
            InventoryTransaction::create([
                'item_id' => $item->item_id,
                'user_id' => Auth::id(),
                'patient_id' => $request->patient_id,
                'transaction_type' => 'Out',
                'quantity' => $request->quantity,
                'transaction_date' => now(),
                'remarks' => $request->remarks ?? "Stock out - Removed {$request->quantity} {$item->unit}(s)",
            ]);

            // Log the activity
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'stock_out',
                'description' => "Stock out for {$item->item_name}: Removed {$request->quantity} {$item->unit}(s). Total: {$oldQuantity} → {$newQuantity}" . ($request->patient_id ? " (Patient involved)" : ""),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$request->quantity} {$item->unit}(s) from {$item->item_name}",
                'item' => $item->load('category'),
                'new_quantity' => $newQuantity
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process stock out. Please try again.'
            ], 500);
        }
    }



    /**
     * Download User Activity Report as PDF
     */
    public function downloadUserActivityReport(Request $request)
    {
        return $this->downloadReport($request, 'admin.reports.pdf.user-activity', 'user-activity-report');
    }

    /**
     * Download Inventory Report as PDF
     */
    public function downloadInventoryReport(Request $request)
    {
        return $this->downloadReport($request, 'admin.reports.pdf.inventory', 'inventory-report');
    }



    /**
     * Download Low Stock Report as PDF
     */
    public function downloadLowStockReport(Request $request)
    {
        return $this->downloadReport($request, 'admin.reports.pdf.low-stock', 'low-stock-report');
    }

    /**
     * Get patient distribution data by nutrition status
     */
    private function getPatientDistribution()
    {
        $patients = Patient::with('assessments')->get();
        $distribution = [
            'normal' => 0,
            'underweight' => 0,
            'malnourished' => 0,
            'severe_malnourishment' => 0,
        ];

        foreach ($patients as $patient) {
            // Get the latest assessment for each patient
            $latestAssessment = $patient->assessments()->latest()->first();
            
            if ($latestAssessment) {
                // Calculate BMI if we have weight and height
                if ($latestAssessment->weight_kg && $latestAssessment->height_cm) {
                    $height_m = $latestAssessment->height_cm / 100;
                    $bmi = $latestAssessment->weight_kg / ($height_m * $height_m);
                    
                    // Classify based on BMI (you can adjust these thresholds based on WHO standards)
                    if ($bmi < 16) {
                        $distribution['severe_malnourishment']++;
                    } elseif ($bmi < 18.5) {
                        $distribution['malnourished']++;
                    } elseif ($bmi < 17) {
                        $distribution['underweight']++;
                    } else {
                        $distribution['normal']++;
                    }
                } else {
                    // If no weight/height data, check recovery status
                    if ($latestAssessment->recovery_status === 'severe') {
                        $distribution['severe_malnourishment']++;
                    } elseif ($latestAssessment->recovery_status === 'moderate') {
                        $distribution['malnourished']++;
                    } elseif ($latestAssessment->recovery_status === 'mild') {
                        $distribution['underweight']++;
                    } else {
                        $distribution['normal']++;
                    }
                }
            } else {
                // Patient with no assessments - classify based on initial data
                if ($patient->weight_kg && $patient->height_cm) {
                    $height_m = $patient->height_cm / 100;
                    $bmi = $patient->weight_kg / ($height_m * $height_m);
                    
                    if ($bmi < 16) {
                        $distribution['severe_malnourishment']++;
                    } elseif ($bmi < 18.5) {
                        $distribution['malnourished']++;
                    } elseif ($bmi < 17) {
                        $distribution['underweight']++;
                    } else {
                        $distribution['normal']++;
                    }
                } else {
                    $distribution['normal']++;
                }
            }
        }

        // Calculate percentages
        $total = array_sum($distribution);
        if ($total > 0) {
            foreach ($distribution as $key => $count) {
                $distribution[$key] = [
                    'count' => $count,
                    'percentage' => round(($count / $total) * 100, 1)
                ];
            }
        }

        return $distribution;
    }

    /**
     * Get monthly progress data for the last 6 months
     */
    private function getMonthlyProgress()
    {
        $months = [];
        $assessmentCounts = [];
        $recoveredCounts = [];
        
        // Get last 6 months of data
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Count assessments for this month
            $monthlyAssessments = Assessment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $assessmentCounts[] = $monthlyAssessments;
            
            // Count recovered patients (assessments with positive recovery status)
            $recoveredCount = Assessment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('recovery_status', 'recovered')
                ->count();
            $recoveredCounts[] = $recoveredCount;
        }

        return [
            'months' => $months,
            'assessments' => $assessmentCounts,
            'recovered' => $recoveredCounts,
            'total_assessments' => array_sum($assessmentCounts),
            'total_recovered' => array_sum($recoveredCounts),
        ];
    }

    /**
     * Get barangay data with patient counts and malnutrition diagnosis statistics
     * 
     * @param bool $includePatientCount Whether to include patient count (for map data)
     * @param bool $includeActivityLevel Whether to include activity level calculation
     * @return array
     */
    private function getBarangayPatientData($includePatientCount = false, $includeActivityLevel = false)
    {
        // Fetch barangays from DB with coordinates
        $barangayList = Barangay::whereNotNull('latitude')->whereNotNull('longitude')->get();
        $barangays = [];

        foreach ($barangayList as $b) {
            $sam = 0;
            $mam = 0;
            $normal = 0;
            $unknown = 0;
            $patients = Patient::where('barangay_id', $b->barangay_id)->pluck('patient_id');
            
            foreach ($patients as $patientId) {
                $latestAssessment = Assessment::where('patient_id', $patientId)
                    ->orderByDesc('assessment_date')
                    ->first();
                    
                if ($latestAssessment) {
                    $diagnosis = null;
                    if ($latestAssessment->treatment) {
                        $treatment = json_decode($latestAssessment->treatment, true);
                        if (isset($treatment['diagnosis'])) {
                            // Normalize: lowercase, remove parentheses and their contents, trim spaces
                            $diagnosis = strtolower($treatment['diagnosis']);
                            $diagnosis = trim($diagnosis);
                        }
                    }
                    
                    if ($diagnosis) {
                        // Use regex to match both full and short forms, with or without parentheses
                        if (preg_match('/severe\s*acute\s*malnutrition(\s*\(sam\))?|^sam$/i', $diagnosis)) {
                            $sam++;
                        } elseif (preg_match('/moderate\s*acute\s*malnutrition(\s*\(mam\))?|^mam$/i', $diagnosis)) {
                            $mam++;
                        } elseif (preg_match('/^normal$/i', $diagnosis) || preg_match('/normal/i', $diagnosis)) {
                            $normal++;
                        } else {
                            $unknown++;
                        }
                    } else {
                        $unknown++;
                    }
                }
            }

            $barangayData = [
                'id' => $b->barangay_id,
                'name' => $b->barangay_name,
                'lat' => (float) $b->latitude,
                'lng' => (float) $b->longitude,
                'sam_count' => $sam,
                'mam_count' => $mam,
                'normal_count' => $normal,
                'unknown_count' => $unknown
            ];

            // Add optional fields for map data
            if ($includePatientCount) {
                $patientCount = count($patients);
                $barangayData['patient_count'] = $patientCount;
                
                if ($includeActivityLevel) {
                    $barangayData['activity_level'] = $patientCount > 10 ? 'high' : ($patientCount > 5 ? 'medium' : 'low');
                }
            }

            $barangays[] = $barangayData;
        }

        return $barangays;
    }

    /**
     * Log activity to audit log
     * 
     * @param string $action
     * @param string|null $tableName
     * @param int|null $recordId
     * @param string $description
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return void
     */
    private function logActivity($action, $tableName = null, $recordId = null, $description = '', $oldValues = null, $newValues = null)
    {
        $logData = [
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ];

        // Add optional fields if provided
        if ($tableName) {
            $logData['table_name'] = $tableName;
        }
        
        if ($recordId) {
            $logData['record_id'] = $recordId;
        }
        
        if ($oldValues) {
            $logData['old_values'] = is_array($oldValues) ? json_encode($oldValues) : $oldValues;
        }
        
        if ($newValues) {
            $logData['new_values'] = is_array($newValues) ? json_encode($newValues) : $newValues;
        }

        AuditLog::create($logData);
    }

    /**
     * Get patient validation rules
     * 
     * @param bool $isUpdate
     * @return array
     */
    private function getPatientValidationRules($isUpdate = false)
    {
        return [
            'parent_id' => 'nullable|exists:users,user_id',
            'nutritionist_id' => 'nullable|exists:users,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'barangay_id' => 'required|exists:barangays,barangay_id',
            'contact_number' => 'required|string|max:20',
            'age_months' => 'required|integer|min:0',
            'sex' => 'required|in:Male,Female',
            'date_of_admission' => 'required|date',
            'weight_kg' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get inventory item validation rules
     * 
     * @param bool $isUpdate
     * @return array
     */
    private function getInventoryValidationRules($isUpdate = false)
    {
        return [
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,category_id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after:today',
        ];
    }

    /**
     * Generic method to get a record with relationships
     * 
     * @param string $modelClass
     * @param int $id
     * @param array $relationships
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecord($modelClass, $id, $relationships = [])
    {
        try {
            $query = $modelClass::query();
            
            if (!empty($relationships)) {
                $query->with($relationships);
            }
            
            $record = $query->findOrFail($id);
            
            $recordName = strtolower(class_basename($modelClass));
            
            return response()->json([
                'success' => true,
                $recordName => $record
            ]);
        } catch (\Exception $e) {
            $recordName = strtolower(class_basename($modelClass));
            return response()->json([
                'success' => false,
                'message' => ucfirst($recordName) . ' not found.'
            ], 404);
        }
    }

    /**
     * Unified PDF download method
     * 
     * @param \Illuminate\Http\Request $request
     * @param string $viewPath
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function downloadReport(Request $request, $viewPath, $fileName)
    {
        $reportData = json_decode($request->input('report_data'), true);
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $pdf = Pdf::loadView($viewPath, [
            'data' => $reportData,
            'generated_at' => $timestamp
        ]);
        
        return $pdf->download($fileName . '-' . now()->format('Y-m-d') . '.pdf');
    }
}
