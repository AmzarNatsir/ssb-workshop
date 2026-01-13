<?php $page = 'work_requests'; ?>
@extends('layout.mainlayout')

@section('title', 'Work Request Details')

@section('content')
<div class="page-wrapper">
    <div class="content">
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Work Request: {{ $workRequest->wr_no }}</h4>
                <div class="page-title-right d-flex gap-2">
                    @if($pendingApproval)
                        <button class="btn btn-success btn-sm approve-btn" data-id="{{ $pendingApproval->id }}">
                            <i class="ti ti-check me-1"></i>Approve
                        </button>
                        <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $pendingApproval->id }}">
                            <i class="ti ti-x me-1"></i>Reject
                        </button>
                    @elseif($workRequest->status === 'APPROVED')
                        <button class="btn btn-success btn-sm create-wo-btn" data-id="{{ $workRequest->id }}">
                            <i class="ti ti-settings me-1"></i>Create Work Order
                        </button>
                    @endif
                    <a href="{{ route('work-request.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: WR Details -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">General Information</h5>
                    @php
                        $statusClass = match($workRequest->status) {
                            'DRAFT' => 'bg-secondary',
                            'PENDING_APPROVAL' => 'bg-warning',
                            'APPROVED' => 'bg-success',
                            'REJECTED' => 'bg-danger',
                            'IN_WORK_ORDER' => 'bg-info',
                            'CLOSED' => 'bg-dark',
                            default => 'bg-light',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} fs-6">{{ str_replace('_', ' ', $workRequest->status) }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Category</label>
                            <p class="fw-semibold mb-0">{{ $workRequest->category }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Type</label>
                            <p class="fw-semibold mb-0">
                                <span class="badge badge-outline-primary">{{ $workRequest->type }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Equipment / Unit</label>
                            <p class="fw-semibold mb-0 text-primary">{{ $workRequest->equipment->code }} - {{ $workRequest->equipment->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted mb-1">Location</label>
                            <p class="fw-semibold mb-0">{{ $workRequest->location }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1">HM / KM</label>
                            <p class="fw-semibold mb-0">{{ number_format($workRequest->hm_km, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1">Operator / Driver</label>
                            <p class="fw-semibold mb-0">{{ $workRequest->operator_name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted mb-1">Asset Condition</label>
                            <p class="fw-semibold mb-0">{{ $workRequest->asset_condition }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted mb-1">Trouble Description</label>
                            <div class="p-3 bg-light rounded border">
                                {!! nl2br(e($workRequest->trouble_description)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($workRequest->type === 'Goods Request' && $workRequest->items->count() > 0)
            <div class="card mt-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Part Details (Required Material)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">#</th>
                                    <th>Part Name</th>
                                    <th class="text-center">Qty</th>
                                    <th>Unit</th>
                                    <th class="text-end pe-3">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workRequest->items as $index => $item)
                                <tr>
                                    <td class="ps-3">{{ $index + 1 }}</td>
                                    <td class="fw-medium">{{ $item->part_name }}</td>
                                    <td class="text-center text-primary fw-bold">{{ number_format($item->qty, 2) }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td class="text-end pe-3">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Estimated Total:</td>
                                    <td class="text-end pe-3 fw-bold text-danger">Rp {{ number_format($workRequest->items->sum(fn($i) => $i->qty * $i->price), 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Status & Timeline -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Approval Progress</h5>
                </div>
                <div class="card-body">
                    <div class="timeline-box">
                        <ul class="list-unstyled mb-0">
                            <!-- Created Step -->
                            <li class="d-flex mb-4">
                                <div class="flex-shrink-0 avatar-xs">
                                    <span class="avatar-title bg-success rounded-circle">
                                        <i class="ti ti-check fs-6"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-dark fw-bold">Created by {{ $workRequest->creator->name }}</h6>
                                    <p class="text-muted mb-0 small">{{ $workRequest->created_at->format('d M Y, h:i A') }}</p>
                                    <p class="text-muted small mt-1 italic">Draft submission initiated.</p>
                                </div>
                            </li>

                            <!-- Approval Steps -->
                            @foreach($workRequest->approvals->sortBy('step_order') as $approval)
                            @php
                                $isPending = $approval->status === 'PENDING';
                                $isApproved = $approval->status === 'APPROVED';
                                $isRejected = $approval->status === 'REJECTED';
                                
                                $icon = $isApproved ? 'ti-check' : ($isRejected ? 'ti-x' : 'ti-clock');
                                $color = $isApproved ? 'bg-success' : ($isRejected ? 'bg-danger' : 'bg-warning');
                            @endphp
                            <li class="d-flex mb-4">
                                <div class="flex-shrink-0 avatar-xs">
                                    <span class="avatar-title {{ $color }} rounded-circle">
                                        <i class="{{ $icon }} fs-6"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1 text-dark fw-bold">{{ $approval->role->name }}</h6>
                                        <span class="badge {{ $color }} opacity-75 small">{{ $approval->status }}</span>
                                    </div>
                                    <p class="text-muted mb-0 small">
                                        @if($approval->user)
                                            By: {{ $approval->user->name }}
                                        @else
                                            Assignee: {{ $approval->role->name }}
                                        @endif
                                    </p>
                                    @if($approval->updated_at && $approval->status !== 'PENDING')
                                        <p class="text-muted mb-0 small">{{ $approval->updated_at->format('d M Y, h:i A') }}</p>
                                    @endif
                                    
                                    @if($approval->comment)
                                        <div class="mt-2 p-2 bg-light rounded italic small border-start border-3 {{ $isApproved ? 'border-success' : 'border-danger' }}">
                                            "{{ $approval->comment }}"
                                        </div>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                            
                            @if($workRequest->status === 'APPROVED' && $workRequest->workOrder)
                            <li class="d-flex">
                                <div class="flex-shrink-0 avatar-xs">
                                    <span class="avatar-title bg-info rounded-circle">
                                        <i class="ti ti-link fs-6"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-info fw-bold">Work Order Created</h6>
                                    <a href="#" class="btn btn-sm btn-soft-info mt-1">
                                        {{ $workRequest->workOrder->work_order_no }}
                                    </a>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-dashed bg-soft-light">
                <div class="card-body">
                    <h6 class="text-muted fw-bold mb-3">System Info</h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span>UID:</span>
                            <span class="text-dark fw-medium">{{ $workRequest->uid }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Last Updated:</span>
                            <span class="text-dark fw-medium">{{ $workRequest->updated_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
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

@push('styles')
<style>
    .timeline-box ul li {
        position: relative;
    }
    .timeline-box ul li:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 30px;
        left: 15px;
        bottom: -20px;
        width: 2px;
        background: #e9ebec;
        z-index: 0;
    }
    .avatar-xs {
        height: 2rem;
        width: 2rem;
        z-index: 1;
    }
    .bg-soft-light {
        background-color: rgba(243, 246, 249, 0.5);
    }
</style>
@endpush
