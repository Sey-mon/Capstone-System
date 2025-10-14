<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\MealPlan;
use App\Services\MalnutritionService;
use App\Services\NutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    // ========================================
    // ADMIN API MANAGEMENT METHODS
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

    // ========================================
    // MALNUTRITION ASSESSMENT API METHODS
    // ========================================

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

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Authorization check based on user role
        if ($user->role->role_name === 'Nutritionist' && $patient->nutritionist_id !== $user->user_id) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to assess this patient.'
                ], 403);
            }
            return redirect()->back()->withErrors(['error' => 'You are not authorized to assess this patient.']);
        }

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
                'nutritionist_id' => $user->role->role_name === 'Nutritionist' ? $user->user_id : null,
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
            Log::error('Assessment API Error: ' . $e->getMessage());
            
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

    // ========================================
    // NUTRITION API METHODS
    // ========================================

    /**
     * Generate nutrition analysis for a patient
     */
    public function generateNutritionAnalysis(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id'
        ]);

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Authorization check based on user role
        if (!$this->canAccessPatient($user, $patient)) {
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
     * Generate meal plan for a patient (Nutritionist version)
     */
    public function generateNutritionistMealPlan(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'available_foods' => 'nullable|string'
        ]);

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Check if this patient is assigned to the authenticated nutritionist
        if ($patient->nutritionist_id !== $user->user_id) {
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
     * Generate meal plan for a child (Parent version)
     */
    public function generateParentMealPlan(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,patient_id',
            'available_foods' => 'required|string',
        ]);

        $parent = Auth::user();
        
        // Verify this child belongs to the authenticated parent
        $child = Patient::where('patient_id', $validated['patient_id'])
            ->where('parent_id', $parent->user_id)
            ->firstOrFail();

        try {
            // Prepare the data exactly as your FastAPI expects
            $requestData = [
                'patient_id' => (int) $validated['patient_id'],
                'available_foods' => $validated['available_foods'] // Keep as string, not array
            ];

            Log::info('Sending request to LLM API', [
                'url' => env('LLM_API_URL') . '/generate_meal_plan',
                'data' => $requestData
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post(env('LLM_API_URL') . '/generate_meal_plan', $requestData);

            Log::info('LLM API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $mealPlan = $responseData['meal_plan'] ?? $responseData['result'] ?? $response->body();
                $mealPlanHtml = $this->formatMealPlanHtml($mealPlan);

                // Save to meal_plans table
                $mealPlanRecord = MealPlan::create([
                    'patient_id' => $child->patient_id,
                    'plan_details' => $mealPlan,
                    'notes' => null,
                    'generated_at' => now(),
                ]);

                return back()->with('success', 'Meal plan generated successfully!')
                            ->with('meal_plan', $mealPlan)
                            ->with('meal_plan_html', $mealPlanHtml)
                            ->with('child_name', $child->first_name . ' ' . $child->last_name);
            } else {
                Log::error('LLM API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);
                
                return back()->withErrors(['api_error' => 'Failed to generate meal plan. API returned status: ' . $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Meal Plan Generation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['api_error' => 'Unable to connect to meal plan service: ' . $e->getMessage()]);
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

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Authorization check
        if (!$this->canAccessPatient($user, $patient)) {
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

        $user = Auth::user();
        $patient = Patient::findOrFail($request->patient_id);

        // Authorization check
        if (!$this->canAccessPatient($user, $patient)) {
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

    // ========================================
    // API TESTING METHODS
    // ========================================

    /**
     * Test API endpoint (for parents)
     */
    public function testApi()
    {
        return view('parent.test-api');
    }

    /**
     * Test API POST (for parents)
     */
    public function testApiPost(Request $request)
    {
        try {
            $requestData = [
                'patient_id' => (int) $request->patient_id,
                'available_foods' => $request->available_foods // Keep as string
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post(env('LLM_API_URL') . '/generate_meal_plan', $requestData);

            $result = "Status: " . $response->status() . "\n";
            $result .= "Headers: " . json_encode($response->headers(), JSON_PRETTY_PRINT) . "\n";
            $result .= "Body: " . $response->body();
            
            return back()->with('test_result', $result);
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Check if user can access a patient based on their role
     */
    private function canAccessPatient($user, $patient)
    {
        $roleName = $user->role->role_name;

        switch ($roleName) {
            case 'Admin':
                return true; // Admin can access all patients
            case 'Nutritionist':
                return $patient->nutritionist_id === $user->user_id;
            case 'Parent':
                return $patient->parent_id === $user->user_id;
            default:
                return false;
        }
    }

    /**
     * Format meal plan text into HTML sections for better UI presentation.
     */
    private function formatMealPlanHtml($text)
    {
        // Section headings
        $text = preg_replace('/COMPREHENSIVE NUTRITION PLAN:/i', '<h4>Comprehensive Nutrition Plan</h4>', $text);
        $text = preg_replace('/ESTIMATED KCAL NEEDS:/i', '<h5>Estimated Kcal Needs</h5>', $text);
        $text = preg_replace('/AGE-SPECIFIC FEEDING GUIDELINES:/i', '<h5>Age-Specific Feeding Guidelines</h5>', $text);
        $text = preg_replace('/7-DAY MEAL PLAN:/i', '<h4>7-Day Meal Plan</h4>', $text);
        $text = preg_replace('/DAY ([0-9]+):/i', '<h5>Day $1</h5><ul>', $text);
        $text = preg_replace('/- \*\*(.*?)\*\*: (.*?)(\(~?\d+ kcal\))?/i', '<li><strong>$1:</strong> $2 <span class="text-muted">$3</span></li>', $text);
        $text = preg_replace('/- ([^-].+)/', '<li>$1</li>', $text);
        $text = preg_replace('/\*\*Daily Total\*\*: ~?(\d+ kcal)/i', '<div class="daily-total">Daily Total: $1</div></ul>', $text);
        $text = preg_replace('/PARENT OBSERVATION TRACKING:/i', '<h5>Parent Observation Tracking</h5><div class="observation">', $text);
        $text = preg_replace('/RED FLAGS & EMERGENCY PROTOCOLS:/i', '</div><h5>Red Flags & Emergency Protocols</h5><div class="red-flags">', $text);
        $text = preg_replace('/FINAL VERIFICATION:/i', '</div><h5>Final Verification</h5>', $text);
        $text = nl2br($text); // Convert newlines to <br>
        return $text;
    }
}