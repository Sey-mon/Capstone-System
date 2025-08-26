<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParentController extends Controller
{
    /**
     * Show form to bind a child to the authenticated parent
     */
    public function showBindChildForm()
    {
        return view('parent.bind_child');
    }

    /**
     * Handle binding a child to the authenticated parent securely
     */
    public function bindChild(Request $request)
    {
        $parent = Auth::user();
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'age_months' => 'required|integer',
            'contact_number' => 'required|string',
        ]);

        $child = Patient::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->where('age_months', $validated['age_months'])
            ->where('contact_number', $validated['contact_number'])
            ->whereNull('parent_id')
            ->first();

        if (!$child) {
            return back()->withErrors(['not_found' => 'No matching child found or child already bound.']);
        }

        $child->parent_id = $parent->user_id;
        $child->save();

        return redirect()->route('parent.children')->with('success', 'Child successfully bound to your account.');
    }
    /**
     * Show parent dashboard
     */
    public function dashboard()
    {
        $parent = Auth::user();
        
        // Get children with their latest assessments for growth tracking
        $children = Patient::where('parent_id', $parent->user_id)
            ->with(['nutritionist', 'assessments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->get();

        // Calculate growth trends for each child
        $childrenWithGrowth = $children->map(function($child) {
            $assessments = $child->assessments;
            $latestAssessment = $assessments->first();
            $previousAssessment = $assessments->skip(1)->first();
            
            $growthTrend = null;
            $weightChange = null;
            $heightChange = null;
            $nutritionStatus = 'No Assessment';
            
            if ($latestAssessment) {
                $nutritionStatus = $this->determineNutritionStatus($latestAssessment);
                
                if ($previousAssessment) {
                    $weightChange = $child->weight_kg - ($previousAssessment->weight_kg ?? $child->weight_kg);
                    $heightChange = $child->height_cm - ($previousAssessment->height_cm ?? $child->height_cm);
                    
                    // Determine growth trend
                    if ($weightChange > 0 && $heightChange > 0) {
                        $growthTrend = 'improving';
                    } elseif ($weightChange < 0 || $heightChange < 0) {
                        $growthTrend = 'declining';
                    } else {
                        $growthTrend = 'stable';
                    }
                }
            }
            
            return [
                'child' => $child,
                'latest_assessment' => $latestAssessment,
                'growth_trend' => $growthTrend,
                'weight_change' => $weightChange,
                'height_change' => $heightChange,
                'nutrition_status' => $nutritionStatus,
                'assessments_count' => $assessments->count()
            ];
        });
        
        $stats = [
            'my_children' => $children->count(),
            'total_assessments' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })->count(),
            'recent_assessments' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })->where('created_at', '>=', now()->subMonth())->count(),
            'children_with_growth' => $childrenWithGrowth,
            'recent_assessments_list' => Assessment::whereHas('patient', function($query) use ($parent) {
                $query->where('parent_id', $parent->user_id);
            })
                ->with(['patient', 'nutritionist'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('parent.dashboard', compact('stats'));
    }

    /**
     * Determine nutrition status from assessment
     */
    private function determineNutritionStatus($assessment)
    {
        // Logic to determine nutrition status based on assessment data
        $weightForAge = $assessment->weight_for_age ?? '';
        $heightForAge = $assessment->height_for_age ?? '';
        $bmiForAge = $assessment->bmi_for_age ?? '';
        
        // Check for severe conditions first
        if (stripos($weightForAge, 'severely') !== false || 
            stripos($heightForAge, 'severely') !== false ||
            stripos($bmiForAge, 'severely') !== false) {
            return 'Severe Malnutrition';
        }
        
        // Check for moderate conditions
        if (stripos($weightForAge, 'moderately') !== false || 
            stripos($heightForAge, 'moderately') !== false ||
            stripos($bmiForAge, 'moderately') !== false) {
            return 'Moderate Malnutrition';
        }
        
        // Check for underweight/stunting
        if (stripos($weightForAge, 'underweight') !== false || 
            stripos($heightForAge, 'stunted') !== false ||
            stripos($bmiForAge, 'wasted') !== false) {
            return 'At Risk';
        }
        
        // Check for normal
        if (stripos($weightForAge, 'normal') !== false && 
            stripos($heightForAge, 'normal') !== false &&
            stripos($bmiForAge, 'normal') !== false) {
            return 'Normal';
        }
        
        return 'Assessment Needed';
    }

    /**
     * Show children (patients) of this parent
     */
    public function children()
    {
        $parent = Auth::user();
        $children = Patient::where('parent_id', $parent->user_id)
            ->with(['nutritionist', 'assessments'])
            ->paginate(15);

        return view('parent.children', compact('children'));
    }

    /**
     * Show assessments for parent's children
     */
    public function assessments()
    {
        $parent = Auth::user();
        // Get all children for this parent, eager load assessments and nutritionist
        $children = Patient::where('parent_id', $parent->user_id)
            ->with(['assessments.nutritionist'])
            ->get();

        return view('parent.assessments', compact('children'));
    }

    /**
     * Show parent profile
     */
    public function profile()
    {
        $parent = Auth::user();
        return view('parent.profile', compact('parent'));
    }

        /**
         * Show a specific child of this parent securely
         */
        public function showChild(Request $request, $childId)
        {
            $parent = Auth::user();
            $child = Patient::where('parent_id', $parent->user_id)
                ->where('patient_id', $childId)
                ->with(['nutritionist', 'assessments'])
                ->firstOrFail();

            return view('parent.child', compact('child'));
        }

        /**
         * Show meal plans page
         */
        public function mealPlans()
        {
            $parent = Auth::user();
            $children = Patient::where('parent_id', $parent->user_id)->get();
            
            return view('parent.meal-plans', compact('children'));
        }

        /**
         * Generate meal plan for a child
         */
        public function generateMealPlan(Request $request)
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
                    
                    return back()->with('success', 'Meal plan generated successfully!')
                                ->with('meal_plan', $mealPlan)
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
         * Test API endpoint
         */
        public function testApi()
        {
            return view('parent.test-api');
        }

        /**
         * Test API POST
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
}
