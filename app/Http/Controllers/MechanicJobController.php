<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\MasterActivity;
use App\Models\MechanicActivity;
use App\Models\MasterComponentTrouble;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\AIMechanicService;
use Illuminate\Support\Str;

class MechanicJobController extends Controller
{
    /**
     * Display a listing of assigned jobs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Fetch WOs assigned to current user and status is OPEN or IN_PROGRESS
            $workOrders = WorkOrder::with(['equipment', 'servicePlan'])
                ->where('assigned_to', Auth::id())
                ->whereIn('status', ['OPEN', 'IN_PROGRESS', 'READY']) // Include READY so they can see what they finished
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $workOrders->map(function ($row) {
                    $badgeClass = match ($row->status) {
                        'OPEN' => 'bg-primary',
                        'IN_PROGRESS' => 'bg-info',
                        'READY' => 'bg-success',
                        default => 'bg-secondary',
                    };
                    $statusHtml = '<span class="badge ' . $badgeClass . '">' . $row->status . '</span>';

                    $priorityColor = match ($row->priority) {
                        'HIGH' => 'text-danger fw-bold',
                        'MEDIUM' => 'text-warning fw-bold',
                        default => 'text-success',
                    };
                    $priorityHtml = '<span class="' . $priorityColor . '">' . $row->priority . '</span>';
                    
                    $actionHtml = '<div class="d-flex gap-1">
                                    <a href="' . route('mechanic-job.show', $row->id) . '" class="btn btn-sm btn-primary">
                                        <i class="ti ti-tool"></i> Start Job
                                    </a>
                                    <button class="btn btn-sm btn-info btn-view-result" data-id="' . $row->id . '">
                                        <i class="ti ti-file-text"></i>
                                    </button>
                                </div>';

                    return [
                        'work_order_no' => $row->work_order_no,
                        'equipment_name' => $row->equipment ? $row->equipment->code . ' - ' . $row->equipment->name : '-',
                        'priority' => $priorityHtml,
                        'status' => $statusHtml,
                        'created_at' => $row->created_at->format('d M Y, h:i a'),
                        'action' => $actionHtml
                    ];
                })
            ]);
        }

        return view('mechanic_jobs.index');
    }

    /**
     * Display the specified job with checklist.
     */
    public function show($id)
    {
        $workOrder = WorkOrder::with(['equipment', 'servicePlan', 'activities'])->findOrFail($id);
        
        // Security check
        if ($workOrder->assigned_to != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this job.');
        }

        // Fetch Master Activities for Checklist
        // Optional: Filter by category if matched with WO -> equipment -> category?
        // For now, get all grouped by category
        $masterActivities = MasterActivity::all()->groupBy('category');

        // Fetch Master Component Troubles
        $componentTroubles = MasterComponentTrouble::all();

        return view('mechanic_jobs.show', compact('workOrder', 'masterActivities', 'componentTroubles'));
    }

    /**
     * Store a checklist selection as a Mechanic Activity.
     */
    public function storeChecklist(Request $request, $id, AIMechanicService $aiService)
    {
        $request->validate([
            'activity_code' => 'required|exists:master_activities,code',
            'notes' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $masterActivity = MasterActivity::where('code', $request->activity_code)->first();

        // Prepare data for AI Service
        $activityData = $request->all();
        $activityData['description'] = $masterActivity->description . ($request->notes ? " - " . $request->notes : "");
        $activityData['status'] = 'IN_PROGRESS'; // Default status for checklist items

        // Process with AI Service
        $aiResult = $aiService->processActivity($workOrder, $activityData);

        if ($aiResult['mechanic_activity_status'] === 'REVISION REQUIRED') {
            return response()->json([
                'success' => false,
                'message' => 'AI Validation Failed: ' . implode(', ', $aiResult['notes']),
                'ai_response' => $aiResult
            ], 422);
        }

        $activity = $workOrder->activities()->create([
            'mechanic_id' => Auth::id(),
            'description' => $activityData['description'],
            'status' => 'IN_PROGRESS',
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
     * Store a component check result.
     */
    public function storeComponentCheck(Request $request, $id)
    {
        $request->validate([
            'component_id' => 'required|exists:master_component_troubles,id',
            'status' => 'required|in:GOOD,TROUBLE',
            'remarks' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $component = MasterComponentTrouble::findOrFail($request->component_id);

        $statusText = $request->status === 'TROUBLE' ? 'ISSUE FOUND' : 'GOOD';
        
        $description = "Component Check: {$component->component_name} - {$statusText}" . ($request->remarks ? ". Remarks: {$request->remarks}" : "");

        // Log as activity. 
        $activity = $workOrder->activities()->create([
            'mechanic_id' => Auth::id(),
            'description' => $description,
            'status' => 'READY', // Checks are instant and valid
            'start_time' => now(),
            'end_time' => now(),
            'working_duration' => 0,
            'activity_summary' => "Checked {$component->component_name}. Result: {$statusText}",
            'validation_status' => 'VALID',
            'ai_notes' => json_encode(['source' => 'component_check', 'trouble' => $request->status === 'TROUBLE']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Component check logged.',
        ]);
    }

    /**
     * Mark the job as finished (READY).
     */
    public function finishJob($id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        if ($workOrder->assigned_to != Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Optional: Check if at least one activity exists?
        if ($workOrder->activities()->count() == 0) {
            return response()->json(['success' => false, 'message' => 'Cannot finish job. No activities logged.'], 422);
        }

        $workOrder->update(['status' => 'READY']);

        return response()->json(['success' => true, 'message' => 'Job marked as READY.']);
    }

    /**
     * Get activity summary for modal.
     */
    public function summary($id)
    {
        $workOrder = WorkOrder::with(['activities.mechanic', 'equipment'])->findOrFail($id);
        
        // Group activities based on type detection (simple heuristic)
        $activities = $workOrder->activities()->latest()->get();
        
        $checklistItems = $activities->filter(function($act) {
            $notes = json_decode($act->ai_notes, true);
            return isset($notes['source']) && $notes['source'] === 'checklist';
        });

        $componentChecks = $activities->filter(function($act) {
            $notes = json_decode($act->ai_notes, true);
            return isset($notes['source']) && $notes['source'] === 'component_check';
        });

        $manualLogs = $activities->diff($checklistItems)->diff($componentChecks);

        $totalDuration = $activities->sum('working_duration');

        return view('mechanic_jobs.partials.summary_modal', compact('workOrder', 'checklistItems', 'componentChecks', 'manualLogs', 'totalDuration'));
    }
}
