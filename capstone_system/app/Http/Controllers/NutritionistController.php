<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use App\Models\Barangay;
use App\Services\MalnutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhereHas('parent', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $patients = $query->paginate(15);
        $barangays = Barangay::all();
        $parents = User::where('role_id', function($query) {
            $query->select('role_id')->from('roles')->where('role_name', 'Parent');
        })->get();

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
    public function assessments()
    {
        $nutritionist = Auth::user();
        $nutritionistId = $nutritionist->user_id;
        $assessments = Assessment::where('nutritionist_id', $nutritionistId)
            ->with('patient')
            ->paginate(15);

        return view('nutritionist.assessments', compact('assessments'));
    }

    /**
     * Show nutritionist profile
     */
    public function profile()
    {
        $nutritionist = Auth::user();
        return view('nutritionist.profile', compact('nutritionist'));
    }

    // ========================================
    // MALNUTRITION ASSESSMENT METHODS
    // ========================================

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
            'muac_cm' => $request->muac_cm,
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
                'muac_cm' => $request->muac_cm,
                'diagnosis' => $result['assessment']['primary_diagnosis'] ?? 'Unknown',
                'treatment_plan' => json_encode($result['treatment_plan'] ?? []),
                'notes' => $request->notes,
                'completed_at' => now(),
            ]);

            return view('nutritionist.assessment-results', compact('result', 'patient', 'assessment', 'childData'));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Assessment failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Quick assessment (AJAX)
     */
    public function quickAssessment(Request $request, MalnutritionService $malnutritionService)
    {
        $request->validate([
            'age_months' => 'required|integer|min:0|max:60',
            'weight_kg' => 'required|numeric|min:1|max:50',
            'height_cm' => 'required|numeric|min:30|max:150',
            'gender' => 'required|in:male,female',
        ]);

        $childData = [
            'age_months' => $request->age_months,
            'weight_kg' => $request->weight_kg,
            'height_cm' => $request->height_cm,
            'gender' => strtolower($request->gender),
            'muac_cm' => $request->muac_cm,
            'has_edema' => $request->has('has_edema'),
        ];

        try {
            $result = $malnutritionService->assessMalnutritionOnly($childData);
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
