<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;

class ArchiveEligiblePatients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patients:archive-eligible 
                            {--dry-run : Display patients that would be archived without actually archiving them}
                            {--force : Archive without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive patients who are 5 years old (60 months) and above';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Searching for patients eligible for archiving (5 years old and above)...');

        // Get all patients eligible for archiving
        $eligiblePatients = Patient::eligibleForArchiving()->get();

        if ($eligiblePatients->isEmpty()) {
            $this->info('No patients found that are eligible for archiving.');
            return Command::SUCCESS;
        }

        $count = $eligiblePatients->count();
        $this->info("Found {$count} patient(s) eligible for archiving.");

        // Display table of eligible patients
        $this->table(
            ['ID', 'Custom ID', 'Name', 'Age (months)', 'Age (years)', 'Birthdate'],
            $eligiblePatients->map(function ($patient) {
                $ageMonths = $patient->age_months;
                $ageYears = round($ageMonths / 12, 1);
                return [
                    $patient->patient_id,
                    $patient->custom_patient_id,
                    "{$patient->first_name} {$patient->last_name}",
                    $ageMonths,
                    $ageYears,
                    $patient->birthdate ? $patient->birthdate->format('Y-m-d') : 'N/A',
                ];
            })->toArray()
        );

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE: No patients were archived.');
            return Command::SUCCESS;
        }

        // Confirmation
        if (!$this->option('force')) {
            if (!$this->confirm("Do you want to archive these {$count} patient(s)?")) {
                $this->info('Archive operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Archive patients
        $archived = 0;
        $failed = 0;

        $this->withProgressBar($eligiblePatients, function ($patient) use (&$archived, &$failed) {
            try {
                $patient->archive();
                $archived++;
            } catch (\Exception $e) {
                $this->error("\nFailed to archive patient {$patient->patient_id}: {$e->getMessage()}");
                $failed++;
            }
        });

        $this->newLine(2);
        $this->info("Successfully archived {$archived} patient(s).");

        if ($failed > 0) {
            $this->error("Failed to archive {$failed} patient(s).");
        }

        return Command::SUCCESS;
    }
}
