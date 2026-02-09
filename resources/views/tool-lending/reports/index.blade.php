@extends('layout.mainlayout')

@section('content')
    <style>
        .report-stats-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .report-stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .report-stats-card .icon-wrapper {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .report-stats-card h3 {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .report-stats-card p {
            font-size: 14px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-stats-card .trend-indicator {
            position: absolute;
            top: 24px;
            right: 24px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            background: rgba(0,0,0,0.03);
        }
        
        .elegant-chart-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }
        .elegant-chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 24px;
        }
        .elegant-chart-card .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0;
        }

        /* Colors */
        .bg-soft-primary { background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); color: #2563eb; }
        .bg-soft-success { background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%); color: #10b981; }
        .bg-soft-danger { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%); color: #ef4444; }
        .bg-soft-warning { background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%); color: #f59e0b; }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header mb-4">
                <div class="add-item d-flex">
                    <div class="page-title">
                        <h4 class="fw-bold" style="color: #0f172a;">Tools Transaction Report</h4>
                        <h6 class="text-muted">Monitoring and Analysis Dashboard</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <a href="{{ route('tool-lending.reports.history') }}" class="btn btn-primary d-flex align-items-center px-4 py-2" style="border-radius: 10px;">
                        <i class="ti ti-list-search me-2 fs-18"></i> 
                        <span class="fw-bold">Transaction History</span>
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-2">
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="report-stats-card w-100 mb-4">
                        <div class="icon-wrapper bg-soft-primary">
                            <i class="ti ti-refresh"></i>
                        </div>
                        <h3><span class="counters" data-count="{{ $stats['total_tx'] }}">{{ $stats['total_tx'] }}</span></h3>
                        <p>Total Transactions</p>
                        <div class="trend-indicator text-primary">LIVE DATA</div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="report-stats-card w-100 mb-4">
                        <div class="icon-wrapper bg-soft-success">
                            <i class="ti ti-tool"></i>
                        </div>
                        <h3><span class="counters" data-count="{{ $stats['current_borrowed'] }}">{{ $stats['current_borrowed'] }}</span></h3>
                        <p>Currently Borrowed</p>
                        <div class="trend-indicator text-success"><i class="ti ti-point-filled"></i> ACTIVE</div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="report-stats-card w-100 mb-4">
                        <div class="icon-wrapper bg-soft-danger">
                            <i class="ti ti-clock-exclamation"></i>
                        </div>
                        <h3><span class="counters" data-count="{{ $stats['overdue'] }}">{{ $stats['overdue'] }}</span></h3>
                        <p>Overdue Tools</p>
                        @if($stats['overdue'] > 0)
                            <div class="trend-indicator text-danger">URGENT</div>
                        @else
                            <div class="trend-indicator text-muted">SECURE</div>
                        @endif
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12 d-flex">
                    <div class="report-stats-card w-100 mb-4">
                        <div class="icon-wrapper bg-soft-warning">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <h3><span class="counters" data-count="{{ $stats['damaged_lost_month'] }}">{{ $stats['damaged_lost_month'] }}</span></h3>
                        <p>Damaged/Lost (Month)</p>
                        <div class="trend-indicator text-warning">INVENTORY</div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Lending Trends -->
                <div class="col-xl-7 d-flex">
                    <div class="elegant-chart-card flex-fill">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Lending Trends (Last 7 Days)</h5>
                        </div>
                        <div class="card-body">
                            <div id="lending-trends-chart"></div>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="col-xl-5 d-flex">
                    <div class="elegant-chart-card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div id="status-pie-chart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Popular Tools -->
                <div class="col-xl-6 d-flex">
                    <div class="elegant-chart-card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">Most Popular Tools</h5>
                        </div>
                        <div class="card-body">
                            <div id="popular-tools-chart"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick links / Activity -->
                <div class="col-xl-6 d-flex">
                    <div class="elegant-chart-card flex-fill">
                        <div class="card-header">
                            <h5 class="card-title">Analysis Shortcuts</h5>
                        </div>
                        <style>
                            .shortcut-item {
                                display: flex;
                                align-items: center;
                                padding: 15px;
                                border-radius: 12px;
                                background: #f8fafc;
                                border: 1px solid #f1f5f9;
                                margin-bottom: 12px;
                                transition: all 0.2s ease;
                                text-decoration: none !important;
                                color: inherit;
                            }
                            .shortcut-item:hover {
                                background: #ffffff;
                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                                transform: translateX(5px);
                                border-color: #e2e8f0;
                            }
                            .shortcut-item .icon {
                                width: 44px;
                                height: 44px;
                                border-radius: 10px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin-right: 15px;
                                font-size: 20px;
                            }
                            .shortcut-item .info h6 {
                                margin-bottom: 2px;
                                font-weight: 700;
                                font-size: 14px;
                            }
                            .shortcut-item .info span {
                                font-size: 12px;
                                color: #64748b;
                            }
                        </style>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('tool-lending.reports.history', ['filter_type' => 'frequent']) }}" class="shortcut-item">
                                        <div class="icon bg-soft-primary"><i class="ti ti-award"></i></div>
                                        <div class="info">
                                            <h6>Ranking Tools</h6>
                                            <span>Most borrowed items</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('tool-lending.reports.history', ['filter_type' => 'unreturned']) }}" class="shortcut-item">
                                        <div class="icon bg-soft-danger"><i class="ti ti-clock-pause"></i></div>
                                        <div class="info">
                                            <h6>Unreturned</h6>
                                            <span>Active loans tracker</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('tool-lending.reports.history') }}" class="shortcut-item">
                                        <div class="icon bg-soft-success"><i class="ti ti-user-search"></i></div>
                                        <div class="info">
                                            <h6>User Reports</h6>
                                            <span>Filter by NIK/Name</span>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('tool-lending.reports.history') }}" class="shortcut-item">
                                        <div class="icon bg-soft-warning"><i class="ti ti-status-change"></i></div>
                                        <div class="info">
                                            <h6>By Status</h6>
                                            <span>Damaged, Lost, Good</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ URL::asset('build/plugins/apexchart/apexcharts.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Fetch Chart Data
            $.ajax({
                url: "{{ route('tool-lending.reports.chart-data') }}",
                method: 'GET',
                success: function(data) {
                    renderTrendsChart(data.trends);
                    renderPieChart(data.status);
                    renderPopularChart(data.popular);
                }
            });

            function renderTrendsChart(trends) {
                var options = {
                    series: [{
                        name: 'Transactions',
                        data: trends.map(t => t.count)
                    }],
                    chart: {
                        height: 300,
                        type: 'area',
                        toolbar: { show: false },
                        sparkline: { enabled: false },
                        zoom: { enabled: false }
                    },
                    colors: ['#3b82f6'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: {
                        categories: trends.map(t => t.date),
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: { labels: { show: true } },
                    grid: { borderColor: '#f1f5f9' }
                };
                new ApexCharts(document.querySelector("#lending-trends-chart"), options).render();
            }

            function renderPieChart(status) {
                var options = {
                    series: status.map(s => s.count),
                    chart: {
                        height: 320,
                        type: 'donut',
                    },
                    labels: status.map(s => s.status),
                    colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    legend: { position: 'bottom', fontSize: '14px', fontWeight: 600 },
                    stroke: { width: 0 },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '75%',
                                labels: {
                                    show: true,
                                    total: { show: true, label: 'TOTAL', fontSize: '12px', fontWeight: 700 }
                                }
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#status-pie-chart"), options).render();
            }

            function renderPopularChart(popular) {
                var options = {
                    series: [{
                        name: 'Loans',
                        data: popular.map(p => p.count)
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: { show: false }
                    },
                    colors: ['#6366f1'],
                    plotOptions: {
                        bar: {
                            borderRadius: 8,
                            horizontal: true,
                            barHeight: '60%',
                        }
                    },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: popular.map(p => p.tool.name),
                        axisBorder: { show: false },
                    },
                    grid: { borderColor: '#f1f5f9' }
                };
                new ApexCharts(document.querySelector("#popular-tools-chart"), options).render();
            }
        });
    </script>
@endpush
