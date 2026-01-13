/**
 * Work Request Module JavaScript
 * Handles DataTables, Asset selection AJAX, and WR submission
 */

$(document).ready(function () {
    const tableElement = $('#work-request-list');
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
                url: route('work-request.datatables'),
                data: function (d) {
                    d.status = $('#filter-status').val();
                }
            },
            columns: [
                { data: 'wr_no' },
                { data: 'category' },
                { data: 'equipment' },
                { data: 'type' },
                { data: 'created_at' },
                { data: 'status' },
                { data: 'action', orderable: false, searchable: false }
            ],
            order: [[4, 'desc']], // Sort by Created At
            language: {
                search: "",
                searchPlaceholder: "Search WR...",
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

    // Equipment Selection Logic (Auto-fill Location)
    $('#add-equipment-id').on('change', function () {
        const id = $(this).val();
        if (id) {
            $.get(route('work-request.asset-details', id), function (response) {
                if (response.success) {
                    $('#asset-location').val(response.data.location);
                }
            });
        } else {
            $('#asset-location').val('');
        }
    });

    // Toggle Goods Items Section
    $('input[name="type"]').on('change', function () {
        if ($(this).val() === 'Goods Request') {
            $('#goods-items-section').slideDown();
            initPartSelect($('.part-select'));
        } else {
            $('#goods-items-section').slideUp();
        }
    });

    // Helper for number formatting (thousands separator)
    function formatNumber(num) {
        if (!num) return '0';
        return parseFloat(num).toLocaleString('en-US');
    }

    // Initialize Select2 for parts
    function initPartSelect(element) {
        element.select2({
            dropdownParent: $('#offcanvas_add'),
            placeholder: 'Search for a part...',
            minimumInputLength: 2,
            ajax: {
                url: route('work-request.parts-search'),
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
        }).on('select2:select', function (e) {
            const data = e.params.data;
            const container = $(this).closest('.item-row');

            // Duplicate Check
            let isDuplicate = false;
            $('.part-select').not(this).each(function () {
                if ($(this).val() === data.id) {
                    isDuplicate = true;
                    return false;
                }
            });

            if (isDuplicate) {
                Swal.fire('Warning', 'This part is already in the list.', 'warning');
                $(this).val(null).trigger('change');
                container.find('.part-price').val('');
                return;
            }

            container.find('.part-price').val(formatNumber(data.price));
            container.find('input[name*="unit"]').val(data.unit || 'Pcs');
        });
    }

    // Add Item Row Logic
    let itemIndex = 1;
    $('#add-item-row').on('click', function () {
        const newRowId = `part-select-${itemIndex}`;
        const newRow = `
            <div class="row item-row mb-2">
                <div class="col-4">
                    <select name="items[${itemIndex}][part_name]" class="form-select form-select-sm part-select" id="${newRowId}" data-placeholder="Search part...">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-2 px-1">
                    <input type="number" name="items[${itemIndex}][qty]" class="form-control form-control-sm" step="0.01" placeholder="Qty">
                </div>
                <div class="col-3 px-1">
                    <input type="text" name="items[${itemIndex}][price]" class="form-control form-control-sm part-price text-end" readonly placeholder="Price">
                </div>
                <div class="col-2 px-1">
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control form-control-sm" placeholder="Unit">
                </div>
                <div class="col-1 p-0">
                    <button type="button" class="btn btn-sm btn-icon btn-light text-danger remove-item-row">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#items-container').append(newRow);
        initPartSelect($(`#${newRowId}`));
        itemIndex++;
    });

    $(document).on('click', '.remove-item-row', function () {
        $(this).closest('.item-row').remove();
    });

    // Edit Work Request
    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('id');
        const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_add'));

        $('#offcanvas_add_title').text('Edit Work Request');
        $('#btn-save-wr').text('Update Request');

        $.get(route('work-request.edit', id), function (response) {
            if (response.success) {
                const data = response.data;
                $('#wr-id').val(data.id);
                $('select[name="category"]').val(data.category);
                $('#add-equipment-id').val(data.equipment_id).trigger('change');
                $('input[name="operator_name"]').val(data.operator_name);
                $('input[name="hm_km"]').val(data.hm_km);
                $('input[name="asset_condition"]').val(data.asset_condition);
                $('textarea[name="trouble_description"]').val(data.trouble_description);
                $(`input[name="type"][value="${data.type}"]`).prop('checked', true).trigger('change');

                if (data.type === 'Goods Request' && data.items.length > 0) {
                    $('#items-container').empty();
                    data.items.forEach((item, index) => {
                        const newRowId = `part-select-edit-${index}`;
                        const rowHtml = `
                            <div class="row item-row mb-2">
                                <div class="col-4">
                                    <select name="items[${index}][part_name]" class="form-select form-select-sm part-select" id="${newRowId}">
                                        <option value="${item.part_name}" selected>${item.part_name}</option>
                                    </select>
                                </div>
                                <div class="col-2 px-1">
                                    <input type="number" name="items[${index}][qty]" class="form-control form-control-sm" step="0.01" value="${item.qty}">
                                </div>
                                <div class="col-3 px-1">
                                    <input type="text" name="items[${index}][price]" class="form-control form-control-sm part-price text-end" readonly value="${formatNumber(item.price)}">
                                </div>
                                <div class="col-2 px-1">
                                    <input type="text" name="items[${index}][unit]" class="form-control form-control-sm" value="${item.unit}">
                                </div>
                                <div class="col-1 p-0">
                                    <button type="button" class="btn btn-sm btn-icon btn-light text-danger remove-item-row">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        $('#items-container').append(rowHtml);
                        initPartSelect($(`#${newRowId}`));
                    });
                    itemIndex = data.items.length;
                }

                offcanvas.show();
            }
        });
    });

    // Reset form when offcanvas is hidden
    $('#offcanvas_add').on('hidden.bs.offcanvas', function () {
        $('#add-wr-form')[0].reset();
        $('#wr-id').val('');
        $('#offcanvas_add_title').text('Add Work Request');
        $('#btn-save-wr').text('Create Request');
        $('#add-equipment-id').val('').trigger('change');
        $('#items-container').html(`
            <div class="row item-row mb-2">
                <div class="col-4">
                    <label class="small text-muted">Part Name</label>
                    <select name="items[0][part_name]" class="form-select form-select-sm part-select" data-placeholder="Search part...">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-2 px-1">
                    <label class="small text-muted">Qty</label>
                    <input type="number" name="items[0][qty]" class="form-control form-control-sm" step="0.01" placeholder="Qty">
                </div>
                <div class="col-3 px-1">
                    <label class="small text-muted">Price</label>
                    <input type="text" name="items[0][price]" class="form-control form-control-sm part-price text-end" readonly placeholder="Price">
                </div>
                <div class="col-3">
                    <label class="small text-muted">Unit</label>
                    <input type="text" name="items[0][unit]" class="form-control form-control-sm" placeholder="Unit">
                </div>
            </div>
        `);
        $('#goods-items-section').hide();
        itemIndex = 1;
    });

    // Create Work Order from WR
    $(document).on('click', '.create-wo-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Create Work Order?',
            text: "This will generate a new Work Order for this request.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Create WO'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).prop('disabled', true);

                $.post(route('work-request.create-work-order', id), function (response) {
                    if (response.success) {
                        Swal.fire('Created!', response.message, 'success');
                        dataTable.ajax.reload();
                    }
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Error occurred', 'error');
                }).always(() => {
                    $(this).prop('disabled', false);
                });
            }
        });
    });

    // Store/Update Work Request
    $('#add-wr-form').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btn = $('#btn-save-wr');
        const id = $('#wr-id').val();

        const url = id ? route('work-request.update', id) : route('work-request.store');
        const method = id ? 'PUT' : 'POST';

        // Strip commas from prices before serializing
        $('.part-price').each(function () {
            const val = $(this).val().replace(/,/g, '');
            $(this).val(val);
        });

        const formData = form.serialize();

        // Restore formatting after serialization so UI stays nice
        $('.part-price').each(function () {
            const val = $(this).val();
            if (val) $(this).val(formatNumber(val));
        });

        $.ajax({
            url: url,
            method: method,
            data: formData,
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

                    if (!id) {
                        const countEl = $('#wr-count');
                        if (countEl.length) {
                            countEl.text(parseInt(countEl.text()) + 1);
                        }
                    }
                }
            },
            error: function (xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error occurred';
                Swal.fire('Error', msg, 'error');
            },
            complete: function () {
                btn.prop('disabled', false).text(id ? 'Update Request' : 'Create Request');
            }
        });
    });

    // Helper function for routes
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'work-request.datatables') return `${base}/work-request/datatables`;
        if (name === 'work-request.store') return `${base}/work-request`;
        if (name === 'work-request.edit') return `${base}/work-request/${params}/edit`;
        if (name === 'work-request.update') return `${base}/work-request/${params}`;
        if (name === 'work-request.asset-details') return `${base}/work-request/asset/${params}`;
        if (name === 'work-request.parts-search') return `${base}/work-request/parts/search`;
        if (name === 'work-request.create-work-order') return `${base}/work-request/${params}/create-work-order`;
        return '#';
    }
});
