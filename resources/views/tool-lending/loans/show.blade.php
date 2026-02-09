<?php $page = 'tool-lending-show'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Loan Details - {{ $loan->loan_number }}</h4>
            </div>
            <div class="gap-2 d-flex">
                @if($loan->status == 'Active')
                <a href="{{ route('tool-lending.loans.return', $loan->loan_number) }}" class="btn btn-success">
                    <i class="ti ti-arrow-back me-2"></i>Process Return
                </a>
                @endif
                <a href="{{ route('tool-lending.loans.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to Loans
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <!-- Loan Information -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Loan Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Loan Number:</strong></p>
                                <p class="text-muted">{{ $loan->loan_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Status:</strong></p>
                                <p>
                                    @if($loan->status == 'Active')
                                        <span class="badge bg-primary">Active</span>
                                    @elseif($loan->status == 'Returned')
                                        <span class="badge bg-success">Returned</span>
                                    @elseif($loan->status == 'Overdue')
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Loan Date:</strong></p>
                                <p class="text-muted">{{ $loan->loan_date->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Expected Return:</strong></p>
                                <p class="text-muted">
                                    {{ $loan->expected_return_date->format('d M Y H:i') }}
                                    @if($loan->isOverdue())
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($loan->actual_return_date)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Actual Return Date:</strong></p>
                                <p class="text-muted">{{ $loan->actual_return_date->format('d M Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Return Condition:</strong></p>
                                <p>
                                    @if($loan->return_condition == 'Good')
                                        <span class="badge bg-success">Good</span>
                                    @elseif($loan->return_condition == 'Damaged')
                                        <span class="badge bg-warning">Damaged</span>
                                    @elseif($loan->return_condition == 'Lost')
                                        <span class="badge bg-danger">Lost</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Requirement Type:</strong></p>
                                <p class="text-muted">{{ $loan->requirement_type }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Reference:</strong></p>
                                <p class="text-muted">{{ $loan->requirement_reference ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @if($loan->requirement_notes)
                        <div class="row mb-3">
                            <div class="col-12">
                                <p class="mb-2"><strong>Notes:</strong></p>
                                <p class="text-muted">{{ $loan->requirement_notes }}</p>
                            </div>
                        </div>
                        @endif
                        @if($loan->return_notes)
                        <div class="row">
                            <div class="col-12">
                                <p class="mb-2"><strong>Return Notes:</strong></p>
                                <p class="text-muted">{{ $loan->return_notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Employee Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Employee Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <img src="{{ $loan->toolCard->employee->photo_url ?? '/assets/img/default-avatar.png' }}" 
                                     class="img-fluid rounded" alt="Employee Photo" style="max-width: 120px;">
                            </div>
                            <div class="col-md-9">
                                <p class="mb-2"><strong>Name:</strong> {{ $loan->toolCard->employee->name }}</p>
                                <p class="mb-2"><strong>NIK:</strong> {{ $loan->toolCard->employee->nik }}</p>
                                <p class="mb-2"><strong>Position:</strong> {{ $loan->toolCard->employee->position }}</p>
                                <p class="mb-2"><strong>Department:</strong> {{ $loan->toolCard->employee->department }}</p>
                                <p class="mb-0"><strong>Access Level:</strong> <span class="badge bg-info">Level {{ $loan->toolCard->access_level }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tool Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Tool Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <img src="{{ $loan->tool->image_url ?? '/assets/img/default-tool.png' }}" 
                                     class="img-fluid" alt="Tool Image" style="max-width: 120px;">
                            </div>
                            <div class="col-md-9">
                                <p class="mb-2"><strong>Tool Name:</strong> {{ $loan->tool->name }}</p>
                                <p class="mb-2"><strong>Code:</strong> {{ $loan->tool->code }}</p>
                                <p class="mb-2"><strong>Serial Number:</strong> {{ $loan->tool->serial_number ?? 'N/A' }}</p>
                                <p class="mb-2"><strong>Location:</strong> {{ $loan->tool->shelf_location_display }}</p>
                                <p class="mb-0"><strong>Required Level:</strong> <span class="badge bg-warning">Level {{ $loan->tool->required_access_level }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline & Reminders -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($timeline as $log)
                                @php
                                    $title = 'Unknown Action';
                                    $icon = 'ti-activity';
                                    $color = 'bg-secondary';
                                    $details = '';

                                    switch($log->action) {
                                        case 'loan_created':
                                            $title = 'Loan Created';
                                            $icon = 'ti-plus';
                                            $color = 'bg-primary';
                                            break;
                                        case 'loan_returned':
                                            $title = 'Tool Returned';
                                            $icon = 'ti-check';
                                            $color = 'bg-success';
                                            $details = 'Condition: ' . ($log->new_values['return_condition'] ?? '-');
                                            break;
                                        case 'incident_created':
                                            $title = 'Incident Reported';
                                            $icon = 'ti-alert-triangle';
                                            $color = 'bg-warning';
                                            $details = 'Type: ' . ($log->new_values['incident_type'] ?? '-');
                                            break;
                                        case 'incident_submitted':
                                            $title = 'Report Submitted';
                                            $icon = 'ti-send';
                                            $color = 'bg-info';
                                            break;
                                        case 'incident_approved_mtn':
                                            $title = 'Approved by Maintenance';
                                            $icon = 'ti-check';
                                            $color = 'bg-primary';
                                            break;
                                        case 'incident_approved_hr':
                                            $title = 'Approved by HR';
                                            $icon = 'ti-check-double';
                                            $color = 'bg-success';
                                            break;
                                        case 'incident_rejected':
                                            $title = 'Report Rejected';
                                            $icon = 'ti-x';
                                            $color = 'bg-danger';
                                            break;
                                    }
                                @endphp

                                <div class="timeline-item">
                                    <div class="timeline-badge {{ $color }}"><i class="ti {{ $icon }}"></i></div>
                                    <div class="timeline-content">
                                        <h6>{{ $title }}</h6>
                                        <p class="text-muted mb-0">{{ $log->created_at->format('d M Y H:i') }}</p>
                                        <small class="text-muted">By {{ $log->user->name ?? 'System' }}</small>
                                        @if($details)
                                            <div class="mt-1 small text-dark">{{ $details }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Reminders can still show if needed, or fetched via logs if we logged them --}}
                            {{-- Assuming reminders are separate for now --}}
                            @foreach($loan->reminders as $reminder)
                            <div class="timeline-item">
                                <div class="timeline-badge bg-info"><i class="ti ti-bell"></i></div>
                                <div class="timeline-content">
                                    <h6>Reminder: {{ $reminder->reminder_type }}</h6>
                                    <p class="text-muted mb-0">{{ $reminder->sent_at->format('d M Y H:i') }}</p>
                                    <small class="text-muted">Status: {{ $reminder->status }}</small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-badge {
    position: absolute;
    left: -30px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
}
.timeline-content {
    padding-left: 10px;
}
.timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
}
</style>

@endsection
