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
use App\Models\Food;
use App\Models\FoodRequest;
use App\Models\SupportTicket;
use App\Mail\AccountApprovalMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Cache dashboard stats using configured duration (default 15 minutes)
        $cacheDuration = config('dashboard.cache_duration', 900);
        
        $stats = cache()->remember('admin_dashboard_stats_v4', $cacheDuration, function () {
            // Get current month dates
            $currentMonthStart = now()->startOfMonth();
            $currentMonthEnd = now()->endOfMonth();
            $previousMonthStart = now()->subMonth()->startOfMonth();
            $previousMonthEnd = now()->subMonth()->endOfMonth();
            
            // Current counts
            $currentUsers = User::count();
            $currentPatients = Patient::active()->count();
            $currentScreenings = Assessment::count();
            $currentInventory = InventoryItem::count();
            
            // Previous month counts for percentage calculations
            $previousUsers = User::where('created_at', '<=', $previousMonthEnd)->count();
            $previousPatients = Patient::active()->where('created_at', '<=', $previousMonthEnd)->count();
            $previousScreenings = Assessment::where('created_at', '<=', $previousMonthEnd)->count();
            
            // Calculate percentage changes
            $usersChange = $previousUsers > 0 ? round((($currentUsers - $previousUsers) / $previousUsers) * 100, 1) : 0;
            $patientsChange = $previousPatients > 0 ? round((($currentPatients - $previousPatients) / $previousPatients) * 100, 1) : 0;
            $screeningsChange = $previousScreenings > 0 ? round((($currentScreenings - $previousScreenings) / $previousScreenings) * 100, 1) : 0;
            
            // Low stock items by severity
            $lowStockThreshold = config('dashboard.low_stock_threshold', 10);
            $warningThreshold = config('dashboard.stock_warning_threshold', 5);
            
            $criticalStock = InventoryItem::where('quantity', '=', 0)->get();
            $warningStock = InventoryItem::where('quantity', '>', 0)
                ->where('quantity', '<=', $warningThreshold)->get();
            $lowStock = InventoryItem::where('quantity', '>', $warningThreshold)
                ->where('quantity', '<=', $lowStockThreshold)->get();
            
            $totalLowStock = $criticalStock->count() + $warningStock->count() + $lowStock->count();
            
            // Items expiring soon
            $expiringDays = config('dashboard.expiring_soon_days', 30);
            $expiringItems = InventoryItem::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [now(), now()->addDays($expiringDays)])
                ->orderBy('expiry_date', 'asc')
                ->get();
            
            // Expired items
            $expiredItems = InventoryItem::whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->orderBy('expiry_date', 'desc')
                ->get();
            
            // Pending screenings
            $pendingScreenings = Assessment::whereNull('completed_at')->count();
            
            // Active vs inactive users
            $activeUsers = User::where('is_active', true)->count();
            $inactiveUsers = User::where('is_active', false)->count();
            $activePercentage = $currentUsers > 0 ? round(($activeUsers / $currentUsers) * 100, 1) : 0;
            
            // Nutritional status distribution - get latest assessment per patient
            // Get latest assessment ID for each patient
            $latestAssessments = DB::table('assessments')
                ->select('patient_id', DB::raw('MAX(assessment_id) as latest_assessment_id'))
                ->groupBy('patient_id')
                ->pluck('latest_assessment_id');
            
            // Count by diagnosis from treatment JSON field (stored at $.patient_info.diagnosis)
            $samCount = Assessment::whereIn('assessment_id', $latestAssessments)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%SEVERE ACUTE MALNUTRITION%'")
                ->count();
            
            $mamCount = Assessment::whereIn('assessment_id', $latestAssessments)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%MODERATE ACUTE MALNUTRITION%'")
                ->count();
            
            $normalCount = Assessment::whereIn('assessment_id', $latestAssessments)
                ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%NORMAL NUTRITIONAL STATUS%'")
                ->count();
            
            // Inventory by category
            $inventoryByCategory = InventoryItem::leftJoin('item_categories', 'inventory_items.category_id', '=', 'item_categories.category_id')
                ->select('item_categories.category_name', DB::raw('COUNT(inventory_items.item_id) as count'))
                ->groupBy('item_categories.category_id', 'item_categories.category_name')
                ->get()
                ->map(function($item) {
                    return [
                        'category' => $item->category_name ?? 'Uncategorized',
                        'count' => $item->count
                    ];
                });
            
            // Add uncategorized items if any
            $uncategorizedCount = InventoryItem::whereNull('category_id')->count();
            if ($uncategorizedCount > 0) {
                $inventoryByCategory->push([
                    'category' => 'Uncategorized',
                    'count' => $uncategorizedCount
                ]);
            }
            
            // Monthly screening trends (last 6 months)
            $screeningTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = now()->subMonths($i)->startOfMonth();
                $monthEnd = now()->subMonths($i)->endOfMonth();
                $count = Assessment::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $screeningTrends[] = [
                    'month' => $monthStart->format('M Y'),
                    'count' => $count
                ];
            }
            
            return [
                // Original stats
                'total_users' => $currentUsers,
                'total_patients' => $currentPatients,
                'total_screenings' => $currentScreenings,
                'total_inventory_items' => $currentInventory,
                'recent_audit_logs' => AuditLog::with('user')->latest()->take(10)->get(),
                
                // New percentage changes
                'users_change' => $usersChange,
                'patients_change' => $patientsChange,
                'screenings_change' => $screeningsChange,
                'inventory_change' => 0, // Inventory doesn't have created_at typically
                
                // Low stock data
                'total_low_stock' => $totalLowStock,
                'critical_stock' => $criticalStock,
                'warning_stock' => $warningStock,
                'low_stock' => $lowStock,
                'critical_count' => $criticalStock->count(),
                'warning_count' => $warningStock->count(),
                'low_count' => $lowStock->count(),
                
                // Expiring items
                'expiring_items' => $expiringItems,
                'expiring_count' => $expiringItems->count(),
                'expiring_days' => $expiringDays,
                
                // Expired items
                'expired_items' => $expiredItems,
                'expired_count' => $expiredItems->count(),
                
                // Pending screenings
                'pending_screenings' => $pendingScreenings,
                'completed_screenings' => $currentScreenings - $pendingScreenings,
                'completion_rate' => $currentScreenings > 0 ? round((($currentScreenings - $pendingScreenings) / $currentScreenings) * 100, 1) : 0,
                
                // Active users
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
                'active_percentage' => $activePercentage,
                
                // Pending nutritionist applications
                'pending_nutritionist_applications' => User::whereHas('role', function($query) {
                    $query->where('role_name', 'Nutritionist');
                })->where('is_active', false)->count(),
                
                // Support tickets (only active, non-archived)
                'unread_support_tickets' => SupportTicket::active()->where('status', 'unread')->count(),
                'urgent_support_tickets' => SupportTicket::active()->where('priority', 'urgent')->whereIn('status', ['unread', 'read'])->count(),
                
                // Chart data
                'nutritional_status' => [
                    'sam' => $samCount,
                    'mam' => $mamCount,
                    'normal' => $normalCount,
                ],
                'inventory_by_category' => $inventoryByCategory,
                'screening_trends' => $screeningTrends,
            ];
        });

        // Use the extracted method for barangay data processing
        $barangays = $this->getBarangayPatientData();

        return view('admin.dashboard', compact('stats', 'barangays'));
    }

    /**
     * Get chart data for AJAX requests
     */
    public function getChartData($type, Request $request)
    {
        try {
            switch ($type) {
                case 'screening-trends':
                    $startDate = $request->input('start_date', now()->subMonths(6)->startOfMonth());
                    $endDate = $request->input('end_date', now()->endOfMonth());
                    
                    // Parse dates
                    $start = \Carbon\Carbon::parse($startDate);
                    $end = \Carbon\Carbon::parse($endDate);
                    
                    $trends = [];
                    $current = $start->copy();
                    
                    while ($current <= $end) {
                        $monthStart = $current->copy()->startOfMonth();
                        $monthEnd = $current->copy()->endOfMonth();
                        
                        $count = Assessment::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                        
                        $trends[] = [
                            'month' => $current->format('M Y'),
                            'count' => $count
                        ];
                        
                        $current->addMonth();
                    }
                    
                    return response()->json([
                        'success' => true,
                        'data' => $trends
                    ]);
                    
                case 'nutritional-status':
                    // Get latest assessment ID for each patient
                    $latestAssessments = DB::table('assessments')
                        ->select('patient_id', DB::raw('MAX(assessment_id) as latest_assessment_id'))
                        ->groupBy('patient_id')
                        ->pluck('latest_assessment_id');
                    
                    // Count by diagnosis from treatment JSON field (stored at $.patient_info.diagnosis)
                    $samCount = Assessment::whereIn('assessment_id', $latestAssessments)
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%SEVERE ACUTE MALNUTRITION%'")
                        ->count();
                    
                    $mamCount = Assessment::whereIn('assessment_id', $latestAssessments)
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%MODERATE ACUTE MALNUTRITION%'")
                        ->count();
                    
                    $normalCount = Assessment::whereIn('assessment_id', $latestAssessments)
                        ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(treatment, '$.patient_info.diagnosis')) LIKE '%NORMAL NUTRITIONAL STATUS%'")
                        ->count();
                    
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'sam' => $samCount,
                            'mam' => $mamCount,
                            'normal' => $normalCount,
                        ]
                    ]);
                    
                case 'inventory-category':
                    $inventoryByCategory = InventoryItem::leftJoin('item_categories', 'inventory_items.category_id', '=', 'item_categories.category_id')
                        ->select('item_categories.category_name', DB::raw('COUNT(inventory_items.item_id) as count'))
                        ->groupBy('item_categories.category_id', 'item_categories.category_name')
                        ->get()
                        ->map(function($item) {
                            return [
                                'category' => $item->category_name ?? 'Uncategorized',
                                'count' => $item->count
                            ];
                        });
                    
                    // Add uncategorized items if any
                    $uncategorizedCount = InventoryItem::whereNull('category_id')->count();
                    if ($uncategorizedCount > 0) {
                        $inventoryByCategory->push([
                            'category' => 'Uncategorized',
                            'count' => $uncategorizedCount
                        ]);
                    }
                    
                    return response()->json([
                        'success' => true,
                        'data' => $inventoryByCategory
                    ]);
                    
                case 'low-stock-alerts':
                    $lowStockThreshold = config('dashboard.low_stock_threshold', 10);
                    $warningThreshold = config('dashboard.stock_warning_threshold', 5);
                    
                    $items = InventoryItem::where('quantity', '<=', $lowStockThreshold)
                        ->orderBy('quantity', 'asc')
                        ->get()
                        ->map(function($item) use ($warningThreshold) {
                            $severity = 'low';
                            if ($item->quantity == 0) {
                                $severity = 'critical';
                            } elseif ($item->quantity <= $warningThreshold) {
                                $severity = 'warning';
                            }
                            
                            return [
                                'name' => $item->item_name,
                                'quantity' => $item->quantity,
                                'severity' => $severity
                            ];
                        });
                    
                    return response()->json([
                        'success' => true,
                        'data' => $items
                    ]);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid chart type'
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading chart data: ' . $e->getMessage()
            ], 500);
        }
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
    public function users(Request $request)
    {
        $perPage = 15; // Fixed to 15 users per page
        
        // Include soft-deleted users
        $query = User::with('role')->withTrashed();
        
        if ($request->input('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('contact_number', 'like', "%$search%");
            });
        }
        
        if ($request->input('role')) {
            $query->where('role_id', $request->input('role'));
        }
        
        // Filter by account status (pending, active, suspended, rejected, deleted)
        if ($request->input('account_status')) {
            $accountStatus = $request->input('account_status');
            if ($accountStatus === 'deleted') {
                $query->whereNotNull('deleted_at');
            } elseif (in_array($accountStatus, ['pending', 'active', 'suspended', 'rejected'])) {
                // Filter by specific ENUM status and exclude deleted users
                $query->where('account_status', $accountStatus)->whereNull('deleted_at');
            }
        }
        
        // Legacy status filter support
        if ($request->input('status') !== null && $request->input('status') !== '' && !$request->input('account_status')) {
            $query->where('is_active', $request->input('status'));
        }

        // Handle sorting
        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('first_name', 'desc')->orderBy('last_name', 'desc');
                break;
            case 'email_asc':
                $query->orderBy('email', 'asc');
                break;
            case 'email_desc':
                $query->orderBy('email', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        $users = $query->paginate($perPage)->appends($request->query());
        $roles = Role::all();
        
        // Handle AJAX requests
        if ($request->ajax() || $request->input('ajax')) {
            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'total' => $users->total(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        }
        
        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Show patients management
     */
    public function patients()
    {
        // Only show active (non-archived) patients
        $patients = Patient::active()
            ->with(['parent', 'nutritionist', 'barangay', 'latestAssessment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
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
        
        // Calculate age_months from birthdate if provided
        $age_months = $request->age_months;
        if ($request->birthdate) {
            $birthdate = new \DateTime($request->birthdate);
            $today = new \DateTime();
            $interval = $birthdate->diff($today);
            $age_months = ($interval->y * 12) + $interval->m;
        }

        try {
            $patient = Patient::create([
                'parent_id' => $request->parent_id,
                'nutritionist_id' => $request->nutritionist_id,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'barangay_id' => $request->barangay_id,
                'contact_number' => $request->contact_number,
                'birthdate' => $request->birthdate,
                'age_months' => $age_months,
                'sex' => $request->sex,
                'date_of_admission' => $request->date_of_admission,
                'total_household_adults' => $request->total_household_adults ?? 0,
                'total_household_children' => $request->total_household_children ?? 0,
                'total_household_twins' => $request->total_household_twins ?? 0,
                'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
                'weight_kg' => $request->weight_kg,
                'height_cm' => $request->height_cm,
                'breastfeeding' => $request->breastfeeding,
                'allergies' => $request->allergies,
                'religion' => $request->religion,
                'other_medical_problems' => $request->other_medical_problems,
                'edema' => $request->edema,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Patient added successfully! Patient ID: ' . $patient->custom_patient_id,
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
        return $this->getRecord(Patient::class, $id, ['parent', 'nutritionist', 'barangay', 'latestAssessment']);
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
        
        $request->validate([
            'parent_id' => 'nullable|exists:users,user_id',
            'nutritionist_id' => 'nullable|exists:users,user_id',
            'barangay_id' => 'required|exists:barangays,barangay_id',
            'contact_number' => 'required|string|max:20',
            'date_of_admission' => 'required|date',
            // Note: first_name, middle_name, last_name, birthdate, sex are locked for editing
            // Note: weight_kg, height_cm, and nutritional indicators are managed via assessments
        ]);
        
        try {
            // Only update non-locked fields (household and contact info)
            $patient->update([
                'parent_id' => $request->parent_id ?: null,
                'nutritionist_id' => $request->nutritionist_id ?: null,
                'barangay_id' => $request->barangay_id,
                'contact_number' => $request->contact_number,
                'date_of_admission' => $request->date_of_admission,
                'total_household_adults' => $request->total_household_adults ?? 0,
                'total_household_children' => $request->total_household_children ?? 0,
                'total_household_twins' => $request->total_household_twins ?? 0,
                'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
                'breastfeeding' => $request->breastfeeding,
                'allergies' => $request->allergies,
                'religion' => $request->religion,
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
     * Archive a patient (Admin)
     */
    public function archivePatient($id)
    {
        try {
            $patient = Patient::findOrFail($id);

            if ($patient->isArchived()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient is already archived.'
                ], 400);
            }

            $patient->archive();

            return response()->json([
                'success' => true,
                'message' => 'Patient archived successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unarchive a patient (Admin)
     */
    public function unarchivePatient($id)
    {
        try {
            $patient = Patient::findOrFail($id);

            if (!$patient->isArchived()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient is not archived.'
                ], 400);
            }

            $patient->unarchive();

            return response()->json([
                'success' => true,
                'message' => 'Patient unarchived successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unarchiving patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get archived patients (Admin)
     */
    public function archivedPatients()
    {
        $patients = Patient::archived()
            ->with(['parent', 'nutritionist', 'barangay', 'latestAssessment'])
            ->orderBy('archived_at', 'desc')
            ->paginate(10);

        return view('admin.archived-patients', compact('patients'));
    }

    /**
     * Bulk archive eligible patients (Admin)
     */
    public function bulkArchiveEligiblePatients()
    {
        try {
            $eligiblePatients = Patient::eligibleForArchiving()->get();
            $count = $eligiblePatients->count();

            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No patients found that are eligible for archiving.'
                ], 404);
            }

            $archived = 0;
            $failed = 0;

            foreach ($eligiblePatients as $patient) {
                try {
                    $patient->archive();
                    $archived++;
                } catch (\Exception $e) {
                    Log::error("Failed to archive patient {$patient->patient_id}: {$e->getMessage()}");
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully archived {$archived} patient(s)." . ($failed > 0 ? " Failed: {$failed}" : ''),
                'archived' => $archived,
                'failed' => $failed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving patients: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patients via AJAX with archive filtering (Admin)
     */
    public function getPatientsAjax(Request $request)
    {
        try {
            $status = $request->get('status', 'active');
            
            // Build query based on status - only show patients matching the exact status
            if ($status === 'archived') {
                // Only archived patients (archived_at is NOT NULL)
                $query = Patient::archived();
            } else {
                // Only active patients (archived_at is NULL)
                $query = Patient::active();
            }

            $query->with(['parent', 'nutritionist', 'barangay', 'latestAssessment']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('custom_patient_id', 'like', "%{$search}%")
                      ->orWhere('contact_number', 'like', "%{$search}%");
                });
            }

            if ($request->filled('barangay')) {
                $query->whereHas('barangay', function($q) use ($request) {
                    $q->where('barangay_name', $request->barangay);
                });
            }

            if ($request->filled('gender')) {
                $query->where('sex', $request->gender);
            }

            if ($request->filled('age_range')) {
                $range = explode('-', $request->age_range);
                if (count($range) === 2) {
                    $query->whereBetween('age_months', [(int)$range[0], (int)$range[1]]);
                } elseif ($request->age_range === '49+') {
                    $query->where('age_months', '>=', 49);
                }
            }

            if ($request->filled('nutritionist')) {
                $query->whereHas('nutritionist', function($q) use ($request) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$request->nutritionist]);
                });
            }

            // Paginate
            $patients = $query->orderBy('created_at', 'desc')->paginate(10);

            // Format data for JSON response
            $patientsData = $patients->map(function($patient) {
                return [
                    'patient_id' => $patient->patient_id,
                    'custom_patient_id' => $patient->custom_patient_id,
                    'first_name' => $patient->first_name,
                    'middle_name' => $patient->middle_name,
                    'last_name' => $patient->last_name,
                    'age_months' => $patient->age_months,
                    'sex' => $patient->sex,
                    'contact_number' => $patient->contact_number,
                    'date_of_admission' => $patient->date_of_admission->format('M d, Y'),
                    'date_of_admission_raw' => $patient->date_of_admission->format('Y-m-d'),
                    'barangay' => $patient->barangay ? $patient->barangay->barangay_name : null,
                    'parent' => $patient->parent ? $patient->parent->first_name . ' ' . $patient->parent->last_name : null,
                    'nutritionist' => $patient->nutritionist ? $patient->nutritionist->first_name . ' ' . $patient->nutritionist->last_name : null,
                ];
            });

            return response()->json([
                'success' => true,
                'patients' => $patientsData,
                'pagination' => [
                    'total' => $patients->total(),
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'links' => $patients->links()->render()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading patients: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient assessments (Admin)
     */
    public function getPatientAssessments($id)
    {
        try {
            // Get patient
            $patient = Patient::findOrFail($id);

            // Get all assessments for this patient, ordered by date (newest first)
            $assessments = Assessment::with(['nutritionist'])
                ->where('patient_id', $id)
                ->orderBy('assessment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Format assessments data
            $assessmentsData = $assessments->map(function ($assessment) {
                // Calculate BMI if height and weight are available
                $bmi = null;
                if ($assessment->weight_kg && $assessment->height_cm) {
                    $heightInMeters = $assessment->height_cm / 100;
                    $bmi = round($assessment->weight_kg / ($heightInMeters * $heightInMeters), 2);
                }

                // Extract diagnosis/classification from treatment plan
                $classification = 'Not specified';

                if ($assessment->treatment) {
                    $treatmentData = json_decode($assessment->treatment, true);
                    if ($treatmentData) {
                        // Try to extract classification from various possible locations
                        if (isset($treatmentData['patient_info']['diagnosis'])) {
                            $classification = $treatmentData['patient_info']['diagnosis'];
                        } elseif (isset($treatmentData['diagnosis'])) {
                            $classification = $treatmentData['diagnosis'];
                        } elseif (isset($treatmentData['classification'])) {
                            $classification = $treatmentData['classification'];
                        }
                    }
                }

                return [
                    'assessment_id' => $assessment->assessment_id,
                    'assessment_date' => $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->format('F d, Y') : 'N/A',
                    'assessment_date_raw' => $assessment->assessment_date,
                    'classification' => $classification,
                    'weight_kg' => $assessment->weight_kg,
                    'height_cm' => $assessment->height_cm,
                    'bmi' => $bmi,
                    'weight_for_age' => $assessment->weight_for_age,
                    'height_for_age' => $assessment->height_for_age,
                    'bmi_for_age' => $assessment->bmi_for_age,
                    'treatment_plan' => $assessment->treatment,
                    'notes' => $assessment->notes,
                    'assessor_name' => $assessment->nutritionist ? $assessment->nutritionist->first_name . ' ' . $assessment->nutritionist->last_name : 'N/A',
                    'recovery_status' => $assessment->recovery_status,
                    'status' => $assessment->completed_at ? 'Completed' : 'Pending',
                    'created_at' => $assessment->created_at->format('M d, Y g:i A')
                ];
            });

            return response()->json([
                'success' => true,
                'patient' => [
                    'patient_id' => $patient->patient_id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'age_months' => $patient->age_months,
                    'sex' => $patient->sex
                ],
                'assessments' => $assessmentsData,
                'total' => $assessmentsData->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
                'error' => $e->getMessage()
            ], 404);
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
     * Get assessment details (Admin)
     */
    public function getAssessmentDetails($id)
    {
        try {
            // Get assessment with related data
            $assessment = Assessment::with(['patient.barangay', 'nutritionist'])
                ->where('assessment_id', $id)
                ->firstOrFail();

            // Calculate BMI if height and weight are available
            $bmi = null;
            if ($assessment->weight_kg && $assessment->height_cm) {
                $heightInMeters = $assessment->height_cm / 100;
                $bmi = round($assessment->weight_kg / ($heightInMeters * $heightInMeters), 2);
            }

            // Decode treatment plan if it exists
            $treatmentPlan = null;
            $diagnosis = 'Not specified';
            if ($assessment->treatment) {
                $treatmentData = json_decode($assessment->treatment, true);
                if ($treatmentData) {
                    $treatmentPlan = $treatmentData;
                    // Extract diagnosis from treatment plan if available
                    if (isset($treatmentData['patient_info']['diagnosis'])) {
                        $diagnosis = $treatmentData['patient_info']['diagnosis'];
                    } elseif (isset($treatmentData['diagnosis'])) {
                        $diagnosis = $treatmentData['diagnosis'];
                    } elseif (isset($treatmentData['classification'])) {
                        $diagnosis = $treatmentData['classification'];
                    }
                }
            }

            // Prepare response data
            $assessmentData = [
                'assessment_id' => $assessment->assessment_id,
                'patient_id' => $assessment->patient_id,
                'assessment_date' => $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->format('F d, Y') : 'N/A',
                'patient' => [
                    'patient_id' => $assessment->patient->patient_id,
                    'name' => $assessment->patient->first_name . ' ' . $assessment->patient->last_name,
                    'age_months' => $assessment->patient->age_months,
                    'sex' => $assessment->patient->sex,
                    'barangay' => $assessment->patient->barangay->barangay_name ?? 'Unknown'
                ],
                'measurements' => [
                    'weight_kg' => $assessment->weight_kg,
                    'height_cm' => $assessment->height_cm,
                    'bmi' => $bmi
                ],
                'weight_kg' => $assessment->weight_kg,
                'height_cm' => $assessment->height_cm,
                'bmi' => $bmi,
                'weight_for_age' => $assessment->weight_for_age,
                'height_for_age' => $assessment->height_for_age,
                'bmi_for_age' => $assessment->bmi_for_age,
                'diagnosis' => $diagnosis,
                'recovery_status' => $assessment->recovery_status,
                'treatment' => $assessment->treatment,
                'treatment_plan' => $treatmentPlan,
                'notes' => $assessment->notes,
                'assessor_name' => $assessment->nutritionist ? $assessment->nutritionist->first_name . ' ' . $assessment->nutritionist->last_name : 'N/A',
                'completed_at' => $assessment->completed_at ? $assessment->completed_at->format('M d, Y g:i A') : null,
                'status' => $assessment->completed_at ? 'Completed' : 'Pending'
            ];

            return response()->json([
                'success' => true,
                'assessment' => $assessmentData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found.',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show inventory management
     */
    public function inventory()
    {
        $items = InventoryItem::with(['category', 'inventoryTransactions'])
            ->paginate(10);
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
        // Get low stock items
        $low_stock_items = InventoryItem::with('category')
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->get();
        
        // Get expired items
        $expired_items = InventoryItem::with('category')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now())
            ->orderBy('expiry_date', 'asc')
            ->get();
        
        // Merge and remove duplicates
        $alert_items = $low_stock_items->merge($expired_items)->unique('item_id');
        
        $low_stock_items_data = $alert_items->map(function($item) {
                $isExpired = $item->expiry_date && $item->expiry_date <= now();
                $isLowStock = $item->quantity < 10;
                
                return [
                    'item_name' => $item->item_name,
                    'category_name' => $item->category ? $item->category->category_name : 'N/A',
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'expiry_date' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : null,
                    'is_expired' => $isExpired,
                    'is_low_stock' => $isLowStock,
                    'alert_type' => $isExpired && $isLowStock ? 'both' : ($isExpired ? 'expired' : 'low_stock')
                ];
            });
        
        $reports = [
            'monthly_assessments' => Assessment::whereMonth('created_at', now()->month)->count(),
            'low_stock_items' => $alert_items->count(),
            'low_stock_items_data' => $low_stock_items_data,
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
    public function generateUserActivityReport(Request $request)
    {
        // Get date range parameters
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        // Set default date range if not provided (last 30 days)
        $start = $start_date ? \Carbon\Carbon::parse($start_date)->startOfDay() : now()->subDays(30)->startOfDay();
        $end = $end_date ? \Carbon\Carbon::parse($end_date)->endOfDay() : now()->endOfDay();
        
        // Get users and assessments within date range
        $users = User::with('role')->get();
        $assessments = Assessment::with(['user.role', 'patient'])
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('nutritionist_id')
            ->get();
        
        // Filter assessments to only include those with valid users
        $assessments = $assessments->filter(function($assessment) {
            return $assessment->user !== null;
        });
        
        // Get all users who performed assessments in the date range
        $activeUsers = $assessments->pluck('nutritionist_id')->unique()->filter();
        
        // Users by role breakdown - ensure role names are properly retrieved
        $usersByRole = [];
        foreach ($users as $user) {
            if ($user->role && !empty($user->role->name)) {
                $roleName = $user->role->name;
                if (!isset($usersByRole[$roleName])) {
                    $usersByRole[$roleName] = 0;
                }
                $usersByRole[$roleName]++;
            }
        }
        
        // Assessments by user with names - ensure proper role loading
        $assessmentsByUser = [];
        $userGroups = $assessments->groupBy('nutritionist_id');
        
        foreach ($userGroups as $userId => $userAssessments) {
            // Get user directly from database to ensure role is loaded
            $user = User::with('role')->find($userId);
            if ($user) {
                $roleName = 'Nutritionist'; // Default role for users performing assessments
                if ($user->role && !empty($user->role->name)) {
                    $roleName = $user->role->name;
                }
                
                $assessmentsByUser[] = [
                    'user_name' => $user->name ?? 'Unknown User',
                    'role' => $roleName,
                    'count' => $userAssessments->count()
                ];
            }
        }
        
        // Sort by count descending
        usort($assessmentsByUser, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        // Recent assessments with full details
        $recentAssessments = $assessments->sortByDesc('created_at')->take(10)->map(function($assessment) {
            $userName = 'System User';
            $userRole = 'Nutritionist';
            
            if ($assessment->user) {
                $userName = $assessment->user->name ?? 'System User';
                if ($assessment->user->role && !empty($assessment->user->role->name)) {
                    $userRole = $assessment->user->role->name;
                }
            }
            
            return [
                'id' => $assessment->assessment_id,
                'patient_name' => $assessment->patient ? 
                    $assessment->patient->first_name . ' ' . $assessment->patient->last_name : 'Unknown Patient',
                'user_name' => $userName,
                'user_role' => $userRole,
                'date' => $assessment->created_at->format('M d, Y'),
                'recovery_status' => $assessment->recovery_status ?? 'N/A'
            ];
        })->values()->toArray();
        
        $report_data = [
            'total_users' => $users->count(),
            'active_users' => $activeUsers->count(),
            'total_assessments' => $assessments->count(),
            'users_by_role' => $usersByRole,
            'assessments_by_user' => $assessmentsByUser,
            'recent_assessments' => $recentAssessments,
            'start_display' => $start->format('M d, Y'),
            'end_display' => $end->format('M d, Y'),
            'date_range_days' => $start->diffInDays($end)
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
                    'category_name' => $item->category ? $item->category->category_name : 'Uncategorized'
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
            'items_by_category' => $inventory_items->groupBy(function($item) {
                return $item->category ? $item->category->category_name : 'Uncategorized';
            })->map->count(),
            'recent_transactions' => $transactions,
            'stock_levels' => $inventory_items->map(function($item) {
                // Calculate total usage (outgoing transactions)
                $totalUsage = InventoryTransaction::where('item_id', $item->item_id)
                    ->where('transaction_type', 'out')
                    ->sum('quantity');
                
                return [
                    'item_name' => $item->item_name,
                    'category_name' => $item->category ? $item->category->category_name : 'Uncategorized',
                    'quantity' => $item->quantity,
                    'total_usage' => $totalUsage,
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
     * Get Monthly Trends Filtered by Date Range
     */
    public function getMonthlyTrendsFiltered(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        
        // Validate dates
        if (!$start_date || !$end_date) {
            return response()->json([
                'success' => false,
                'message' => 'Start date and end date are required'
            ], 400);
        }

        try {
            $start = \Carbon\Carbon::parse($start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($end_date)->endOfDay();
            
            // Get all assessments within date range
            $allAssessments = Assessment::whereBetween('created_at', [$start, $end])
                ->get();
            
            // Get all recovered assessments within date range
            $allRecovered = Assessment::where('recovery_status', 'recovered')
                ->whereBetween('created_at', [$start, $end])
                ->get();
            
            // Build complete month range and count data
            $months = [];
            $assessment_data = [];
            $recovered_data = [];
            
            $current = clone $start;
            while ($current <= $end) {
                $month_key = $current->format('Y-m');
                $month_display = $current->format('M Y');
                
                $months[] = $month_display;
                
                // Count assessments for this month
                $assessmentCount = $allAssessments->filter(function($assessment) use ($month_key) {
                    return $assessment->created_at->format('Y-m') === $month_key;
                })->count();
                $assessment_data[] = $assessmentCount;
                
                // Count recovered assessments for this month
                $recoveredCount = $allRecovered->filter(function($assessment) use ($month_key) {
                    return $assessment->created_at->format('Y-m') === $month_key;
                })->count();
                $recovered_data[] = $recoveredCount;
                
                $current->addMonth();
            }
            
            return response()->json([
                'success' => true,
                'months' => $months,
                'assessments' => $assessment_data,
                'recovered' => $recovered_data,
                'start_display' => $start->format('M d, Y'),
                'end_display' => $end->format('M d, Y'),
                'total_months' => count($months)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Monthly trends filter error: ' . $e->getMessage(), [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing date range: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Get Malnutrition Cases Report (API)
     */
    public function getMalnutritionCasesReport()
    {
        $distribution = $this->getPatientDistribution();
        
        return response()->json([
            'success' => true,
            'data' => $distribution,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get Patient Progress Report (API)
     */
    public function getPatientProgressReport()
    {
        $progress = $this->getMonthlyProgress();
        
        return response()->json([
            'success' => true,
            'data' => $progress,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get Low Stock Alert Report (API)
     */
    public function getLowStockAlertReport()
    {
        $low_stock_items = InventoryItem::with('category')
            ->where('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->get();
        
        $expired_items = InventoryItem::with('category')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now())
            ->orderBy('expiry_date', 'asc')
            ->get();
        
        $alert_items = $low_stock_items->merge($expired_items)->unique('item_id')->map(function($item) {
            $isExpired = $item->expiry_date && $item->expiry_date <= now();
            $isLowStock = $item->quantity < 10;
            
            return [
                'item_name' => $item->item_name,
                'category_name' => $item->category ? $item->category->category_name : 'N/A',
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'expiry_date' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : null,
                'is_expired' => $isExpired,
                'is_low_stock' => $isLowStock,
                'alert_type' => $isExpired && $isLowStock ? 'both' : ($isExpired ? 'expired' : 'low_stock'),
                'priority' => $item->quantity < 5 ? 'critical' : ($item->quantity < 10 ? 'high' : 'medium')
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'alert_items' => $alert_items,
                'total_alerts' => $alert_items->count(),
                'critical_count' => $alert_items->where('priority', 'critical')->count(),
                'high_count' => $alert_items->where('priority', 'high')->count()
            ],
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get Monthly Trends Report (API)
     */
    public function getMonthlyTrendsReport()
    {
        $progress = $this->getMonthlyProgress();
        
        return response()->json([
            'success' => true,
            'data' => $progress,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get list of all patients for selection (API)
     */
    public function getPatientsList()
    {
        $patients = Patient::with(['barangay', 'assessments'])
            ->get()
            ->map(function($patient) {
                $latestAssessment = $patient->assessments()->latest()->first();
                return [
                    'id' => $patient->patient_id,
                    'custom_id' => $patient->custom_patient_id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'barangay' => $patient->barangay ? $patient->barangay->barangay_name : 'Unknown',
                    'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : null,
                    'total_assessments' => $patient->assessments->count(),
                    'last_assessment' => $latestAssessment ? $latestAssessment->created_at->format('M d, Y') : 'No assessments'
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $patients,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get Individual Patient Report (API)
     */
    public function getIndividualPatientReport($id)
    {
        $patient = Patient::with(['barangay', 'assessments' => function($query) {
            $query->orderBy('assessment_date', 'desc')->orderBy('created_at', 'desc');
        }])->find($id);
        
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }
        
        $assessments = $patient->assessments->map(function($assessment) {
            $bmi = null;
            if ($assessment->weight_kg && $assessment->height_cm) {
                $height_m = $assessment->height_cm / 100;
                $bmi = round($assessment->weight_kg / ($height_m * $height_m), 2);
            }
            
            return [
                'id' => $assessment->assessment_id,
                'date' => $assessment->assessment_date ? $assessment->assessment_date->format('M d, Y') : $assessment->created_at->format('M d, Y'),
                'weight' => $assessment->weight_kg,
                'height' => $assessment->height_cm,
                'bmi' => $bmi,
                'muac' => $assessment->muac_cm ?? null,
                'recovery_status' => $assessment->recovery_status ?? 'N/A',
                'notes' => $assessment->notes ?? ''
            ];
        });
        
        $latestAssessment = $assessments->first();
        $firstAssessment = $assessments->last();
        
        $weightChange = null;
        $bmiChange = null;
        
        if ($firstAssessment && $latestAssessment && $assessments->count() > 1) {
            if ($firstAssessment['weight'] && $latestAssessment['weight']) {
                $weightChange = round($latestAssessment['weight'] - $firstAssessment['weight'], 2);
            }
            if ($firstAssessment['bmi'] && $latestAssessment['bmi']) {
                $bmiChange = round($latestAssessment['bmi'] - $firstAssessment['bmi'], 2);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'patient' => [
                    'id' => $patient->patient_id,
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'date_of_birth' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A',
                    'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : null,
                    'sex' => $patient->sex ?? 'N/A',
                    'barangay' => $patient->barangay ? $patient->barangay->barangay_name : 'Unknown',
                    'address' => $patient->address ?? 'N/A'
                ],
                'assessments' => $assessments,
                'summary' => [
                    'total_assessments' => $assessments->count(),
                    'first_assessment_date' => $firstAssessment ? $firstAssessment['date'] : null,
                    'latest_assessment_date' => $latestAssessment ? $latestAssessment['date'] : null,
                    'weight_change' => $weightChange,
                    'bmi_change' => $bmiChange,
                    'current_status' => $latestAssessment ? $latestAssessment['recovery_status'] : 'No assessments',
                    'progress_trend' => $bmiChange > 0 ? 'improving' : ($bmiChange < 0 ? 'declining' : 'stable')
                ]
            ],
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
        
        // System Health Data
        $systemHealth = [
            'total_users' => User::count(),
            'active_users' => User::whereNull('deleted_at')->count(),
            'total_patients' => Patient::count(),
            'total_inventory_items' => InventoryItem::count(),
            'low_stock_items' => InventoryItem::where('quantity', '<', 10)->count(),
            'total_assessments' => Assessment::count(),
            'total_categories' => ItemCategory::count(),
            'total_barangays' => Barangay::count(),
            'recent_activity' => AuditLog::latest()->take(5)->get(),
            'database_size' => $this->getDatabaseSize(),
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
        ];
        
        return view('admin.system-management', compact('categories', 'barangays', 'systemHealth'));
    }
    
    /**
     * Get database size
     */
    private function getDatabaseSize()
    {
        try {
            $databaseName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [$databaseName]);
            
            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
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

        $validator = Validator::make($request->all(), [
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
        ], [
            'email.unique' => 'This email address is already in use by another user.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

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

            // Auto-verify email for staff members when activated via edit form
            $newRole = \App\Models\Role::find($request->role_id);
            $staffRoles = ['Nutritionist', 'Health Worker', 'BHW'];
            $isBeingActivated = $updateData['is_active'] && !$user->is_active;
            
            if ($newRole && in_array($newRole->role_name, $staffRoles) && $isBeingActivated) {
                $updateData['email_verified_at'] = now();
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

            // Prevent deleting users with Admin role
            if ($user->role && $user->role->role_name === 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin users cannot be deleted.'
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
    /**
     * Restore deleted user (enhanced version)
     */
    public function restoreUser($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);

            // Prevent modifying the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            DB::beginTransaction();

            $userName = "{$user->first_name} {$user->last_name}";

            // Restore deleted user
            $user->restore();

            // Reactivate the account
            $user->update([
                'is_active' => true,
                'account_status' => 'active'
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'RESTORE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Restored deleted user account: {$userName}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully.',
                'user' => $user->load('role')
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
            // Use withTrashed to include soft-deleted users
            $user = User::withTrashed()->with('role')->findOrFail($id);

            // Prevent activating/deactivating the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            // Check if user is deleted
            if ($user->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot activate a deleted user. Please restore the user first.'
                ], 400);
            }

            if ($user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already active.'
                ], 400);
            }

            DB::beginTransaction();

            // Activate account and verify email for staff members and parents
            $updateData = ['is_active' => true, 'account_status' => 'active'];
            
            // For staff roles (Nutritionist, Health Worker, BHW) and Parents, auto-verify email when activated
            $autoVerifyRoles = ['Nutritionist', 'Health Worker', 'BHW', 'Parent'];
            if ($user->role && in_array($user->role->role_name, $autoVerifyRoles)) {
                $updateData['email_verified_at'] = now();
            }
            
            $user->update($updateData);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'ACTIVATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Activated user account: {$user->first_name} {$user->last_name}" . 
                    (isset($updateData['email_verified_at']) ? ' (Email auto-verified)' : ''),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User activated successfully.',
                'user' => $user->fresh('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to activate user', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            // Use withTrashed to include soft-deleted users
            $user = User::withTrashed()->findOrFail($id);

            // Prevent activating/deactivating the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            // Check if user is deleted
            if ($user->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate a deleted user.'
                ], 400);
            }

            if (!$user->is_active || $user->account_status === 'suspended') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already inactive or suspended.'
                ], 400);
            }

            DB::beginTransaction();

            $user->update([
                'is_active' => false,
                'account_status' => 'suspended'
            ]);

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
     * Reactivate suspended user
     */
    public function reactivateUser($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            // Prevent modifying the current authenticated user
            if ($user->user_id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own account status.'
                ], 403);
            }

            DB::beginTransaction();

            // Reactivate suspended account
            $user->update([
                'is_active' => true,
                'account_status' => 'active'
            ]);

            // If soft-deleted, restore it
            if ($user->trashed()) {
                $user->restore();
            }

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'REACTIVATE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'description' => "Reactivated suspended user account: {$user->first_name} {$user->last_name}",
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User reactivated successfully.',
                'user' => $user->load('role')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate user: ' . $e->getMessage()
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

            // Activate the user account (keep their existing password)
            $user->update([
                'is_active' => true
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

            // Send account approval email to the nutritionist
            try {
                Mail::to($user->email)->send(new AccountApprovalMail($user));
            } catch (\Exception $e) {
                // Log email error but don't fail the approval
                Log::error('Failed to send approval email to nutritionist: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Nutritionist application approved successfully. An email notification has been sent to the nutritionist.'
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
        $patients = Patient::with(['assessments', 'barangay'])->get();
        $distribution = [
            'normal' => ['count' => 0, 'percentage' => 0, 'patients' => []],
            'underweight' => ['count' => 0, 'percentage' => 0, 'patients' => []],
            'malnourished' => ['count' => 0, 'percentage' => 0, 'patients' => []],
            'severe_malnourishment' => ['count' => 0, 'percentage' => 0, 'patients' => []],
        ];
        
        $barangay_breakdown = [];

        foreach ($patients as $patient) {
            // Get the latest assessment for each patient
            $latestAssessment = $patient->assessments()->latest()->first();
            $category = 'normal';
            $bmi = null;
            
            if ($latestAssessment) {
                // Calculate BMI if we have weight and height
                if ($latestAssessment->weight_kg && $latestAssessment->height_cm) {
                    $height_m = $latestAssessment->height_cm / 100;
                    $bmi = round($latestAssessment->weight_kg / ($height_m * $height_m), 2);
                    
                    // Classify based on BMI (WHO standards)
                    if ($bmi < 16) {
                        $category = 'severe_malnourishment';
                    } elseif ($bmi < 17) {
                        $category = 'underweight';
                    } elseif ($bmi < 18.5) {
                        $category = 'malnourished';
                    } else {
                        $category = 'normal';
                    }
                } else {
                    // If no weight/height data, check recovery status
                    if ($latestAssessment->recovery_status === 'severe') {
                        $category = 'severe_malnourishment';
                    } elseif ($latestAssessment->recovery_status === 'moderate') {
                        $category = 'malnourished';
                    } elseif ($latestAssessment->recovery_status === 'mild') {
                        $category = 'underweight';
                    } else {
                        $category = 'normal';
                    }
                }
            } else {
                // Patient with no assessments - classify based on initial data
                if ($patient->weight_kg && $patient->height_cm) {
                    $height_m = $patient->height_cm / 100;
                    $bmi = round($patient->weight_kg / ($height_m * $height_m), 2);
                    
                    if ($bmi < 16) {
                        $category = 'severe_malnourishment';
                    } elseif ($bmi < 17) {
                        $category = 'underweight';
                    } elseif ($bmi < 18.5) {
                        $category = 'malnourished';
                    } else {
                        $category = 'normal';
                    }
                }
            }
            
            $distribution[$category]['count']++;
            
            // Add patient details for at-risk categories
            if ($category !== 'normal') {
                $barangayName = $patient->barangay ? $patient->barangay->barangay_name : 'Unknown';
                
                $distribution[$category]['patients'][] = [
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'barangay' => $barangayName,
                    'bmi' => $bmi,
                    'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : null,
                    'last_assessment' => $latestAssessment ? $latestAssessment->created_at->format('M d, Y') : 'No assessment'
                ];
                
                // Track barangay breakdown
                if (!isset($barangay_breakdown[$barangayName])) {
                    $barangay_breakdown[$barangayName] = [
                        'severe' => 0,
                        'malnourished' => 0,
                        'underweight' => 0,
                        'total' => 0
                    ];
                }
                
                if ($category === 'severe_malnourishment') {
                    $barangay_breakdown[$barangayName]['severe']++;
                } elseif ($category === 'malnourished') {
                    $barangay_breakdown[$barangayName]['malnourished']++;
                } elseif ($category === 'underweight') {
                    $barangay_breakdown[$barangayName]['underweight']++;
                }
                $barangay_breakdown[$barangayName]['total']++;
            }
        }

        // Calculate percentages
        $total = $distribution['normal']['count'] + $distribution['underweight']['count'] + 
                 $distribution['malnourished']['count'] + $distribution['severe_malnourishment']['count'];
        
        if ($total > 0) {
            foreach ($distribution as $key => $data) {
                $distribution[$key]['percentage'] = round(($data['count'] / $total) * 100, 1);
            }
        }
        
        // Sort barangays by total at-risk cases
        uasort($barangay_breakdown, function($a, $b) {
            return $b['total'] - $a['total'];
        });
        
        $distribution['barangay_breakdown'] = $barangay_breakdown;

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
        
        // Get detailed patient progress data
        $patients = Patient::with(['barangay', 'assessments' => function($query) {
            $query->orderBy('assessment_date', 'desc')->limit(10);
        }])->get();
        
        $patientProgress = [];
        $barangayProgress = [];
        
        foreach ($patients as $patient) {
            if ($patient->assessments->count() > 0) {
                $latestAssessment = $patient->assessments->first();
                $firstAssessment = $patient->assessments->last();
                
                // Calculate BMI change
                $initialBmi = null;
                $currentBmi = null;
                
                if ($firstAssessment->weight_kg && $firstAssessment->height_cm) {
                    $height_m = $firstAssessment->height_cm / 100;
                    $initialBmi = round($firstAssessment->weight_kg / ($height_m * $height_m), 2);
                }
                
                if ($latestAssessment->weight_kg && $latestAssessment->height_cm) {
                    $height_m = $latestAssessment->height_cm / 100;
                    $currentBmi = round($latestAssessment->weight_kg / ($height_m * $height_m), 2);
                }
                
                $bmiChange = ($initialBmi && $currentBmi) ? round($currentBmi - $initialBmi, 2) : null;
                $weightChange = ($firstAssessment->weight_kg && $latestAssessment->weight_kg) 
                    ? round($latestAssessment->weight_kg - $firstAssessment->weight_kg, 2) 
                    : null;
                
                $barangayName = $patient->barangay ? $patient->barangay->barangay_name : 'Unknown';
                
                $patientData = [
                    'name' => $patient->first_name . ' ' . $patient->last_name,
                    'barangay' => $barangayName,
                    'age' => $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : null,
                    'total_assessments' => $patient->assessments->count(),
                    'initial_weight' => $firstAssessment->weight_kg,
                    'current_weight' => $latestAssessment->weight_kg,
                    'weight_change' => $weightChange,
                    'initial_bmi' => $initialBmi,
                    'current_bmi' => $currentBmi,
                    'bmi_change' => $bmiChange,
                    'recovery_status' => $latestAssessment->recovery_status,
                    'last_assessment_date' => $latestAssessment->assessment_date ? $latestAssessment->assessment_date->format('M d, Y') : $latestAssessment->created_at->format('M d, Y'),
                    'first_assessment_date' => $firstAssessment->assessment_date ? $firstAssessment->assessment_date->format('M d, Y') : $firstAssessment->created_at->format('M d, Y'),
                    'progress_trend' => $bmiChange > 0 ? 'improving' : ($bmiChange < 0 ? 'declining' : 'stable')
                ];
                
                $patientProgress[] = $patientData;
                
                // Group by barangay
                if (!isset($barangayProgress[$barangayName])) {
                    $barangayProgress[$barangayName] = [
                        'total_patients' => 0,
                        'improving' => 0,
                        'stable' => 0,
                        'declining' => 0,
                        'recovered' => 0
                    ];
                }
                
                $barangayProgress[$barangayName]['total_patients']++;
                
                if ($latestAssessment->recovery_status === 'recovered') {
                    $barangayProgress[$barangayName]['recovered']++;
                } elseif ($bmiChange > 0.5) {
                    $barangayProgress[$barangayName]['improving']++;
                } elseif ($bmiChange < -0.5) {
                    $barangayProgress[$barangayName]['declining']++;
                } else {
                    $barangayProgress[$barangayName]['stable']++;
                }
            }
        }
        
        // Sort patients by BMI change (most improvement first)
        usort($patientProgress, function($a, $b) {
            if ($a['bmi_change'] === null) return 1;
            if ($b['bmi_change'] === null) return -1;
            return $b['bmi_change'] <=> $a['bmi_change'];
        });

        return [
            'months' => $months,
            'assessments' => $assessmentCounts,
            'recovered' => $recoveredCounts,
            'total_assessments' => array_sum($assessmentCounts),
            'total_recovered' => array_sum($recoveredCounts),
            'patient_progress' => $patientProgress,
            'barangay_progress' => $barangayProgress,
            'barangays' => array_keys($barangayProgress)
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
                        
                        // Try patient_info.diagnosis first (new format)
                        if (isset($treatment['patient_info']['diagnosis'])) {
                            $diagnosis = $treatment['patient_info']['diagnosis'];
                        }
                        // Fallback to diagnosis (old format)
                        elseif (isset($treatment['diagnosis'])) {
                            $diagnosis = $treatment['diagnosis'];
                        }
                    }
                    
                    if ($diagnosis) {
                        // Match the exact strings from the database
                        if (stripos($diagnosis, 'SEVERE ACUTE MALNUTRITION') !== false) {
                            $sam++;
                        } elseif (stripos($diagnosis, 'MODERATE ACUTE MALNUTRITION') !== false) {
                            $mam++;
                        } elseif (stripos($diagnosis, 'NORMAL NUTRITIONAL STATUS') !== false) {
                            $normal++;
                        } else {
                            $unknown++;
                        }
                    } else {
                        $unknown++;
                    }
                } else {
                    // Patient has no assessment - count as unknown
                    $unknown++;
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
            'birthdate' => 'required|date|before:today',
            'age_months' => 'nullable|integer|min:0',
            'sex' => 'required|in:Male,Female',
            'date_of_admission' => 'required|date',
            'weight_kg' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
            'allergies' => 'nullable|string|max:500',
            'religion' => 'nullable|string|max:100',
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

    /**
     * Show admin profile page
     */
    public function profile()
    {
        $admin = Auth::user();
        return view('admin.profile', compact('admin'));
    }

    /**
     * Update admin profile information
     */
    public function updateProfile(Request $request)
    {
        try {
            $admin = User::findOrFail(Auth::id());

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->user_id, 'user_id')],
                'contact_number' => 'nullable|string|max:20',
                'sex' => 'nullable|in:male,female,other',
            ]);

            $admin->update($validated);

            // Log the profile update
            AuditLog::create([
                'user_id' => $admin->user_id,
                'action' => 'update',
                'table_name' => 'users',
                'record_id' => $admin->user_id,
                'description' => 'Admin updated their profile information',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
            }

            return redirect()->route('admin.profile')->with('success', 'Profile updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Update admin password
     */
    public function updatePassword(Request $request)
    {
        try {
            $admin = User::findOrFail(Auth::id());

            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Verify current password
            if (!Hash::check($validated['current_password'], $admin->password)) {
                if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect.'
                    ], 422);
                }
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            // Update password
            $admin->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            // Log the password change
            AuditLog::create([
                'user_id' => $admin->user_id,
                'action' => 'update',
                'table_name' => 'users',
                'record_id' => $admin->user_id,
                'description' => 'Admin changed their password',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully!'
                ]);
            }

            return redirect()->route('admin.profile')->with('success', 'Password updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }
    
    /**
     * Display support tickets
     */
    public function supportTickets(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $search = $request->get('search');
        $priority = $request->get('priority');
        $status = $request->get('status');
        $category = $request->get('category');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        $query = SupportTicket::query();
        
        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('reporter_email', 'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        // Apply priority filter
        if ($priority) {
            $query->where('priority', $priority);
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }
        
        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        // Apply filters
        switch ($filter) {
            case 'unread':
                $query->active()->where('status', 'unread');
                break;
            case 'urgent':
                $query->active()->where('priority', 'urgent')->where('status', '!=', 'resolved');
                break;
            case 'resolved':
                $query->active()->where('status', 'resolved');
                break;
            case 'archived':
                $query->archived();
                break;
            default:
                // Show only active (non-archived, non-resolved) tickets by default
                $query->active()->where('status', '!=', 'resolved');
                break;
        }
        
        // Optimize query with indexes and latest ordering
        $tickets = $query->select([
                'ticket_id', 
                'ticket_number', 
                'reporter_email', 
                'category', 
                'subject', 
                'status', 
                'priority', 
                'created_at',
                'archived_at'
            ])
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString(); // Preserve all query parameters in pagination
        
        // Get stats (optimized with single queries)
        $stats = [
            'total' => SupportTicket::active()->where('status', '!=', 'resolved')->count(),
            'unread' => SupportTicket::active()->where('status', 'unread')->count(),
            'urgent' => SupportTicket::active()->where('priority', 'urgent')->where('status', '!=', 'resolved')->count(),
            'resolved' => SupportTicket::active()->where('status', 'resolved')->count(),
            'archived' => SupportTicket::archived()->count(),
        ];
        
        // Get unique categories for filter dropdown
        $categories = SupportTicket::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
        
        // Handle AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'tickets' => $tickets->map(function($ticket) {
                    return [
                        'ticket_id' => $ticket->ticket_id,
                        'ticket_number' => $ticket->ticket_number,
                        'reporter_email' => $ticket->reporter_email,
                        'category' => $ticket->category,
                        'category_name' => ucwords(str_replace('_', ' ', $ticket->category)),
                        'subject' => $ticket->subject,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'created_at' => $ticket->created_at->toISOString(),
                        'archived_at' => $ticket->archived_at,
                    ];
                }),
                'stats' => $stats,
                'pagination' => [
                    'total' => $tickets->total(),
                    'from' => $tickets->firstItem(),
                    'to' => $tickets->lastItem(),
                    'current_page' => $tickets->currentPage(),
                    'last_page' => $tickets->lastPage(),
                    'per_page' => $tickets->perPage(),
                    'links' => $tickets->links()->toHtml(),
                ]
            ]);
        }
        
        return view('admin.support-tickets', compact('tickets', 'stats', 'search', 'categories'));
    }
    
    /**
     * View single support ticket and mark as read
     */
    public function viewSupportTicket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        // Mark as read if first time viewing
        if (!$ticket->read_at) {
            $ticket->update([
                'read_at' => now(),
                'status' => 'read'
            ]);
        }
        
        return response()->json($ticket);
    }
    
    /**
     * Update admin notes for a ticket
     */
    public function updateTicketNotes(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $ticket->update([
            'admin_notes' => $request->input('admin_notes')
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Mark ticket as resolved
     */
    public function resolveTicket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $ticket->update([
            'status' => 'resolved'
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Archive support ticket
     */
    public function archiveSupportTicket($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $ticket->update([
            'archived_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Unarchive support ticket
     */
    public function unarchiveSupportTicket($id)
    {
        $ticket = SupportTicket::withoutGlobalScopes()->findOrFail($id);
        
        $ticket->update([
            'archived_at' => null
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Permanently delete support ticket (only for archived tickets)
     */
    public function permanentDeleteSupportTicket($id)
    {
        $ticket = SupportTicket::withoutGlobalScopes()->findOrFail($id);
        
        // Only allow permanent deletion of archived tickets
        if (!$ticket->archived_at) {
            return response()->json([
                'success' => false,
                'message' => 'Only archived tickets can be permanently deleted'
            ], 403);
        }
        
        // Permanently delete the ticket
        $ticket->forceDelete();
        
        return response()->json(['success' => true]);
    }
}