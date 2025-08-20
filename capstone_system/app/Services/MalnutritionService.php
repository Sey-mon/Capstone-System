<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MalnutritionService
{
    private $baseUrl;
    private $apiKey;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.malnutrition_api.base_url');
        $this->apiKey = config('services.malnutrition_api.api_key');
        $this->timeout = config('services.malnutrition_api.timeout');
    }

    /**
     * Get authentication token for API
     */
    private function getToken()
    {
        $token = Cache::get('malnutrition_token');
        
        if (!$token) {
            try {
                $response = Http::timeout($this->timeout)
                    ->post($this->baseUrl . '/auth/token', [
                        'api_key' => $this->apiKey
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    $token = $data['access_token'];
                    // Cache token for 25 minutes (expires in 30)
                    Cache::put('malnutrition_token', $token, 25 * 60);
                    Log::info('Malnutrition API token refreshed');
                } else {
                    Log::error('Failed to get malnutrition API token', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    throw new \Exception('Failed to authenticate with malnutrition API');
                }
            } catch (\Exception $e) {
                Log::error('Malnutrition API authentication error', [
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Cannot connect to malnutrition assessment API: ' . $e->getMessage());
            }
        }
        
        return $token;
    }

    /**
     * Perform complete assessment with treatment plan
     */
    public function assessChild($childData, $socioData = [])
    {
        $token = $this->getToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->post($this->baseUrl . '/assess/complete', [
                    'child_data' => $childData,
                    'socioeconomic_data' => $socioData
                ]);

            if ($response->successful()) {
                Log::info('Malnutrition assessment completed', [
                    'child_age' => $childData['age_months'] ?? 'unknown',
                    'diagnosis' => $response->json()['assessment']['primary_diagnosis'] ?? 'unknown'
                ]);
                return $response->json();
            } else {
                Log::error('Malnutrition assessment failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new \Exception('Assessment failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Malnutrition assessment error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Assessment error: ' . $e->getMessage());
        }
    }

    /**
     * Quick malnutrition assessment only
     */
    public function assessMalnutritionOnly($childData)
    {
        $token = $this->getToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->post($this->baseUrl . '/assess/malnutrition-only', $childData);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('Quick assessment failed: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Quick malnutrition assessment error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Quick assessment error: ' . $e->getMessage());
        }
    }

    /**
     * Get WHO standards reference data
     */
    public function getWhoStandards($gender, $indicator)
    {
        $token = $this->getToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get($this->baseUrl . "/reference/who-standards/{$gender}/{$indicator}");

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('Failed to get WHO standards: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WHO standards retrieval error', [
                'error' => $e->getMessage(),
                'gender' => $gender,
                'indicator' => $indicator
            ]);
            throw new \Exception('WHO standards error: ' . $e->getMessage());
        }
    }

    /**
     * Get treatment protocols
     */
    public function getTreatmentProtocols()
    {
        $token = $this->getToken();
        
        try {
            $response = Http::timeout($this->timeout)
                ->withToken($token)
                ->get($this->baseUrl . '/reference/treatment-protocols');

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('Failed to get treatment protocols: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Treatment protocols retrieval error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Treatment protocols error: ' . $e->getMessage());
        }
    }

    /**
     * Check API health status
     */
    public function checkApiHealth()
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl . '/health');

            if ($response->successful()) {
                return $response->json();
            } else {
                return [
                    'status' => 'error',
                    'message' => 'API returned status: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error('API health check failed', [
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'error',
                'message' => 'Cannot connect to API: ' . $e->getMessage()
            ];
        }
    }
}
