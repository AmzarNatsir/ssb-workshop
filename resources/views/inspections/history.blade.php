<?php $page = 'inspections'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Inspection History</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Inspection History</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Filters -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">Unit</label>
                            <select class="form-select" id="filter-unit">
                                <option value="">All Units</option>
                                @foreach(\App\Models\Equipments::all() as $unit)
                                    <option value="{{ $unit->id }}" {{ isset($unitId) && $unitId == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->code }} - {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label small">Form</label>
                            <select class="form-select" id="filter-form">
                                <option value="">All Forms</option>
                                @foreach(\App\Models\InspectionForm::published()->get() as $form)
                                    <option value="{{ $form->id }}">{{ $form->form_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select" id="filter-status">
                                <option value="">All Status</option>
                                <option value="PASS">Pass</option>
                                <option value="FAIL">Fail</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">Date From</label>
                            <input type="date" class="form-control" id="filter-date-from">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="form-label small">Date To</label>
                            <input type="date" class="form-control" id="filter-date-to">
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="history-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Result Code</th>
                                    <th>Form</th>
                                    <th>Unit</th>
                                    <th>Inspector</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Unit Ready</th>
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
            const table = $('#history-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("inspections.datatables") }}',
                    data: function(d) {
                        d.unit_id = $('#filter-unit').val();
                        d.form_id = $('#filter-form').val();
                        d.status = $('#filter-status').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                        d.all_results = true; // Get all results, not just current user
                    }
                },
                columns: [
                    { data: 'result_code', name: 'result_code' },
                    { data: 'form_title', name: 'form_title' },
                    { data: 'unit_name', name: 'unit_name' },
                    { 
                        data: 'inspector', 
                        name: 'inspector',
                        render: function(data, type, row) {
                            return data ? data.name : '-';
                        }
                    },
                    { data: 'inspection_date', name: 'inspection_date' },
                    { 
                        data: 'duration', 
                        name: 'duration',
                        render: function(data, type, row) {
                            if (row.start_time && row.end_time) {
                                const start = new Date(row.start_time);
                                const end = new Date(row.end_time);
                                const minutes = Math.round((end - start) / 60000);
                                return minutes + ' min';
                            }
                            return '-';
                        }
                    },
                    { data: 'status_badge', name: 'overall_status', orderable: false },
                    { 
                        data: 'unit_ready_for_operation', 
                        name: 'unit_ready_for_operation',
                        render: function(data) {
                            return data 
                                ? '<span class="badge bg-success">Yes</span>' 
                                : '<span class="badge bg-danger">No</span>';
                        }
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false,
                        render: function(data, type, row) {
                            return '<a href="{{ url("inspections/result") }}/' + row.id + '" class="btn btn-sm btn-info">View</a>';
                        }
                    }
                ],
                order: [[4, 'desc']]
            });

            // Filter handlers
            $('#filter-unit, #filter-form, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
                table.draw();
            });
        });
    </script>
@endpush
