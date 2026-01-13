<?php $page = 'tools.index'; ?>
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
                    <h4 class="mb-1">Tools<span class="badge badge-soft-primary ms-2">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Tools</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="javascript:void(0);" class="btn btn-icon btn-outline-light shadow" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Refresh" data-bs-original-title="Refresh" onclick="refreshTable()"><i class="ti ti-refresh"></i></a>
                </div>
            </div>
            <!-- End Page Header -->

            <!-- card start -->
            <div class="card border-0 rounded-0">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="input-icon input-icon-start position-relative">
                        <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="search-input" placeholder="Search">
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="javascript:void(0);" class="btn btn-primary btn-add" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add New</a>
                        <a href="{{ route('tools.monitoring') }}" class="btn btn-info"><i class="ti ti-activity me-1"></i>Monitoring</a>
                        <button class="btn btn-warning" id="print-ids-btn"><i class="ti ti-printer me-1"></i>Print Tool IDs</button>
                    </div>
                    <form id="print-form" action="{{ route('tools.print-labels') }}" method="POST" target="_blank" style="display: none;">
                        @csrf
                        <input type="hidden" name="ids" id="print-ids-input">
                    </form>
                </div>
                <div class="card-body">

                    <!-- List -->
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="tools-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th class="no-sort">Image</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Tool Type</th>
                                    <th>Status</th>
                                    <th>Rack</th>
                                    <th>Quantity</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <!-- /List -->

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
    
    <!-- Add Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add New Tool</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-add-body"></div>
    </div>
    <!-- /Add Offcanvas -->

    <!-- Edit Offcanvas -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Edit Tool</h5>
            <button type="button"
                class="btn-close custom-btn-close border p-1 me-0 d-flex align-items-center justify-content-center rounded-circle"
                data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="offcanvas-edit-body"></div>
    </div>
    <!-- /Edit Offcanvas -->

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable initialization
    let table = $('#tools-table').DataTable({
        ajax: {
            url: '{{ route("tools.datatables") }}',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return '<div class="form-check form-check-md"><input class="form-check-input row-checkbox" type="checkbox" value="' + row.id + '"></div>';
                }
            },
            {
                data: 'image_url',
                render: function(data, type, row) {
                    if (data) {
                        return '<img src="' + data + '" class="avatar avatar-md rounded-circle" alt="Tool Image">';
                    } else {
                        return '<span class="avatar avatar-md rounded-circle bg-light text-primary"><i class="ti ti-tool"></i></span>';
                    }
                }
            },
            { data: 'code' },
            { data: 'name' },
            { data: 'tool_type' },
            { 
                data: 'status',
                render: function(data, type, row) {
                    if(data && data !== '-') {
                        let color = row.status_color || 'secondary';
                        return `<span class="badge badge-soft-${color}">${data}</span>`;
                    } else {
                        return '-';
                    }
                }
            },
            { data: 'rack' },
            { data: 'quantity' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center gap-2">
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-light edit-btn" data-id="${row.id}">
                                <i class="ti ti-edit"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-icon btn-danger delete-btn" data-id="${row.id}">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                    `;
                }
            }
        ],
        order: [[2, 'asc']],
        pageLength: 10,
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            lengthMenu: '_MENU_',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: {
                first: 'First',
                last: 'Last',
                next: '<i class="ti ti-chevron-right"></i>',
                previous: '<i class="ti ti-chevron-left"></i>'
            }
        },
        createdRow: function(row, data, dataIndex) {
            if (data.is_low_stock && data.min_quantity > 0) {
                $(row).addClass('table-danger');
                $('td:eq(7)', row).append(' <i class="ti ti-alert-triangle text-danger" title="Low Stock"></i>');
            }
        }
    });

    // Search functionality
    $('#search-input').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Select all checkbox
    $('#select-all').on('click', function() {
        $('.row-checkbox').prop('checked', this.checked);
    });

    // Load add form
    $('#offcanvas_add').on('show.bs.offcanvas', function() {
        $.get('{{ route("tools.create") }}', function(data) {
            $('#offcanvas-add-body').html(data);
        });
    });

    // Handle add form submission
    $(document).on('submit', '#add-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnHtml = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Creating...');
        
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
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessages
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Load edit form
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.get('{{ url("tools") }}/' + id + '/edit', function(data) {
            $('#offcanvas-edit-body').html(data);
            $('#offcanvas_edit').offcanvas('show');
        });
    });

    // Handle edit form submission
    $(document).on('submit', '#edit-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnHtml = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...');
        
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
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = Object.values(errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessages
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            },
            complete: function() {
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Delete functionality
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
                    url: '{{ url("tools") }}/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
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
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Generate ID for Print
    $('#print-ids-btn').on('click', function() {
        let selectedIds = [];
        $('.row-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select at least one tool to print.'
            });
            return;
        }

        $('#print-ids-input').val(selectedIds.join(','));
        $('#print-form').submit();
    });

    function updateCount() {
        $.get('{{ route("tools.index") }}', function(data) {
            const newCount = $(data).find('.badge-soft-primary').text();
            $('.badge-soft-primary').text(newCount);
        });
    }
});

function refreshTable() {
    $('#tools-table').DataTable().ajax.reload();
}
</script>
@endpush
