<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Services\NutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LLMIntegrationController extends Controller
{
    protected $nutritionService;

    public function __construct(NutritionService $nutritionService)
    {
        $this->nutritionService = $nutritionService;
    }

    /**
     * Dashboard for LLM integration management
     */
    public function dashboard()
    {
        try {
            $modelStatus = Cache::remember('llm_model_status', 300, function () {
                return $this->nutritionService->getModelStatus();
            });

            $stats = [
                'total_knowledge_entries' => KnowledgeBase::count(),
                'entries_this_month' => KnowledgeBase::whereMonth('added_at', now()->month)->count(),
                'model_status' => $modelStatus,
                'api_connection' => $this->nutritionService->testConnection(),
            ];

            return view('admin.llm.dashboard', compact('stats'));
        } catch (\Exception $e) {
            Log::error('LLM Dashboard error: ' . $e->getMessage());
            
            $stats = [
                'total_knowledge_entries' => KnowledgeBase::count(),
                'entries_this_month' => KnowledgeBase::whereMonth('added_at', now()->month)->count(),
                'model_status' => ['status' => 'error', 'message' => 'Unable to connect to LLM service'],
                'api_connection' => false,
            ];

            return view('admin.llm.dashboard', compact('stats'));
        }
    }

    /**
     * Sync knowledge base with LLM
     */
    public function syncKnowledgeBase(Request $request)
    {
        try {
            $limit = $request->get('limit', 100);
            $knowledgeData = KnowledgeBase::orderBy('added_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function($entry) {
                    return [
                        'id' => $entry->kb_id,
                        'title' => $entry->pdf_name,
                        'content' => $entry->pdf_text,
                        'summary' => $entry->ai_summary,
                        'created_at' => $entry->added_at->toISOString(),
                    ];
                });

            $result = $this->nutritionService->updateKnowledgeBase($knowledgeData->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Knowledge base synchronized successfully',
                'synced_entries' => $knowledgeData->count(),
                'result' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Knowledge base sync error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error synchronizing knowledge base: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query the LLM with a question
     */
    public function queryLLM(Request $request)
    {
        $request->validate([
            'question' => 'required|string|min:5|max:500',
            'context' => 'nullable|string|max:2000',
        ]);

        try {
            $result = $this->nutritionService->queryLLM(
                $request->question,
                $request->context
            );

            Log::info('LLM query executed', [
                'user_id' => Auth::id(),
                'question_length' => strlen($request->question),
                'has_context' => !empty($request->context)
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'query_info' => [
                    'question' => $request->question,
                    'timestamp' => now()->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('LLM query error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error querying LLM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate nutrition recommendations for a patient
     */
    public function generateRecommendations(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|integer|exists:patients,patient_id',
            'custom_prompt' => 'nullable|string|max:1000',
        ]);

        try {
            // Get patient data (you'll need to implement this based on your Patient model)
            $patientData = $this->getPatientData($request->patient_id);

            $result = $this->nutritionService->generateNutritionRecommendations(
                $patientData,
                $request->custom_prompt
            );

            Log::info('Nutrition recommendations generated', [
                'user_id' => Auth::id(),
                'patient_id' => $request->patient_id,
                'has_custom_prompt' => !empty($request->custom_prompt)
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'patient_id' => $request->patient_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Nutrition recommendations generation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating recommendations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test LLM connection and model status
     */
    public function testConnection()
    {
        try {
            $connectionTest = $this->nutritionService->testConnection();
            $modelStatus = $this->nutritionService->getModelStatus();

            return response()->json([
                'success' => true,
                'connection' => $connectionTest,
                'model_status' => $modelStatus,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connection' => false,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Get comprehensive LLM health check
     */
    public function healthCheck()
    {
        $health = [
            'api_connection' => false,
            'model_status' => null,
            'knowledge_base_count' => 0,
            'last_sync' => null,
            'recommendations' => []
        ];

        try {
            // Test API connection
            $health['api_connection'] = $this->nutritionService->testConnection();

            // Get model status
            if ($health['api_connection']) {
                $health['model_status'] = $this->nutritionService->getModelStatus();
            }

            // Knowledge base stats
            $health['knowledge_base_count'] = KnowledgeBase::count();
            $health['last_knowledge_entry'] = KnowledgeBase::latest('added_at')->first()?->added_at;

            // Health recommendations
            if (!$health['api_connection']) {
                $health['recommendations'][] = 'LLM API service is not reachable. Check if the service is running on ' . config('services.nutrition_api.base_url');
            }

            if ($health['knowledge_base_count'] < 10) {
                $health['recommendations'][] = 'Consider adding more knowledge base entries for better LLM performance';
            }

            return response()->json([
                'success' => true,
                'health' => $health,
                'overall_status' => $health['api_connection'] ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            $health['recommendations'][] = 'Error during health check: ' . $e->getMessage();
            
            return response()->json([
                'success' => false,
                'health' => $health,
                'overall_status' => 'error',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Helper method to get patient data
     */
    private function getPatientData($patientId)
    {
        // You'll need to implement this based on your Patient model structure
        // This is a placeholder - adjust according to your Patient model
        $patient = \App\Models\Patient::findOrFail($patientId);
        
        return [
            'id' => $patient->patient_id,
            'age' => $patient->age ?? null,
            'gender' => $patient->gender ?? null,
            'weight' => $patient->weight ?? null,
            'height' => $patient->height ?? null,
            // Add other relevant patient data fields
        ];
    }
}