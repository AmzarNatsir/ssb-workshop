<?php $page = 'inspections'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">My Inspections</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Inspections</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="PENDING">Pending</option>
                            <option value="PASS">Passed</option>
                            <option value="FAIL">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="inspections-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Result Code</th>
                                    <th>Form</th>
                                    <th>Unit</th>
                                    <th>Inspection Date</th>
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

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#inspections-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("inspections.datatables") }}',
                    data: function(d) {
                        d.status = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'result_code', name: 'result_code' },
                    { data: 'form_title', name: 'form_title' },
                    { data: 'unit_name', name: 'unit_name' },
                    { data: 'inspection_date', name: 'inspection_date' },
                    { data: 'status_badge', name: 'overall_status', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']]
            });

            $('#filter-status').change(function() {
                table.draw();
            });
        });
    </script>
@endpush
