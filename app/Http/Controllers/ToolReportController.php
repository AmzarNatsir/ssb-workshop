<?php

namespace App\Http\Controllers;

use App\Models\LoanTransaction;
use App\Models\ToolStockOpnameDetail;
use App\Models\Tools;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoanTransactionsExport;

class ToolReportController extends Controller
{
    /**
     * Display the reports dashboard with summary and charts.
     */
    public function index()
    {
        // Summary Card Stats
        $stats = [
            'total_tx' => LoanTransaction::count(),
            'current_borrowed' => LoanTransaction::where('status', 'Active')->count(),
            'overdue' => LoanTransaction::where('status', 'Active')
                ->where('expected_return_date', '<', now())
                ->count(),
            'damaged_lost_month' => ToolStockOpnameDetail::whereIn('physical_condition', ['DAMAGE', 'LOST'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->count()
        ];

        return view('tool-lending.reports.index', compact('stats'));
    }

    /**
     * Display detailed transaction history with filters.
     */
    public function history(Request $request)
    {
        $query = LoanTransaction::with(['tool', 'employee', 'admin', 'toolCard']);

        // Filter: Frequently Borrowed (Ranking)
        if ($request->filter_type === 'frequent') {
            return $this->handleFrequentReport($request);
        }

        // Filter: Unreturned
        if ($request->filter_type === 'unreturned') {
            $query->where('status', 'Active');
        }

        // Filter: By User
        if ($request->employee_id) {
            $query->whereHas('toolCard', function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        // Filter: By Status (From Tool snapshot/incident)
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Search/Multi-filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('loan_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('tool', function($t) use ($request) {
                      $t->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $transactions = $query->latest('loan_date')->paginate(20);
        $employees = Employee::orderBy('name')->get();

        return view('tool-lending.reports.history', compact('transactions', 'employees'));
    }

    /**
     * Handle Frequently Borrowed Tools ranking.
     */
    private function handleFrequentReport(Request $request)
    {
        $frequentTools = LoanTransaction::select('tool_id', DB::raw('count(*) as total_loans'))
            ->groupBy('tool_id')
            ->orderByDesc('total_loans')
            ->with('tool')
            ->paginate(20);

        return view('tool-lending.reports.frequent', compact('frequentTools'));
    }

    /**
     * Export transaction history based on filters.
     */
    public function export(Request $request)
    {
        $format = $request->format ?? 'excel';
        $filename = 'Tool-Transactions-' . now()->format('YmdHis');

        if ($format === 'pdf') {
            $query = LoanTransaction::with(['tool', 'employee', 'admin', 'toolCard']);
            if ($request->filter_type === 'unreturned') { $query->where('status', 'Active'); }
            if ($request->employee_id) { $query->whereHas('toolCard', function($q) use ($request) { $q->where('employee_id', $request->employee_id); }); }
            if ($request->status) { $query->where('status', $request->status); }
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('loan_number', 'like', '%' . $request->search . '%')
                      ->orWhereHas('tool', function($t) use ($request) {
                          $t->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('code', 'like', '%' . $request->search . '%');
                      });
                });
            }
            $transactions = $query->latest('loan_date')->get();
            $pdf = Pdf::loadView('tool-lending.reports.pdf', compact('transactions'));
            return $pdf->download($filename . '.pdf');
        }

        $extension = $format === 'csv' ? 'csv' : 'xlsx';
        $excelFormat = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new LoanTransactionsExport($request), $filename . '.' . $extension, $excelFormat);
    }

    /**
     * Get chart data for the dashboard (AJAX).
     */
    public function getChartData()
    {
        // 1. Lending Trends (Last 7 days)
        $trends = LoanTransaction::select(DB::raw('DATE(loan_date) as date'), DB::raw('count(*) as count'))
            ->where('loan_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->get();

        // 2. Status Distribution
        $statusDistrib = LoanTransaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // 3. Most Popular Tools
        $popularTools = LoanTransaction::select('tool_id', DB::raw('count(*) as count'))
            ->with('tool:id,name')
            ->groupBy('tool_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return response()->json([
            'trends' => $trends,
            'status' => $statusDistrib,
            'popular' => $popularTools
        ]);
    }
}
