<?php

namespace App\Http\Controllers;

use App\Models\ToolStockOpname;
use App\Models\ToolStockOpnameDetail;
use App\Models\Tools;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ToolStockOpnameController extends Controller
{
    /**
     * Display a listing of stock opnames.
     */
    public function index()
    {
        $opnames = ToolStockOpname::with('creator')
            ->withCount('details')
            ->latest()
            ->paginate(10);
            
        return view('tool-lending.stock-opname.index', compact('opnames'));
    }

    /**
     * Show the form for creating a new opname.
     */
    public function create()
    {
        return view('tool-lending.stock-opname.create');
    }

    /**
     * Store a newly created opname and populate details.
     */
    public function store(Request $request)
    {
        $request->validate([
            'period' => 'required|in:MONTHLY,QUARTERLY,YEARLY',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $opnameNo = ToolStockOpname::generateOpnameNumber($request->period);

            $opname = ToolStockOpname::create([
                'opname_no' => $opnameNo,
                'period' => $request->period,
                'start_date' => $request->start_date,
                'status' => ToolStockOpname::STATUS_DRAFT,
                'created_by' => Auth::id(),
            ]);

            // Populate details from current Tools inventory
            $tools = Tools::with(['status', 'racks'])->get();
            
            foreach ($tools as $tool) {
                // Map system status to opname detail status
                $statusName = $tool->status->name ?? 'Available';
                $systemStatus = ToolStockOpnameDetail::SYSTEM_STATUS_AVAILABLE;
                
                if ($statusName === 'In Use') {
                    $systemStatus = ToolStockOpnameDetail::SYSTEM_STATUS_BORROWED;
                } elseif ($statusName === 'Broken') {
                    $systemStatus = ToolStockOpnameDetail::SYSTEM_STATUS_DAMAGED;
                } elseif ($statusName === 'Lost' || $statusName === 'Disposed') {
                    $systemStatus = ToolStockOpnameDetail::SYSTEM_STATUS_LOST;
                }

                ToolStockOpnameDetail::create([
                    'opname_id' => $opname->id,
                    'tool_id' => $tool->id,
                    'physical_exist' => ToolStockOpnameDetail::EXIST_YES,
                    'physical_condition' => ToolStockOpnameDetail::CONDITION_GOOD,
                    'physical_location' => $tool->shelf_location_display,
                    'system_status' => $systemStatus,
                    'mismatch' => false,
                ]);
            }

            AuditLog::log(
                'opname_started',
                'ToolStockOpname',
                $opname->id,
                null,
                ['opname_no' => $opnameNo, 'count' => $tools->count()]
            );

            DB::commit();

            return redirect()->route('tool-lending.stock-opname.show', $opname->id)
                ->with('success', 'Stock Opname ' . $opnameNo . ' started with ' . $tools->count() . ' tools.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to start opname: ' . $e->getMessage());
        }
    }

    /**
     * Display the opname dashboard.
     */
    public function show($id)
    {
        $opname = ToolStockOpname::with(['details.tool.status', 'creator'])->findOrFail($id);
        
        $stats = [
            'total' => $opname->details->count(),
            'missing' => $opname->details->where('physical_exist', ToolStockOpnameDetail::EXIST_NO)->count(),
            'excess' => $opname->details->where('notes', 'Manual Finding')->count(),
            'damaged' => $opname->details->where('physical_condition', ToolStockOpnameDetail::CONDITION_DAMAGE)->count(),
            'mismatch' => $opname->details->where('mismatch', true)->count(),
        ];

        // For "Add Finding" modal, show tools not in this opname snapshot
        $existingToolIds = $opname->details->pluck('tool_id')->toArray();
        $allTools = Tools::whereNotIn('id', $existingToolIds)->get();

        return view('tool-lending.stock-opname.show', compact('opname', 'stats', 'allTools'));
    }

    /**
     * Update physical status of a tool during opname (AJAX).
     */
    public function updateDetail(Request $request, $id)
    {
        $request->validate([
            'physical_exist' => 'required|in:EXISTS,NOT_EXISTS',
            'physical_condition' => 'required|in:GOOD,DAMAGE,LOST',
            'notes' => 'nullable|string',
        ]);

        try {
            $detail = ToolStockOpnameDetail::with('tool')->findOrFail($id);
            $opname = $detail->opname;

            if ($opname->status === ToolStockOpname::STATUS_FINAL) {
                return response()->json(['success' => false, 'message' => 'Cannot update finalized opname.'], 403);
            }

            // Update detail
            $detail->physical_exist = $request->physical_exist;
            $detail->physical_condition = $request->physical_condition;
            $detail->notes = $request->notes;

            // Logic for mismatch
            // 1. If physical_exist is NOT_EXISTS but system says it should be there (Available/Borrowed?)
            // Actually, if it's NOT_EXISTS, it's a mismatch if it's not already LOST in system.
            $mismatch = false;
            if ($detail->physical_exist === ToolStockOpnameDetail::EXIST_NO && $detail->system_status !== ToolStockOpnameDetail::SYSTEM_STATUS_LOST) {
                $mismatch = true;
            }
            // 2. If it exists but condition is DAMAGE and system says it's AVAILABLE
            if ($detail->physical_condition === ToolStockOpnameDetail::CONDITION_DAMAGE && $detail->system_status === ToolStockOpnameDetail::SYSTEM_STATUS_AVAILABLE) {
                $mismatch = true;
            }

            $detail->mismatch = $mismatch;
            $detail->save();

            // Update Opname status to IN_PROGRESS if it was DRAFT
            if ($opname->status === ToolStockOpname::STATUS_DRAFT) {
                $opname->status = ToolStockOpname::STATUS_IN_PROGRESS;
                $opname->save();
            }

            // Calculate updated stats for the dashboard summary cards
            $stats = [
                'missing' => $opname->details()->where('physical_exist', ToolStockOpnameDetail::EXIST_NO)->count(),
                'excess' => $opname->details()->where('notes', 'like', 'Manual Finding%')->count(),
                'damaged' => $opname->details()->where('physical_condition', ToolStockOpnameDetail::CONDITION_DAMAGE)->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Tool verification updated.',
                'mismatch' => $mismatch,
                'stats' => $stats,
                'physical_exist' => $detail->physical_exist,
                'physical_condition' => $detail->physical_condition
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Finalize the opname.
     */
    public function finalize($id)
    {
        try {
            DB::beginTransaction();
            
            $opname = ToolStockOpname::findOrFail($id);
            
            if ($opname->status === ToolStockOpname::STATUS_FINAL) {
                return back()->with('error', 'Opname already finalized.');
            }

            $opname->status = ToolStockOpname::STATUS_FINAL;
            $opname->end_date = now();
            $opname->save();

            AuditLog::log(
                'opname_finalized',
                'ToolStockOpname',
                $opname->id,
                ['status' => ToolStockOpname::STATUS_IN_PROGRESS],
                ['status' => ToolStockOpname::STATUS_FINAL]
            );

            DB::commit();

            return redirect()->route('tool-lending.stock-opname.show', $opname->id)
                ->with('success', 'Stock Opname finalized successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to finalize opname: ' . $e->getMessage());
        }
    }

    /**
     * Export opname report to PDF.
     */
    public function exportPdf($id)
    {
        $opname = ToolStockOpname::with(['details.tool.status', 'creator'])->findOrFail($id);
        
        $stats = [
            'total' => $opname->details->count(),
            'missing' => $opname->details->where('physical_exist', ToolStockOpnameDetail::EXIST_NO)->count(),
            'excess' => $opname->details->where('notes', 'Manual Finding')->count(),
            'damaged' => $opname->details->where('physical_condition', ToolStockOpnameDetail::CONDITION_DAMAGE)->count(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('tool-lending.stock-opname.pdf', compact('opname', 'stats'));
        
        return $pdf->download('Stock-Opname-' . $opname->opname_no . '.pdf');
    }

    /**
     * Add a manual tool finding (Excess).
     */
    public function addFinding(Request $request, $id)
    {
        $request->validate([
            'tool_id' => 'required|exists:tools,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $opname = ToolStockOpname::findOrFail($id);
            
            if ($opname->details()->where('tool_id', $request->tool_id)->exists()) {
                return back()->with('error', 'Tool already in opname list.');
            }

            $tool = Tools::with('status')->findOrFail($request->tool_id);
            
            ToolStockOpnameDetail::create([
                'opname_id' => $opname->id,
                'tool_id' => $tool->id,
                'physical_exist' => ToolStockOpnameDetail::EXIST_YES,
                'physical_condition' => ToolStockOpnameDetail::CONDITION_GOOD,
                'physical_location' => $tool->shelf_location_display,
                'system_status' => 'UNTRACKED', 
                'mismatch' => true,
                'notes' => 'Manual Finding' . ($request->notes ? ': ' . $request->notes : ''),
            ]);

            return back()->with('success', 'Tool added as manual finding.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add finding: ' . $e->getMessage());
        }
    }
}
