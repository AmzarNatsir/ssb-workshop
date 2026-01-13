<?php

namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\WorkRequestApproval;
use App\Services\WorkRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkRequestApprovalController extends Controller
{
    protected $wrService;

    public function __construct(WorkRequestService $wrService)
    {
        $this->wrService = $wrService;
    }

    public function index()
    {
        return view('work_requests.approvals');
    }

    public function datatables(Request $request)
    {
        $user = Auth::user();
        $userRoleIds = $user->roles->pluck('id');

        $approvals = WorkRequestApproval::with(['workRequest.equipment', 'workRequest.creator', 'role'])
            ->whereIn('role_id', $userRoleIds)
            ->where('status', 'PENDING')
            ->get();

        return response()->json([
            'data' => $approvals->map(function ($item) {
                return [
                    'wr_no' => $item->workRequest->wr_no,
                    'equipment' => $item->workRequest->equipment->code . ' - ' . $item->workRequest->equipment->name,
                    'requested_by' => $item->workRequest->creator->name,
                    'type' => $item->workRequest->type,
                    'requested_at' => $item->workRequest->created_at->format('d M Y, H:i'),
                    'action' => '
                        <div class="d-flex align-items-center gap-2">
                            <a href="' . route('work-request.show', $item->workRequest->uid) . '" class="btn btn-sm btn-icon btn-light" title="View Details">
                                <i class="ti ti-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-success approve-btn" data-id="' . $item->id . '">
                                <i class="ti ti-check me-1"></i>Approve
                            </button>
                            <button class="btn btn-sm btn-danger reject-btn" data-id="' . $item->id . '">
                                <i class="ti ti-x me-1"></i>Reject
                            </button>
                        </div>
                    '
                ];
            })
        ]);
    }

    public function approve(Request $request, $id)
    {
        $approval = WorkRequestApproval::findOrFail($id);
        
        // Security check: user must have the role
        if (!Auth::user()->hasRole($approval->role->name)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $this->wrService->updateApprovalStep($approval, 'APPROVED', $request->comment);
            return response()->json(['success' => true, 'message' => 'Work Request approved.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $approval = WorkRequestApproval::findOrFail($id);
        
        if (!Auth::user()->hasRole($approval->role->name)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate(['comment' => 'required|string']);

        try {
            $this->wrService->updateApprovalStep($approval, 'REJECTED', $request->comment);
            return response()->json(['success' => true, 'message' => 'Work Request rejected.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
