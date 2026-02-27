<?php $page = 'unit_requests'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Unit Requests <span class="badge badge-soft-primary ms-2" id="ur-count">{{ $count }}</span></h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Unit Request</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    @if($isConnected)
                        <span class="badge bg-success-subtle text-success border border-success me-2">
                            <i class="ti ti-circle-filled fs-2 me-1"></i>Project Server Connected
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger me-2">
                            <i class="ti ti-circle-filled fs-2 me-1"></i>Project Server Disconnected
                        </span>
                    @endif
                    <button type="button" class="btn btn-outline-primary" id="btn-sync-ur">
                        <i class="ti ti-refresh me-1"></i>Sync New Unit Request
                    </button>
                    <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_add">
                        <i class="ti ti-square-rounded-plus-filled me-1"></i>Add Unit Request
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-icon input-icon-start">
                            <span class="input-icon-addon text-dark"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search Request..." id="search-input">
                        </div>
                        <select class="form-select w-auto" id="filter-status">
                            <option value="">All Status</option>
                            <option value="DRAFT">DRAFT</option>
                            <option value="SUBMITTED">SUBMITTED</option>
                            <option value="GA_VALIDATED">GA VALIDATED</option>
                            <option value="APPROVED">APPROVED</option>
                            <option value="REJECTED">REJECTED</option>
                            <option value="RFU">READY FOR USE</option>
                            <option value="FINALIZED">FINALIZED</option>
                            <option value="FORWARDED_TO_WORKSHOP">FORWARDED TO WORKSHOP</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap" id="unit-request-list">
                            <thead class="table-light">
                                <tr>
                                    <th>Request No</th>
                                    <th>Project</th>
                                    <th>Units</th>
                                    <th>Requested By</th>
                                    <th>Date</th>
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
            <h5 class="offcanvas-title" id="offcanvas_add_title">Add Unit Request</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="add-ur-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Project <span class="text-danger">*</span></label>
                    <select name="project_id" class="form-select project-select" id="add-project-id" data-placeholder="Search project..." required>
                        <option value=""></option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Additional notes..."></textarea>
                </div>

                <div class="mb-4">
                    <h6 class="fw-semibold mb-3 border-bottom pb-2">Unit Details</h6>
                    <div id="items-container">
                        <div class="row item-row mb-2">
                            <div class="col-10">
                                <label class="small text-muted">Select unit from Asset Master</label>
                                <select name="items[0][equipment_id]" class="form-select select2" data-placeholder="Choose unit..." required>
                                    <option value=""></option>
                                    @foreach($equipments as $equipment)
                                        <option value="{{ $equipment->id }}">{{ $equipment->code }} - {{ $equipment->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2 d-flex align-items-end">
                                <button type="button" class="btn btn-icon btn-outline-danger remove-item-row" disabled>
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-item-row">
                        <i class="ti ti-plus me-1"></i>Add More Unit
                    </button>
                </div>

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-ur">Create Request</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/unit-request-list.js') }}"></script>
@endpush
