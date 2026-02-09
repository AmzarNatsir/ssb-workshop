@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Incident Report Detail</h4>
                <p class="text-muted mb-0">No: {{ $report->incident_number }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tool-lending.incidents.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to List
                </a>
                
                @if($report->status == 'DRAFT' || $report->status == 'REJECTED')
                    <a href="{{ route('tool-lending.incidents.edit', $report->id) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-2"></i>Edit Report
                    </a>
                @endif
            </div>
        </div>

        {{-- Alerts replaced by SweetAlert Toasts --}}

        <div class="row">
            <!-- Status Timeline / Actions -->
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Current Status: 
                                    @if($report->status == 'DRAFT') <span class="badge bg-secondary">Draft</span>
                                    @elseif($report->status == 'SUBMITTED') <span class="badge bg-info">Submitted</span>
                                    @elseif($report->status == 'APPROVED_MTN') <span class="badge bg-primary">Approved (MTN)</span>
                                    @elseif($report->status == 'APPROVED_HR') <span class="badge bg-success">Approved (HR)</span>
                                    @elseif($report->status == 'REJECTED') <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </h5>
                                <p class="text-muted mb-0">
                                    Last Update: {{ $report->updated_at->format('d M Y H:i') }}
                                </p>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                @if($report->status == 'DRAFT' || $report->status == 'REJECTED')
                                    <form action="{{ route('tool-lending.incidents.submit', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success confirm-action" 
                                                data-text="Submit this report for approval?" 
                                                data-title="Submit Report"
                                                data-icon="question">
                                            <i class="ti ti-send me-2"></i>Submit
                                        </button>
                                    </form>
                                @endif

                                @if($report->status == 'SUBMITTED')
                                    <!-- Maintenance Approval Simulation -->
                                    <form action="{{ route('tool-lending.incidents.approve-mtn', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success confirm-action" 
                                                data-text="Approve as Maintenance Head?" 
                                                data-title="Approve Report"
                                                data-icon="warning">
                                            <i class="ti ti-check me-2"></i>Approve (MTN Head)
                                        </button>
                                    </form>
                                    <form action="{{ route('tool-lending.incidents.reject', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger confirm-action" 
                                                data-text="Reject this report?" 
                                                data-title="Reject Report"
                                                data-icon="error"
                                                data-confirm-button-text="Yes, Reject it!"
                                                data-confirm-button-color="#d33">
                                            <i class="ti ti-x me-2"></i>Reject
                                        </button>
                                    </form>
                                @endif

                                @if($report->status == 'APPROVED_MTN')
                                    <!-- HR Approval Simulation -->
                                    <form action="{{ route('tool-lending.incidents.approve-hr', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success confirm-action" 
                                                data-text="Approve as HR Manager? This will generate installment plan." 
                                                data-title="Final Approval"
                                                data-icon="warning">
                                            <i class="ti ti-check-double me-2"></i>Approve (HR Manager)
                                        </button>
                                    </form>
                                    <form action="{{ route('tool-lending.incidents.reject', $report->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger confirm-action" 
                                                data-text="Reject this report?" 
                                                data-title="Reject Report"
                                                data-icon="error"
                                                data-confirm-button-text="Yes, Reject it!"
                                                data-confirm-button-color="#d33">
                                            <i class="ti ti-x me-2"></i>Reject
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Incident Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%" class="text-muted">Type</td>
                                <td class="fw-bold">{{ $report->incident_type == 'MISSING' ? 'LOST (BAH)' : 'DAMAGED (BAR)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Date</td>
                                <td class="fw-bold">{{ $report->incident_date ? $report->incident_date->format('d M Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tool</td>
                                <td>{{ $report->tool->name }} ({{ $report->tool->code }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Employee</td>
                                <td>{{ $report->employee->name }} ({{ $report->employee->nik }})</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Location</td>
                                <td>{{ $report->last_location ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Last Condition</td>
                                <td>{{ $report->last_condition ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Document</td>
                                <td>
                                    @if($report->document_path)
                                        <a href="{{ Storage::url($report->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-file me-1"></i>View Document
                                        </a>
                                    @else
                                        <span class="text-muted">Not uploaded</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <div class="mt-3">
                            <label class="text-muted d-block small mb-1">Chronology:</label>
                            <div class="p-3 bg-light rounded">{{ $report->chronology ?? '-' }}</div>
                        </div>
                        <div class="mt-3">
                            <label class="text-muted d-block small mb-1">Description:</label>
                            <div class="p-3 bg-light rounded">{{ $report->description ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Financial Info -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Replacement & Installments</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-6">
                                <p class="text-muted small mb-1">Total Cost</p>
                                <h4 class="text-danger">Rp {{ number_format($report->replacement_cost, 0, ',', '.') }}</h4>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small mb-1">Installment Plan</p>
                                <h4>{{ $report->installment_months ?? 0 }} Months</h4>
                            </div>
                        </div>

                        @if($report->status == 'APPROVED_HR' && $report->installments->count() > 0)
                            <h6 class="mb-3">Installment Schedule</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Period</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report->installments as $inst)
                                            <tr>
                                                <td>{{ date('F Y', mktime(0, 0, 0, $inst->period_month, 1, $inst->period_year)) }}</td>
                                                <td>Rp {{ number_format($inst->amount, 0, ',', '.') }}</td>
                                                <td>
                                                    @if($inst->status == 'PAID')
                                                        <span class="badge bg-success">PAID</span>
                                                    @else
                                                        <span class="badge bg-warning">PENDING</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($report->installment_months > 0)
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-1"></i>
                                Estimated Monthly: <strong>Rp {{ number_format($report->monthly_installment_amount, 0, ',', '.') }}</strong>
                                <br>
                                Period: {{ $report->installment_start_month ? $report->installment_start_month->format('M Y') : 'TBD' }} 
                                - {{ $report->installment_end_month ? $report->installment_end_month->format('M Y') : 'TBD' }}
                            </div>
                        @else
                            <div class="text-center text-muted py-3">
                                No installment plan active.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // SweetAlert Toast Configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Success Message
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: {!! json_encode(session('success')) !!}
            });
        @endif

        // Error Message
        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: {!! json_encode(session('error')) !!}
            });
        @endif

        // Generic Confirmation Handler
        $(document).on('click', '.confirm-action', function(e) {
            e.preventDefault();
            let btn = $(this);
            let form = btn.closest('form');
            let title = btn.data('title') || 'Are you sure?';
            let text = btn.data('text') || 'Confirm this action?';
            let icon = btn.data('icon') || 'warning';
            let confirmBtnText = btn.data('confirm-button-text') || 'Yes, Proceed';
            let confirmBtnColor = btn.data('confirm-button-color') || '#3085d6';

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmBtnColor,
                cancelButtonColor: '#d33',
                confirmButtonText: confirmBtnText
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
