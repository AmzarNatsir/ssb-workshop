<?php $page = 'tool-cards'; ?>
@extends('layout.mainlayout')
@section('content')

    <div class="page-wrapper">
        <div class="content">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-4">
                <div>
                    <h4 class="mb-1">Review Tool Card</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('tool-cards.index') }}">Tool Cards</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $toolCard->uid }}</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <!-- Card Preview -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Card Preview</h5>
                        </div>
                        <div class="card-body d-flex justify-content-center">
                            <!-- Simulated Print View -->
                            <div class="border rounded p-3 text-center" style="width: 300px; background: #fff;">
                                <img src="{{ asset('assets/img/logo.png') }}" class="mb-2" style="height: 30px;" alt="Logo"/>
                                <h5 class="fw-bold mb-3">TOOL ACCESS CARD</h5>
                                
                                <div class="mb-3">
                                    <!-- Placeholder Photo -->
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 120px;">
                                        <i class="ti ti-user fs-1 text-muted"></i>
                                    </div>
                                </div>
                                
                                <h6 class="fw-bold mb-0">{{ $toolCard->employee->name }}</h6>
                                <small class="text-muted">{{ $toolCard->employee->nik }}</small>
                                
                                <div class="my-3">
                                    @if($toolCard->access_level == '1')
                                        <h2 class="fw-bold text-secondary">LEVEL 1</h2>
                                        <span class="badge bg-secondary">Basic Access</span>
                                    @elseif($toolCard->access_level == '2')
                                        <h2 class="fw-bold text-primary">LEVEL 2</h2>
                                        <span class="badge bg-primary">Standard Access</span>
                                    @elseif($toolCard->access_level == '3')
                                        <h2 class="fw-bold text-danger">LEVEL 3</h2>
                                        <span class="badge bg-danger">Full Access</span>
                                    @endif
                                </div>

                                <div class="mb-2">
                                    <!-- QR/Barcode Generation -->
                                    @if($toolCard->code_type == 'BARCODE')
                                        <div class="d-flex justify-content-center bg-white p-2">
                                            {!! Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($toolCard->uid, 'C128', 1.5, 50) !!}
                                        </div>
                                    @else
                                        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate($toolCard->uid) !!}
                                    @endif
                                </div>
                                <small class="text-muted d-block">{{ $toolCard->uid }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info & Actions -->
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <label class="col-sm-4 text-muted">Status</label>
                                <div class="col-sm-8 fw-semibold">
                                    @if($toolCard->status == 'DRAFT') <span class="badge bg-secondary">Draft</span>
                                    @elseif($toolCard->status == 'SUBMITTED') <span class="badge bg-info">Submitted</span>
                                    @elseif($toolCard->status == 'APPROVED_WSP') <span class="badge bg-primary">Approved WSP</span>
                                    @elseif($toolCard->status == 'APPROVED_FINAL') <span class="badge bg-success">Approved Final</span>
                                    @elseif($toolCard->status == 'REJECTED') <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-4 text-muted">Created By</label>
                                <div class="col-sm-8">{{ $toolCard->creator->name }} <small class="text-muted">({{ $toolCard->created_at->format('d M Y H:i') }})</small></div>
                            </div>
                            <div class="row mb-3">
                                <label class="col-sm-4 text-muted">Allowed Categories</label>
                                <div class="col-sm-8">
                                    @if($toolCard->tool_categories)
                                        @foreach($toolCard->tool_categories as $catId)
                                            <!-- Assuming catId maps to something, simply showing ID for now due to complexity -->
                                            <span class="badge bg-light text-dark border me-1">{{ \App\Models\common\ToolType::find($catId)->name ?? 'Type '.$catId }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval History -->
                    <div class="card border-0 shadow-sm mb-4">
                         <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0">Approval History</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($toolCard->approvals as $approval)
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-0">{{ $approval->approver->name }}</h6>
                                                <small class="text-muted">
                                                    Level {{ $approval->level }} 
                                                    @if($approval->level == 1) (WSP Manager) @elseif($approval->level == 2) (KA Plan) @endif
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge {{ $approval->status == 'APPROVED' ? 'bg-success' : 'bg-danger' }}">{{ $approval->status }}</span>
                                                <small class="d-block text-muted">{{ $approval->created_at->format('d M H:i') }}</small>
                                            </div>
                                        </div>
                                        @if($approval->notes)
                                            <div class="mt-2 p-2 bg-light rounded small text-muted">
                                                "{{ $approval->notes }}"
                                            </div>
                                        @endif
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-3">No approvals yet.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 justify-content-end">
                        @if($toolCard->status == 'DRAFT' || $toolCard->status == 'REJECTED')
                            <button class="btn btn-primary" onclick="submitCard()">Submit for Approval</button>
                        @endif

                        <!-- Simulation of Approver Actions -->
                        <!-- In real app, check Auth::user()->role or permission. Here we simulate for demo -->
                        @if($toolCard->status == 'SUBMITTED' || $toolCard->status == 'APPROVED_WSP')
                             <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="ti ti-check me-1"></i>Approve (Simulate)
                            </button>
                             <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="ti ti-x me-1"></i>Reject
                            </button>
                        @endif

                        @if($toolCard->status == 'APPROVED_FINAL')
                            <a href="{{ route('tool-cards.print', $toolCard->id) }}" target="_blank" class="btn btn-dark">
                                <i class="ti ti-printer me-1"></i>Print Card
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @component('components.footer')
        @endcomponent
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Tool Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="approve-notes" placeholder="Notes (optional)..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="approveCard()">Confirm Approval</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Reject Tool Card</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                     <textarea class="form-control" id="reject-notes" placeholder="Reason for rejection (required)..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="rejectCard()">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function showNotification(message, type = 'success') {
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
            });
            Toast.fire({ icon: type, title: message });
        }

        function submitCard() {
            Swal.fire({
                title: 'Submit for Approval?',
                text: "The approval workflow will start.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('{{ route("tool-cards.submit", $toolCard->id) }}', { _token: '{{ csrf_token() }}' })
                    .done(function(res) {
                        showNotification(res.message);
                        setTimeout(() => location.reload(), 1000);
                    })
                    .fail(function(xhr) { showNotification(xhr.responseJSON?.message, 'error'); });
                }
            });
        }

        function approveCard() {
            const notes = $('#approve-notes').val();
            $.post('{{ route("tool-cards.approve", $toolCard->id) }}', { _token: '{{ csrf_token() }}', notes: notes })
            .done(function(res) {
                $('#approveModal').modal('hide');
                showNotification(res.message);
                setTimeout(() => location.reload(), 1000);
            })
            .fail(function(xhr) { showNotification(xhr.responseJSON?.message, 'error'); });
        }

        function rejectCard() {
            const notes = $('#reject-notes').val();
            if(!notes) { showNotification('Please provide a reason', 'error'); return; }
            
            $.post('{{ route("tool-cards.reject", $toolCard->id) }}', { _token: '{{ csrf_token() }}', notes: notes })
            .done(function(res) {
                $('#rejectModal').modal('hide');
                showNotification(res.message);
                setTimeout(() => location.reload(), 1000);
            })
            .fail(function(xhr) { showNotification(xhr.responseJSON?.message, 'error'); });
        }
    </script>
@endpush
