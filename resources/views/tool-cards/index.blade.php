<?php $page = 'tool-cards'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Tool Card Access</h4>
                    <p class="text-muted mb-0">Manage Employee Tool Access Cards</p>
                </div>
                <a href="{{ route('tool-cards.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i>New Tool Card</a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="tool-cards-table">
                            <thead class="table-light">
                                <tr>
                                    <th>UID</th>
                                    <th>Employee</th>
                                    <th>Access Level</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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
            var table = $('#tool-cards-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("tool-cards.datatables") }}',
                columns: [
                    { data: 'uid', name: 'uid' },
                    { data: 'employee_name', name: 'employee.name' },
                    { data: 'access_level_badge', name: 'access_level' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'creator.name', name: 'creator.name', defaultContent: '-' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']] // Sort by ID desc roughly (or timestamp if column exists)
            });
        });
    </script>
@endpush
