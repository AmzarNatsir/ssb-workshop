<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ToolCard;
use App\Models\ToolCardApproval;
use App\Models\common\ToolType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ToolCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tool-cards.index');
    }

    /**
     * DataTables AJAX
     */
    public function datatables()
    {
        $cards = ToolCard::with(['employee', 'creator'])
            ->select('tool_cards.*');

        return DataTables::of($cards)
            ->addColumn('employee_name', function ($card) {
                return $card->employee->name . ' (' . $card->employee->nik . ')';
            })
            ->addColumn('access_level_badge', function ($card) {
                $badges = [
                    '1' => '<span class="badge bg-secondary">Level 1 - Basic</span>',
                    '2' => '<span class="badge bg-primary">Level 2 - Standard</span>',
                    '3' => '<span class="badge bg-danger">Level 3 - Full</span>',
                ];
                return $badges[$card->access_level] ?? '-';
            })
            ->addColumn('status_badge', function ($card) {
                $badges = [
                    'DRAFT' => '<span class="badge bg-secondary">Draft</span>',
                    'SUBMITTED' => '<span class="badge bg-info">Submitted</span>',
                    'APPROVED_WSP' => '<span class="badge bg-primary">Approved WSP</span>',
                    'APPROVED_FINAL' => '<span class="badge bg-success">Approved Final</span>',
                    'REJECTED' => '<span class="badge bg-danger">Rejected</span>',
                ];
                return $badges[$card->status] ?? '-';
            })
            ->addColumn('action', function ($card) {
                $btn = '<div class="btn-group">';
                
                // Edit only if DRAFT or REJECTED
                if (in_array($card->status, ['DRAFT', 'REJECTED'])) {
                    $btn .= '<a href="' . route('tool-cards.edit', $card->id) . '" class="btn btn-sm btn-outline-warning" title="Edit"><i class="ti ti-edit"></i></a>';
                }

                // Show/Review
                $btn .= '<a href="' . route('tool-cards.show', $card->id) . '" class="btn btn-sm btn-outline-info" title="Review"><i class="ti ti-eye"></i></a>';

                // Print if Final Approved
                if ($card->status === 'APPROVED_FINAL') {
                    $btn .= '<a href="' . route('tool-cards.print', $card->id) . '" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print"><i class="ti ti-printer"></i></a>';
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['access_level_badge', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        // Categorize tools by type for selection
        $toolTypes = ToolType::all();
        
        return view('tool-cards.create', compact('employees', 'toolTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'access_level' => 'required|in:1,2,3',
            'code_type' => 'required|in:QR,BARCODE',
            'tool_categories' => 'nullable|array',
        ]);

        try {
            // Check if employee already has active card (optional business rule, skipping for now)

            $toolCard = ToolCard::create([
                'employee_id' => $request->employee_id,
                'access_level' => $request->access_level,
                'code_type' => $request->code_type,
                'tool_categories' => $request->tool_categories,
                'status' => 'DRAFT',
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tool card drafted successfully',
                'redirect_url' => route('tool-cards.show', $toolCard->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tool card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $toolCard = ToolCard::with(['employee', 'creator', 'approvals.approver'])->findOrFail($id);
        
        // Generate QR Code/Barcode for preview
        // Using SimpleSoftwareIO/QrCode
        // Format: UID
        $barcode = $toolCard->uid;

        return view('tool-cards.show', compact('toolCard', 'barcode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $toolCard = ToolCard::findOrFail($id);
        
        if (!in_array($toolCard->status, ['DRAFT', 'REJECTED'])) {
            return redirect()->route('tool-cards.index')->with('error', 'Cannot edit submitted or approved cards');
        }

        $employees = Employee::orderBy('name')->get();
        $toolTypes = ToolType::all();

        return view('tool-cards.edit', compact('toolCard', 'employees', 'toolTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $toolCard = ToolCard::findOrFail($id);

        if (!in_array($toolCard->status, ['DRAFT', 'REJECTED'])) {
            return response()->json(['success' => false, 'message' => 'Cannot update submitted card'], 400);
        }

        $request->validate([
            'access_level' => 'required|in:1,2,3',
            'tool_categories' => 'nullable|array',
        ]);

        try {
            $toolCard->update([
                'access_level' => $request->access_level,
                'tool_categories' => $request->tool_categories
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tool card updated successfully',
                'redirect_url' => route('tool-cards.show', $toolCard->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $toolCard = ToolCard::findOrFail($id);
        
        if ($toolCard->status !== 'DRAFT') {
            return response()->json(['success' => false, 'message' => 'Only draft cards can be deleted'], 400);
        }

        $toolCard->delete();

        return response()->json(['success' => true, 'message' => 'Tool card deleted']);
    }

    /**
     * Submit for approval
     */
    public function submit($id)
    {
        $toolCard = ToolCard::findOrFail($id);
        if ($toolCard->status !== 'DRAFT' && $toolCard->status !== 'REJECTED') {
            return response()->json(['success' => false, 'message' => 'Invalid status for submission'], 400);
        }

        $toolCard->update([
            'status' => 'SUBMITTED',
            'current_approval_level' => 1 // Ready for 1st approval (WSP Manager)
        ]);

        return response()->json(['success' => true, 'message' => 'Submitted for approval']);
    }

    /**
     * Approve card
     */
    public function approve(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string']);
        $toolCard = ToolCard::findOrFail($id);
        
        $currentLevel = $toolCard->current_approval_level;

        // Logic for approval levels
        // Level 1: WSP Manager -> Moves to Status APPROVED_WSP, Level 2
        // Level 2: KA Plan -> Moves to Status APPROVED_FINAL, Level 3 (Done)

        $newStatus = $toolCard->status;
        $newLevel = $currentLevel;

        if ($currentLevel == 1) {
            $newStatus = 'APPROVED_WSP';
            $newLevel = 2; // Next is KA Plan
        } elseif ($currentLevel == 2) {
            $newStatus = 'APPROVED_FINAL';
            $newLevel = 3; // Done
        } else {
            return response()->json(['success' => false, 'message' => 'No pending approval at this level'], 400);
        }

        DB::beginTransaction();
        try {
            // Log approval
            ToolCardApproval::create([
                'tool_card_id' => $toolCard->id,
                'approver_id' => Auth::id(),
                'level' => $currentLevel,
                'status' => 'APPROVED',
                'notes' => $request->notes
            ]);

            // Update card
            $toolCard->update([
                'status' => $newStatus,
                'current_approval_level' => $newLevel
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Approved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject card
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['notes' => 'required|string']);
        $toolCard = ToolCard::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Log rejection
            ToolCardApproval::create([
                'tool_card_id' => $toolCard->id,
                'approver_id' => Auth::id(),
                'level' => $toolCard->current_approval_level,
                'status' => 'REJECTED',
                'notes' => $request->notes
            ]);

            // Update card -> Send back to DRAFT or specifically REJECTED
            $toolCard->update([
                'status' => 'REJECTED',
                'current_approval_level' => 0 // Reset flow
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Rejected successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Print View
     */
    public function print($id)
    {
        $toolCard = ToolCard::with('employee')->findOrFail($id);
        
        if ($toolCard->status !== 'APPROVED_FINAL') {
             // In real app, might restrict. For demo, maybe allow preview?
             // Restriction:
             // abort(403, 'Card not fully approved yet');
        }

        // Pass data for print view
        return view('tool-cards.print', compact('toolCard'));
    }
}
