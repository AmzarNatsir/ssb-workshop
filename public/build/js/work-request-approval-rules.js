/**
 * Work Request Approval Rules Management
 * Handles DataTables, Offcanvas AJAX, and CRUD for approval rules
 */

$(document).ready(function () {
    const tableElement = $('#rule-list');
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
            ajax: route('approval-matrix.work-request.datatables'),
            columns: [
                { data: 'category' },
                { data: 'wr_type' },
                { data: 'role_name' },
                { data: 'step_order', className: 'text-center' },
                { data: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search rules...",
                lengthMenu: "_MENU_",
                paginate: {
                    next: 'Next <i class="ti ti-chevron-right ms-1"></i>',
                    previous: '<i class="ti ti-chevron-left me-1"></i> Previous'
                }
            }
        });
    }

    // Initialize Select2 in offcanvas
    $('#offcanvas_rule').on('shown.bs.offcanvas', function () {
        $('.select2').select2({
            dropdownParent: $('#offcanvas_rule')
        });
    });

    // Reset Form when offcanvas is hidden
    $('#offcanvas_rule').on('hidden.bs.offcanvas', function () {
        $('#rule-form')[0].reset();
        $('#rule-id').val('');
        $('#offcanvas_rule_title').text('Add Approval Rule');
        $('#btn-save-rule').text('Save Rule');
        $('select[name="role_id"]').val('').trigger('change');
    });

    // Form Submission (Add/Update)
    $('#rule-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-save-rule');
        const id = $('#rule-id').val();

        const url = id ? route('approval-matrix.work-request.update', id) : route('approval-matrix.work-request.store');
        const method = id ? 'PUT' : 'POST';

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_rule')).hide();
                    dataTable.ajax.reload();
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error occurred';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Save Rule');
            }
        });
    });

    // Edit Rule
    $(document).on('click', '.edit-rule-btn', function () {
        const id = $(this).data('id');
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_rule'));

        $('#offcanvas_rule_title').text('Edit Approval Rule');
        $('#btn-save-rule').text('Update Rule');

        $.get(route('approval-matrix.work-request.edit', id), function (response) {
            if (response.success) {
                const data = response.data;
                $('#rule-id').val(data.id);
                $('select[name="category"]').val(data.category);
                $('select[name="wr_type"]').val(data.wr_type);
                $('select[name="role_id"]').val(data.role_id).trigger('change');
                $('input[name="step_order"]').val(data.step_order);

                offcanvas.show();
            }
        });
    });

    // Delete Rule
    $(document).on('click', '.delete-rule-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Delete Rule?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('approval-matrix.work-request.destroy', id),
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

    // Route Helper
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'approval-matrix.work-request.datatables') return `${base}/approval-matrix/work-request/datatables`;
        if (name === 'approval-matrix.work-request.store') return `${base}/approval-matrix/work-request`;
        if (name === 'approval-matrix.work-request.edit') return `${base}/approval-matrix/work-request/${params}/edit`;
        if (name === 'approval-matrix.work-request.update') return `${base}/approval-matrix/work-request/${params}`;
        if (name === 'approval-matrix.work-request.destroy') return `${base}/approval-matrix/work-request/${params}`;
        return '#';
    }
});
