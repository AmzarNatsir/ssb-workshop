<?php

namespace App\Exports;

use App\Models\LoanTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LoanTransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = LoanTransaction::with(['tool', 'employee', 'admin']);

        if ($this->request->unreturned) {
            $query->where('status', 'Active');
        }

        if ($this->request->employee_id) {
            $query->whereHas('toolCard', function($q) {
                $q->where('employee_id', $this->request->employee_id);
            });
        }

        if ($this->request->status) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->search) {
            $query->where(function($q) {
                $q->where('loan_number', 'like', '%' . $this->request->search . '%')
                  ->orWhereHas('tool', function($t) {
                      $t->where('name', 'like', '%' . $this->request->search . '%')
                        ->orWhere('code', 'like', '%' . $this->request->search . '%');
                  });
            });
        }

        return $query->latest('loan_date');
    }

    public function headings(): array
    {
        return [
            'Loan Number',
            'Tool Code',
            'Tool Name',
            'Brand',
            'Borrower NIK',
            'Borrower Name',
            'Loan Date',
            'Expected Return',
            'Actual Return',
            'Status',
            'Condition'
        ];
    }

    public function map($tx): array
    {
        return [
            $tx->loan_number,
            $tx->tool->code,
            $tx->tool->name,
            $tx->tool->brand,
            $tx->employee->nik,
            $tx->employee->name,
            $tx->loan_date->format('Y-m-d H:i'),
            $tx->expected_return_date->format('Y-m-d H:i'),
            $tx->actual_return_date ? $tx->actual_return_date->format('Y-m-d H:i') : '-',
            $tx->status,
            $tx->return_condition ?? '-'
        ];
    }
}
