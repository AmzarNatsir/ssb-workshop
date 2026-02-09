<?php $page = 'tool-lending-settings'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Tool Lending Settings</h4>
            </div>
            <div>
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

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">System Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tool-lending.settings.update') }}">
                            @csrf
                            @method('PUT')
                            
                            @foreach($settings as $index => $setting)
                            <div class="mb-4">
                                <label class="form-label fw-bold">{{ ucwords(str_replace('_', ' ', $setting->setting_key)) }}</label>
                                <input type="hidden" name="settings[{{ $index }}][key]" value="{{ $setting->setting_key }}">
                                <input type="number" name="settings[{{ $index }}][value]" class="form-control" 
                                       value="{{ $setting->setting_value }}" required>
                                <small class="text-muted">{{ $setting->description }}</small>
                            </div>
                            @endforeach

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-2"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Settings Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">Loan Duration Hours</h6>
                            <p class="text-muted small">Default duration for tool loans in hours. Tools must be returned within this timeframe.</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Reminder Before Hours</h6>
                            <p class="text-muted small">Hours before the due date to send a return reminder notification to the employee.</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Overdue Check Interval</h6>
                            <p class="text-muted small">Interval in minutes to check for overdue loans and send notifications.</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="mb-0">Current Settings</h5>
                    </div>
                    <div class="card-body">
                        @foreach($settings as $setting)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ ucwords(str_replace('_', ' ', $setting->setting_key)) }}</span>
                            <span class="fw-bold">{{ $setting->setting_value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@endsection
