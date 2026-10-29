@extends('layouts.admin')

@section('title', 'Pengeluaran Toko')

@section('content')
<div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem;">
    <div>
        <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-file-invoice-dollar" style="color: #ef4444;"></i> Catatan Pengeluaran Toko</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Catat biaya operasional toko (listrik, gaji, perlengkapan) untuk menghitung laba bersih.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openModal()">
        <i class="fa-solid fa-plus"></i> Catat Pengeluaran Baru
    </button>
</div>

<!-- Summary Cards -->
<div class="metrics-grid" style="margin-bottom: 1.5rem;">
    <div class="metric-card" style="border-top: 4px solid #ef4444;">
        <span class="metric-title">Pengeluaran Bulan Ini</span>
        <span class="metric-val" style="color: #dc2626;">Rp {{ number_format($totalMonthlyExpense, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--text-muted);">
            <i class="fa-solid fa-calendar-day"></i> {{ now()->translatedFormat('F Y') }}
        </span>
    </div>
    <div class="metric-card" style="border-top: 4px solid var(--olivenite);">
        <span class="metric-title">Total Pengeluaran Kumulatif</span>
        <span class="metric-val">Rp {{ number_format($totalOverallExpense, 0, ',', '.') }}</span>
        <span class="metric-sub" style="color: var(--text-muted);">
            <i class="fa-solid fa-calculator"></i> Keseluruhan Catatan
        </span>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">No</th>
                    <th>Judul Pengeluaran</th>
                    <th>Kategori</th>
                    <th>Jumlah Nominal</th>
                    <th>Tanggal Pengeluaran</th>
                    <th>Dicatat Oleh</th>
                    <th>Catatan</th>
                    <th style="width: 80px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td style="text-align: center; font-weight: 700; color: var(--text-muted); font-size: 0.85rem;">
                            {{ ($expenses->currentPage() - 1) * $expenses->perPage() + $loop->iteration }}
                        </td>
                        <td style="font-weight: 700; color: var(--olivenite);">{{ $expense->title }}</td>
                        <td>
                            <span class="badge badge-info" style="font-size: 0.75rem;">{{ $expense->category }}</span>
                        </td>
                        <td style="font-weight: 700; color: #dc2626;">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </td>
                        <td style="font-size: 0.85rem;">{{ $expense->expense_date->format('d M Y') }}</td>
                        <td style="font-size: 0.85rem;">{{ $expense->user ? $expense->user->name : '-' }}</td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $expense->notes ?? '-' }}</td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus catatan pengeluaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem 1rem;">
                            Belum ada catatan pengeluaran toko.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
    <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--border,#e5e7eb);">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

<!-- Modal Form Create Expense -->
<div id="expenseModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:480px; padding:1.5rem; background:white; border-radius:16px; margin:1rem;">
        <h3 style="margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <i class="fa-solid fa-file-invoice-dollar" style="color:#ef4444;"></i> Catat Pengeluaran Baru
        </h3>
        <form action="{{ route('admin.expenses.store') }}" method="POST">
            @csrf
            
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Judul Pengeluaran *</label>
                <input type="text" name="title" class="form-control" required placeholder="Contoh: Bayar Listrik Bulan Ini / Beli Kantong Plastik">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Kategori *</label>
                    <select id="expense-category-select" class="form-control" required onchange="toggleCustomCategory()">
                        <option value="Operasional">Operasional</option>
                        <option value="Listrik & Air">Listrik & Air</option>
                        <option value="Gaji Karyawan">Gaji Karyawan</option>
                        <option value="Perlengkapan">Perlengkapan Toko</option>
                        <option value="Sewa Tempat">Sewa Tempat</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <input type="hidden" name="category" id="expense-category-raw">
                </div>
                <div>
                    <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Nominal (Rp) *</label>
                    <input type="text" id="expense-amount-display" class="form-control" required placeholder="Contoh: 50.000">
                    <input type="hidden" name="amount" id="expense-amount-raw">
                </div>
            </div>

            <div id="custom-category-container" style="display:none; margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem; color:var(--terracotta);">Tulis Kategori Baru *</label>
                <input type="text" id="expense-category-custom" class="form-control" placeholder="Contoh: Biaya Transportasi / Perbaikan Pintu">
            </div>

            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Tanggal *</label>
                <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>

            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-size:0.85rem; font-weight:700; margin-bottom:0.35rem;">Catatan Tambahan</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn btn-primary" style="background:#ef4444; border-color:#ef4444;">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openModal() {
        document.getElementById('expenseModal').style.display = 'flex';
    }
    function closeModal() {
        document.getElementById('expenseModal').style.display = 'none';
    }

    function toggleCustomCategory() {
        const select = document.getElementById('expense-category-select');
        const container = document.getElementById('custom-category-container');
        const customInput = document.getElementById('expense-category-custom');
        
        if (select.value === 'Lainnya') {
            container.style.display = 'block';
            customInput.required = true;
        } else {
            container.style.display = 'none';
            customInput.required = false;
        }
    }

    // Dynamic numeric formatting for nominal input (thousands separators)
    const amountDisplay = document.getElementById('expense-amount-display');
    const amountRaw = document.getElementById('expense-amount-raw');

    amountDisplay.addEventListener('input', function() {
        // Strip out non-digits
        let val = this.value.replace(/\D/g, '');
        amountRaw.value = val;

        if (val) {
            this.value = parseInt(val, 10).toLocaleString('id-ID');
        } else {
            this.value = '';
        }
    });

    // Form submit listener to set raw input values correctly
    document.getElementById('expenseModal').querySelector('form').addEventListener('submit', function(e) {
        const select = document.getElementById('expense-category-select');
        const customInput = document.getElementById('expense-category-custom');
        const categoryRaw = document.getElementById('expense-category-raw');

        if (select.value === 'Lainnya') {
            categoryRaw.value = customInput.value.trim() || 'Lainnya';
        } else {
            categoryRaw.value = select.value;
        }

        // Final verification of raw amount
        if (!amountRaw.value) {
            e.preventDefault();
            alert('Masukkan nominal pengeluaran yang valid!');
        }
    });
</script>
@endsection
