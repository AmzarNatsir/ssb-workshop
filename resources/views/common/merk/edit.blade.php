<form action="{{ route('merk.update', $merk->id) }}" method="POST" id="edit-form">
    @csrf
    @method('PUT')
    <div>
        <!-- Basic Info -->
        <div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Merk Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $merk->name }}" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $merk->description }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Basic Info -->
    </div>
    <div class="d-flex align-items-center justify-content-end">
        <a href="#" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</a>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
