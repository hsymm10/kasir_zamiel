<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Belanja - {{ $transaction->invoice_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap');

        :root {
            --olivenite: {{ $settings['color_primary'] ?? '#313118' }};
            --bancha: {{ $settings['color_secondary'] ?? '#6D6844' }};
            --hillside: {{ $settings['color_accent'] ?? '#CCC6A9' }};
            --terracotta: {{ $settings['color_danger'] ?? '#83482B' }};
            --bg-cream: #FAF8F0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg-cream); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }

        .invoice-container {
            display: flex;
            gap: 2rem;
            max-width: 780px;
            width: 100%;
            margin: 0 auto;
            align-items: flex-start;
        }

        .receipt {
            background: white;
            flex: 1.2;
            min-width: 320px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .invoice-actions {
            flex: 0.8;
            background: white;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: sticky;
            top: 2rem;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            width: 100%;
            padding: 0.8rem 1.25rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-family: inherit;
        }

        .btn-action.primary {
            background: var(--olivenite);
            color: white;
            border: 2px solid var(--olivenite);
        }
        .btn-action.primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-action.secondary {
            background: transparent;
            color: var(--olivenite);
            border: 2px solid var(--olivenite);
        }
        .btn-action.secondary:hover {
            background: rgba(49,49,24,0.05);
            transform: translateY(-1px);
        }

        .btn-action.text {
            background: #f3f4f6;
            color: #374151;
            border: none;
        }
        .btn-action.text:hover {
            background: #e5e7eb;
        }

        .invoice-no {
            text-align: center;
            font-family: monospace;
            font-size: 0.95rem;
            color: var(--bancha);
            font-weight: 700;
            padding: 0.75rem;
            background: rgba(109,104,68,0.08);
            border-radius: 8px;
            margin-bottom: 1.25rem;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .meta-label { color: #888; }
        .meta-val { font-weight: 600; color: var(--olivenite); }

        hr { border: none; border-top: 1.5px dashed var(--hillside); margin: 1.25rem 0; }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.875rem;
        }
        .item-name { font-size: 0.85rem; color: var(--olivenite); font-weight: 600; flex: 1; }
        .item-detail { font-size: 0.78rem; color: #888; margin-top: 0.15rem; }
        .item-subtotal { font-size: 0.85rem; font-weight: 700; color: var(--olivenite); text-align: right; white-space: nowrap; }

        .total-section {
            background: var(--bg-cream);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .total-row.grand {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--olivenite);
            border-top: 1.5px dashed var(--hillside);
            padding-top: 0.625rem;
            margin-top: 0.625rem;
        }
        .total-row.change { color: var(--terracotta); font-weight: 700; }

        .receipt-footer {
            text-align: center;
            padding: 1.5rem;
            border-top: 1.5px dashed var(--hillside);
        }
        .receipt-footer p { font-size: 0.8rem; color: #888; margin-top: 0.5rem; }
        .footer-brand { font-weight: 800; color: var(--olivenite); font-size: 1rem; margin-bottom: 0.25rem; }

        .badge-paid {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .invoice-container {
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
            }
            .receipt {
                width: 100%;
                max-width: 450px;
            }
            .invoice-actions {
                width: 100%;
                max-width: 450px;
                position: static;
                padding: 1.25rem;
            }
        }

        @media print {
            @page {
                size: auto;
                margin: 4mm;
            }
            .no-print {
                display: none !important;
            }
            .invoice-container {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: flex !important;
                justify-content: center !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .receipt {
                width: 100% !important;
                max-width: 80mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 auto !important;
            }
            .receipt-header {
                background: none !important;
                color: black !important;
                padding: 0.5rem 0 0.75rem !important;
                border-bottom: 1.5px dashed #000 !important;
                text-align: center !important;
            }
            .receipt-header h1 {
                font-size: 1.15rem !important;
                color: black !important;
                font-weight: 800 !important;
            }
            .receipt-header p {
                color: #333 !important;
                font-size: 0.75rem !important;
            }
            .receipt-header .icon, .badge-paid {
                display: none !important;
            }
            .receipt-body {
                padding: 0.75rem 0 !important;
            }
            .invoice-no {
                background: none !important;
                border: 1.5px solid #000 !important;
                color: black !important;
                padding: 0.3rem !important;
                margin-top: 0.25rem !important;
                margin-bottom: 0.65rem !important;
                font-size: 0.85rem !important;
                font-weight: 700 !important;
            }
            .meta-row {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
            }
            .meta-label {
                color: #444 !important;
            }
            .meta-val {
                color: black !important;
                font-weight: 700 !important;
            }
            hr {
                border-top: 1.5px dashed #000 !important;
                margin: 0.65rem 0 !important;
            }
            .item-row {
                margin-bottom: 0.5rem !important;
            }
            .item-name {
                color: black !important;
                font-weight: 700 !important;
                font-size: 0.8rem !important;
            }
            .item-detail {
                color: #444 !important;
                font-size: 0.72rem !important;
            }
            .item-subtotal {
                color: black !important;
                font-weight: 700 !important;
                font-size: 0.8rem !important;
            }
            .total-section {
                background: none !important;
                border-top: 1.5px dashed #000 !important;
                padding: 0.5rem 0 0 !important;
                margin-top: 0.5rem !important;
                border-radius: 0 !important;
            }
            .total-row {
                font-size: 0.8rem !important;
                margin-bottom: 0.25rem !important;
            }
            .total-row.grand {
                border-top: 1.5px dashed #000 !important;
                color: black !important;
                font-size: 0.95rem !important;
                font-weight: 800 !important;
                padding-top: 0.4rem !important;
                margin-top: 0.4rem !important;
            }
            .total-row.change {
                color: black !important;
                font-weight: 700 !important;
            }
            .receipt-footer {
                border-top: 1.5px dashed #000 !important;
                padding: 0.75rem 0 !important;
                text-align: center !important;
            }
            .footer-brand {
                color: black !important;
                font-weight: 800 !important;
                font-size: 0.9rem !important;
            }
            .receipt-footer p {
                color: #333 !important;
                font-size: 0.72rem !important;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Struk Belanja -->
        <div class="receipt">
            <!-- Header -->
            <div class="receipt-header">
                <div class="icon"><i class="fa-solid fa-store"></i></div>
                <h1>{{ $settings['store_name'] ?? 'Toko Kami' }}</h1>
                <p>Terima kasih telah berbelanja!</p>
                <span class="badge-paid"><i class="fa-solid fa-check"></i> LUNAS</span>
            </div>

            <!-- Body -->
            <div class="receipt-body">
                <!-- Invoice Number -->
                <div class="invoice-no">
                    <i class="fa-solid fa-file-invoice"></i> {{ $transaction->invoice_number }}
                </div>

                <!-- Meta Info -->
                <div class="meta-row">
                    <span class="meta-label">Tanggal</span>
                    <span class="meta-val">{{ $transaction->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Waktu</span>
                    <span class="meta-val">{{ $transaction->created_at->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Kasir</span>
                    <span class="meta-val">{{ $transaction->user->name }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Metode Bayar</span>
                    <span class="meta-val">{{ $transaction->payment_method }}</span>
                </div>

                <hr>

                <!-- Items -->
                @foreach($transaction->items as $item)
                    <div class="item-row">
                        <div>
                            <div class="item-name">{{ $item->product ? $item->product->name : 'Produk' }}</div>
                            <div class="item-detail">{{ $item->qty }} × Rp {{ number_format($item->price_sell, 0, ',', '.') }}</div>
                        </div>
                        <div class="item-subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                    </div>
                @endforeach

                <!-- Totals -->
                <div class="total-section">
                    @if($transaction->discount > 0)
                    <div class="total-row" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.2rem;">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($transaction->total_price + $transaction->discount, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-row" style="font-size: 0.85rem; color: var(--terracotta); margin-bottom: 0.2rem;">
                        <span>Diskon Produk</span>
                        <span>-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="total-row grand">
                        <span>Total</span>
                        <span>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="total-row">
                        <span>Bayar ({{ $transaction->payment_method }})</span>
                        <span>Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->payment_method === 'Tunai')
                    <div class="total-row change">
                        <span>Kembalian</span>
                        <span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                    </div>
                    @else
                    <div style="text-align: center; font-size: 0.8rem; font-weight: 700; color: #2e7d32; background: #e8f5e9; padding: 0.35rem; border-radius: 6px; margin-top: 0.4rem;">
                        <i class="fa-solid fa-circle-check"></i> LUNAS PAS ({{ $transaction->payment_method }})
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <div class="footer-brand">{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</div>
                <p>{{ $settings['store_name'] ?? '' }}</p>
                @if(!empty($settings['store_address']))
                <p style="font-size: 0.75rem;">{{ $settings['store_address'] }}</p>
                @endif
                @if(!empty($settings['store_phone']))
                <p style="font-size: 0.75rem;"><i class="fa-solid fa-phone" style="margin-right: 0.25rem;"></i>{{ $settings['store_phone'] }}</p>
                @endif
                <p style="margin-top: 0.75rem; font-size: 0.75rem;">Simpan struk ini sebagai bukti pembelian Anda.</p>
            </div>
        </div>

        <!-- Sidebar Menu Aksi (no-print) -->
        <div class="invoice-actions no-print">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--olivenite); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-gears"></i> Menu Struk
            </h3>
            <p style="font-size: 0.8rem; color: #666; margin-bottom: 0.75rem;">Silakan cetak struk belanja atau unduh sebagai dokumen PDF.</p>
            
            <button onclick="window.print()" class="btn-action primary">
                <i class="fa-solid fa-print"></i> Cetak Struk (Print)
            </button>
            <button onclick="window.print()" class="btn-action secondary">
                <i class="fa-solid fa-file-pdf"></i> Unduh PDF
            </button>
            
            @if(Auth::check())
                <a href="{{ Auth::user()->isAdmin() ? route('admin.transactions') : route('cashier.pos') }}" class="btn-action text">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Aplikasi
                </a>
            @endif
        </div>
    </div>

    @if(request()->has('print'))
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
    @endif
</body>
</html>
