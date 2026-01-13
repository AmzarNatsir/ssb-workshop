<?php $page = 'mechanic_jobs'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">Job Details: {{ $workOrder->work_order_no }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('mechanic-job.index') }}">My Jobs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $workOrder->work_order_no }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <h4 class="mb-1">Job Details: {{ $workOrder->work_order_no }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('mechanic-job.index') }}">My Jobs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $workOrder->work_order_no }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-info btn-view-summary"><i class="ti ti-file-report me-1"></i> Summary</button>
                    @if($workOrder->status != 'READY' && $workOrder->status != 'CLOSED')
                        <button class="btn btn-success btn-finish-job"><i class="ti ti-check me-1"></i> Finish Job</button>
                    @endif
                </div>
            </div>

            <div class="row">
                <!-- Job Info -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Work Order Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small d-block">Asset / Unit</label>
                                <span class="fw-medium">{{ $workOrder->equipment->code }} - {{ $workOrder->equipment->name }}</span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Description</label>
                                <p class="mb-0">{{ $workOrder->description }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Priority</label>
                                <span class="badge {{ $workOrder->priority == 'HIGH' ? 'bg-danger' : 'bg-warning' }}">{{ $workOrder->priority }}</span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Status</label>
                                <span class="badge bg-secondary">{{ $workOrder->status }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Log -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">Recent Logs</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="activity-log-list">
                                @forelse($workOrder->activities()->latest()->take(5)->get() as $act)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <small class="fw-bold">{{ $act->mechanic->name ?? 'Me' }}</small>
                                            <small class="text-muted">{{ $act->created_at->format('d M H:i') }}</small>
                                        </div>
                                        <p class="mb-0 small text-muted">{{ Str::limit($act->description, 50) }}</p>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-muted small">No activities yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checklist -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header border-bottom bg-light p-0">
                            <!-- Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bottom border-0" id="jobTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="checklist-tab" data-bs-toggle="tab" href="#checklist" role="tab" aria-controls="checklist" aria-selected="true">
                                        <i class="ti ti-list-check me-1"></i> Activity Checklist
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="component-tab" data-bs-toggle="tab" href="#component" role="tab" aria-controls="component" aria-selected="false">
                                        <i class="ti ti-engine me-1"></i> Component Check
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="jobTabsContent">
                                <!-- Activity Checklist Tab -->
                                <div class="tab-pane fade show active" id="checklist" role="tabpanel" aria-labelledby="checklist-tab">
                                    <div class="accordion" id="checklistAccordion">
                                        @foreach($masterActivities as $category => $activities)
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading{{ Str::slug($category) }}">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ Str::slug($category) }}">
                                                        {{ $category ?: 'General' }}
                                                    </button>
                                                </h2>
                                                <div id="collapse{{ Str::slug($category) }}" class="accordion-collapse collapse" data-bs-parent="#checklistAccordion">
                                                    <div class="accordion-body p-0">
                                                        <div class="list-group list-group-flush">
                                                            @foreach($activities as $item)
                                                                <div class="list-group-item d-flex align-items-center gap-3">
                                                                    <div class="flex-grow-1">
                                                                        <h6 class="mb-0">{{ $item->description }}</h6>
                                                                        <small class="text-muted">{{ $item->code }}</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary btn-check-item" 
                                                                        data-code="{{ $item->code }}" 
                                                                        data-desc="{{ $item->description }}">
                                                                        <i class="ti ti-check"></i> Done
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Component Check Tab -->
                                <div class="tab-pane fade" id="component" role="tabpanel" aria-labelledby="component-tab">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Component</th>
                                                    <th>Status</th>
                                                    <th>Remarks</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($componentTroubles as $comp)
                                                    <tr id="row-{{ $comp->id }}">
                                                        <td>
                                                            <span class="fw-bold d-block">{{ $comp->component_name }}</span>
                                                            <small class="text-muted">{{ $comp->description }}</small>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <input type="radio" class="btn-check" name="status-{{ $comp->id }}" id="good-{{ $comp->id }}" value="GOOD" checked autocomplete="off">
                                                                <label class="btn btn-outline-success btn-sm" for="good-{{ $comp->id }}">Good</label>
        
                                                                <input type="radio" class="btn-check" name="status-{{ $comp->id }}" id="trouble-{{ $comp->id }}" value="TROUBLE" autocomplete="off">
                                                                <label class="btn btn-outline-danger btn-sm" for="trouble-{{ $comp->id }}">Trouble</label>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm remark-input" id="remark-{{ $comp->id }}" placeholder="Details..." style="display:none;">
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary btn-save-check" data-id="{{ $comp->id }}">
                                                                <i class="ti ti-device-floppy"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-muted">No components defined.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Log Modal -->
    <div class="modal fade" id="logItemModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Log Activity: <span id="modal-item-desc"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="checklist-form">
                        @csrf
                        <input type="hidden" name="activity_code" id="modal-item-code">
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Start Time</label>
                                <input type="datetime-local" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">End Time</label>
                                <input type="datetime-local" name="end_time" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any observations?"></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="btn-save-log">Save Log</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        
        // Open Modal
        $(document).on('click', '.btn-check-item', function() {
            const code = $(this).data('code');
            const desc = $(this).data('desc');
            
            $('#modal-item-code').val(code);
            $('#modal-item-desc').text(desc);
            
            // Set default times (Now - 30m, Now)
            const now = new Date();
            const offset = now.getTimezoneOffset() * 60000;
            const end = (new Date(now - offset)).toISOString().slice(0, 16);
            const start = (new Date(now - offset - 1800000)).toISOString().slice(0, 16); // 30 mins ago

            $('input[name="end_time"]').val(end);
            $('input[name="start_time"]').val(start);
            $('textarea[name="notes"]').val('');

            $('#logItemModal').modal('show');
        });

        // Submit Log
        $('#checklist-form').submit(function(e) {
            e.preventDefault();
            const btn = $('#btn-save-log');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('mechanic-job.store-checklist', $workOrder->id) }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Success', 'Activity logged!', 'success');
                        $('#logItemModal').modal('hide');
                        // Refresh log list?
                        location.reload(); 
                    }
                },
                error: function(err) {
                    Swal.fire('Error', err.responseJSON?.message || 'Failed to log.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save Log');
                }
            });
        });

        // Toggle Remarks input
        $(document).on('change', 'input[type=radio][name^=status-]', function() {
            const rowId = $(this).attr('name').split('-')[1];
            if ($(this).val() === 'TROUBLE') {
                $('#remark-' + rowId).show().focus();
            } else {
                $('#remark-' + rowId).hide();
            }
        });

        // Save Component Check
        $('.btn-save-check').click(function() {
            const id = $(this).data('id');
            const status = $('input[name="status-'+id+'"]:checked').val();
            const remarks = $('#remark-'+id).val();
            const btn = $(this);

            if (!status) return;
            if (status === 'TROUBLE' && !remarks) {
                Swal.fire('Warning', 'Please provide remarks for trouble items.', 'warning');
                return;
            }

            btn.prop('disabled', true);

            $.ajax({
                url: "{{ route('mechanic-job.store-component-check', $workOrder->id) }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    component_id: id,
                    status: status,
                    remarks: remarks
                },
                success: function(res) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Saved',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    // Visual feedback
                    $('#row-'+id).addClass('table-success');
                    setTimeout(() => $('#row-'+id).removeClass('table-success'), 1000);
                },
                error: function(err) {
                    Swal.fire('Error', 'Failed to save check.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });


        // View Summary
        $('.btn-view-summary').click(function() {
            // Use existing modal container or create a new one?
            // Let's reuse offcanvas logic but for modal, or just a generic modal container.
            // We have #logItemModal, let's make a generic one.
            $('#summaryModalContent').html('<div class="p-4 text-center"><span class="spinner-border"></span> Loading...</div>');
            $('#summaryModal').modal('show');

            $.get("{{ route('mechanic-job.summary', $workOrder->id) }}", function(data) {
                $('#summaryModalContent').html(data);
            });
        });

        // Finish Job
        $('.btn-finish-job').click(function() {
            Swal.fire({
                title: 'Finish this Job?',
                text: "This will mark the Work Order as READY. Ensure all items are checked.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Yes, Finish Job!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('mechanic-job.finish', $workOrder->id) }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire('Finished!', 'Job is now READY.', 'success').then(() => {
                                    window.location.href = "{{ route('mechanic-job.index') }}";
                                });
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error', err.responseJSON?.message || 'Failed.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
<!-- Generic Modal for Summary -->
<div class="modal fade" id="summaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" id="summaryModalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>
@endpush
