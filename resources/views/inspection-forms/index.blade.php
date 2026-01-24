<?php $page = 'inspection_forms'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Inspection Forms</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Inspection Forms</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="{{ route('inspection-forms.create') }}" class="btn btn-primary">
                        <i class="ti ti-square-rounded-plus-filled me-1"></i>Create New Form
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon input-icon-start">
                            <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search forms..." id="search-input">
                        </div>
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="PUBLISHED">PUBLISHED</option>
                            <option value="ARCHIVED">ARCHIVED</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="inspection-forms-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Form Code</th>
                                    <th>Title</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Created By</th>
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
            const table = $('#inspection-forms-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("inspection-forms.datatables") }}',
                    data: function(d) {
                        d.status = $('#filter-status').val();
                        d.search = $('#search-input').val();
                    }
                },
                columns: [
                    { data: 'form_code', name: 'form_code' },
                    { data: 'form_title', name: 'form_title' },
                    { data: 'version', name: 'version' },
                    { data: 'status_badge', name: 'status', orderable: false },
                    { data: 'created_by_name', name: 'created_by_name' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[5, 'desc']]
            });

            // Filter handlers
            $('#filter-status, #search-input').on('change keyup', function() {
                table.draw();
            });

            // Edit form
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                window.location.href = '{{ url("inspection-forms") }}/' + id + '/edit';
            });

            // Preview form
            $(document).on('click', '.preview-btn', function() {
                const id = $(this).data('id');
                window.location.href = '{{ url("inspection-forms") }}/' + id + '/preview';
            });

            // Publish form
            $(document).on('click', '.publish-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Publish Form?',
                    text: "Published forms cannot be edited directly (will create revision).",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, publish it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("inspection-forms") }}/' + id + '/publish', {
                            _token: '{{ csrf_token() }}'
                        }).done(function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                table.ajax.reload();
                            }
                        }).fail(function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to publish form', 'error');
                        });
                    }
                });
            });

            // Archive form
            $(document).on('click', '.archive-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Archive Form?',
                    text: "Archived forms cannot be scheduled.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("inspection-forms") }}/' + id + '/archive', {
                            _token: '{{ csrf_token() }}'
                        }).done(function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                table.ajax.reload();
                            }
                        }).fail(function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to archive form', 'error');
                        });
                    }
                });
            });

            // Delete form
            $(document).on('click', '.delete-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Form?',
                    text: "You won't be able to revert this!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("inspection-forms") }}/' + id,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' }
                        }).done(function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                table.ajax.reload();
                            }
                        }).fail(function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to delete form', 'error');
                        });
                    }
                });
            });

            // Duplicate form
            $(document).on('click', '.duplicate-btn', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Duplicate Form?',
                    text: "Create a copy of this form?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, duplicate it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('{{ url("inspection-forms") }}/' + id + '/duplicate', {
                            _token: '{{ csrf_token() }}'
                        }).done(function(response) {
                            if (response.success) {
                                showNotification(response.message, 'success');
                                table.ajax.reload();
                            }
                        }).fail(function(xhr) {
                            showNotification(xhr.responseJSON?.message || 'Failed to duplicate form', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endpush
