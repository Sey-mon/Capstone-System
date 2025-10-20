<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use App\Services\NutritionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncKnowledgeBase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'llm:sync-knowledge 
                            {--limit=100 : Number of entries to sync}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     */
    protected $description = 'Sync knowledge base entries with the LLM service';

    protected $nutritionService;

    public function __construct(NutritionService $nutritionService)
    {
        parent::__construct();
        $this->nutritionService = $nutritionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting knowledge base synchronization...');

        try {
            // Test connection first
            if (!$this->nutritionService->testConnection()) {
                $this->error('LLM service is not reachable. Please check the service status.');
                return 1;
            }

            $limit = $this->option('limit');
            $this->info("Fetching up to {$limit} knowledge base entries...");

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

            if ($knowledgeData->isEmpty()) {
                $this->warn('No knowledge base entries found to sync.');
                return 0;
            }

            $this->info("Syncing {$knowledgeData->count()} entries with LLM service...");

            $progressBar = $this->output->createProgressBar($knowledgeData->count());
            $progressBar->start();

            $result = $this->nutritionService->updateKnowledgeBase($knowledgeData->toArray());

            $progressBar->finish();
            $this->newLine();

            $this->info('Knowledge base synchronization completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Entries synced', $knowledgeData->count()],
                    ['Total words', $knowledgeData->sum(fn($item) => str_word_count($item['content'] ?? ''))],
                    ['Sync timestamp', now()->toDateTimeString()],
                ]
            );

            Log::info('Knowledge base sync completed via command', [
                'entries_synced' => $knowledgeData->count(),
                'result' => $result
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('Error synchronizing knowledge base: ' . $e->getMessage());
            Log::error('Knowledge base sync command failed', [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
}