<?php $page = 'part-requirements.index'; ?>
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
                    <h4 class="mb-1">Part Requirements<span class="badge badge-soft-primary ms-2">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Part Requirements</li>
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
                    <a href="{{ route('part-requirements.create') }}" class="btn btn-primary"><i class="ti ti-square-rounded-plus-filled me-1"></i>Add New</a>
                </div>
                <div class="card-body">

                    <!-- List -->
                    <div class="table-responsive custom-table">
                        <table class="table table-nowrap" id="part-requirements-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="no-sort">
                                        <div class="form-check form-check-md">
                                            <input class="form-check-input" type="checkbox" id="select-all">
                                        </div>
                                    </th>
                                    <th>No</th>
                                    <th>Equipment</th>
                                    <th>Service Period</th>
                                    <th>Parts</th>
                                    <th>Description</th>
                                    <th>Status</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // DataTable initialization
    let table = $('#part-requirements-table').DataTable({
        ajax: {
            url: '{{ route("part-requirements.datatables") }}',
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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'equipment_name' },
            { data: 'service_type' },
            { 
                data: 'parts',
                name: 'parts',
                render: function(data, type, row) {
                    if (!data) return '-';
                    // Optional: truncate if too long or allow wrap
                    return '<div class="text-wrap" style="min-width: 200px;">' + data + '</div>';
                }
            },
            { 
                data: 'description',
                render: function(data, type, row) {
                    return '<div class="text-wrap" style="min-width: 200px; white-space: normal;">' + (data ? data : '-') + '</div>';
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let color = data === 'Active' ? 'success' : 'danger';
                    return `<span class="badge badge-soft-${color}">${data}</span>`;
                }
            },
            { data: 'action', orderable: false, searchable: false }
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
                    url: '{{ url("part-requirements") }}/' + id,
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

    // Edit button click - Redirect to edit page
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        window.location.href = '{{ url("part-requirements") }}/' + id + '/edit';
    });

    function updateCount() {
        $.get('{{ route("part-requirements.index") }}', function(data) {
            const newCount = $(data).find('.badge-soft-primary').text();
            $('.badge-soft-primary').text(newCount);
        });
    }
});

function refreshTable() {
    $('#part-requirements-table').DataTable().ajax.reload();
}
</script>
@endpush
