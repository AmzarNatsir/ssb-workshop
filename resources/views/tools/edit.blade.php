<form action="{{ route('tools.update', $tool->id) }}" method="POST" id="edit-form" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div>
        <!-- Basic Info -->
        <div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" required value="{{ $tool->code }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ $tool->name }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="2" required>{{ $tool->description }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Category (Tool Type) <span class="text-danger">*</span></label>
                    <select name="tool_type_id" class="form-select" required>
                        <option value="">Select Type</option>
                        @foreach($toolTypes as $type)
                            <option value="{{ $type->id }}" {{ $tool->tool_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rack <span class="text-danger">*</span></label>
                    <select name="racks_id" class="form-select" required>
                        <option value="">Select Rack</option>
                        @foreach($racks as $rack)
                            <option value="{{ $rack->id }}" {{ $tool->racks_id == $rack->id ? 'selected' : '' }}>{{ $rack->name }} ({{ $rack->rack_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_id" class="form-select">
                        <option value="">Select Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ $tool->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Min Quantity (Low Stock Alert)</label>
                    <input type="number" name="min_quantity" class="form-control" value="{{ $tool->min_quantity }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Acquisition Date</label>
                    <input type="date" name="acquisition_date" class="form-control" value="{{ $tool->acquisition_date }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Acquisition Cost</label>
                    <input type="number" name="acquisition_cost" class="form-control" step="0.01" value="{{ $tool->acquisition_cost }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="{{ $tool->quantity }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control" id="image-input-edit" accept="image/*">
                <div class="mt-2" id="image-preview-container-edit">
                    @if($tool->image)
                        <img src="{{ Storage::url($tool->image) }}" id="image-preview-edit" class="img-thumbnail" style="max-height: 200px;">
                    @else
                        <img src="" id="image-preview-edit" class="img-thumbnail" style="max-height: 200px; display: none;">
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="print_barcode" id="print_barcode_edit" {{ $tool->print_barcode ? 'checked' : '' }}>
                    <label class="form-check-label" for="print_barcode_edit">
                        Print Barcode
                    </label>
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
