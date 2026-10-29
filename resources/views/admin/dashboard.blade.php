@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-chart-line" style="color: var(--bancha);"></i> Dashboard Admin</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Pantau analitis penjualan, pengeluaran, laba bersih, dan produk terlaris <strong>{{ $settings['store_name'] ?? 'toko Anda' }}</strong>.</p>
</div>

<!-- Expiration Warning Alert Box -->
@if(($settings['enable_expiration'] ?? '1') == '1' && $expirationAlerts->count() > 0)
    <div class="card" style="border-left: 5px solid var(--terracotta); margin-bottom: 2rem; background-color: var(--warning-bg);">
        <h3 style="color: var(--terracotta); display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
            <i class="fa-solid fa-triangle-exclamation"></i> Pengingat Kadaluarsa Produk ({{ $expirationAlerts->count() }})
        </h3>
        <p style="font-size: 0.9rem; color: var(--text-dark); margin-bottom: 1rem;">
            Beberapa produk sudah mendekati tanggal kadaluarsa (kurang dari 30 hari) atau sudah kadaluarsa. Silakan periksa barang di rak.
        </p>
        <div class="table-responsive">
            <table style="background: var(--bg-white); border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th>Sisa Stok</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expirationAlerts as $product)
                        @php
                            $daysLeft = now()->diffInDays($product->expiration_date, false);
                        @endphp
                        <tr>
                            <td style="font-weight: 600; color: var(--olivenite);">{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td><strong>{{ $product->stock }}</strong> pcs</td>
                            <td style="font-family: monospace;">{{ $product->expiration_date->format('d M Y') }}</td>
                            <td>
                                @if($daysLeft < 0)
                                    <span class="badge badge-danger">Sudah Kadaluarsa</span>
                                @elseif($daysLeft <= 7)
                                    <span class="badge badge-danger">Bahaya ({{ $daysLeft }} hari lagi)</span>
                                @else
                                    <span class="badge badge-warning">Peringatan ({{ $daysLeft }} hari lagi)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- Financial Metrics Grid -->
<div class="metrics-grid">
    <!-- Harian -->
    <div class="metric-card" style="border-top: 4px solid var(--bancha);">
        <span class="metric-title">Pemasukan Hari Ini</span>
        <span class="metric-val" id="daily-income-val">Rp {{ number_format($dailyIncome, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--success);">
            <i class="fa-solid fa-arrow-trend-up"></i> Keuntungan: <span id="daily-profit-val">Rp {{ number_format($dailyProfit, 0, ',', '.') }}</span>
        </span>
    </div>
    <!-- Bulanan & Laba Bersih -->
    <div class="metric-card" style="border-top: 4px solid var(--olivenite);">
        <span class="metric-title">Pemasukan Bulan Ini</span>
        <span class="metric-val" id="monthly-income-val">Rp {{ number_format($monthlyIncome, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--success);">
            <i class="fa-solid fa-calculator"></i> Laba Kotor: <span id="monthly-profit-val">Rp {{ number_format($monthlyProfit, 0, ',', '.') }}</span>
        </span>
    </div>
    <!-- Pengeluaran Toko -->
    <div class="metric-card" style="border-top: 4px solid #ef4444;">
        <span class="metric-title">Pengeluaran Bulan Ini</span>
        <span class="metric-val" style="color: #dc2626;" id="monthly-expenses-val">Rp {{ number_format($monthlyExpenses, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--text-muted);">
            <i class="fa-solid fa-receipt"></i> Total Operasional
        </span>
    </div>
    <!-- Laba Bersih (Net Profit) -->
    <div class="metric-card" style="border-top: 4px solid #16a34a; background: #f0fdf4;">
        <span class="metric-title" style="color: #15803d; font-weight: 700;">Laba Bersih Bulan Ini</span>
        <span class="metric-val" style="color: #16a34a;" id="monthly-net-val">Rp {{ number_format($monthlyNetProfit, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: #15803d;">
            <i class="fa-solid fa-piggy-bank"></i> Laba Kotor - Pengeluaran
        </span>
    </div>
</div>

<style>
    .dashboard-charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 992px) {
        .dashboard-charts-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-charts-grid">
    <!-- Financial Trend Chart -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-chart-area" style="color: var(--bancha);"></i> Tren Keuangan 7 Hari Terakhir</h3>
        </div>
        <div style="height: 300px; position: relative;">
            <canvas id="financialChart"></canvas>
        </div>
    </div>

    <!-- Top 5 Best Selling Products -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-fire" style="color: #eab308;"></i> 5 Produk Terlaris
                <span id="live-dot" title="Data real-time — diperbarui tiap 30 detik" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;margin-left:6px;vertical-align:middle;box-shadow:0 0 6px #22c55e;transition:background 0.3s;"></span>
            </h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th style="text-align: center;">Terjual</th>
                        <th style="text-align: right;">Total Omset</th>
                    </tr>
                </thead>
                <tbody id="top-products-tbody">
                    @forelse($topSellingProducts as $index => $item)
                        <tr>
                            <td style="font-weight: 700; color: var(--bancha);">{{ $index + 1 }}</td>
                            <td style="font-weight: 600;">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $item->product && $item->product->category ? $item->product->category->name : '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge badge-info">{{ $item->total_qty }} pcs</span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: var(--olivenite);">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">Belum ada data penjualan produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 5 Transaksi Terakhir -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-header">
        <h3><i class="fa-solid fa-clock-rotate-left" style="color: var(--bancha);"></i> 5 Transaksi Terakhir
            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#22c55e;margin-left:6px;vertical-align:middle;box-shadow:0 0 6px #22c55e;"></span>
        </h3>
        <a href="{{ route('admin.transactions') }}" class="btn btn-secondary btn-sm" style="font-size: 0.75rem;">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Struk</th>
                    <th>Kasir</th>
                    <th>Metode Pembayaran</th>
                    <th>Total Belanja</th>
                    <th>Keuntungan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody id="recent-transactions-tbody">
                @forelse($recentTransactions as $tx)
                    <tr>
                        <td>
                            <a href="{{ route('admin.transactions.show', $tx->id) }}" style="font-weight: 700; font-family: monospace; color: var(--olivenite);">
                                {{ $tx->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $tx->user->name }}</td>
                        <td><span class="badge badge-info">{{ $tx->payment_method }}</span></td>
                        <td style="font-weight: 700; color: var(--olivenite);">Rp {{ number_format($tx->total_price, 0, ',', '.') }}</td>
                        <td style="font-weight: 700; color: var(--success);">Rp {{ number_format($tx->profit, 0, ',', '.') }}</td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $tx->created_at->format('d M, H:i') }} WIB</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">Belum ada transaksi recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ── Helper: format angka ke Rupiah ──
    function toRp(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    // ── Inisialisasi Chart ──
    const ctx = document.getElementById('financialChart').getContext('2d');
    const chartData = @json($chartData);

    const financialChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Pemasukan (Gross)',
                    data: chartData.income,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Keuntungan Kotor',
                    data: chartData.profit,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Pengeluaran',
                    data: chartData.expenses,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 500 },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { family: 'Outfit' } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        },
                        font: { family: 'Outfit' }
                    }
                },
                x: {
                    ticks: { font: { family: 'Outfit' } }
                }
            }
        }
    });

    // ── Fungsi update tabel 5 Produk Terlaris ──
    function renderTopProducts(products) {
        const tbody = document.getElementById('top-products-tbody');
        if (!tbody) return;
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);">Belum ada data penjualan produk.</td></tr>';
            return;
        }
        tbody.innerHTML = products.map((item, i) => `
            <tr>
                <td style="font-weight:700;color:var(--bancha);">${i+1}</td>
                <td style="font-weight:600;">${item.name}</td>
                <td style="font-size:0.85rem;color:var(--text-muted);">${item.category}</td>
                <td style="text-align:center;"><span class="badge badge-info">${item.qty} pcs</span></td>
                <td style="text-align:right;font-weight:700;color:var(--olivenite);">${toRp(item.revenue)}</td>
            </tr>
        `).join('');
    }

    // ── Fungsi update tabel 5 Transaksi Terakhir ──
    function renderRecentTransactions(transactions) {
        const tbody = document.getElementById('recent-transactions-tbody');
        if (!tbody) return;
        if (transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text-muted);">Belum ada transaksi recorded.</td></tr>';
            return;
        }
        tbody.innerHTML = transactions.map(tx => `
            <tr>
                <td><a href="${tx.show_url}" style="font-weight:700;font-family:monospace;color:var(--olivenite);">${tx.invoice}</a></td>
                <td>${tx.cashier}</td>
                <td><span class="badge badge-info">${tx.method}</span></td>
                <td style="font-weight:700;color:var(--olivenite);">${toRp(tx.total)}</td>
                <td style="font-weight:700;color:var(--success);">${toRp(tx.profit)}</td>
                <td style="font-size:0.85rem;color:var(--text-muted);">${tx.time}</td>
            </tr>
        `).join('');
    }

    // ── Indikator live ──
    function setLiveIndicator(loading) {
        const dot = document.getElementById('live-dot');
        if (!dot) return;
        dot.style.background = loading ? '#f59e0b' : '#22c55e';
        dot.title = loading ? 'Memperbarui...' : 'Data real-time — diperbarui tiap 30 detik';
    }

    // ── Fungsi utama: ambil data terbaru dari server ──
    function refreshDashboard() {
        setLiveIndicator(true);
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            // Update metric cards
            document.getElementById('daily-income-val').textContent   = toRp(data.dailyIncome);
            document.getElementById('daily-profit-val').textContent   = toRp(data.dailyProfit);
            document.getElementById('monthly-income-val').textContent = toRp(data.monthlyIncome);
            document.getElementById('monthly-profit-val').textContent = toRp(data.monthlyProfit);
            document.getElementById('monthly-expenses-val').textContent = toRp(data.monthlyExpenses);
            document.getElementById('monthly-net-val').textContent    = toRp(data.monthlyNetProfit);

            // Update tabel
            renderTopProducts(data.topSellingProducts);
            renderRecentTransactions(data.recentTransactions);

            // Update chart
            financialChart.data.labels            = data.chartData.labels;
            financialChart.data.datasets[0].data  = data.chartData.income;
            financialChart.data.datasets[1].data  = data.chartData.profit;
            financialChart.data.datasets[2].data  = data.chartData.expenses;
            financialChart.update();

            setLiveIndicator(false);
        })
        .catch(() => setLiveIndicator(false));
    }

    // Auto-refresh tiap 30 detik
    setInterval(refreshDashboard, 30000);
</script>
@endsection
