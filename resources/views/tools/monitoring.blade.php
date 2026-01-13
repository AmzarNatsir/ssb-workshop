<?php $page = 'tools.monitoring'; ?>
@extends('layout.mainlayout')
@section('content')

    <!-- ========================
        Start Page Content
    ========================= -->

    <div class="page-wrapper">

        <!-- Start Content -->
        <div class="content pb-0">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Rack Monitoring</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{url('tools')}}">Tools</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Monitoring</li>
                        </ol>
                    </nav>
                </div>
                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <a href="{{ route('tools.index') }}" class="btn btn-outline-light shadow"><i class="ti ti-arrow-left me-1"></i>Back to List</a>
                </div>
            </div>
            <!-- End Page Header -->

            <div class="row">
                @foreach($racks as $rack)
                <div class="col-md-6 col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ $rack->name }}</h5>
                            <small class="text-muted">{{ $rack->location }} ({{ $rack->rack_code }})</small>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                @if($rack->tools->count() > 0)
                                    @foreach($rack->tools as $tool)
                                    <div class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($tool->image)
                                                <img src="{{ Storage::url($tool->image) }}" class="avatar avatar-sm rounded" alt="Tool">
                                            @else
                                                <span class="avatar avatar-sm rounded bg-light text-primary"><i class="ti ti-tool"></i></span>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $tool->name }}</h6>
                                                <small class="text-muted">{{ $tool->code }}</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="mb-1">
                                                <span class="badge bg-light text-dark">{{ $tool->quantity }} Qty</span>
                                            </div>
                                            @if($tool->status)
                                                @php
                                                    $statusColor = 'secondary';
                                                    if(str_contains(strtolower($tool->status->name), 'available') || str_contains(strtolower($tool->status->name), 'good')) $statusColor = 'success';
                                                    if(str_contains(strtolower($tool->status->name), 'repair') || str_contains(strtolower($tool->status->name), 'maintenance')) $statusColor = 'warning';
                                                    if(str_contains(strtolower($tool->status->name), 'broken') || str_contains(strtolower($tool->status->name), 'lost')) $statusColor = 'danger';
                                                @endphp
                                                <span class="badge badge-soft-{{ $statusColor }}">{{ $tool->status->name }}</span>
                                            @endif
                                            @if($tool->min_quantity > 0 && $tool->quantity <= $tool->min_quantity)
                                                 <i class="ti ti-alert-triangle text-danger" title="Low Stock"></i>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="list-group-item text-center text-muted py-4">
                                        <i class="ti ti-box-off fs-2 mb-2"></i>
                                        <p class="mb-0">No tools in this rack</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer text-muted font-size-12">
                            Total Tools: {{ $rack->tools->count() }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        <!-- End Content -->

        @component('components.footer')
        @endcomponent
    </div>

    <!-- ========================
        End Page Content
    ========================= -->

@endsection

@push('scripts')
<script>
    // Any specific scripts for monitoring can go here
</script>
@endpush
