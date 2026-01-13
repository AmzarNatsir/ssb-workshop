<?php $page = 'equipment.index'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content pb-0">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Equipments<span class="badge badge-soft-primary ms-2" id="equipment-count">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Equipments</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh" onclick="refreshTable()"><i class="ti ti-refresh"></i></a>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- Form Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select filter-select" id="filter-category">
                                <option value="">All Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Merk</label>
                            <select class="form-select filter-select" id="filter-merk">
                                <option value="">All Merk</option>
                                @foreach($merks as $merk)
                                    <option value="{{ $merk->id }}">{{ $merk->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Type</label>
                            <select class="form-select filter-select" id="filter-unit-type">
                                <option value="">All Unit Type</option>
                                @foreach($unitTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100" onclick="refreshTable()">
                                <i class="ti ti-filter me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card start -->
            <div class="card border-0 rounded-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="input-icon input-icon-start position-relative">
                        <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="search-input" placeholder="Search">
                    </div>
                    <a href="javascript:void(0);" class="btn btn-primary btn-add" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add New</a>
                </div>
                <div class="card-body">

                    <!-- Table -->
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="equipment-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Plate Number</th>
                                    <th>Category</th>
                                    <th>Merk</th>
                                    <th>Unit Type</th>
                                    <th>Service Period</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="no-sort text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /Table -->

                </div>
            </div>
            <!-- Card end -->

        </div>

        @component('components.footer')
        @endcomponent
    </div>

    <!-- Add Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add New Equipment</h5>
            <button type="button" class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-add-body"></div>
    </div>
    <!-- /Add Offcanvas -->

    <!-- Edit Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Edit Equipment</h5>
            <button type="button" class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-edit-body"></div>
    </div>
    <!-- /Edit Offcanvas -->

    <!-- Documents Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_docs">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Equipment Documents</h5>
            <button type="button" class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-docs-body"></div>
    </div>
    <!-- /Documents Offcanvas -->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let table = $('#equipment-table').DataTable({
        processing: true,
        serverSide: false, // Set to true if you want actual server-side pagination
        ajax: {
            url: '{{ route("equipment.datatables") }}',
            data: function(d) {
                d.category_id = $('#filter-category').val();
                d.merk_id = $('#filter-merk').val();
                d.unit_type_id = $('#filter-unit-type').val();
            }
        },
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'plate_number' },
            { data: 'category' },
            { data: 'merk' },
            { data: 'unit_type' },
            { data: 'service_period' },
            { data: 'status' },
            { data: 'created' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-outline-primary docs-btn" data-id="${row.id}" title="Documents">
                                <i class="ti ti-file-description"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light edit-btn" data-id="${row.id}" title="Edit">
                                <i class="ti ti-edit"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-danger delete-btn" data-id="${row.id}" title="Delete">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[7, 'desc']],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: '_MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                next: '<i class="ti ti-chevron-right"></i>',
                previous: '<i class="ti ti-chevron-left"></i>'
            }
        }
    });

    $('#search-input').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Load add form
    $('#offcanvas_add').on('show.bs.offcanvas', function() {
        $.get('{{ route("equipment.create") }}', function(data) {
            $('#offcanvas-add-body').html(data);
        });
    });

    // Handle form submission (Add)
    $(document).on('submit', '#add-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#offcanvas_add').offcanvas('hide');
                    table.ajax.reload();
                    updateCount();
                }
            },
            error: function(xhr) {
                let message = 'An error occurred.';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    message = Object.values(errors).flat().join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error!', text: message });
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('Save');
            }
        });
    });

    // Load edit form
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get('{{ url("equipment") }}/' + id + '/edit', function(data) {
            $('#offcanvas-edit-body').html(data);
            $('#offcanvas_edit').offcanvas('show');
        });
    });

    // Handle form submission (Edit)
    $(document).on('submit', '#edit-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#offcanvas_edit').offcanvas('hide');
                    table.ajax.reload();
                }
            },
            error: function(xhr) {
                let message = 'An error occurred.';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    message = Object.values(errors).flat().join('\n');
                }
                Swal.fire({ icon: 'error', title: 'Error!', text: message });
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html('Update');
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
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
                $.ajax({
                    url: '{{ url("equipment") }}/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                            updateCount();
                        }
                    }
                });
            }
        });
    });

    function updateCount() {
        $.get('{{ route("equipment.index") }}', function(data) {
            const newCount = $(data).find('#equipment-count').text();
            $('#equipment-count').text(newCount);
        });
    }

    // Documents management
    $(document).on('click', '.docs-btn', function() {
        const id = $(this).data('id');
        loadDocuments(id);
    });

    window.loadDocuments = function(id) {
        $.get('{{ url("equipment") }}/' + id + '/documents', function(data) {
            $('#offcanvas-docs-body').html(data);
            $('#offcanvas_docs').offcanvas('show');
        });
    }
});

function refreshTable() {
    $('#equipment-table').DataTable().ajax.reload();
}
</script>

<!-- Dropzone JS & CSS -->
<link rel="stylesheet" href="{{URL::asset('build/plugins/dropzone/dropzone.css')}}">
<script src="{{URL::asset('build/plugins/dropzone/dropzone-min.js')}}"></script>

<!-- Fancybox JS & CSS -->
<link rel="stylesheet" href="{{URL::asset('build/plugins/fancybox/jquery.fancybox.min.css')}}">
<script src="{{URL::asset('build/plugins/fancybox/jquery.fancybox.min.js')}}"></script>

<style>
.dropzone {
    border: 2px dashed #e5e5e5;
    border-radius: 5px;
    background: #f9f9f9;
    min-height: 150px;
}
.dropzone .dz-message {
    margin: 2em 0;
}
</style>
@endpush
