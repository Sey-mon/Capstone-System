<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use App\Models\Barangay;
use App\Services\MalnutritionService;
use App\Services\NutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        return view('nutritionist.dashboard', compact('stats'));
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
                $q->whereHas('patient', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('contact_number', 'like', "%{$search}%");
                })
                ->orWhere('treatment', 'like', "%{$search}%")
                ->orWhere('assessment_id', 'like', "%{$search}%");
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

        return view('nutritionist.patients', compact('patients', 'barangays', 'parents'));
    }

    /**
     * Store a new patient
     */
    public function storePatient(Request $request)
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        
        $request->validate([
            'parent_id' => 'required|exists:users,user_id',
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
            'parent_id' => 'required|exists:users,user_id',
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
                'parent_id' => $request->parent_id,
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
        
        switch ($sortBy) {
            case 'assessment_date':
                $query->leftJoin('assessments', function($join) {
                    $join->on('patients.patient_id', '=', 'assessments.patient_id')
                         ->whereRaw('assessments.assessment_id = (SELECT MAX(assessment_id) FROM assessments WHERE assessments.patient_id = patients.patient_id)');
                })->select('patients.*')->orderBy('assessments.assessment_date', $sortOrder);
                break;
            case 'patient_id':
            case 'first_name':
                $query->orderBy('first_name', $sortOrder)->orderBy('last_name', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
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
     * Perform malnutrition assessment
     */
    public function performAssessment(Request $request, MalnutritionService $malnutritionService)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'age_months' => 'required|integer|min:0|max:60',
            'weight_kg' => 'required|numeric|min:1|max:50',
            'height_cm' => 'required|numeric|min:30|max:150',
            'gender' => 'required|in:male,female',
        ]);

        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $patient = Patient::where('patient_id', $request->patient_id)
            ->where('nutritionist_id', $nutritionistId)
            ->firstOrFail();

        // Prepare child data for API
        $childData = [
            'age_months' => $request->age_months,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'gender' => strtolower($request->gender),
            'has_edema' => $request->has('has_edema'),
            'appetite' => $request->appetite ?? 'good',
            'diarrhea_days' => $request->diarrhea_days ?? 0,
            'fever_days' => $request->fever_days ?? 0,
            'vomiting' => $request->has('vomiting'),
        ];

        // Prepare socioeconomic data
        $socioData = [
            'is_4ps_beneficiary' => $request->has('is_4ps_beneficiary'),
            'household_size' => $request->household_size ?? 4,
            'has_electricity' => $request->has('has_electricity'),
            'has_clean_water' => $request->has('has_clean_water'),
            'mother_education' => $request->mother_education ?? 'primary',
            'father_present' => $request->has('father_present'),
        ];

        try {
            // Perform assessment using API
            $result = $malnutritionService->assessChild($childData, $socioData);
            
            // Store assessment in database
            $assessment = Assessment::create([
                'patient_id' => $patient->patient_id,
                'nutritionist_id' => $nutritionistId,
                'assessment_date' => now(),
                'weight_kg' => $request->weight_kg,
                'height_cm' => $request->height_cm,
                'treatment' => json_encode($result['treatment_plan'] ?? []),
                'notes' => $request->notes,
                'completed_at' => now(),
            ]);

            // If it's an AJAX request, return JSON response
            if ($request->ajax()) {
                // Extract diagnosis from the result for the response
                $diagnosisText = $result['assessment']['primary_diagnosis'] ?? 'Unknown';
                
                return response()->json([
                    'success' => true,
                    'message' => 'Assessment completed successfully!',
                    'assessment_id' => $assessment->assessment_id,
                    'diagnosis' => $diagnosisText
                ]);
            }

            return view('nutritionist.assessment-results', compact('result', 'patient', 'assessment', 'childData'));
            
        } catch (\Exception $e) {
            // If it's an AJAX request, return JSON error
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Assessment failed: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Assessment failed: ' . $e->getMessage()]);
        }
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

        // Generate PDF
        $pdf = Pdf::loadView('nutritionist.assessment-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'assessment_' . $patient->first_name . '_' . $patient->last_name . '_' . date('Y-m-d') . '.pdf';
        
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
     * Generate nutrition analysis for a patient
     */
    public function generateNutritionAnalysis(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id'
        ]);

        $nutritionist = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if this patient is assigned to the authenticated nutritionist
        if ($patient->nutritionist_id !== $nutritionist->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this patient.'
            ], 403);
        }

        try {
            $nutritionService = new NutritionService();
            $analysis = $nutritionService->analyzeNutrition($request->patient_id);

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate nutrition analysis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate meal plan for a patient
     */
    public function generateMealPlan(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'available_foods' => 'nullable|string'
        ]);

        $nutritionist = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if this patient is assigned to the authenticated nutritionist
        if ($patient->nutritionist_id !== $nutritionist->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this patient.'
            ], 403);
        }

        try {
            $nutritionService = new NutritionService();
            $mealPlan = $nutritionService->generateMealPlan(
                $request->patient_id,
                $request->available_foods
            );

            return response()->json([
                'success' => true,
                'data' => $mealPlan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate meal plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate patient assessment using AI
     */
    public function generatePatientAssessment(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id'
        ]);

        $nutritionist = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if this patient is assigned to the authenticated nutritionist
        if ($patient->nutritionist_id !== $nutritionist->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this patient.'
            ], 403);
        }

        try {
            $nutritionService = new NutritionService();
            $assessment = $nutritionService->generateAssessment($request->patient_id);

            return response()->json([
                'success' => true,
                'data' => $assessment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate patient assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get foods data from nutrition API
     */
    public function getFoodsData()
    {
        try {
            $nutritionService = new NutritionService();
            $foods = $nutritionService->getFoodsData();

            return response()->json([
                'success' => true,
                'data' => $foods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve foods data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meal plans for a specific patient
     */
    public function getPatientMealPlans(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'most_recent' => 'nullable|boolean'
        ]);

        $nutritionist = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if this patient is assigned to the authenticated nutritionist
        if ($patient->nutritionist_id !== $nutritionist->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to access this patient.'
            ], 403);
        }

        try {
            $nutritionService = new NutritionService();
            $mealPlans = $nutritionService->getMealPlansByChild(
                $request->patient_id,
                $request->boolean('most_recent', false)
            );

            return response()->json([
                'success' => true,
                'data' => $mealPlans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meal plans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get knowledge base data
     */
    public function getKnowledgeBase()
    {
        try {
            $nutritionService = new NutritionService();
            $knowledgeBase = $nutritionService->getKnowledgeBase();

            return response()->json([
                'success' => true,
                'data' => $knowledgeBase
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve knowledge base: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get meal plan detail
     */
    public function getMealPlanDetail(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer'
        ]);

        try {
            $nutritionService = new NutritionService();
            $mealPlan = $nutritionService->getMealPlanDetail($request->plan_id);

            return response()->json([
                'success' => true,
                'data' => $mealPlan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve meal plan detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test nutrition API connection
     */
    public function testNutritionAPI()
    {
        try {
            $nutritionService = new NutritionService();
            $isConnected = $nutritionService->testConnection();

            return response()->json([
                'success' => true,
                'connected' => $isConnected,
                'message' => $isConnected ? 'Nutrition API is connected' : 'Nutrition API is not responding'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => 'Failed to test nutrition API connection: ' . $e->getMessage()
            ], 500);
        }
    }
}
