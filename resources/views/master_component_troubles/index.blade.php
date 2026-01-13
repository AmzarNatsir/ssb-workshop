<?php $page = 'master-component-troubles'; ?>
@extends('layout.mainlayout')
@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Master Component Troubles</h4>
                <a href="javascript:void(0);" class="btn btn-primary" id="btn-add-new">Add Component</a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="component-trouble-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Offcanvas Container -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_form">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvas_title"></h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" id="offcanvas_body">
            <!-- Form content loaded via AJAX -->
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var table = $('#component-trouble-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('master-component-troubles.datatables') }}",
                columns: [
                    { data: 'component_name', name: 'component_name' },
                    { data: 'description', name: 'description' },
                    { 
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-info me-1 btn-edit" data-id="${data}"><i class="ti ti-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-delete" data-id="${data}"><i class="ti ti-trash"></i></button>
                            `;
                        }
                    }
                ]
            });

            // Add New Button
            $('#btn-add-new').click(function() {
                $('#offcanvas_title').text('Add Component');
                $.get("{{ route('master-component-troubles.create') }}", function(data) {
                    $('#offcanvas_body').html(data);
                    var offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_form'));
                    offcanvas.show();
                });
            });

            // Edit Button
            $(document).on('click', '.btn-edit', function() {
                var id = $(this).data('id');
                $('#offcanvas_title').text('Edit Component');
                $.get("{{ route('master-component-troubles.index') }}/" + id + "/edit", function(data) {
                    $('#offcanvas_body').html(data);
                    var offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas_form'));
                    offcanvas.show();
                });
            });

            // Handle Add Form Submission
            $(document).on('submit', '#add-form', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                            var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_form'));
                            offcanvas.hide();
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Failed to save data.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).text('Save');
                    }
                });
            });

            // Handle Edit Form Submission
            $(document).on('submit', '#edit-form', function(e) {
                e.preventDefault();
                var form = $(this);
                var btn = form.find('button[type="submit"]');
                btn.prop('disabled', true).text('Updating...');

                $.ajax({
                    url: form.attr('action'),
                    method: 'POST',
                    data: form.serialize(),
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            table.ajax.reload();
                            var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('offcanvas_form'));
                            offcanvas.hide();
                        }
                    },
                    error: function(err) {
                        Swal.fire('Error', 'Failed to update data.', 'error');
                    },
                    complete: function() {
                        btn.prop('disabled', false).text('Update');
                    }
                });
            });

            // Handle Delete
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
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
                            url: "{{ route('master-component-troubles.index') }}/" + id,
                            type: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                Swal.fire(
                                    'Deleted!',
                                    res.message,
                                    'success'
                                );
                                table.ajax.reload();
                            },
                            error: function(err) {
                                Swal.fire('Error', 'Failed to delete.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
