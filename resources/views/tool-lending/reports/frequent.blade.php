@extends('layout.mainlayout')

@section('content')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4>Frequently Borrowed Tools</h4>
                        <h6>Ranking and Utilization Analysis</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('tool-lending.reports.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Ranking Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Tool Info</th>
                                    <th>Total Loans</th>
                                    <th>Last Borrowed</th>
                                    <th>Current Status</th>
                                    <th>Utilization</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($frequentTools as $index => $item)
                                    <tr>
                                        <td>
                                            @if($index == 0) <span class="badge bg-warning">#1</span>
                                            @elseif($index == 1) <span class="badge bg-secondary">#2</span>
                                            @elseif($index == 2) <span class="badge bg-bronze" style="background-color: #cd7f32;">#3</span>
                                            @else #{{ $index + 1 }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="ms-2">
                                                    <span class="d-block fw-bold">{{ $item->tool->name }}</span>
                                                    <small class="text-muted">{{ $item->tool->code }} | {{ $item->tool->brand }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info">{{ $item->total_loans }} Loans</span></td>
                                        <td>{{ $item->tool->loanTransactions()->latest('loan_date')->first()?->loan_date->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $item->tool->status->name === 'AVAILABLE' ? 'bg-success' : 'bg-warning' }}">
                                                {{ $item->tool->status->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 5px; width: 100px;">
                                                @php $percent = min(($item->total_loans / 50) * 100, 100); @endphp
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ round($percent) }}% Popularity</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $frequentTools->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
