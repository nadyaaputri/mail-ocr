@extends('layout.main')

@push('style')
    {{-- Memuat CSS ApexCharts --}}
    <link rel="stylesheet" href="{{asset('sneat/vendor/libs/apex-charts/apex-charts.css')}}" />

    {{-- CSS Kustom untuk tema dan gaya kartu --}}
    <style>
        .layout-page {
            background-color: #f0f2f8 !important;
        }
        .content-wrapper {
            background-color: transparent !important;
        }
        .card {
            border: none;
            border-radius: 0.8rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
            background-color: rgba(255, 255, 255, 0.95) !important;
        }
        .card-header {
            background-color: transparent;
            border-bottom: none;
            padding: 1.5rem 1.5rem 0 1.5rem;
        }
        .card-title {
            font-weight: 600;
            color: #566a7f;
        }
        .stat-card { text-align: center; padding: 1rem; }
        .stat-card .stat-icon { font-size: 2.5rem; line-height: 1; margin-bottom: 0.5rem; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; }
        .stat-card .stat-label { font-size: 0.8rem; color: #a1acb8; text-transform: uppercase; }
    </style>
@endpush

@push('script')
    {{-- Memuat JS ApexCharts --}}
    <script src="{{asset('sneat/vendor/libs/apex-charts/apexcharts.js')}}"></script>
    <script>
        // ======================================================================
        //          KONFIGURASI GRAFIK AREA FUTURISTIK
        // ======================================================================
        const mainChartOptions = {
            chart: {
                height: 350,
                type: 'area',
                toolbar: { show: false },
                zoom: { enabled: false },
                dropShadow: {
                    enabled: true,
                    top: 5,
                    left: 0,
                    blur: 10,
                    color: '#696cff',
                    opacity: 0.3
                }
            },
            series: [{
                name: '{{ __('dashboard.letter_transaction') }}',
                data: [{{ $todayIncomingLetter }}, {{ $todayOutgoingLetter }}, {{ $todayDispositionLetter }}]
            }],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: "vertical",
                    shadeIntensity: 0.5,
                    gradientToColors: ['#696cff', '#ab72ff'],
                    inverseColors: true,
                    opacityFrom: 0.8,
                    opacityTo: 0.2,
                    stops: [0, 90, 100]
                }
            },
            grid: { show: false },
            xaxis: {
                categories: ['Surat Masuk', 'Surat Keluar', 'Disposisi'],
                labels: { style: { colors: '#a1acb8', fontSize: '13px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#a1acb8', fontSize: '13px' },
                    formatter: (val) => parseInt(val)
                }
            },
            tooltip: {
                theme: 'dark',
                x: { show: false }
            },
            colors: ['#7c5cff']
        };
        const mainChart = new ApexCharts(document.querySelector("#main-chart"), mainChartOptions);
        mainChart.render();

        // ======================================================================
        //        KONFIGURASI GRAFIK RADIAL BAR FUTURISTIK
        // ======================================================================
        const statusChartOptions = {
            chart: {
                height: 350,
                type: 'radialBar',
            },
            series: [
                ({{ $statusNew }} / ({{ $statusNew + $statusProcessed + $statusDone }} || 1)) * 100,
                ({{ $statusProcessed }} / ({{ $statusNew + $statusProcessed + $statusDone }} || 1)) * 100,
                ({{ $statusDone }} / ({{ $statusNew + $statusProcessed + $statusDone }} || 1)) * 100
            ],
            plotOptions: {
                radialBar: {
                    offsetY: 0,
                    startAngle: 0,
                    endAngle: 270,
                    hollow: {
                        margin: 5,
                        size: '30%',
                        background: 'transparent',
                    },
                    track: {
                        background: '#f0f2f8',
                        strokeWidth: '100%',
                    },
                    dataLabels: {
                        name: { show: false },
                        value: { show: false },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '18px',
                            fontWeight: 600,
                            color: '#566a7f',
                            formatter: function (w) {
                                return {{ $statusNew + $statusProcessed + $statusDone }};
                            }
                        }
                    }
                }
            },
            colors: ['#ff4d4f', '#ffc107', '#28a745'], // Merah, Kuning, Hijau
            labels: ['Baru', 'Diproses', 'Selesai'],
            legend: {
                show: true,
                floating: true,
                fontSize: '14px',
                position: 'left',
                offsetX: 50,
                offsetY: 15,
                labels: { useSeriesColors: true },
                markers: { size: 0 },
                formatter: function(seriesName, opts) {
                    return seriesName + ":  " + {{ '['.$statusNew.','.$statusProcessed.','.$statusDone.']' }}[opts.seriesIndex]
                },
                itemMargin: { vertical: 3 }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    legend: { show: false }
                }
            }]
        };
        const statusChart = new ApexCharts(document.querySelector("#status-chart"), statusChartOptions);
        statusChart.render();
    </script>
@endpush

@section('content')
    {{-- Judul Halaman --}}
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Home /</span> Dashboard
    </h4>

    {{-- STRUKTUR BARU YANG LEBIH RAPI --}}
    <div class="row">

        <!-- Kartu Ucapan Selamat Datang -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h4 class="card-title text-primary">{{ $greeting }}!</h4>
                            <p class="mb-2">{{ $currentDate }}</p>
                            <a href="{{ route('transaction.incoming.create') }}" class="btn btn-sm btn-primary">Buat Surat Baru</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="col-12 mb-4">
             <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Hari Ini</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="stat-card">
                                <div class="stat-icon text-success"><i class="bx bx-log-in-circle"></i></div>
                                <div class="stat-value">{{ $todayIncomingLetter }}</div>
                                <div class="stat-label">Surat Masuk</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="stat-card">
                                <div class="stat-icon text-danger"><i class="bx bx-log-out-circle"></i></div>
                                <div class="stat-value">{{ $todayOutgoingLetter }}</div>
                                <div class="stat-label">Surat Keluar</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="stat-card">
                                <div class="stat-icon text-primary"><i class="bx bx-transfer-alt"></i></div>
                                <div class="stat-value">{{ $todayDispositionLetter }}</div>
                                <div class="stat-label">Disposisi</div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="stat-card">
                                <div class="stat-icon text-info"><i class="bx bx-user-check"></i></div>
                                <div class="stat-value">{{ $activeUser }}</div>
                                <div class="stat-label">User Aktif</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baris untuk Dua Grafik -->
        <div class="col-12">
            <div class="row">
                <!-- Grafik Utama -->
                 <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Grafik Transaksi Hari Ini</h5>
                        </div>
                        <div class="card-body d-flex align-items-center">
                            <div id="main-chart" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
                <!-- Grafik Status Surat -->
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ringkasan Status Surat</h5>
                        </div>
                        <div class="card-body">
                            <div id="status-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
