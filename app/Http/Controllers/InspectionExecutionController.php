<?php

namespace App\Http\Controllers;

use App\Models\InspectionResult;
use App\Models\InspectionResultItem;
use App\Models\InspectionForm;
use App\Models\WorkRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InspectionExecutionController extends Controller
{
    /**
     * Display a listing of pending inspections for current user
     */
    public function index()
    {
        return view('inspections.index');
    }

    /**
     * DataTables AJAX endpoint for pending inspections
     */
    public function datatables()
    {
        $inspections = InspectionResult::with(['form', 'unit', 'inspector'])
            ->where('inspector_id', Auth::id())
            ->orWhereNull('inspector_id')
            ->select('inspection_results.*');

        return DataTables::of($inspections)
            ->addColumn('form_title', function ($inspection) {
                return $inspection->form->form_title ?? '-';
            })
            ->addColumn('unit_name', function ($inspection) {
                return $inspection->unit->name ?? '-';
            })
            ->addColumn('inspection_date', function ($inspection) {
                return $inspection->inspection_date->format('Y-m-d');
            })
            ->addColumn('status_badge', function ($inspection) {
                $badges = [
                    'PENDING' => '<span class="badge bg-warning">Pending</span>',
                    'PASS' => '<span class="badge bg-success">Pass</span>',
                    'FAIL' => '<span class="badge bg-danger">Fail</span>',
                ];
                return $badges[$inspection->overall_status] ?? '';
            })
            ->addColumn('action', function ($inspection) {
                if ($inspection->overall_status === 'PENDING') {
                    return '<a href="' . route('inspections.execute', $inspection->id) . '" class="btn btn-sm btn-primary">Start Inspection</a>';
                } else {
                    return '<a href="' . route('inspections.result', $inspection->id) . '" class="btn btn-sm btn-info">View Result</a>';
                }
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Show inspection form for execution
     */
    public function show($id)
    {
        $result = InspectionResult::with(['form.sections.items', 'unit'])->findOrFail($id);
        
        // Assign to current user if not assigned
        if (!$result->inspector_id) {
            $result->inspector_id = Auth::id();
            $result->start_time = now();
            $result->save();
        }

        return view('inspections.execute', compact('result'));
    }

    /**
     * Submit inspection result
     */
    public function submit(Request $request, $id)
    {
        $result = InspectionResult::findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inspection_items,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Save result items
            foreach ($request->items as $itemData) {
                $item = \App\Models\InspectionItem::findOrFail($itemData['item_id']);
                
                // Validate required fields
                if ($item->is_required && empty($itemData['value_text']) && empty($itemData['value_number']) && empty($itemData['value_option'])) {
                    throw new \Exception("Item '{$item->item_name}' is required");
                }

                $resultItem = InspectionResultItem::create([
                    'result_id' => $result->id,
                    'item_id' => $itemData['item_id'],
                    'value_text' => $itemData['value_text'] ?? null,
                    'value_number' => $itemData['value_number'] ?? null,
                    'value_option' => $itemData['value_option'] ?? null,
                    'image_path' => $itemData['image_path'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // Check and trigger auto-actions
                $value = $itemData['value_option'] ?? $itemData['value_number'] ?? $itemData['value_text'];
                if ($item->shouldTriggerAutoAction($value)) {
                    $this->triggerAutoAction($result, $item, $resultItem, $value);
                }
            }

            // Update result
            $result->end_time = now();
            $result->notes = $request->notes;
            $result->overall_status = $result->calculateOverallStatus();
            $result->unit_ready_for_operation = $result->overall_status === 'PASS';
            $result->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inspection submitted successfully',
                'data' => [
                    'result_id' => $result->id,
                    'overall_status' => $result->overall_status,
                    'unit_ready' => $result->unit_ready_for_operation
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show inspection result details
     */
    public function result($resultId)
    {
        $result = InspectionResult::with([
            'form.sections.items',
            'unit',
            'inspector',
            'resultItems.item'
        ])->findOrFail($resultId);

        return view('inspections.result', compact('result'));
    }

    /**
     * Show inspection history for a unit
     */
    public function history($unitId)
    {
        $results = InspectionResult::with(['form', 'inspector'])
            ->where('unit_id', $unitId)
            ->orderBy('inspection_date', 'desc')
            ->get();

        return view('inspections.history', compact('results', 'unitId'));
    }

    /**
     * Upload inspection image
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
            'item_id' => 'required|exists:inspection_items,id'
        ]);

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $request->item_id . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('inspections', $filename, 'public');

                return response()->json([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'data' => [
                        'path' => $path,
                        'url' => asset('storage/' . $path)
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No image file provided'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger auto-action based on item configuration
     */
    private function triggerAutoAction($result, $item, $resultItem, $value)
    {
        $autoAction = $item->getAutoActionConfig();
        
        if (!$autoAction) {
            return;
        }

        $triggeredActions = [];

        // Create Work Request
        if (isset($autoAction['action']) && $autoAction['action'] === 'CREATE_WR') {
            $workRequest = WorkRequest::create([
                'uid' => \Illuminate\Support\Str::uuid(),
                'request_no' => WorkRequest::generateRequestNo(),
                'equipment_id' => $result->unit_id,
                'request_type' => 'CORRECTIVE',
                'priority' => $autoAction['priority'] ?? 'MEDIUM',
                'description' => "Auto-generated from inspection: {$item->item_name} = {$value}",
                'requested_by' => $result->inspector_id,
                'status' => 'PENDING',
            ]);

            $triggeredActions[] = [
                'action' => 'CREATE_WR',
                'work_request_id' => $workRequest->id,
                'work_request_no' => $workRequest->request_no,
            ];
        }

        // Store triggered actions
        $resultItem->triggered_action = $triggeredActions;
        $resultItem->save();
    }
}
