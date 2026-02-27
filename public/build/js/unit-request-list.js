/**
 * Unit Request Module JavaScript
 * Handles DataTables, Unit selection, and Workflow actions
 */

$(document).ready(function () {
    const tableElement = $('#unit-request-list');
    let dataTable;

    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    if (tableElement.length > 0) {
        dataTable = tableElement.DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: route('unit-request.datatables'),
                data: function (d) {
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'request_no' },
                { data: 'project' },
                { data: 'total_units' },
                { data: 'requested_by' },
                { data: 'created_at' },
                { data: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[4, 'desc']], // Sort by Created At
            language: {
                search: "",
                searchPlaceholder: "Search Request...",
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

        // Initialize Project Search (Select2 Remote)
        $('#add-project-id').select2({
            dropdownParent: $('#offcanvas_add'),
            placeholder: 'Search for a project...',
            minimumInputLength: 2,
            ajax: {
                url: route('unit-request.project-search'),
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        term: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });
    }

    // Add Item Row Logic
    let itemIndex = 1;
    $('#add-item-row').on('click', function () {
        const rowCount = $('.item-row').length;
        const newRow = `
            <div class="row item-row mb-2">
                <div class="col-10">
                    <select name="items[${itemIndex}][equipment_id]" class="form-select select2" data-placeholder="Choose unit..." required>
                        ${$('#items-container .item-row:first-child select').html()}
                    </select>
                </div>
                <div class="col-2 d-flex align-items-end">
                    <button type="button" class="btn btn-icon btn-outline-danger remove-item-row">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#items-container').append(newRow);

        // Initialize Select2 for the new row
        $('#items-container .item-row:last-child .select2').select2({
            dropdownParent: $('#offcanvas_add'),
            placeholder: 'Choose unit...'
        });

        itemIndex++;
        updateRemoveButtonState();
    });

    $(document).on('click', '.remove-item-row', function () {
        $(this).closest('.item-row').remove();
        updateRemoveButtonState();
    });

    // Sync Unit Requests from Project Server
    $('#btn-sync-ur').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="ti ti-refresh rotate me-1"></i>Syncing...');

        $.ajax({
            url: route('unit-request.sync'),
            method: 'POST',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Synced',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    dataTable.ajax.reload();

                    const countEl = $('#ur-count');
                    if (countEl.length && response.count > 0) {
                        countEl.text(parseInt(countEl.text()) + response.count);
                    }
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error during sync';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i>Sync New Unit Request');
            }
        });
    });

    // View Unit Request Details
    $(document).on('click', '.view-btn', function () {
        const uid = $(this).data('uid');
        if (!uid) return;

        $.ajax({
            url: route('unit-request.show', uid),
            method: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    let itemsHtml = '';

                    if (data.items && data.items.length > 0) {
                        data.items.forEach((item, index) => {
                            const unitName = item.equipment ? (item.equipment.unit_name || item.equipment.unit_code) : (item.unit_name || 'Pending Assignment');
                            itemsHtml += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${unitName}</td>
                                    <td><span class="badge bg-light text-dark">${item.status}</span></td>
                                </tr>
                            `;
                        });
                    } else {
                        itemsHtml = '<tr><td colspan="3" class="text-center">No items found.</td></tr>';
                    }

                    Swal.fire({
                        title: `Request: ${data.request_no}`,
                        html: `
                            <div class="text-start">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <p class="mb-1 small text-muted">Project</p>
                                        <p class="fw-bold">${data.project_name || 'Local Project'}</p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="mb-1 small text-muted">Status</p>
                                        <p>${data.status}</p>
                                    </div>
                                </div>
                                <p class="mb-1 small text-muted">Requested Items</p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th style="width: 50px">#</th>
                                                <th>Unit Description</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${itemsHtml}
                                        </tbody>
                                    </table>
                                </div>
                                ${data.remarks ? `<p class="mt-2 mb-1 small text-muted">Remarks</p><p class="small bg-light p-2 rounded">${data.remarks}</p>` : ''}
                            </div>
                        `,
                        width: 600,
                        confirmButtonText: 'Close',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                }
            },
            error: function () {
                Swal.fire('Error', 'Failed to fetch request details.', 'error');
            }
        });
    });

    function updateRemoveButtonState() {
        const rows = $('.item-row');
        if (rows.length <= 1) {
            rows.find('.remove-item-row').prop('disabled', true);
        } else {
            rows.find('.remove-item-row').prop('disabled', false);
        }
    }

    // Store Unit Request
    $('#add-ur-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-save-ur');

        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: route('unit-request.store'),
            method: 'POST',
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_add')).hide();
                    dataTable.ajax.reload();

                    const countEl = $('#ur-count');
                    if (countEl.length) {
                        countEl.text(parseInt(countEl.text()) + 1);
                    }
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error occurred';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Create Request');
            }
        });
    });

    // Reset form when offcanvas is hidden
    $('#offcanvas_add').on('hidden.bs.offcanvas', function () {
        $('#add-ur-form')[0].reset();
        $('#items-container').html(`
            <div class="row item-row mb-2">
                <div class="col-10">
                    <label class="small text-muted">Select unit from Asset Master</label>
                    <select name="items[0][equipment_id]" class="form-select select2" data-placeholder="Choose unit..." required>
                        ${$('#items-container .item-row:first-child select').html()}
                    </select>
                </div>
                <div class="col-2 d-flex align-items-end">
                    <button type="button" class="btn btn-icon btn-outline-danger remove-item-row" disabled>
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `);
        $('.select2').select2({
            dropdownParent: $('#offcanvas_add')
        });
        itemIndex = 1;
    });

    // Helper function for routes
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'unit-request.datatables') return `${base}/unit-request/datatables`;
        if (name === 'unit-request.project-search') return `${base}/unit-request/project-search`;
        if (name === 'unit-request.store') return `${base}/unit-request`;
        if (name === 'unit-request.show') return `${base}/unit-request/${params}`;
        if (name === 'unit-request.sync') return `${base}/unit-request/sync`;
        return '#';
    }

    // Initial Select2
    $('.select2').select2({
        dropdownParent: $('#offcanvas_add'),
        placeholder: 'Choose unit...'
    });
});
