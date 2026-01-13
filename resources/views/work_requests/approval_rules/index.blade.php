<?php $page = 'approval_matrix_wr'; ?>
@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-1">Approval Matrix: Work Requests</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Approval Matrix</li>
                    </ol>
                </nav>
            </div>
            <div class="gap-2 d-flex align-items-center flex-wrap">
                <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas_rule">
                    <i class="ti ti-square-rounded-plus-filled me-1"></i>Add Approval Rule
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap" id="rule-list">
                        <thead class="table-light">
                            <tr>
                                <th>Category</th>
                                <th>WR Type</th>
                                <th>Approver Role</th>
                                <th>Step Order</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>
</div>

<!-- Offcanvas for Add/Edit Rule -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_rule">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvas_rule_title">Add Approval Rule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="rule-form">
            @csrf
            <input type="hidden" name="id" id="rule-id">
            
            <div class="mb-3">
                <label class="form-label text-dark fw-medium">WR Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-dark fw-medium">WR Type <span class="text-danger">*</span></label>
                <select name="wr_type" class="form-select" required>
                    <option value="">Select Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Approver Role <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select select2" required>
                    <option value="">Select Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label text-dark fw-medium">Step Order <span class="text-danger">*</span></label>
                <input type="number" name="step_order" class="form-control" min="1" required placeholder="e.g. 1, 2, 3">
                <div class="form-text mt-1 italic small text-muted">Defines the sequence of approval.</div>
            </div>

            <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-save-rule">Save Rule</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/work-request-approval-rules.js') }}"></script>
@endpush
