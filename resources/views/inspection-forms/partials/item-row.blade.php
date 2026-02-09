<div class="item-row border rounded p-3 mb-2 bg-white">
    <div class="row">
        <div class="col-md-4 mb-2">
            <label class="form-label small">Item Name <span class="text-danger">*</span></label>
            <input type="text" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][item_name]" class="form-control form-control-sm" required placeholder="e.g. Engine Oil Level" value="{{ $item->item_name }}">
            <input type="hidden" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][item_id]" value="{{ $item->id }}">
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label small">Input Type <span class="text-danger">*</span></label>
            <select name="sections[{{ $sIndex }}][items][{{ $iIndex }}][input_type]" class="form-select form-select-sm input-type-select" required>
                <option value="GOOD_REPAIR_REPLACE_NA" {{ $item->input_type == 'GOOD_REPAIR_REPLACE_NA' ? 'selected' : '' }}>Good / Repair / Replace / NA</option>
                <option value="GOOD_OTHERS" {{ $item->input_type == 'GOOD_OTHERS' ? 'selected' : '' }}>Good / Others (Text)</option>
                <option value="YES_NO_NA" {{ $item->input_type == 'YES_NO_NA' ? 'selected' : '' }}>Yes / No / NA</option>
                <option value="PASS_FAIL_NA" {{ $item->input_type == 'PASS_FAIL_NA' ? 'selected' : '' }}>Pass / Fail / NA</option>
                <option value="OK_FAULTY_NA" {{ $item->input_type == 'OK_FAULTY_NA' ? 'selected' : '' }}>Ok / Faulty / NA</option>
                <option value="NUMBER" {{ $item->input_type == 'NUMBER' ? 'selected' : '' }}>Number</option>
                <option value="TEXT" {{ $item->input_type == 'TEXT' ? 'selected' : '' }}>Text</option>
                <option value="IMAGE" {{ $item->input_type == 'IMAGE' ? 'selected' : '' }}>Image Upload</option>
                <option value="DATE" {{ $item->input_type == 'DATE' ? 'selected' : '' }}>Date</option>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label class="form-label small">Required</label>
            <div class="form-check form-switch mt-1">
                <input type="checkbox" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][is_required]" class="form-check-input" value="1" {{ $item->is_required ? 'checked' : '' }}>
            </div>
        </div>
        <div class="col-md-3 mb-2 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn mt-3">
                <i class="ti ti-trash"></i>
            </button>
        </div>
    </div>
    <!-- Threshold fields (shown only for NUMBER type) -->
    <div class="row threshold-fields" style="{{ $item->input_type == 'NUMBER' ? '' : 'display: none;' }}">
        <div class="col-md-4 mb-2">
            <label class="form-label small">Threshold Warning</label>
            <input type="number" step="0.01" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][threshold_warning]" class="form-control form-control-sm" placeholder="e.g. 25" value="{{ $item->threshold_warning }}">
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label small">Threshold Critical</label>
            <input type="number" step="0.01" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][threshold_critical]" class="form-control form-control-sm" placeholder="e.g. 10" value="{{ $item->threshold_critical }}">
        </div>
    </div>
    <!-- Auto Action -->
    @php
        $autoAction = $item->auto_action;
        if (is_string($autoAction)) {
            $autoAction = json_decode($autoAction, true);
        }
    @endphp
    <div class="row mt-2">
        <div class="col-md-4 mb-2">
            <label class="form-label small">Auto Action</label>
            <select name="sections[{{ $sIndex }}][items][{{ $iIndex }}][auto_action_type]" class="form-select form-select-sm">
                <option value="">No Action</option>
                <option value="CREATE_WR" {{ ($autoAction['action'] ?? '') == 'CREATE_WR' ? 'selected' : '' }}>Create Work Request</option>
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label small">Priority (if WR)</label>
            <select name="sections[{{ $sIndex }}][items][{{ $iIndex }}][auto_action_priority]" class="form-select form-select-sm">
                <option value="LOW" {{ ($autoAction['priority'] ?? '') == 'LOW' ? 'selected' : '' }}>Low</option>
                <option value="MEDIUM" {{ ($autoAction['priority'] ?? '') == 'MEDIUM' ? 'selected' : '' }}>Medium</option>
                <option value="HIGH" {{ ($autoAction['priority'] ?? '') == 'HIGH' ? 'selected' : '' }}>High</option>
                <option value="CRITICAL" {{ ($autoAction['priority'] ?? '') == 'CRITICAL' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label small">Trigger On</label>
            <input type="text" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][auto_action_trigger]" class="form-control form-control-sm" placeholder="e.g. Repair,Replace" value="{{ isset($autoAction['trigger_on']) ? implode(',', $autoAction['trigger_on']) : '' }}">
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-6">
            <label class="form-label small">Instruction</label>
            <input type="text" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][instruction]" class="form-control form-control-sm" placeholder="Instructions for inspector" value="{{ $item->instruction }}">
        </div>
        <div class="col-md-6">
            <label class="form-label small">Reference Image</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control item-image-input" accept="image/*">
                <input type="hidden" name="sections[{{ $sIndex }}][items][{{ $iIndex }}][item_image]" class="item-image-path" value="{{ $item->item_image }}">
            </div>
            <div class="item-image-preview mt-1">
                @if($item->item_image)
                    <img src="{{ asset('storage/' . $item->item_image) }}" class="img-thumbnail" style="max-height: 50px;">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-image-btn"><i class="ti ti-x"></i></button>
                @endif
            </div>
        </div>
    </div>
</div>
