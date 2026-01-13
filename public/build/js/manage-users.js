/**
 * Manage Users Module
 * Handles DataTables, Offcanvas AJAX forms, and User CRUD
 */

$(document).ready(function () {
    const tableElement = $('#manage-users-list');
    let dataTable;

    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if (tableElement.length > 0) {
        dataTable = tableElement.DataTable({
            processing: true,
            serverSide: false,
            ajax: route('users.datatables'),
            columns: [
                {
                    data: 'id',
                    render: function (data) {
                        return `<div class="form-check form-check-md"><input class="form-check-input" type="checkbox" value="${data}"></div>`;
                    }
                },
                {
                    data: 'name',
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm rounded-circle bg-light text-dark me-2">
                                    ${data.charAt(0).toUpperCase()}
                                </span>
                                <h6 class="fw-medium mb-0">${data}</h6>
                            </div>
                        `;
                    }
                },
                { data: 'email' },
                { data: 'roles' },
                { data: 'created' },
                { data: 'last_activity' },
                {
                    data: 'status',
                    render: function (data) {
                        const badgeClass = data === 'Active' ? 'badge-soft-success' : 'badge-soft-danger';
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function (data) {
                        return `
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-icon btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item edit-user-btn" href="javascript:void(0);" data-id="${data}">
                                        <i class="ti ti-edit me-2"></i>Edit
                                    </a>
                                    <a class="dropdown-item delete-user-btn text-danger" href="javascript:void(0);" data-id="${data}">
                                        <i class="ti ti-trash me-2"></i>Delete
                                    </a>
                                </div>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search users...",
                lengthMenu: "_MENU_",
                paginate: {
                    next: 'Next <i class="ti ti-chevron-right ms-1"></i>',
                    previous: '<i class="ti ti-chevron-left me-1"></i> Previous'
                }
            },
            initComplete: function () {
                $('.dataTables_filter input').addClass('form-control form-control-md');
            }
        });
    }

    // Load Add Form
    $('[data-bs-target="#offcanvas_add"]').on('click', function () {
        $('#offcanvas_add_body').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
        $.get(route('users.create'), function (response) {
            $('#offcanvas_add_body').html(response);
        });
    });

    // Load Edit Form
    $(document).on('click', '.edit-user-btn', function () {
        const id = $(this).data('id');
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_edit'));

        $('#offcanvas_edit_body').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');
        offcanvas.show();

        $.get(route('users.edit', id), function (response) {
            $('#offcanvas_edit_body').html(response);
        });
    });

    // Handle Form Submissions (Add)
    $(document).on('submit', '#add-user-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-save-user');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_add')).hide();
                    dataTable.ajax.reload();
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error occurring';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Create User');
            }
        });
    });

    // Handle Form Submissions (Update)
    $(document).on('submit', '#edit-user-form', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-update-user');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST', // Laravel method spoofing handles the PUT
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_edit')).hide();
                    dataTable.ajax.reload();
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error occurring';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Update User');
            }
        });
    });

    // Delete User
    $(document).on('click', '.delete-user-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Delete User?',
            text: "This user will be permanently removed.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('users.destroy', id),
                    method: 'DELETE',
                    success: function (response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            dataTable.ajax.reload();
                        }
                    }
                });
            }
        });
    });

    // Toggle Password Visibility
    $(document).on('click', '.toggle-password', function () {
        const input = $(this).closest('.input-group').find('.pass-input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('ti-eye-off').addClass('ti-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('ti-eye').addClass('ti-eye-off');
        }
    });

    // Route Helper
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'users.datatables') return `${base}/users/datatables`;
        if (name === 'users.create') return `${base}/users/create`;
        if (name === 'users.edit') return `${base}/users/${params}/edit`;
        if (name === 'users.update') return `${base}/users/${params}`;
        if (name === 'users.destroy') return `${base}/users/${params}`;
        return '#';
    }
});
