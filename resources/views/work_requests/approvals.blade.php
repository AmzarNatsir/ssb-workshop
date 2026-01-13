<?php $page = 'approval_center_wr'; ?>
@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <!-- Page Header -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
            <div>
                <h4 class="mb-1">Approval Center: Work Requests</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Approval Center</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Pending Approvals</h5>
                <p class="text-muted small mb-0">List of work requests waiting for your verification.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap" id="approval-wr-list">
                        <thead class="table-light">
                            <tr>
                                <th>WR No</th>
                                <th>Asset / Unit</th>
                                <th>Type</th>
                                <th>Requested By</th>
                                <th>Requested At</th>
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
@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/work-request-approval.js') }}"></script>
@endpush
