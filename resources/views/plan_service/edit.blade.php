<form id="edit-plan-form" action="{{ route('plan-service.update', $servicePlan->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label class="form-label">Equipment / Unit</label>
        <input type="text" class="form-control bg-light" value="{{ $servicePlan->equipment->code }} - {{ $servicePlan->equipment->name }}" readonly>
        <input type="hidden" name="equipment_id" id="edit-equipment-id" value="{{ $servicePlan->equipment_id }}">
    </div>

    <div class="p-3 bg-light rounded mb-3">
        <h6>Equipment Details:</h6>
        <div class="row small">
            <div class="col-6"><strong>WH / Project:</strong> <span>{{ $servicePlan->wh_project }}</span></div>
            <div class="col-6"><strong>Current Service:</strong> <span>{{ $servicePlan->service_type }}</span></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">HM PS Sebelumnya <span class="text-danger">*</span></label>
            <input type="number" name="hm_ps_sebelumnya" class="form-control" step="0.01" required id="edit-hm-ps" value="{{ $servicePlan->hm_ps_sebelumnya }}">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">HM Actual <span class="text-danger">*</span></label>
            <input type="number" name="hm_actual" class="form-control" step="0.01" required id="edit-hm-actual" value="{{ $servicePlan->hm_actual }}">
        </div>
    </div>

    <div id="edit-calculation-preview" class="p-3 border rounded mb-3 bg-soft-info shadow-sm">
        <h6 class="text-info"><i class="ti ti-calculator me-1"></i>Recalculation Preview</h6>
        <div class="row">
            <div class="col-6 mb-2"><strong>Overdue:</strong> <span id="edit-preview-overdue" class="{{ $servicePlan->overdue > 0 ? 'text-danger' : 'text-success' }}">{{ $servicePlan->overdue }}</span></div>
            <div class="col-6 mb-2"><strong>Next PS:</strong> <span id="edit-preview-next">{{ $servicePlan->ps_berikutnya }}</span></div>
            <div class="col-6"><strong>Plan Date:</strong> <span id="edit-preview-date">{{ $servicePlan->plan_date->format('Y-m-d') }}</span></div>
            <div class="col-6"><strong>Service:</strong> <span id="edit-preview-type">{{ $servicePlan->service_type }}</span></div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control" rows="3">{{ $servicePlan->notes }}</textarea>
    </div>

    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btn-update-plan">Update Plan</button>
    </div>
</form>

<script>
    // Script for edit form behavior (runs immediately on AJAX load)
    (function($) {
        $('#edit-hm-ps, #edit-hm-actual').on('input', function () {
            const equipmentId = $('#edit-equipment-id').val();
            const hmPs = $('#edit-hm-ps').val();
            const hmActual = $('#edit-hm-actual').val();

            if (equipmentId && hmPs && hmActual) {
                $.post('{{ route("plan-service.calculate") }}', {
                    _token: '{{ csrf_token() }}',
                    equipment_id: equipmentId,
                    hm_ps_sebelumnya: hmPs,
                    hm_actual: hmActual,
                    wh_per_project: '{{ $servicePlan->wh_project }}'
                }).done(function (response) {
                    if (response.success) {
                        const data = response.data;
                        $('#edit-preview-overdue').text(data.overdue).removeClass('text-danger text-success').addClass(data.overdue > 0 ? 'text-danger' : 'text-success');
                        $('#edit-preview-next').text(data.ps_berikutnya);
                        $('#edit-preview-date').text(data.plan_date);
                        $('#edit-preview-type').text(data.service_type);
                        $('#edit-calculation-preview').slideDown();
                    }
                });
            }
        });
        
        $('#edit-plan-form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const btn = $('#btn-update-plan');
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Updating...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    if (response.success) {
                        Swal.fire('Updated', response.message, 'success');
                        $('#offcanvas_edit').offcanvas('hide');
                        $('#plan-service-list').DataTable().ajax.reload();
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON.message || 'Error occurred', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Update Plan');
                }
            });
        });
    })(jQuery);
</script>
