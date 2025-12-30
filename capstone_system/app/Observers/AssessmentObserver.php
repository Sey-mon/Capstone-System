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

            // Extract nutritional indicators from treatment JSON
            $this->extractAndSyncIndicators($patient, $latestAssessment);

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

    /**
     * Extract nutritional indicators from assessment treatment JSON
     * and update patient record.
     * 
     * Handles multiple possible JSON structures and logs warnings
     * if indicators cannot be extracted.
     * 
     * @param Patient $patient
     * @param Assessment $assessment
     * @return void
     */
    private function extractAndSyncIndicators(Patient $patient, Assessment $assessment): void
    {
        // Check if treatment exists
        if (!$assessment->treatment) {
            Log::warning('AssessmentObserver: Treatment data is null, skipping indicator sync', [
                'assessment_id' => $assessment->assessment_id,
                'patient_id' => $assessment->patient_id
            ]);
            
            // Set indicators to null if no treatment data
            $patient->weight_for_age = null;
            $patient->height_for_age = null;
            $patient->bmi_for_age = null;
            return;
        }

        try {
            // Decode treatment JSON
            $treatment = json_decode($assessment->treatment, true);

            if (!$treatment || !is_array($treatment)) {
                Log::warning('AssessmentObserver: Invalid treatment JSON structure', [
                    'assessment_id' => $assessment->assessment_id,
                    'patient_id' => $assessment->patient_id,
                    'treatment' => $assessment->treatment
                ]);
                
                $patient->weight_for_age = null;
                $patient->height_for_age = null;
                $patient->bmi_for_age = null;
                return;
            }

            // Extract indicators - try multiple possible JSON structures
            $patient->weight_for_age = $this->extractIndicator($treatment, 'weight_for_age');
            $patient->height_for_age = $this->extractIndicator($treatment, 'height_for_age');
            $patient->bmi_for_age = $this->extractIndicator($treatment, 'bmi_for_age');

            Log::info('AssessmentObserver: Extracted nutritional indicators', [
                'assessment_id' => $assessment->assessment_id,
                'patient_id' => $assessment->patient_id,
                'weight_for_age' => $patient->weight_for_age,
                'height_for_age' => $patient->height_for_age,
                'bmi_for_age' => $patient->bmi_for_age
            ]);

        } catch (\Exception $e) {
            Log::error('AssessmentObserver: Error extracting indicators from treatment JSON', [
                'assessment_id' => $assessment->assessment_id,
                'patient_id' => $assessment->patient_id,
                'error' => $e->getMessage()
            ]);
            
            // Set to null on error
            $patient->weight_for_age = null;
            $patient->height_for_age = null;
            $patient->bmi_for_age = null;
        }
    }

    /**
     * Extract a specific nutritional indicator from treatment JSON.
     * 
     * Tries multiple common JSON structures:
     * - treatment['patient_info']['field_name']
     * - treatment['classification']['field_name']
     * - treatment['nutritional_status']['field_name']
     * - treatment['field_name']
     * 
     * @param array $treatment
     * @param string $field
     * @return string|null
     */
    private function extractIndicator(array $treatment, string $field): ?string
    {
        // Try patient_info structure (most common)
        if (isset($treatment['patient_info'][$field])) {
            return $treatment['patient_info'][$field];
        }

        // Try classification structure
        if (isset($treatment['classification'][$field])) {
            return $treatment['classification'][$field];
        }

        // Try nutritional_status structure
        if (isset($treatment['nutritional_status'][$field])) {
            return $treatment['nutritional_status'][$field];
        }

        // Try assessment structure
        if (isset($treatment['assessment'][$field])) {
            return $treatment['assessment'][$field];
        }

        // Try direct field access
        if (isset($treatment[$field])) {
            return $treatment[$field];
        }

        // Not found in any known structure
        return null;
    }
}
