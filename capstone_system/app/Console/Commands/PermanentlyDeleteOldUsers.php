<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PermanentlyDeleteOldUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:permanent-delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete users that have been soft-deleted for more than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        // Get users that were soft-deleted more than 30 days ago
        $oldDeletedUsers = User::onlyTrashed()
            ->where('deleted_at', '<=', $thirtyDaysAgo)
            ->get();

        if ($oldDeletedUsers->isEmpty()) {
            $this->info('No users found for permanent deletion.');
            return 0;
        }

        $this->info("Found {$oldDeletedUsers->count()} users to permanently delete.");

        DB::beginTransaction();

        try {
            foreach ($oldDeletedUsers as $user) {
                $userName = "{$user->first_name} {$user->last_name}";
                
                // Log the permanent deletion
                AuditLog::create([
                    'user_id' => 1, // System user ID
                    'action' => 'PERMANENT_DELETE',
                    'table_name' => 'users',
                    'record_id' => $user->user_id,
                    'description' => "Permanently deleted user: {$userName} (soft-deleted on {$user->deleted_at})",
                ]);

                // Permanently delete the user
                $user->forceDelete();
                
                $this->line("Permanently deleted: {$userName}");
            }

            DB::commit();
            $this->info("Successfully permanently deleted {$oldDeletedUsers->count()} users.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error permanently deleting users: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
