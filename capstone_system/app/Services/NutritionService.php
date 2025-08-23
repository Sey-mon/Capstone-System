<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NutritionService
{
    private $baseUrl;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.nutrition_api.base_url');
        $this->timeout = config('services.nutrition_api.timeout');
    }

    /**
     * Perform nutrition analysis for a patient
     */
    public function analyzeNutrition($patientId)
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/nutrition/analysis', [
                    'patient_id' => $patientId
                ]);

            if ($response->successful()) {
                Log::info('Nutrition analysis completed', [
                    'patient_id' => $patientId,
                    'status' => 'success'
                ]);
                return $response->json();
            } else {
                Log::error('Nutrition analysis failed', [
                    'patient_id' => $patientId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Nutrition analysis failed: ' . $response->body());
            }
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
        try {
            $requestData = [
                'patient_id' => $patientId
            ];

            if (!empty($availableFoods)) {
                $requestData['available_foods'] = $availableFoods;
            }

            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/generate_meal_plan', $requestData);

            if ($response->successful()) {
                Log::info('Meal plan generated', [
                    'patient_id' => $patientId,
                    'status' => 'success'
                ]);
                return $response->json();
            } else {
                Log::error('Meal plan generation failed', [
                    'patient_id' => $patientId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Meal plan generation failed: ' . $response->body());
            }
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
            $response = Http::timeout($this->timeout)
                ->post($this->baseUrl . '/get_knowledge_base');

            if ($response->successful()) {
                Log::info('Knowledge base retrieved successfully');
                return $response->json();
            } else {
                Log::error('Failed to retrieve knowledge base', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
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
}
