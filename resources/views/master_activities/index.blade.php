@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h3 class="page-title mb-1">Master Mechanical Activities</h3>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('index') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Common</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Master Activ.</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                <button class="btn btn-primary" id="btn-add">
                    <i class="ti ti-plus me-1"></i> Add Activity
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="activity-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Add/Edit -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas_activity">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvas-title">Add / Edit Activity</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form id="activity-form">
            @csrf
            <input type="hidden" name="id" id="activity-id">
            
            <div class="mb-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="activity-code" class="form-control" placeholder="e.g. ACT-001" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" id="activity-description" class="form-control" rows="3" placeholder="Description of the activity" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" id="activity-category" class="form-select">
                    <option value="">Select Category</option>
                    <option value="Engine">Engine</option>
                    <option value="Hydraulic">Hydraulic</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Undercarriage">Undercarriage</option>
                    <option value="Transmission">Transmission</option>
                    <option value="General">General</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Cancel</button>
                <button type="submit" class="btn btn-primary" id="btn-save">Save changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('build/js/master-activity-list.js') }}?v={{ time() }}"></script>
@endpush
