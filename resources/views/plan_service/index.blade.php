<?php $page = 'plan_service'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Plan Service <span class="badge badge-soft-primary ms-2" id="plan-count">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Plan Service</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add">
                        <i class="ti ti-square-rounded-plus-filled me-1"></i>Add New Plan
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon input-icon-start">
                            <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search Unit..." id="search-input">
                        </div>
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="PLANNED">PLANNED</option>
                            <option value="OVERDUE">OVERDUE</option>
                            <option value="COMPLETED">COMPLETED</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="plan-service-list">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit</th>
                                    <th>HM Actual</th>
                                    <th>Overdue (Hours)</th>
                                    <th>Next PS (HM)</th>
                                    <th>Service Type</th>
                                    <th>Plan Date</th>
                                    <th>Status</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Offcanvas Add New -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_add">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Add New Service Plan</h5>
            <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body">
            <form id="add-plan-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Equipment / Unit <span class="text-danger">*</span></label>
                    <select name="equipment_id" class="form-select select2" required id="add-equipment-id">
                        <option value="">Select Equipment</option>
                        {{-- Options will be loaded via script or passed from controller --}}
                        @isset($equipments)
                            @foreach($equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->code }} - {{ $equipment->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div id="equipment-info" class="p-3 bg-light rounded mb-3" style="display: none;">
                    <h6>Equipment Details:</h6>
                    <div class="row small">
                        <div class="col-6"><strong>WH / Project:</strong> <span id="info-wh"></span></div>
                        <div class="col-6"><strong>Service Type:</strong> <span id="info-service"></span></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">HM PS Sebelumnya <span class="text-danger">*</span></label>
                        <input type="number" name="hm_ps_sebelumnya" class="form-control" step="0.01" required id="add-hm-ps">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">HM Actual <span class="text-danger">*</span></label>
                        <input type="number" name="hm_actual" class="form-control" step="0.01" required id="add-hm-actual">
                    </div>
                </div>

                <div id="calculation-preview" class="p-3 border rounded mb-3 bg-soft-info" style="display: none;">
                    <h6 class="text-info"><i class="ti ti-calculator me-1"></i>Calculation Preview</h6>
                    <div class="row">
                        <div class="col-6 mb-2"><strong>Overdue:</strong> <span id="preview-overdue"></span></div>
                        <div class="col-6 mb-2"><strong>Next PS:</strong> <span id="preview-next"></span></div>
                        <div class="col-6"><strong>Plan Date:</strong> <span id="preview-date"></span></div>
                        <div class="col-6"><strong>Service:</strong> <span id="preview-type"></span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Add any service notes..."></textarea>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-plan">Create Plan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offcanvas Edit (Loaded dynamically) -->
    <div class="offcanvas offcanvas-end offcanvas-large" tabindex="-1" id="offcanvas_edit">
        <div class="offcanvas-header border-bottom">
            <h5 class="fw-semibold">Edit Service Plan</h5>
            <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="offcanvas" aria-label="Close">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div class="offcanvas-body" id="edit-form-container">
            <!-- Content loaded via AJAX -->
        </div>
    </div>

    <!-- Complete Modal -->
    <div class="modal fade" id="modal_complete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="complete-plan-form">
                    @csrf
                    <input type="hidden" name="id" id="complete-id">
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <i class="ti ti-circle-check-filled text-success fs-1"></i>
                            <h6 class="mt-2 text-muted">Confirm service completion</h6>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Actual HM at Service <span class="text-danger">*</span></label>
                            <input type="number" name="hm_at_service" class="form-control" step="0.01" required id="complete-hm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Date <span class="text-danger">*</span></label>
                            <input type="date" name="service_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Complete Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="modal_cancel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">Cancel Service Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="cancel-plan-form">
                    @csrf
                    <input type="hidden" name="id" id="cancel-id">
                    <div class="modal-body text-center">
                        <i class="ti ti-alert-circle-filled text-warning fs-1"></i>
                        <p class="mt-3">Are you sure you want to cancel this service plan? This action cannot be undone.</p>
                        <div class="mb-3 text-start">
                            <label class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="Describe why this plan is being cancelled..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Back</button>
                        <button type="submit" class="btn btn-warning">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- I'll create a separate JS file and link it here --}}
    <script src="{{ URL::asset('build/js/plan-service.js') }}"></script>
@endpush
