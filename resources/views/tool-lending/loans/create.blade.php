<?php $page = 'tool-lending-create'; ?>
@extends('layout.mainlayout')
@section('content')

<div class="page-wrapper">
    <div class="content">
        
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-0">Create New Loan</h4>
            </div>
            <div>
                <a href="{{ route('tool-lending.loans.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-2"></i>Back to Loans
                </a>
            </div>
        </div>

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form id="loan-form" method="POST" action="{{ route('tool-lending.loans.store') }}">
            @csrf
            
            <!-- Step 1: Scan Tool Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ti ti-id me-2"></i>Step 1: Scan Tool Card</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tool Card Barcode / Employee NIK</label>
                            <input type="text" id="tool-card-barcode" class="form-control form-control-lg" 
                                   placeholder="Scan or enter tool card barcode" autofocus>
                            <input type="hidden" name="tool_card_id" id="tool_card_id" required>
                            <small class="text-muted">Focus this field and scan the tool card barcode</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Info Panel (Hidden until scanned) -->
            <div id="employee-panel" class="card mb-4" style="display:none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ti ti-user-check me-2"></i>Employee Information</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img id="employee-photo" src="" class="img-fluid rounded" alt="Employee Photo" style="max-width: 120px;">
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Name:</strong> <span id="employee-name"></span></p>
                                    <p class="mb-2"><strong>NIK:</strong> <span id="employee-nik"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Position:</strong> <span id="employee-position"></span></p>
                                    <p class="mb-2"><strong>Access Level:</strong> <span id="employee-access-level" class="badge bg-info"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Scan Tool -->
            <div id="tool-scan-section" class="card mb-4" style="display:none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ti ti-tool me-2"></i>Step 2: Scan Tool</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tool Barcode</label>
                            <div class="input-group">
                                <input type="text" id="tool-barcode" class="form-control form-control-lg" 
                                    placeholder="Scan or enter tool barcode">
                                <button class="btn btn-outline-primary" type="button" id="btn-browse-tools">
                                    <i class="ti ti-search me-1"></i>Browse
                                </button>
                            </div>
                            <input type="hidden" name="tool_id" id="tool_id" required>
                            <small class="text-muted">Focus this field and scan the tool card barcode</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tool Info Panel (Hidden until scanned) -->
            <div id="tool-panel" class="card mb-4" style="display:none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="ti ti-tool-check me-2"></i>Tool Information</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img id="tool-image" src="" class="img-fluid" alt="Tool Image" style="max-width: 120px;">
                        </div>
                        <div class="col-md-10">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Tool Name:</strong> <span id="tool-name"></span></p>
                                    <p class="mb-2"><strong>Code:</strong> <span id="tool-code"></span></p>
                                    <p class="mb-2"><strong>Serial Number:</strong> <span id="tool-serial"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Location:</strong> <span id="tool-location"></span></p>
                                    <p class="mb-2"><strong>Required Level:</strong> <span id="tool-required-level" class="badge bg-warning"></span></p>
                                    <p class="mb-2"><strong>Availability:</strong> <span id="tool-availability" class="badge"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Requirement Details -->
            <div id="requirement-section" class="card mb-4" style="display:none;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="ti ti-file-text me-2"></i>Step 3: Requirement Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Requirement Type <span class="text-danger">*</span></label>
                            <select name="requirement_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Work Order">Work Order</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input type="text" name="requirement_reference" class="form-control" 
                                   placeholder="e.g., WO-2024-001">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="requirement_notes" class="form-control" rows="3" 
                                      placeholder="Additional notes or comments"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="ti ti-check me-2"></i>Create Loan
                            </button>
                            <a href="{{ route('tool-lending.loans.index') }}" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>
                    </div>

    <!-- Tools Browser Modal -->
    <div class="modal fade" id="toolsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Available Tools</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Search within modal -->
                    <div class="mb-3">
                        <input type="text" id="modal-tool-search" class="form-control" placeholder="Search by name or code...">
                    </div>
                    
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover" id="tools-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Image</th>
                                    <th>Tool Info</th>
                                    <th>Location</th>
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tools-list">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Loading State -->
                    <div id="tools-loading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <!-- No Data State -->
                    <div id="tools-empty" class="text-center py-4" style="display: none;">
                        <p class="text-muted">No available tools found for your access level.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
                </div>
            </div>
        </form>

    </div>

    @component('components.footer')
    @endcomponent
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentAccessLevel = 0;

    // Tool Card Barcode Scanning
    $('#tool-card-barcode').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            
            if (!barcode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Scanning Tool',
                    text: 'Please enter a barcode',
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            // Show loading
            $(this).prop('disabled', true);
            
            $.get(`/tool-lending/scan/tool-card/${barcode}`)
                .done(function(response) {
                    if (response.success) {
                        // Store data
                        $('#tool_card_id').val(response.data.tool_card_id);
                        currentAccessLevel = response.data.access_level;
                        
                        // Populate employee panel
                        $('#employee-name').text(response.data.employee.name);
                        $('#employee-nik').text(response.data.employee.nik);
                        $('#employee-position').text(response.data.employee.position);
                        $('#employee-access-level').text('Level ' + response.data.access_level);
                        
                        const photoUrl = response.data.employee.photo_url || '/assets/img/default-avatar.png';
                        $('#employee-photo').attr('src', photoUrl);
                        
                        // Show panels
                        $('#employee-panel').slideDown();
                        $('#tool-scan-section').slideDown();
                        
                        // Focus tool barcode input
                        setTimeout(() => $('#tool-barcode').focus(), 300);
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error scanning tool card';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Scanning Tool',
                        text: message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                })
                .always(function() {
                    $('#tool-card-barcode').prop('disabled', false);
                });
        }
    });

    // Tool Barcode Scanning
    $('#tool-barcode').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            
            if (!barcode) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error Scanning Tool',
                    text: 'Please enter a barcode',
                    timer: 3000,
                    showConfirmButton: false
                });
                return;
            }

            // Show loading
            $(this).prop('disabled', true);
            
            $.get(`/tool-lending/scan/tool/${barcode}`)
                .done(function(response) {
                    if (response.success) {
                        const tool = response.data;
                        
                        // Store data
                        $('#tool_id').val(tool.id);
                        
                        // Populate tool panel
                        $('#tool-name').text(tool.name);
                        $('#tool-code').text(tool.code);
                        $('#tool-serial').text(tool.serial_number || 'N/A');
                        $('#tool-location').text(tool.shelf_location);
                        $('#tool-required-level').text('Level ' + tool.required_access_level);
                        
                        const imageUrl = tool.image_url || '/assets/img/default-tool.png';
                        $('#tool-image').attr('src', imageUrl);
                        
                        // Check availability
                        if (tool.is_available) {
                            $('#tool-availability').removeClass('bg-danger').addClass('bg-success').text('Available');
                        } else {
                            $('#tool-availability').removeClass('bg-success').addClass('bg-danger').text('Not Available');
                        }
                        
                        // Check access level
                        if (currentAccessLevel < tool.required_access_level) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Access Denied',
                                text: `This tool requires Level ${tool.required_access_level}, but employee has Level ${currentAccessLevel}`,
                                timer: 3000,
                                showConfirmButton: false
                            });
                            $('#tool-barcode').val('').focus();
                            return;
                        }
                        
                        if (!tool.is_available) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tool Unavailable',
                                text: 'This tool is currently not available for borrowing',
                                timer: 3000,
                                showConfirmButton: false
                            });
                            $('#tool-barcode').val('').focus();
                            return;
                        }
                        
                        // Show panels
                        $('#tool-panel').slideDown();
                        $('#requirement-section').slideDown();
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error scanning tool';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Scanning Tool',
                        text: message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    $('#tool-barcode').val('').focus();
                })
                .always(function() {
                    $('#tool-barcode').prop('disabled', false);
                });
        }
    });

    // --- Tools Browser Modal Logic ---
    const toolsModal = new bootstrap.Modal(document.getElementById('toolsModal'));
    let allTools = [];

    $('#btn-browse-tools').on('click', function() {
        if (!currentAccessLevel || currentAccessLevel === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Scan Tool Card First',
                text: 'Please scan the employee tool card (Step 1) before browsing tools.',
                timer: 3000,
                showConfirmButton: false
            });
            $('#tool-card-barcode').focus();
            return;
        }

        // Show Modal & Loading
        toolsModal.show();
        $('#tools-loading').show();
        $('#tools-list').empty();
        $('#tools-empty').hide();
        $('#tools-table').hide();

        // Fetch Tools
        $.get(`/tool-lending/tools/available/${currentAccessLevel}`)
            .done(function(response) {
                if (response.success) {
                    allTools = response.data;
                    renderTools(allTools);
                } else {
                    $('#tools-empty').show();
                }
            })
            .fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load tools list.',
                });
            })
            .always(function() {
                $('#tools-loading').hide();
            });
    });

    // Render Tools Function
    function renderTools(tools) {
        const tbody = $('#tools-list');
        tbody.empty();

        if (tools.length === 0) {
            $('#tools-table').hide();
            $('#tools-empty').show();
            return;
        }

        $('#tools-table').show();
        $('#tools-empty').hide();

        tools.forEach(tool => {
            const imageUrl = tool.image_url || '/assets/img/default-tool.png';
            const row = `
                <tr>
                    <td>
                        <img src="${imageUrl}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                    <td>
                        <div class="fw-bold">${tool.name}</div>
                        <div class="small text-muted">Code: ${tool.code}</div>
                        <div class="small text-muted">SN: ${tool.serial_number || '-'}</div>
                    </td>
                    <td>${tool.shelf_location || '-'}</td>
                    <td><span class="badge bg-warning">Lvl ${tool.required_access_level}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-select-tool" 
                                data-barcode="${tool.barcode || tool.code}">
                            Select
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    // Modal Search Filter
    $('#modal-tool-search').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        const filtered = allTools.filter(tool => 
            tool.name.toLowerCase().includes(value) || 
            tool.code.toLowerCase().includes(value) ||
            (tool.serial_number && tool.serial_number.toLowerCase().includes(value))
        );
        renderTools(filtered);
    });

    // Handle Selection
    $(document).on('click', '.btn-select-tool', function() {
        const barcode = $(this).data('barcode');
        toolsModal.hide();
        
        // Populate input and trigger scan simulation
        $('#tool-barcode').val(barcode);
        
        // Simulate "Enter" keypress to trigger existing scan logic
        const e = $.Event('keypress');
        e.which = 13;
        $('#tool-barcode').trigger(e);
    });

    // Form Submission with SweetAlert
    $('#loan-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        
        Swal.fire({
            title: 'Create Loan?',
            text: "Are you sure you want to create this loan transaction?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Creating loan transaction',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit via AJAX
                $.ajax({
                    url: $(form).attr('action'),
                    method: 'POST',
                    data: $(form).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = response.redirect_url;
                            });
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to create loan';
                        Swal.fire({
                            title: 'Error!',
                            text: message,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush
