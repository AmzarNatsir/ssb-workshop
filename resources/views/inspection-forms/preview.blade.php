<?php $page = 'inspection_forms'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4 flex-wrap">
                <div>
                    <h4 class="mb-1">Form Preview: {{ $form->form_title }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{url('index')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('inspection-forms.index') }}">Inspection Forms</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Preview</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('inspection-forms.index') }}" class="btn btn-light">
                        <i class="ti ti-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>

            <!-- Form Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Form Code</small>
                            <p class="fw-semibold mb-0">{{ $form->form_code }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Version</small>
                            <p class="fw-semibold mb-0">{{ $form->version }}</p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Status</small>
                            <p class="mb-0">
                                @if($form->status == 'PUBLISHED')
                                    <span class="badge bg-success">Published</span>
                                @elseif($form->status == 'DRAFT')
                                    <span class="badge bg-secondary">Draft</span>
                                @else
                                    <span class="badge bg-warning">Archived</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Applicable Category</small>
                            <p class="mb-0">{{ $form->applicable_unit_category ?? 'All Categories' }}</p>
                        </div>
                    </div>
                    @if($form->form_description)
                        <hr>
                        <div>
                            <small class="text-muted">Description</small>
                            <p class="mb-0">{{ $form->form_description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Form Sections Preview -->
            @foreach($form->sections as $section)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-bottom bg-light">
                        <h5 class="card-title mb-0">
                            <i class="ti ti-list-check me-2"></i>{{ $section->section_title }}
                        </h5>
                        @if($section->section_description)
                            <small class="text-muted">{{ $section->section_description }}</small>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">Item Name</th>
                                        <th width="15%">Input Type</th>
                                        <th width="10%">Required</th>
                                        <th width="15%">Thresholds</th>
                                        <th width="15%">Auto Action</th>
                                        <th width="15%">Instruction</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($section->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
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
                                                <span class="badge bg-info">{{ str_replace('_', ' ', $item->input_type) }}</span>
                                            </td>
                                            <td>
                                                @if($item->is_required)
                                                    <span class="badge bg-danger">Required</span>
                                                @else
                                                    <span class="text-muted">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->input_type == 'NUMBER' && ($item->threshold_warning || $item->threshold_critical))
                                                    @if($item->threshold_warning)
                                                        <small><span class="badge bg-warning">⚠ {{ $item->threshold_warning }}</span></small>
                                                    @endif
                                                    @if($item->threshold_critical)
                                                        <small><span class="badge bg-danger">🔴 {{ $item->threshold_critical }}</span></small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->auto_action)
                                                    @php $autoAction = is_string($item->auto_action) ? json_decode($item->auto_action, true) : $item->auto_action; @endphp
                                                    @if(isset($autoAction['action']))
                                                        <small>
                                                            <span class="badge bg-primary">{{ $autoAction['action'] }}</span>
                                                            @if(isset($autoAction['priority']))
                                                                <br><span class="badge bg-secondary">{{ $autoAction['priority'] }}</span>
                                                            @endif
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->instruction)
                                                    <small class="text-info">{{ Str::limit($item->instruction, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Summary Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-primary mb-0">{{ $form->sections->count() }}</h3>
                            <small class="text-muted">Sections</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success mb-0">{{ $form->sections->sum(fn($s) => $s->items->count()) }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning mb-0">{{ $form->sections->sum(fn($s) => $s->items->where('is_required', true)->count()) }}</h3>
                            <small class="text-muted">Required Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info mb-0">{{ $form->sections->sum(fn($s) => $s->items->whereNotNull('auto_action')->count()) }}</h3>
                            <small class="text-muted">Auto-Actions</small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @component('components.footer')
        @endcomponent
    </div>

@endsection
