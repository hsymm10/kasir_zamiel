<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ $settings['app_name'] ?? 'Kasir Zamiel' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Dynamic Theme Color Overrides from Settings --}}
    <style>
        :root {
            --olivenite: {{ $settings['color_primary'] ?? '#1a1a2e' }};
            --bancha: {{ $settings['color_secondary'] ?? '#6D6844' }};
            --hillside: {{ $settings['color_accent'] ?? '#CCC6A9' }};
            --terracotta: {{ $settings['color_danger'] ?? '#83482B' }};
            --sidebar-bg: {{ $settings['color_primary'] ?? '#1a1a2e' }};
            --sidebar-width: 270px;
        }
        .chat-badge {
            background: var(--terracotta);
            color: white;
            border-radius: 99px;
            padding: 0.1rem 0.55rem;
            font-size: 0.72rem;
            font-weight: 800;
            display: inline-block;
        }
    </style>
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: auto !important;
            }

            .admin-sidebar,
            .mobile-top-bar,
            .mobile-bottom-nav,
            .mobile-drawer,
            .mobile-drawer-overlay,
            .no-print {
                display: none !important;
            }

            .admin-layout {
                display: block !important;
                min-height: auto !important;
            }

            .admin-content {
                background: white !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                width: 100% !important;
            }
        }
    </style>
    <style>
        /* ═══ SIDEBAR — FINAL CLEAN VERSION ═══
           Strategy: icon is a normal flex child (width:48px).
           Sidebar CSS gradient gives the two-tone rail/panel background.
           Active state: lnk-text gets cream bg + border-radius (not the link itself).
           This avoids overflow-hidden clipping the icon completely.
        */

        /* BASE */
        body {
            background-color: #1e3299;
            overflow-x: hidden;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar container */
        .admin-sidebar {
            width: 300px;
            min-height: 100vh;
            background: var(--sidebar-bg); /* Dynamic background color based on settings! */
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
            flex-shrink: 0;
            z-index: 100;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.18);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* ── Brand ── */
        .sidebar-brand {
            display: flex;
            align-items: center;
            height: 90px;
            flex-shrink: 0;
            padding: 0 1.25rem;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.6rem;
        }

        .sidebar-brand-name {
            flex: 1;
            padding-left: 0.6rem;
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: 0.01em;
            color: white;
            white-space: nowrap;
            overflow: visible;
        }

        /* ── Nav list ── */
        .sidebar-nav-card {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem 0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .sidebar-nav-card::-webkit-scrollbar {
            width: 0;
        }

        .sidebar-nav-spacer {
            flex: 1;
            min-height: 4px;
        }

        .sidebar-nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.05);
            margin: 1rem 2rem;
        }

        /* ── Nav link: clean style */
        .admin-sidebar a.nav-lnk {
            display: flex;
            align-items: center;
            width: calc(100% - 2rem);
            margin: 0 1rem;
            height: 48px;
            text-decoration: none;
            position: relative;
            white-space: nowrap;
            color: rgba(255, 255, 255, 0.55) !important;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
            padding: 0 1rem;
            border-radius: 12px;
        }

        .admin-sidebar a.nav-lnk:hover {
            color: rgba(255, 255, 255, 0.9) !important;
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-sidebar a.nav-lnk .lnk-icon {
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: inherit;
        }

        .admin-sidebar a.nav-lnk .lnk-text {
            flex: 1;
            color: inherit;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 0;
            overflow: hidden;
        }

        .admin-sidebar a.nav-lnk .lnk-text span:first-child {
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .admin-sidebar a.nav-lnk.active {
            color: white !important;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.15) !important;
        }

        .admin-sidebar a.nav-lnk.active::before {
            content: '';
            position: absolute;
            left: -1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 28px;
            background-color: white;
            border-radius: 0 4px 4px 0;
            box-shadow: 2px 0 8px rgba(255, 255, 255, 0.4);
        }

        /* ── Footer ── */
        .sidebar-footer {
            padding: 1.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex-shrink: 0;
        }

        .sidebar-user-row {
            display: flex;
            align-items: center;
            width: 100%;
        }

        .sidebar-user-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            color: white;
        }

        .sidebar-user-text {
            padding-left: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-user-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: white;
            line-height: 1.2;
            margin-bottom: 0.2rem;
        }

        .sidebar-user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.55);
        }

        .btn-sidebar-logout {
            display: flex;
            align-items: center;
            width: 100%;
            height: 40px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            padding: 0;
            transition: all 0.2s ease;
            color: rgba(255, 255, 255, 0.55);
        }

        .btn-sidebar-logout:hover {
            color: rgba(255, 255, 255, 0.9);
        }

        .btn-sidebar-logout .lnk-icon {
            width: 32px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
            font-size: 1.25rem;
            color: inherit;
        }

        .btn-sidebar-logout .lnk-text {
            flex: 1;
            font-size: 0.95rem;
            font-weight: 600;
            color: inherit;
            text-align: left;
            display: flex;
            align-items: center;
        }

        /* ── Main content ── */
        .admin-content {
            flex: 1;
            background: var(--bg-cream);
            border-radius: 20px 0 0 20px;
            min-height: 100vh;
            padding: 2rem;
            overflow-y: auto;
            box-shadow: -4px 0 24px rgba(0, 0, 0, 0.18);
        }


        /* ═══════════════════════════ MOBILE ELEMENTS ═══════════════════════════ */
        .mobile-top-bar {
            display: none;
        }

        .mobile-bottom-nav {
            display: none;
        }

        .mobile-drawer-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.52);
            z-index: 1000;
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-drawer-overlay.open {
            display: block;
            opacity: 1;
        }

        .mobile-drawer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-radius: 24px 24px 0 0;
            padding: 1.25rem 1.25rem calc(85px + env(safe-area-inset-bottom));
            z-index: 1001;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
            box-shadow: 0 -8px 40px rgba(0, 0, 0, 0.25);
        }

        .mobile-drawer.open {
            transform: translateY(0);
        }

        .mobile-drawer-handle {
            width: 40px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 999px;
            margin: -0.25rem auto 1.25rem;
        }

        .mobile-drawer-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #bbb;
            margin-bottom: 0.625rem;
        }

        .mobile-drawer-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.8rem 1rem;
            border-radius: 14px;
            color: #444;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
        }

        .mobile-drawer-link:hover,
        .mobile-drawer-link.active {
            background: linear-gradient(135deg, var(--bancha), var(--olivenite));
            color: white;
        }

        .mobile-drawer-link .dicon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            flex-shrink: 0;
            transition: all 0.2s;
            color: #666;
        }

        .mobile-drawer-link.active .dicon,
        .mobile-drawer-link:hover .dicon {
            background: rgba(255, 255, 255, 0.22);
            color: white;
        }

        /* ═══════════════════════════ RESPONSIVE ═══════════════════════════ */
        @media (max-width: 768px) {
            body {
                background-color: var(--bg-cream);
            }

            .admin-sidebar {
                display: none !important;
            }

            .admin-layout {
                flex-direction: column;
                overflow-x: hidden;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }

            .admin-content {
                border-radius: 0;
                min-height: auto;
                padding: 1rem 0.75rem 140px;
                overflow-x: hidden;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }

            /* Prevent grid items from stretching page width */
            .card, .metrics-grid, .dashboard-charts-grid, .dashboard-charts-grid > * {
                min-width: 0 !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }

            /* Custom styled horizontal scrollbar for tables */
            .table-responsive {
                width: 100% !important;
                overflow-x: auto !important;
                display: block !important;
                padding-bottom: 8px;
            }

            .table-responsive::-webkit-scrollbar {
                height: 8px !important;
                display: block !important;
            }

            .table-responsive::-webkit-scrollbar-track {
                background: #f1f1f1 !important;
                border-radius: 10px !important;
            }

            .table-responsive::-webkit-scrollbar-thumb {
                background: #000000 !important;
                border-radius: 10px !important;
            }

            /* Mobile top bar */
            .mobile-top-bar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: var(--sidebar-bg);
                color: white;
                padding: 0.875rem 1.25rem;
                position: sticky;
                top: 0;
                z-index: 50;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }

            .mob-topbar-logo {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                min-width: 0;
                flex: 1;
                overflow: hidden;
            }
            .mob-topbar-logo span {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .mob-topbar-icon {
                width: 32px;
                height: 32px;
                border-radius: 9px;
                background: var(--bancha);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.9rem;
                color: white;
            }

            /* ─── Curved Active Indicator Bottom Nav (Gambar 2 style) ─── */
            .mobile-bottom-nav {
                display: flex;
                align-items: center;
                position: fixed;
                bottom: 12px;
                left: 50%;
                transform: translateX(-50%);
                width: calc(100% - 24px);
                max-width: 480px;
                background: var(--sidebar-bg);
                border-radius: 999px;
                padding: 6px 8px;
                z-index: 990;
                box-shadow: 0 10px 35px rgba(0, 0, 0, 0.35), 0 2px 8px rgba(0, 0, 0, 0.15);
                height: 62px;
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            /* Store icon circle */
            .mob-logo-btn {
                width: 46px;
                height: 46px;
                border-radius: 50%;
                background: var(--bancha);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.1rem;
                color: white;
                flex-shrink: 0;
                margin-right: 4px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                text-decoration: none;
                transition: transform 0.2s;
            }

            .mob-logo-btn:hover {
                transform: scale(1.05);
                color: white;
            }

            /* Items container */
            .mob-nav-items {
                display: flex;
                align-items: center;
                flex: 1;
                justify-content: space-around;
                height: 100%;
            }

            /* Each item */
            .mob-nav-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
                text-decoration: none;
                color: rgba(255, 255, 255, 0.55);
                font-size: 0.62rem;
                font-weight: 600;
                padding: 4px 6px;
                position: relative;
                flex: 1;
                background: none;
                border: none;
                cursor: pointer;
                font-family: inherit;
                transition: color 0.15s ease;
            }

            .mob-nav-item i {
                font-size: 1.05rem;
                transition: all 0.2s ease;
                z-index: 10;
            }

            .mob-nav-item span {
                font-size: 0.58rem;
                z-index: 10;
                transition: all 0.2s ease;
            }

            .mob-nav-item:hover {
                color: rgba(255, 255, 255, 0.85);
            }

            /* Active dome curve effect (Gambar 2 style) */
            .mob-nav-item.active {
                color: white;
            }

            .mob-nav-item.active i {
                transform: translateY(-13px);
                filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            }

            .mob-nav-item.active span {
                transform: translateY(-3px);
                font-weight: 800;
            }

            /* Center raised circle */
            .mob-nav-item .active-dome {
                display: none;
            }

            .mob-nav-item.active .active-dome {
                display: block;
                position: absolute;
                top: -21px;
                left: 50%;
                transform: translateX(-50%);
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: var(--sidebar-bg);
                border: 2px solid var(--bancha);
                border-bottom-color: transparent;
                z-index: 1;
                box-shadow: 0 -3px 8px rgba(0, 0, 0, 0.2);
            }

            /* Smooth concave wings left/right */
            .mob-nav-item.active .active-dome::before {
                content: '';
                position: absolute;
                bottom: -1px;
                left: -17px;
                width: 16px;
                height: 16px;
                background: transparent;
                border-bottom-right-radius: 12px;
                border-right: 2px solid var(--bancha);
                box-shadow: 4px 4px 0 0 var(--sidebar-bg);
            }

            .mob-nav-item.active .active-dome::after {
                content: '';
                position: absolute;
                bottom: -1px;
                right: -17px;
                width: 16px;
                height: 16px;
                background: transparent;
                border-bottom-left-radius: 12px;
                border-left: 2px solid var(--bancha);
                box-shadow: -4px 4px 0 0 var(--sidebar-bg);
            }

            /* Small accent dot indicator below active item (like gambar2.png) */
            .mob-nav-item .active-dot {
                display: none;
            }

            .mob-nav-item.active .active-dot {
                display: block;
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background: var(--bancha);
                box-shadow: 0 0 6px var(--bancha);
                position: absolute;
                bottom: 3px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 12;
            }
        }
    </style>
</head>

<body>

    {{-- Mobile Top Bar --}}
    <div class="mobile-top-bar">
        <div class="mob-topbar-logo">
            <div class="mob-topbar-icon"><i class="fa-solid fa-store"></i></div>
            <span style="font-weight:800;font-size:0.95rem;color:white;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:0.625rem;flex-shrink:0;">
            <span style="font-size:0.78rem;color:rgba(255,255,255,0.7);white-space:nowrap;">{{ Auth::user()->fresh()->name ?? Auth::user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                    style="background:none;border:none;color:rgba(255,255,255,0.85);cursor:pointer;font-size:1rem;padding:0.2rem;"
                    title="Keluar">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="admin-layout">
        {{-- ── Desktop Sidebar ── --}}
        <aside class="admin-sidebar" id="sidebar">

            {{-- Brand --}}
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <iconify-icon icon="solar:shop-bold" width="18"></iconify-icon>
                </div>
                <div class="sidebar-brand-name">{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</div>
            </div>

            {{-- Navigation --}}
            <div class="sidebar-nav-card">

                <a href="{{ route('admin.dashboard') }}"
                    class="nav-lnk {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:widget-bold" width="18"></iconify-icon></span>
                    <span class="lnk-text">Dashboard</span>
                </a>
                <a href="{{ route('admin.categories') }}"
                    class="nav-lnk {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:tag-bold" width="18"></iconify-icon></span>
                    <span class="lnk-text">Kategori</span>
                </a>
                @php $realProductCount = \App\Models\Product::count(); @endphp
                <a href="{{ route('admin.products') }}"
                    class="nav-lnk {{ request()->routeIs('admin.products*') ? 'active' : '' }}"
                    style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="lnk-icon"><iconify-icon icon="solar:box-bold" width="18"></iconify-icon></span>
                        <span class="lnk-text">Produk</span>
                    </div>
                    <span style="background: rgba(255, 255, 255, 0.25); color: currentColor; border-radius: 99px; padding: 0.1rem 0.55rem; font-size: 0.72rem; font-weight: 800; min-width: 20px; text-align: center; backdrop-filter: blur(4px);">
                        {{ $realProductCount }}
                    </span>
                </a>
                <a href="{{ route('admin.suppliers') }}"
                    class="nav-lnk {{ request()->routeIs('admin.suppliers*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:delivery-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Supplier</span>
                </a>
                <a href="{{ route('admin.stock_logs') }}"
                    class="nav-lnk {{ request()->routeIs('admin.stock_logs*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:layers-bold" width="18"></iconify-icon></span>
                    <span class="lnk-text">Riwayat Stok</span>
                </a>
                <a href="{{ route('admin.cashiers') }}"
                    class="nav-lnk {{ request()->routeIs('admin.cashiers*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:users-group-rounded-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Akun Kasir</span>
                </a>
                <a href="{{ route('admin.shifts') }}"
                    class="nav-lnk {{ request()->routeIs('admin.shifts*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:clock-circle-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Shift Kasir</span>
                </a>
                <a href="{{ route('admin.transactions') }}"
                    class="nav-lnk {{ request()->routeIs('admin.transactions*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:document-text-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Laporan Transaksi</span>
                </a>
                <a href="{{ route('admin.expenses') }}"
                    class="nav-lnk {{ request()->routeIs('admin.expenses*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:wallet-money-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Pengeluaran Toko</span>
                </a>
                <a href="{{ route('admin.refunds') }}"
                    class="nav-lnk {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:arrow-left-up-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Retur Barang</span>
                </a>
                <a href="{{ route('admin.chat') }}"
                    class="nav-lnk {{ request()->routeIs('admin.chat*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:chat-round-dots-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span>Chat Kasir</span>
                        <span id="admin-chat-badge" class="chat-badge" style="display: none; background: var(--terracotta); color: white; border-radius: 99px; padding: 0.1rem 0.55rem; font-size: 0.72rem; font-weight: 800;">0</span>
                    </span>
                </a>

                <div class="sidebar-nav-spacer"></div>
                <div class="sidebar-nav-divider"></div>

                <a href="{{ route('admin.settings') }}"
                    class="nav-lnk {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <span class="lnk-icon"><iconify-icon icon="solar:settings-bold"
                            width="18"></iconify-icon></span>
                    <span class="lnk-text">Pengaturan</span>
                </a>

            </div>

            {{-- Footer --}}
            <div class="sidebar-footer">
                <div class="sidebar-user-row">
                    <div class="sidebar-user-icon">
                        <div class="sidebar-user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    </div>
                    <div class="sidebar-user-text">
                        <div class="sidebar-user-name">{{ Auth::user()->fresh()->name ?? Auth::user()->name }}</div>
                        <div class="sidebar-user-role">Super Admin</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn-sidebar-logout">
                        <span class="lnk-icon"><iconify-icon icon="solar:logout-2-bold"
                                width="17"></iconify-icon></span>
                        <span class="lnk-text">Log Out</span>
                    </button>
                </form>
            </div>

        </aside>

        {{-- ── Main Content ── --}}
        <main class="admin-content">
            @if (session('success'))
                <div class="alert alert-success" style="margin-bottom:1.5rem;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger" style="margin-bottom:1.5rem;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    {{-- ── Mobile Floating Bottom Nav (Redesigned with Active Dome & Dot like Gambar 2) ── --}}
    <nav class="mobile-bottom-nav" id="mobileBottomNav">
        <a href="{{ route('admin.dashboard') }}" class="mob-logo-btn" title="Home">
            <i class="fa-solid fa-store"></i>
        </a>
        <div class="mob-nav-items">
            <a href="{{ route('admin.dashboard') }}"
                class="mob-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <div class="active-dome"></div>
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
                <div class="active-dot"></div>
            </a>
            <a href="{{ route('admin.products') }}"
                class="mob-nav-item {{ request()->routeIs('admin.products*') ? 'active' : '' }}">
                <div class="active-dome"></div>
                <div style="position: relative; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-box-open"></i>
                    <span style="position: absolute; top: -6px; right: -12px; background: rgba(255,255,255,0.9); color: var(--olivenite); font-size: 0.58rem; font-weight: 900; border-radius: 99px; padding: 1px 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">{{ $realProductCount }}</span>
                </div>
                <span>Produk</span>
                <div class="active-dot"></div>
            </a>
            <a href="{{ route('admin.transactions') }}"
                class="mob-nav-item {{ request()->routeIs('admin.transactions*') ? 'active' : '' }}">
                <div class="active-dome"></div>
                <i class="fa-solid fa-receipt"></i>
                <span>Laporan</span>
                <div class="active-dot"></div>
            </a>
            <a href="{{ route('admin.chat') }}"
                class="mob-nav-item {{ request()->routeIs('admin.chat*') ? 'active' : '' }}">
                <div class="active-dome"></div>
                <div style="position: relative; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-comments"></i>
                    <span id="admin-chat-badge-mobile" class="mobile-chat-badge" style="display: none; position: absolute; top: -5px; right: -8px; background: var(--terracotta); color: white; font-size: 0.58rem; font-weight: 900; border-radius: 99px; padding: 1px 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">0</span>
                </div>
                <span>Chat</span>
                <div class="active-dot"></div>
            </a>
            <button type="button"
                class="mob-nav-item {{ request()->routeIs('admin.categories*') || request()->routeIs('admin.cashiers*') || request()->routeIs('admin.settings*') ? 'active' : '' }}"
                onclick="openMobileDrawer()">
                <div class="active-dome"></div>
                <i class="fa-solid fa-grip"></i>
                <span>Lainnya</span>
                <div class="active-dot"></div>
            </button>
        </div>
    </nav>

    {{-- Mobile Bottom Drawer --}}
    <div class="mobile-drawer-overlay" id="drawerOverlay" onclick="closeMobileDrawer()">
        <div class="mobile-drawer" id="mobileDrawer" onclick="event.stopPropagation()">
            <div class="mobile-drawer-handle"></div>
            <div class="mobile-drawer-title">Menu Lainnya</div>
            <a href="{{ route('admin.categories') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-tags"></i></span> Kategori
            </a>
            <a href="{{ route('admin.suppliers') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.suppliers*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-truck-field"></i></span> Supplier
            </a>
            <a href="{{ route('admin.stock_logs') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.stock_logs*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-boxes-packing"></i></span> Riwayat Stok
            </a>
            <a href="{{ route('admin.cashiers') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.cashiers*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-users-gear"></i></span> Akun Kasir
            </a>
            <a href="{{ route('admin.shifts') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.shifts*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-clock-rotate-left"></i></span> Shift Kasir
            </a>
            <a href="{{ route('admin.expenses') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.expenses*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-file-invoice-dollar"></i></span> Pengeluaran Toko
            </a>
            <a href="{{ route('admin.refunds') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.refunds*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-rotate-left"></i></span> Retur Barang
            </a>
            <a href="{{ route('admin.settings') }}"
                class="mobile-drawer-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <span class="dicon"><i class="fa-solid fa-gear"></i></span> Pengaturan
            </a>
            <div style="height:1px;background:#f0f0f0;margin:0.75rem 0;"></div>
            <div
                style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 1rem;background:#fafafa;border-radius:14px;margin-bottom:0.75rem;">
                <div
                    style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--bancha),var(--olivenite));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;color:white;flex-shrink:0;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700;font-size:0.85rem;color:#222;">{{ Auth::user()->name }}</div>
                    <div style="font-size:0.68rem;color:#aaa;">Super Admin</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                    style="width:100%;padding:0.75rem;background:#fee2e2;color:var(--terracotta);border:none;border-radius:14px;font-weight:700;font-size:0.85rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:0.5rem;font-family:inherit;">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    @yield('scripts')
    <script>
        function openMobileDrawer() {
            const overlay = document.getElementById('drawerOverlay');
            const drawer = document.getElementById('mobileDrawer');
            overlay.style.display = 'block';
            requestAnimationFrame(() => {
                overlay.classList.add('open');
                drawer.classList.add('open');
            });
            document.body.style.overflow = 'hidden';
        }

        function closeMobileDrawer() {
            const overlay = document.getElementById('drawerOverlay');
            const drawer = document.getElementById('mobileDrawer');
            overlay.classList.remove('open');
            drawer.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 360);
        }
        let _ts = 0;
        document.getElementById('mobileDrawer').addEventListener('touchstart', e => _ts = e.touches[0].clientY, {
            passive: true
        });
        document.getElementById('mobileDrawer').addEventListener('touchend', e => {
            if (e.changedTouches[0].clientY - _ts > 80) closeMobileDrawer();
        });

        // Global Unread Chat Count Polling for Admin
        function checkAdminUnreadChats() {
            fetch('{{ route('admin.chat.unread-count') }}')
                .then(r => r.json())
                .then(data => {
                    const count = data.unread_count || 0;
                    const badgeDesktop = document.getElementById('admin-chat-badge');
                    const badgeMobile = document.getElementById('admin-chat-badge-mobile');
                    
                    if (count > 0) {
                        if (badgeDesktop) {
                            badgeDesktop.textContent = count;
                            badgeDesktop.style.display = 'inline-block';
                        }
                        if (badgeMobile) {
                            badgeMobile.textContent = count;
                            badgeMobile.style.display = 'inline-block';
                        }
                    } else {
                        if (badgeDesktop) badgeDesktop.style.display = 'none';
                        if (badgeMobile) badgeMobile.style.display = 'none';
                    }
                })
                .catch(() => {});
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            checkAdminUnreadChats();
            setInterval(checkAdminUnreadChats, 8000);
        });
    </script>
</body>

</html>
