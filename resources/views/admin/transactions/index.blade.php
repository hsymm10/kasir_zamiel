@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;" class="no-print">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-receipt" style="color: var(--bancha);"></i> Laporan Transaksi</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Daftar transaksi penjualan <strong>{{ $settings['store_name'] ?? 'toko Anda' }}</strong> secara transparan.</p>
    </div>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="{{ route('admin.transactions.export') }}" class="btn btn-secondary" id="btn-export-excel">
            <i class="fa-solid fa-file-excel"></i> Unduh Excel
        </a>
        <button type="button" class="btn btn-secondary" id="btn-print-pdf" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- Print Header (only shown on print / PDF) -->
<div class="print-only" style="display: none; margin-bottom: 1.5rem; text-align: center;">
    <div style="font-size: 1.5rem; font-weight: 800; text-transform: uppercase; color: #111;">{{ $settings['store_name'] ?? 'Kasir Zamiel' }}</div>
    <div style="font-size: 0.85rem; color: #444; margin-top: 2px;">{{ $settings['store_address'] ?? 'Alamat Toko' }} | Telp: {{ $settings['store_phone'] ?? '-' }}</div>
    <div style="font-size: 0.85rem; font-weight: 700; color: #222; margin-top: 6px; text-transform: uppercase;">LAPORAN PENJUALAN DAN KEUNTUNGAN</div>
    <div style="font-size: 0.75rem; color: #666;">Dicetak pada: {{ now()->format('d F Y, H:i') }} WIB</div>
    <hr style="margin: 0.75rem 0; border: 0; border-top: 2px solid #222;">

    <div style="display: flex; justify-content: space-around; background: #f8f9fa; padding: 0.6rem; border: 1px solid #ccc; margin-bottom: 1rem; border-radius: 4px; font-size: 0.85rem;">
        <div>Total Transaksi: <strong style="color:#111;">{{ $totalCount }} Transaksi</strong></div>
        <div>Total Belanja: <strong style="color:#111;">Rp {{ number_format($totalSales, 0, ',', '.') }}</strong></div>
        <div>Total Keuntungan: <strong style="color:#16a34a;">Rp {{ number_format($totalProfit, 0, ',', '.') }}</strong></div>
    </div>
</div>

<!-- Transactions Table Card -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:48px; text-align:center;">No</th>
                    <th>No. Struk</th>
                    <th>Nama Kasir</th>
                    <th>Metode</th>
                    <th>Total Belanja</th>
                    <th>Keuntungan (Profit)</th>
                    <th>Tanggal &amp; Waktu</th>
                    <th class="no-print" style="width: 120px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td style="text-align:center; font-weight:700; color:var(--text-muted); font-size:0.85rem;">
                            {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <span style="font-weight: 700; font-family: monospace; color: var(--olivenite);">
                                {{ $tx->invoice_number }}
                            </span>
                        </td>
                        <td>{{ $tx->user->name }}</td>
                        <td>
                            <span class="badge badge-info" style="font-size: 0.7rem;">{{ $tx->payment_method }}</span>
                        </td>
                        <td style="font-weight: 700; color: var(--olivenite);">
                            Rp {{ number_format($tx->total_price, 0, ',', '.') }}
                        </td>
                        <td style="font-weight: 700; color: var(--success);">
                            Rp {{ number_format($tx->profit, 0, ',', '.') }}
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.9rem;">
                            {{ $tx->created_at->format('d M Y, H:i') }} WIB
                        </td>
                        <td class="no-print">
                            <div style="display: flex; justify-content: center;">
                                <a href="{{ route('admin.transactions.show', $tx->id) }}" class="btn btn-secondary btn-sm">
                                    <i class="fa-solid fa-circle-info"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada riwayat transaksi masuk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($transactions->hasPages())
    <div class="no-print" id="pagination-wrapper" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-top: 1px solid var(--border, #e5e7eb); flex-wrap: wrap; gap: 0.5rem;">
        <div style="font-size: 0.875rem; color: var(--text-muted);">
            Menampilkan <strong>{{ $transactions->firstItem() }}</strong>–<strong>{{ $transactions->lastItem() }}</strong>
            dari <strong>{{ $transactions->total() }}</strong> transaksi
        </div>
        <div class="pagination-links" style="display: flex; align-items: center; gap: 0.25rem;">
            {{-- Previous --}}
            @if($transactions->onFirstPage())
                <span class="page-btn page-btn-disabled" aria-disabled="true">&laquo;</span>
            @else
                <a href="{{ $transactions->previousPageUrl() }}" class="page-btn" aria-label="Sebelumnya">&laquo;</a>
            @endif

            {{-- Page numbers --}}
            @php
                $currentPage = $transactions->currentPage();
                $lastPage    = $transactions->lastPage();
                $start       = max(1, $currentPage - 2);
                $end         = min($lastPage, $currentPage + 2);
            @endphp

            @if($start > 1)
                <a href="{{ $transactions->url(1) }}" class="page-btn">1</a>
                @if($start > 2)
                    <span class="page-btn page-btn-dots">…</span>
                @endif
            @endif

            @for($p = $start; $p <= $end; $p++)
                @if($p === $currentPage)
                    <span class="page-btn page-btn-active">{{ $p }}</span>
                @else
                    <a href="{{ $transactions->url($p) }}" class="page-btn">{{ $p }}</a>
                @endif
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="page-btn page-btn-dots">…</span>
                @endif
                <a href="{{ $transactions->url($lastPage) }}" class="page-btn">{{ $lastPage }}</a>
            @endif

            {{-- Next --}}
            @if($transactions->hasMorePages())
                <a href="{{ $transactions->nextPageUrl() }}" class="page-btn" aria-label="Berikutnya">&raquo;</a>
            @else
                <span class="page-btn page-btn-disabled" aria-disabled="true">&raquo;</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<style>
    /* ── Pagination Styles ──────────────────────────────── */
    .pagination-links {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        border: 1.5px solid transparent;
        transition: all 0.15s ease;
        cursor: pointer;
        color: var(--text-muted, #6b7280);
        background: transparent;
    }

    .page-btn:hover:not(.page-btn-disabled):not(.page-btn-active):not(.page-btn-dots) {
        background: var(--bancha, #3b82f6);
        color: #fff;
        border-color: var(--bancha, #3b82f6);
    }

    .page-btn-active {
        background: var(--bancha, #3b82f6);
        color: #fff !important;
        border-color: var(--bancha, #3b82f6);
        cursor: default;
    }

    .page-btn-disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    .page-btn-dots {
        cursor: default;
        letter-spacing: 1px;
    }

    /* ── Print Styles ───────────────────────────────────── */
    @media print {
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        html, body {
            background: white !important;
            color: black !important;
            font-family: system-ui, -apple-system, sans-serif !important;
            font-size: 10pt !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Hide all layout frame elements */
        header, footer, nav, aside,
        .admin-sidebar,
        .mobile-header,
        .app-topbar,
        .no-print,
        .sidebar,
        .nav,
        .btn-checkout,
        #pagination-wrapper {
            display: none !important;
        }

        .print-only {
            display: block !important;
        }

        .admin-layout, .main-content {
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            background: transparent !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            font-size: 9pt !important;
            border-collapse: collapse !important;
            margin-top: 0.5rem;
        }

        th, td {
            border: 1px solid #888 !important;
            padding: 5px 8px !important;
            text-align: left !important;
            color: black !important;
        }

        thead th {
            background: #eee !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .badge {
            border: none !important;
            background: transparent !important;
            color: black !important;
            font-size: 9pt !important;
            padding: 0 !important;
        }
    }
</style>
@endsection
