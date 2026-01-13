<?php

namespace App\Services;

use App\Models\WorkRequest;
use App\Models\WorkRequestApproval;
use App\Models\WorkRequestApprovalRule;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkRequestService
{
    /**
     * Submit a Work Request for approval.
     */
    public function submit(WorkRequest $workRequest)
    {
        return DB::transaction(function () use ($workRequest) {
            $rules = WorkRequestApprovalRule::where('category', $workRequest->category)
                ->where('wr_type', $workRequest->type)
                ->orderBy('step_order', 'asc')
                ->get();

            if ($rules->isEmpty()) {
                // If no rules, auto-approve or handle as needed
                $this->approveAutomatically($workRequest);
                return $workRequest;
            }

            foreach ($rules as $rule) {
                WorkRequestApproval::create([
                    'work_request_id' => $workRequest->id,
                    'role_id' => $rule->role_id,
                    'step_order' => $rule->step_order,
                    'status' => 'PENDING',
                ]);
            }

            $workRequest->update(['status' => 'PENDING_APPROVAL']);
            
            return $workRequest;
        });
    }

    /**
     * Approve or Reject a Work Request step.
     */
    public function updateApprovalStep(WorkRequestApproval $approval, string $status, ?string $comment = null)
    {
        return DB::transaction(function () use ($approval, $status, $comment) {
            $approval->update([
                'status' => $status,
                'comment' => $comment,
                'user_id' => auth()->id(),
            ]);

            $workRequest = $approval->workRequest;

            if ($status === 'REJECTED') {
                $workRequest->update(['status' => 'REJECTED']);
                return $approval;
            }

            // Check if all steps are approved
            $remainingSteps = WorkRequestApproval::where('work_request_id', $workRequest->id)
                ->where('status', 'PENDING')
                ->count();

            if ($remainingSteps === 0) {
                $this->finalizeApproval($workRequest);
            }

            return $approval;
        });
    }

    /**
     * Finalize approval and trigger follow-up actions.
     */
    protected function finalizeApproval(WorkRequest $workRequest)
    {
        $workRequest->update(['status' => 'APPROVED']);

        // According to BPMN:
        // 1. Scheduled WO from PS/PI (Has service_plan_id)
        // 2. Repair Request from manual WR (type === Repair Request)
        
        if ($workRequest->service_plan_id || $workRequest->type === 'Repair Request') {
            $this->createWorkOrderFromWR($workRequest);
        }
    }

    /**
     * Create a Work Order from an approved WR.
     */
    public function createWorkOrderFromWR(WorkRequest $workRequest)
    {
        $isScheduled = !empty($workRequest->service_plan_id);
        
        $wo = WorkOrder::create([
            'uid' => (string) Str::uuid(),
            'work_order_no' => WorkOrder::generateWorkOrderNo($isScheduled),
            'work_request_id' => $workRequest->id,
            'service_plan_id' => $workRequest->service_plan_id,
            'equipment_id' => $workRequest->equipment_id,
            'wo_type' => $isScheduled ? 'Scheduled' : 'Unscheduled',
            'priority' => $isScheduled ? 'HIGH' : 'MEDIUM',
            'status' => 'OPEN',
            'description' => $isScheduled 
                ? "Work Order created from Service Plan for unit: " . ($workRequest->equipment->code ?? '-')
                : "Work Order created from Work Request number {$workRequest->wr_no}",
            'created_by' => $workRequest->created_by,
        ]);

        // If from Service Plan: Auto-fill spare parts list (placeholder for now, can be expanded)
        // if ($isScheduled) {
        //     $this->fillSparePartsFromPlan($wo, $workRequest->service_plan_id);
        // }
        
        $workRequest->update(['status' => 'IN_WORK_ORDER']);

        return $wo;
    }

    protected function approveAutomatically(WorkRequest $workRequest)
    {
        $this->finalizeApproval($workRequest);
    }
}
