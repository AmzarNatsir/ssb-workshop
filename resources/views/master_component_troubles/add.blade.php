<form action="{{ route('master-component-troubles.store') }}" method="POST" id="add-form">
    @csrf
    <div class="mb-3">
        <label class="form-label">Component Name <span class="text-danger">*</span></label>
        <input type="text" name="component_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="d-flex align-items-center justify-content-end">
        <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
