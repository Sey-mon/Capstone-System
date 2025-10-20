
<?php

namespace App\Console\Commands;

use App\Models\KnowledgeBase;
use App\Services\NutritionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LLMHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'llm:health-check {--detailed : Show detailed health information}';

    /**
     * The console command description.
     */
    protected $description = 'Check the health status of the LLM service and knowledge base';

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
        $this->info('Running LLM Health Check...');
        $this->newLine();

        $detailed = $this->option('detailed');
        $overallHealth = true;

        // Test API Connection
        $this->info('🔗 Testing API Connection...');
        $connectionStatus = $this->nutritionService->testConnection();
        
        if ($connectionStatus) {
            $this->info('✅ API Connection: HEALTHY');
        } else {
            $this->error('❌ API Connection: FAILED');
            $overallHealth = false;
        }

        // Test Model Status
        if ($connectionStatus) {
            $this->info('🤖 Checking Model Status...');
            try {
                $modelStatus = $this->nutritionService->getModelStatus();
                $this->info('✅ Model Status: HEALTHY');
                
                if ($detailed && isset($modelStatus['status'])) {
                    $this->table(
                        ['Property', 'Value'],
                        collect($modelStatus)->map(fn($value, $key) => [$key, is_array($value) ? json_encode($value) : $value])->toArray()
                    );
                }
            } catch (\Exception $e) {
                $this->error('❌ Model Status: ERROR - ' . $e->getMessage());
                $overallHealth = false;
            }
        }

        // Knowledge Base Statistics
        $this->info('📚 Knowledge Base Statistics...');
        
        try {
            $kbCount = KnowledgeBase::count();
            $recentEntries = KnowledgeBase::where('added_at', '>=', now()->subDays(7))->count();
            $lastEntry = KnowledgeBase::latest('added_at')->first();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Entries', $kbCount],
                    ['Entries (Last 7 days)', $recentEntries],
                    ['Last Entry', $lastEntry ? $lastEntry->added_at->diffForHumans() : 'None'],
                    ['Average Entry Size', $kbCount > 0 ? round(KnowledgeBase::avg(DB::raw('LENGTH(pdf_text)'))) . ' chars' : 'N/A'],
                ]
            );
            
            $dbHealthy = true;
        } catch (\Exception $e) {
            $this->error('❌ Database Connection: FAILED - ' . $e->getMessage());
            $kbCount = 0;
            $recentEntries = 0;
            $dbHealthy = false;
        }

        // Recommendations
        $this->info('💡 Recommendations:');
        $recommendations = [];

        if (!$connectionStatus) {
            $recommendations[] = 'LLM API service is not reachable. Check if the service is running on ' . config('services.nutrition_api.base_url');
        }

        if (!isset($dbHealthy) || !$dbHealthy) {
            $recommendations[] = 'Database connection failed. Please check your database configuration and ensure MySQL/XAMPP is running.';
        }

        if ($kbCount < 10) {
            $recommendations[] = 'Consider adding more knowledge base entries for better LLM performance (currently: ' . $kbCount . ')';
        }

        if ($recentEntries === 0 && isset($dbHealthy) && $dbHealthy) {
            $recommendations[] = 'No new knowledge base entries in the last 7 days. Consider regular updates.';
        }

        if (empty($recommendations)) {
            $this->info('✅ No recommendations - system appears healthy!');
        } else {
            foreach ($recommendations as $recommendation) {
                $this->warn('⚠️  ' . $recommendation);
            }
        }

        // Overall Status
        $this->newLine();
        if ($overallHealth && (isset($dbHealthy) ? $dbHealthy : true)) {
            $this->info('🎉 Overall Health Status: HEALTHY');
        } else {
            $this->error('⚠️  Overall Health Status: NEEDS ATTENTION');
        }

        return ($overallHealth && (isset($dbHealthy) ? $dbHealthy : true)) ? 0 : 1;
    }
}