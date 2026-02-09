<?php $page = 'inspections'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Execute Inspection</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Execute</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Inspection Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Form</small>
                            <p class="fw-semibold mb-0">{{ $result->form->form_title }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Unit</small>
                            <p class="fw-semibold mb-0">{{ $result->unit->code }} - {{ $result->unit->name }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Inspection Date</small>
                            <p class="fw-semibold mb-0">{{ $result->inspection_date->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Result Code</small>
                            <p class="fw-semibold mb-0">{{ $result->result_code }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inspection Form -->
            <form id="inspection-form">
                @csrf
                @foreach($result->form->sections as $section)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header border-bottom bg-light">
                            <h5 class="card-title mb-0">{{ $section->section_title }}</h5>
                            @if($section->section_description)
                                <small class="text-muted">{{ $section->section_description }}</small>
                            @endif
                        </div>
                        <div class="card-body">
                            @foreach($section->items as $item)
                                <div class="mb-4 pb-3 border-bottom inspection-item" data-item-id="{{ $item->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <label class="form-label fw-semibold mb-1">
                                                {{ $item->item_name }}
                                                @if($item->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            @if($item->item_description)
                                                <p class="text-muted small mb-1">{{ $item->item_description }}</p>
                                            @endif
                                            @if($item->instruction)
                                                <p class="text-info small mb-0"><i class="ti ti-info-circle"></i> {{ $item->instruction }}</p>
                                            @endif
                                            @if($item->item_image)
                                                <div class="mt-2">
                                                    <a href="{{ asset('storage/' . $item->item_image) }}" target="_blank">
                                                        <img src="{{ asset('storage/' . $item->item_image) }}" class="img-thumbnail" style="max-height: 100px;" title="Reference Image">
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <input type="hidden" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][item_id]" value="{{ $item->id }}">

                                    @if($item->input_type == 'GOOD_REPAIR_REPLACE_NA')
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_good" value="Good" {{ $item->is_required ? 'required' : '' }}>
                                            <label class="btn btn-outline-success" for="item_{{ $item->id }}_good">Good</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_repair" value="Repair">
                                            <label class="btn btn-outline-warning" for="item_{{ $item->id }}_repair">Repair</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_replace" value="Replace">
                                            <label class="btn btn-outline-danger" for="item_{{ $item->id }}_replace">Replace</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_na" value="NA">
                                            <label class="btn btn-outline-secondary" for="item_{{ $item->id }}_na">N/A</label>
                                        </div>

                                    @elseif($item->input_type == 'GOOD_OTHERS')
                                        <div class="d-flex flex-column gap-2">
                                            <div class="btn-group" role="group">
                                                <input type="radio" class="btn-check good-others-radio" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_good" value="Good" data-item-id="{{ $item->id }}" {{ $item->is_required ? 'required' : '' }}>
                                                <label class="btn btn-outline-success" for="item_{{ $item->id }}_good">Good</label>
                                                
                                                <input type="radio" class="btn-check good-others-radio" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_others" value="Others" data-item-id="{{ $item->id }}">
                                                <label class="btn btn-outline-secondary" for="item_{{ $item->id }}_others">Others</label>
                                            </div>
                                            <div class="others-text-input" id="others_input_{{ $item->id }}" style="display: none;">
                                                <input type="text" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_text]" class="form-control" placeholder="Please specify...">
                                            </div>
                                        </div>

                                    @elseif($item->input_type == 'YES_NO_NA')
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_yes" value="Yes" {{ $item->is_required ? 'required' : '' }}>
                                            <label class="btn btn-outline-success" for="item_{{ $item->id }}_yes">Yes</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_no" value="No">
                                            <label class="btn btn-outline-danger" for="item_{{ $item->id }}_no">No</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_na2" value="NA">
                                            <label class="btn btn-outline-secondary" for="item_{{ $item->id }}_na2">N/A</label>
                                        </div>

                                    @elseif($item->input_type == 'PASS_FAIL_NA')
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_pass" value="Pass" {{ $item->is_required ? 'required' : '' }}>
                                            <label class="btn btn-outline-success" for="item_{{ $item->id }}_pass">Pass</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_fail" value="Fail">
                                            <label class="btn btn-outline-danger" for="item_{{ $item->id }}_fail">Fail</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_na3" value="NA">
                                            <label class="btn btn-outline-secondary" for="item_{{ $item->id }}_na3">N/A</label>
                                        </div>

                                    @elseif($item->input_type == 'OK_FAULTY_NA')
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_ok" value="Ok" {{ $item->is_required ? 'required' : '' }}>
                                            <label class="btn btn-outline-success" for="item_{{ $item->id }}_ok">Ok</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_faulty" value="Faulty">
                                            <label class="btn btn-outline-danger" for="item_{{ $item->id }}_faulty">Faulty</label>
                                            
                                            <input type="radio" class="btn-check" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_option]" id="item_{{ $item->id }}_na4" value="NA">
                                            <label class="btn btn-outline-secondary" for="item_{{ $item->id }}_na4">N/A</label>
                                        </div>

                                    @elseif($item->input_type == 'NUMBER')
                                        <div class="row">
                                            <div class="col-md-4">
                                                <input type="number" step="0.01" class="form-control number-input" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_number]" placeholder="Enter value" {{ $item->is_required ? 'required' : '' }}
                                                    data-warning="{{ $item->threshold_warning }}" 
                                                    data-critical="{{ $item->threshold_critical }}">
                                                <div class="threshold-indicator mt-1"></div>
                                            </div>
                                        </div>

                                    @elseif($item->input_type == 'TEXT')
                                        <textarea class="form-control" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_text]" rows="2" placeholder="Enter notes" {{ $item->is_required ? 'required' : '' }}></textarea>

                                    @elseif($item->input_type == 'IMAGE')
                                        <div class="image-upload-container">
                                            <input type="file" class="form-control image-input" accept="image/*" data-item-id="{{ $item->id }}">
                                            <input type="hidden" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][image_path]" class="image-path-input">
                                            <div class="image-preview mt-2"></div>
                                        </div>

                                    @elseif($item->input_type == 'DATE')
                                        <input type="date" class="form-control" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][value_text]" {{ $item->is_required ? 'required' : '' }}>
                                    @endif

                                    <div class="mt-2">
                                        <input type="text" class="form-control form-control-sm" name="items[{{ $loop->parent->index }}_{{ $loop->index }}][notes]" placeholder="Additional notes (optional)">
                                    </div>
                                </div>
                            @endforeach

                            <!-- Section Image Upload -->
                            <div class="mt-4 pt-3 border-top section-image-upload-container" data-section-id="{{ $section->id }}">
                                <label class="form-label fw-semibold">Section Documentation Image</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control section-image-input" accept="image/*" data-section-id="{{ $section->id }}">
                                        <input type="hidden" name="sections[{{ $loop->index }}][image_path]" class="section-image-path-input">
                                    </div>
                                    <div class="section-image-preview"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- General Notes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-light">
                        <h5 class="card-title mb-0">General Notes</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional observations or comments..."></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex gap-2 justify-content-end mb-4">
                    <a href="{{ route('inspections.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submit-inspection-btn">
                        <i class="ti ti-check me-1"></i>Submit Inspection
                    </button>
                </div>
            </form>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

@endsection

@push('scripts')
    <script>
        // SweetAlert2 notification function
        function showNotification(message, type = 'success') {
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

            Toast.fire({
                icon: type,
                title: message
            });
        }

        $(document).ready(function() {
            // Threshold validation for number inputs
            $('.number-input').on('input', function() {
                const value = parseFloat($(this).val());
                const warning = parseFloat($(this).data('warning'));
                const critical = parseFloat($(this).data('critical'));
                const indicator = $(this).siblings('.threshold-indicator');

                if (isNaN(value)) {
                    indicator.html('');
                    return;
                }

                if (critical && value >= critical) {
                    indicator.html('<span class="badge bg-danger"><i class="ti ti-alert-triangle"></i> Critical Level</span>');
                } else if (warning && value >= warning) {
                    indicator.html('<span class="badge bg-warning"><i class="ti ti-alert-circle"></i> Warning Level</span>');
                } else {
                    indicator.html('<span class="badge bg-success"><i class="ti ti-check"></i> Normal</span>');
                }
            });

            // Image upload
            $('.image-input').change(function() {
                const file = this.files[0];
                const itemId = $(this).data('item-id');
                const container = $(this).closest('.image-upload-container');
                const preview = container.find('.image-preview');
                const pathInput = container.find('.image-path-input');

                if (file) {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('item_id', itemId);
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: '{{ route("inspections.upload-image") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                pathInput.val(response.data.path);
                                preview.html(`<img src="${response.data.url}" class="img-thumbnail" style="max-width: 200px;">`);
                                showNotification('Image uploaded successfully', 'success');
                            }
                        },
                        error: function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to upload image', 'error');
                        }
                    });
                }
            });

            // Good / Others Toggle
            $(document).on('change', '.good-others-radio', function() {
                const itemId = $(this).data('item-id');
                const value = $(this).val();
                const $inputDiv = $('#others_input_' + itemId);
                const $textInput = $inputDiv.find('input');

                if (value === 'Others') {
                    $inputDiv.slideDown();
                    $textInput.prop('required', true);
                } else {
                    $inputDiv.slideUp();
                    $textInput.prop('required', false).val('');
                }
            });

            // Section Image upload
            $('.section-image-input').change(function() {
                const file = this.files[0];
                const sectionId = $(this).data('section-id');
                const container = $(this).closest('.section-image-upload-container');
                const preview = container.find('.section-image-preview');
                const pathInput = container.find('.section-image-path-input');

                if (file) {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('section_id', sectionId);
                    formData.append('_token', '{{ csrf_token() }}');

                    $.ajax({
                        url: '{{ route("inspections.upload-image") }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                pathInput.val(response.data.path);
                                preview.html(`<img src="${response.data.url}" class="img-thumbnail" style="max-height: 50px;">`);
                                showNotification('Section image uploaded successfully', 'success');
                            }
                        },
                        error: function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to upload image', 'error');
                        }
                    });
                }
            });

            // Form submission
            $('#inspection-form').submit(function(e) {
                e.preventDefault();

                const formData = {};
                const items = [];

                $('.inspection-item').each(function() {
                    const itemId = $(this).data('item-id');
                    const itemData = {
                        item_id: itemId,
                        value_text: $(this).find('[name*="value_text"]').val() || null,
                        value_number: $(this).find('[name*="value_number"]').val() || null,
                        value_option: $(this).find('[name*="value_option"]:checked').val() || null,
                        image_path: $(this).find('.image-path-input').val() || null,
                        notes: $(this).find('[name*="notes"]').val() || null
                    };
                    items.push(itemData);
                });

                formData.items = items;
                formData.notes = $('[name="notes"]').val();
                
                const sectionImages = [];
                $('.section-image-upload-container').each(function() {
                    const sectionId = $(this).data('section-id');
                    const path = $(this).find('.section-image-path-input').val();
                    if (path) {
                        sectionImages.push({
                            section_id: sectionId,
                            image_path: path
                        });
                    }
                });
                formData.section_images = sectionImages;

                $.ajax({
                    url: '{{ route("inspections.submit", $result->id) }}',
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            setTimeout(() => {
                                window.location.href = '{{ route("inspections.result", $result->id) }}';
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to submit inspection', 'error');
                    }
                });
            });
        });
    </script>
@endpush
