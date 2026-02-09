@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Edit Incident Report (BA Tools)</h4>
                <p class="text-muted mb-0">Incident No: {{ $report->incident_number }}</p>
            </div>
            <div>
                <a href="{{ route('tool-lending.loans.show', $report->loanTransaction->loan_number) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to Loan
                </a>
            </div>
        </div>

        {{-- Alerts replaced by SweetAlert Toasts --}}

        <div class="row">
            <div class="col-md-4">
                <!-- Incident Info -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Incident Info</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold">Type</td>
                                <td>
                                    @if($report->incident_type == 'MISSING')
                                        <span class="badge bg-danger">LOST (BAH)</span>
                                    @else
                                        <span class="badge bg-warning">DAMAGED (BAR)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tool</td>
                                <td>{{ $report->tool->name }} <br> <small>{{ $report->tool->code }}</small></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Employee</td>
                                <td>{{ $report->employee->name }} <br> <small>{{ $report->employee->nik }}</small></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Loan Date</td>
                                <td>{{ $report->loanTransaction->loan_date->format('d M Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <form action="{{ route('tool-lending.incidents.update', $report->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Incident Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Incident Date <span class="text-danger">*</span></label>
                                    <input type="date" name="incident_date" class="form-control" 
                                           value="{{ old('incident_date', $report->incident_date ? $report->incident_date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Location <span class="text-danger">*</span></label>
                                    <input type="text" name="last_location" class="form-control" 
                                           value="{{ old('last_location', $report->last_location) }}" required
                                           placeholder="e.g. Workshop Area A">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Last Condition <span class="text-danger">*</span></label>
                                    <input type="text" name="last_condition" class="form-control" 
                                           value="{{ old('last_condition', $report->last_condition) }}" required
                                           placeholder="e.g. Good condition before lost / Broken handle">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chronology <span class="text-danger">*</span></label>
                                <textarea name="chronology" class="form-control" rows="3" required
                                          placeholder="Explain how the incident happened...">{{ old('chronology', $report->chronology) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description / Remarks <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="2" required
                                          placeholder="Additional notes...">{{ old('description', $report->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Financial & Replacement</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Replacement / Repair Cost (IDR) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" id="replacement_cost_display" class="form-control" 
                                               value="{{ old('replacement_cost', $report->replacement_cost ? number_format($report->replacement_cost, 0, ',', '.') : '') }}" 
                                               placeholder="0" required>
                                        <input type="hidden" name="replacement_cost" id="replacement_cost" 
                                               value="{{ old('replacement_cost', $report->replacement_cost) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Installment Duration (Months)</label>
                                    <input type="number" name="installment_months" id="installment_months" class="form-control" 
                                           value="{{ old('installment_months', $report->installment_months) }}" min="1">
                                    <small class="text-muted">Leave empty if full payment</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Start Month</label>
                                    @php
                                        $indoMonths = [
                                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                        ];
                                        $currentVal = old('installment_start_month', $report->installment_start_month ? $report->installment_start_month->format('Y-m') : now()->format('Y-m'));
                                        $parts = explode('-', $currentVal);
                                        $curYear = $parts[0] ?? date('Y');
                                        $curMonth = $parts[1] ?? date('m');
                                    @endphp
                                    <div class="row g-2">
                                        <div class="col-8">
                                            <select id="ui_month_select" class="form-select">
                                                @foreach($indoMonths as $key => $name)
                                                    <option value="{{ $key }}" {{ $curMonth == $key ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" id="ui_year_input" class="form-control" value="{{ $curYear }}">
                                        </div>
                                    </div>
                                    <input type="hidden" name="installment_start_month" id="installment_start_month" value="{{ $currentVal }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Monthly Estimation</label>
                                    <input type="text" id="monthly_estimation" class="form-control bg-light" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Document</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Signed Statement (BA) <span class="text-danger">*</span></label>
                                <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                @if($report->document_path)
                                    <div class="mt-2">
                                        <a href="{{ Storage::url($report->document_path) }}" target="_blank" class="text-primary">
                                            <i class="ti ti-file me-1"></i>View Current Document
                                        </a>
                                    </div>
                                @endif
                                <small class="text-muted">Upload the signed replacement/damage statement.</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ti ti-device-floppy me-2"></i>Save Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@endsection

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

        // Validation Errors
        @if($errors->any())
            let errorMsg = '';
            @foreach ($errors->all() as $error)
                errorMsg += {!! json_encode($error . "\n") !!};
            @endforeach
            
            Toast.fire({
                icon: 'error',
                title: 'Validation Error',
                text: errorMsg
            });
        @endif


        // Format Currency Function
        function formatCurrency(value) {
            return value.replace(/\D/g, '')
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Handle Display Input
        $('#replacement_cost_display').on('input', function() {
            let value = $(this).val().replace(/\./g, ''); // Remove dots
            let formatted = formatCurrency(value);
            
            $(this).val(formatted);
            $('#replacement_cost').val(value); // Update hidden input
            
            calculateInstallment();
        });

        // Initialize display value if hidden input has numeric value (for edit mode)
        let initialVal = $('#replacement_cost').val();
        if(initialVal) {
             // Ensure it's treated as string safely
             let numericVal = String(initialVal).replace('.00', ''); // strip decimal if any
             $('#replacement_cost_display').val(formatCurrency(numericVal));
        }

        function calculateInstallment() {
            const cost = parseFloat($('#replacement_cost').val()) || 0;
            const months = parseInt($('#installment_months').val()) || 0;
            
            if (cost > 0 && months > 0) {
                const monthly = cost / months;
                $('#monthly_estimation').val('Rp ' + monthly.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            } else {
                $('#monthly_estimation').val('-');
            }
        }

        $('#installment_months').on('input', calculateInstallment);
        
        // Sync Month/Year to Hidden Input
        function updateStartMonthHidden() {
            let m = $('#ui_month_select').val();
            let y = $('#ui_year_input').val();
            $('#installment_start_month').val(y + '-' + m);
        }

        $('#ui_month_select, #ui_year_input').on('change input', updateStartMonthHidden);

        // Initial calculation
        calculateInstallment();
    });
</script>
@endpush
