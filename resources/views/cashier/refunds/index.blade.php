<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retur Barang - {{ $settings['app_name'] ?? 'Kasir Zamiel' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            --olivenite: {{ $settings['color_primary'] ?? '#313118' }};
            --bancha: {{ $settings['color_secondary'] ?? '#6D6844' }};
            --hillside: {{ $settings['color_accent'] ?? '#CCC6A9' }};
            --terracotta: {{ $settings['color_danger'] ?? '#83482B' }};
        }
        body { background: var(--bg-cream); font-family: system-ui, -apple-system, sans-serif; padding: 1.5rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .top-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .card { background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 1.5rem; }
        .form-control { width: 100%; padding: 0.65rem 0.85rem; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.9rem; }
        .btn { padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: var(--bancha); color: white; }
        .btn-secondary { background: #e5e7eb; color: #374151; }
        .btn-danger { background: var(--terracotta); color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
        th { background: #f8fafc; font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .alert { padding: 1rem; border-radius: 12px; margin-bottom: 1rem; font-weight: 600; }
        .alert-success { background: #dcfce7; color: #15803d; }
        .alert-danger { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <div>
                <h1 style="font-size: 1.5rem; color: var(--olivenite);"><i class="fa-solid fa-rotate-left" style="color: var(--terracotta);"></i> Retur / Refund Barang (Kasir)</h1>
                <p style="font-size: 0.85rem; color: #64748b;">Cari transaksi berdasarkan No. Struk / Invoice untuk memproses barang rusak atau dikembalikan.</p>
            </div>
            <a href="{{ route('cashier.pos') }}" class="btn btn-secondary">
                <i class="fa-solid fa-cash-register"></i> Kembali ke POS
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
        @endif

        <!-- Search Form -->
        <div class="card">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fa-solid fa-magnifying-glass"></i> Cari Struk Transaksi</h3>
            <form method="GET" action="{{ route('cashier.refunds.index') }}" style="display: flex; gap: 0.75rem;">
                <input type="text" name="invoice" class="form-control" placeholder="Masukkan No. Struk (Contoh: KZR-20260812-0001)" value="{{ $invoice ?? '' }}" required>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
            </form>
        </div>

        <!-- Transaction Details & Refund Form -->
        @if($transaction)
            <div class="card" style="border: 2px solid var(--bancha);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #eee;">
                    <div>
                        <div style="font-weight: 800; font-family: monospace; color: var(--olivenite); font-size: 1.1rem;">{{ $transaction->invoice_number }}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">Kasir: {{ $transaction->user->name }} | {{ $transaction->created_at->format('d M Y, H:i') }} WIB</div>
                    </div>
                    <span class="btn btn-secondary" style="font-size: 0.8rem; cursor: default;">Metode: {{ $transaction->payment_method }}</span>
                </div>

                <h4 style="font-size: 0.9rem; margin-bottom: 0.75rem;">Pilih Item yang Ingin Diretur:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Item Produk</th>
                            <th>Jumlah Dibeli</th>
                            <th>Harga Jual</th>
                            <th>Subtotal</th>
                            <th style="width: 150px; text-align: center;">Aksi Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $item)
                            <tr>
                                <td style="font-weight: 700;">{{ $item->product ? $item->product->name : 'Produk' }}</td>
                                <td>{{ $item->qty }} pcs</td>
                                <td>Rp {{ number_format($item->price_sell, 0, ',', '.') }}</td>
                                <td style="font-weight: 700; color: var(--olivenite);">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.4rem 0.75rem;" 
                                            onclick='openRefundModal({{ $transaction->id }}, {{ $item->id }}, "{{ addslashes($item->product ? $item->product->name : "Produk") }}", {{ $item->qty }}, {{ $item->subtotal / $item->qty }})'>
                                        <i class="fa-solid fa-rotate-left"></i> Retur Item
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(request()->filled('invoice'))
            <div class="card" style="text-align: center; color: var(--terracotta); padding: 2rem;">
                <i class="fa-solid fa-circle-xmark" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                <p>Transaksi dengan nomor struk <strong>{{ request('invoice') }}</strong> tidak ditemukan.</p>
            </div>
        @endif

        <!-- Recent Refunds by Me -->
        <div class="card">
            <h3 style="font-size: 1rem; margin-bottom: 1rem;"><i class="fa-solid fa-clock-rotate-left"></i> 10 Retur Terakhir yang Anda Proses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Struk</th>
                        <th>Barang</th>
                        <th>Qty</th>
                        <th>Total Refund</th>
                        <th>Alasan</th>
                        <th>Status Stok</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myRefunds as $ref)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700;">{{ $ref->transaction ? $ref->transaction->invoice_number : '-' }}</td>
                            <td style="font-weight: 600;">{{ $ref->product_name }}</td>
                            <td>{{ $ref->qty }} pcs</td>
                            <td style="font-weight: 700; color: var(--terracotta);">Rp {{ number_format($ref->refund_amount, 0, ',', '.') }}</td>
                            <td style="font-size: 0.85rem;">{{ $ref->reason }}</td>
                            <td>
                                @if($ref->is_restocked)
                                    <span style="color: #16a34a; font-size: 0.75rem; font-weight: 700;">+Restok</span>
                                @else
                                    <span style="color: #dc2626; font-size: 0.75rem; font-weight: 700;">Rusak (No Restok)</span>
                                @endif
                            </td>
                            <td style="font-size: 0.8rem; color: #64748b;">{{ $ref->created_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #94a3b8;">Belum ada riwayat retur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div class="card" style="width:100%; max-width:480px; padding:1.5rem;">
            <h3 style="margin-bottom:1rem; font-size: 1.1rem; color: var(--terracotta);"><i class="fa-solid fa-rotate-left"></i> Form Retur Barang</h3>
            <form action="{{ route('cashier.refunds.process') }}" method="POST">
                @csrf
                <input type="hidden" name="transaction_id" id="rf_tx_id">
                <input type="hidden" name="transaction_item_id" id="rf_item_id">

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Nama Barang</label>
                    <input type="text" id="rf_product_name" class="form-control" readonly style="background: #f1f5f9; font-weight: 700;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Jumlah Retur (Qty) *</label>
                        <input type="number" name="qty" id="rf_qty" class="form-control" min="1" required oninput="calcRefund()">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Total Refund (Rp)</label>
                        <input type="text" id="rf_total_display" class="form-control" readonly style="background: #f1f5f9; font-weight: 800; color: var(--terracotta);">
                    </div>
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Alasan Retur *</label>
                    <select id="rf_reason_select" class="form-control" required onchange="toggleCustomReason()">
                        <option value="Barang Rusak / Cacat">Barang Rusak / Cacat</option>
                        <option value="Kadaluarsa">Kadaluarsa</option>
                        <option value="Salah Beli (Pelanggan)">Salah Beli (Pelanggan)</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <input type="hidden" name="reason" id="rf_real_reason">
                </div>

                <div id="custom-reason-container" style="display:none; margin-bottom: 0.75rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem; color: var(--terracotta);">Tulis Alasan Lainnya *</label>
                    <input type="text" id="rf_custom_reason" class="form-control" placeholder="Contoh: Barang tidak sesuai deskripsi">
                </div>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Kembalikan ke Stok Toko? *</label>
                    <select name="is_restocked" class="form-control" required>
                        <option value="0">TIDAK (Barang Rusak / Dibuang)</option>
                        <option value="1">YA (Barang Bagus / Ditambah ke Stok)</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Catatan Tambahan</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Catatan detail retur"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeRefundModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Proses Refund</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let unitPrice = 0;

        function openRefundModal(txId, itemId, pName, maxQty, price) {
            document.getElementById('rf_tx_id').value = txId;
            document.getElementById('rf_item_id').value = itemId;
            document.getElementById('rf_product_name').value = pName;
            document.getElementById('rf_qty').max = maxQty;
            document.getElementById('rf_qty').value = 1;
            unitPrice = price;
            calcRefund();
            document.getElementById('rf_reason_select').value = 'Barang Rusak / Cacat';
            toggleCustomReason();
            document.getElementById('refundModal').style.display = 'flex';
        }

        function calcRefund() {
            let qty = parseInt(document.getElementById('rf_qty').value) || 0;
            let total = qty * unitPrice;
            document.getElementById('rf_total_display').value = 'Rp ' + total.toLocaleString('id-ID');
        }

        function closeRefundModal() {
            document.getElementById('refundModal').style.display = 'none';
        }

        function toggleCustomReason() {
            const select = document.getElementById('rf_reason_select');
            const container = document.getElementById('custom-reason-container');
            const customInput = document.getElementById('rf_custom_reason');

            if (select.value === 'Lainnya') {
                container.style.display = 'block';
                customInput.required = true;
            } else {
                container.style.display = 'none';
                customInput.required = false;
            }
        }

        // Form submit handler
        document.getElementById('refundModal').querySelector('form').addEventListener('submit', function(e) {
            const select = document.getElementById('rf_reason_select');
            const customInput = document.getElementById('rf_custom_reason');
            const realReason = document.getElementById('rf_real_reason');

            if (select.value === 'Lainnya') {
                realReason.value = customInput.value.trim() || 'Lainnya';
            } else {
                realReason.value = select.value;
            }
        });
    </script>
</body>
</html>
