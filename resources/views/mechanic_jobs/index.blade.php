<?php $page = 'mechanic_jobs'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-1">My Jobs</h4>
                    <p class="mb-0 text-muted">Manage your assigned Work Orders.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="mechanic-job-list">
                            <thead class="table-light">
                                <tr>
                                    <th>WO No</th>
                                    <th>Asset / Unit</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created At</th>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#mechanic-job-list').DataTable({
            processing: true,
            serverSide: false, // Changed to false because we send all data in one go manually
            ajax: "{{ route('mechanic-job.index') }}",
            columns: [
                { data: 'work_order_no', name: 'work_order_no' },
                { data: 'equipment_name', name: 'equipment_name' },
                { data: 'priority', name: 'priority' },
                { data: 'status', name: 'status' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[4, 'desc']]
        });

        // View Result Button
        $(document).on('click', '.btn-view-result', function() {
            var id = $(this).data('id');
            $('#summaryModalContent').html('<div class="p-4 text-center"><span class="spinner-border"></span> Loading...</div>');
            $('#summaryModal').modal('show');

            $.get("{{ url('mechanic-jobs') }}/" + id + "/summary", function(data) {
                $('#summaryModalContent').html(data);
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
