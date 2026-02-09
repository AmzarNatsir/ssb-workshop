@extends('layout.mainlayout')

@section('content')
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Stock Opname Tools</h4>
                <h6>Dashboard / {{ $opname->opname_no }}</h6>
            </div>
            <div class="page-btn d-flex">
                @if($opname->status === 'FINAL')
                <a href="{{ route('tool-lending.stock-opname.pdf', $opname->id) }}" class="btn btn-primary d-flex align-items-center me-2">
                    <i class="ti ti-file-type-pdf me-2"></i>Download PDF
                </a>
                @else
                <button type="button" onclick="finalizeOpname()" class="btn btn-success d-flex align-items-center">
                    <i class="ti ti-check me-2"></i>Finalisasi Laporan
                </button>
                @endif
            </div>
        </div>

        <!-- Opname Header Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">Nomor Opname:</p>
                                <h5 class="mb-3">{{ $opname->opname_no }}</h5>
                                <p class="mb-1 text-muted">Periode Opname:</p>
                                <h5 class="mb-0">{{ $opname->period }} ({{ \Carbon\Carbon::parse($opname->start_date)->format('F Y') }})</h5>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 text-muted">PIC:</p>
                                <h5 class="mb-3">{{ $opname->creator->name ?? 'Admin' }}</h5>
                                <p class="mb-1 text-muted">Status:</p>
                                <span class="badge @if($opname->status == 'FINAL') bg-success @else bg-warning @endif">
                                    {{ $opname->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="p-3 bg-light rounded text-center">
                            <h2 class="mb-0">{{ $opname->status }}</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-danger-light">
                    <div class="card-body text-center p-4">
                        <h1 class="display-4 fw-bold text-danger mb-0" id="stat-missing">{{ $stats['missing'] }}</h1>
                        <p class="text-danger fw-bold mb-0">SELISIH HILANG</p>
                        <small class="text-danger-emphasis">TIDAK ADA</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning-light">
                    <div class="card-body text-center p-4">
                        <h1 class="display-4 fw-bold text-warning mb-0" id="stat-excess">{{ $stats['excess'] }}</h1>
                        <p class="text-warning fw-bold mb-0">SELISIH BERLEBIH</p>
                        <small class="text-warning-emphasis">TIDAK ADA DI SISTEM</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-orange-light" style="background-color: rgba(255, 127, 80, 0.1);">
                    <div class="card-body text-center p-4">
                        <h1 class="display-4 fw-bold mb-0" style="color: coral;" id="stat-damaged">{{ $stats['damaged'] }}</h1>
                        <p class="fw-bold mb-0" style="color: coral;">KONDISI RUSAK</p>
                        <small style="color: coral;">RUSAK</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <select class="form-select form-select-sm me-2" style="width: 200px;" id="filter-location">
                        <option value="">Rak / Lokasi: Semua</option>
                    </select>
                    <select class="form-select form-select-sm me-2" style="width: 150px;" id="filter-exist">
                        <option value="">Status Fisik: Semua</option>
                        <option value="EXISTS">ADA</option>
                        <option value="NOT_EXISTS">HILANG</option>
                    </select>
                    <select class="form-select form-select-sm" style="width: 150px;" id="filter-condition">
                        <option value="">Kondisi Fisik: Semua</option>
                        <option value="GOOD">BAIK</option>
                        <option value="DAMAGE">RUSAK</option>
                    </select>
                </div>
                <div class="page-btn">
                     <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFindingModal"><i class="ti ti-plus me-1"></i>Tambah Temuan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" id="opname-table">
                        <thead>
                            <tr>
                                <th>Kode Tools</th>
                                <th>Nama Tools</th>
                                <th>Nomor Seri</th>
                                <th>Lokasi Sesuai</th>
                                <th>Status Sistem</th>
                                <th>Status Fisik</th>
                                <th>Selisih</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($opname->details as $detail)
                            <tr data-id="{{ $detail->id }}" class="@if($detail->mismatch) table-warning @endif">
                                <td>{{ $detail->tool->code }}</td>
                                <td>{{ $detail->tool->name }}</td>
                                <td>{{ $detail->tool->serial_number ?: '-' }}</td>
                                <td>{{ $detail->physical_location ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-info-light text-info">{{ $detail->system_status }}</span>
                                    @if($detail->system_status === 'AVAILABLE')
                                        <span class="badge bg-success-light text-success">ADA</span>
                                    @endif
                                </td>
                                <td>
                                    @if($opname->status !== 'FINAL')
                                    <div class="d-flex flex-column gap-1">
                                        <select class="form-select form-select-sm update-trigger" data-field="physical_exist">
                                            <option value="EXISTS" @if($detail->physical_exist == 'EXISTS') selected @endif>ADA</option>
                                            <option value="NOT_EXISTS" @if($detail->physical_exist == 'NOT_EXISTS') selected @endif>HILANG</option>
                                        </select>
                                        <select class="form-select form-select-sm update-trigger" data-field="physical_condition">
                                            <option value="GOOD" @if($detail->physical_condition == 'GOOD') selected @endif>BAIK</option>
                                            <option value="DAMAGE" @if($detail->physical_condition == 'DAMAGE') selected @endif>RUSAK</option>
                                        </select>
                                    </div>
                                    @else
                                    <span class="badge @if($detail->physical_exist == 'EXISTS') bg-success @else bg-danger @endif">
                                        {{ $detail->physical_exist }}
                                    </span>
                                    <br>
                                    <span class="badge @if($detail->physical_condition == 'GOOD') bg-success @else bg-warning @endif mt-1">
                                        {{ $detail->physical_condition }}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($detail->mismatch)
                                        @if($detail->physical_exist === 'NOT_EXISTS')
                                            <span class="badge bg-danger">HILANG</span>
                                        @elseif($detail->physical_condition === 'DAMAGE')
                                            <span class="badge bg-warning">RUSAK</span>
                                        @endif
                                    @else
                                        <span class="badge bg-success">OK</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="checkLoanHistory({{ $detail->tool_id }})">Check History</a></li>
                                            @if($detail->mismatch && $detail->physical_exist === 'NOT_EXISTS')
                                            <li><a class="dropdown-item text-danger" href="{{ route('tool-lending.incidents.create-from-opname', ['tool_id' => $detail->tool_id, 'incident_type' => 'MISSING', 'opname_id' => $opname->id]) }}">Buat BA Hilang</a></li>
                                            @endif
                                            @if($detail->mismatch && $detail->physical_condition === 'DAMAGE')
                                            <li><a class="dropdown-item text-warning" href="{{ route('tool-lending.incidents.create-from-opname', ['tool_id' => $detail->tool_id, 'incident_type' => 'DAMAGED', 'opname_id' => $opname->id]) }}">Buat BA Rusak</a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0 d-flex justify-content-between p-3">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" onclick="checkLoanHistoryAll()">Cek Riwayat Peminjaman</button>
                    <button class="btn btn-danger btn-sm">Buat BA Hilang</button>
                    <button class="btn btn-orange btn-sm text-white" style="background-color: coral;">Buat BA Rusak</button>
                </div>
                <div>
                     @if($opname->status !== 'FINAL')
                        <button type="button" onclick="finalizeOpname()" class="btn btn-success">Finalisasi Opname ></button>
                     @else
                        <a href="{{ route('tool-lending.stock-opname.index') }}" class="btn btn-primary">Kembali ke Daftar</a>
                     @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Finding Modal -->
<div class="modal fade" id="addFindingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Temuan Alat (Excess)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tool-lending.stock-opname.add-finding', $opname->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        Gunakan fitur ini hanya jika menemukan alat secara fisik yang <strong>tidak terdaftar</strong> dalam daftar opname sistem ini.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Alat <span class="text-danger">*</span></label>
                        <select name="tool_id" class="form-select" required>
                            <option value="">-- Pilih Alat --</option>
                            @foreach($allTools as $tool)
                            <option value="{{ $tool->id }}">{{ $tool->code }} - {{ $tool->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Temuan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Ditemukan di Workshop Area/Toolbox Unit"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Temuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Finalize Form (Hidden) -->
<form id="finalize-form" action="{{ route('tool-lending.stock-opname.finalize', $opname->id) }}" method="POST" style="display: none;">
    @csrf
</form>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle dropdown changes for real-time update
        $('.update-trigger').on('change', function() {
            const row = $(this).closest('tr');
            const id = row.data('id');
            const exist = row.find('select[data-field="physical_exist"]').val();
            const condition = row.find('select[data-field="physical_condition"]').val();

            $.ajax({
                url: `{{ route('tool-lending.stock-opname.update-detail', ':id') }}`.replace(':id', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    physical_exist: exist,
                    physical_condition: condition
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        // Update Summary Cards
                        if (response.stats) {
                            $('#stat-missing').text(response.stats.missing);
                            $('#stat-excess').text(response.stats.excess);
                            $('#stat-damaged').text(response.stats.damaged);
                        }

                        // Update Row Visuals (Table Warning Class)
                        if (response.mismatch) {
                            row.addClass('table-warning');
                        } else {
                            row.removeClass('table-warning');
                        }

                        // Update Selisih Badge (Cell Index 6)
                        let badgeHtml = '<span class="badge bg-success">OK</span>';
                        if (response.mismatch) {
                            if (response.physical_exist === 'NOT_EXISTS') {
                                badgeHtml = '<span class="badge bg-danger">HILANG</span>';
                            } else if (response.physical_condition === 'DAMAGE') {
                                badgeHtml = '<span class="badge bg-warning">RUSAK</span>';
                            }
                        }
                        row.find('td').eq(6).html(badgeHtml);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update tool status.'
                    });
                }
            });
        });
    });

    function finalizeOpname() {
        Swal.fire({
            title: 'Finalisasi Laporan?',
            text: "Setelah difinalisasi, Anda tidak dapat mengubah data opname ini lagi.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Finalisasi!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#finalize-form').submit();
            }
        });
    }

    function checkLoanHistory(toolId) {
        // Implementation for checking loan history (could be a modal or redirect)
        window.location.href = `{{ route('tool-lending.loans.history') }}?tool_id=${toolId}`;
    }
</script>
@endpush
