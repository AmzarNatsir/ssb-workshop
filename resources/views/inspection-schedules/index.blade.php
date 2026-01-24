<?php $page = 'inspection_schedules'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Inspection Schedules</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Inspection Schedules</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add">
                        <i class="ti ti-square-rounded-plus-filled me-1"></i>Create Schedule
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="schedules-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Form</th>
                                    <th>Unit</th>
                                    <th>Frequency</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Schedule Time</th>
                                    <th>Status</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Offcanvas Add Schedule -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Create Inspection Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="add-schedule-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Inspection Form <span class="text-danger">*</span></label>
                    <select name="form_id" class="form-select" required>
                        <option value="">Select Form</option>
                        @foreach($forms as $form)
                            <option value="{{ $form->id }}">{{ $form->form_code }} - {{ $form->form_title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Equipment / Unit <span class="text-danger">*</span></label>
                    <select name="unit_id" class="form-select select2" required>
                        <option value="">Select Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->code }} - {{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select name="frequency" class="form-select" required>
                            <option value="DAILY">Daily</option>
                            <option value="WEEKLY">Weekly</option>
                            <option value="MONTHLY">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Schedule Time</label>
                        <input type="time" name="schedule_time" class="form-control" value="07:00">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Schedule</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offcanvas Edit Schedule -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Edit Inspection Schedule</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="edit-schedule-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id">
                <!-- Form and Unit are read-only in edit mode to prevent logic issues -->
                <div class="mb-3">
                    <label class="form-label">Inspection Form</label>
                    <input type="text" class="form-control" id="edit_form_title" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Equipment / Unit</label>
                    <input type="text" class="form-control" id="edit_unit_name" readonly disabled>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select name="frequency" id="edit_frequency" class="form-select" required>
                            <option value="DAILY">Daily</option>
                            <option value="WEEKLY">Weekly</option>
                            <option value="MONTHLY">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Schedule Time</label>
                        <input type="time" name="schedule_time" id="edit_schedule_time" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" id="edit_end_date" class="form-control">
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </div>
            </form>
        </div>
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
            const table = $('#schedules-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("inspection-schedules.datatables") }}',
                columns: [
                    { data: 'form_title', name: 'form_title' },
                    { data: 'unit_name', name: 'unit_name' },
                    { data: 'frequency', name: 'frequency' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'schedule_time', name: 'schedule_time' },
                    { data: 'status_badge', name: 'is_active', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Initialize Select2
            $('.select2').select2({
                dropdownParent: $('#offcanvas_add')
            });

            // Create schedule
            $('#add-schedule-form').submit(function(e) {
                e.preventDefault();
                $.post('{{ route("inspection-schedules.store") }}', $(this).serialize())
                    .done(function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            table.ajax.reload();
                            bootstrap.Offcanvas.getInstance('#offcanvas_add').hide();
                            $('#add-schedule-form')[0].reset();
                        }
                    })
                    .fail(function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to create schedule', 'error');
                    });
            });

            // Edit schedule - load data and show offcanvas
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                $.get(`{{ url("inspection-schedules") }}/${id}`)
                    .done(function(schedule) {
                        $('#edit_id').val(schedule.id);
                        $('#edit_form_title').val(schedule.form ? schedule.form.form_code + ' - ' + schedule.form.form_title : '-');
                        $('#edit_unit_name').val(schedule.unit ? schedule.unit.code + ' - ' + schedule.unit.name : '-');
                        $('#edit_frequency').val(schedule.frequency);
                        $('#edit_schedule_time').val(schedule.schedule_time || '');
                        
                        // Format dates to Y-m-d for input fields
                        if (schedule.start_date) {
                            const startDate = new Date(schedule.start_date);
                            $('#edit_start_date').val(startDate.toISOString().split('T')[0]);
                        }
                        if (schedule.end_date) {
                            const endDate = new Date(schedule.end_date);
                            $('#edit_end_date').val(endDate.toISOString().split('T')[0]);
                        }
                        
                        const offcanvas = new bootstrap.Offcanvas('#offcanvas_edit');
                        offcanvas.show();
                    })
                    .fail(function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to load schedule data', 'error');
                    });
            });

            // Update schedule
            $('#edit-schedule-form').submit(function(e) {
                e.preventDefault();
                const id = $('#edit_id').val();
                
                // Prepare form data, removing empty schedule_time
                let formData = $(this).serializeArray();
                formData = formData.filter(function(item) {
                    // Remove schedule_time if it's empty
                    if (item.name === 'schedule_time' && item.value === '') {
                        return false;
                    }
                    // Convert schedule_time to H:i format if it contains AM/PM
                    if (item.name === 'schedule_time' && item.value) {
                        // Check if value contains AM/PM (12-hour format)
                        if (item.value.match(/AM|PM/i)) {
                            const time = item.value.trim();
                            const parts = time.match(/(\d+):(\d+)(?::(\d+))?\s*(AM|PM)/i);
                            if (parts) {
                                let hours = parseInt(parts[1]);
                                const minutes = parts[2];
                                const period = parts[4].toUpperCase();
                                
                                // Convert to 24-hour format
                                if (period === 'PM' && hours !== 12) {
                                    hours += 12;
                                } else if (period === 'AM' && hours === 12) {
                                    hours = 0;
                                }
                                
                                item.value = String(hours).padStart(2, '0') + ':' + minutes;
                            }
                        }
                    }
                    return true;
                });
                
                $.ajax({
                    url: `{{ url("inspection-schedules") }}/${id}`,
                    type: 'PUT',
                    data: $.param(formData),
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            table.ajax.reload();
                            bootstrap.Offcanvas.getInstance('#offcanvas_edit').hide();
                        }
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to update schedule', 'error');
                    }
                });
            });

            // Activate/Deactivate
            $(document).on('click', '.activate-btn, .deactivate-btn', function() {
                const id = $(this).data('id');
                const action = $(this).hasClass('activate-btn') ? 'activate' : 'deactivate';
                $.post(`{{ url("inspection-schedules") }}/${id}/${action}`, { _token: '{{ csrf_token() }}' })
                    .done(function(response) {
                        if (response.success) {
                            showNotification(response.message, 'success');
                            table.ajax.reload();
                        }
                    })
                    .fail(function(xhr) {
                        showNotification(xhr.responseJSON?.message || 'Failed to ' + action + ' schedule', 'error');
                    });
            });

            // Delete
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Schedule?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url("inspection-schedules") }}/${id}`,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' }
                        }).done(function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                table.ajax.reload();
                            }
                        }).fail(function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to delete schedule', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endpush
