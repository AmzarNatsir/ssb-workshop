<?php $page = 'work_orders'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Work Orders <span class="badge badge-soft-primary ms-2" id="wo-count">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Work Order</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon input-icon-start">
                            <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search WO..." id="search-input">
                        </div>
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="OPEN">OPEN</option>
                            <option value="IN_PROGRESS">IN PROGRESS</option>
                            <option value="READY">READY (VAL)</option>
                            <option value="CLOSED">CLOSED</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="work-order-list">
                            <thead class="table-light">
                                <tr>
                                    <th>WO No</th>
                                    <th>Type</th>
                                    <th>Asset / Unit</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Age (Days)</th>
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

    <!-- Offcanvas Planning (Edit DRAFT) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_planning">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Work Order Planning</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="planning-form">
                @csrf
                <input type="hidden" name="id" id="planning-wo-id">
                
                <div class="mb-3">
                    <label class="form-label">WO Type <span class="text-danger">*</span></label>
                    <select name="wo_type" class="form-select" required>
                        <option value="General">General</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Cleaning">Cleaning</option>
                        <option value="Testing">Testing</option>
                        <option value="Modification">Modification</option>
                        <option value="Inspection">Inspection</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Service Category <span class="text-danger">*</span></label>
                    <select name="service_category" class="form-select" required>
                        <option value="">Select Category</option>
                        <optgroup label="Scheduled">
                            <option value="Backlog">Backlog</option>
                            <option value="Periodic Service">Periodic Service</option>
                            <option value="Midlife Overhaul">Midlife Overhaul</option>
                        </optgroup>
                        <optgroup label="Unscheduled">
                            <option value="Accident">Accident</option>
                            <option value="Sub Contractor">Sub Contractor</option>
                            <option value="Under Repair">Under Repair</option>
                            <option value="Waiting Parts">Waiting Parts</option>
                            <option value="Warranty/Miss Product">Warranty/Miss Product</option>
                            <option value="Waiting Tool">Waiting Tool</option>
                            <option value="Waiting Mechanic">Waiting Mechanic</option>
                            <option value="Others">Others</option>
                        </optgroup>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                    <select name="maintenance_type" class="form-select" required>
                        <option value="Periodik">Periodik</option>
                        <option value="Preventive">Preventive</option>
                        <option value="Corrective">Corrective</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Manpower (Assigned To)</label>
                    <select name="assigned_to" class="form-select select2">
                        <option value="">Select Mechanic</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                    <select name="priority" class="form-select" required>
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM">MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Work Date</label>
                        <input type="date" name="work_date" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Work Order Age (Days)</label>
                        <input type="text" id="planning-wo-age" class="form-control bg-light" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description / Instructions</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-planning">Release Work Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offcanvas Spare Part Request -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_spare_part">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Request Spare Part</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="spare-part-form">
                @csrf
                <input type="hidden" name="id" id="part-wo-id">
                <div class="mb-3">
                    <label class="form-label">Part Name / Item <span class="text-danger">*</span></label>
                    <input type="text" name="part_name" class="form-control" required placeholder="Input part name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity Requested <span class="text-danger">*</span></label>
                    <input type="number" name="qty" class="form-control" step="0.01" required placeholder="0.00">
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-part">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offcanvas Mechanic Activity -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_activity">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Log Mechanic Activity</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="activity-form">
                @csrf
                <input type="hidden" name="id" id="activity-wo-id">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Activity Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="4" required placeholder="What have you done?"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Readiness <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="IN_PROGRESS">STILL IN PROGRESS</option>
                        <option value="READY">READY FOR FINAL VALIDATION</option>
                    </select>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-activity">Log Activity</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offcanvas View Detail -->
    <div class="offcanvas offcanvas-end w-50" tabindex="-1" id="offcanvas_view_detail">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="view-wo-title">Work Order Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Header Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1" id="view-wo-no">WO-XXXX</h5>
                    <span class="badge bg-secondary" id="view-wo-status">STATUS</span>
                </div>
                <div class="text-end">
                    <span class="text-muted d-block small">Priority</span>
                    <span class="fw-bold" id="view-wo-priority">HIGH</span>
                </div>
            </div>

            <!-- Details Table -->
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Type</small>
                            <span class="fw-medium" id="view-wo-type">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Asset / Unit</small>
                            <span class="fw-medium" id="view-wo-equipment">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Assigned To</small>
                            <span class="fw-medium" id="view-wo-assignee">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Created By</small>
                            <span class="fw-medium" id="view-wo-creator">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Service Category</small>
                            <span class="fw-medium" id="view-wo-category">-</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Maintenance Type</small>
                            <span class="fw-medium" id="view-wo-maint-type">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h6 class="border-bottom pb-2">Description / Instructions</h6>
                <p class="text-muted" id="view-wo-description">-</p>
            </div>

            <!-- Standard Part Requirements List -->
            <div class="mb-4" id="view-wo-standard-req-container" style="display:none;">
                <h6 class="border-bottom pb-2 d-flex justify-content-between align-items-center">
                    Standard Part Requirements
                    <span class="badge bg-soft-info text-info rounded-pill" id="view-wo-standard-req-count">0</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Qty</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="view-wo-standard-req-list">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Spare Parts List -->
            <div class="mb-4">
                <h6 class="border-bottom pb-2 d-flex justify-content-between align-items-center">
                    Spare Parts
                    <span class="badge bg-soft-primary text-primary rounded-pill" id="view-wo-parts-count">0</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="view-wo-parts-list">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Activity Log -->
            <div>
                <h6 class="border-bottom pb-2 d-flex justify-content-between align-items-center">
                    Activity Logs
                    <span class="badge bg-soft-info text-info rounded-pill" id="view-wo-activities-count">0</span>
                </h6>
                <div class="activity-feed" id="view-wo-activities-list">
                    <!-- Populated via JS -->
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/work-order-list.js') }}?v={{ time() + 3 }}"></script>
@endpush
