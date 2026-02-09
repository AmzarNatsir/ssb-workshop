@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="add-item d-flex">
                <div class="page-title">
                    <h4>Stock Opname Tools</h4>
                    <h6>Manage tool inventory schedules and verification</h6>
                </div>
            </div>
            <ul class="table-top-head">
                <li>
                    <a data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh"><i class="ti ti-refresh"></i></a>
                </li>
            </ul>
            <div class="page-btn">
                <a href="{{ route('tool-lending.stock-opname.create') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-plus me-2"></i>Start New Opname
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>Opname No</th>
                                <th>Period</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Tools</th>
                                <th>Status</th>
                                <th>PIC</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($opnames as $opname)
                            <tr>
                                <td><span class="fw-bold text-primary">{{ $opname->opname_no }}</span></td>
                                <td>{{ $opname->period }}</td>
                                <td>{{ \Carbon\Carbon::parse($opname->start_date)->format('d M Y') }}</td>
                                <td>{{ $opname->end_date ? \Carbon\Carbon::parse($opname->end_date)->format('d M Y') : '-' }}</td>
                                <td>{{ $opname->details_count }} tools</td>
                                <td>
                                    @if($opname->status === 'DRAFT')
                                        <span class="badge bg-secondary">DRAFT</span>
                                    @elseif($opname->status === 'IN_PROGRESS')
                                        <span class="badge bg-warning">IN PROGRESS</span>
                                    @else
                                        <span class="badge bg-success">FINAL</span>
                                    @endif
                                </td>
                                <td>{{ $opname->creator->name ?? 'Admin' }}</td>
                                <td class="action-table-data">
                                    <div class="edit-delete-action">
                                        <a class="me-2 p-2" href="{{ route('tool-lending.stock-opname.show', $opname->id) }}">
                                            <i class="ti ti-eye text-primary"></i>
                                        </a>
                                        @if($opname->status !== 'FINAL')
                                        <a class="p-2" href="{{ route('tool-lending.stock-opname.show', $opname->id) }}">
                                            <i class="ti ti-edit text-warning"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $opnames->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
