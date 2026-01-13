<?php $page = 'roles-permissions'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        <!-- Start Content -->
        <div class="content pb-0">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Roles & Permissions<span class="badge badge-soft-primary ms-2">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Roles & Permissions</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-outline-light px-2 shadow" data-bs-toggle="dropdown"><i class="ti ti-package-export me-2"></i>Export</a>
                        <div class="dropdown-menu  dropdown-menu-end">
                            <ul>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-file-type-pdf me-1"></i>Export as
                                        PDF</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item"><i class="ti ti-file-type-xls me-1"></i>Export as
                                        Excel </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh"><i class="ti ti-refresh"></i></a>
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Collapse" data-bs-original-title="Collapse" id="collapse-header"><i class="ti ti-transition-top"></i></a>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- card start -->
            <div class="card border-0 rounded-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="input-icon input-icon-start position-relative">
                        <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    <a href="javascript:void(0);" class="btn btn-primary btn-add-role" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add New Role</a>
                </div>
                <div class="card-body">

                    <!-- Contact List -->
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="roles_list">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>Role Name</th>
                                    <th>Created</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="row align-items-center d-none">
                        <div class="col-md-6">
                            <div class="datatable-length"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="datatable-paginate"></div>
                        </div>
                    </div>
                    <!-- /Contact List -->

                </div>
            </div>
            <!-- card end -->

        </div>
        <!-- End Content -->

        @component('components.footer')
        @endcomponent

    </div>

    <!-- ========================
        End Page Content
    ========================= -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold" id="offcanvas_title">Add New Role</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-add-body"></div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($('#roles_list').length > 0) {
        $('#roles_list').DataTable({
            processing: true,
            serverSide: false,
            ajax: "{{ route('roles.datatables') }}",
            columns: [
                { data: 'id', render: function(data) {
                    return '<div class="form-check form-check-md"><input class="form-check-input" type="checkbox" value="'+data+'"></div>';
                }},
                { data: 'name', render: function(data) {
                    return '<h6 class="fw-medium"><a href="javascript:void(0);">'+data+'</a></h6>';
                }},
                { data: 'created' },
                { data: 'id', render: function(data) {
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light edit-role-btn" data-id="${data}">
                                <i class="ti ti-edit"></i>
                            </a>
                            <form action="{{ url('roles') }}/${data}" method="POST" class="d-inline delete-role-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-icon btn-light text-danger">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </div>
                    `;
                }}
            ],
            language: {
                search: " ",
                searchPlaceholder: "Search Roles...",
                paginate: {
                    next: 'Next <i class="ti ti-chevron-right ms-1"></i>',
                    previous: '<i class="ti ti-chevron-left me-1"></i> Previous'
                }
            }
        });
    }

    // Role Add Offcanvas
    $('.btn-add-role').on('click', function() {
        $('#offcanvas_title').text('Add New Role');
        $.get("{{ route('roles.create') }}", function(data) {
            $('#offcanvas-add-body').html(data);
        });
    });

    // Role Edit Offcanvas
    $(document).on('click', '.edit-role-btn', function() {
        const id = $(this).data('id');
        $('#offcanvas_title').text('Edit Role');
        $.get("{{ url('roles') }}/" + id + "/edit", function(data) {
            $('#offcanvas-add-body').html(data);
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_add'));
            offcanvas.show();
        });
    });

    // Matrix Row All Toggle
    $(document).on('change', '.row-all-check', function() {
        const isChecked = $(this).prop('checked');
        $(this).closest('tr').find('input[type="checkbox"]').not(this).prop('checked', isChecked);
    });

    // Update Row All Toggle on individual checkbox change
    $(document).on('change', '#permission_list input[type="checkbox"]:not(.row-all-check)', function() {
        const row = $(this).closest('tr');
        const checkboxes = row.find('input[type="checkbox"]:not(.row-all-check):not(:disabled)');
        const checkedCount = checkboxes.filter(':checked').length;
        row.find('.row-all-check').prop('checked', checkedCount === checkboxes.length);
    });

    // Delete confirmation
    $(document).on('submit', '.delete-role-form', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
