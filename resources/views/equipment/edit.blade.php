<form action="{{ route('equipment.update', $equipment->id) }}" method="POST" id="edit-form" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Code <span class="text-danger">*</span></label>
            <input type="text" name="code" class="form-control" required value="{{ $equipment->code }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="{{ $equipment->name }}">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2">{{ $equipment->description }}</textarea>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Engine No</label>
            <input type="text" name="engine_no" class="form-control" value="{{ $equipment->engine_no }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Chassis No</label>
            <input type="text" name="chassis_no" class="form-control" value="{{ $equipment->chassis_no }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Plate Number</label>
            <input type="text" name="plate_number" class="form-control" value="{{ $equipment->plate_number }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
                <option value="">Select Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $equipment->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Merk</label>
            <select name="merk_id" class="form-select">
                <option value="">Select Merk</option>
                @foreach($merks as $merk)
                    <option value="{{ $merk->id }}" {{ $equipment->merk_id == $merk->id ? 'selected' : '' }}>{{ $merk->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Unit Type</label>
            <select name="unit_type_id" class="form-select">
                <option value="">Select Unit Type</option>
                @foreach($unitTypes as $type)
                    <option value="{{ $type->id }}" {{ $equipment->unit_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
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
                    <option value="{{ $pst->id }}" {{ $equipment->periodic_service_type_id == $pst->id ? 'selected' : '' }}>{{ $pst->name }}</option>
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
                    <option value="{{ $status->id }}" {{ $equipment->equipment_status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Status Information</label>
            <input type="text" name="status_information" class="form-control" value="{{ $equipment->status_information }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Project Location (Read-only)</label>
            <input type="text" class="form-control" readonly value="{{ $equipment->project_id ?? 'Not assigned' }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Project Status (Read-only)</label>
            <input type="text" name="project_status" class="form-control" readonly value="{{ $equipment->project_status ?? 'Idle' }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">WH / Project (Work Hours per Project) <span class="text-danger">*</span></label>
            <input type="number" name="wh_per_project" class="form-control" step="0.01" min="0.01" required value="{{ $equipment->wh_per_project ?? 8.0 }}">
            <small class="text-muted">Work hours per project day (must be greater than 0)</small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Meter Reading</label>
            <select name="meter_reading_id" class="form-select">
                <option value="">Select Meter Reading</option>
                @foreach($meterReadings as $mr)
                    <option value="{{ $mr->id }}" {{ $equipment->meter_reading_id == $mr->id ? 'selected' : '' }}>{{ $mr->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Capacity</label>
            <input type="text" name="capacity" class="form-control" value="{{ $equipment->capacity }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Ownership Mode</label>
            <select name="ownership_mode_id" class="form-select">
                <option value="">Select Ownership Mode</option>
                @foreach($ownershipModes as $om)
                    <option value="{{ $om->id }}" {{ $equipment->ownership_mode_id == $om->id ? 'selected' : '' }}>{{ $om->name }}</option>
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
                    <option value="{{ $user->id }}" {{ $equipment->pic_unit == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Supplier (nullable)</label>
            <select name="supplier_id" class="form-select">
                <option value="">Select Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $equipment->supplier_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Production Year</label>
            <input type="number" name="prodution_year" class="form-control" min="1900" max="{{ date('Y') + 1 }}" value="{{ $equipment->prodution_year }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Purchase Date</label>
            <input type="date" name="purchase_date" class="form-control" value="{{ $equipment->purchase_date }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Warranty Date</label>
            <input type="date" name="warranty_date" class="form-control" value="{{ $equipment->warranty_date }}">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Purchase Price</label>
            <input type="number" name="purchase_price" class="form-control" step="0.01" value="{{ $equipment->purchase_price }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Internal Estimated Price</label>
            <input type="number" name="internal_estimated_price" class="form-control" step="0.01" value="{{ $equipment->internal_estimated_price }}">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Market Price</label>
            <input type="number" name="market_price" class="form-control" step="0.01" value="{{ $equipment->market_price }}">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Unit Image</label>
        <input type="file" name="image" class="form-control" id="image-input-edit" accept="image/*">
        <div class="mt-2" id="image-preview-container-edit">
            @if($equipment->image)
                <img src="{{ Storage::url($equipment->image) }}" id="image-preview-edit" class="img-thumbnail" style="max-height: 200px;">
            @else
                <img src="" id="image-preview-edit" class="img-thumbnail" style="max-height: 200px; display: none;">
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#image-input-edit').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview-edit').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
