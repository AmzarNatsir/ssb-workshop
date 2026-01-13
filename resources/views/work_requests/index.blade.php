<?php $page = 'work_requests'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Work Requests <span class="badge badge-soft-primary ms-2" id="wr-count">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Work Request</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add">
                        <i class="ti ti-square-rounded-plus-filled me-1"></i>Add Work Request
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon input-icon-start">
                            <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search WR..." id="search-input">
                        </div>
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="PENDING_APPROVAL">PENDING APPROVAL</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="REJECTED">REJECTED</option>
                            <option value="IN_WORK_ORDER">IN WORK ORDER</option>
                            <option value="CLOSED">CLOSED</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="work-request-list">
                            <thead class="table-light">
                                <tr>
                                    <th>WR No</th>
                                    <th>Category</th>
                                    <th>Asset / Unit</th>
                                    <th>WR Type</th>
                                    <th>Created At</th>
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
            <h5 class="offcanvas-title" id="offcanvas_add_title">Add Work Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="add-wr-form">
                @csrf
                <input type="hidden" name="id" id="wr-id">
                <div class="mb-3">
                    <label class="form-label">Repair Request Category <span class="text-danger">*</span></label>
                    <select name="category" class="form-select" required>
                        <option value="">Select Category</option>
                        <option value="On-Project – Operation">On-Project – Operation</option>
                        <option value="Non-Project – Operation">Non-Project – Operation</option>
                        <option value="Non-Project – Non-Operation">Non-Project – Non-Operation</option>
                        <option value="Non-Asset">Non-Asset</option>
                        <option value="Project">Project</option>
                        <option value="Department">Department</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Equipment / Unit <span class="text-danger">*</span></label>
                    <select name="equipment_id" class="form-select select2" required id="add-equipment-id">
                        <option value="">Select Equipment</option>
                        @isset($equipments)
                            @foreach($equipments as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->code }} - {{ $equipment->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Operator / Driver</label>
                        <input type="text" name="operator_name" class="form-control" placeholder="Input operator name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">HM / KM <span class="text-danger">*</span></label>
                        <input type="number" name="hm_km" class="form-control" step="0.01" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Project Location</label>
                    <input type="text" id="asset-location" class="form-control bg-light" readonly placeholder="Auto-filled from Asset">
                </div>

                <div class="mb-3">
                    <label class="form-label">Asset Condition <span class="text-danger">*</span></label>
                    <textarea name="asset_condition" class="form-control" rows="2" required placeholder="Describe current condition..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Trouble Description <span class="text-danger">*</span></label>
                    <textarea name="trouble_description" class="form-control" rows="3" required placeholder="Describe the trouble..."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Work Request Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_repair" value="Repair Request" checked>
                            <label class="form-check-label" for="type_repair">Repair Request</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_goods" value="Goods Request">
                            <label class="form-check-label" for="type_goods">Goods Request</label>
                        </div>
                    </div>
                </div>

                <!-- Goods Request Items Section -->
                <div id="goods-items-section" style="display: none;" class="mb-4">
                    <h6 class="fw-semibold mb-3 border-bottom pb-2">Part Details (Goods Request)</h6>
                    <div id="items-container">
                        <div class="row item-row mb-2">
                            <div class="col-4">
                                <label class="small text-muted">Part Name</label>
                                <select name="items[0][part_name]" class="form-select form-select-sm part-select" data-placeholder="Search part...">
                                    <option value=""></option>
                                </select>
                            </div>
                            <div class="col-2 px-1">
                                <label class="small text-muted">Qty</label>
                                <input type="number" name="items[0][qty]" class="form-control form-control-sm" step="0.01" placeholder="Qty">
                            </div>
                            <div class="col-3 px-1">
                                <label class="small text-muted">Price</label>
                                <input type="text" name="items[0][price]" class="form-control form-control-sm part-price text-end" readonly placeholder="Price">
                            </div>
                            <div class="col-3">
                                <label class="small text-muted">Unit</label>
                                <input type="text" name="items[0][unit]" class="form-control form-control-sm" placeholder="Unit">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-item-row">
                        <i class="ti ti-plus me-1"></i>Add More Part
                    </button>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="submit_direct" id="submit_direct" value="1">
                        <label class="form-check-label" for="submit_direct">Submit for approval immediately</label>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-wr">Create Request</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/work-request-list.js') }}"></script>
@endpush
