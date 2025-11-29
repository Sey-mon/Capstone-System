<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use App\Models\Barangay;
use App\Models\Food;
use App\Models\FeedingProgramPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;

class NutritionistController extends Controller
{
    /**
     * Show nutritionist dashboard
     */
    public function dashboard()
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        // Basic stats
        $stats = [
            'my_patients' => Patient::where('nutritionist_id', $nutritionistId)->count(),
            'pending_assessments' => Assessment::where('nutritionist_id', $nutritionistId)
                ->whereNull('completed_at')
                ->count(),
            'completed_assessments' => Assessment::where('nutritionist_id', $nutritionistId)
                ->whereNotNull('completed_at')
                ->count(),
            'recent_patients' => Patient::where('nutritionist_id', $nutritionistId)
                ->with('parent')
                ->latest()
                ->take(5)
                ->get(),
            'recent_assessments' => Assessment::where('nutritionist_id', $nutritionistId)
                ->with('patient')
                ->latest()
                ->take(5)
                ->get(),
        ];

        // Chart Data: Patient Gender Distribution
        $genderStats = Patient::where('nutritionist_id', $nutritionistId)
            ->select('sex', DB::raw('count(*) as count'))
            ->groupBy('sex')
            ->get();
        
        // Chart Data: Age Distribution (in years)
        $ageGroups = Patient::where('nutritionist_id', $nutritionistId)
            ->select(DB::raw('FLOOR(age_months/12) as age_years'), DB::raw('count(*) as count'))
            ->groupBy('age_years')
            ->orderBy('age_years')
            ->get();

        // Chart Data: Monthly Assessment Trends (Last 6 months)
        $monthlyAssessments = Assessment::where('nutritionist_id', $nutritionistId)
            ->where('assessment_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(assessment_date, "%Y-%m") as month'),
                DB::raw('count(*) as count'),
                DB::raw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(CASE WHEN completed_at IS NULL THEN 1 ELSE 0 END) as pending')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Chart Data: Recovery Status Distribution
        $recoveryStats = Assessment::where('nutritionist_id', $nutritionistId)
            ->whereNotNull('recovery_status')
            ->select('recovery_status', DB::raw('count(*) as count'))
            ->groupBy('recovery_status')
            ->get();

        // Chart Data: Nutritional Status Distribution (BMI for Age)
        $nutritionalStatus = Patient::where('nutritionist_id', $nutritionistId)
            ->whereNotNull('bmi_for_age')
            ->select('bmi_for_age', DB::raw('count(*) as count'))
            ->groupBy('bmi_for_age')
            ->get();

        // Quick Stats for Cards
        $stats['total_assessments_this_month'] = Assessment::where('nutritionist_id', $nutritionistId)
            ->whereMonth('assessment_date', now()->month)
            ->whereYear('assessment_date', now()->year)
            ->count();

        $stats['active_cases'] = Patient::where('nutritionist_id', $nutritionistId)
            ->whereHas('assessments', function($query) {
                $query->whereNull('completed_at');
            })
            ->count();

        return view('nutritionist.dashboard', compact(
            'stats', 
            'genderStats', 
            'ageGroups', 
            'monthlyAssessments', 
            'recoveryStats',
            'nutritionalStatus'
        ));
    }

    /**
     * Show patients assigned to this nutritionist
     */
    public function patients(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $query = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['parent', 'barangay', 'assessments']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filter by barangay
        if ($request->has('barangay') && !empty($request->barangay)) {
            $query->where('barangay_id', $request->barangay);
        }

        // Filter by sex
        if ($request->has('sex') && !empty($request->sex)) {
            $query->where('sex', $request->sex);
        }

        // Filter by age range
        if ($request->has('age_min') && !empty($request->age_min)) {
            $query->where('age_months', '>=', $request->age_min);
        }
        if ($request->has('age_max') && !empty($request->age_max)) {
            $query->where('age_months', '<=', $request->age_max);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'first_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        switch ($sortBy) {
            case 'name':
                $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder);
                break;
            case 'age':
                $query->orderBy('age_months', $sortOrder);
                break;
            case 'date_admitted':
                $query->orderBy('date_of_admission', $sortOrder);
                break;
            case 'barangay':
                $query->join('barangays', 'patients.barangay_id', '=', 'barangays.barangay_id')
                      ->orderBy('barangays.barangay_name', $sortOrder)
                      ->select('patients.*');
                break;
            default:
                $query->orderBy('first_name', $sortOrder);
        }

        // Pagination with filters
        $perPage = $request->get('per_page', 15);
        $patients = $query->paginate($perPage)->appends($request->query());
        
        $barangays = Barangay::all();
        $parents = User::where('role_id', function($query) {
            $query->select('role_id')->from('roles')->where('role_name', 'Parent');
        })->get();
        
        // Get all nutritionists for admin view
        $nutritionists = User::where('role_id', function($query) {
            $query->select('role_id')->from('roles')->where('role_name', 'Nutritionist');
        })->get();

        // If it's an AJAX request, return filtered data
        if ($request->ajax()) {
            // If requesting for assessment modal, return JSON
            if ($request->has('ajax') && $request->ajax == '1') {
                $allPatients = $query->get(['patient_id', 'first_name', 'last_name', 'age_months', 'sex', 'barangay_id']);
                $patientsWithBarangay = $allPatients->map(function($patient) {
                    return [
                        'patient_id' => $patient->patient_id,
                        'first_name' => $patient->first_name,
                        'last_name' => $patient->last_name,
                        'age_months' => $patient->age_months,
                        'sex' => $patient->sex,
                        'barangay' => $patient->barangay ? [
                            'barangay_id' => $patient->barangay->barangay_id,
                            'barangay_name' => $patient->barangay->barangay_name
                        ] : null
                    ];
                });
                
                return response()->json(['patients' => $patientsWithBarangay]);
            }
            
            return view('nutritionist.partials.patients-table', compact('patients'));
        }
        
        return view('nutritionist.patients', compact('patients', 'barangays', 'parents', 'nutritionists'));
    }    /**
     * Calculate nutritional indicators (Weight for Age, Height for Age, BMI for Age)
     */
    public function calculateNutritionalIndicators(Request $request)
    {
        $request->validate([
            'age_months' => 'required|integer|min:0|max:60',
            'weight_kg' => 'required|numeric|min:0',
            'height_cm' => 'required|numeric|min:0',
            'sex' => 'required|in:Male,Female,male,female',
        ]);

        try {
            $malnutritionService = app(\App\Services\MalnutritionService::class);
            
            // Prepare child data for quick assessment
            $childData = [
                'age_months' => $request->age_months,
                'weight_kg' => $request->weight_kg,
                'height_cm' => $request->height_cm,
                'gender' => strtolower($request->sex),
            ];

            // Perform quick malnutrition assessment to get indicators
            $result = $malnutritionService->assessMalnutritionOnly($childData);
            
            // Log the full response for debugging
            \Illuminate\Support\Facades\Log::info('API Response for nutritional indicators:', ['result' => $result]);
            
            // Extract classification from the new /calculate/all-indices endpoint response
            // The new endpoint returns objects with 'classification' property for each indicator
            $extractClassification = function($indicator) {
                if (is_array($indicator) && isset($indicator['classification'])) {
                    return $indicator['classification'];
                } elseif (is_array($indicator) && isset($indicator['status'])) {
                    return $indicator['status'];
                } elseif (is_string($indicator)) {
                    return $indicator;
                }
                return 'Unknown';
            };
            
            $indicators = [
                'weight_for_age' => $extractClassification($result['weight_for_age'] ?? null),
                'height_for_age' => $extractClassification($result['height_for_age'] ?? null),
                'bmi_for_age' => $extractClassification($result['bmi'] ?? $result['bmi_for_age'] ?? null),
            ];

            return response()->json([
                'success' => true,
                'indicators' => $indicators,
                'debug' => $result // Include full response for debugging
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Nutritional indicators calculation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate nutritional indicators. Please enter them manually.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new patient
     */
    public function storePatient(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        $request->validate([
            'parent_id' => 'nullable|exists:users,user_id',
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
                'parent_id' => $request->parent_id ?: null,
                'nutritionist_id' => $nutritionistId,
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
                'patient' => $patient->load(['parent', 'barangay'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient data for editing
     */
    public function getPatient($id)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $patient = Patient::where('nutritionist_id', $nutritionistId)
            ->where('patient_id', $id)
            ->with(['parent', 'barangay'])
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found or you do not have permission to access this patient.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'patient' => $patient
        ]);
    }

    /**
     * Update patient
     */
    public function updatePatient(Request $request, $id)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $patient = Patient::where('nutritionist_id', $nutritionistId)
            ->where('patient_id', $id)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found or you do not have permission to access this patient.'
            ], 404);
        }

        $request->validate([
            'parent_id' => 'nullable|exists:users,user_id',
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
            $patient->update([
                'parent_id' => $request->parent_id ?: null,
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
                'patient' => $patient->load(['parent', 'barangay'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete patient
     */
    public function deletePatient($id)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $patient = Patient::where('nutritionist_id', $nutritionistId)
            ->where('patient_id', $id)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found or you do not have permission to access this patient.'
            ], 404);
        }

        try {
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
     * Show assessments
     */
    public function assessments(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        // Get all patients assigned to this nutritionist with their latest assessment
        $query = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['barangay', 'parent'])
            ->with(['assessments' => function($q) {
                $q->latest('assessment_date')->limit(1);
            }]);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhereHas('assessments', function($subQ) use ($search) {
                      $subQ->where('treatment', 'like', "%{$search}%")
                           ->orWhere('assessment_id', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'completed') {
                $query->whereHas('assessments', function($q) {
                    $q->whereNotNull('completed_at');
                });
            } elseif ($request->status === 'pending') {
                $query->where(function($q) {
                    $q->whereDoesntHave('assessments')
                      ->orWhereHas('assessments', function($subQ) {
                          $subQ->whereNull('completed_at');
                      });
                });
            }
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereHas('assessments', function($q) use ($request) {
                $q->whereDate('assessment_date', '>=', $request->date_from);
            });
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereHas('assessments', function($q) use ($request) {
                $q->whereDate('assessment_date', '<=', $request->date_to);
            });
        }

        // Diagnosis filter
        if ($request->has('diagnosis') && !empty($request->diagnosis)) {
            $query->whereHas('assessments', function($q) use ($request) {
                $q->where('treatment', 'like', "%{$request->diagnosis}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'first_name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validate sort order
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
        
        switch ($sortBy) {
            case 'assessment_date':
                // Join with the latest assessment for sorting
                $query->leftJoin('assessments as latest_assessment', function($join) {
                    $join->on('patients.patient_id', '=', 'latest_assessment.patient_id')
                         ->whereRaw('latest_assessment.assessment_id = (SELECT MAX(assessment_id) FROM assessments WHERE assessments.patient_id = patients.patient_id)');
                })
                ->select('patients.*', 'latest_assessment.assessment_date')
                ->orderBy('latest_assessment.assessment_date', $sortOrder)
                ->orderBy('patients.first_name', 'asc'); // Secondary sort by name
                break;
            case 'treatment':
                // Sort by diagnosis in the treatment JSON field
                $query->leftJoin('assessments as latest_assessment', function($join) {
                    $join->on('patients.patient_id', '=', 'latest_assessment.patient_id')
                         ->whereRaw('latest_assessment.assessment_id = (SELECT MAX(assessment_id) FROM assessments WHERE assessments.patient_id = patients.patient_id)');
                })
                ->select('patients.*', 'latest_assessment.treatment')
                ->orderBy('latest_assessment.treatment', $sortOrder)
                ->orderBy('patients.first_name', 'asc'); // Secondary sort by name
                break;
            case 'patient_id':
                $query->orderBy('patient_id', $sortOrder);
                break;
            case 'first_name':
            default:
                $query->orderBy('first_name', $sortOrder)
                      ->orderBy('last_name', $sortOrder);
                break;
        }

        // Pagination with validation
        $perPage = min(max($request->get('per_page', 15), 10), 100); // Limit between 10 and 100
        $patients = $query->paginate($perPage);
        
        // Append current query parameters to pagination links
        $patients->appends($request->query());

        // If it's an AJAX request, return JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('nutritionist.partials.assessments-table', compact('patients'))->render(),
                'pagination' => [
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'total' => $patients->total(),
                    'from' => $patients->firstItem(),
                    'to' => $patients->lastItem(),
                ]
            ]);
        }

        // For backward compatibility, also pass as 'assessments' but it's actually patients with their latest assessments
        $assessments = $patients;
        return view('nutritionist.assessments', compact('patients', 'assessments'));
    }

    /**
     * Show nutritionist profile
     */
    public function profile()
    {
        $nutritionist = Auth::user();
        return view('nutritionist.profile', compact('nutritionist'));
    }

    /**
     * Update nutritionist personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $nutritionist = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'sex' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
        ]);

        try {
            User::where('user_id', $nutritionist->user_id)->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'contact_number' => $request->contact_number,
                'birth_date' => $request->birth_date,
                'sex' => $request->sex,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Personal information updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating personal information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update nutritionist professional information
     */
    public function updateProfessionalInfo(Request $request)
    {
        $nutritionist = Auth::user();
        
        $request->validate([
            'years_experience' => 'nullable|integer|min:0|max:50',
            'qualifications' => 'nullable|string|max:2000',
            'professional_experience' => 'nullable|string|max:2000',
        ]);

        try {
            User::where('user_id', $nutritionist->user_id)->update([
                'years_experience' => $request->years_experience,
                'qualifications' => $request->qualifications,
                'professional_experience' => $request->professional_experience,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Professional information updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating professional information: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $nutritionist = Auth::user();
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string|same:new_password',
        ]);

        try {
            // Verify current password
            if (!Hash::check($request->current_password, $nutritionist->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            // Update password
            User::where('user_id', $nutritionist->user_id)->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // MALNUTRITION ASSESSMENT METHODS
    // ========================================

    /**
     * Show patient selection for creating new assessment
     */
    public function createAssessment()
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        $patients = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['parent', 'barangay'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
            
        return view('nutritionist.assessment-create', compact('patients'));
    }

    /**
     * Show assessment form for patient
     */
    public function showAssessmentForm($patientId)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $patient = Patient::where('patient_id', $patientId)
            ->where('nutritionist_id', $nutritionistId)
            ->with(['parent', 'barangay'])
            ->firstOrFail();

        // If it's an AJAX request, return only the form content
        if (request()->ajax()) {
            return view('nutritionist.partials.assessment-form-content', compact('patient'))->render();
        }

        return view('nutritionist.assessment-form', compact('patient'));
    }



    /**
     * Get assessment details for viewing
     */
    public function getAssessmentDetails($assessmentId)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        try {
            // Get assessment with related data
            $assessment = Assessment::with(['patient.barangay', 'nutritionist'])
                ->where('assessment_id', $assessmentId)
                ->where('nutritionist_id', $nutritionistId)
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
                    }
                }
            }

            // Prepare response data
            $assessmentData = [
                'assessment_id' => $assessment->assessment_id,
                'assessment_date' => $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->format('M d, Y') : 'N/A',
                'patient' => [
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
                'diagnosis' => $diagnosis,
                'recovery_status' => $assessment->recovery_status,
                'treatment' => $assessment->treatment,
                'treatment_plan' => $treatmentPlan,
                'notes' => $assessment->notes,
                'nutritionist' => $assessment->nutritionist->first_name . ' ' . $assessment->nutritionist->last_name,
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
                'message' => 'Assessment not found or access denied.'
            ], 404);
        }
    }

    /**
     * Generate PDF report for assessment
     */
    public function downloadAssessmentPDF($assessmentId)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        // Get assessment with patient data
        $assessment = Assessment::with('patient')
            ->where('assessment_id', $assessmentId)
            ->where('nutritionist_id', $nutritionistId)
            ->firstOrFail();

        $patient = $assessment->patient;
        
        // Decode treatment plan (from 'treatment' column)
        $treatmentPlan = json_decode($assessment->treatment, true);
        
        // Prepare data for PDF
        $data = [
            'assessment' => $assessment,
            'patient' => $patient,
            'treatmentPlan' => $treatmentPlan,
            'nutritionist' => $nutritionist
        ];

        // Generate PDF with enhanced settings
        $pdf = Pdf::loadView('nutritionist.assessment-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        // Optional: Set additional options for better rendering
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
            'dpi' => 150,
            'isFontSubsettingEnabled' => true
        ]);
        
        // Generate filename with proper formatting
        $filename = 'Nutritional_Assessment_' . 
                    str_replace(' ', '_', $patient->first_name . '_' . $patient->last_name) . 
                    '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show meal plans page
     */
    public function mealPlans()
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        // Get patients assigned to this nutritionist
        $patients = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['parent', 'barangay'])
            ->get();

        return view('nutritionist.meal-plans', compact('patients'));
    }

    /**
     * View foods database (Read-only for nutritionists)
     */
    public function viewFoods(Request $request)
    {
        $search = $request->input('search');
        $tag = $request->input('tag');
        $perPage = $request->input('per_page', 15);
        $view = $request->input('view', 'foods');
        $status = $request->input('status');
        $user = Auth::user();

        $foods = Food::query()
            ->search($search)
            ->withTag($tag)
            ->orderBy('food_name_and_description', 'asc')
            ->paginate($perPage);

        // Get unique tags for filter
        $allTags = Food::whereNotNull('nutrition_tags')
            ->where('nutrition_tags', '!=', '')
            ->pluck('nutrition_tags')
            ->flatMap(function ($tags) {
                return array_map('trim', explode(',', $tags));
            })
            ->unique()
            ->sort()
            ->values();

        // Get food requests data
        $requestsQuery = \App\Models\FoodRequest::with(['requester', 'reviewer'])
            ->where('requested_by', $user->user_id);

        if ($status) {
            $requestsQuery->where('status', $status);
        }

        $requests = $requestsQuery->orderBy('created_at', 'desc')->paginate(10);

        // Calculate stats
        $stats = [
            'pending' => \App\Models\FoodRequest::pending()->where('requested_by', $user->user_id)->count(),
            'approved' => \App\Models\FoodRequest::approved()->where('requested_by', $user->user_id)->count(),
            'rejected' => \App\Models\FoodRequest::rejected()->where('requested_by', $user->user_id)->count(),
        ];

        return view('nutritionist.foods', compact('foods', 'allTags', 'search', 'tag', 'requests', 'stats', 'status'));
    }

    /**
     * Search foods (AJAX)
     */
    public function searchFoods(Request $request)
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
     * Show reports for nutritionist
     */
    public function reports()
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        // Get nutritionist's patients
        $myPatients = Patient::where('nutritionist_id', $nutritionistId)->get();
        $patientIds = $myPatients->pluck('patient_id');

        // Report statistics
        $reports = [
            'total_patients' => $myPatients->count(),
            'male_patients' => $myPatients->where('sex', 'Male')->count(),
            'female_patients' => $myPatients->where('sex', 'Female')->count(),
            'total_assessments' => Assessment::where('nutritionist_id', $nutritionistId)->count(),
            'completed_assessments' => Assessment::where('nutritionist_id', $nutritionistId)
                ->whereNotNull('completed_at')
                ->count(),
            'pending_assessments' => Assessment::where('nutritionist_id', $nutritionistId)
                ->whereNull('completed_at')
                ->count(),
            'assessments_this_month' => Assessment::where('nutritionist_id', $nutritionistId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            '4ps_beneficiaries' => $myPatients->where('is_4ps_beneficiary', true)->count(),
        ];

        // Age distribution (in years)
        $ageDistribution = $myPatients->groupBy(function($patient) {
            $ageYears = floor($patient->age_months / 12);
            if ($ageYears < 2) return '0-2 years';
            if ($ageYears < 5) return '3-5 years';
            if ($ageYears < 10) return '6-10 years';
            return '10+ years';
        })->map->count();

        // Nutritional status distribution (from latest assessments)
        $nutritionalStatus = [];
        foreach ($myPatients as $patient) {
            $latestAssessment = Assessment::where('patient_id', $patient->patient_id)
                ->latest()
                ->first();
            
            if ($latestAssessment && $latestAssessment->recovery_status) {
                $status = $latestAssessment->recovery_status;
                $nutritionalStatus[$status] = ($nutritionalStatus[$status] ?? 0) + 1;
            }
        }

        // Barangay distribution
        $barangayDistribution = $myPatients->load('barangay')
            ->groupBy('barangay.name')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        // Monthly assessment trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyTrend[$date->format('M Y')] = Assessment::where('nutritionist_id', $nutritionistId)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
        }

        // Recent assessments for tracking
        $recentAssessments = Assessment::where('nutritionist_id', $nutritionistId)
            ->with(['patient', 'patient.barangay'])
            ->latest()
            ->take(10)
            ->get();

        // Children needing attention (based on latest assessment)
        $childrenNeedingAttention = [];
        foreach ($myPatients as $patient) {
            $latestAssessment = Assessment::where('patient_id', $patient->patient_id)
                ->latest()
                ->first();
            
            if ($latestAssessment && 
                in_array($latestAssessment->recovery_status, ['Severely Malnourished', 'Moderately Malnourished', 'At Risk'])) {
                $childrenNeedingAttention[] = [
                    'patient' => $patient,
                    'assessment' => $latestAssessment,
                ];
            }
        }

        return view('nutritionist.reports', compact(
            'reports',
            'ageDistribution',
            'nutritionalStatus',
            'barangayDistribution',
            'monthlyTrend',
            'recentAssessments',
            'childrenNeedingAttention'
        ));
    }

    /**
     * Download Children Monitoring Report PDF
     */
    public function downloadChildrenMonitoringReport(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        // Filter parameters
        $startDate = $request->input('start_date', now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $barangayId = $request->input('barangay_id');
        $status = $request->input('status');

        // Get filtered patients
        $patientsQuery = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['barangay', 'assessments' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('assessment_date', [$startDate, $endDate])
                    ->orderBy('assessment_date', 'desc');
            }]);

        if ($barangayId) {
            $patientsQuery->where('barangay_id', $barangayId);
        }

        $patients = $patientsQuery->get();

        // Filter by status if provided
        if ($status) {
            $patients = $patients->filter(function($patient) use ($status) {
                $latestAssessment = $patient->assessments->first();
                return $latestAssessment && $latestAssessment->recovery_status === $status;
            });
        }

        $data = [
            'title' => 'Children Monitoring Report',
            'nutritionist' => $nutritionist,
            'patients' => $patients,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedDate' => now()->format('F d, Y'),
            'generatedTime' => now()->format('h:i A'),
        ];

        $pdf = PDF::loadView('nutritionist.reports.pdf.children-monitoring', $data);
        $pdf->setPaper('legal', 'landscape');
        
        $filename = 'children-monitoring-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Download Assessment Summary Report PDF
     */
    public function downloadAssessmentSummaryReport(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        // Filter parameters
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Get assessments in date range
        $assessments = Assessment::where('nutritionist_id', $nutritionistId)
            ->with(['patient', 'patient.barangay'])
            ->whereBetween('assessment_date', [$startDate, $endDate])
            ->orderBy('assessment_date', 'desc')
            ->get();

        // Calculate statistics
        $statistics = [
            'total_assessments' => $assessments->count(),
            'completed_assessments' => $assessments->whereNotNull('completed_at')->count(),
            'by_status' => $assessments->whereNotNull('recovery_status')
                ->groupBy('recovery_status')
                ->map->count(),
            'by_barangay' => $assessments->groupBy('patient.barangay.name')
                ->map->count(),
            'average_weight' => round($assessments->avg('weight_kg'), 2),
            'average_height' => round($assessments->avg('height_cm'), 2),
        ];

        $data = [
            'title' => 'Assessment Summary Report',
            'nutritionist' => $nutritionist,
            'assessments' => $assessments,
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedDate' => now()->format('F d, Y'),
            'generatedTime' => now()->format('h:i A'),
        ];

        $pdf = PDF::loadView('nutritionist.reports.pdf.assessment-summary', $data);
        $pdf->setPaper('legal', 'landscape');
        
        $filename = 'assessment-summary-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Download Monthly Progress Report PDF
     */
    public function downloadMonthlyProgressReport(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;

        // Get month and year
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get all patients
        $patients = Patient::where('nutritionist_id', $nutritionistId)
            ->with(['barangay'])
            ->get();

        // Get assessments for the month
        $assessments = Assessment::where('nutritionist_id', $nutritionistId)
            ->whereMonth('assessment_date', $month)
            ->whereYear('assessment_date', $year)
            ->with(['patient'])
            ->get();

        // Progress tracking - compare with previous assessments
        $progressData = [];
        foreach ($patients as $patient) {
            $currentMonthAssessment = $assessments->where('patient_id', $patient->patient_id)->first();
            
            if ($currentMonthAssessment) {
                // Get previous assessment
                $previousAssessment = Assessment::where('patient_id', $patient->patient_id)
                    ->where('assessment_date', '<', $currentMonthAssessment->assessment_date)
                    ->latest('assessment_date')
                    ->first();

                $progressData[] = [
                    'patient' => $patient,
                    'current' => $currentMonthAssessment,
                    'previous' => $previousAssessment,
                    'weight_change' => $previousAssessment ? 
                        round($currentMonthAssessment->weight_kg - $previousAssessment->weight_kg, 2) : null,
                    'height_change' => $previousAssessment ? 
                        round($currentMonthAssessment->height_cm - $previousAssessment->height_cm, 2) : null,
                ];
            }
        }

        // Summary statistics
        $statistics = [
            'total_patients' => $patients->count(),
            'assessed_this_month' => $assessments->count(),
            'improved' => collect($progressData)->filter(function($item) {
                return $item['weight_change'] > 0;
            })->count(),
            'stable' => collect($progressData)->filter(function($item) {
                return $item['weight_change'] == 0;
            })->count(),
            'declined' => collect($progressData)->filter(function($item) {
                return $item['weight_change'] < 0;
            })->count(),
        ];

        $data = [
            'title' => 'Monthly Progress Report',
            'nutritionist' => $nutritionist,
            'month' => date('F', mktime(0, 0, 0, $month, 1)),
            'year' => $year,
            'progressData' => $progressData,
            'statistics' => $statistics,
            'generatedDate' => now()->format('F d, Y'),
            'generatedTime' => now()->format('h:i A'),
        ];

        $pdf = PDF::loadView('nutritionist.reports.pdf.monthly-progress', $data);
        $pdf->setPaper('legal', 'landscape');
        
        $filename = 'monthly-progress-report-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Save feeding program meal plan
     */
    public function saveFeedingProgramPlan(Request $request)
    {
        try {
            $validated = $request->validate([
                'target_age_group' => 'required|string',
                'total_children' => 'nullable|integer',
                'program_duration_days' => 'required|integer|min:1|max:7',
                'budget_level' => 'required|string|in:low,moderate,high',
                'barangay' => 'nullable|string',
                'available_ingredients' => 'nullable|string',
                'meal_plan' => 'required',
            ]);

            $plan = FeedingProgramPlan::create([
                'target_age_group' => $validated['target_age_group'],
                'total_children' => $validated['total_children'],
                'program_duration_days' => $validated['program_duration_days'],
                'budget_level' => $validated['budget_level'],
                'barangay' => $validated['barangay'],
                'available_ingredients' => $validated['available_ingredients'],
                'plan_details' => $validated['meal_plan'], // Will auto-encode to JSON
                'generated_at' => now(),
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feeding program meal plan saved successfully',
                'plan_id' => $plan->program_plan_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save meal plan: ' . $e->getMessage()
            ], 500);
        }
    }


}
