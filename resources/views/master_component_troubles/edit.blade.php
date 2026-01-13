<form action="{{ route('master-component-troubles.update', $component->id) }}" method="POST" id="edit-form">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Component Name <span class="text-danger">*</span></label>
        <input type="text" name="component_name" class="form-control" value="{{ $component->component_name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ $component->description }}</textarea>
    </div>
    <div class="d-flex align-items-center justify-content-end">
        <button type="button" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
