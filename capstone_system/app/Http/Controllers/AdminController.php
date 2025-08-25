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
use App\Services\MalnutritionService;
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

        return view('admin.dashboard', compact('stats'));
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
                'barangays' => Barangay::whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get()
                    ->map(function($barangay) {
                        $patientCount = Patient::where('barangay_id', $barangay->barangay_id)->count();
                        return [
                            'id' => $barangay->barangay_id,
                            'name' => $barangay->name,
                            'lat' => (float) $barangay->latitude,
                            'lng' => (float) $barangay->longitude,
                            'patient_count' => $patientCount,
                            'activity_level' => $patientCount > 10 ? 'high' : ($patientCount > 5 ? 'medium' : 'low')
                        ];
                    })
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
        $users = User::with('role')->paginate(15);
        $roles = Role::all();
        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Show patients management
     */
    public function patients()
    {
        $patients = Patient::with(['parent', 'nutritionist', 'barangay'])->paginate(15);
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
        $request->validate([
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
        ]);

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
        try {
            $patient = Patient::with(['parent', 'nutritionist', 'barangay'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'patient' => $patient
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.'
            ], 404);
        }
    }

    /**
     * Update patient (Admin)
     */
    public function updatePatient(Request $request, $id)
    {
        try {
            $patient = Patient::findOrFail($id);

            $request->validate([
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
            ]);

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating patient: ' . $e->getMessage()
            ], 500);
        }
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
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,category_id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after:today',
        ]);

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
        $request->validate([
            'item_name' => 'required|string|max:255',
            'category_id' => 'required|exists:item_categories,category_id',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date|after:today',
        ]);

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
        try {
            $item = InventoryItem::with('category')->findOrFail($id);
            return response()->json([
                'success' => true,
                'item' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found.'
            ], 404);
        }
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
            'assessment_trends' => $this->getAssessmentTrends(),
            'inventory_by_category' => $this->getInventoryByCategory(),
            'recent_activities' => $this->getRecentActivities(),
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
        
        $report_data = [
            'total_items' => $inventory_items->count(),
            'total_value' => $inventory_items->sum(function($item) {
                return $item->quantity * $item->unit_cost;
            }),
            'low_stock_items' => $inventory_items->where('quantity', '<', 10)->values(),
            'items_by_category' => $inventory_items->groupBy('category.name')->map->count(),
            'recent_transactions' => $transactions,
            'stock_levels' => $inventory_items->map(function($item) {
                return [
                    'name' => $item->name,
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
     * Generate Assessment Trends Report
     */
    public function generateAssessmentTrendsReport()
    {
        try {
            // Load assessments with nested relationships
            $assessments = Assessment::with(['patient.barangay', 'nutritionist'])->get();
            
            // Calculate monthly trends
            $monthlyData = $assessments->groupBy(function($assessment) {
                return $assessment->created_at ? $assessment->created_at->format('Y-m') : 'Unknown';
            })->map(function($group) {
                return $group->count();
            })->sortKeys();
            
            // Calculate trends with growth rate
            $monthlyTrends = [];
            $previousCount = 0;
            foreach ($monthlyData as $month => $count) {
                $growth = $previousCount > 0 ? round((($count - $previousCount) / $previousCount) * 100, 1) : 0;
                $monthlyTrends[] = [
                    'month' => $month,
                    'count' => $count,
                    'growth' => $growth
                ];
                $previousCount = $count;
            }
            
            // Get assessments by barangay (safely)
            $assessmentsByBarangay = [];
            foreach ($assessments as $assessment) {
                if ($assessment->patient && $assessment->patient->barangay) {
                    $barangayName = $assessment->patient->barangay->name;
                    $assessmentsByBarangay[$barangayName] = ($assessmentsByBarangay[$barangayName] ?? 0) + 1;
                }
            }
            
            // Get assessments by nutritionist
            $assessmentsByNutritionist = [];
            foreach ($assessments as $assessment) {
                if ($assessment->nutritionist) {
                    $nutritionistName = $assessment->nutritionist->name;
                    $assessmentsByNutritionist[$nutritionistName] = ($assessmentsByNutritionist[$nutritionistName] ?? 0) + 1;
                }
            }
            
            $report_data = [
                'total_assessments' => $assessments->count(),
                'completed_assessments' => $assessments->whereNotNull('completed_at')->count(),
                'pending_assessments' => $assessments->whereNull('completed_at')->count(),
                'assessments_this_month' => $assessments->filter(function($assessment) {
                    return $assessment->created_at && $assessment->created_at->isCurrentMonth();
                })->count(),
                'avg_assessments_per_day' => $assessments->count() > 0 ? 
                    round($assessments->count() / max(1, now()->diffInDays($assessments->min('created_at') ?? now())), 1) : 0,
                'monthly_trends' => $monthlyTrends,
                'assessments_by_month' => $monthlyData,
                'assessments_by_barangay' => $assessmentsByBarangay,
                'assessments_by_nutritionist' => $assessmentsByNutritionist,
                'assessment_outcomes' => [
                    'completed' => $assessments->whereNotNull('completed_at')->count(),
                    'pending' => $assessments->whereNull('completed_at')->count(),
                ],
                'recent_assessments' => $assessments->sortByDesc('created_at')->take(10)->map(function($assessment) {
                    return [
                        'id' => $assessment->assessment_id,
                        'patient_name' => $assessment->patient ? 
                            $assessment->patient->first_name . ' ' . $assessment->patient->last_name : 'N/A',
                        'nutritionist_name' => $assessment->nutritionist ? $assessment->nutritionist->name : 'N/A',
                        'created_at' => $assessment->created_at ? $assessment->created_at->format('Y-m-d H:i:s') : 'N/A',
                    ];
                })->values(),
            ];

            return response()->json([
                'success' => true,
                'data' => $report_data,
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Assessment Trends Report Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating assessment trends report: ' . $e->getMessage()
            ], 500);
        }
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
     * Get assessment trends for dashboard
     */
    private function getAssessmentTrends()
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trends[$date->format('M d')] = Assessment::whereDate('created_at', $date)->count();
        }
        return $trends;
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
        try {
            $category = ItemCategory::findOrFail($id);
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    /**
     * Get barangay details for editing
     */
    public function getBarangay($id)
    {
        try {
            $barangay = Barangay::findOrFail($id);
            return response()->json([
                'success' => true,
                'barangay' => $barangay
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barangay not found.'
            ], 404);
        }
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
        try {
            $user = User::with('role')->findOrFail($id);
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }
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
     * Show inventory transactions
     */

    // ========================================
    // API MANAGEMENT METHODS
    // ========================================

    /**
     * Show API management dashboard
     */
    public function apiManagement(MalnutritionService $malnutritionService)
    {
        try {
            $apiStatus = $malnutritionService->checkApiHealth();
            
            return view('admin.api-management', compact('apiStatus'));
        } catch (\Exception $e) {
            $apiStatus = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            
            return view('admin.api-management', compact('apiStatus'));
        }
    }

    /**
     * Show WHO standards reference data
     */
    public function whoStandards(MalnutritionService $malnutritionService)
    {
        try {
            // Get sample data for both genders and indicators
            $maleWfa = $malnutritionService->getWhoStandards('male', 'wfa');
            $femaleWfa = $malnutritionService->getWhoStandards('female', 'wfa');
            $maleLhfa = $malnutritionService->getWhoStandards('male', 'lhfa');
            $femaleLhfa = $malnutritionService->getWhoStandards('female', 'lhfa');

            return view('admin.who-standards', compact(
                'maleWfa', 'femaleWfa', 'maleLhfa', 'femaleLhfa'
            ));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show treatment protocols
     */
    public function treatmentProtocols(MalnutritionService $malnutritionService)
    {
        try {
            $protocols = $malnutritionService->getTreatmentProtocols();
            return view('admin.treatment-protocols', compact('protocols'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Check API status
     */
    public function apiStatus(MalnutritionService $malnutritionService)
    {
        try {
            $status = $malnutritionService->checkApiHealth();
            return response()->json([
                'success' => true,
                'data' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download User Activity Report as PDF
     */
    public function downloadUserActivityReport(Request $request)
    {
        $reportData = json_decode($request->input('report_data'), true);
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $pdf = Pdf::loadView('admin.reports.pdf.user-activity', [
            'data' => $reportData,
            'generated_at' => $timestamp
        ]);
        
        return $pdf->download('user-activity-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download Inventory Report as PDF
     */
    public function downloadInventoryReport(Request $request)
    {
        $reportData = json_decode($request->input('report_data'), true);
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $pdf = Pdf::loadView('admin.reports.pdf.inventory', [
            'data' => $reportData,
            'generated_at' => $timestamp
        ]);
        
        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download Assessment Trends Report as PDF
     */
    public function downloadAssessmentTrendsReport(Request $request)
    {
        $reportData = json_decode($request->input('report_data'), true);
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $pdf = Pdf::loadView('admin.reports.pdf.assessment-trends', [
            'data' => $reportData,
            'generated_at' => $timestamp
        ]);
        
        return $pdf->download('assessment-trends-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Download Low Stock Report as PDF
     */
    public function downloadLowStockReport(Request $request)
    {
        $reportData = json_decode($request->input('report_data'), true);
        $timestamp = now()->format('Y-m-d H:i:s');
        
        $pdf = Pdf::loadView('admin.reports.pdf.low-stock', [
            'data' => $reportData,
            'generated_at' => $timestamp
        ]);
        
        return $pdf->download('low-stock-report-' . now()->format('Y-m-d') . '.pdf');
    }
}
