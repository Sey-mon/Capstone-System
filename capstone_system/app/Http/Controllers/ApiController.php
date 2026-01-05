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
            // Check if an assessment already exists for this patient today
            $todayDate = now()->format('Y-m-d');
            $existingAssessment = Assessment::where('patient_id', $patient->patient_id)
                ->whereDate('assessment_date', $todayDate)
                ->first();
            
            // If assessment exists for today, prevent duplicate
            if ($existingAssessment) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This patient has already been assessed today. You can only assess a patient once per day. Please try again tomorrow or view the existing assessment.',
                        'existing_assessment_id' => $existingAssessment->assessment_id
                    ], 422);
                }
                
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'This patient has already been assessed today. You can only assess a patient once per day.']);
            }
            
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
     * Generate feeding program meal plan
     */
    public function generateFeedingProgram(Request $request)
    {
        $request->validate([
            'target_age_group' => 'required|string|in:all,6-12months,12-24months,24-60months',
            'program_duration_days' => 'required|integer|min:1|max:5',
            'budget_level' => 'required|string|in:low,moderate,high',
            'barangay' => 'nullable|string',
            'total_children' => 'nullable|integer|min:1',
            'available_ingredients' => 'nullable|string'
        ]);

        try {
            $nutritionService = new NutritionService();
            $result = $nutritionService->generateFeedingProgram(
                $request->target_age_group,
                $request->program_duration_days,
                $request->budget_level,
                $request->barangay,
                $request->total_children,
                $request->available_ingredients
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate feeding program: ' . $e->getMessage()
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

        // Check if a meal plan was generated within the last 7 days
        $latestMealPlan = MealPlan::where('patient_id', $validated['patient_id'])
            ->orderBy('generated_at', 'desc')
            ->first();

        if ($latestMealPlan && $latestMealPlan->generated_at) {
            $generatedAt = \Carbon\Carbon::parse($latestMealPlan->generated_at);
            $now = now();
            
            // Calculate days and hours
            $totalHours = $generatedAt->diffInHours($now);
            $daysSinceLastPlan = floor($totalHours / 24);
            $hoursSinceLastPlan = $totalHours % 24;
            
            if ($daysSinceLastPlan < 7 || ($daysSinceLastPlan == 7 && $hoursSinceLastPlan == 0)) {
                $daysRemaining = 7 - $daysSinceLastPlan;
                $hoursRemaining = $hoursSinceLastPlan > 0 ? 24 - $hoursSinceLastPlan : 0;
                
                // Build time ago string
                $timeAgo = "{$daysSinceLastPlan} " . ($daysSinceLastPlan === 1 ? 'day' : 'days');
                if ($hoursSinceLastPlan > 0) {
                    $timeAgo .= " and {$hoursSinceLastPlan} " . ($hoursSinceLastPlan === 1 ? 'hour' : 'hours');
                }
                
                // Build time remaining string
                $timeRemaining = '';
                if ($daysRemaining > 0) {
                    $timeRemaining = "{$daysRemaining} " . ($daysRemaining === 1 ? 'day' : 'days');
                    if ($hoursRemaining > 0) {
                        $timeRemaining .= " and {$hoursRemaining} " . ($hoursRemaining === 1 ? 'hour' : 'hours');
                    }
                } else {
                    $timeRemaining = "{$hoursRemaining} " . ($hoursRemaining === 1 ? 'hour' : 'hours');
                }
                
                $nextAvailableDate = $generatedAt->copy()->addDays(7)->format('M d, Y \a\t g:i A');
                
                return back()->withErrors([
                    'cooldown' => "You can only generate a new meal plan once every 7 days. A meal plan for {$child->first_name} was generated {$timeAgo} ago. Please wait {$timeRemaining} more (available on {$nextAvailableDate})."
                ])->with('last_meal_plan', $latestMealPlan->plan_details)
                  ->with('meal_plan_html', $this->formatMealPlanHtml($latestMealPlan->plan_details))
                  ->with('child_name', $child->first_name . ' ' . $child->last_name)
                  ->with('plan_date', $generatedAt->format('M d, Y'));
            }
        }

        try {
            // Get LLM API URL from config (proper Laravel way)
            $llmApiUrl = config('services.nutrition_api.base_url');
            
            if (empty($llmApiUrl)) {
                Log::error('LLM API URL is not configured', [
                    'config_value' => $llmApiUrl,
                    'env_value' => env('LLM_API_URL')
                ]);
                return back()->withErrors(['api_error' => 'Meal plan service is not configured. Please contact administrator.']);
            }
            
            // Prepare the data exactly as your FastAPI expects
            $requestData = [
                'patient_id' => (int) $validated['patient_id'],
                'available_foods' => $validated['available_foods'] // Keep as string, not array
            ];

            $fullUrl = rtrim($llmApiUrl, '/') . '/generate_meal_plan';

            Log::info('Sending request to LLM API', [
                'url' => $fullUrl,
                'data' => $requestData,
                'config_base_url' => $llmApiUrl
            ]);

            $response = Http::timeout(config('services.nutrition_api.timeout', 30))
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($fullUrl, $requestData);

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
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Meal Plan API Connection Error', [
                'message' => $e->getMessage(),
                'llm_api_url' => config('services.nutrition_api.base_url'),
                'env_llm_url' => env('LLM_API_URL'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'api_error' => 'Unable to connect to meal plan service. Please ensure the API is running.'
            ]);
        } catch (\Exception $e) {
            Log::error('Meal Plan Generation Error', [
                'message' => $e->getMessage(),
                'llm_api_url' => config('services.nutrition_api.base_url'),
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
    // Section headings with big bold black styling (h4)
    // Ensure the child profile table is opened once and closed before other sections
    $text = preg_replace('/CHILD PROFILE:/i', '<h4 class="meal-plan-heading">Child Profile</h4><div class="child-profile-table">', $text);
        
        // Filipino specific profile items - compact inline format
        $text = preg_replace('/\*\*Edad\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>👶 Edad:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*Timbang\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>⚖️ Timbang:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*Taas\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>📏 Taas:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*BMI\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>📊 BMI:</strong> $1</span>', $text);
        // Profile items: keep as inline blocks inside the child-profile-table
        $text = preg_replace('/\*\*Allergy\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>🚫 Allergy:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*Allergies\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>🚫 Allergies:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*Karamdaman\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>⚕️ Karamdaman:</strong> $1</span>', $text);
        $text = preg_replace('/\*\*Relihiyon\*\*:\s*([^\n\*]+)/i', '<span class="profile-item"><strong>🕌 Relihiyon:</strong> $1</span>', $text);

        // Additional patterns for compliance items that might not have ** formatting
        $text = preg_replace('/(^|\n)\s*Allerg(?:y|ies):\s*([^\n]+)/i', '<span class="profile-item"><strong>🚫 Allergy:</strong> $2</span>', $text);
        $text = preg_replace('/(^|\n)\s*Relihiyon:\s*([^\n]+)/i', '<span class="profile-item"><strong>🕌 Relihiyon:</strong> $2</span>', $text);
        $text = preg_replace('/(^|\n)\s*Karamdaman:\s*([^\n]+)/i', '<span class="profile-item"><strong>⚕️ Karamdaman:</strong> $2</span>', $text);
        $text = preg_replace('/\*\*Available Ingredients\*\*:/i', '</div><h4 class="meal-plan-heading">🥘 Available Ingredients:</h4>', $text);
        // Fix the 7-DAY MEAL PLAN pattern to ensure it's properly detected as a section header
        $text = preg_replace('/\b7-DAY MEAL PLAN\b:?\s*/i', '</div><h4 class="meal-plan-heading">📅 7-Day Meal Plan</h4>', $text);
        $text = preg_replace('/###\s*7-DAY MEAL PLAN\s*/i', '</div><h4 class="meal-plan-heading">📅 7-Day Meal Plan</h4>', $text);
        $text = preg_replace('/\*\*Kasalukuyang Edad \([0-9]+ buwan\)\*\*:/i', '<h5 class="meal-plan-heading">👶 Kasalukuyang Edad:</h5>', $text);
        $text = preg_replace('/\*\*Araw-Araw\*\*:/i', '<h4 class="meal-plan-heading">Araw-Araw:</h4>', $text);
        $text = preg_replace('/\*\*Kailangan ng Agarang Atensyon\*\*:/i', '<h4 class="meal-plan-heading">Kailangan ng Agarang Atensyon:</h4>', $text);
        
        // Green h4 patterns
        $text = preg_replace('/AGE-SPECIFIC GUIDELINES:/i', '<h4 class="green-heading">🍼 AGE-SPECIFIC GUIDELINES:</h4>', $text);
        
        // Day headers - handle both quoted and unquoted formats with big bold black styling (h3)
        $text = preg_replace('/\*\*Day ([0-9]+)\*\*:/i', '<h3 class="day-heading">📅 Day $1</h3>', $text);
        $text = preg_replace('/"?\*\*Day ([0-9]+)\*\*"?:?/i', '<h3 class="day-heading">📅 Day $1</h3>', $text);
        $text = preg_replace('/DAY ([0-9]+):/i', '<h3 class="day-heading">📅 Day $1</h3>', $text);
        
        // Meal type formatting - big bold black with emojis (h4)
        $text = preg_replace('/- \*\*Breakfast \(Almusal\)\*\*:/i', '<h4 class="meal-type-heading">🍳 Breakfast (Almusal)</h4>', $text);
        $text = preg_replace('/- \*\*Lunch \(Tanghalian\)\*\*:/i', '<h4 class="meal-type-heading">🍽️ Lunch (Tanghalian)</h4>', $text);
        $text = preg_replace('/- \*\*Snack \(Meryenda\)\*\*:/i', '<h4 class="meal-type-heading">🍪 Snack (Meryenda)</h4>', $text);
        $text = preg_replace('/- \*\*Dinner \(Hapunan\)\*\*:/i', '<h4 class="meal-type-heading">🌙 Dinner (Hapunan)</h4>', $text);
        
        // Alternative patterns without parentheses (fallback) - big bold black (h4)
        $text = preg_replace('/- \*\*Breakfast\*\*:/i', '<h4 class="meal-type-heading">🍳 Breakfast (Almusal)</h4>', $text);
        $text = preg_replace('/- \*\*Lunch\*\*:/i', '<h4 class="meal-type-heading">🍽️ Lunch (Tanghalian)</h4>', $text);
        $text = preg_replace('/- \*\*Snack\*\*:/i', '<h4 class="meal-type-heading">🍪 Snack (Meryenda)</h4>', $text);
        $text = preg_replace('/- \*\*Dinner\*\*:/i', '<h4 class="meal-type-heading">🌙 Dinner (Hapunan)</h4>', $text);
        
        // Fix bullet points - convert • to proper list items
        $text = preg_replace('/• ([^<\n]+)/i', '<ul class="meal-details"><li>$1</li></ul>', $text);
        $text = preg_replace('/\* ([^<\n]+)/i', '<ul class="meal-details"><li>$1</li></ul>', $text);
        
        // Special fix for Day 1 bullet points that appear after meal names
        $text = preg_replace('/(📅 Day 1.*?🍳 Breakfast \(Almusal\).*?)(\n)• ([^\n]+)/s', '$1$2<ul class="meal-details"><li>$3</li></ul>', $text);
        
        // Format meal items with proper spacing and structure
        $text = preg_replace('/(🍳|🍽️|🍪|🌙)\s*([^\n]+)\n([^🍳🍽️🍪🌙📅\n]+)\n?([^🍳🍽️🍪🌙📅\n]*)/m', 
            '$1 $2<div class="meal-item"><div class="meal-name">$3</div><div class="meal-description">$4</div></div>', $text);
        
        // Handle simple meal items (food name - description)
        $text = preg_replace('/^([A-Za-z][^\n-]*?)\s*-\s*([^\n]+)$/m', '<div class="meal-item"><div class="meal-name">$1</div><div class="meal-description">$2</div></div>', $text);
        
        // Clean up any remaining meal formatting patterns
        // Remove old meal item patterns that are no longer needed
        $text = preg_replace('/- \*\*(.*?)\*\*: (.*?)(\(~?\d+ kcal\))?/i', '<div class="meal-item"><div class="meal-name">$1</div><div class="meal-description">$2 $3</div></div>', $text);
        $text = preg_replace('/\*\*Daily Total\*\*: ~?(\d+ kcal)/i', '<div class="daily-total"><strong>Daily Total:</strong> $1</div>', $text);
        
        // Fix long text blocks that run together - add line breaks
        $text = preg_replace('/([a-z])\s*-\s*([A-Z])/m', '$1<br>- $2', $text);
        $text = preg_replace('/([^\n])\s*\*\*([A-Z])/m', '$1<br><br>**$2', $text);
        
        // Ensure proper spacing after periods in long text
        $text = preg_replace('/([a-z]\.)\s*([A-Z][a-z])/m', '$1<br>$2', $text);
        
        // Fix spacing around observation sections
        // Minimal spacing between sections
        $text = preg_replace('/([a-z])\s*(Regular na Obserbahan|Balanseng Pagkain)/i', '$1 $2', $text);
        
        // Filipino observation sections - h4 headings with spacing
        $text = preg_replace('/REGULAR NA OBSERBAHAN:/i', '<br><h4 class="observation-heading">👀 Regular na Obserbahan</h4><div class="observation">', $text);
        
        // Observation frequency headings - simplified patterns
        $text = preg_replace('/\*\*Araw-Araw\*\*\s*(\(MUST include this exact subheader\))?:/i', '<h4 class="observation-subheading">📅 Araw-Araw</h4>', $text);
        $text = preg_replace('/\*\*Bawat Linggo\*\*\s*(\(MUST include this exact subheader\))?:/i', '<h4 class="observation-subheading">📊 Bawat Linggo</h4>', $text);
        $text = preg_replace('/\*\*Bawat Buwan\*\*:/i', '<h4 class="observation-subheading">📅 Bawat Buwan</h4>', $text);
        // BALANSENG PAGKAIN section - h3 heading with spacing
        $text = preg_replace('/BALANSENG PAGKAIN PARA SA BATA:/i', '<br><h3 class="balanced-food-heading">🍽️ BALANSENG PAGKAIN PARA SA BATA</h3>', $text);
        $text = preg_replace('/###\\s*BALANSENG PAGKAIN PARA SA BATA/i', '<br><h3 class="balanced-food-heading">🍽️ BALANSENG PAGKAIN PARA SA BATA</h3>', $text);
        
        // Make "Bawat pagkain dapat may:" an h4 heading
        $text = preg_replace('/Bawat pagkain dapat may:/i', '<h4 class="balanced-food-subheading">Bawat pagkain dapat may:</h4>', $text);
        
        // Warning sub-sections - h4 headings 
        $text = preg_replace('/KAILANGAN NG AGARANG ATENSYON:/i', '<h4 class="urgent-heading">🚨 Kailangan ng Agarang Atensyon</h4>', $text);
        $text = preg_replace('/MGA DAPAT PANSININ:/i', '<h4 class="notice-heading">👁️ Mga Dapat Pansinin</h4>', $text);
        
        $text = nl2br($text); // Convert newlines to <br>
        
        return $text;
    }
}