<?php $page = 'tool-cards'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Create Tool Card</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('tool-cards.index') }}">Tool Cards</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="create-card-form">
                        @csrf
                        <div class="row">
                            <!-- Employee Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->nik }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Access Level -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Access Level <span class="text-danger">*</span></label>
                                <select class="form-select" name="access_level" id="access_level" required>
                                    <option value="">Select Level</option>
                                    <option value="1">Level 1 - Basic (Hand Tools Only)</option>
                                    <option value="2">Level 2 - Standard (Power Tools)</option>
                                    <option value="3">Level 3 - Full (Heavy/Special Tools)</option>
                                </select>
                                <div class="form-text mt-2" id="level-desc"></div>
                            </div>
                            
                            <!-- Code Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Code Type <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="code_type" id="code_qr" value="QR" checked>
                                    <label class="btn btn-outline-primary" for="code_qr"><i class="ti ti-qrcode me-1"></i>QR Code</label>
                                    
                                    <input type="radio" class="btn-check" name="code_type" id="code_barcode" value="BARCODE">
                                    <label class="btn btn-outline-primary" for="code_barcode"><i class="ti ti-barcode me-1"></i>Barcode</label>
                                </div>
                            </div>

                            <!-- Tool Categories (Conditional/Multi-select) -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Assign Tool Categories</label>
                                <div class="row" id="tool-categories-container">
                                    @foreach($toolTypes as $type)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input tool-check" type="checkbox" name="tool_categories[]" value="{{ $type->id }}" id="type_{{ $type->id }}">
                                                <label class="form-check-label" for="type_{{ $type->id }}">
                                                    {{ $type->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('tool-cards.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Draft</button>
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
            $('.select2').select2();

            // Dynamic Level Description
            $('#access_level').change(function() {
                const val = $(this).val();
                let desc = '';
                if(val == '1') desc = '<span class="text-warning"><i class="ti ti-info-circle"></i> Can only borrow basic hand tools (Wrenches, Screwdrivers).</span>';
                if(val == '2') desc = '<span class="text-primary"><i class="ti ti-info-circle"></i> Can borrow standard power tools (Drills, Grinders) + Hand tools.</span>';
                if(val == '3') desc = '<span class="text-danger"><i class="ti ti-alert-triangle"></i> Can borrow ALL tools including Heavy Equipment tools.</span>';
                $('#level-desc').html(desc);
            });

            $('#create-card-form').submit(function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("tool-cards.store") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            setTimeout(() => {
                                window.location.href = response.redirect_url;
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Error occurred', 'error');
                    }
                });
            });

             function showNotification(message, type = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                Toast.fire({ icon: type, title: message });
            }
        });
    </script>
@endpush
