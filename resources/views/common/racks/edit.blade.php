<form action="{{ route('racks.update', $rack->id) }}" method="POST" id="edit-form">
    @csrf
    @method('PUT')
    <div>
        <!-- Basic Info -->
        <div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Rack Code <span class="text-danger">*</span></label>
                        <input type="text" name="rack_code" class="form-control" value="{{ $rack->rack_code }}" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Rack Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $rack->name }}" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="{{ $rack->location }}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Responsible Person</label>
                        <input type="text" name="responsible_person" class="form-control" value="{{ $rack->responsible_person }}">
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
