@extends('layouts.app')

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <p class="text-[11px] uppercase tracking-[0.18em] text-slate-400 mb-1">Overview</p>
            <h1 class="text-xl md:text-2xl font-semibold tracking-tight text-slate-50">Duka Dashboard</h1>
            <p class="text-xs text-slate-400 mt-1">Today&apos;s performance across sales, deni, and inventory.</p>
        </div>
        <div class="hidden md:flex items-center space-x-2 text-[11px] text-slate-400">
            <span>Dashboard</span>
            <span class="text-slate-500">/</span>
            <span class="text-cyan-300">Overview</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid gap-4 md:gap-6 md:grid-cols-4">
        <!-- Metrics -->
        <div class="md:col-span-4 grid gap-3 md:grid-cols-4">
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <p class="text-[11px] text-slate-400 mb-1">Today&apos;s Sales</p>
                <p class="text-lg md:text-xl font-semibold tracking-tight text-emerald-300">
                    {{ number_format($metrics['today_sales'] ?? 0, 2) }} KES
                </p>
                <p class="text-[11px] text-slate-500 mt-1">Across {{ $metrics['today_transactions'] ?? 0 }} transactions</p>
            </div>
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <p class="text-[11px] text-slate-400 mb-1">Outstanding Deni</p>
                <p class="text-lg md:text-xl font-semibold tracking-tight text-rose-300">
                    {{ number_format($metrics['total_debt'] ?? 0, 2) }} KES
                </p>
                <p class="text-[11px] text-slate-500 mt-1">Total customer credit</p>
            </div>
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <p class="text-[11px] text-slate-400 mb-1">Top Product</p>
                <p class="text-xs font-semibold tracking-tight text-slate-50 line-clamp-2">
                    {{ $metrics['top_product']['name'] ?? '—' }}
                </p>
                <p class="text-[11px] text-slate-500 mt-1">
                    Units sold: {{ $metrics['top_product']['quantity'] ?? 0 }}
                </p>
            </div>
            <div class="glass-panel px-4 py-3 md:px-5 md:py-4">
                <p class="text-[11px] text-slate-400 mb-1">Payment Mix</p>
                <p class="text-xs text-slate-300">
                    Cash / M-PESA / Credit
                </p>
                <p class="text-[11px] text-slate-500 mt-1">
                    {{ $metrics['payments']['cash'] ?? 0 }} / {{ $metrics['payments']['mpesa'] ?? 0 }} / {{ $metrics['payments']['credit'] ?? 0 }}
                </p>
            </div>
        </div>

        <!-- Charts -->
        <div class="md:col-span-2 glass-panel px-4 py-3 md:px-5 md:py-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Sales Timeline</p>
                <p class="text-[11px] text-slate-500">Today</p>
            </div>
            <div id="sales-chart" class="h-56 md:h-64"></div>
        </div>

        <div class="md:col-span-2 glass-panel px-4 py-3 md:px-5 md:py-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-400">Payment Methods</p>
                <p class="text-[11px] text-slate-500">Last 7 days</p>
            </div>
            <div id="payment-chart" class="h-56 md:h-64"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const salesData = @json($charts['sales'] ?? ['labels' => [], 'series' => []]);
                const paymentData = @json($charts['payments'] ?? ['labels' => [], 'series' => []]);

                const salesEl = document.querySelector('#sales-chart');
                const paymentEl = document.querySelector('#payment-chart');

                if (salesEl && salesData.series && salesData.series.length) {
                    const salesChart = new ApexCharts(salesEl, {
                        chart: {
                            type: 'area',
                            height: '100%',
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            foreColor: '#9ca3af',
                            background: 'transparent',
                        },
                        theme: { mode: 'dark' },
                        colors: ['#22d3ee'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        grid: {
                            borderColor: 'rgba(148, 163, 184, 0.15)',
                            yaxis: { lines: { show: true } },
                        },
                        xaxis: {
                            categories: salesData.labels || [],
                            labels: { style: { colors: '#64748b' } },
                        },
                        yaxis: {
                            labels: {
                                formatter: (value) => value.toLocaleString('en-KE'),
                            },
                        },
                        series: salesData.series,
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 0.6,
                                opacityFrom: 0.35,
                                opacityTo: 0,
                                stops: [0, 90, 100],
                            },
                        },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (value) => 'KES ' + value.toLocaleString('en-KE'),
                            },
                        },
                    });

                    salesChart.render();
                }

                if (paymentEl && paymentData.series && paymentData.series.length) {
                    const paymentChart = new ApexCharts(paymentEl, {
                        chart: {
                            type: 'donut',
                            height: '100%',
                            toolbar: { show: false },
                            foreColor: '#9ca3af',
                            background: 'transparent',
                        },
                        theme: { mode: 'dark' },
                        labels: paymentData.labels || [],
                        series: paymentData.series || [],
                        colors: ['#22c55e', '#22d3ee', '#fb7185'],
                        legend: {
                            position: 'bottom',
                            labels: { colors: '#9ca3af' },
                        },
                        stroke: { colors: ['#020617'] },
                        dataLabels: { enabled: false },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (value) => value.toLocaleString('en-KE'),
                            },
                        },
                    });

                    paymentChart.render();
                }
            });
        </script>
    @endpush
@endsection