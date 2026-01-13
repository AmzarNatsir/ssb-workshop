/**
 * Work Request Approval Module
 * Handles Approve/Reject actions in the Approval Center
 */

$(document).ready(function () {
    const tableElement = $('#approval-wr-list');
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
            ajax: route('approval-center.work-request.datatables'),
            columns: [
                { data: 'wr_no' },
                { data: 'equipment' },
                { data: 'type' },
                { data: 'requested_by' },
                { data: 'requested_at' },
                { data: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search pending requests...",
                lengthMenu: "_MENU_",
                paginate: {
                    next: 'Next <i class="ti ti-chevron-right ms-1"></i>',
                    previous: '<i class="ti ti-chevron-left me-1"></i> Previous'
                }
            }
        });
    }

    // Approve Action
    $(document).on('click', '.approve-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Approve Request?',
            text: "Are you sure you want to approve this work request?",
            icon: 'question',
            input: 'textarea',
            inputPlaceholder: 'Add comment (optional)...',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, Approve'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route('approval-center.work-request.approve', id), {
                    comment: result.value
                }, function (response) {
                    if (response.success) {
                        Swal.fire('Approved!', response.message, 'success');
                        if (dataTable) {
                            dataTable.ajax.reload();
                        } else {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    }
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Error occurred', 'error');
                });
            }
        });
    });

    // Reject Action
    $(document).on('click', '.reject-btn', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Reject Request?',
            text: "Please provide a reason for rejection:",
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Provide reason (mandatory)...',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to provide a reason for rejection!'
                }
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reject'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(route('approval-center.work-request.reject', id), {
                    comment: result.value
                }, function (response) {
                    if (response.success) {
                        Swal.fire('Rejected!', response.message, 'info');
                        if (dataTable) {
                            dataTable.ajax.reload();
                        } else {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        }
                    }
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Error occurred', 'error');
                });
            }
        });
    });

    // Create Work Order from WR (Detail View)
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Created!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                }).fail(function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Error occurred', 'error');
                    $(this).prop('disabled', false);
                });
            }
        });
    });

    // Route Helper
    function route(name, params = null) {
        const base = window.location.origin;
        if (name === 'approval-center.work-request.datatables') return `${base}/approval-center/work-request/datatables`;
        if (name === 'approval-center.work-request.approve') return `${base}/approval-center/work-request/${params}/approve`;
        if (name === 'approval-center.work-request.reject') return `${base}/approval-center/work-request/${params}/reject`;
        if (name === 'work-request.create-work-order') return `${base}/work-request/${params}/create-work-order`;
        if (name === 'work-request.show') return `${base}/work-request/${params}`;
        return '#';
    }
});
