<form action="{{ route('racks.store') }}" method="POST" id="add-form">
    @csrf
    <div>
        <!-- Basic Info -->
        <div>
            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Rack Code <span class="text-danger">*</span></label>
                        <input type="text" name="rack_code" class="form-control" placeholder="e.g. RACK-01" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Rack Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Rack A" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Warehouse 1, Shelf 2">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Responsible Person</label>
                        <input type="text" name="responsible_person" class="form-control" placeholder="e.g. John Doe">
                    </div>
                </div>
            </div>
        </div>
        <!-- /Basic Info -->
    </div>
    <div class="d-flex align-items-center justify-content-end">
        <a href="#" class="btn btn-light me-2" data-bs-dismiss="offcanvas">Cancel</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>
