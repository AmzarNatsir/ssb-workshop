<?php

namespace App\Http\Controllers;

use App\Models\LoanTransaction;
use App\Models\SystemSetting;
use App\Models\ToolCard;
use App\Models\Tools;
use App\Services\LoanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanTransactionController extends Controller
{
    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    /**
     * Display active loans dashboard
     */
    public function index(Request $request)
    {
        $query = LoanTransaction::with(['toolCard.employee', 'tool', 'admin'])
                                ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->whereHas('toolCard', function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('loan_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('loan_date', '<=', $request->date_to);
        }

        $loans = $query->paginate(20);
        $statistics = $this->loanService->getStatistics();

        return view('tool-lending.loans.index', compact('loans', 'statistics'));
    }

    /**
     * Show loan creation form
     */
    public function create()
    {
        return view('tool-lending.loans.create');
    }

    /**
     * Store a new loan transaction
     */
    public function store(Request $request)
    {
        // Check if system settings exist
        $loanDuration = SystemSetting::where('setting_key', 'loan_duration_hours')->first();
        if (!$loanDuration) {
            return back()
                ->withInput()
                ->with('error', 'Please configure Tool Lending Settings (Loan Duration) before creating a loan.');
        }

        $request->validate([
            'tool_card_id' => 'required|exists:tool_cards,id',
            'tool_id' => 'required|exists:tools,id',
            'requirement_type' => 'required|in:Work Order,Other',
            'requirement_reference' => 'nullable|string|max:255',
            'requirement_notes' => 'nullable|string',
        ]);

        $result = $this->loanService->createLoan(
            $request->tool_card_id,
            $request->tool_id,
            Auth::id(),
            $request->requirement_type,
            $request->requirement_reference,
            $request->requirement_notes
        );

        if ($result['success']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect_url' => route('tool-lending.loans.show', $result['data']->loan_number)
                ]);
            }

            return redirect()
                ->route('tool-lending.loans.show', $result['data']->loan_number)
                ->with('success', $result['message']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => $result['errors'] ?? []
            ], 422);
        }

        return back()
            ->withInput()
            ->with('error', $result['message'])
            ->withErrors($result['errors'] ?? []);
    }

    /**
     * Display loan details
     */
    public function show($loanNumber)
    {
        $loan = LoanTransaction::where('loan_number', $loanNumber)
                               ->with(['toolCard.employee', 'tool', 'admin', 'reminders', 'incidentReport'])
                               ->firstOrFail();

        // Fetch Audit Logs for Loan
        $loanLogs = \App\Models\AuditLog::where('entity_type', 'LoanTransaction')
                                        ->where('entity_id', $loan->id)
                                        ->get();

        // Fetch Audit Logs for Incident (if exists)
        $incidentLogs = collect();
        if ($loan->incidentReport) {
            $incidentLogs = \App\Models\AuditLog::where('entity_type', 'ToolIncidentReport')
                                                ->where('entity_id', $loan->incidentReport->id)
                                                ->get();
        }

        // Merge and Sort
        $timeline = $loanLogs->concat($incidentLogs)->sortBy('created_at');

        return view('tool-lending.loans.show', compact('loan', 'timeline'));
    }

    /**
     * Show return form
     */
    public function returnForm($loanNumber)
    {
        $loan = LoanTransaction::where('loan_number', $loanNumber)
                               ->with(['toolCard.employee', 'tool'])
                               ->where('status', 'Active')
                               ->firstOrFail();

        return view('tool-lending.loans.return', compact('loan'));
    }

    /**
     * Process tool return
     */
    public function processReturn(Request $request)
    {
        $request->validate([
            'loan_number' => 'required|exists:loan_transactions,loan_number',
            'return_condition' => 'required|in:Good,Damaged,Lost',
            'return_notes' => 'nullable|string',
        ]);

        $result = $this->loanService->processReturn(
            $request->loan_number,
            Auth::id(),
            $request->return_condition,
            $request->return_notes
        );

        if ($result['success']) { 
        // Check if incident report was created
        if (isset($result['data']['incident_id']) && $result['data']['incident_id']) {
            return redirect()
                ->route('tool-lending.incidents.edit', $result['data']['incident_id'])
                ->with('warning', 'Tool returned as ' . $request->return_condition . '. Please complete the Incident Report (BA).');
        }

        return redirect()
            ->route('tool-lending.loans.show', $request->loan_number)
            ->with('success', $result['message']);
    }

        return back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Display loan history
     */
    public function history(Request $request)
    {
        $query = LoanTransaction::with(['toolCard.employee', 'tool', 'admin'])
                                ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->whereHas('toolCard', function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('loan_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('loan_date', '<=', $request->date_to);
        }

        $loans = $query->paginate(50);

        return view('tool-lending.loans.history', compact('loans'));
    }

    /**
     * API: Get available tools by access level
     */
    public function getAvailableTools($accessLevel)
    {
        try {
            $tools = Tools::available()
                         ->byAccessLevel($accessLevel)
                         ->with(['racks', 'tool_type'])
                         ->get()
                         ->map(function($tool) {
                             return [
                                 'id' => $tool->id,
                                 'code' => $tool->code,
                                 'name' => $tool->name,
                                 'barcode' => $tool->barcode,
                                 'serial_number' => $tool->serial_number,
                                 'shelf_location' => $tool->shelf_location_display,
                                 'image_url' => $tool->image_url,
                                 'required_access_level' => $tool->required_access_level,
                                 'quantity' => $tool->quantity,
                             ];
                         });

            return response()->json([
                'success' => true,
                'data' => $tools
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tools: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Scan tool barcode
     */
    public function scanTool($barcode)
    {
        try {
            $tool = Tools::where('barcode', $barcode)
                        ->orWhere('code', $barcode)
                        ->with(['racks', 'tool_type'])
                        ->first();

            if (!$tool) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tool not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tool->id,
                    'code' => $tool->code,
                    'name' => $tool->name,
                    'barcode' => $tool->barcode,
                    'serial_number' => $tool->serial_number,
                    'shelf_location' => $tool->shelf_location_display,
                    'image_url' => $tool->image_url,
                    'required_access_level' => $tool->required_access_level,
                    'quantity' => $tool->quantity,
                    'is_available' => $tool->isAvailable(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error scanning tool: ' . $e->getMessage()
            ], 500);
        }
    }
}
