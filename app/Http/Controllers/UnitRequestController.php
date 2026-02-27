<?php

namespace App\Http\Controllers;

use App\Models\UnitRequest;
use App\Models\UnitRequestItem;
use App\Models\Equipments;
use App\Models\WorkRequest;
use App\Models\Employee;
use App\Services\ProjectIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UnitRequestController extends Controller
{
    protected $projectService;

    public function __construct(ProjectIntegrationService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        $count = UnitRequest::count();
        $equipments = Equipments::all();
        $isConnected = $this->projectService->checkConnection();
        return view('unit_requests.index', compact('count', 'equipments', 'isConnected'));
    }

    public function datatables(Request $request)
    {
        $query = UnitRequest::with(['requestedBy', 'items.equipment']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $unitRequests = $query->orderBy('created_at', 'desc')->get();
        
        // Fetch project names for local-only records if name is missing
        $projectIds = $unitRequests->whereNull('project_name')->pluck('project_id')->filter()->unique()->toArray();
        $projectNames = !empty($projectIds) ? $this->projectService->getProjectNames($projectIds) : [];

        return response()->json([
            'data' => $unitRequests->map(function ($item) use ($projectNames) {
                $statusBadge = match($item->status) {
                    'DRAFT'                  => '<span class="badge bg-secondary">DRAFT</span>',
                    'SUBMITTED'              => '<span class="badge bg-warning">SUBMITTED</span>',
                    'GA_VALIDATED'           => '<span class="badge bg-info">GA VALIDATED</span>',
                    'APPROVED'               => '<span class="badge bg-success">APPROVED</span>',
                    'REJECTED'               => '<span class="badge bg-danger">REJECTED</span>',
                    'RFU'                    => '<span class="badge bg-primary">READY FOR USE</span>',
                    'FINALIZED'              => '<span class="badge bg-dark">FINALIZED</span>',
                    'FORWARDED_TO_WORKSHOP'  => '<span class="badge bg-warning text-dark">FORWARDED TO WORKSHOP</span>',
                    default                  => '<span class="badge bg-light">UNKNOWN</span>',
                };

                return [
                    'id'           => $item->id,
                    'request_no'   => $item->request_no,
                    'project'      => $item->project_name ?? ($projectNames[$item->project_id] ?? 'Project #' . $item->project_id),
                    'total_units'  => $item->total_units,
                    'requested_by' => $item->requestedBy ? $item->requestedBy->name : 'System/API',
                    'created_at'   => $item->created_at->format('d M Y, h:i a'),
                    'status'       => $statusBadge,
                    'action'       => '
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light view-btn" data-uid="' . $item->uid . '" title="View Details">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>',
                ];
            })
        ]);
    }

    public function sync()
    {
        try {
            $remoteRequests = $this->projectService->getUnitRequests('FORWARDED_TO_WORKSHOP');
            $newCount = 0;

            DB::beginTransaction();
            foreach ($remoteRequests as $remote) {
                $exists = UnitRequest::where('request_no', $remote['request_number'])->exists();
                if ($exists) continue;

                $unitRequest = UnitRequest::create([
                    'request_no'   => $remote['request_number'],
                    'project_id'   => $remote['project_id'],
                    'project_name' => $remote['project']['name'] ?? ($remote['project']['project_name'] ?? null),
                    'status'       => 'FORWARDED_TO_WORKSHOP',
                    'remarks'      => $remote['notes'] ?? null,
                    'total_units'  => isset($remote['items']) ? count($remote['items']) : 0,
                    'requested_by' => null, // Originates from Project System
                ]);

                if (isset($remote['items']) && is_array($remote['items'])) {
                    foreach ($remote['items'] as $it) {
                        $unitRequest->items()->create([
                            'equipment_id' => null, // To be assigned in workshop
                            'unit_name'    => $it['unit_name'] ?? ($it['quotation_item']['unit_name'] ?? 'Unknown Unit'),
                            'status'       => 'PENDING',
                        ]);
                    }
                }
                $newCount++;
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$newCount} new unit requests.",
                'count'   => $newCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function getAll(Request $request)
    {
        $unitRequests = UnitRequest::with(['requestedBy', 'items.equipment'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success' => true, 'data' => $unitRequests]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.equipment_id' => 'required|exists:equipments,id',
        ]);

        DB::beginTransaction();
        try {
            $unitRequest = UnitRequest::create([
                'project_id' => $request->project_id,
                'remarks' => $request->remarks,
                'requested_by' => Auth::id(),
                'status' => 'DRAFT',
                'total_units' => count($request->items),
            ]);

            foreach ($request->items as $item) {
                $unitRequest->items()->create([
                    'equipment_id' => $item['equipment_id'],
                    'status' => 'PENDING',
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Unit Request created successfully.', 'data' => $unitRequest->load('items')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($uid)
    {
        $unitRequest = UnitRequest::with([
            'requestedBy', 
            'gaValidatedBy', 
            'omApprovedBy', 
            'items.equipment', 
            'items.mechanic', 
            'items.workRequest', 
            'items.operator',
            'items.p2hResult'
        ])->where('uid', $uid)->firstOrFail();

        return response()->json(['success' => true, 'data' => $unitRequest]);
    }

    public function submitToGA($id)
    {
        $unitRequest = UnitRequest::findOrFail($id);
        if ($unitRequest->status !== 'DRAFT') {
            return response()->json(['success' => false, 'message' => 'Only DRAFT requests can be submitted.'], 403);
        }

        $unitRequest->update(['status' => 'SUBMITTED']);
        return response()->json(['success' => true, 'message' => 'Request submitted to General Affairs.']);
    }

    public function validateGA(Request $request, $id)
    {
        $unitRequest = UnitRequest::findOrFail($id);
        if ($unitRequest->status !== 'SUBMITTED') {
            return response()->json(['success' => false, 'message' => 'Only SUBMITTED requests can be validated by GA.'], 403);
        }

        $unitRequest->update([
            'status' => 'GA_VALIDATED',
            'ga_validated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Request validated by GA and forwarded to Operational Manager.']);
    }

    public function approveOM(Request $request, $id)
    {
        $unitRequest = UnitRequest::findOrFail($id);
        if ($unitRequest->status !== 'GA_VALIDATED') {
            return response()->json(['success' => false, 'message' => 'Only GA_VALIDATED requests can be approved by OM.'], 403);
        }

        if ($request->action === 'REJECT') {
            $unitRequest->update(['status' => 'REJECTED']);
            return response()->json(['success' => true, 'message' => 'Request rejected and returned for revision.']);
        }

        $unitRequest->update([
            'status' => 'APPROVED',
            'om_approved_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Request approved by Operational Manager.']);
    }

    public function assignMechanic(Request $request, $itemId)
    {
        $item = UnitRequestItem::findOrFail($itemId);
        $request->validate([
            'mechanic_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            // Create Work Request for commissioning
            $equipment = $item->equipment;
            $workRequest = WorkRequest::create([
                'category' => 'Commissioning',
                'equipment_id' => $item->equipment_id,
                'trouble_description' => 'Unit Request Commissioning for Request #' . $item->request->request_no,
                'hm_km' => $equipment->meterReading->last_reading ?? 0,
                'asset_condition' => 'Good',
                'location' => 'Workshop',
                'type' => 'Service Request',
                'status' => 'APPROVED', // Skip approval for internal commissioning?
                'created_by' => Auth::id(),
            ]);

            $item->update([
                'mechanic_id' => $request->mechanic_id,
                'work_request_id' => $workRequest->id,
                'status' => 'ASSIGNED',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mechanic assigned and Work Request created.', 'work_request' => $workRequest]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markRFU($itemId)
    {
        $item = UnitRequestItem::findOrFail($itemId);
        if ($item->status !== 'ASSIGNED') {
            return response()->json(['success' => false, 'message' => 'Only ASSIGNED units can be marked as RFU.'], 403);
        }

        $item->update(['status' => 'RFU']);
        return response()->json(['success' => true, 'message' => 'Unit marked as Ready For Use (RFU).']);
    }

    public function finalizeMobilization(Request $request, $itemId)
    {
        $item = UnitRequestItem::findOrFail($itemId);
        $request->validate([
            'operator_id' => 'required|exists:employees,id',
            'hm_start' => 'required|numeric',
            'km_start' => 'required|numeric',
            'fuel_level' => 'required|string',
            'refuel_status' => 'required|string',
            'attachments' => 'nullable|array',
        ]);

        $item->update([
            'operator_id' => $request->operator_id,
            'hm_start' => $request->hm_start,
            'km_start' => $request->km_start,
            'fuel_level' => $request->fuel_level,
            'refuel_status' => $request->refuel_status,
            'attachment_paths' => $request->attachments,
            'status' => 'FINALIZED',
        ]);

        // Check if all items in the request are finalized
        $requestHeader = $item->request;
        if ($requestHeader->items()->where('status', '!=', 'FINALIZED')->count() === 0) {
            $requestHeader->update(['status' => 'RFU']); // Or a status indicating ready for manager validation
        }

        return response()->json(['success' => true, 'message' => 'Mobilization data finalized.']);
    }


    public function validateFinalized($id)
    {
        $unitRequest = UnitRequest::findOrFail($id);
        // Prompt: Approved units proceed to Mobilization Module.
        $unitRequest->update(['status' => 'FINALIZED']);
        
        return response()->json(['success' => true, 'message' => 'Unit request finalized and ready for mobilization.']);
    }

    public function projectSearch(Request $request)
    {
        $results = $this->projectService->searchProjects($request->term);
        return response()->json(['results' => $results]);
    }
}
