<form action="{{ route('users.store') }}" method="POST" id="add-user-form">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">User Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
            </div>
        </div>
        <div class="col-md-12">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Assign Roles <span class="text-danger">*</span></label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($roles as $role)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_add_{{ $role->id }}">
                        <label class="form-check-label text-capitalize" for="role_add_{{ $role->id }}">
                            {{ $role->name }}
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password" class="form-control pass-input" placeholder="Min 8 characters" required>
                    <span class="input-group-text toggle-password cursor-pointer">
                        <i class="ti ti-eye-off"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group input-group-flat">
                    <input type="password" name="password_confirmation" class="form-control pass-input" placeholder="Repeat password" required>
                    <span class="input-group-text toggle-password cursor-pointer">
                        <i class="ti ti-eye-off"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-end mt-4 pt-3 border-top">
        <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary px-4" id="btn-save-user">Create User</button>
    </div>
</form>
