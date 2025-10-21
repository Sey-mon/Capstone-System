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
            $knowledgeBase = KnowledgeBase::with('user')
                ->orderBy('added_at', 'desc')
                ->get();

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

        try {
            $file = $request->file('pdf_file');
            
            // Read file content
            $fileContent = file_get_contents($file->getRealPath());
            
            // Create form data for FastAPI
            $formData = [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $fileContent,
                        'filename' => $file->getClientOriginalName(),
                        'headers' => ['Content-Type' => 'application/pdf']
                    ],
                    [
                        'name' => 'uploaded_by_id',
                        'contents' => Auth::id()
                    ]
                ]
            ];

            // Call FastAPI endpoint
            $client = new \GuzzleHttp\Client(['timeout' => 120]);
            $response = $client->post(config('services.fastapi.base_url') . '/upload_pdf', $formData);
            
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
            $requestData = [
                'kb_ids' => $request->kb_ids ?? null,
                'chunk_size' => $request->chunk_size ?? 1000,
                'overlap' => $request->overlap ?? 200,
                'batch_size' => $request->batch_size ?? 128
            ];

            $client = new \GuzzleHttp\Client(['timeout' => 300]); // 5 minutes timeout
            $response = $client->post(
                config('services.fastapi.base_url') . '/process_embeddings',
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
                'message' => 'Embeddings processed successfully!',
                'data' => $result
            ]);

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
            $requestData = [
                'batch_size' => $request->batch_size ?? 128
            ];

            $client = new \GuzzleHttp\Client(['timeout' => 300]);
            $response = $client->post(
                config('services.fastapi.base_url') . '/reembed_missing',
                [
                    'json' => $requestData,
                    'headers' => ['Content-Type' => 'application/json']
                ]
            );
            
            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('Missing embeddings processed', [
                'user_id' => Auth::id(),
                'result' => $result
            ]);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Re-embedding completed!',
                'data' => $result
            ]);

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
                config('services.fastapi.base_url') . '/embedding_status',
                [
                    'json' => [],
                    'headers' => ['Content-Type' => 'application/json']
                ]
            );
            
            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

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
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            $response = $client->get(config('services.fastapi.base_url') . '/');
            
            $result = json_decode($response->getBody()->getContents(), true);

            return response()->json([
                'success' => true,
                'status' => 'healthy',
                'message' => 'LLM service is running',
                'data' => $result
            ]);

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
