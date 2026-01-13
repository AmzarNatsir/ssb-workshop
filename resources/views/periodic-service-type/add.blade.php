<form action="{{ route('periodic-service-type.store') }}" method="POST" id="add-form">
    @csrf
    <div>
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="e.g 1000 HM Service">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-end">
        <a href="#" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>
