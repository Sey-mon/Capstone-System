<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseController extends Controller
{
    /**
     * Display Knowledge Base Management page
     */
    public function index()
    {
        try {
            // Get knowledge base from local database
            $knowledgeBase = KnowledgeBase::with('user')
                ->orderBy('added_at', 'desc')
                ->get();

            // Check if LLM API is configured
            $llmApiAvailable = !empty(config('services.nutrition_api.base_url'));
            
            // Optionally, you can also fetch from the API to sync data
            if ($llmApiAvailable) {
                try {
                    $client = new \GuzzleHttp\Client(['timeout' => 30]);
                    $response = $client->post(
                        config('services.nutrition_api.base_url') . '/get_knowledge_base',
                        [
                            'json' => new \stdClass(), // Empty object for Pydantic validation
                            'headers' => ['Content-Type' => 'application/json']
                        ]
                    );
                    $apiResult = json_decode($response->getBody()->getContents(), true);
                    // You could use this to sync or display additional information
                    // For now, we'll just use the local database data
                } catch (\Exception $e) {
                    Log::warning('Could not fetch knowledge base from API: ' . $e->getMessage());
                    // Continue with local data
                }
            }

            return view('admin.knowledge-base', compact('knowledgeBase'));
        } catch (\Exception $e) {
            Log::error('Error loading knowledge base: ' . $e->getMessage());
            return back()->with('error', 'Error loading knowledge base.');
        }
    }

    /**
     * Upload PDF to Knowledge Base
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Check if LLM API is configured
        if (empty(config('services.nutrition_api.base_url'))) {
            return response()->json([
                'success' => false,
                'message' => 'AI Knowledge Base service is not configured. Please configure the Python API.'
            ], 503);
        }

        try {
            $file = $request->file('pdf_file');
            
            // Prepare multipart form data for FastAPI
            $client = new \GuzzleHttp\Client(['timeout' => 120]);
            
            $response = $client->post(config('services.nutrition_api.base_url') . '/upload_pdf', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                    [
                        'name' => 'uploaded_by_id',
                        'contents' => (string) Auth::id()
                    ]
                ]
            ]);
            
            $result = json_decode($response->getBody()->getContents(), true);

            // Log the action
            Log::info('PDF uploaded to knowledge base', [
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'kb_id' => $result['kb_id'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PDF uploaded successfully!',
                'data' => $result
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = 'Upload failed';
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $errorBody['detail'] ?? $errorMessage;
            }
            Log::error('Error uploading PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error uploading PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process embeddings for all or specific knowledge base entries
     */
    public function processEmbeddings(Request $request)
    {
        try {
            // Prepare request data matching FastAPI spec
            // Send empty object if no parameters, FastAPI expects valid JSON
            $requestData = new \stdClass();
            
            // Only include fields if they are provided
            if ($request->has('kb_ids') && !empty($request->kb_ids)) {
                $requestData->kb_ids = $request->kb_ids;
            }
            if ($request->has('chunk_size')) {
                $requestData->chunk_size = (int) $request->chunk_size;
            }
            if ($request->has('overlap')) {
                $requestData->overlap = (int) $request->overlap;
            }
            if ($request->has('batch_size')) {
                $requestData->batch_size = (int) $request->batch_size;
            }

            $client = new \GuzzleHttp\Client(['timeout' => 300]); // 5 minutes timeout
            $response = $client->post(
                config('services.nutrition_api.base_url') . '/process_embeddings',
                [
                    'json' => $requestData,
                    'headers' => ['Content-Type' => 'application/json']
                ]
            );
            
            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Embeddings processed', [
                'user_id' => Auth::id(),
                'stats' => $result['stats'] ?? []
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Embeddings processed successfully!',
                'data' => $result
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = 'Processing failed';
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $errorBody['detail'] ?? $errorMessage;
                Log::error('FastAPI Error Response: ' . json_encode($errorBody));
            }
            Log::error('Error processing embeddings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error processing embeddings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing embeddings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Re-embed only missing PDFs
     */
    public function reembedMissing(Request $request)
    {
        try {
            // Prepare request data - send empty object if no batch_size
            $requestData = new \stdClass();
            if ($request->has('batch_size')) {
                $requestData->batch_size = (int) $request->batch_size;
            }

            $client = new \GuzzleHttp\Client(['timeout' => 300]);
            $response = $client->post(
                config('services.nutrition_api.base_url') . '/reembed_missing',
                [
                    'json' => $requestData,
                    'headers' => ['Content-Type' => 'application/json']
                ]
            );
            
            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Missing embeddings processed', [
                'user_id' => Auth::id(),
                'status' => $result['status'] ?? 'unknown'
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Re-embedding completed!',
                'data' => $result
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = 'Re-embedding failed';
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $errorBody['detail'] ?? $errorMessage;
                Log::error('FastAPI Error Response: ' . json_encode($errorBody));
            }
            Log::error('Error re-embedding missing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error re-embedding missing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error re-embedding: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check embedding status
     */
    public function checkEmbeddingStatus()
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 30]);
            $response = $client->post(
                config('services.nutrition_api.base_url') . '/embedding_status',
                [
                    'json' => new \stdClass(), // Empty object for Pydantic validation
                    'headers' => ['Content-Type' => 'application/json']
                ]
            );
            
            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = 'Status check failed';
            if ($e->hasResponse()) {
                $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                $errorMessage = $errorBody['detail'] ?? $errorMessage;
                Log::error('FastAPI Error Response: ' . json_encode($errorBody));
            }
            Log::error('Error checking embedding status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error checking embedding status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check LLM service health
     */
    public function checkLlmHealth()
    {
        // Check if LLM API is configured
        if (empty(config('services.nutrition_api.base_url'))) {
            return response()->json([
                'success' => false,
                'status' => 'disabled',
                'message' => 'AI Knowledge Base service is not configured yet'
            ], 200);
        }

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get(config('services.nutrition_api.base_url') . '/');
            
            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'success' => true,
                'status' => 'healthy',
                'message' => $result['message'] ?? 'LLM service is running',
                'data' => $result
            ]);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('LLM health check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 'unhealthy',
                'message' => 'LLM service is not responding'
            ], 503);
        } catch (\Exception $e) {
            Log::error('LLM health check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'status' => 'unhealthy',
                'message' => 'LLM service is not responding: ' . $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get document summary
     */
    public function getSummary($id)
    {
        try {
            $kb = KnowledgeBase::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'summary' => $kb->ai_summary ?? 'No summary available for this document.',
                'pdf_name' => $kb->pdf_name,
                'added_at' => $kb->added_at->format('M d, Y'),
                'uploaded_by' => $kb->user ? $kb->user->first_name . ' ' . $kb->user->last_name : 'System'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching document summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching document summary.'
            ], 500);
        }
    }

    /**
     * Delete knowledge base entry
     */
    public function destroy($id)
    {
        try {
            $kb = KnowledgeBase::findOrFail($id);
            
            Log::info('Knowledge base entry deleted', [
                'user_id' => Auth::id(),
                'kb_id' => $id,
                'pdf_name' => $kb->pdf_name
            ]);
            
            $kb->delete();

            return response()->json([
                'success' => true,
                'message' => 'Knowledge base entry deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting knowledge base: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting entry.'
            ], 500);
        }
    }
}
