<?php $page = 'inspection_forms'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">{{ isset($form) ? 'Edit Inspection Form' : 'Create Inspection Form' }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inspection-forms.index') }}">Inspection Forms</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ isset($form) ? 'Edit' : 'Create' }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <form id="form-builder-form">
                @csrf
                @if(isset($form))
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="id" value="{{ $form->id }}">
                @endif

                <!-- Form Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">Form Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Form Title <span class="text-danger">*</span></label>
                                <input type="text" name="form_title" class="form-control" required placeholder="e.g. Daily Inspection - Excavator" value="{{ $form->form_title ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Applicable Unit Category</label>
                                <select name="applicable_unit_category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="Excavator" {{ (isset($form) && $form->applicable_unit_category == 'Excavator') ? 'selected' : '' }}>Excavator</option>
                                    <option value="Dump Truck" {{ (isset($form) && $form->applicable_unit_category == 'Dump Truck') ? 'selected' : '' }}>Dump Truck</option>
                                    <option value="Wheel Loader" {{ (isset($form) && $form->applicable_unit_category == 'Wheel Loader') ? 'selected' : '' }}>Wheel Loader</option>
                                    <option value="Generator" {{ (isset($form) && $form->applicable_unit_category == 'Generator') ? 'selected' : '' }}>Generator</option>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="form_description" class="form-control" rows="2" placeholder="Describe the purpose of this inspection form">{{ $form->form_description ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sections Container -->
                <div id="sections-container">
                    @if(isset($form) && $form->sections)
                        @foreach($form->sections as $sIndex => $section)
                            <div class="card border-0 shadow-sm mb-4 section-card" data-section-index="{{ $sIndex }}">
                                <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ti ti-grip-vertical text-muted cursor-move"></i>
                                        <input type="text" name="sections[{{ $sIndex }}][section_title]" class="form-control form-control-sm fw-semibold" value="{{ $section->section_title }}" placeholder="Section Title" style="max-width: 300px;">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">
                                        <i class="ti ti-trash"></i> Remove Section
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="text" name="sections[{{ $sIndex }}][section_description]" class="form-control form-control-sm" value="{{ $section->section_description }}" placeholder="Section Description (optional)">
                                    </div>
                                    <div class="items-container">
                                        @foreach($section->items as $iIndex => $item)
                                            @include('inspection-forms.partials.item-row', ['sIndex' => $sIndex, 'iIndex' => $iIndex, 'item' => $item])
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary add-item-btn mt-2">
                                        <i class="ti ti-plus"></i> Add Inspection Item
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Add Section Button -->
                <div class="mb-4">
                    <button type="button" class="btn btn-outline-success" id="add-section-btn">
                        <i class="ti ti-plus"></i> Add Section
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 justify-content-end mb-4">
                    <a href="{{ route('inspection-forms.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="save-form-btn">
                        <i class="ti ti-device-floppy me-1"></i>Save Form
                    </button>
                </div>
            </form>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Item Row Template -->
    <template id="item-row-template">
        <div class="item-row border rounded p-3 mb-2 bg-white">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="sections[__SECTION__][items][__ITEM__][item_name]" class="form-control form-control-sm" required placeholder="e.g. Engine Oil Level">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label small">Input Type <span class="text-danger">*</span></label>
                    <select name="sections[__SECTION__][items][__ITEM__][input_type]" class="form-select form-select-sm input-type-select" required>
                        <option value="GOOD_REPAIR_REPLACE_NA">Good / Repair / Replace / NA</option>
                        <option value="YES_NO_NA">Yes / No / NA</option>
                        <option value="PASS_FAIL_NA">Pass / Fail / NA</option>
                        <option value="OK_FAULTY_NA">Ok / Faulty / NA</option>
                        <option value="NUMBER">Number</option>
                        <option value="TEXT">Text</option>
                        <option value="IMAGE">Image Upload</option>
                        <option value="DATE">Date</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Required</label>
                    <div class="form-check form-switch mt-1">
                        <input type="checkbox" name="sections[__SECTION__][items][__ITEM__][is_required]" class="form-check-input" value="1">
                    </div>
                </div>
                <div class="col-md-3 mb-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn mt-3">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
            <!-- Threshold fields (shown only for NUMBER type) -->
            <div class="row threshold-fields" style="display: none;">
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Threshold Warning</label>
                    <input type="number" step="0.01" name="sections[__SECTION__][items][__ITEM__][threshold_warning]" class="form-control form-control-sm" placeholder="e.g. 25">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Threshold Critical</label>
                    <input type="number" step="0.01" name="sections[__SECTION__][items][__ITEM__][threshold_critical]" class="form-control form-control-sm" placeholder="e.g. 10">
                </div>
            </div>
            <!-- Auto Action -->
            <div class="row mt-2">
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Auto Action</label>
                    <select name="sections[__SECTION__][items][__ITEM__][auto_action_type]" class="form-select form-select-sm">
                        <option value="">No Action</option>
                        <option value="CREATE_WR">Create Work Request</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Priority (if WR)</label>
                    <select name="sections[__SECTION__][items][__ITEM__][auto_action_priority]" class="form-select form-select-sm">
                        <option value="LOW">Low</option>
                        <option value="MEDIUM">Medium</option>
                        <option value="HIGH">High</option>
                        <option value="CRITICAL">Critical</option>
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Trigger On</label>
                    <input type="text" name="sections[__SECTION__][items][__ITEM__][auto_action_trigger]" class="form-control form-control-sm" placeholder="e.g. Repair,Replace">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <label class="form-label small">Instruction</label>
                    <input type="text" name="sections[__SECTION__][items][__ITEM__][instruction]" class="form-control form-control-sm" placeholder="Instructions for inspector">
                </div>
            </div>
        </div>
    </template>

    <!-- Section Template -->
    <template id="section-template">
        <div class="card border-0 shadow-sm mb-4 section-card" data-section-index="__SECTION__">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-grip-vertical text-muted cursor-move"></i>
                    <input type="text" name="sections[__SECTION__][section_title]" class="form-control form-control-sm fw-semibold" placeholder="Section Title (e.g. Engine)" style="max-width: 300px;">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">
                    <i class="ti ti-trash"></i> Remove Section
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" name="sections[__SECTION__][section_description]" class="form-control form-control-sm" placeholder="Section Description (optional)">
                </div>
                <div class="items-container"></div>
                <button type="button" class="btn btn-sm btn-outline-primary add-item-btn mt-2">
                    <i class="ti ti-plus"></i> Add Inspection Item
                </button>
            </div>
        </div>
    </template>

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
            let sectionIndex = {{ isset($form) ? $form->sections->count() : 0 }};
            let itemIndexes = {};

            // Add Section
            $('#add-section-btn').click(function() {
                const template = $('#section-template').html().replace(/__SECTION__/g, sectionIndex);
                $('#sections-container').append(template);
                itemIndexes[sectionIndex] = 0;
                sectionIndex++;
            });

            // Remove Section
            $(document).on('click', '.remove-section-btn', function() {
                if (confirm('Remove this section and all its items?')) {
                    $(this).closest('.section-card').remove();
                }
            });

            // Add Item
            $(document).on('click', '.add-item-btn', function() {
                const sectionCard = $(this).closest('.section-card');
                const sIndex = sectionCard.data('section-index');
                if (!itemIndexes[sIndex]) itemIndexes[sIndex] = 0;
                
                const template = $('#item-row-template').html()
                    .replace(/__SECTION__/g, sIndex)
                    .replace(/__ITEM__/g, itemIndexes[sIndex]);
                
                sectionCard.find('.items-container').append(template);
                itemIndexes[sIndex]++;
            });

            // Remove Item
            $(document).on('click', '.remove-item-btn', function() {
                $(this).closest('.item-row').remove();
            });

            // Toggle threshold fields based on input type
            $(document).on('change', '.input-type-select', function() {
                const thresholdFields = $(this).closest('.item-row').find('.threshold-fields');
                if ($(this).val() === 'NUMBER') {
                    thresholdFields.show();
                } else {
                    thresholdFields.hide();
                }
            });

            // Form Submit
            $('#form-builder-form').submit(function(e) {
                e.preventDefault();
                
                const formData = {};
                const $form = $(this);
                
                formData.form_title = $form.find('[name="form_title"]').val();
                formData.form_description = $form.find('[name="form_description"]').val();
                formData.applicable_unit_category = $form.find('[name="applicable_unit_category"]').val();
                formData.sections = [];

                // Collect sections and items
                $form.find('.section-card').each(function(sIdx) {
                    const section = {
                        section_title: $(this).find('[name*="section_title"]').val(),
                        section_description: $(this).find('[name*="section_description"]').val(),
                        items: []
                    };

                    $(this).find('.item-row').each(function(iIdx) {
                        const $item = $(this);
                        const autoActionType = $item.find('[name*="auto_action_type"]').val();
                        const autoActionPriority = $item.find('[name*="auto_action_priority"]').val();
                        const autoActionTrigger = $item.find('[name*="auto_action_trigger"]').val();

                        const item = {
                            item_name: $item.find('[name*="item_name"]').val(),
                            input_type: $item.find('[name*="input_type"]').val(),
                            is_required: $item.find('[name*="is_required"]').is(':checked'),
                            threshold_warning: $item.find('[name*="threshold_warning"]').val() || null,
                            threshold_critical: $item.find('[name*="threshold_critical"]').val() || null,
                            instruction: $item.find('[name*="instruction"]').val(),
                            auto_action: autoActionType ? {
                                action: autoActionType,
                                priority: autoActionPriority,
                                trigger_on: autoActionTrigger ? autoActionTrigger.split(',').map(s => s.trim()) : []
                            } : null
                        };
                        section.items.push(item);
                    });

                    formData.sections.push(section);
                });

                const isEdit = $form.find('[name="id"]').val();
                const url = isEdit 
                    ? '{{ url("inspection-forms") }}/' + isEdit
                    : '{{ route("inspection-forms.store") }}';
                const method = isEdit ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).done(function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("inspection-forms.index") }}';
                        }, 1000);
                    }
                }).fail(function(xhr) {
                    showNotification(xhr.responseJSON?.message || 'Failed to save form', 'error');
                });
            });

            // Initialize item indexes for existing sections
            @if(isset($form))
                @foreach($form->sections as $sIndex => $section)
                    itemIndexes[{{ $sIndex }}] = {{ $section->items->count() }};
                @endforeach
            @endif
        });
    </script>
@endpush
