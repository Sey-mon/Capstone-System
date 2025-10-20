<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Services\NutritionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdf\Fpdf;

class KnowledgeBaseController extends Controller
{
    protected $nutritionService;

    public function __construct(NutritionService $nutritionService)
    {
        $this->nutritionService = $nutritionService;
    }

    /**
     * Display knowledge base index
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = KnowledgeBase::with('user');

        if ($search) {
            $query->search($search);
        }

        $knowledgeBase = $query->orderBy('added_at', 'desc')->paginate(12);

        return view('admin.knowledge-base.index', compact('knowledgeBase', 'search'));
    }

    /**
     * Show create knowledge base form
     */
    public function create()
    {
        return view('admin.knowledge-base.create');
    }

    /**
     * Store new knowledge base entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'summary' => 'nullable|string|max:1000',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            $knowledgeBase = KnowledgeBase::create([
                'pdf_name' => $request->input('title'),
                'pdf_text' => $request->input('content'),
                'ai_summary' => $request->input('summary'),
                'user_id' => Auth::id(),
                'added_at' => now(),
            ]);

            // Handle PDF file upload
            if ($request->hasFile('pdf_file')) {
                $this->processPdfFile($request->file('pdf_file'), $knowledgeBase);
            }

            // Try to sync with LLM service
            try {
                $this->syncWithLLMService();
            } catch (\Exception $e) {
                Log::warning('Failed to sync with LLM service after creating knowledge base entry', [
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Knowledge Base entry created', [
                'user_id' => Auth::id(),
                'kb_id' => $knowledgeBase->kb_id,
                'title' => $request->input('title')
            ]);

            return redirect()->route('admin.knowledge-base.index')
                ->with('success', 'Knowledge base entry created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating knowledge base entry: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error creating knowledge base entry. Please try again.');
        }
    }

    /**
     * Show knowledge base entry
     */
    public function show($id)
    {
        $knowledgeBase = KnowledgeBase::with('user')->findOrFail($id);
        return view('admin.knowledge-base.show', compact('knowledgeBase'));
    }

    /**
     * Show edit knowledge base form
     */
    public function edit($id)
    {
        $knowledgeBase = KnowledgeBase::findOrFail($id);
        return view('admin.knowledge-base.edit', compact('knowledgeBase'));
    }

    /**
     * Update knowledge base entry
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'summary' => 'nullable|string|max:1000',
        ]);

        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            $knowledgeBase->update([
                'pdf_name' => $request->input('title'),
                'pdf_text' => $request->input('content'),
                'ai_summary' => $request->input('summary'),
            ]);

            // Try to sync with LLM service
            try {
                $this->syncWithLLMService();
            } catch (\Exception $e) {
                Log::warning('Failed to sync with LLM service after updating knowledge base entry', [
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Knowledge Base entry updated', [
                'user_id' => Auth::id(),
                'kb_id' => $id,
                'title' => $request->input('title')
            ]);

            return redirect()->route('admin.knowledge-base.index')
                ->with('success', 'Knowledge base entry updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating knowledge base entry: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Error updating knowledge base entry. Please try again.');
        }
    }

    /**
     * Delete knowledge base entry
     */
    public function destroy($id)
    {
        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            Log::info('Knowledge Base entry deleted', [
                'user_id' => Auth::id(),
                'kb_id' => $id,
                'title' => $knowledgeBase->pdf_name
            ]);
            
            $knowledgeBase->delete();

            return response()->json([
                'success' => true,
                'message' => 'Knowledge base entry deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting knowledge base entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting knowledge base entry.'
            ], 500);
        }
    }

    /**
     * Upload and process PDF file
     */
    public function uploadPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $file = $request->file('pdf_file');
            $title = $request->input('title') ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            // Extract text from PDF
            $pdfText = $this->extractTextFromPdf($file);

            if (empty(trim($pdfText))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not extract text from PDF. Please ensure the PDF contains readable text.'
                ], 400);
            }

            // Create knowledge base entry
            $knowledgeBase = KnowledgeBase::create([
                'pdf_name' => $title,
                'pdf_text' => $pdfText,
                'ai_summary' => 'PDF content extracted automatically.',
                'user_id' => Auth::id(),
                'added_at' => now(),
            ]);

            // Try to upload to LLM service as well
            try {
                $llmResult = $this->nutritionService->uploadPdf($file->getPathname(), [
                    'title' => $title,
                    'kb_id' => $knowledgeBase->kb_id
                ]);
                
                Log::info('PDF uploaded to LLM service', [
                    'kb_id' => $knowledgeBase->kb_id,
                    'filename' => $file->getClientOriginalName(),
                    'llm_result' => $llmResult
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to upload PDF to LLM service', [
                    'error' => $e->getMessage(),
                    'kb_id' => $knowledgeBase->kb_id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'PDF uploaded and processed successfully.',
                'data' => [
                    'id' => $knowledgeBase->kb_id,
                    'title' => $title,
                    'content' => $pdfText,
                    'word_count' => str_word_count($pdfText),
                    'character_count' => strlen($pdfText),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing PDF upload: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing PDF upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process embeddings for knowledge base
     */
    public function processEmbeddings(Request $request)
    {
        try {
            $result = $this->nutritionService->processEmbeddings();
            
            return response()->json([
                'success' => true,
                'message' => 'Embeddings processing initiated successfully.',
                'result' => $result
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
     * Extract text from PDF file
     */
    private function extractTextFromPdf($file)
    {
        try {
            // Simple text extraction - you might want to use a more sophisticated library
            $content = file_get_contents($file->getPathname());
            
            // Basic PDF text extraction (very simple approach)
            if (preg_match_all('/\((.*?)\)/', $content, $matches)) {
                return implode(' ', $matches[1]);
            }
            
            // Alternative approach using shell command if available
            if (function_exists('shell_exec')) {
                $tempPath = $file->getPathname();
                $text = shell_exec("pdftotext '$tempPath' -");
                if ($text) {
                    return $text;
                }
            }
            
            return 'PDF text extraction not available. Please enter content manually.';
        } catch (\Exception $e) {
            Log::error('PDF text extraction failed: ' . $e->getMessage());
            return 'Error extracting PDF text. Please enter content manually.';
        }
    }

    /**
     * Process PDF file and extract content
     */
    private function processPdfFile($file, $knowledgeBase)
    {
        try {
            // Store the file
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('knowledge-base-pdfs', $fileName, 'public');
            
            // Extract text content
            $extractedText = $this->extractTextFromPdf($file);
            
            if (!empty(trim($extractedText)) && trim($extractedText) !== 'PDF text extraction not available. Please enter content manually.') {
                // Update the knowledge base entry with extracted text if it's better than manual content
                if (strlen($extractedText) > strlen($knowledgeBase->pdf_text)) {
                    $knowledgeBase->update([
                        'pdf_text' => $extractedText
                    ]);
                }
            }
            
            Log::info('PDF file processed', [
                'kb_id' => $knowledgeBase->kb_id,
                'filename' => $fileName,
                'file_path' => $path
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing PDF file: ' . $e->getMessage());
        }
    }

    /**
     * Sync with LLM service
     */
    private function syncWithLLMService()
    {
        try {
            // Get recent knowledge base entries
            $recentEntries = KnowledgeBase::orderBy('added_at', 'desc')
                ->limit(10)
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

            $this->nutritionService->updateKnowledgeBase($recentEntries->toArray());
        } catch (\Exception $e) {
            // Don't throw - just log the error
            Log::warning('LLM service sync failed: ' . $e->getMessage());
        }
    }
}