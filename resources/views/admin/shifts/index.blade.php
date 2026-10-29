@extends('layouts.admin')

@section('title', 'Laporan Shift Kasir')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-clock-rotate-left" style="color: var(--bancha);"></i> Laporan Shift Kasir & Laci Uang</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Pantau saldo modal awal, total tunai, saldo fisik yang diinput kasir, dan deteksi otomatis selisih uang (mencegah kecurangan).</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem;">
    <form method="GET" action="{{ route('admin.shifts') }}" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Filter Kasir</label>
            <select name="user_id" class="form-control">
                <option value="">-- Semua Kasir --</option>
                @foreach($cashiers as $c)
                    <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="width: 180px;">
            <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Status Shift</label>
            <select name="status" class="form-control">
                <option value="">-- Semua Status --</option>
                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Aktif (Open)</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Selesai (Closed)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
        @if(request()->hasAny(['user_id', 'status']))
            <a href="{{ route('admin.shifts') }}" class="btn btn-secondary" style="background:#e5e7eb; color:#374151;">Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Nama Kasir</th>
                    <th>Status Shift</th>
                    <th>Waktu Buka</th>
                    <th>Waktu Tutup</th>
                    <th style="text-align: right;">Modal Awal</th>
                    <th style="text-align: right;">Penjualan Tunai</th>
                    <th style="text-align: right;">Saldo Seharusnya</th>
                    <th style="text-align: right;">Saldo Fisik (Kasir)</th>
                    <th style="text-align: right;">Selisih Uang</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    @php
                        // Hitung total tunai masuk selama shift ini jika shift masih open atau sudah closed
                        $cashSales = \App\Models\Transaction::where('user_id', $shift->user_id)
                            ->where('payment_method', 'Tunai')
                            ->where('created_at', '>=', $shift->start_time)
                            ->when($shift->end_time, fn($q) => $q->where('created_at', '<=', $shift->end_time))
                            ->sum('total_price');

                        $expectedEnding = $shift->starting_cash + $cashSales;
                        $diff = $shift->status === 'closed' ? ($shift->actual_ending_cash - $expectedEnding) : 0;
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--text-muted); font-size: 0.85rem;">
                            {{ ($shifts->currentPage() - 1) * $shifts->perPage() + $loop->iteration }}
                        </td>
                        <td style="font-weight: 700; color: var(--olivenite);">{{ $shift->user ? $shift->user->name : 'Kasir' }}</td>
                        <td>
                            @if($shift->status === 'open')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-play"></i> Sedang Aktif</span>
                            @else
                                <span class="badge badge-info"><i class="fa-solid fa-circle-check"></i> Selesai (Closed)</span>
                            @endif
                        </td>
                        <td style="font-size: 0.85rem;">{{ $shift->start_time->format('d M Y, H:i') }} WIB</td>
                        <td style="font-size: 0.85rem;">
                            {{ $shift->end_time ? $shift->end_time->format('d M Y, H:i') . ' WIB' : '-' }}
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            Rp {{ number_format($shift->starting_cash, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 600; color: var(--success);">
                            Rp {{ number_format($cashSales, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: var(--olivenite);">
                            Rp {{ number_format($expectedEnding, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 700;">
                            @if($shift->status === 'closed')
                                Rp {{ number_format($shift->actual_ending_cash, 0, ',', '.') }}
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: normal;">(Shift Belum Ditutup)</span>
                            @endif
                        </td>
                        <td style="text-align: right; font-weight: 800;">
                            @if($shift->status === 'closed')
                                @if($diff == 0)
                                    <span style="color: #16a34a;"><i class="fa-solid fa-check-double"></i> Pas (Rp 0)</span>
                                @elseif($diff < 0)
                                    <span style="color: #dc2626; background: #fee2e2; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Kurang Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span style="color: #2563eb; background: #dbeafe; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        <i class="fa-solid fa-circle-plus"></i> Lebih Rp {{ number_format($diff, 0, ',', '.') }}
                                    </span>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada riwayat shift kasir recorded.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($shifts->hasPages())
    <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border,#e5e7eb);">
        {{ $shifts->links() }}
    </div>
    @endif
</div>
@endsection
