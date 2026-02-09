<?php $page = 'tool-lending-return'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Process Tool Return</h4>
            </div>
            <div>
                <a href="{{ route('tool-lending.loans.show', $loan->loan_number) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to Details
                </a>
            </div>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <!-- Loan Summary -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Loan Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Loan Number:</strong></p>
                                <p class="text-muted">{{ $loan->loan_number }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Loan Date:</strong></p>
                                <p class="text-muted">{{ $loan->loan_date->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Employee:</strong></p>
                                <p class="text-muted">{{ $loan->toolCard->employee->name }} ({{ $loan->toolCard->employee->nik }})</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Tool:</strong></p>
                                <p class="text-muted">{{ $loan->tool->name }} ({{ $loan->tool->code }})</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Expected Return:</strong></p>
                                <p class="text-muted">
                                    {{ $loan->expected_return_date->format('d M Y H:i') }}
                                    @if($loan->isOverdue())
                                        <span class="badge bg-danger ms-2">Overdue</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Duration:</strong></p>
                                @php
                                    $timeInfo = $loan->getTimeRemaining();
                                @endphp
                                <p class="text-muted">
                                    {{ $timeInfo['duration'] }}
                                    @if($timeInfo['overdue'])
                                        <span class="text-danger">(Overdue)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return Form -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="ti ti-arrow-back me-2"></i>Return Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tool-lending.loans.process-return') }}">
                            @csrf
                            <input type="hidden" name="loan_number" value="{{ $loan->loan_number }}">
                            
                            <div class="mb-4">
                                <label class="form-label">Return Condition <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-check-lg">
                                            <input class="form-check-input" type="radio" name="return_condition" 
                                                   id="condition-good" value="Good" required>
                                            <label class="form-check-label" for="condition-good">
                                                <i class="ti ti-check-circle text-success me-2"></i>
                                                <strong>Good</strong>
                                                <p class="text-muted mb-0 small">Tool is in good condition</p>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-lg">
                                            <input class="form-check-input" type="radio" name="return_condition" 
                                                   id="condition-damaged" value="Damaged" required>
                                            <label class="form-check-label" for="condition-damaged">
                                                <i class="ti ti-alert-triangle text-warning me-2"></i>
                                                <strong>Damaged</strong>
                                                <p class="text-muted mb-0 small">Tool has some damage</p>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-lg">
                                            <input class="form-check-input" type="radio" name="return_condition" 
                                                   id="condition-lost" value="Lost" required>
                                            <label class="form-check-label" for="condition-lost">
                                                <i class="ti ti-x-circle text-danger me-2"></i>
                                                <strong>Lost</strong>
                                                <p class="text-muted mb-0 small">Tool is missing/lost</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Return Notes</label>
                                <textarea name="return_notes" class="form-control" rows="4" 
                                          placeholder="Add any notes about the tool condition, issues, or comments..."></textarea>
                                <small class="text-muted">Optional: Provide details about the tool condition or any issues</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="ti ti-check me-2"></i>Process Return
                                </button>
                                <a href="{{ route('tool-lending.loans.show', $loan->loan_number) }}" class="btn btn-outline-secondary btn-lg">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tool Image -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tool Information</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ $loan->tool->image_url ?? '/assets/img/default-tool.png' }}" 
                             class="img-fluid mb-3" alt="Tool Image">
                        <h5>{{ $loan->tool->name }}</h5>
                        <p class="text-muted">{{ $loan->tool->code }}</p>
                        <p class="mb-0"><strong>Serial:</strong> {{ $loan->tool->serial_number ?? 'N/A' }}</p>
                        <p class="mb-0"><strong>Location:</strong> {{ $loan->tool->shelf_location_display }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

<style>
.form-check-lg {
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s;
}
.form-check-lg:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}
.form-check-lg input:checked + label {
    font-weight: bold;
}
.form-check-lg input:checked ~ * {
    border-color: #007bff;
}
</style>

@endsection
