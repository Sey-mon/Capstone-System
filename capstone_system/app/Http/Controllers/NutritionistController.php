<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use App\Models\Barangay;
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
        
        $stats = [
            'my_patients' => Patient::where('nutritionist_id', $nutritionist->user_id)->count(),
            'pending_assessments' => Assessment::where('nutritionist_id', $nutritionist->user_id)
                ->whereNull('completed_at')
                ->count(),
            'completed_assessments' => Assessment::where('nutritionist_id', $nutritionist->user_id)
                ->whereNotNull('completed_at')
                ->count(),
            'recent_patients' => Patient::where('nutritionist_id', $nutritionist->user_id)
                ->with('parent')
                ->latest()
                ->take(5)
                ->get(),
            'recent_assessments' => Assessment::where('nutritionist_id', $nutritionist->user_id)
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
        $query = Patient::where('nutritionist_id', $nutritionist->user_id)
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
                'nutritionist_id' => $nutritionist->user_id,
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
        $patient = Patient::where('nutritionist_id', $nutritionist->user_id)
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
        $patient = Patient::where('nutritionist_id', $nutritionist->user_id)
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
        $patient = Patient::where('nutritionist_id', $nutritionist->user_id)
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
        $assessments = Assessment::where('nutritionist_id', $nutritionist->user_id)
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
}
