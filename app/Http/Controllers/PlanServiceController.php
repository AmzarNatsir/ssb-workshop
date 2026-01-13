<?php

namespace App\Http\Controllers;

use App\Models\ServicePlan;
use App\Models\ServiceHistory;
use App\Models\Equipments;
use App\Models\User;
use App\Services\ServicePlanCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PlanServiceController extends Controller
{
    protected $calculator;

    public function __construct()
    {
        $this->calculator = new ServicePlanCalculator();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $count = ServicePlan::count();
        $equipments = Equipments::with('periodicServiceType')->get();
        return view('plan_service.index', compact('count', 'equipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $equipments = Equipments::with('periodicServiceType')->get();
        return view('plan_service.create', compact('equipments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'hm_ps_sebelumnya' => 'required|numeric|min:0',
            'hm_actual' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get equipment
            $equipment = Equipments::findOrFail($request->equipment_id);

            // Validate WH/Project
            if (!$equipment->wh_per_project || $equipment->wh_per_project <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment WH/Project is invalid. Please update equipment master data first.'
                ], 422);
            }

            // Check for existing active plan
            $existingPlan = ServicePlan::where('equipment_id', $request->equipment_id)
                ->where('is_active', true)
                ->first();

            if ($existingPlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment already has an active service plan. Please complete or cancel the existing plan first.'
                ], 422);
            }

            // Calculate service plan
            $calculation = $this->calculator->calculate(
                $request->hm_ps_sebelumnya,
                $request->hm_actual,
                $equipment->wh_per_project
            );

            // Create service plan
            $servicePlan = ServicePlan::create([
                'uid' => Str::uuid(),
                'equipment_id' => $request->equipment_id,
                'hm_ps_sebelumnya' => $request->hm_ps_sebelumnya,
                'hm_actual' => $request->hm_actual,
                'wh_project' => $equipment->wh_per_project, // Snapshot
                'overdue' => $calculation['overdue'],
                'ps_berikutnya' => $calculation['ps_berikutnya'],
                'plan_date' => $calculation['plan_date'],
                'service_type' => $calculation['service_type'],
                'status' => $calculation['status'],
                'is_active' => true,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service plan created successfully.',
                'data' => $servicePlan
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        $servicePlan = ServicePlan::with('equipment')->findOrFail($id);
        
        // Only allow editing PLANNED or OVERDUE plans
        if (!in_array($servicePlan->status, ['PLANNED', 'OVERDUE'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot edit ' . $servicePlan->status . ' service plans.'], 422);
            }
            return redirect()->route('plan-service.index')
                ->with('error', 'Cannot edit ' . $servicePlan->status . ' service plans.');
        }

        $equipments = Equipments::with('periodicServiceType')->get();
        
        if ($request->ajax()) {
            return view('plan_service.edit', compact('servicePlan', 'equipments'))->render();
        }

        return view('plan_service.edit', compact('servicePlan', 'equipments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'hm_ps_sebelumnya' => 'required|numeric|min:0',
            'hm_actual' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $servicePlan = ServicePlan::findOrFail($id);

            // Only allow updating PLANNED or OVERDUE plans
            if (!in_array($servicePlan->status, ['PLANNED', 'OVERDUE'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update ' . $servicePlan->status . ' service plans.'
                ], 422);
            }

            $equipment = $servicePlan->equipment;

            // Recalculate with new values
            $calculation = $this->calculator->calculate(
                $request->hm_ps_sebelumnya,
                $request->hm_actual,
                $servicePlan->wh_project // Use snapshotted value
            );

            // Update service plan
            $servicePlan->update([
                'hm_ps_sebelumnya' => $request->hm_ps_sebelumnya,
                'hm_actual' => $request->hm_actual,
                'overdue' => $calculation['overdue'],
                'ps_berikutnya' => $calculation['ps_berikutnya'],
                'plan_date' => $calculation['plan_date'],
                'service_type' => $calculation['service_type'],
                'status' => $calculation['status'],
                'notes' => $request->notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service plan updated successfully.',
                'data' => $servicePlan
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $servicePlan = ServicePlan::findOrFail($id);
        $servicePlan->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Service plan deleted successfully.'
        ]);
    }

    /**
     * Complete a service plan
     */
    public function complete(Request $request, string $id)
    {
        $request->validate([
            'hm_at_service' => 'required|numeric|min:0',
            'service_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $servicePlan = ServicePlan::findOrFail($id);

            if (!in_array($servicePlan->status, ['PLANNED', 'OVERDUE'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot complete ' . $servicePlan->status . ' service plans.'
                ], 422);
            }

            // Update plan status
            $servicePlan->update([
                'status' => 'COMPLETED',
                'is_active' => false,
            ]);

            // Create service history
            ServiceHistory::create([
                'uid' => Str::uuid(),
                'service_plan_id' => $servicePlan->id,
                'hm_at_service' => $request->hm_at_service,
                'service_date' => $request->service_date,
                'service_type' => $servicePlan->service_type,
                'notes' => $request->notes,
                'performed_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service plan marked as completed successfully.'
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
     * Cancel a service plan
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $servicePlan = ServicePlan::findOrFail($id);

        $servicePlan->update([
            'status' => 'CANCELLED',
            'is_active' => false,
            'notes' => ($servicePlan->notes ? $servicePlan->notes . "\n\n" : '') . 
                       "[CANCELLED] " . $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service plan cancelled successfully.'
        ]);
    }

    /**
     * Calculate service plan (AJAX preview)
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|exists:equipments,id',
            'hm_ps_sebelumnya' => 'required|numeric|min:0',
            'hm_actual' => 'required|numeric|min:0',
        ]);

        try {
            $equipment = Equipments::findOrFail($request->equipment_id);

            $whProject = $request->wh_per_project ?: $equipment->wh_per_project;

            if (!$whProject || $whProject <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Equipment WH/Project is invalid.'
                ], 422);
            }

            $calculation = $this->calculator->calculate(
                $request->hm_ps_sebelumnya,
                $request->hm_actual,
                $whProject
            );

            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get equipment details (AJAX)
     */
    public function getEquipmentDetails(string $id)
    {
        $equipment = Equipments::with(['periodicServiceType', 'activeServicePlan'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $equipment->id,
                'code' => $equipment->code,
                'name' => $equipment->name,
                'wh_per_project' => $equipment->wh_per_project,
                'service_period' => $equipment->periodicServiceType ? $equipment->periodicServiceType->name : '-',
                'has_active_plan' => $equipment->activeServicePlan ? true : false,
            ]
        ]);
    }

    /**
     * Get datatables data
     */
    public function datatables(Request $request)
    {
        $query = ServicePlan::with(['equipment', 'creator']);

        // Filtering
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->equipment_id) {
            $query->where('equipment_id', $request->equipment_id);
        }
        if ($request->date_from) {
            $query->where('plan_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->where('plan_date', '<=', $request->date_to);
        }

        $servicePlans = $query->orderBy('plan_date', 'asc')->get();

        return response()->json([
            'data' => $servicePlans->map(function ($item) {
                $statusBadge = match($item->status) {
                    'PLANNED' => '<span class="badge bg-success">PLANNED</span>',
                    'OVERDUE' => '<span class="badge bg-danger">OVERDUE</span>',
                    'COMPLETED' => '<span class="badge bg-secondary">COMPLETED</span>',
                    'CANCELLED' => '<span class="badge bg-warning">CANCELLED</span>',
                    default => '<span class="badge bg-light">UNKNOWN</span>',
                };

                $overdueClass = $item->overdue > 0 ? 'text-danger fw-bold' : 'text-success';

                $actions = '';
                if (in_array($item->status, ['PLANNED', 'OVERDUE'])) {
                    $actions .= '
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light edit-btn" data-id="' . $item->id . '" title="Edit Plan">
                                <i class="ti ti-edit"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-success complete-btn" data-id="' . $item->id . '" title="Complete Service">
                                <i class="ti ti-check"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-warning cancel-btn" data-id="' . $item->id . '" title="Cancel Plan">
                                <i class="ti ti-x"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-danger delete-btn" data-id="' . $item->id . '" title="Delete Plan">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                    ';

                    // Work Order Creation Rule
                    if ($item->canCreateWorkOrder()) {
                        $actions .= '
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-soft-primary w-100 create-wo-btn" data-id="' . $item->id . '">
                                    <i class="ti ti-file-plus me-1"></i>Create Work Order
                                </button>
                            </div>
                        ';
                    }
                } else {
                    $actions = '<span class="text-muted">-</span>';
                }

                return [
                    'id' => $item->id,
                    'equipment_code' => $item->equipment ? $item->equipment->code : '-',
                    'equipment_name' => $item->equipment ? $item->equipment->name : '-',
                    'hm_actual' => number_format($item->hm_actual, 2),
                    'ps_berikutnya' => number_format($item->ps_berikutnya, 2),
                    'plan_date' => $item->plan_date ? $item->plan_date->format('d M Y') : '-',
                    'service_type' => $item->service_type,
                    'status' => $statusBadge,
                    'overdue' => '<span class="' . $overdueClass . '">' . number_format($item->overdue, 2) . '</span>',
                    'created_at' => $item->created_at ? $item->created_at->format('d M Y, h:i a') : '-',
                    'action' => $actions,
                ];
            })
        ]);
    }
}
