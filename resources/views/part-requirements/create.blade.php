<?php $page = 'part-requirements.create'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content pb-0">
            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Create Part Requirement</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('part-requirements.index')}}">Part Requirements</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <!-- End Page Header -->

            <div class="card border-0 rounded-0">
                <div class="card-body">
                    <form action="{{ route('part-requirements.store') }}" method="POST" id="create-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment <span class="text-danger">*</span></label>
                                <select name="equipment_id" class="select2 form-select" required>
                                    <option value="">Select Equipment</option>
                                    @foreach($equipments as $equipment)
                                        <option value="{{ $equipment->id }}" data-service-period="{{ $equipment->periodicServiceType ? $equipment->periodicServiceType->name : '-' }}">{{ $equipment->code }} - {{ $equipment->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service Period</label>
                                <input type="text" id="service_period_display" class="form-control" readonly placeholder="Select Equipment first">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <h5 class="mb-3">Parts Details</h5>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered" id="parts-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Part Name <span class="text-danger">*</span></th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="parts[0][part_id]" class="select2 form-select" required>
                                                <option value="">Select Part</option>
                                                @foreach($parts as $part)
                                                    <option value="{{ $part->id }}">{{ $part->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row"><i class="ti ti-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-info mb-4" id="add-row"><i class="ti ti-plus me-1"></i> Add Part</button>

                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('part-requirements.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit">Create Requirement</button>
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
    // Initialize Select2 for existing elements
    $('.select2').select2();
    
    // Update service period on equipment change
    $('select[name="equipment_id"]').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const servicePeriod = selectedOption.data('service-period') || 'Select Equipment first';
        $('#service_period_display').val(servicePeriod);
    });

    let rowIdx = 1;

    // Add Row
    $('#add-row').on('click', function() {
        // Validate if the last row is empty
        let lastRowSelect = $('#parts-table tbody tr:last').find('select');
        if (lastRowSelect.length > 0 && lastRowSelect.val() === '') {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Row',
                text: 'Please select a part for the current row before adding a new one.'
            });
            return;
        }

        let newRow = `
            <tr>
                <td>
                    <select name="parts[${rowIdx}][part_id]" class="select2 form-select" required>
                        <option value="">Select Part</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}">{{ $part->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-row"><i class="ti ti-trash"></i></button>
                </td>
            </tr>
        `;
        let $newRow = $(newRow);
        $('#parts-table tbody').append($newRow);
        
        // Initialize Select2 for the new select element
        $newRow.find('.select2').select2();
        
        rowIdx++;
    });

    // Check for duplicates
    $(document).on('change', 'select[name^="parts"][name$="[part_id]"]', function() {
        let currentSelect = $(this);
        let currentValue = currentSelect.val();
        
        if (!currentValue) return;

        let duplicateFound = false;
        $('select[name^="parts"][name$="[part_id]"]').not(currentSelect).each(function() {
            if ($(this).val() == currentValue) {
                duplicateFound = true;
                return false;
            }
        });

        if (duplicateFound) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Part',
                text: 'This part is already selected. Please choose a different part.'
            });
            currentSelect.val('').trigger('change');
        }
    });

    // Remove Row
    $(document).on('click', '.remove-row', function() {
        if($('#parts-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Cannot delete',
                text: 'At least one part is required.'
            });
        }
    });

    // Submit Form
    $('#create-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $('#btn-submit');
        const originalBtnHtml = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('part-requirements.index') }}";
                    });
                }
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessages
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON.message || 'An error occurred. Please try again.'
                    });
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });
});
</script>
@endpush
