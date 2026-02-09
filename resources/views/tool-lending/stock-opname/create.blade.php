@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Start New Stock Opname</h4>
                <h6>Create a new verification schedule for tool inventory</h6>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('tool-lending.stock-opname.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Opname Period <span class="text-danger">*</span></label>
                                <select name="period" class="form-select" required>
                                    <option value="MONTHLY">MONTHLY</option>
                                    <option value="QUARTERLY">QUARTERLY</option>
                                    <option value="YEARLY">YEARLY</option>
                                </select>
                                <small class="text-muted">Determines the frequency and number naming convention.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes / Description</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Additional details about this opname..."></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>System Note:</strong> Starting an opname will automatically create a snapshot of all active tools in the master inventory for verification.
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('tool-lending.stock-opname.index') }}" class="btn btn-light me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Initialize Opname</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
