@extends('layouts.admin')

@section('title', 'Laporan Retur / Refund Barang')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-rotate-left" style="color: var(--terracotta);"></i> Laporan Retur & Refund Barang</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Pantau barang yang dikembalikan/rusak, alasan retur, kasir yang memproses, dan nominal refund tersinkronisasi.</p>
    </div>
</div>

<!-- Metrics Cards -->
<div class="metrics-grid" style="margin-bottom: 1.5rem;">
    <div class="metric-card" style="border-top: 4px solid var(--terracotta);">
        <span class="metric-title">Total Nominal Refund</span>
        <span class="metric-val" style="color: var(--terracotta);">Rp {{ number_format($totalRefundAmount, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--text-muted);">
            <i class="fa-solid fa-money-bill-wave"></i> Total Uang Dikembalikan
        </span>
    </div>
    <div class="metric-card" style="border-top: 4px solid var(--bancha);">
        <span class="metric-title">Total Item Diretur</span>
        <span class="metric-val">{{ number_format($totalRefundQty, 0, ',', '.') }} pcs</span>
        <span class="metric-sub" style="color: var(--text-muted);">
            <i class="fa-solid fa-box"></i> Kuantitas Barang Retur
        </span>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
    <form method="GET" action="{{ route('admin.refunds') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="width: 220px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Alasan Retur</label>
            <select name="reason" class="form-control">
                <option value="">-- Semua Alasan --</option>
                <option value="Barang Rusak / Cacat" {{ request('reason') == 'Barang Rusak / Cacat' ? 'selected' : '' }}>Barang Rusak / Cacat</option>
                <option value="Kadaluarsa" {{ request('reason') == 'Kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                <option value="Salah Beli (Pelanggan)" {{ request('reason') == 'Salah Beli (Pelanggan)' ? 'selected' : '' }}>Salah Beli (Pelanggan)</option>
                <option value="Lainnya" {{ request('reason') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request()->filled('reason'))
            <a href="{{ route('admin.refunds') }}" class="btn btn-secondary" style="background:#e5e7eb; color:#374151;">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>No. Invoice</th>
                    <th>Nama Produk</th>
                    <th style="text-align: center;">Jumlah (Qty)</th>
                    <th style="text-align: right;">Total Refund</th>
                    <th>Alasan Retur</th>
                    <th style="text-align: center;">Status Stok</th>
                    <th>Kasir Pemproses</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $refund)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--text-muted); font-size: 0.85rem;">
                            {{ ($refunds->currentPage() - 1) * $refunds->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <a href="{{ route('admin.transactions.show', $refund->transaction_id) }}" style="font-weight: 700; font-family: monospace; color: var(--olivenite);">
                                {{ $refund->transaction ? $refund->transaction->invoice_number : '-' }}
                            </a>
                        </td>
                        <td style="font-weight: 700;">{{ $refund->product_name }}</td>
                        <td style="text-align: center; font-weight: 800; color: var(--terracotta);">{{ $refund->qty }} pcs</td>
                        <td style="text-align: right; font-weight: 700; color: var(--terracotta);">
                            Rp {{ number_format($refund->refund_amount, 0, ',', '.') }}
                        </td>
                        <td>
                            <span class="badge badge-warning" style="font-size: 0.75rem;">{{ $refund->reason }}</span>
                            @if($refund->notes)
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $refund->notes }}</div>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($refund->is_restocked)
                                <span class="badge badge-success"><i class="fa-solid fa-boxes-packing"></i> Stok Dikembalikan</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-trash-can"></i> Dibuang (Barang Rusak)</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem;">{{ $refund->user ? $refund->user->name : '-' }}</td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $refund->created_at->format('d M Y, H:i') }} WIB</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada riwayat retur atau refund barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($refunds->hasPages())
    <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border,#e5e7eb);">
        {{ $refunds->links() }}
    </div>
    @endif
</div>
@endsection
