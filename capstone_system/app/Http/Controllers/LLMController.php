<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class LLMController extends Controller
{
    /**
     * Display LLM knowledge base index
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        $query = KnowledgeBase::with('user');

        if ($search) {
            $query->search($search);
        }

        if ($category) {
            // Future enhancement for categories
            $query->where('category', $category);
        }

        $knowledgeBase = $query->orderBy('added_at', 'desc')->paginate(12);

        return view('admin.llm.index', compact('knowledgeBase', 'search', 'category'));
    }

    /**
     * Show create knowledge base form
     */
    public function create()
    {
        return view('admin.llm.create');
    }

    /**
     * Store new knowledge base entry for LLM training
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'summary' => 'nullable|string|max:1000',
            'tags' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'content_type' => 'nullable|in:guideline,protocol,research,faq,case_study',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240', // 10MB max
        ]);

        try {
            // Process tags
            $tags = $request->tags ? 
                json_encode(array_map('trim', explode(',', $request->tags))) : 
                null;

            $knowledgeBase = KnowledgeBase::create([
                'pdf_name' => $request->title,
                'pdf_text' => $request->content,
                'ai_summary' => $request->summary,
                'user_id' => Auth::id(),
                'added_at' => now(),
                // Additional LLM-focused fields (would need migration update)
                // 'tags' => $tags,
                // 'priority' => $request->priority ?? 'medium',
                // 'content_type' => $request->content_type ?? 'guideline',
                // 'is_approved' => false, // For review workflow
                // 'version' => 1,
            ]);

            // Handle file upload if provided
            if ($request->hasFile('pdf_file')) {
                $file = $request->file('pdf_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('llm-knowledge', $fileName, 'public');
                
                // You could store the file path in a separate field
                // $knowledgeBase->update(['file_path' => $path]);
            }

            // Log for audit trail
            Log::info('LLM Knowledge Base entry created', [
                'user_id' => Auth::id(),
                'kb_id' => $knowledgeBase->kb_id,
                'title' => $request->title
            ]);

            return redirect()->route('admin.llm.index')
                ->with('success', 'Knowledge base entry created successfully and ready for LLM training.');
        } catch (\Exception $e) {
            Log::error('Error creating LLM knowledge base entry: ' . $e->getMessage());
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
        
        // Track view for analytics
        // You could add a view counter or log views
        
        return view('admin.llm.show', compact('knowledgeBase'));
    }

    /**
     * Show edit knowledge base form
     */
    public function edit($id)
    {
        $knowledgeBase = KnowledgeBase::findOrFail($id);
        return view('admin.llm.edit', compact('knowledgeBase'));
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
            'tags' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,critical',
            'content_type' => 'nullable|in:guideline,protocol,research,faq,case_study',
        ]);

        try {
            $knowledgeBase = KnowledgeBase::findOrFail($id);
            
            // Process tags
            $tags = $request->tags ? 
                json_encode(array_map('trim', explode(',', $request->tags))) : 
                null;
            
            $knowledgeBase->update([
                'pdf_name' => $request->title,
                'pdf_text' => $request->content,
                'ai_summary' => $request->summary,
                // 'tags' => $tags,
                // 'priority' => $request->priority ?? 'medium',
                // 'content_type' => $request->content_type ?? 'guideline',
            ]);

            Log::info('LLM Knowledge Base entry updated', [
                'user_id' => Auth::id(),
                'kb_id' => $id,
                'title' => $request->title
            ]);

            return redirect()->route('admin.llm.index')
                ->with('success', 'Knowledge base entry updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating LLM knowledge base entry: ' . $e->getMessage());
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
            
            Log::info('LLM Knowledge Base entry deleted', [
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
            Log::error('Error deleting LLM knowledge base entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting knowledge base entry.'
            ], 500);
        }
    }

    /**
     * Get LLM training data in JSON format
     * This endpoint will be used by your LLM system
     */
    public function getTrainingData(Request $request)
    {
        $limit = $request->get('limit', 100);
        $format = $request->get('format', 'standard'); // standard, openai, custom
        
        $knowledgeBase = KnowledgeBase::with('user')
            ->orderBy('added_at', 'desc')
            ->limit($limit)
            ->get();

        $trainingData = [];

        foreach ($knowledgeBase as $entry) {
            switch ($format) {
                case 'openai':
                    // Format for OpenAI fine-tuning
                    $trainingData[] = [
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a nutrition and malnutrition expert assistant.'
                            ],
                            [
                                'role' => 'user', 
                                'content' => $entry->ai_summary ?? 'Explain this topic: ' . $entry->pdf_name
                            ],
                            [
                                'role' => 'assistant',
                                'content' => $entry->pdf_text
                            ]
                        ]
                    ];
                    break;
                    
                case 'qa':
                    // Question-Answer format
                    $trainingData[] = [
                        'id' => $entry->kb_id,
                        'question' => $entry->ai_summary ?? $entry->pdf_name,
                        'answer' => $entry->pdf_text,
                        'context' => 'nutrition_malnutrition',
                        'created_at' => $entry->added_at
                    ];
                    break;
                    
                default:
                    // Standard format
                    $trainingData[] = [
                        'id' => $entry->kb_id,
                        'title' => $entry->pdf_name,
                        'content' => $entry->pdf_text,
                        'summary' => $entry->ai_summary,
                        'word_count' => str_word_count($entry->pdf_text ?? ''),
                        'character_count' => strlen($entry->pdf_text ?? ''),
                        'created_at' => $entry->added_at,
                        'created_by' => $entry->user->first_name ?? 'System'
                    ];
            }
        }

        return response()->json([
            'success' => true,
            'format' => $format,
            'total_entries' => count($trainingData),
            'data' => $trainingData
        ]);
    }

    /**
     * Export training data for LLM
     */
    public function exportTrainingData(Request $request)
    {
        $format = $request->get('format', 'json');
        $knowledgeBase = KnowledgeBase::with('user')->get();

        switch ($format) {
            case 'csv':
                return $this->exportToCsv($knowledgeBase);
            case 'jsonl':
                return $this->exportToJsonLines($knowledgeBase);
            case 'txt':
                return $this->exportToText($knowledgeBase);
            default:
                return $this->exportToJson($knowledgeBase);
        }
    }

    /**
     * Get LLM knowledge base statistics
     */
    public function getStats()
    {
        $stats = [
            'total_entries' => KnowledgeBase::count(),
            'total_words' => KnowledgeBase::sum(DB::raw('(LENGTH(pdf_text) - LENGTH(REPLACE(pdf_text, " ", "")) + 1)')),
            'total_characters' => KnowledgeBase::sum(DB::raw('LENGTH(pdf_text)')),
            'entries_this_month' => KnowledgeBase::whereMonth('added_at', now()->month)->count(),
            'entries_today' => KnowledgeBase::whereDate('added_at', today())->count(),
            'average_entry_length' => KnowledgeBase::avg(DB::raw('LENGTH(pdf_text)')),
            'contributors' => KnowledgeBase::distinct('user_id')->count('user_id'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Search knowledge base for LLM queries
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $limit = $request->get('limit', 10);
        
        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $results = KnowledgeBase::search($query)
            ->limit($limit)
            ->get()
            ->map(function($entry) {
                return [
                    'id' => $entry->kb_id,
                    'title' => $entry->pdf_name,
                    'summary' => $entry->ai_summary,
                    'relevance_score' => 1.0, // You could implement proper scoring
                    'word_count' => str_word_count($entry->pdf_text ?? ''),
                ];
            });

        return response()->json([
            'success' => true,
            'query' => $query,
            'total_results' => $results->count(),
            'results' => $results
        ]);
    }

    /**
     * Bulk import knowledge base entries
     */
    public function bulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json,csv,txt|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('import_file');
            $extension = $file->getClientOriginalExtension();
            $content = file_get_contents($file->getPathname());
            
            $imported = 0;
            $errors = [];

            switch ($extension) {
                case 'json':
                    $data = json_decode($content, true);
                    foreach ($data as $entry) {
                        try {
                            KnowledgeBase::create([
                                'pdf_name' => $entry['title'] ?? 'Imported Entry',
                                'pdf_text' => $entry['content'] ?? $entry['text'],
                                'ai_summary' => $entry['summary'] ?? null,
                                'user_id' => Auth::id(),
                                'added_at' => now(),
                            ]);
                            $imported++;
                        } catch (\Exception $e) {
                            $errors[] = "Error importing entry: " . $e->getMessage();
                        }
                    }
                    break;
                    
                // Add CSV and TXT parsing as needed
            }

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing import file: ' . $e->getMessage()
            ], 500);
        }
    }

    // Private helper methods for exports

    private function exportToJson($knowledgeBase)
    {
        $data = $knowledgeBase->map(function($entry) {
            return [
                'title' => $entry->pdf_name,
                'content' => $entry->pdf_text,
                'summary' => $entry->ai_summary,
                'created_at' => $entry->added_at,
            ];
        });

        $response = response()->json($data);
        $response->headers->set('Content-Disposition', 'attachment; filename="llm_training_data.json"');
        return $response;
    }

    private function exportToJsonLines($knowledgeBase)
    {
        $lines = [];
        foreach ($knowledgeBase as $entry) {
            $lines[] = json_encode([
                'text' => $entry->pdf_text,
                'meta' => [
                    'title' => $entry->pdf_name,
                    'summary' => $entry->ai_summary,
                ]
            ]);
        }

        $content = implode("\n", $lines);
        return response($content)
            ->header('Content-Type', 'application/x-ndjson')
            ->header('Content-Disposition', 'attachment; filename="llm_training_data.jsonl"');
    }

    private function exportToCsv($knowledgeBase)
    {
        $csv = "title,content,summary,created_at\n";
        foreach ($knowledgeBase as $entry) {
            $csv .= '"' . str_replace('"', '""', $entry->pdf_name) . '",';
            $csv .= '"' . str_replace('"', '""', $entry->pdf_text) . '",';
            $csv .= '"' . str_replace('"', '""', $entry->ai_summary ?? '') . '",';
            $csv .= '"' . $entry->added_at . '"' . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="llm_training_data.csv"');
    }

    private function exportToText($knowledgeBase)
    {
        $text = "";
        foreach ($knowledgeBase as $entry) {
            $text .= "TITLE: " . $entry->pdf_name . "\n";
            $text .= "SUMMARY: " . ($entry->ai_summary ?? 'No summary') . "\n";
            $text .= "CONTENT:\n" . $entry->pdf_text . "\n";
            $text .= "---\n\n";
        }

        return response($text)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="llm_training_data.txt"');
    }
}