<!-- Edit Role -->
<form action="{{ route('roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div>
        <!-- Basic Info -->
        <div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-0">
                        <label class="form-label text-dark fw-medium">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" name="name" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="col-md-12 mt-3">
                    <div class="table-responsive border rounded">
                        <table class="table table-hover mb-0" id="permission_list">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 30%;">Modules</th>
                                    <th class="text-center">View</th>
                                    <th class="text-center">Create</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                    <th class="text-center">All</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedPermissions as $module => $actions)
                                <tr>
                                    <td class="fw-medium ps-3 text-dark">{{ $module }}</td>
                                    @php
                                        $standardActions = ['view', 'create', 'edit', 'delete'];
                                    @endphp
                                    @foreach($standardActions as $action)
                                    <td class="text-center">
                                        @if(isset($actions[$action]))
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $actions[$action] }}" id="perm_{{ md5($actions[$action]) }}" {{ in_array($actions[$action], $rolePermissions) ? 'checked' : '' }}>
                                        @elseif(isset($actions['list']) && $action == 'view')
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $actions['list'] }}" id="perm_{{ md5($actions['list']) }}" {{ in_array($actions['list'], $rolePermissions) ? 'checked' : '' }}>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="text-center">
                                        <input class="form-check-input row-all-check" type="checkbox" title="Toggle All for {{ $module }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Basic Info -->
    </div>
    <div class="d-flex align-items-center justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary px-4">Update Role</button>
    </div>
</form>
