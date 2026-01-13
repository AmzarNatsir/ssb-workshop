<?php
namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\Equipments;
use App\Models\wh\PartsTemp;
use App\Services\WorkRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WorkRequestController extends Controller
{
    protected $wrService;

    public function __construct(WorkRequestService $wrService)
    {
        $this->wrService = $wrService;
    }

    public function searchParts(Request $request)
    {
        $search = $request->term;
        $parts = PartsTemp::where('name', 'LIKE', "%{$search}%")
            ->orWhere('id', 'LIKE', "%{$search}%")
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $parts->map(function ($part) {
                return [
                    'id' => $part->name,
                    'text' => $part->name,
                    'price' => $part->price,
                    'unit' => 'Pcs', // Default unit if not in parts_temp, or we can add it later
                ];
            })
        ]);
    }

    public function index()
    {
        $count = WorkRequest::count();
        $equipments = Equipments::all();
        return view('work_requests.index', compact('count', 'equipments'));
    }

    public function datatables(Request $request)
    {
        $query = WorkRequest::with(['equipment', 'creator']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $workRequests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $workRequests->map(function ($item) {
                $statusBadge = match($item->status) {
                    'DRAFT' => '<span class="badge bg-secondary">DRAFT</span>',
                    'PENDING_APPROVAL' => '<span class="badge bg-warning">PENDING APPROVAL</span>',
                    'APPROVED' => '<span class="badge bg-success">APPROVED</span>',
                    'REJECTED' => '<span class="badge bg-danger">REJECTED</span>',
                    'IN_WORK_ORDER' => '<span class="badge bg-info">IN WORK ORDER</span>',
                    'CLOSED' => '<span class="badge bg-dark">CLOSED</span>',
                    default => '<span class="badge bg-light">UNKNOWN</span>',
                };

                $actions = '
                    <div class="d-flex align-items-center gap-2">
                        <a href="' . route('work-request.show', $item->uid) . '" class="btn btn-sm btn-icon btn-light" title="View Details">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';
                
                if (in_array($item->status, ['DRAFT', 'PENDING_APPROVAL'])) {
                    $actions .= '
                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light-primary edit-btn" data-id="' . $item->id . '" title="Edit Request">
                            <i class="ti ti-pencil"></i>
                        </a>
                    ';
                }

                if ($item->status === 'APPROVED') {
                    $actions .= '
                        <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-success create-wo-btn" data-id="' . $item->id . '" title="Create Work Order">
                            <i class="ti ti-settings"></i>
                        </a>
                    ';
                }

                $actions .= '</div>';

                return [
                    'id' => $item->id,
                    'wr_no' => $item->wr_no,
                    'category' => $item->category,
                    'equipment' => $item->equipment ? $item->equipment->code . ' - ' . $item->equipment->name : '-',
                    'type' => $item->type,
                    'status' => $statusBadge,
                    'created_at' => $item->created_at->format('d M Y, h:i a'),
                    'action' => $actions,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'equipment_id' => 'required|exists:equipments,id',
            'operator_name' => 'nullable|string',
            'hm_km' => 'required|numeric',
            'asset_condition' => 'required|string',
            'trouble_description' => 'required|string',
            'type' => 'required|string',
            'submit_direct' => 'boolean',
            'items' => 'nullable|array',
            'items.*.part_name' => 'required_if:type,Goods Request|string',
            'items.*.qty' => 'required_if:type,Goods Request|numeric',
            'items.*.unit' => 'required_if:type,Goods Request|string',
        ]);

        // Manual check for duplicate parts
        if ($request->type === 'Goods Request' && $request->has('items')) {
            $partNames = collect($request->items)->pluck('part_name')->filter()->toArray();
            if (count($partNames) !== count(array_unique($partNames))) {
                return response()->json(['success' => false, 'message' => 'Duplicate parts found in the list.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $workRequest = WorkRequest::create([
                'category' => $request->category,
                'equipment_id' => $request->equipment_id,
                'operator_name' => $request->operator_name,
                'hm_km' => $request->hm_km,
                'asset_condition' => $request->asset_condition,
                'trouble_description' => $request->trouble_description,
                'location' => Equipments::find($request->equipment_id)->project_status ?? '-',
                'type' => $request->type,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            // Save items if Goods Request
            if ($request->type === 'Goods Request' && $request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['part_name'])) {
                        $workRequest->items()->create([
                            'part_name' => $item['part_name'],
                            'qty' => $item['qty'],
                            'price' => $item['price'] ?? 0,
                            'unit' => $item['unit'],
                        ]);
                    }
                }
            }

            if ($request->submit_direct) {
                $this->wrService->submit($workRequest);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work Request created successfully.',
                'data' => $workRequest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($uid)
    {
        $workRequest = WorkRequest::with(['equipment', 'creator', 'items', 'approvals.role', 'approvals.user', 'workOrder'])
            ->where('uid', $uid)
            ->firstOrFail();

        $userRoleIds = auth()->user()->roles->pluck('id');
        $pendingApproval = $workRequest->approvals()
            ->where('status', 'PENDING')
            ->whereIn('role_id', $userRoleIds)
            ->first();

        return view('work_requests.show', compact('workRequest', 'pendingApproval'));
    }

    public function edit($id)
    {
        $workRequest = WorkRequest::with('items')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $workRequest
        ]);
    }

    public function update(Request $request, $id)
    {
        $workRequest = WorkRequest::findOrFail($id);

        // Security check: only DRAFT or PENDING_APPROVAL can be edited? 
        // User said: Add Edit (status=PENDING)
        if (!in_array($workRequest->status, ['DRAFT', 'PENDING_APPROVAL'])) {
            return response()->json(['success' => false, 'message' => 'Only DRAFT or PENDING requests can be edited.'], 403);
        }

        $request->validate([
            'category' => 'required|string',
            'equipment_id' => 'required|exists:equipments,id',
            'operator_name' => 'nullable|string',
            'hm_km' => 'required|numeric',
            'asset_condition' => 'required|string',
            'trouble_description' => 'required|string',
            'type' => 'required|string',
            'items' => 'nullable|array',
            'items.*.part_name' => 'required_if:type,Goods Request|string',
            'items.*.qty' => 'required_if:type,Goods Request|numeric',
            'items.*.price' => 'nullable|numeric',
            'items.*.unit' => 'required_if:type,Goods Request|string',
        ]);

        // Manual check for duplicate parts
        if ($request->type === 'Goods Request' && $request->has('items')) {
            $partNames = collect($request->items)->pluck('part_name')->filter()->toArray();
            if (count($partNames) !== count(array_unique($partNames))) {
                return response()->json(['success' => false, 'message' => 'Duplicate parts found in the list.'], 422);
            }
        }

        DB::beginTransaction();
        try {
            $workRequest->update([
                'category' => $request->category,
                'equipment_id' => $request->equipment_id,
                'operator_name' => $request->operator_name,
                'hm_km' => $request->hm_km,
                'asset_condition' => $request->asset_condition,
                'trouble_description' => $request->trouble_description,
                'location' => Equipments::find($request->equipment_id)->project_status ?? '-',
                'type' => $request->type,
            ]);

            // Sync items
            $workRequest->items()->delete();
            if ($request->type === 'Goods Request' && $request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['part_name'])) {
                        $workRequest->items()->create([
                            'part_name' => $item['part_name'],
                            'qty' => $item['qty'],
                            'price' => $item['price'] ?? 0,
                            'unit' => $item['unit'],
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work Request updated successfully.',
                'data' => $workRequest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAssetDetails($id)
    {
        $equipment = Equipments::find($id);
        if (!$equipment) {
            return response()->json(['success' => false, 'message' => 'Asset not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'location' => $equipment->project_status ?? 'No Location',
            ]
        ]);
    }
    public function createWorkOrder($id)
    {
        $workRequest = WorkRequest::findOrFail($id);

        if ($workRequest->status !== 'APPROVED') {
            return response()->json(['success' => false, 'message' => 'Work Order can only be created for APPROVED requests.'], 403);
        }

        try {
            DB::beginTransaction();
            $wo = $this->wrService->createWorkOrderFromWR($workRequest);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Work Order ' . $wo->work_order_no . ' created successfully.',
                'data' => $wo
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
