<?php

namespace App\Services;

use App\Models\LoanTransaction;
use App\Models\ToolCard;
use App\Models\Tools;
use App\Models\ToolIncidentReport;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use App\Models\common\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class LoanService
{
    /**
     * Create a new loan transaction
     *
     * @param int $toolCardId
     * @param int $toolId
     * @param int $adminId
     * @param string $requirementType
     * @param string|null $requirementReference
     * @param string|null $notes
     * @return array
     */
    public function createLoan($toolCardId, $toolId, $adminId, $requirementType, $requirementReference = null, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Load tool card and tool
            $toolCard = ToolCard::with('employee')->findOrFail($toolCardId);
            $tool = Tools::findOrFail($toolId);

            // Validate loan
            $validation = $this->validateLoan($toolCard, $tool);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors'] ?? []
                ];
            }

            // Create loan transaction
            $loan = LoanTransaction::create([
                'tool_card_id' => $toolCardId,
                'tool_id' => $toolId,
                'admin_id' => $adminId,
                'requirement_type' => $requirementType,
                'requirement_reference' => $requirementReference,
                'requirement_notes' => $notes,
                'loan_date' => now(),
                'expected_return_date' => LoanTransaction::calculateExpectedReturn(),
                'status' => 'Active',
            ]);

            // Log audit trail
            AuditLog::log(
                'loan_created',
                'LoanTransaction',
                $loan->id,
                null,
                $loan->toArray()
            );

            // Update tool status to In Use
            $inUseStatus = Status::where('slug', 'in-use')->first();
            if ($inUseStatus) {
                $tool->status_id = $inUseStatus->id;
                $tool->save();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Loan created successfully',
                'data' => $loan->load(['toolCard.employee', 'tool', 'admin'])
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create loan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process tool return
     *
     * @param string $loanNumber
     * @param int $adminId
     * @param string $returnCondition
     * @param string|null $notes
     * @return array
     */
    public function processReturn($loanNumber, $adminId, $returnCondition, $notes = null)
    {
        try {
            DB::beginTransaction();

            // Find loan transaction
            $loan = LoanTransaction::where('loan_number', $loanNumber)
                                   ->with(['toolCard.employee', 'tool'])
                                   ->firstOrFail();

            // Validate loan is active
            if ($loan->status !== 'Active') {
                return [
                    'success' => false,
                    'message' => 'This loan is not active'
                ];
            }

            $oldValues = $loan->toArray();

            // Update loan
            $loan->update([
                'actual_return_date' => now(),
                'status' => 'Returned',
                'return_condition' => $returnCondition,
                'return_notes' => $notes,
            ]);

            // Update tool status if damaged/lost
            if ($returnCondition === 'Damaged' || $returnCondition === 'Lost') {
                // Determine incident type
                $type = $returnCondition === 'Lost' ? ToolIncidentReport::TYPE_MISSING : ToolIncidentReport::TYPE_DAMAGED;
                
                // Generate incident number
                $incidentNumber = ToolIncidentReport::generateIncidentNumber($type);
                
                // Create draft incident report
                $incident = ToolIncidentReport::create([
                    'incident_number' => $incidentNumber,
                    'incident_type' => $type,
                    'tool_id' => $loan->tool_id,
                    'loan_transaction_id' => $loan->id,
                    'employee_id' => $loan->toolCard->employee_id,
                    'incident_date' => now(), // Default to return date
                    'status' => ToolIncidentReport::STATUS_DRAFT,
                    'created_by' => $adminId,
                ]);

                // Log audit trail for incident creation
                AuditLog::log(
                    'incident_created',
                    'ToolIncidentReport',
                    $incident->id,
                    null,
                    $incident->toArray()
                );
            }

            // Log audit trail
            AuditLog::log(
                'loan_returned',
                'LoanTransaction',
                $loan->id,
                $oldValues,
                $loan->toArray()
            );

            // Update tool status based on condition
            $newStatusSlug = 'available';
            if ($returnCondition === 'Damaged') {
                $newStatusSlug = 'broken';
            } elseif ($returnCondition === 'Lost') {
                $newStatusSlug = 'disposed';
            }

            $newStatus = Status::where('slug', $newStatusSlug)->first();
            if ($newStatus) {
                $loan->tool->status_id = $newStatus->id;
                $loan->tool->save();
            }

            DB::commit();

            $responseData = [
                'loan' => $loan->fresh(['toolCard.employee', 'tool']),
                'incident_id' => isset($incident) ? $incident->id : null
            ];

            return [
                'success' => true,
                'message' => 'Tool returned successfully' . (isset($incident) ? '. Draft Incident Report created.' : ''),
                'data' => $responseData
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to process return: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate if a loan can be created
     *
     * @param ToolCard $toolCard
     * @param Tools $tool
     * @return array
     */
    public function validateLoan(ToolCard $toolCard, Tools $tool)
    {
        $errors = [];

        // Check if tool card is approved
        if ($toolCard->status !== 'APPROVED_FINAL') {
            $errors[] = 'Tool card is not approved';
        }

        // Check if tool is available
        if (!$tool->isAvailable()) {
            $errors[] = 'Tool is currently not available';
        }

        // Check access level (hierarchical)
        if ($toolCard->access_level < $tool->required_access_level) {
            $errors[] = "Insufficient access level. Required: Level {$tool->required_access_level}, Employee has: Level {$toolCard->access_level}";
        }

        // Check if employee already has an active loan for this tool
        $existingLoan = LoanTransaction::where('tool_card_id', $toolCard->id)
                                       ->where('tool_id', $tool->id)
                                       ->where('status', 'Active')
                                       ->exists();
        
        if ($existingLoan) {
            $errors[] = 'Employee already has an active loan for this tool';
        }

        return [
            'valid' => empty($errors),
            'message' => empty($errors) ? 'Validation passed' : 'Validation failed',
            'errors' => $errors
        ];
    }

    /**
     * Get active loans statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        $totalActive = LoanTransaction::where('status', 'Active')->count();
        $totalOverdue = LoanTransaction::where('status', 'Active')
                                       ->where('expected_return_date', '<', now())
                                       ->count();
        $totalReturnedToday = LoanTransaction::where('status', 'Returned')
                                             ->whereDate('actual_return_date', today())
                                             ->count();

        return [
            'total_active' => $totalActive,
            'total_overdue' => $totalOverdue,
            'returned_today' => $totalReturnedToday,
        ];
    }
}
