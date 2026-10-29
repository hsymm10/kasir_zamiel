@extends('layouts.admin')

@section('title', 'Riwayat Stok Barang')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-boxes-packing" style="color: var(--bancha);"></i> Riwayat Pergerakan Stok</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Catatan otomatis setiap stok masuk, keluar, penjualan, dan penyesuaian barang.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openModal()">
        <i class="fa-solid fa-plus-minus"></i> Restok / Adjust Stok Manual
    </button>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
    <form method="GET" action="{{ route('admin.stock_logs') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Filter Produk</label>
            <select name="product_id" class="form-control">
                <option value="">-- Semua Produk --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="width: 180px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Tipe Pergerakan</label>
            <select name="type" class="form-control">
                <option value="">-- Semua Tipe --</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Masuk (Restok)</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Keluar (Penjualan/Barang Rusak)</option>
                <option value="adjust" {{ request('type') == 'adjust' ? 'selected' : '' }}>Penyesuaian Manual</option>
                <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Retur / Refund</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request()->hasAny(['product_id', 'type']))
            <a href="{{ route('admin.stock_logs') }}" class="btn btn-secondary" style="background:#e5e7eb; color:#374151;">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Nama Produk</th>
                    <th>Tipe Pergerakan</th>
                    <th style="text-align: center;">Jumlah (Qty)</th>
                    <th style="text-align: center;">Stok Sebelum</th>
                    <th style="text-align: center;">Stok Sesudah</th>
                    <th>Keterangan / Alasan</th>
                    <th>Operator</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--text-muted); font-size: 0.85rem;">
                            {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                        </td>
                        <td style="font-weight: 700; color: var(--olivenite);">
                            {{ $log->product ? $log->product->name : 'Produk Dihapus' }}
                        </td>
                        <td>
                            @if($log->type == 'in')
                                <span class="badge badge-success"><i class="fa-solid fa-arrow-down-left"></i> Masuk (Restok)</span>
                            @elseif($log->type == 'out')
                                <span class="badge badge-danger"><i class="fa-solid fa-arrow-up-right"></i> Keluar</span>
                            @elseif($log->type == 'refund')
                                <span class="badge badge-info"><i class="fa-solid fa-rotate-left"></i> Retur / Refund</span>
                            @else
                                <span class="badge badge-warning"><i class="fa-solid fa-sliders"></i> Adjust Manual</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 800;">
                            @if($log->type == 'in' || $log->type == 'refund')
                                <span style="color: var(--success);">+{{ $log->qty }}</span>
                            @else
                                <span style="color: var(--terracotta);">-{{ $log->qty }}</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-size: 0.9rem;">{{ $log->stock_before }}</td>
                        <td style="text-align: center; font-weight: 700; font-size: 0.9rem; color: var(--olivenite);">{{ $log->stock_after }}</td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $log->notes ?? '-' }}</td>
                        <td style="font-size: 0.85rem;">{{ $log->user ? $log->user->name : 'Sistem' }}</td>
                        <td style="font-size: 0.8rem; color: var(--text-muted);">{{ $log->created_at->format('d M Y, H:i') }} WIB</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada riwayat pergerakan stok tercatat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border,#e5e7eb);">
        {{ $logs->links() }}
    </div>
    @endif
</div>

<!-- Modal Manual Adjust -->
<div id="stockModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:480px; padding:1.5rem; background:white; border-radius:16px; margin:1rem;">
        <h3 style="margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <i class="fa-solid fa-boxes-packing" style="color:var(--bancha);"></i> Restok / Adjust Stok Manual
        </h3>
        <form action="{{ route('admin.stock_logs.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Pilih Produk *</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (Sisa Stok: {{ $p->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Tipe *</label>
                    <select name="type" class="form-control" required>
                        <option value="in">Tambah Stok (Masuk)</option>
                        <option value="out">Kurangi Stok (Keluar)</option>
                        <option value="adjust">Set Total Stok Baru</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Jumlah (Qty) *</label>
                    <input type="number" name="qty" class="form-control" required min="1" placeholder="Misal: 10">
                </div>
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Keterangan / Catatan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Contoh: Restok dari Supplier PT Sumber Makmur"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal() {
        document.getElementById('stockModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('stockModal').style.display = 'none';
    }
</script>
@endsection
