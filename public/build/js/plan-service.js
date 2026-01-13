/**
 * Plan Service Module JavaScript
 * Handles DataTables, real-time calculations, and AJAX actions
 */

$(document).ready(function () {
    const tableElement = $('#plan-service-list');
    let dataTable;

    // CSRF Token Setup for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    if (tableElement.length > 0) {
        dataTable = tableElement.DataTable({
            processing: true,
            serverSide: false, // Set to true if using actual server-side pagination with DataTables
            ajax: {
                url: route('plan-service.datatables'),
                data: function (d) {
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                {
                    data: 'equipment_code',
                    render: function (data, type, row) {
                        return `<div><strong>${data}</strong><br><small class="text-muted">${row.equipment_name}</small></div>`;
                    }
                },
                { data: 'hm_actual' },
                { data: 'overdue' },
                { data: 'ps_berikutnya' },
                { data: 'service_type' },
                { data: 'plan_date' },
                { data: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[5, 'asc']], // Sort by Plan Date
            language: {
                search: "",
                searchPlaceholder: "Search Unit...",
                lengthMenu: "_MENU_",
                paginate: {
                    next: 'Next <i class="ti ti-chevron-right ms-1"></i>',
                    previous: '<i class="ti ti-chevron-left me-1"></i> Previous'
                }
            },
            initComplete: function () {
                $('.dataTables_filter').hide();
            }
        });

        // Search and Filter Handlers
        $('#search-input').on('keyup', function () {
            dataTable.search(this.value).draw();
        });

        $('#filter-status').on('change', function () {
            dataTable.ajax.reload();
        });
    }

    // Equipment Selection Logic (Add Form)
    $('#add-equipment-id').on('change', function () {
        const id = $(this).val();
        if (id) {
            $.get(route('plan-service.equipment-details', id), function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#info-wh').text(data.wh_per_project);
                    $('#info-service').text(data.service_period);
                    $('#equipment-info').slideDown();

                    if (data.has_active_plan) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Active Plan Exists',
                            text: 'This unit already has an active service plan. You must complete or cancel it before creating a new one.',
                        });
                        $('#btn-save-plan').prop('disabled', true);
                    } else {
                        $('#btn-save-plan').prop('disabled', false);
                    }

                    // Trigger calculation if HM values already present
                    calculatePlan('add');
                }
            });
        } else {
            $('#equipment-info').slideUp();
        }
    });

    // Real-time Calculation Logic
    $('#add-hm-ps, #add-hm-actual').on('input', function () {
        calculatePlan('add');
    });

    function calculatePlan(type) {
        const equipmentId = $(`#${type}-equipment-id`).val();
        const hmPs = $(`#${type}-hm-ps`).val();
        const hmActual = $(`#${type}-hm-actual`).val();

        if (equipmentId && hmPs && hmActual) {
            $.post(route('plan-service.calculate'), {
                _token: $('meta[name="csrf-token"]').attr('content'),
                equipment_id: equipmentId,
                hm_ps_sebelumnya: hmPs,
                hm_actual: hmActual
            }).done(function (response) {
                if (response.success) {
                    const data = response.data;
                    $('#preview-overdue').text(data.overdue).removeClass('text-danger text-success').addClass(data.overdue > 0 ? 'text-danger' : 'text-success');
                    $('#preview-next').text(data.ps_berikutnya);
                    $('#preview-date').text(data.plan_date);
                    $('#preview-type').text(data.service_type);
                    $('#calculation-preview').slideDown();
                }
            }).fail(function (xhr) {
                console.error('Calculation failed', xhr.responseJSON);
                $('#calculation-preview').slideUp();
            });
        } else {
            $('#calculation-preview').slideUp();
        }
    }

    // Store Plan
    $('#add-plan-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Creating...');

        $.ajax({
            url: route('plan-service.store'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success');
                    $('#offcanvas_add').offcanvas('hide');
                    form[0].reset();
                    $('#equipment-info, #calculation-preview').hide();
                    dataTable.ajax.reload();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Internal Server Error';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Create Plan');
            }
        });
    });

    // Edit Logic (AJAX Load)
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const container = $('#edit-form-container');

        container.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading plan details...</p></div>');
        $('#offcanvas_edit').offcanvas('show');

        $.get(`${window.location.origin}/plan-service/${id}/edit`, function (html) {
            container.html(html);
        }).fail(function () {
            container.html('<div class="alert alert-danger">Failed to load edit form. Please try again.</div>');
        });
    });

    // Complete Logic
    $(document).on('click', '.complete-btn', function () {
        const id = $(this).data('id');
        $('#complete-id').val(id);
        $('#modal_complete').modal('show');
    });

    $('#complete-plan-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#complete-id').val();
        const form = $(this);

        $.ajax({
            url: route('plan-service.complete', id),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Completed', response.message, 'success');
                    $('#modal_complete').modal('hide');
                    dataTable.ajax.reload();
                }
            }
        });
    });

    // Cancel Logic
    $(document).on('click', '.cancel-btn', function () {
        const id = $(this).data('id');
        $('#cancel-id').val(id);
        $('#modal_cancel').modal('show');
    });

    $('#cancel-plan-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#cancel-id').val();
        const form = $(this);

        $.ajax({
            url: route('plan-service.cancel', id),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire('Cancelled', response.message, 'success');
                    $('#modal_cancel').modal('hide');
                    dataTable.ajax.reload();
                }
            }
        });
    });

    // Create Work Order Logic
    $(document).on('click', '.create-wo-btn', function () {
        const id = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: 'Create Work Order?',
            text: "This will create a new Work Order based on this service plan schedule.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: route('work-order.store-from-plan', id),
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') }
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error.responseJSON ? error.responseJSON.message : 'Internal error'}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    title: 'Created!',
                    text: result.value.message,
                    icon: 'success'
                });
                dataTable.ajax.reload();
            }
        });
    });

    // Delete Logic
    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Plan?',
            text: "This record will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('plan-service.destroy', id),
                    method: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
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

    // Helper function for routes (Assuming Ziggy or similar is NOT available, but we can use relative paths or a helper)
    // Actually, I'll use simple string templates since I know the structure
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'plan-service.datatables') return `${base}/plan-service/datatables`;
        if (name === 'plan-service.store') return `${base}/plan-service`;
        if (name === 'plan-service.calculate') return `${base}/plan-service/calculate`;
        if (name === 'plan-service.equipment-details') return `${base}/plan-service/equipment/${params}`;
        if (name === 'plan-service.complete') return `${base}/plan-service/${params}/complete`;
        if (name === 'plan-service.cancel') return `${base}/plan-service/${params}/cancel`;
        if (name === 'plan-service.destroy') return `${base}/plan-service/${params}`;
        if (name === 'work-order.store-from-plan') return `${base}/work-order/from-plan/${params}`;
        return '#';
    }
});
