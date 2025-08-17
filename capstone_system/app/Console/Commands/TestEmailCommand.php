<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Models\User;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {--to=veriiemail22@gmail.com : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration using Brevo SMTP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $toEmail = $this->option('to');
        
        $this->info('Testing Brevo SMTP Configuration...');
        $this->info('====================================');
        $this->newLine();
        
        // Display current mail configuration
        $this->info('Current Mail Configuration:');
        $this->line('MAIL_MAILER: ' . config('mail.default'));
        $this->line('MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption'));
        $this->line('MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->line('MAIL_FROM_NAME: ' . config('mail.from.name'));
        $this->newLine();
        
        try {
            // Test basic email sending
            $this->info('Sending basic test email...');
            
            Mail::raw('This is a test email to verify Brevo SMTP configuration is working correctly. Sent from BMI Malnutrition Monitoring System.', function ($message) use ($toEmail) {
                $message->to($toEmail)
                        ->subject('Brevo SMTP Test - BMI Monitoring System');
            });
            
            $this->info('✅ Basic test email sent successfully!');
            $this->newLine();
            
            // Test welcome email template
            $this->info('Testing Welcome Email Template...');
            
            // Create a dummy user for template testing (not saved to database)
            $testUser = new User([
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $toEmail
            ]);
            
            Mail::to($toEmail)->send(new WelcomeEmail($testUser));
            
            $this->info('✅ Welcome email template sent successfully!');
            $this->newLine();
            
            $this->info("🎉 All email tests completed successfully!");
            $this->info("Check your email inbox ({$toEmail}) for both test messages.");
            $this->info("Your Brevo SMTP configuration is working correctly.");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Email test failed: " . $e->getMessage());
            $this->newLine();
            $this->warn("Common issues to check:");
            $this->line("1. MAIL_PASSWORD is missing or incorrect in .env file");
            $this->line("2. Brevo SMTP credentials are incorrect");
            $this->line("3. Brevo account is not active or suspended");
            $this->line("4. Firewall blocking outbound SMTP traffic (port 587)");
            $this->line("5. Internet connection issues");
            $this->newLine();
            $this->info("Please verify your Brevo SMTP settings and try again.");
            
            return Command::FAILURE;
        }
    }
}
