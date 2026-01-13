$(document).ready(function () {
    "use strict";

    const table = $('#work-order-list').DataTable({
        processing: true,
        serverSide: false, // Using client-side for now for simplicity in mapping
        ajax: {
            url: route('work-order.datatables'),
            data: function (d) {
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            { data: 'work_order_no', name: 'work_order_no' },
            { data: 'wo_type', name: 'wo_type' },
            { data: 'equipment', name: 'equipment' },
            { data: 'assigned_to', name: 'assigned_to' },
            { data: 'priority', name: 'priority' },
            { data: 'age', name: 'age' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "_MENU_",
        }
    });

    // Handle Status Filter
    $('#filter-status').on('change', function () {
        table.ajax.reload();
    });

    // Custom Search
    $('#search-input').on('keyup', function () {
        table.search(this.value).draw();
    });

    // 1. Planning Offcanvas
    $(document).on('click', '.edit-planning-btn', function () {
        const id = $(this).data('id');
        $('#planning-wo-id').val(id);
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_planning'));
        offcanvas.show();

        // Load existing data
        $.ajax({
            url: route('work-order.show', id),
            method: 'GET',
            success: function (res) {
                if (res.success) {
                    const data = res.data;
                    const form = $('#planning-form');

                    form.find('[name="wo_type"]').val(data.wo_type).trigger('change');
                    form.find('[name="service_category"]').val(data.service_category);
                    form.find('[name="maintenance_type"]').val(data.maintenance_type).trigger('change');
                    form.find('[name="priority"]').val(data.priority);
                    form.find('[name="assigned_to"]').val(data.assigned_to).trigger('change'); // If select2 is used
                    form.find('[name="description"]').val(data.description);

                    // Work Date
                    if (data.work_date) {
                        // Ensure format YYYY-MM-DD
                        const wd = new Date(data.work_date);
                        const dateStr = wd.toISOString().split('T')[0];
                        form.find('[name="work_date"]').val(dateStr);
                    } else {
                        form.find('[name="work_date"]').val('');
                    }

                    // Calculate Age (Today - Created At)
                    if (data.created_at) {
                        const created = new Date(data.created_at);
                        const today = new Date();
                        const diffTime = Math.abs(today - created);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        $('#planning-wo-age').val(diffDays + ' Days');
                    } else {
                        $('#planning-wo-age').val('-');
                    }
                }
            },
            error: function (xhr) {
                console.error("Failed to load planning data", xhr);
            }
        });
    });

    $('#planning-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#planning-wo-id').val();
        const btn = $('#btn-save-planning');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Releasing...');

        $.ajax({
            url: route('work-order.update', id),
            method: 'PUT',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_planning')).hide();
                    table.ajax.reload();
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Error updating planning.';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Release Work Order');
            }
        });
    });

    // 2. Spare Part Request
    $(document).on('click', '.req-part-btn', function () {
        const id = $(this).data('id');
        $('#part-wo-id').val(id);
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_spare_part'));
        offcanvas.show();
    });

    $('#spare-part-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#part-wo-id').val();
        const btn = $('#btn-save-part');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Submitting...');

        $.ajax({
            url: route('work-order.spare-part-request', id),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire('Success', res.message, 'success');
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_spare_part')).hide();
                    $('#spare-part-form')[0].reset();
                }
            },
            error: function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error requesting part.', 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text('Submit Request');
            }
        });
    });

    // 3. Mechanic Activity
    $(document).on('click', '.log-activity-btn', function () {
        const id = $(this).data('id');
        $('#activity-wo-id').val(id);
        $('#activity-form')[0].reset();

        // precise current time for value setting ex: 2026-01-02T15:04
        const now = new Date();
        const offset = now.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(now - offset)).toISOString().slice(0, 16);
        $('input[name="start_time"]').val(localISOTime);
        $('input[name="end_time"]').val(localISOTime);

        new bootstrap.Offcanvas(document.getElementById('offcanvas_activity')).show();
    });

    $('#activity-form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#activity-wo-id').val();
        const btn = $('#btn-save-activity');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Validating with AI...');

        $.ajax({
            url: route('work-order.log-activity', id),
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: res.message, // Contains AI Recommendation
                        icon: 'success'
                    });
                    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_activity')).hide();
                    $('#activity-form')[0].reset();
                    table.ajax.reload();

                    // Refresh detail if open
                    const openId = $('#view-wo-title').data('id');
                    if (openId && openId == id) {
                        $('.view-wo-btn[data-id="' + openId + '"]').trigger('click');
                    }
                }
            },
            error: function (xhr) {
                let msg = 'Error logging activity.';
                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message || msg;
                }
                Swal.fire({
                    title: xhr.status === 422 ? 'Revision Required' : 'Error',
                    text: msg,
                    icon: xhr.status === 422 ? 'warning' : 'error'
                });
            },
            complete: function () {
                btn.prop('disabled', false).text('Log Activity');
            }
        });
    });

    // 4. Close Work Order
    $(document).on('click', '.close-wo-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Close Work Order?',
            text: "This will finalize the work order and lock it from further changes.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Validate & Close'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: route('work-order.close', id),
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Closed!', res.message, 'success');
                            table.ajax.reload();
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error closing work order.', 'error');
                    }
                });
            }
        });
    });

    // 5. View Details
    $(document).on('click', '.view-wo-btn', function () {
        const id = $(this).data('id');
        const offcanvasEl = document.getElementById('offcanvas_view_detail');
        const offcanvas = new bootstrap.Offcanvas(offcanvasEl);

        // Store ID for refresh
        $('#view-wo-title').data('id', id);

        // Reset content
        $('#view-wo-no').text('Loading...');
        $('#view-wo-status').text('');
        $('#view-wo-parts-list').html('<tr><td colspan="3" class="text-center text-muted">Loading parts...</td></tr>');
        $('#view-wo-activities-list').html('<p class="text-muted text-center">Loading activities...</p>');

        // Reset Standard Req
        $('#view-wo-standard-req-container').hide();
        $('#view-wo-standard-req-list').empty();

        offcanvas.show();

        $.ajax({
            url: route('work-order.show', id), // Uses generic resource route or implicit
            method: 'GET',
            success: function (res) {
                if (res.success) {
                    const data = res.data;

                    // Header
                    $('#view-wo-no').text(data.work_order_no);
                    $('#view-wo-status').text(data.status)
                        .removeClass().addClass('badge ' + getStatusClass(data.status));
                    $('#view-wo-priority').text(data.priority)
                        .removeClass().addClass('fw-bold ' + getPriorityClass(data.priority));

                    // Details
                    $('#view-wo-type').text(data.wo_type || '-');
                    $('#view-wo-equipment').text(data.equipment ? `${data.equipment.code} - ${data.equipment.name}` : '-');
                    $('#view-wo-assignee').text(data.assignee ? data.assignee.name : '-');
                    $('#view-wo-creator').text(data.creator ? data.creator.name : '-');
                    $('#view-wo-category').text(data.service_category || '-');
                    $('#view-wo-maint-type').text(data.maintenance_type || '-');
                    $('#view-wo-maint-type').text(data.maintenance_type || '-');
                    $('#view-wo-description').text(data.description || '-');

                    // Standard Part Requirements
                    if (data.part_requirement && data.part_requirement.details && data.part_requirement.details.length > 0) {
                        let stdHtml = '';
                        data.part_requirement.details.forEach(detail => {
                            const partName = detail.part ? detail.part.name : 'Unknown Part';
                            stdHtml += `
                                <tr>
                                    <td>${partName}</td>
                                    <td>${detail.quantity}</td>
                                    <td>
                                        <button class="btn btn-xs btn-outline-primary req-standard-part-btn" 
                                            data-name="${partName}" 
                                            data-qty="${detail.quantity}" 
                                            title="Request this part">
                                            <i class="ti ti-plus"></i> Request
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#view-wo-standard-req-list').html(stdHtml);
                        $('#view-wo-standard-req-count').text(data.part_requirement.details.length);
                        $('#view-wo-standard-req-container').show();
                    } else {
                        $('#view-wo-standard-req-container').hide();
                    }

                    // Spare Parts
                    let partsHtml = '';
                    if (data.spare_parts && data.spare_parts.length > 0) {
                        data.spare_parts.forEach(part => {
                            let actions = '';

                            // 1. Admin Validation (PENDING -> VALIDATED)
                            if (part.status === 'PENDING') {
                                actions += `<button class="btn btn-xs btn-outline-primary btn-sp-validate" data-id="${part.id}" title="Validate"><i class="ti ti-check"></i></button>`;
                            }
                            // 2. Manager Approval (VALIDATED -> APPROVED)
                            if (part.status === 'VALIDATED') {
                                actions += `<button class="btn btn-xs btn-outline-success btn-sp-approve" data-id="${part.id}" title="Approve"><i class="ti ti-thumb-up"></i></button>`;
                            }
                            // 3. Issue Part (APPROVED -> ISSUED)
                            if (part.status === 'APPROVED') {
                                actions += `<button class="btn btn-xs btn-outline-info btn-sp-issue" data-id="${part.id}" title="Issue Part"><i class="ti ti-box"></i></button>`;
                            }
                            // 4. Return Part (ISSUED -> ISSUED with Return Pending)
                            if (part.status === 'ISSUED') {
                                actions += `<button class="btn btn-xs btn-outline-warning btn-sp-return" data-id="${part.id}" data-max="${part.qty_issued}" title="Return Part"><i class="ti ti-arrow-back-up"></i></button>`;
                            }
                            // Return Workflow
                            if (part.return_status === 'PENDING') {
                                actions += `<button class="btn btn-xs btn-outline-primary btn-sp-validate-return" data-id="${part.id}" title="Validate Return"><i class="ti ti-check"></i></button>`;
                            }
                            if (part.return_status === 'VALIDATED') {
                                actions += `<button class="btn btn-xs btn-outline-success btn-sp-approve-return" data-id="${part.id}" title="Approve Return"><i class="ti ti-thumb-up"></i></button>`;
                            }

                            // Status Display
                            let statusBadge = `<span class="badge ${getPartStatusClass(part.status)}">${part.status}</span>`;
                            if (part.return_status !== 'NONE') {
                                statusBadge += `<br><small class="text-muted">Return: ${part.return_status}</small>`;
                            }

                            partsHtml += `
                                <tr>
                                    <td>${part.part_name}</td>
                                    <td>${part.qty_requested}</td>
                                    <td>${statusBadge}</td>
                                    <td>${actions}</td>
                                </tr>
                            `;
                        });
                        $('#view-wo-parts-count').text(data.spare_parts.length);
                    } else {
                        partsHtml = '<tr><td colspan="4" class="text-center text-muted">No spare parts requested</td></tr>';
                        $('#view-wo-parts-count').text('0');
                    }
                    $('#view-wo-parts-list').html(partsHtml);

                    // Activities
                    let activitiesHtml = '';
                    if (data.activities && data.activities.length > 0) {
                        data.activities.forEach(act => {
                            const mechanicName = act.mechanic ? act.mechanic.name : 'Unknown';
                            const statusBadge = act.status === 'READY' ? '<span class="badge bg-success">READY</span>' : '<span class="badge bg-warning">IN PROGRESS</span>';
                            const aiBadge = act.activity_summary ? '<span class="badge bg-soft-info text-info me-1"><i class="ti ti-robot"></i> AI Validated</span>' : '';
                            const description = act.activity_summary || act.description;

                            activitiesHtml += `
                                <div class="d-flex gap-3 mb-3 border-bottom pb-2">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm bg-light text-primary rounded-circle">
                                            <i class="ti ti-user"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="mb-0 fs-13">${mechanicName}</h6>
                                            <small class="text-muted">${formatDate(act.created_at)}</small>
                                        </div>
                                        <div class="mb-1">
                                            ${statusBadge} ${aiBadge}
                                        </div>
                                        <p class="text-muted mb-1 small">${description}</p>
                                        ${act.working_duration ? '<small class="text-muted d-block"><i class="ti ti-clock"></i> Duration: ' + act.working_duration + ' Hours</small>' : ''}
                                    </div>
                                </div>
                            `;
                        });
                        $('#view-wo-activities-count').text(data.activities.length);
                    } else {
                        activitiesHtml = '<p class="text-center text-muted">No activities logged yet.</p>';
                        $('#view-wo-activities-count').text('0');
                    }
                    $('#view-wo-activities-list').html(activitiesHtml);
                }
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire('Error', 'Failed to load details.', 'error');
            }
        });
    });

    // Helpers
    function getStatusClass(status) {
        switch (status) {
            case 'DRAFT': return 'bg-secondary';
            case 'OPEN': return 'bg-primary';
            case 'IN_PROGRESS': return 'bg-info';
            case 'READY': return 'bg-warning';
            case 'CLOSED': return 'bg-success';
            default: return 'bg-light';
        }
    }

    function getPriorityClass(p) {
        if (p === 'HIGH') return 'text-danger';
        if (p === 'MEDIUM') return 'text-warning';
        return 'text-success';
    }

    function getPartStatusClass(s) {
        if (s === 'APPROVED') return 'bg-success';
        if (s === 'PENDING') return 'bg-warning';
        if (s === 'ISSUED') return 'bg-info';
        return 'bg-secondary';
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    // 6. Spare Part Workflow Actions
    // Request Standard Part (Prefill form)
    $(document).on('click', '.req-standard-part-btn', function () {
        const id = $('#view-wo-title').data('id');
        const name = $(this).data('name');
        const qty = $(this).data('qty');

        $('#part-wo-id').val(id);
        $('#spare-part-form [name="part_name"]').val(name);
        $('#spare-part-form [name="qty"]').val(qty);

        // Hide detail offcanvas temporarily or keep open? 
        // Bootstrap offcanvas doesn't support stacked offcanvas well by default without custom z-index.
        // Let's close detail and open request.
        // const detailOffcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_view_detail'));
        // detailOffcanvas.hide();

        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_spare_part'));
        offcanvas.show();
    });

    // Validate Request
    $(document).on('click', '.btn-sp-validate', function () {
        actionSparePart($(this).data('id'), 'validate', 'Validate this request?');
    });

    // Approve Request
    $(document).on('click', '.btn-sp-approve', function () {
        actionSparePart($(this).data('id'), 'approve', 'Approve this request?');
    });

    // Issue Part
    $(document).on('click', '.btn-sp-issue', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Issue Spare Part',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label required">Quantity Issued</label>
                    <input type="number" id="swal-qty" class="form-control" placeholder="Enter quantity">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label required">Picking Mechanic</label>
                    <input type="text" id="swal-picker" class="form-control" placeholder="Mechanic Name">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Issue',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const qty = document.getElementById('swal-qty').value;
                const picker = document.getElementById('swal-picker').value;

                if (!qty || !picker) {
                    Swal.showValidationMessage('Please fill all fields');
                    return false;
                }

                return $.ajax({
                    url: route('spare-part.issue', id),
                    method: 'POST',
                    data: {
                        qty_issued: qty,
                        picking_mechanic: picker,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Issued!', 'Part has been issued.', 'success');
                const openedId = $('#view-wo-title').data('id');
                if (openedId) $('.view-wo-btn[data-id="' + openedId + '"]').trigger('click');
            }
        });
    });

    // Return Part
    $(document).on('click', '.btn-sp-return', function () {
        const id = $(this).data('id');
        const max = $(this).data('max');
        Swal.fire({
            title: 'Return Spare Part',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label required">Quantity Returned (Max: ${max})</label>
                    <input type="number" id="swal-qty" class="form-control" max="${max}" placeholder="Enter quantity">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label required">Reason</label>
                    <textarea id="swal-reason" class="form-control" rows="2" placeholder="Why returning?"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Return',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const qty = document.getElementById('swal-qty').value;
                const reason = document.getElementById('swal-reason').value;

                if (!qty || !reason) {
                    Swal.showValidationMessage('Please fill all fields');
                    return false;
                }

                return $.ajax({
                    url: route('spare-part.return', id),
                    method: 'POST',
                    data: {
                        qty_returned: qty,
                        return_reason: reason,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Submitted!', 'Return request submitted.', 'success');
                const openedId = $('#view-wo-title').data('id');
                if (openedId) $('.view-wo-btn[data-id="' + openedId + '"]').trigger('click');
            }
        });
    });

    // Validate Return
    $(document).on('click', '.btn-sp-validate-return', function () {
        actionSparePart($(this).data('id'), 'validate-return', 'Validate this return?');
    });

    // Approve Return
    $(document).on('click', '.btn-sp-approve-return', function () {
        actionSparePart($(this).data('id'), 'approve-return', 'Approve this return?');
    });

    function actionSparePart(id, action, title) {
        Swal.fire({
            title: title,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                let url;
                if (action === 'validate') url = route('spare-part.validate', id);
                if (action === 'approve') url = route('spare-part.approve', id);
                if (action === 'validate-return') url = route('spare-part.validate-return', id);
                if (action === 'approve-return') url = route('spare-part.approve-return', id);

                return $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') }
                }).then(response => {
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error.responseJSON.message}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Success!', result.value.message, 'success');
                const openedId = $('#view-wo-title').data('id');
                if (openedId) $('.view-wo-btn[data-id="' + openedId + '"]').trigger('click');
            }
        });
    }

    // Helper: Route function (ensure it includes work-order routes)
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'work-order.datatables') return `${base}/work-order/datatables`;
        if (name === 'work-order.update') return `${base}/work-order/${params}`;
        if (name === 'work-order.spare-part-request') return `${base}/work-order/${params}/spare-part`;
        if (name === 'work-order.log-activity') return `${base}/work-order/${params}/activity`;
        if (name === 'work-order.close') return `${base}/work-order/${params}/close`;
        if (name === 'work-order.show') return `${base}/work-order/${params}`;

        // Spare Part Workflow Routes
        if (name === 'spare-part.validate') return `${base}/work-order/spare-part/${params}/validate`;
        if (name === 'spare-part.approve') return `${base}/work-order/spare-part/${params}/approve`;
        if (name === 'spare-part.issue') return `${base}/work-order/spare-part/${params}/issue`;
        if (name === 'spare-part.return') return `${base}/work-order/spare-part/${params}/return`;
        if (name === 'spare-part.validate-return') return `${base}/work-order/spare-part/${params}/validate-return`;
        if (name === 'spare-part.approve-return') return `${base}/work-order/spare-part/${params}/approve-return`;

        return '#';
    }
});
