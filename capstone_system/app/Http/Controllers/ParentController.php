<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Assessment;
use App\Models\User;
use App\Models\MealPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     * Verify child using Patient ID and Birthdate, return masked preview
     */
    public function previewChildByCode(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|string',
            'birthdate' => 'required|date',
        ]);

        try {
            // Find child by custom_patient_id and birthdate
            $child = Patient::where('custom_patient_id', $validated['patient_code'])
                ->whereDate('birthdate', $validated['birthdate'])
                ->with(['barangay'])
                ->first();

            if (!$child) {
                return response()->json([
                    'success' => false,
                    'message' => 'No child found with the provided information. Please verify the Patient ID and Birthdate.'
                ], 404);
            }

            // Check if child is already linked to a parent
            if ($child->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'This child is already linked to a parent account.'
                ], 400);
            }

            // Create masked preview data
            $maskedData = [
                'patient_id' => $child->patient_id,
                'custom_patient_id' => $child->custom_patient_id,
                'first_name_masked' => $this->maskString($child->first_name),
                'middle_name_masked' => $child->middle_name ? $this->maskString($child->middle_name) : null,
                'last_name_masked' => $this->maskString($child->last_name),
                'sex' => $child->sex,
                'age_months' => $child->age_months,
                'barangay_masked' => $child->barangay ? $this->maskString($child->barangay->barangay_name, 3) : null,
            ];

            return response()->json([
                'success' => true,
                'child' => $maskedData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying child information.'
            ], 500);
        }
    }

    /**
     * Mask a string for privacy (show first character and mask the rest)
     */
    private function maskString($string, $showChars = 1)
    {
        if (empty($string)) {
            return '';
        }
        
        $length = mb_strlen($string);
        if ($length <= $showChars) {
            return $string;
        }
        
        return mb_substr($string, 0, $showChars) . str_repeat('*', min($length - $showChars, 3));
    }

    /**
     * Link a child to parent using unique patient code (after confirmation)
     */
    public function linkChildByCode(Request $request)
    {
        $parent = Auth::user();
        
        $validated = $request->validate([
            'patient_id' => 'required|integer',
        ]);

        try {
            // Find child by patient_id
            $child = Patient::where('patient_id', $validated['patient_id'])
                ->first();

            if (!$child) {
                return response()->json([
                    'success' => false,
                    'message' => 'Child not found.'
                ], 404);
            }

            // Check if child is already linked to a parent
            if ($child->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'This child is already linked to a parent account.'
                ], 400);
            }

            // Link the child to the parent
            $child->parent_id = $parent->user_id;
            $child->save();

            return response()->json([
                'success' => true,
                'message' => "Successfully linked {$child->first_name} {$child->last_name} to your account!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while linking the child. Please try again later.'
            ], 500);
        }
    }

    /**
     * Unlink a child from parent account
     */
    public function unlinkChild(Request $request)
    {
        $parent = Auth::user();
        
        $validated = $request->validate([
            'patient_id' => 'required|integer',
        ]);

        try {
            // Find child and verify ownership
            $child = Patient::where('patient_id', $validated['patient_id'])
                ->where('parent_id', $parent->user_id)
                ->first();

            if (!$child) {
                return response()->json([
                    'success' => false,
                    'message' => 'Child not found or not linked to your account.'
                ], 404);
            }

            // Unlink the child
            $childName = $child->first_name . ' ' . $child->last_name;
            $child->parent_id = null;
            $child->save();

            return response()->json([
                'success' => true,
                'message' => "Successfully unlinked {$childName} from your account."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while unlinking the child.'
            ], 500);
        }
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
                $query->orderBy('created_at', 'asc');
            }])
            ->get();

        // Calculate growth trends for each child
        $childrenWithGrowth = $children->map(function($child) {
            $assessments = $child->assessments;
            $latestAssessment = $assessments->last();
            $previousAssessment = $assessments->count() > 1 ? $assessments->get($assessments->count() - 2) : null;
            
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
            
            // Prepare assessment history for line chart
            $assessmentHistory = $assessments->map(function($assessment) {
                return [
                    'date' => $assessment->assessment_date->format('M d, Y'),
                    'weight' => (float) $assessment->weight_kg,
                    'height' => (float) $assessment->height_cm,
                ];
            })->values();
            
            return [
                'child' => $child,
                'latest_assessment' => $latestAssessment,
                'growth_trend' => $growthTrend,
                'weight_change' => $weightChange,
                'height_change' => $heightChange,
                'nutrition_status' => $nutritionStatus,
                'assessments_count' => $assessments->count(),
                'assessment_history' => $assessmentHistory
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
         * View all meal plans for the parent's children
         */
        public function viewMealPlans()
        {
            $parent = Auth::user();
            $children = Patient::where('parent_id', $parent->user_id)->get();
            $childrenIds = $children->pluck('patient_id');
            
            // Get all meal plans for the parent's children, paginated
            $mealPlans = MealPlan::with('patient')
                ->whereIn('patient_id', $childrenIds)
                ->orderBy('generated_at', 'desc')
                ->paginate(9);
            
            return view('parent.view-meal-plans', compact('mealPlans', 'children'));
        }

        /**
         * Get a single meal plan details (for AJAX)
         */
        public function getMealPlanDetails($planId)
        {
            $parent = Auth::user();
            $childrenIds = Patient::where('parent_id', $parent->user_id)->pluck('patient_id');
            
            $plan = MealPlan::with('patient')
                ->whereIn('patient_id', $childrenIds)
                ->where('plan_id', $planId)
                ->first();
            
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meal plan not found or access denied'
                ], 404);
            }
            
            // Parse the meal plan into structured format
            $structuredPlan = $this->parseMealPlanToWeeklyFormat($plan->plan_details);
            
            return response()->json([
                'success' => true,
                'plan' => [
                    'patient_name' => $plan->patient->first_name . ' ' . $plan->patient->last_name,
                    'generated_at' => $plan->generated_at->format('F d, Y'),
                    'notes' => $plan->notes,
                    'plan_details' => $plan->plan_details,
                    'weekly_format' => $structuredPlan
                ]
            ]);
        }
        
        /**
         * Parse meal plan HTML into weekly table format
         */
        private function parseMealPlanToWeeklyFormat($planDetailsHtml)
        {
            $days = ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'];
            $meals = [
                'Breakfast' => [], 
                'Lunch' => [], 
                'PM Snack' => [],
                'Dinner' => []
            ];
            
            // Strip HTML tags to get plain text
            $plainText = strip_tags($planDetailsHtml);
            
            // Initialize structure with empty values
            foreach ($days as $day) {
                foreach ($meals as $mealType => $value) {
                    $meals[$mealType][$day] = '-';
                }
            }
            
            // Parse each day section with more flexible patterns
            for ($i = 1; $i <= 7; $i++) {
                $dayKey = "Day $i";
                
                // Multiple patterns to match different day formats
                $dayPatterns = [
                    '/\*\*Day\s+' . $i . '\*\*:?\s*(.*?)(?=\*\*Day\s+\d+\*\*:|REGULAR\s+NA\s+OBSERBAHAN|BALANSENG\s+PAGKAIN|$)/is',
                    '/Day\s+' . $i . '\s*[:\-]?\s*(.*?)(?=Day\s+\d+|REGULAR\s+NA|BALANSENG|$)/is',
                    '/\b' . $i . '\.\s*(.*?)(?=\d+\.|REGULAR\s+NA|BALANSENG|$)/is'
                ];
                
                $dayContent = '';
                foreach ($dayPatterns as $pattern) {
                    if (preg_match($pattern, $plainText, $dayMatches)) {
                        $dayContent = $dayMatches[1];
                        break;
                    }
                }
                
                if (!empty($dayContent)) {
                    // Extract Breakfast with multiple pattern attempts
                    $breakfastPatterns = [
                        '/(?:\*\*)?Breakfast\s*\(Almusal\)\*\*:?\s*([^*\n]+?)(?=\*\*Lunch|\*\*Snack|\*\*Dinner|Lunch\s*\(|Snack\s*\(|Dinner\s*\(|$)/is',
                        '/(?:\*\*)?Breakfast\*\*:?\s*([^*\n]+?)(?=\*\*Lunch|\*\*Snack|\*\*Dinner|Lunch:|Snack:|Dinner:|$)/is',
                        '/Almusal[:\-]?\s*([^\n]+?)(?=Tanghalian|Meryenda|Hapunan|Lunch|Snack|Dinner|$)/is'
                    ];
                    
                    foreach ($breakfastPatterns as $pattern) {
                        if (preg_match($pattern, $dayContent, $breakfastMatch)) {
                            $breakfast = $this->cleanMealText($breakfastMatch[1]);
                            if (!empty($breakfast)) {
                                $meals['Breakfast'][$dayKey] = $breakfast;
                                break;
                            }
                        }
                    }
                    
                    // Extract Lunch with multiple pattern attempts
                    $lunchPatterns = [
                        '/(?:\*\*)?Lunch\s*\(Tanghalian\)\*\*:?\s*([^*\n]+?)(?=\*\*Snack|\*\*Dinner|Snack\s*\(|Dinner\s*\(|$)/is',
                        '/(?:\*\*)?Lunch\*\*:?\s*([^*\n]+?)(?=\*\*Snack|\*\*Dinner|Snack:|Dinner:|$)/is',
                        '/Tanghalian[:\-]?\s*([^\n]+?)(?=Meryenda|Hapunan|Snack|Dinner|$)/is'
                    ];
                    
                    foreach ($lunchPatterns as $pattern) {
                        if (preg_match($pattern, $dayContent, $lunchMatch)) {
                            $lunch = $this->cleanMealText($lunchMatch[1]);
                            if (!empty($lunch)) {
                                $meals['Lunch'][$dayKey] = $lunch;
                                break;
                            }
                        }
                    }
                    
                    // Extract PM Snack with multiple pattern attempts
                    $snackPatterns = [
                        '/(?:\*\*)?Snack\s*\(Meryenda\)\*\*:?\s*([^*\n]+?)(?=\*\*Dinner|Dinner\s*\(|$)/is',
                        '/(?:\*\*)?Snack\*\*:?\s*([^*\n]+?)(?=\*\*Dinner|Dinner:|$)/is',
                        '/Meryenda[:\-]?\s*([^\n]+?)(?=Hapunan|Dinner|$)/is',
                        '/(?:\*\*)?PM\s*Snack\*\*:?\s*([^*\n]+?)(?=\*\*Dinner|Dinner:|$)/is'
                    ];
                    
                    foreach ($snackPatterns as $pattern) {
                        if (preg_match($pattern, $dayContent, $snackMatch)) {
                            $snack = $this->cleanMealText($snackMatch[1]);
                            if (!empty($snack)) {
                                $meals['PM Snack'][$dayKey] = $snack;
                                break;
                            }
                        }
                    }
                    
                    // Extract Dinner with multiple pattern attempts
                    $dinnerPatterns = [
                        '/(?:\*\*)?Dinner\s*\(Hapunan\)\*\*:?\s*([^*\n]+?)(?=\*\*Day\s+\d+|Day\s+\d+|REGULAR\s+NA|BALANSENG|$)/is',
                        '/(?:\*\*)?Dinner\*\*:?\s*([^*\n]+?)(?=\*\*Day\s+\d+|Day\s+\d+|REGULAR|BALANSENG|$)/is',
                        '/Hapunan[:\-]?\s*([^\n]+?)(?=Day\s+\d+|REGULAR|BALANSENG|$)/is'
                    ];
                    
                    foreach ($dinnerPatterns as $pattern) {
                        if (preg_match($pattern, $dayContent, $dinnerMatch)) {
                            $dinner = $this->cleanMealText($dinnerMatch[1]);
                            if (!empty($dinner)) {
                                $meals['Dinner'][$dayKey] = $dinner;
                                break;
                            }
                        }
                    }
                }
            }
            
            return [
                'days' => $days,
                'meals' => $meals
            ];
        }
        
        /**
         * Clean and format meal text for display
         */
        private function cleanMealText($text)
        {
            // Remove extra whitespace and line breaks
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);
            
            // Remove leading/trailing dashes, asterisks, or colons
            $text = trim($text, " \t\n\r\0\x0B-:*");
            
            // Remove markdown formatting (**, *, -, etc.)
            $text = str_replace('**', '', $text);
            $text = preg_replace('/^\s*[\*\-]+\s*/', '', $text);
            
            // Remove benefit descriptions (text after " - " that starts with common Filipino descriptors)
            $text = preg_replace('/\s*-\s*(?:Mayaman|Provides|Good source|Rich in|Helps|Contains).*$/i', '', $text);
            
            // Remove nutritional information in parentheses
            $text = preg_replace('/\s*\([^\)]*(?:kcal|calories|grams|protein|vitamins)\s*[^\)]*\)\s*/i', '', $text);
            
            // Remove any remaining leading/trailing punctuation
            $text = trim($text, " \t\n\r\0\x0B-:*.,;");
            
            // If text is too long, truncate intelligently
            if (strlen($text) > 150) {
                // Try to cut at a sentence or phrase boundary
                $text = substr($text, 0, 147);
                $lastSpace = strrpos($text, ' ');
                if ($lastSpace !== false && $lastSpace > 100) {
                    $text = substr($text, 0, $lastSpace);
                }
                $text .= '...';
            }
            
            // Return empty string if only dashes or whitespace remain
            if (preg_match('/^[\s\-\.]+$/', $text)) {
                return '';
            }
            
            return $text;
        }

        /**
         * Download meal plan as PDF
         */
        public function downloadMealPlan($planId)
        {
            $parent = Auth::user();
            $childrenIds = Patient::where('parent_id', $parent->user_id)->pluck('patient_id');
            
            $plan = MealPlan::with('patient')
                ->whereIn('patient_id', $childrenIds)
                ->where('plan_id', $planId)
                ->first();
            
            if (!$plan) {
                abort(404, 'Meal plan not found');
            }
            
            // Parse meal plan into structured data
            $parsedMeals = $this->parseMealPlanForPDF($plan->plan_details);
            
            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('parent.meal-plan-pdf', compact('plan', 'parsedMeals'));
            
            $filename = 'meal-plan-' . $plan->patient->first_name . '-' . $plan->generated_at->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        }
        
        /**
         * Parse meal plan text into structured array for PDF table
         */
        private function parseMealPlanForPDF($mealPlanText)
        {
            $meals = [
                'breakfast' => [],
                'lunch' => [],
                'snack' => [],
                'dinner' => []
            ];
            
            // Find all day sections
            preg_match_all('/(?:\*\*|📅)?\s*Day\s+(\d+)(?:\*\*)?\s*(?:\([^)]*\))?\s*:?/i', $mealPlanText, $dayMatches, PREG_OFFSET_CAPTURE);
            
            $days = [];
            foreach ($dayMatches[1] as $index => $match) {
                $days[] = [
                    'number' => (int)$match[0],
                    'position' => $dayMatches[0][$index][1]
                ];
            }
            
            // Extract meals for each day
            for ($i = 0; $i < count($days); $i++) {
                $dayNumber = $days[$i]['number'];
                $startPos = $days[$i]['position'];
                $endPos = ($i < count($days) - 1) ? $days[$i + 1]['position'] : strlen($mealPlanText);
                $dayContent = substr($mealPlanText, $startPos, $endPos - $startPos);
                
                // Extract breakfast
                if (preg_match('/(?:🍳|🥞)?\s*(?:\*\*)?(?:Breakfast|Almusal)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i', $dayContent, $match)) {
                    $meals['breakfast'][$dayNumber - 1] = $this->cleanMealText($match[1]);
                }
                
                // Extract lunch
                if (preg_match('/(?:🍽️|🍲)?\s*(?:\*\*)?(?:Lunch|Tanghalian)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i', $dayContent, $match)) {
                    $meals['lunch'][$dayNumber - 1] = $this->cleanMealText($match[1]);
                }
                
                // Extract snack
                if (preg_match('/(?:🍪|🥤)?\s*(?:\*\*)?(?:PM\s+Snack|Snack|Meryenda)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i', $dayContent, $match)) {
                    $meals['snack'][$dayNumber - 1] = $this->cleanMealText($match[1]);
                }
                
                // Extract dinner
                if (preg_match('/(?:🌙|🍴)?\s*(?:\*\*)?(?:Dinner|Hapunan)(?:\*\*)?[^\n]*?[:\n]\s*([^\n-]+)/i', $dayContent, $match)) {
                    $meals['dinner'][$dayNumber - 1] = $this->cleanMealText($match[1]);
                }
            }
            
            return $meals;
        }

        /**
         * Delete a meal plan
         */
        public function deleteMealPlan($planId)
        {
            $parent = Auth::user();
            $childrenIds = Patient::where('parent_id', $parent->user_id)->pluck('patient_id');
            
            $plan = MealPlan::whereIn('patient_id', $childrenIds)
                ->where('plan_id', $planId)
                ->first();
            
            if (!$plan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meal plan not found or access denied'
                ], 404);
            }
            
            $plan->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Meal plan deleted successfully'
            ]);
        }





    /**
     * Update parent profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Manual validation for better AJAX error handling
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'sex' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Update user using DB query
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'contact_number' => $validated['contact_number'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'sex' => $validated['sex'] ?? null,
                'address' => $validated['address'] ?? null,
                'updated_at' => now(),
            ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!'
            ]);
        }

        return redirect()->route('parent.profile')->with('success', 'Profile updated successfully!');
    }    /**
     * Update parent password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        // Manual validation for better AJAX error handling with strong password requirements
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&#).'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        // Check if current password is correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 422);
            }
            return redirect()->route('parent.profile')->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update the password using DB query
        DB::table('users')
            ->where('user_id', $user->user_id)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
        }

        return redirect()->route('parent.profile')->with('success', 'Password updated successfully!');
    }
}
