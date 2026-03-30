<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupExpiredPasswordResets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired password reset tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get token expiry time from config (default: 60 minutes)
        $expiryMinutes = config('auth.passwords.users.expire', 60);
        
        // Calculate cutoff time
        $cutoffTime = Carbon::now()->subMinutes($expiryMinutes);
        
        // Delete expired tokens
        $deleted = DB::table('password_reset_tokens')
            ->where('created_at', '<', $cutoffTime)
            ->delete();
        
        $this->info("Deleted {$deleted} expired password reset token(s).");
        
        return Command::SUCCESS;
    }
}
