@extends('layouts.admin')

@section('title', 'Detail Transaksi ' . $transaction->invoice_number)

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-file-invoice" style="color: var(--bancha);"></i> Detail Transaksi</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Nomor Struk: <strong style="font-family: monospace;">{{ $transaction->invoice_number }}</strong></p>
    </div>
    <div>
        <a href="{{ route('admin.transactions') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('invoice.show', $transaction->invoice_number) }}" target="_blank" class="btn btn-primary">
            <i class="fa-solid fa-up-right-from-square"></i> Lihat Struk Publik
        </a>
    </div>
</div>

<style>
    .transaction-detail-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }
    @media (max-width: 992px) {
        .transaction-detail-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }
</style>

<div class="transaction-detail-grid">

    <!-- Transaction Items Card -->
    <div class="card">
        <div class="card-header">
            <h3>Daftar Item Belanjaan</h3>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga Beli (Satuan)</th>
                        <th>Harga Jual (Satuan)</th>
                        <th style="text-align: center;">Qty</th>
                        <th>Subtotal</th>
                        <th>Profit Item</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $item)
                        @php
                            $itemProfit = ($item->price_sell - $item->price_buy) * $item->qty;
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: var(--olivenite);">
                                    {{ $item->product ? $item->product->name : 'Produk Dihapus' }}
                                </div>
                                <small style="color: var(--text-muted); font-family: monospace;">
                                    Barcode: {{ $item->product && $item->product->barcode ? $item->product->barcode : '-' }}
                                </small>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.85rem;">Rp {{ number_format($item->price_buy, 0, ',', '.') }}</td>
                            <td style="font-weight: 600;">Rp {{ number_format($item->price_sell, 0, ',', '.') }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $item->qty }}</td>
                            <td style="font-weight: 700; color: var(--olivenite);">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td style="font-weight: 700; color: var(--success);">Rp {{ number_format($itemProfit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transaction Summary Card -->
    <div class="card" style="height: fit-content;">
        <div class="card-header">
            <h3>Rincian Pembayaran</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Nama Kasir</span>
                <strong style="color: var(--olivenite);">{{ $transaction->user->name }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Metode Pembayaran</span>
                <strong style="color: var(--bancha);">{{ $transaction->payment_method }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <span style="color: var(--text-muted);">Waktu Transaksi</span>
                <strong style="color: var(--olivenite);">{{ $transaction->created_at->format('d/m/Y, H:i') }} WIB</strong>
            </div>
            
            <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; background-color: rgba(49,49,24,0.02); padding: 1rem; border-radius: var(--radius-md); border: 1px dashed var(--border-color);">
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Total Harga</span>
                    <strong style="color: var(--olivenite);">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Uang Bayar</span>
                    <strong style="color: var(--olivenite);">Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Uang Kembalian</span>
                    <strong style="color: var(--terracotta);">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; border-top: 1px dashed var(--border-color); padding-top: 0.5rem; margin-top: 0.5rem;">
                    <span style="color: var(--success); font-weight: 700;">Total Keuntungan</span>
                    <strong style="color: var(--success); font-size: 1.1rem;">Rp {{ number_format($transaction->profit, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
