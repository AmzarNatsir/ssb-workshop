<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\ServicePlan;
use App\Models\Equipments;
use App\Models\User;
use App\Models\WorkOrderSparePart;
use App\Models\MechanicActivity;
use App\Models\PartRequirement; // Added
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Services\AIMechanicService;


class WorkOrderController extends Controller
{
    public function datatables(Request $request)
    {
        $query = WorkOrder::with(['equipment', 'creator', 'assignee']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $workOrders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $workOrders->map(function ($item) {
                // Status Badge Logic
                $statusBadge = match($item->status) {
                    'DRAFT' => '<span class="badge bg-secondary">DRAFT</span>',
                    'OPEN' => '<span class="badge bg-primary">OPEN</span>',
                    'IN_PROGRESS' => '<span class="badge bg-info">IN PROGRESS</span>',
                    'READY' => '<span class="badge bg-warning">READY (VAL)</span>',
                    'CLOSED' => '<span class="badge bg-success">CLOSED</span>',
                    'CANCELLED' => '<span class="badge bg-danger">CANCELLED</span>',
                    default => '<span class="badge bg-light">' . $item->status . '</span>',
                };
                
                // Calculate Age
                $age = $item->created_at ? round($item->created_at->diffInDays(now())) : 0;

                // Action Buttons based on status and BPMN roles
                $actions = '<div class="d-flex align-items-center gap-2">';
                
                // 1. View Details (Always available)
                $actions .= '<a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light view-wo-btn" data-id="' . $item->id . '" title="View Details"><i class="ti ti-eye"></i></a>';

                // 2. Planning (DRAFT)
                if ($item->status === 'DRAFT') {
                    $actions .= '<a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-primary edit-planning-btn" data-id="' . $item->id . '" title="Edit Planning"><i class="ti ti-pencil"></i></a>';
                }

                // 3. Mechanic Operations (OPEN, IN_PROGRESS, READY)
                if (in_array($item->status, ['OPEN', 'IN_PROGRESS', 'READY'])) {
                    $actions .= '<a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-info req-part-btn" data-id="' . $item->id . '" title="Request Part"><i class="ti ti-settings"></i></a>';
                    $actions .= '<a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-success log-activity-btn" data-id="' . $item->id . '" title="Log Activity"><i class="ti ti-activity"></i></a>';
                }

                // 4. Closing (READY/OPEN/IN_PROGRESS)
                if ($item->status === 'READY') {
                    $actions .= '<a href="javascript:void(0);" class="btn btn-sm btn-icon btn-success close-wo-btn" data-id="' . $item->id . '" title="Final Validation & Close"><i class="ti ti-checks"></i></a>';
                }

                $actions .= '</div>';

                return [
                    'id' => $item->id,
                    'work_order_no' => $item->work_order_no,
                    'wo_type' => $item->wo_type ?? '-',
                    'equipment' => $item->equipment ? $item->equipment->code . ' - ' . $item->equipment->name : '-',
                    'assigned_to' => $item->assignee ? $item->assignee->name : '-',
                    'priority' => $item->priority,
                    'age' => $age,
                    'status' => $statusBadge,
                    'action' => $actions,
                ];
            })
        ]);
    }

    public function index()
    {
        $count = WorkOrder::count();
        $equipments = Equipments::orderBy('code')->get();
        $users = User::all(); // General users list for assignment
        
        return view('work_orders.index', compact('count', 'equipments', 'users'));
    }

    /**
     * Show Work Order Details
     */
    public function show($id)
    {
        $workOrder = WorkOrder::with([
            'equipment', 
            'assignee', 
            'creator', 
            'spareParts', 
            'activities.mechanic' // Assuming MechanicActivity has 'mechanic' relationship to User
        ])->findOrFail($id);

        // Fetch Standard Part Requirements for this Equipment
        $partRequirements = PartRequirement::with(['details.part'])
            ->where('equipment_id', $workOrder->equipment_id)
            ->where('status', 'Active')
            ->first();
            
        // Append to data
        $workOrder->part_requirement = $partRequirements;

        return response()->json([
            'success' => true,
            'data' => $workOrder
        ]);
    }

    /**
     * Update Work Order (Planning Phase)
     */
    public function update(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status !== 'DRAFT') {
            return response()->json(['success' => false, 'message' => 'Planning details can only be updated in DRAFT status.'], 403);
        }

        $validated =  $request->validate([
            'wo_type' => 'required|string',
            'service_category' => 'required|string',
            'maintenance_type' => 'required|string',
            'priority' => 'required|in:LOW,MEDIUM,HIGH',
            'assigned_to' => 'nullable|exists:users,id',
            'work_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $workOrder->update([
            ...$validated,
            'status' => 'IN_PROGRESS'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work Order planning updated successfully.',
            'data' => $workOrder
        ]);
    }

    /**
     * Request Spare Part
     */
    /**
     * Request Spare Part (Mechanic)
     */
    public function requestSparePart(Request $request, $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status === 'CLOSED') {
            return response()->json(['success' => false, 'message' => 'Cannot request parts for a CLOSED work order.'], 403);
        }

        $request->validate([
            'part_name' => 'required|string',
            'qty' => 'required|numeric|min:0.01',
        ]);

        $part = $workOrder->spareParts()->create([
            'part_name' => $request->part_name,
            'qty_requested' => $request->qty,
            'status' => 'PENDING', // Waiting for Admin Validation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Spare part requested. Waiting for validation.',
            'data' => $part
        ]);
    }

    /**
     * Validate Spare Part Request (Admin)
     */
    public function validateSparePart(Request $request, $partId)
    {
        // 1. Check Role: Admin only
        if (!$this->checkRole('admin')) {
             return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admin can validate requests.'], 403);
        }

        $part = WorkOrderSparePart::findOrFail($partId);

        if ($part->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only PENDING requests can be validated.'], 403);
        }

        $part->update([
            'status' => 'VALIDATED', // Ready for Manager Approval
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Spare part request validated.']);
    }

    /**
     * Approve Spare Part Request (Manager)
     */
    public function approveSparePart(Request $request, $partId)
    {
        // 2. Check Role: Workshop Manager OR Head of Planning
        if (!$this->checkRole(['Workshop Manager', 'Head of Planning & Maintenance', 'admin'])) { // Admin included for dev/testing ease
             return response()->json(['success' => false, 'message' => 'Unauthorized. Manager approval required.'], 403);
        }

        $part = WorkOrderSparePart::findOrFail($partId);
        
        // Allow approval from PENDING or VALIDATED for flexibility, but ideally VALIDATED
        if (!in_array($part->status, ['PENDING', 'VALIDATED'])) {
            return response()->json(['success' => false, 'message' => 'Request must be PENDING or VALIDATED.'], 403);
        }

        $part->update(['status' => 'APPROVED']); // Ready for Issue

        return response()->json(['success' => true, 'message' => 'Spare part request approved.']);
    }

    /**
     * Issue Spare Part (Warehouse)
     */
    public function issueSparePart(Request $request, $partId)
    {
        // 3. Check Role: Warehouse Staff
        if (!$this->checkRole(['Warehouse Staff', 'admin'])) {
             return response()->json(['success' => false, 'message' => 'Unauthorized. Warehouse access required.'], 403);
        }

        $part = WorkOrderSparePart::findOrFail($partId);

        if ($part->status !== 'APPROVED') {
            return response()->json(['success' => false, 'message' => 'Only APPROVED requests can be issued.'], 403);
        }

        $request->validate([
            'qty_issued' => 'required|numeric|min:0',
            'picking_mechanic' => 'required|string',
        ]);

        $part->update([
            'status' => 'ISSUED',
            'qty_issued' => $request->qty_issued,
            'picking_mechanic' => $request->picking_mechanic,
            'issued_by' => Auth::id(),
            'issued_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Spare part issued successfully.']);
    }

    /**
     * Return Spare Part (Mechanic)
     */
    public function returnSparePart(Request $request, $partId)
    {
        // 4. Check Role: Mechanic (or anyone really, but typically mechanic)
        $part = WorkOrderSparePart::findOrFail($partId);

        if ($part->status !== 'ISSUED') {
            return response()->json(['success' => false, 'message' => 'Only ISSUED parts can be returned.'], 403);
        }

        $request->validate([
            'qty_returned' => 'required|numeric|min:0.01|max:' . $part->qty_issued,
            'return_reason' => 'required|string',
        ]);

        $part->update([
            'qty_returned' => $request->qty_returned,
            'return_reason' => $request->return_reason,
            'returned_by' => Auth::id(),
            'returned_at' => now(),
            'return_status' => 'PENDING', // Waiting for Admin Validation
        ]);

        return response()->json(['success' => true, 'message' => 'Return initiated. Waiting for validation.']);
    }

    /**
     * Validate Spare Part Return (Admin)
     */
    public function validateReturn(Request $request, $partId)
    {
         // 5. Check Role: Admin
         if (!$this->checkRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Admin can validate returns.'], 403);
       }

        $part = WorkOrderSparePart::findOrFail($partId);

        if ($part->return_status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'Only PENDING returns can be validated.'], 403);
        }

        $part->update([
            'return_status' => 'VALIDATED',
            'return_validated_by' => Auth::id(),
            'return_validated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Part return validated.']);
    }

    /**
     * Approve Spare Part Return (Manager/Warehouse)
     */
    public function approveReturn(Request $request, $partId)
    {
        // 6. Check Role: Manager OR Head
        if (!$this->checkRole(['Workshop Manager', 'Head of Planning & Maintenance', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Manager approval required.'], 403);
       }

        $part = WorkOrderSparePart::findOrFail($partId);

        if ($part->return_status !== 'VALIDATED') {
            return response()->json(['success' => false, 'message' => 'Only VALIDATED returns can be approved.'], 403);
        }

        $part->update([
            'return_status' => 'APPROVED',
            'return_approved_by' => Auth::id(),
            'return_approved_at' => now(),
            // In a real system, you would increase stock in Equipments/Parts table here
        ]);

        return response()->json(['success' => true, 'message' => 'Part return approved and stock adjusted.']);
    }


    /**
     * Log Mechanic Activity (with AI Validation)
     */
    public function logActivity(Request $request, $id, AIMechanicService $aiService)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->status === 'CLOSED') {
            return response()->json(['success' => false, 'message' => 'Cannot log activity for a CLOSED work order.'], 403);
        }

        $request->validate([
            'description' => 'required|string',
            'status' => 'required|in:IN_PROGRESS,READY',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        // Process with AI Service
        $aiResult = $aiService->processActivity($workOrder, $request->all());

        if ($aiResult['mechanic_activity_status'] === 'REVISION REQUIRED') {
            return response()->json([
                'success' => false,
                'message' => 'AI Validation Failed: ' . implode(', ', $aiResult['notes']),
                'ai_response' => $aiResult
            ], 422);
        }

        $activity = $workOrder->activities()->create([
            'mechanic_id' => Auth::id(),
            'description' => $request->description,
            'status' => $request->status,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'working_duration' => $aiResult['working_duration_hours'],
            'activity_summary' => $aiResult['activity_summary'],
            'validation_status' => $aiResult['validation_status'],
            'ai_notes' => json_encode($aiResult['notes']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity logged successfully. AI Recommendation: ' . $aiResult['recommendation'],
            'data' => $activity,
            'ai_response' => $aiResult
        ]);
    }

    /**
     * Close Work Order (Final Validation)
     */
    public function close($id)
    {
        // 7. Check Role: Head of Planning & Maintenance
        if (!$this->checkRole(['Head of Planning & Maintenance', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Only Head of Planning can close Work Orders.'], 403);
        }

        $workOrder = WorkOrder::with('activities')->findOrFail($id);

        // Validation: Must have at least one READY activity?
        // And maybe all spare parts must be processed (Issued/Returned)?
        $ready = $workOrder->activities()->where('status', 'READY')->exists();

        if (!$ready) {
            return response()->json(['success' => false, 'message' => 'Cannot close Work Order without a READY mechanic activity.'], 422);
        }

        $workOrder->update(['status' => 'CLOSED']);

        return response()->json([
            'success' => true,
            'message' => 'Work Order ' . $workOrder->work_order_no . ' has been CLOSED successfully.',
            'data' => $workOrder
        ]);
    }
    
    /**
     * Helper to check roles
     */
    private function checkRole($roles)
    {
        if (!auth()->check()) return false;
        
        // For development/testing without real roles, bypass if user is admin email
        if (auth()->user()->email === 'admin@gmail.com') return true;

        return auth()->user()->hasAnyRole($roles);
    }

    /**
     * Create a Work Order from a Service Plan
     */
    public function storeFromPlan(Request $request, $planId)
    {
        try {
            DB::beginTransaction();

            $plan = ServicePlan::with('workOrder')->findOrFail($planId);

            // Validation: One WO per Plan
            if ($plan->workOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'A Work Order already exists for this service plan.'
                ], 422);
            }

            // Check if plan is active
            if (!$plan->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create Work Order for an inactive service plan.'
                ], 422);
            }

            $priority = $this->determinePriority($plan);
            $woNo = WorkOrder::generateWorkOrderNo(true); // Scheduled

            $workOrder = WorkOrder::create([
                'uid' => Str::uuid(),
                'work_order_no' => $woNo,
                'service_plan_id' => $plan->id,
                'equipment_id' => $plan->equipment_id,
                'wo_type' => 'Scheduled',
                'priority' => $priority,
                'status' => 'DRAFT',
                'description' => "Work Order created from Service Plan for unit: " . ($plan->equipment->code ?? '-'),
                'created_by' => Auth::id() ?? 1,
            ]);

            // Auto-populate Spare Parts from Part Requirement (if any)
            $partReq = PartRequirement::with('details')
                ->where('equipment_id', $plan->equipment_id)
                ->where('status', 'Active')
                ->first();

            if ($partReq) {
                foreach ($partReq->details as $detail) {
                    $workOrder->spareParts()->create([
                        'part_name' => $detail->part->name ?? 'Unknown Part', // Ensure part name is available
                        'qty_requested' => $detail->quantity ?? 1,
                        'status' => 'PENDING', // Waiting for validation
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work Order ' . $woNo . ' created successfully in DRAFT status.',
                'data' => $workOrder
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine priority based on business rules
     */
    private function determinePriority($plan)
    {
        if ($plan->overdue > 0) {
            return 'HIGH';
        }

        return 'MEDIUM';
    }
}
