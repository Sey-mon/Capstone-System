<?php

namespace App\Observers;

use App\Models\Assessment;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class AssessmentObserver
{
    /**
     * Handle the Assessment "created" event.
     * 
     * When a new assessment is created and marked as complete,
     * sync the latest assessment data to the patient table.
     */
    public function created(Assessment $assessment): void
    {
        $this->syncPatientData($assessment);
    }

    /**
     * Handle the Assessment "updated" event.
     * 
     * When an assessment is updated (e.g., marked as complete),
     * sync the latest assessment data to the patient table.
     */
    public function updated(Assessment $assessment): void
    {
        $this->syncPatientData($assessment);
    }

    /**
     * Sync patient data from the most recent completed assessment.
     * 
     * Only syncs if the assessment is marked as complete (completed_at is set).
     * Always uses the most recent completed assessment by assessment_date.
     * Extracts nutritional indicators from the treatment JSON field.
     * 
     * @param Assessment $assessment
     * @return void
     */
    private function syncPatientData(Assessment $assessment): void
    {
        try {
            // Only sync if assessment is complete
            if (!$assessment->completed_at) {
                return;
            }

            // Get the patient (reload to ensure fresh data)
            $patient = Patient::find($assessment->patient_id);
            
            if (!$patient) {
                Log::warning('AssessmentObserver: Patient not found', [
                    'assessment_id' => $assessment->assessment_id,
                    'patient_id' => $assessment->patient_id
                ]);
                return;
            }

            // Get the most recent completed assessment for this patient
            $latestAssessment = Assessment::where('patient_id', $assessment->patient_id)
                ->whereNotNull('completed_at')
                ->orderBy('assessment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$latestAssessment) {
                return;
            }

            // Update patient with raw measurements from latest assessment
            $patient->weight_kg = $latestAssessment->weight_kg;
            $patient->height_cm = $latestAssessment->height_cm;

            // Save patient updates
            $patient->save();

            Log::info('AssessmentObserver: Successfully synced patient data', [
                'assessment_id' => $latestAssessment->assessment_id,
                'patient_id' => $patient->patient_id,
                'weight_kg' => $patient->weight_kg,
                'height_cm' => $patient->height_cm
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the assessment save
            Log::error('AssessmentObserver: Failed to sync patient data', [
                'assessment_id' => $assessment->assessment_id,
                'patient_id' => $assessment->patient_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
