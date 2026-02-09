@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Tool Incident Reports (BA Tools)</h4>
                <p class="text-muted mb-0">Manage Lost (BAH) and Damaged (BAR) Tool Reports</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Incident No</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Tool</th>
                                <th>Employee</th>
                                <th>Cost</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                            <tr>
                                <td>
                                    <a href="{{ route('tool-lending.incidents.show', $report->id) }}" class="fw-bold text-primary">
                                        {{ $report->incident_number }}
                                    </a>
                                </td>
                                <td>
                                    @if($report->incident_type == 'MISSING')
                                        <span class="badge bg-danger">LOST</span>
                                    @else
                                        <span class="badge bg-warning">DAMAGED</span>
                                    @endif
                                </td>
                                <td>{{ $report->incident_date ? $report->incident_date->format('d M Y') : '-' }}</td>
                                <td>{{ $report->tool->name }}</td>
                                <td>{{ $report->employee->name }}</td>
                                <td>Rp {{ number_format($report->replacement_cost, 0, ',', '.') }}</td>
                                <td>
                                    @if($report->status == 'DRAFT') <span class="badge bg-secondary">Draft</span>
                                    @elseif($report->status == 'SUBMITTED') <span class="badge bg-info">Submitted</span>
                                    @elseif($report->status == 'APPROVED_MTN') <span class="badge bg-primary">Approved (MTN)</span>
                                    @elseif($report->status == 'APPROVED_HR') <span class="badge bg-success">Approved (HR)</span>
                                    @elseif($report->status == 'REJECTED') <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tool-lending.incidents.show', $report->id) }}" class="btn btn-sm btn-icon btn-light">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    @if($report->status == 'DRAFT' || $report->status == 'REJECTED')
                                        <a href="{{ route('tool-lending.incidents.edit', $report->id) }}" class="btn btn-sm btn-icon btn-light">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No incident reports found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@endsection
