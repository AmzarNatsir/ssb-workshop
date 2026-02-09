@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Transaction History</h4>
                        <h6>Detailed Report & Analysis</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item" href="{{ route('tool-lending.reports.export', array_merge(request()->all(), ['format' => 'pdf'])) }}"><i class="ti ti-file-type-pdf me-2"></i>PDF</a></li>
                            <li><a class="dropdown-item" href="{{ route('tool-lending.reports.export', array_merge(request()->all(), ['format' => 'excel'])) }}"><i class="ti ti-file-type-xls me-2"></i>Excel</a></li>
                            <li><a class="dropdown-item" href="{{ route('tool-lending.reports.export', array_merge(request()->all(), ['format' => 'csv'])) }}"><i class="ti ti-file-type-csv me-2"></i>CSV</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('tool-lending.reports.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('tool-lending.reports.history') }}" method="GET" id="filter-form">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search Tool/LN</label>
                                <input type="text" name="search" class="form-control" placeholder="Code, Name, or LN..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" class="form-select select">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->nik }} - {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select select">
                                    <option value="">All Status</option>
                                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Borrowed</option>
                                    <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Filter Type</label>
                                <select name="filter_type" class="form-select select">
                                    <option value="">Regular</option>
                                    <option value="unreturned" {{ request('filter_type') == 'unreturned' ? 'selected' : '' }}>Unreturned Only</option>
                                    <option value="frequent" {{ request('filter_type') == 'frequent' ? 'selected' : '' }}>Rankings</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-search me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Loan Number</th>
                                    <th>Tool</th>
                                    <th>User</th>
                                    <th>Out Date</th>
                                    <th>Exp. Return</th>
                                    <th>Act. Return</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                    @php
                                        $isOverdue = $tx->status === 'Active' && $tx->expected_return_date < now();
                                        $duration = $tx->actual_return_date 
                                            ? $tx->loan_date->diffInHours($tx->actual_return_date) . ' hrs'
                                            : $tx->loan_date->diffInHours(now()) . ' hrs';
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                        <td><strong>{{ $tx->loan_number }}</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="ms-2">
                                                    <span class="d-block fw-bold">{{ $tx->tool->name }}</span>
                                                    <small class="text-muted">{{ $tx->tool->code }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-block">{{ $tx->employee->name }}</span>
                                            <small class="text-muted">{{ $tx->employee->nik }}</small>
                                        </td>
                                        <td>{{ $tx->loan_date->format('d M Y H:i') }}</td>
                                        <td>{{ $tx->expected_return_date->format('d M Y H:i') }}</td>
                                        <td>{{ $tx->actual_return_date ? $tx->actual_return_date->format('d M Y H:i') : '-' }}</td>
                                        <td>{{ $duration }}</td>
                                        <td>
                                            @if($tx->status === 'Active')
                                                <span class="badge bg-warning">{{ $isOverdue ? 'OVERDUE' : 'BORROWED' }}</span>
                                            @else
                                                <span class="badge bg-success">RETURNED</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tx->return_condition)
                                                <span class="badge {{ $tx->return_condition === 'Good' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ strtoupper($tx->return_condition) }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No transactions found matching filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            if ($('.select').length > 0) {
                $('.select').select2({
                    width: '100%'
                });
            }
        });
    </script>
@endpush
