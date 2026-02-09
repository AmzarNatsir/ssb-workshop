<?php $page = 'inspections'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Inspection Result</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inspections.index') }}">Inspections</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Result</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('inspections.index') }}" class="btn btn-light">
                        <i class="ti ti-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Result Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Result Code</small>
                            <p class="fw-semibold mb-0">{{ $result->result_code }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Form</small>
                            <p class="fw-semibold mb-0">{{ $result->form->form_title }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Unit</small>
                            <p class="fw-semibold mb-0">{{ $result->unit->code }} - {{ $result->unit->name }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Overall Status</small>
                            <p class="mb-0">
                                @if($result->overall_status == 'PASS')
                                    <span class="badge bg-success fs-6">PASS</span>
                                @elseif($result->overall_status == 'FAIL')
                                    <span class="badge bg-danger fs-6">FAIL</span>
                                @else
                                    <span class="badge bg-warning fs-6">PENDING</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Inspector</small>
                            <p class="mb-0">{{ $result->inspector->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Inspection Date</small>
                            <p class="mb-0">{{ $result->inspection_date->format('d M Y H:i') }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Duration</small>
                            <p class="mb-0">
                                @if($result->start_time && $result->end_time)
                                    {{ $result->start_time->diffInMinutes($result->end_time) }} minutes
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Unit Ready for Operation</small>
                            <p class="mb-0">
                                @if($result->unit_ready_for_operation)
                                    <span class="badge bg-success">YES</span>
                                @else
                                    <span class="badge bg-danger">NO</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inspection Results by Section -->
            @foreach($result->form->sections as $section)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-light">
                        <h5 class="card-title mb-0">{{ $section->section_title }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="30%">Item</th>
                                        <th width="20%">Result</th>
                                        <th width="30%">Notes</th>
                                        <th width="20%">Actions Triggered</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($section->items as $item)
                                        @php
                                            $resultItem = $result->resultItems->where('item_id', $item->id)->first();
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $item->item_name }}</strong>
                                                @if($item->item_description)
                                                    <br><small class="text-muted">{{ $item->item_description }}</small>
                                                @endif
                                                @if($item->item_image)
                                                    <div class="mt-1">
                                                        <a href="{{ asset('storage/' . $item->item_image) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $item->item_image) }}" class="img-thumbnail" style="max-height: 40px;" title="Reference Image">
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($resultItem)
                                                    @if($resultItem->value_option)
                                                        @php
                                                            $badgeClass = 'secondary';
                                                            if(in_array($resultItem->value_option, ['Good', 'Yes', 'Pass', 'Ok'])) {
                                                                $badgeClass = 'success';
                                                            } elseif(in_array($resultItem->value_option, ['Repair', 'No', 'Fail', 'Faulty'])) {
                                                                $badgeClass = 'danger';
                                                            } elseif($resultItem->value_option == 'Replace') {
                                                                $badgeClass = 'warning';
                                                            }
                                                        @endphp
                                                        <span class="badge bg-{{ $badgeClass }}">{{ $resultItem->value_option }}</span>
                                                    @elseif($resultItem->value_number !== null)
                                                        <strong>{{ $resultItem->value_number }}</strong>
                                                        @php
                                                            $thresholdStatus = $item->validateThreshold($resultItem->value_number);
                                                        @endphp
                                                        @if($thresholdStatus == 'CRITICAL')
                                                            <span class="badge bg-danger ms-1">Critical</span>
                                                        @elseif($thresholdStatus == 'WARNING')
                                                            <span class="badge bg-warning ms-1">Warning</span>
                                                        @endif
                                                    @elseif($resultItem->value_text)
                                                        {{ $resultItem->value_text }}
                                                    @elseif($resultItem->image_path)
                                                        <a href="{{ asset('storage/' . $resultItem->image_path) }}" target="_blank">
                                                            <img src="{{ asset('storage/' . $resultItem->image_path) }}" class="img-thumbnail" style="max-width: 100px;">
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($resultItem && $resultItem->notes)
                                                    <small>{{ $resultItem->notes }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($resultItem && $resultItem->triggered_action)
                                                    @foreach($resultItem->triggered_action as $action)
                                                        @if(is_array($action) && isset($action['action']) && $action['action'] == 'CREATE_WR')
                                                            <a href="{{ route('work-request.show', $action['work_request_id'] ?? '#') }}" class="badge bg-info text-decoration-none" target="_blank">
                                                                <i class="ti ti-file-text"></i> WR: {{ $action['work_request_no'] ?? 'N/A' }}
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @php
                            $sectionResult = $result->sectionResults->where('section_id', $section->id)->first();
                        @endphp
                        @if($sectionResult && $sectionResult->image_path)
                            <div class="mt-4 pt-3 border-top">
                                <label class="form-label fw-semibold">Section Documentation Image</label>
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $sectionResult->image_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $sectionResult->image_path) }}" class="img-thumbnail" style="max-height: 200px;">
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- General Notes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-light">
                        <h5 class="card-title mb-0">General Notes</h5>
                    </div>
                    <div class="card-body">
                        @if($result->notes)
                            <p class="mb-0">{{ $result->notes }}</p>
                        @else
                            <p class="text-muted mb-0">No notes provided.</p>
                        @endif
                    </div>
                </div>

        </div>
        @component('components.footer')
        @endcomponent
    </div>

@endsection
