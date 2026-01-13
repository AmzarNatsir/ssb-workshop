<div class="modal-header">
    <h5 class="modal-title">Job Summary: {{ $workOrder->work_order_no }}</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <!-- Header Info -->
    <div class="alert alert-light border mb-3">
        <div class="d-flex justify-content-between">
            <div>
                <strong>Unit:</strong> {{ $workOrder->equipment->code }}
            </div>
            <div>
                <strong>Total Duration:</strong> {{ $totalDuration }} Hours
            </div>
            <div>
                <strong>Status:</strong> <span class="badge bg-secondary">{{ $workOrder->status }}</span>
            </div>
        </div>
    </div>

    @if($componentChecks->count() > 0)
        <h6 class="fw-bold text-uppercase text-muted border-bottom pb-1 mb-2">Component Checks</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Description</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($componentChecks as $act)
                        @php $notes = json_decode($act->ai_notes, true); $isTrouble = $notes['trouble'] ?? false; @endphp
                        <tr>
                            <td style="width: 150px;">{{ $act->created_at->format('d M H:i') }}</td>
                            <td>{{ $act->description }}</td>
                            <td>
                                @if($isTrouble)
                                    <span class="badge bg-danger">TROUBLE</span>
                                @else
                                    <span class="badge bg-success">GOOD</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($checklistItems->count() > 0)
        <h6 class="fw-bold text-uppercase text-muted border-bottom pb-1 mb-2">Checklist Activities</h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Activity</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checklistItems as $act)
                        <tr>
                            <td style="width: 150px;">{{ $act->start_time ? \Carbon\Carbon::parse($act->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($act->end_time)->format('H:i') : '-' }}</td>
                            <td>{{ $act->description }}</td>
                            <td>{{ $act->working_duration }} hrs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($manualLogs->count() > 0)
        <h6 class="fw-bold text-uppercase text-muted border-bottom pb-1 mb-2">Other Logs</h6>
        <div class="list-group list-group-flush mb-3">
            @foreach($manualLogs as $act)
                <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between">
                        <small class="fw-bold">{{ $act->created_at->format('d M H:i') }}</small>
                        <small class="text-muted">{{ $act->working_duration }} hrs</small>
                    </div>
                    <span>{{ $act->description }}</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
