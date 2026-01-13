<form action="{{ route('equipment.store') }}" method="POST" id="add-form" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control" required placeholder="EQ-001">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required placeholder="Excavator CAT 320">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"></textarea>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Engine No</label>
            <input type="text" name="engine_no" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Chassis No</label>
            <input type="text" name="chassis_no" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Plate Number</label>
            <input type="text" name="plate_number" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Merk</label>
            <select name="merk_id" class="form-select">
                <option value="">Select Merk</option>
                @foreach($merks as $merk)
                    <option value="{{ $merk->id }}">{{ $merk->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Unit Type</label>
            <select name="unit_type_id" class="form-select">
                <option value="">Select Unit Type</option>
                @foreach($unitTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">Service Period Type</label>
            <select name="periodic_service_type_id" class="form-select">
                <option value="">Select Service Period Type</option>
                @foreach($periodicServiceTypes as $pst)
                    <option value="{{ $pst->id }}">{{ $pst->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select name="equipment_status_id" class="form-select">
                <option value="">Select Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Status Information</label>
            <input type="text" name="status_information" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Project Location (Read-only)</label>
            <input type="text" class="form-control" readonly placeholder="Will be assigned by system">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Project Status (Read-only)</label>
            <input type="text" name="project_status" class="form-control" readonly placeholder="Idle">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">WH / Project (Work Hours per Project) <span class="text-danger">*</span></label>
            <input type="number" name="wh_per_project" class="form-control" step="0.01" min="0.01" required placeholder="8.5">
            <small class="text-muted">Work hours per project day (must be greater than 0)</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Meter Reading</label>
            <select name="meter_reading_id" class="form-select">
                <option value="">Select Meter Reading</option>
                @foreach($meterReadings as $mr)
                    <option value="{{ $mr->id }}">{{ $mr->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Capacity</label>
            <input type="text" name="capacity" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Ownership Mode</label>
            <select name="ownership_mode_id" class="form-select">
                <option value="">Select Ownership Mode</option>
                @foreach($ownershipModes as $om)
                    <option value="{{ $om->id }}">{{ $om->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">PIC Unit</label>
            <select name="pic_unit" class="form-select">
                <option value="">Select PIC</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Supplier (nullable)</label>
            <select name="supplier_id" class="form-select">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Production Year</label>
            <input type="number" name="prodution_year" class="form-control" min="1900" max="{{ date('Y') + 1 }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Purchase Date</label>
            <input type="date" name="purchase_date" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Warranty Date</label>
            <input type="date" name="warranty_date" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Purchase Price</label>
            <input type="number" name="purchase_price" class="form-control" step="0.01">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Internal Estimated Price</label>
            <input type="number" name="internal_estimated_price" class="form-control" step="0.01">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Market Price</label>
            <input type="number" name="market_price" class="form-control" step="0.01">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Image</label>
        <input type="file" name="image" class="form-control" id="image-input" accept="image/*">
        <div class="mt-2" id="image-preview-container" style="display: none;">
            <img src="" id="image-preview" class="img-thumbnail" style="max-height: 200px;">
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#image-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
                $('#image-preview-container').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#image-preview-container').hide();
        }
    });
});
</script>
