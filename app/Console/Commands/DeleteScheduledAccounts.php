<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteScheduledAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:delete-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete accounts that have been scheduled for deletion for 30+ days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled account deletion process...');

        // Find users scheduled for deletion where 30 days have passed
        $usersToDelete = User::whereNotNull('scheduled_deletion_at')
            ->where('scheduled_deletion_at', '<=', now())
            ->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('No accounts found for deletion.');
            return Command::SUCCESS;
        }

        $deletedCount = 0;

        foreach ($usersToDelete as $user) {
            try {
                DB::beginTransaction();

                // Store user info for logging before deletion
                $userId = $user->user_id;
                $userEmail = $user->email;
                $userName = $user->first_name . ' ' . $user->last_name;
                $scheduledDate = $user->scheduled_deletion_at;

                // Unlink all children (set parent_id to null)
                $childrenCount = Patient::where('parent_id', $user->user_id)->count();
                Patient::where('parent_id', $user->user_id)
                    ->update(['parent_id' => null]);

                // Create a system audit log (with user_id = 1 for system/admin)
                // This preserves a record that the account was deleted
                $systemLog = "Account permanently deleted: {$userName} ({$userEmail}, ID: {$userId}). Scheduled on: {$scheduledDate}. Children unlinked: {$childrenCount}";
                
                // Try to create system audit log (use admin user ID if exists, otherwise skip)
                $adminUser = User::where('role_id', 1)->first();
                if ($adminUser) {
                    AuditLog::create([
                        'user_id' => $adminUser->user_id,
                        'action' => 'account_permanently_deleted',
                        'description' => $systemLog,
                        'ip_address' => '127.0.0.1',
                        'user_agent' => 'System Scheduled Task',
                    ]);
                }

                // Delete all audit logs for this user (or they will prevent deletion)
                AuditLog::where('user_id', $userId)->delete();

                // Permanently delete the user
                $user->forceDelete();

                DB::commit();

                $deletedCount++;
                $this->info("Deleted account: {$userEmail} (Children unlinked: {$childrenCount})");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to delete account {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Successfully deleted {$deletedCount} accounts.");

        return Command::SUCCESS;
    }
}
