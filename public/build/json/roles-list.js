$(document).ready(function () {

    if($('#roles_list').length > 0) {
		$('#roles_list').DataTable({
			"bFilter": false,
				"bInfo": false,
					"ordering": true,
				"autoWidth": true,
				"language": {
					search: ' ',
					sLengthMenu: '_MENU_',
					searchPlaceholder: "Search",
					info: "_START_ - _END_ of _TOTAL_ items",
					"lengthMenu":     "Show _MENU_ entries",
					paginate: {
					next: '<i class="ti ti-chevron-right"></i> ',
					previous: '<i class="ti ti-chevron-left"></i> '
				},
					},
				initComplete: (settings, json)=>{
					$('.dataTables_paginate').appendTo('.datatable-paginate');
					$('.dataTables_length').appendTo('.datatable-length');
				},
            "ajax": window.rolesDatatableUrl,
			"columns": [
				{ "render": function ( data, type, row ){
					return '<div class="form-check form-check-md"><input class="form-check-input" type="checkbox"></div>';
				}},
				{ "data": "name" },
				{ "data": "created" },
				{ "render": function ( data, type, row ){
					return '<div class="dropdown table-action"><a href="#" class="action-icon btn btn-xs shadow btn-icon btn-outline-light" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical"></i></a><div class="dropdown-menu dropdown-menu-right"><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#edit_role"><i class="ti ti-edit text-blue"></i> Edit</a><a class="dropdown-item" href="permission"><i class="ti ti-shield"></i> Permission</a></div></div>';
				}}
			]
		});
	}

});
$(document).on('click', '.btn-add-role', function () {
    const offcanvas = new bootstrap.Offcanvas('#offcanvas_add');
    offcanvas.show();
    $.ajax({
        url: `/roles/create`, // route edit
        type: 'GET',
        success: function (res) {
            $('#offcanvas-add-body').html(res);
        },
        error: function () {
            $('#offcanvas-add-body').html(
                '<div class="alert alert-danger">Failed to load data</div>'
            );
        }
    });
});
