<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;

class ServicePlanCalculator
{
    const SERVICE_INTERVAL = 300; // Hours between services
    const GENERAL_SERVICE_INTERVAL = 1200; // HM interval for general service

    /**
     * Calculate all service plan values
     *
     * @param float $hmPsSebelumnya Previous service HM
     * @param float $hmActual Current actual HM
     * @param float $whProject Work hours per project
     * @return array Calculated values
     * @throws Exception
     */
    public function calculate($hmPsSebelumnya, $hmActual, $whProject)
    {
        // Validate inputs
        $this->validateInputs($hmPsSebelumnya, $hmActual, $whProject);

        // Step 1: Calculate Overdue
        $overdue = $this->calculateOverdue($hmPsSebelumnya, $hmActual);

        // Step 2: Calculate PS Berikutnya
        $psBerikutnya = $this->calculatePsBerikutnya($hmActual, $overdue);

        // Step 3: Calculate Plan Date
        $planDate = $this->calculatePlanDate($overdue, $whProject);

        // Step 4: Determine Service Type
        $serviceType = $this->determineServiceType($psBerikutnya);

        // Step 5: Determine Status
        $status = $this->determineStatus($overdue);

        return [
            'overdue' => round($overdue, 2),
            'ps_berikutnya' => round($psBerikutnya, 2),
            'plan_date' => $planDate->format('Y-m-d'),
            'service_type' => $serviceType,
            'status' => $status,
            'wh_project' => round($whProject, 2),
        ];
    }

    /**
     * Calculate overdue hours
     * Formula: Overdue = HM Actual - (PS Sebelumnya + 300)
     *
     * @param float $hmPsSebelumnya
     * @param float $hmActual
     * @return float
     */
    public function calculateOverdue($hmPsSebelumnya, $hmActual)
    {
        return $hmActual - ($hmPsSebelumnya + self::SERVICE_INTERVAL);
    }

    /**
     * Calculate next service HM
     * Formula: PS Berikutnya = HM Actual + (-Overdue)
     *
     * @param float $hmActual
     * @param float $overdue
     * @return float
     */
    public function calculatePsBerikutnya($hmActual, $overdue)
    {
        return $hmActual + (-$overdue);
    }

    /**
     * Calculate planned service date
     * Formula: Plan Date = (-Overdue / WH_Project) + TODAY()
     *
     * @param float $overdue
     * @param float $whProject
     * @return Carbon
     */
    public function calculatePlanDate($overdue, $whProject)
    {
        $daysToService = (-$overdue) / $whProject;
        return Carbon::today()->addDays(ceil($daysToService));
    }

    /**
     * Determine service type based on PS Berikutnya
     * If PS Berikutnya is a multiple of 1200 → "General Service"
     * Otherwise → "Service Engine Oil"
     *
     * @param float $psBerikutnya
     * @return string
     */
    public function determineServiceType($psBerikutnya)
    {
        if ($psBerikutnya % self::GENERAL_SERVICE_INTERVAL == 0) {
            return 'General Service';
        }
        return 'Service Engine Oil';
    }

    /**
     * Determine plan status based on overdue value
     * If Overdue > 0 → OVERDUE (service is late)
     * If Overdue ≤ 0 → PLANNED (service is on schedule or ahead)
     *
     * @param float $overdue
     * @return string
     */
    public function determineStatus($overdue)
    {
        return $overdue > 0 ? 'OVERDUE' : 'PLANNED';
    }

    /**
     * Validate all input parameters
     *
     * @param float $hmPsSebelumnya
     * @param float $hmActual
     * @param float $whProject
     * @throws Exception
     */
    protected function validateInputs($hmPsSebelumnya, $hmActual, $whProject)
    {
        if ($whProject <= 0) {
            throw new Exception('WH per Project must be greater than 0');
        }

        if ($hmActual < $hmPsSebelumnya) {
            throw new Exception('HM Actual must not be less than HM PS Sebelumnya');
        }

        if ($hmPsSebelumnya < 0 || $hmActual < 0) {
            throw new Exception('HM values cannot be negative');
        }
    }

    /**
     * Get calculation details for debugging/display
     *
     * @param float $hmPsSebelumnya
     * @param float $hmActual
     * @param float $whProject
     * @return array
     */
    public function getCalculationDetails($hmPsSebelumnya, $hmActual, $whProject)
    {
        $result = $this->calculate($hmPsSebelumnya, $hmActual, $whProject);

        return [
            'result' => $result,
            'formulas' => [
                'overdue' => "{$hmActual} - ({$hmPsSebelumnya} + " . self::SERVICE_INTERVAL . ") = {$result['overdue']}",
                'ps_berikutnya' => "{$hmActual} + (-{$result['overdue']}) = {$result['ps_berikutnya']}",
                'plan_date' => "(-{$result['overdue']} / {$whProject}) + TODAY() = {$result['plan_date']}",
                'service_type' => "{$result['ps_berikutnya']} % " . self::GENERAL_SERVICE_INTERVAL . " == 0 ? General Service : Service Engine Oil",
            ],
        ];
    }
}
