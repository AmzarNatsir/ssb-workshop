<form action="{{ route('users.update', $user->id) }}" method="POST" id="edit-user-form">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">User Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
            </div>
        </div>
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Assign Roles <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_edit_{{ $role->id }}" {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                        <label class="form-check-label text-capitalize" for="role_edit_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="alert alert-soft-info border-0 d-flex align-items-center mb-3">
                <i class="ti ti-info-circle me-2 fs-18"></i>
                <span>Leave password fields blank if you don't want to change it.</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">New Password</label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password" class="form-control pass-input" placeholder="Min 8 characters">
                    <span class="input-group-text toggle-password cursor-pointer">
                        <i class="ti ti-eye-off"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Confirm New Password</label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password_confirmation" class="form-control pass-input" placeholder="Repeat password">
                    <span class="input-group-text toggle-password cursor-pointer">
                        <i class="ti ti-eye-off"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary px-4" id="btn-update-user">Update User</button>
    </div>
</form>
