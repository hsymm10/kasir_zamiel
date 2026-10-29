@extends('layouts.admin')

@section('title', 'Pengaturan Aplikasi')

@section('content')
<style>
    /* ── Desktop vs Mobile Distinct Settings Layout (Gambar 3 & Gambar 5) ── */
    .settings-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 2rem;
    }

    .settings-header {
        margin-bottom: 1.5rem;
    }
    .settings-header h1 {
        font-size: 1.55rem;
        font-weight: 800;
        color: var(--olivenite);
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .settings-header p {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
    }

    /* Desktop 2-Column Grid to fill screen & avoid empty look */
    .settings-desktop-grid {
        display: grid;
        grid-template-columns: 1.05fr 0.95fr;
        gap: 1.5rem;
        align-items: start;
    }

    .settings-column {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .settings-section-title {
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-muted);
        margin-bottom: 0.6rem;
        padding-left: 0.5rem;
    }

    .settings-group-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .settings-item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        gap: 1rem;
        transition: background 0.15s ease;
    }
    .settings-item-row:last-child {
        border-bottom: none;
    }
    .settings-item-row:hover {
        background: #fafafa;
    }

    .settings-item-left {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex: 1;
        min-width: 0;
    }

    .settings-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--bg-cream);
        color: var(--bancha);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
        border: 1px solid var(--border-color);
    }

    .settings-item-text {
        flex: 1;
        min-width: 0;
    }
    .settings-item-text strong {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--olivenite);
        margin-bottom: 0.15rem;
    }
    .settings-item-text span {
        display: block;
        font-size: 0.76rem;
        color: var(--text-muted);
        line-height: 1.35;
    }

    .settings-item-control {
        min-width: 140px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .settings-item-control input[type="text"] {
        width: 100%;
        max-width: 280px;
        padding: 0.55rem 0.85rem;
        border-radius: 12px;
        border: 1.5px solid var(--border-color);
        font-size: 0.85rem;
        font-family: inherit;
        outline: none;
        background: white;
        transition: border-color 0.2s;
    }
    .settings-item-control input[type="text"]:focus {
        border-color: var(--bancha);
    }

    /* iOS Switch */
    .ios-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 28px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .ios-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .ios-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #cbd5e1;
        border-radius: 99px;
        transition: background 0.25s ease;
    }
    .ios-slider::before {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        left: 3px;
        top: 3px;
        background: white;
        border-radius: 50%;
        transition: transform 0.25s cubic-bezier(0.3, 1.5, 0.7, 1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .ios-switch input:checked + .ios-slider {
        background: #22c55e;
    }
    .ios-switch input:checked + .ios-slider::before {
        transform: translateX(22px);
    }

    /* Business Type Cards */
    .biz-type-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        padding: 1rem 1.25rem;
    }
    .biz-type-btn {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem;
        border-radius: 14px;
        border: 2px solid var(--border-color);
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        text-align: left;
    }
    .biz-type-btn.selected {
        border-color: var(--bancha);
        background: #f8faf6;
        box-shadow: 0 4px 12px rgba(109,104,68,0.1);
    }
    .biz-type-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* Color Presets */
    .color-presets-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .preset-chip {
        padding: 0.4rem 0.75rem;
        border-radius: 99px;
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
        border: none;
        cursor: pointer;
        transition: transform 0.15s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .preset-chip:hover {
        transform: scale(1.04);
    }

    /* Sticky Save Bar */
    .settings-save-bar {
        position: sticky;
        bottom: 1.5rem;
        background: white;
        border-radius: 20px;
        padding: 0.875rem 1.5rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        z-index: 50;
        margin-top: 2rem;
    }

    /* Responsive Mobile layout adjustment */
    @media (max-width: 900px) {
        .settings-desktop-grid {
            grid-template-columns: 1fr;
        }
        .settings-item-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .settings-item-control {
            width: 100%;
            justify-content: flex-start;
        }
        .settings-item-control input[type="text"] {
            max-width: 100%;
        }
        .biz-type-container {
            grid-template-columns: 1fr;
        }
        .settings-save-bar {
            flex-direction: column;
            gap: 0.75rem;
            text-align: center;
            bottom: 80px;
        }
    }
</style>

<div class="settings-wrapper">
    <div class="settings-header">
        <h1><i class="fa-solid fa-gear" style="color: var(--bancha);"></i> Pengaturan Kasir</h1>
        <p>Atur identitas bisnis, fitur aktif, dan tampilan kasir dalam satu tempat yang terstruktur.</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
        @csrf

        <div class="settings-desktop-grid">
            
            {{-- ══ LEFT COLUMN (Identitas Toko & Tipe Bisnis) ══ --}}
            <div class="settings-column">
                
                {{-- SECTION 1: IDENTITAS TOKO --}}
                <div class="settings-section">
                    <div class="settings-section-title"><i class="fa-solid fa-store"></i> Identitas Toko & UMKM</div>
                    <div class="settings-group-card">
                        
                        {{-- App Name --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-tag"></i></div>
                                <div class="settings-item-text">
                                    <strong>Nama Aplikasi / Website</strong>
                                    <span>Tampil di tab browser &amp; sidebar dashboard</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Kasir Zamiel' }}" required placeholder="Contoh: Kasir Zamiel">
                            </div>
                        </div>

                        {{-- Store Name --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-shop"></i></div>
                                <div class="settings-item-text">
                                    <strong>Nama Toko / UMKM</strong>
                                    <span>Judul utama toko pada struk &amp; kasir POS</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" name="store_name" value="{{ $settings['store_name'] ?? 'Warung Sembako Zamiel' }}" required placeholder="Contoh: Warung Sembako Zamiel">
                            </div>
                        </div>

                        {{-- Store Address --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-location-dot"></i></div>
                                <div class="settings-item-text">
                                    <strong>Alamat Toko</strong>
                                    <span>Alamat lengkap pada header nota belanja</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" name="store_address" value="{{ $settings['store_address'] ?? '' }}" required placeholder="Jl. Raya No. 12, Jakarta">
                            </div>
                        </div>

                        {{-- Store Phone --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-phone"></i></div>
                                <div class="settings-item-text">
                                    <strong>Telepon / WhatsApp</strong>
                                    <span>Nomor kontak toko di nota belanja</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" required placeholder="081234567890">
                            </div>
                        </div>

                        {{-- Store QRIS Upload --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-qrcode"></i></div>
                                <div class="settings-item-text">
                                    <strong>Gambar QRIS Toko</strong>
                                    <span>Tampil saat pembeli bayar via QRIS</span>
                                </div>
                            </div>
                            <div class="settings-item-control" style="flex-direction: column; align-items: flex-end; gap: 0.4rem;">
                                <input type="file" name="store_qris" accept="image/*" style="font-size: 0.78rem;">
                                @if(!empty($settings['store_qris']))
                                    <img src="{{ asset($settings['store_qris']) }}" alt="QRIS" style="height: 44px; width: 44px; object-fit: contain; border-radius: 6px; border: 1px solid #ccc; padding: 2px;">
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- SECTION 2: TIPE BISNIS --}}
                <div class="settings-section">
                    <div class="settings-section-title"><i class="fa-solid fa-briefcase"></i> Tipe Bisnis &amp; Usaha</div>
                    <div class="settings-group-card">
                        @php
                            $types = [
                                'sembako' => ['icon' => 'fa-basket-shopping', 'label' => 'Warung / Sembako', 'desc' => 'Barcode, Expired & Stok ON', 'color' => '#6D6844'],
                                'fnb'     => ['icon' => 'fa-utensils',        'label' => 'F&B / Makanan',    'desc' => 'Barcode & Expired OFF',      'color' => '#C0392B'],
                                'fashion' => ['icon' => 'fa-shirt',           'label' => 'Fashion / Baju',    'desc' => 'Barcode ON, Expired OFF',   'color' => '#8E44AD'],
                                'retail'  => ['icon' => 'fa-store',           'label' => 'Retail Umum',       'desc' => 'Semua fitur aktif',          'color' => '#2980B9'],
                            ];
                            $currentType = $settings['business_type'] ?? 'sembako';
                        @endphp
                        <div class="biz-type-container">
                            @foreach($types as $value => $type)
                                <label class="biz-type-btn {{ $currentType === $value ? 'selected' : '' }}">
                                    <input type="radio" name="business_type" value="{{ $value }}" {{ $currentType === $value ? 'checked' : '' }} style="display:none;" onchange="onBusinessTypeSelect('{{ $value }}')">
                                    <div class="biz-type-icon" style="background: {{ $type['color'] }}1a; color: {{ $type['color'] }};">
                                        <i class="fa-solid {{ $type['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <strong style="display:block; font-size: 0.82rem; color: var(--olivenite);">{{ $type['label'] }}</strong>
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">{{ $type['desc'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══ RIGHT COLUMN (Fitur Aktif & Tema Warna) ══ --}}
            <div class="settings-column">
                
                {{-- SECTION 3: FITUR FITUR POS --}}
                <div class="settings-section">
                    <div class="settings-section-title"><i class="fa-solid fa-sliders"></i> Fitur Aktif / Nonaktif</div>
                    <div class="settings-group-card">

                        {{-- Barcode --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-barcode"></i></div>
                                <div class="settings-item-text">
                                    <strong>Fitur Barcode Produk</strong>
                                    <span>Scan barcode &amp; kolom barcode di POS</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <label class="ios-switch">
                                    <input type="checkbox" id="enable_barcode" name="enable_barcode" {{ ($settings['enable_barcode'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="ios-slider"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Expiration Date --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-calendar-days"></i></div>
                                <div class="settings-item-text">
                                    <strong>Tanggal Kedaluwarsa</strong>
                                    <span>Catat tanggal kedaluwarsa produk</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <label class="ios-switch">
                                    <input type="checkbox" id="enable_expiration" name="enable_expiration" {{ ($settings['enable_expiration'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="ios-slider"></span>
                                </label>
                            </div>
                        </div>

                        {{-- Stock Control --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                                <div class="settings-item-text">
                                    <strong>Kontrol Stok Produk</strong>
                                    <span>Cegah jual saat stok habis di kasir POS</span>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <label class="ios-switch">
                                    <input type="checkbox" id="enable_stock" name="enable_stock" {{ ($settings['enable_stock'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <span class="ios-slider"></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- SECTION 4: TEMA WARNA --}}
                <div class="settings-section">
                    <div class="settings-section-title"><i class="fa-solid fa-palette"></i> Tema Warna Aplikasi</div>
                    <div class="settings-group-card">

                        <div class="color-presets-row">
                            <button type="button" class="preset-chip" style="background: linear-gradient(135deg, #313118, #6D6844);" onclick="applyColorPreset('#313118','#6D6844','#CCC6A9','#83482B')">Hutan (Default)</button>
                            <button type="button" class="preset-chip" style="background: linear-gradient(135deg, #0D47A1, #1976D2);" onclick="applyColorPreset('#0D47A1','#1976D2','#BBDEFB','#E53935')">Biru Samudra</button>
                            <button type="button" class="preset-chip" style="background: linear-gradient(135deg, #4A148C, #7B1FA2);" onclick="applyColorPreset('#4A148C','#7B1FA2','#E1BEE7','#F57F17')">Ungu Elegan</button>
                            <button type="button" class="preset-chip" style="background: linear-gradient(135deg, #B71C1C, #D32F2F);" onclick="applyColorPreset('#B71C1C','#D32F2F','#FFCDD2','#E65100')">Merah Berani</button>
                            <button type="button" class="preset-chip" style="background: linear-gradient(135deg, #1A1A2E, #16213E);" onclick="applyColorPreset('#1A1A2E','#16213E','#E2E2E2','#E94560')">Gelap Modern</button>
                        </div>

                        {{-- Color Pickers --}}
                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-paint-roller"></i></div>
                                <div class="settings-item-text">
                                    <strong>Warna Utama (Sidebar)</strong>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" id="color_primary" name="color_primary" value="{{ $settings['color_primary'] ?? '#313118' }}" style="width: 48px; height: 32px; border-radius: 8px; border: 1px solid #ccc; cursor: pointer;">
                            </div>
                        </div>

                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-fill-drip"></i></div>
                                <div class="settings-item-text">
                                    <strong>Warna Sekunder (Tombol)</strong>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" id="color_secondary" name="color_secondary" value="{{ $settings['color_secondary'] ?? '#6D6844' }}" style="width: 48px; height: 32px; border-radius: 8px; border: 1px solid #ccc; cursor: pointer;">
                            </div>
                        </div>

                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                                <div class="settings-item-text">
                                    <strong>Warna Aksen (Highlight)</strong>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" id="color_accent" name="color_accent" value="{{ $settings['color_accent'] ?? '#CCC6A9' }}" style="width: 48px; height: 32px; border-radius: 8px; border: 1px solid #ccc; cursor: pointer;">
                            </div>
                        </div>

                        <div class="settings-item-row">
                            <div class="settings-item-left">
                                <div class="settings-item-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                <div class="settings-item-text">
                                    <strong>Warna Bahaya (Hapus / Alert)</strong>
                                </div>
                            </div>
                            <div class="settings-item-control">
                                <input type="color" id="color_danger" name="color_danger" value="{{ $settings['color_danger'] ?? '#83482B' }}" style="width: 48px; height: 32px; border-radius: 8px; border: 1px solid #ccc; cursor: pointer;">
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>{{-- end settings-desktop-grid --}}

        {{-- Sticky Save Bar --}}
        <div class="settings-save-bar">
            <span style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-circle-info" style="color: var(--bancha);"></i> Perubahan langsung aktif setelah disimpan.
            </span>
            <button type="submit" class="btn btn-primary" style="padding: 0.65rem 1.75rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
            </button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
    // Automatically configure features when a Business Type is selected,
    // while keeping controls fully interactive for manual Super Admin overrides.
    function onBusinessTypeSelect(type) {
        document.querySelectorAll('.biz-type-btn').forEach(b => b.classList.remove('selected'));
        const activeRadio = document.querySelector(`input[name="business_type"][value="${type}"]`);
        if (activeRadio) activeRadio.closest('label').classList.add('selected');

        const barcodeToggle = document.getElementById('enable_barcode');
        const expirationToggle = document.getElementById('enable_expiration');
        const stockToggle = document.getElementById('enable_stock');

        if (type === 'sembako') {
            if (barcodeToggle) barcodeToggle.checked = true;
            if (expirationToggle) expirationToggle.checked = true;
            if (stockToggle) stockToggle.checked = true;
        } else if (type === 'fnb') {
            if (barcodeToggle) barcodeToggle.checked = false;
            if (expirationToggle) expirationToggle.checked = false;
            if (stockToggle) stockToggle.checked = true;
        } else if (type === 'fashion') {
            if (barcodeToggle) barcodeToggle.checked = true;
            if (expirationToggle) expirationToggle.checked = false;
            if (stockToggle) stockToggle.checked = true;
        } else if (type === 'retail') {
            if (barcodeToggle) barcodeToggle.checked = true;
            if (expirationToggle) expirationToggle.checked = true;
            if (stockToggle) stockToggle.checked = true;
        }
    }

    function applyColorPreset(p, s, a, d) {
        document.getElementById('color_primary').value = p;
        document.getElementById('color_secondary').value = s;
        document.getElementById('color_accent').value = a;
        document.getElementById('color_danger').value = d;
    }
</script>
@endsection
