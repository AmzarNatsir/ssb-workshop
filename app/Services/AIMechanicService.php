<?php

namespace App\Services;

use App\Models\WorkOrder;
use Illuminate\Support\Carbon;

class AIMechanicService
{
    /**
     * Process Activity Data: Validate, Summarize, and Status
     */
    public function processActivity(WorkOrder $wo, array $data)
    {
        $validation = $this->validateData($wo, $data);
        $status = $this->determineStatus($validation);
        
        // Calculate duration
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $duration = $start->diffInHours($end); // decimal hours? or just use diffInMinutes / 60
        $duration = round($start->diffInMinutes($end) / 60, 2);

        $summary = $this->generateSummary($wo, $data, $duration);

        return [
            'validation_status' => $validation['status'],
            'mechanic_activity_status' => $status,
            'working_duration_hours' => $duration,
            'activity_summary' => $summary,
            'recommendation' => $validation['recommendation'] ?? 'Activity is ready for approval.',
            'notes' => $validation['errors'] ?? null, // JSON encoded errors if any
        ];
    }

    /**
     * Validate Data against Business Rules
     */
    protected function validateData(WorkOrder $wo, array $data)
    {
        $errors = [];

        // Rule 1: WO must be active (Handled by Controller usually, but double check)
        if ($wo->status === 'CLOSED') {
            $errors[] = "Work Order is CLOSED and cannot accept new activities.";
        }

        // Rule 2: End Time >= Start Time
        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        if ($end->lessThan($start)) {
            $errors[] = "End Time cannot be earlier than Start Time.";
        }

        // Rule 3: Components match WO Type (Simple keyword check for now)
        // This is a basic simulation of "AI" context awareness
        $woContext = strtolower($wo->description . ' ' . $wo->wo_type . ' ' . $wo->maintenance_type);
        $activityContext = strtolower($data['description']);
        
        // Example check: If WO is "Tyre Change" but activity is "Engine Overhaul" -> Suspicious
        // For now, we'll just check if description is too short
        if (strlen($data['description']) < 10) {
            $errors[] = "Description is too short. Please provide details.";
        }

        if (empty($errors)) {
            return ['status' => 'VALID'];
        }

        return [
            'status' => 'INVALID',
            'errors' => $errors,
            'recommendation' => 'Complete and correct the mechanic activity data before resubmitting.'
        ];
    }

    /**
     * Generate Professional Summary
     */
    protected function generateSummary(WorkOrder $wo, array $data, $duration)
    {
        // Template: Mechanic [Name] [Action] the [Component] on [Unit] for [Duration] hours. [Result].
        // Since we don't have separate Action/Component fields in the simplistic DB structure yet, 
        // we extract from description or just use description.
        
        $mechanicName = auth()->user()->name ?? 'The mechanic';
        $unitCode = $wo->equipment->code ?? 'the unit';
        
        return "Mechanic {$mechanicName} performed maintenance on {$unitCode} ({$wo->wo_type}). " .
               "Activity: {$data['description']}. " .
               "Duration: {$duration} hours. " .
               "Status: {$data['status']}.";
    }

    /**
     * Determine Status
     */
    protected function determineStatus($validation)
    {
        if ($validation['status'] === 'VALID') {
            return 'READY';
        }
        return 'REVISION REQUIRED';
    }
}
