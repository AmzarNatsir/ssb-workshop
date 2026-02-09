<?php $page = 'tool-lending-loans'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Tool Lending - Active Loans</h4>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <a href="{{ route('tool-lending.loans.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-2"></i>New Loan
                </a>
                <a href="{{ route('tool-lending.loans.history') }}" class="btn btn-outline-primary">
                    <i class="ti ti-history me-2"></i>History
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-1">Active Loans</h6>
                                <h3 class="text-white mb-0">{{ $statistics['total_active'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-white-transparent">
                                <i class="ti ti-tools fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-1">Overdue</h6>
                                <h3 class="text-white mb-0">{{ $statistics['total_overdue'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-white-transparent">
                                <i class="ti ti-alert-triangle fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white mb-1">Returned Today</h6>
                                <h3 class="text-white mb-0">{{ $statistics['returned_today'] }}</h3>
                            </div>
                            <div class="avatar avatar-lg bg-white-transparent">
                                <i class="ti ti-check fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('tool-lending.loans.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Returned" {{ request('status') == 'Returned' ? 'selected' : '' }}>Returned</option>
                            <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('tool-lending.loans.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Loans Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Loan Number</th>
                                <th>Employee</th>
                                <th>Tool</th>
                                <th>Loan Date</th>
                                <th>Expected Return</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loans as $loan)
                            <tr>
                                <td>
                                    <a href="{{ route('tool-lending.loans.show', $loan->loan_number) }}" class="text-primary fw-bold">
                                        {{ $loan->loan_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="fw-bold">{{ $loan->toolCard->employee->name }}</div>
                                            <small class="text-muted">{{ $loan->toolCard->employee->nik }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $loan->tool->name }}</div>
                                    <small class="text-muted">{{ $loan->tool->code }}</small>
                                </td>
                                <td>{{ $loan->loan_date->format('d M Y H:i') }}</td>
                                <td>
                                    {{ $loan->expected_return_date->format('d M Y H:i') }}
                                    @if($loan->isOverdue())
                                        <br><span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    @if($loan->status == 'Active')
                                        <span class="badge bg-primary">Active</span>
                                    @elseif($loan->status == 'Returned')
                                        <span class="badge bg-success">Returned</span>
                                    @elseif($loan->status == 'Overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tool-lending.loans.show', $loan->loan_number) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if($loan->status == 'Active')
                                        <a href="{{ route('tool-lending.loans.return', $loan->loan_number) }}" class="btn btn-sm btn-outline-success">
                                            <i class="ti ti-arrow-back"></i> Return
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="ti ti-inbox fs-48 text-muted"></i>
                                    <p class="text-muted mt-2">No loans found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $loans->links() }}
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@endsection
