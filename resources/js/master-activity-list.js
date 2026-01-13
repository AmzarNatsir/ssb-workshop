$(document).ready(function () {
    "use strict";

    // 1. Initialize DataTable
    const table = $('#activity-table').DataTable({
        processing: true,
        serverSide: false,
        ajax: route('master-activities.datatables'),
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'description', name: 'description' },
            { data: 'category', name: 'category' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        }
    });

    // 2. Add Button
    $('#btn-add').on('click', function () {
        $('#activity-form')[0].reset();
        $('#activity-id').val('');
        $('#offcanvas-title').text('Add Activity');
        $('#activity-code').prop('readonly', false);

        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_activity'));
        offcanvas.show();
    });

    // 3. Edit Button
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');

        $.get(route('master-activities.show', id), function (data) {
            $('#activity-id').val(data.id);
            $('#activity-code').val(data.code).prop('readonly', true); // Code should be unique/immutable often
            $('#activity-description').val(data.description);
            $('#activity-category').val(data.category);

            $('#offcanvas-title').text('Edit Activity');
            const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_activity'));
            offcanvas.show();
        });
    });

    // 4. Save (Create/Update)
    $('#activity-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#activity-id').val();
        const url = id ? route('master-activities.update', id) : route('master-activities.store');
        const method = id ? 'PUT' : 'POST';
        const btn = $('#btn-save');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_activity')).hide();
                    table.ajax.reload();
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Error saving data.';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Save changes');
            }
        });
    });

    // 5. Delete
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('master-activities.destroy', id),
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Deleted!', res.message, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', 'Failed to delete.', 'error');
                    }
                });
            }
        });
    });

    // Helper for route generation
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'master-activities.datatables') return `${base}/master-activities/datatables`;
        if (name === 'master-activities.store') return `${base}/master-activities`;
        if (name === 'master-activities.show') return `${base}/master-activities/${params}`;
        if (name === 'master-activities.update') return `${base}/master-activities/${params}`;
        if (name === 'master-activities.destroy') return `${base}/master-activities/${params}`;
        return '#';
    }
});
