<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NutritionService
{
    private $baseUrl;
    private $timeout;
    private $maxRetries;
    private $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('services.nutrition_api.base_url');
        $this->timeout = config('services.nutrition_api.timeout', 30);
        $this->maxRetries = config('services.nutrition_api.max_retries', 3);
        $this->retryDelay = config('services.nutrition_api.retry_delay', 1);
    }

    /**
     * Make HTTP request with retry logic
     */
    private function makeRequest($method, $endpoint, $data = [])
    {
        $attempt = 1;
        
        while ($attempt <= $this->maxRetries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->$method($this->baseUrl . $endpoint, $data);

                if ($response->successful()) {
                    return $response;
                }

                Log::warning("API request failed (attempt {$attempt})", [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                if ($attempt === $this->maxRetries) {
                    throw new \Exception("API request failed after {$this->maxRetries} attempts: " . $response->body());
                }

            } catch (\Exception $e) {
                Log::error("API request error (attempt {$attempt})", [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ]);

                if ($attempt === $this->maxRetries) {
                    throw $e;
                }
            }

            $attempt++;
            sleep($this->retryDelay);
        }
    }

    /**
     * Perform nutrition analysis for a patient
     */
    public function analyzeNutrition($patientId)
    {
        try {
            $response = $this->makeRequest('post', '/nutrition/analysis', [
                'patient_id' => $patientId
            ]);

            Log::info('Nutrition analysis completed', [
                'patient_id' => $patientId,
                'status' => 'success'
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Nutrition analysis error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Nutrition analysis error: ' . $e->getMessage());
        }
    }

    /**
     * Generate meal plan for a patient
     */
    public function generateMealPlan($patientId, $availableFoods = null)
    {
        Log::info('Nutrition API base URL', ['base_url' => $this->baseUrl]);
        try {
            $requestData = [
                'patient_id' => $patientId
            ];

            if (!empty($availableFoods)) {
                $requestData['available_foods'] = $availableFoods;
            }

            $response = $this->makeRequest('post', '/generate_meal_plan', $requestData);

            Log::info('Meal plan generated', [
                'patient_id' => $patientId,
                'status' => 'success'
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Meal plan generation error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Meal plan generation error: ' . $e->getMessage());
        }
    }

    /**
     * Generate patient assessment
     */
    public function generateAssessment($patientId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/assessment', [
                    'patient_id' => $patientId
                ]);

            if ($response->successful()) {
                Log::info('Patient assessment generated', [
                    'patient_id' => $patientId,
                    'status' => 'success'
                ]);
                return $response->json();
            } else {
                Log::error('Patient assessment generation failed', [
                    'patient_id' => $patientId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Patient assessment generation failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Patient assessment generation error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Patient assessment generation error: ' . $e->getMessage());
        }
    }

    /**
     * Get foods data from the nutrition API
     */
    public function getFoodsData()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/get_foods_data');

            if ($response->successful()) {
                Log::info('Foods data retrieved successfully');
                return $response->json();
            } else {
                Log::error('Failed to retrieve foods data', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Failed to retrieve foods data: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Foods data retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Foods data retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Get children by parent ID
     */
    public function getChildrenByParent($parentId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/get_children_by_parent', [
                    'parent_id' => $parentId
                ]);

            if ($response->successful()) {
                Log::info('Children data retrieved for parent', [
                    'parent_id' => $parentId
                ]);
                return $response->json();
            } else {
                Log::error('Failed to retrieve children data', [
                    'parent_id' => $parentId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Failed to retrieve children data: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Children data retrieval error', [
                'parent_id' => $parentId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Children data retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Get meal plans by patient ID
     */
    public function getMealPlansByChild($patientId, $mostRecent = false)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/get_meal_plans_by_child', [
                    'patient_id' => $patientId,
                    'most_recent' => $mostRecent
                ]);

            if ($response->successful()) {
                Log::info('Meal plans retrieved for patient', [
                    'patient_id' => $patientId,
                    'most_recent' => $mostRecent
                ]);
                return $response->json();
            } else {
                Log::error('Failed to retrieve meal plans', [
                    'patient_id' => $patientId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Failed to retrieve meal plans: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Meal plans retrieval error', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Meal plans retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Get knowledge base data
     */
    public function getKnowledgeBase()
    {
        try {
            // The endpoint expects a proper request body
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/get_knowledge_base', [
                    'request_type' => 'all'  // Add a parameter to indicate we want all data
                ]);

            if ($response->successful()) {
                Log::info('Knowledge base retrieved successfully');
                return $response->json();
            } else {
                throw new \Exception('Failed to retrieve knowledge base: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Knowledge base retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Knowledge base retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Get meal plan detail by plan ID
     */
    public function getMealPlanDetail($planId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/get_meal_plan_detail', [
                    'plan_id' => $planId
                ]);

            if ($response->successful()) {
                Log::info('Meal plan detail retrieved', [
                    'plan_id' => $planId
                ]);
                return $response->json();
            } else {
                Log::error('Failed to retrieve meal plan detail', [
                    'plan_id' => $planId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Failed to retrieve meal plan detail: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Meal plan detail retrieval error', [
                'plan_id' => $planId,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Meal plan detail retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl . '/docs');

            return $response->status() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Send knowledge base data to LLM for training
     */
    public function updateKnowledgeBase($knowledgeData)
    {
        try {
            // The LLM service expects a POST request with a proper body structure
            // Let's try with an empty object first to see what it returns
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/get_knowledge_base', (object)[]);

            if ($response->successful()) {
                Log::info('Knowledge base retrieved successfully');
                return $response->json();
            } else {
                throw new \Exception('Knowledge base request failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Knowledge base retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Knowledge base retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Query the LLM with a specific question
     */
    public function queryLLM($question, $context = null)
    {
        try {
            $requestData = ['question' => $question];
            
            if ($context) {
                $requestData['context'] = $context;
            }

            $response = $this->makeRequest('post', '/query_llm', $requestData);

            Log::info('LLM query completed', [
                'question_length' => strlen($question),
                'has_context' => !empty($context)
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('LLM query error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('LLM query error: ' . $e->getMessage());
        }
    }

    /**
     * Get LLM model information and status
     */
    public function getModelStatus()
    {
        try {
            // Use the root endpoint which provides service status
            $response = Http::timeout(5)->get($this->baseUrl . '/');
            
            if ($response->successful()) {
                $data = $response->json();
                Log::info('Service status retrieved successfully');
                return [
                    'status' => $data['status'] ?? 'unknown',
                    'message' => $data['message'] ?? 'Service running',
                    'version' => $data['version'] ?? '1.0',
                    'endpoints' => $data['endpoints'] ?? [],
                    'timestamp' => now()->toISOString()
                ];
            }
            
            throw new \Exception('Failed to get service status');
        } catch (\Exception $e) {
            Log::error('Service status retrieval error', [
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => 'Service status unavailable: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Check embedding processing status
     */
    public function getEmbeddingStatus()
    {
        try {
            $response = $this->makeRequest('get', '/embedding_status');
            Log::info('Embedding status retrieved successfully');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Embedding status retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Embedding status retrieval error: ' . $e->getMessage());
        }
    }

    /**
     * Process embeddings for knowledge base
     */
    public function processEmbeddings()
    {
        try {
            $response = $this->makeRequest('post', '/process_embeddings');
            Log::info('Embeddings processing initiated');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Embeddings processing error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Embeddings processing error: ' . $e->getMessage());
        }
    }

    /**
     * Upload PDF to LLM service
     */
    public function uploadPdf($pdfFile, $metadata = [])
    {
        try {
            $response = Http::timeout($this->timeout)
                ->attach('file', file_get_contents($pdfFile), basename($pdfFile))
                ->post($this->baseUrl . '/upload_pdf', $metadata);

            if ($response->successful()) {
                Log::info('PDF uploaded successfully', [
                    'filename' => basename($pdfFile)
                ]);
                return $response->json();
            } else {
                throw new \Exception('PDF upload failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('PDF upload error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('PDF upload error: ' . $e->getMessage());
        }
    }

    /**
     * Generate nutrition recommendations using LLM
     */
    public function generateNutritionRecommendations($patientData, $customPrompt = null)
    {
        try {
            $requestData = ['patient_data' => $patientData];
            
            if ($customPrompt) {
                $requestData['custom_prompt'] = $customPrompt;
            }

            $response = $this->makeRequest('post', '/generate_recommendations', $requestData);

            Log::info('Nutrition recommendations generated', [
                'patient_data_size' => strlen(json_encode($patientData))
            ]);
            
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Nutrition recommendations generation error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Nutrition recommendations generation error: ' . $e->getMessage());
        }
    }
}
