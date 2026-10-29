<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS - {{ $settings['app_name'] ?? 'Kasir Zamiel' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Dynamic Theme Color Overrides from Settings --}}
    <style>
        :root {
            --olivenite: {{ $settings['color_primary'] ?? '#313118' }};
            --bancha: {{ $settings['color_secondary'] ?? '#6D6844' }};
            --hillside: {{ $settings['color_accent'] ?? '#CCC6A9' }};
            --terracotta: {{ $settings['color_danger'] ?? '#83482B' }};
        }
    </style>
    <style>
        /* ── POS Layout ── */
        body { background: var(--bg-cream); overflow: hidden; }

        .pos-wrapper {
            display: grid;
            grid-template-columns: 1fr 380px;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Left Panel: Product Grid ── */
        .pos-left {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-cream);
            height: 100vh;
        }

        .pos-topbar {
            background: var(--olivenite);
            color: white;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .pos-topbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            flex: 1;
            min-width: 0; /* Prevents flex overflow */
        }

        .pos-topbar-brand h1 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-topbar-desktop {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .pos-mob-hamburger-btn { display: none; }
        .pos-mob-header-dropdown { display: none; }

        .cart-slide-mode .cart-item {
            min-width: 320px;
        }

        .pos-topbar .btn-logout {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            color: white;
            padding: 0.4rem 0.875rem;
            border-radius: var(--radius-md);
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: background 0.2s;
        }
        .pos-topbar .btn-logout:hover { background: rgba(255,255,255,0.25); }

        .pos-search-bar {
            padding: 0.75rem 1.25rem;
            background: white;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .pos-search-bar input {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            font-family: var(--font-main);
            background: var(--bg-cream) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%236D6844' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.868-3.834zm-5.242 1.154a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z'/%3E%3C/svg%3E") no-repeat 0.75rem center / 1rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .pos-search-bar input:focus { border-color: var(--bancha); }

        /* Category Tabs */
        .cat-tabs {
            display: flex;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            overflow-x: auto;
            background: white;
            border-bottom: 1px solid var(--border-color);
            flex-shrink: 0;
        }
        .cat-tabs::-webkit-scrollbar { height: 3px; }
        .cat-tabs::-webkit-scrollbar-thumb { background: var(--hillside); border-radius: 99px; }

        .cat-tab {
            padding: 0.4rem 1rem;
            border-radius: var(--radius-full);
            border: 1.5px solid var(--border-color);
            background: none;
            font-size: 0.82rem;
            font-family: var(--font-main);
            cursor: pointer;
            white-space: nowrap;
            color: var(--text-dark);
            transition: all 0.2s;
            font-weight: 600;
        }
        .cat-tab.active, .cat-tab:hover {
            background: var(--bancha);
            border-color: var(--bancha);
            color: white;
        }

        /* Product Grid */
        .product-grid {
            flex: 1;
            overflow-y: auto;
            padding: 1.25rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            align-content: start;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            border: 1.5px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }
        .product-card:hover {
            border-color: var(--bancha);
            box-shadow: 0 6px 16px rgba(109,104,68,0.18);
            transform: translateY(-2px);
        }
        .product-card:hover .product-card-add-icon {
            background-color: var(--bancha) !important;
            color: white !important;
        }
        .product-card:active { transform: scale(0.97); }

        .product-card-img {
            width: 100%;
            height: 155px; /* Fixed height for visual consistency */
            background: #f8f6ee;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--hillside);
            font-size: 2rem;
            overflow: hidden;
            position: relative;
        }
        .product-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .product-card-info {
            padding: 0.75rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--olivenite);
            line-height: 1.3;
            margin-bottom: 0.35rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.2em;
        }
        .product-card-price {
            font-size: 0.88rem;
            font-weight: 800;
            color: var(--terracotta);
        }
        .product-card-stock {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        /* ── Right Panel: Cart ── */
        .pos-right {
            background: white;
            border-left: 1.5px solid var(--border-color);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            height: 100vh;
        }

        .cart-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            background: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cart-header h2 { font-size: 1rem; margin: 0; color: var(--olivenite); font-weight: 800; }

        /* Cart items table header */
        .cart-table-header {
            display: grid;
            grid-template-columns: 22px 1fr auto;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            background: #f8f6ee;
            border-bottom: 1.5px solid var(--border-color);
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .cart-item {
            background: var(--bg-cream);
            border-radius: var(--radius-md);
            padding: 0.5rem 0.625rem;
            display: grid;
            grid-template-columns: 22px 1fr auto;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid var(--border-color);
        }
        .cart-item-num {
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--text-muted);
            background: var(--border-color);
            border-radius: 50%;
            width: 18px; height: 18px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .cart-item-name {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--olivenite);
            line-height: 1.25;
        }
        .cart-item-price {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }
        .cart-item-price .price-original {
            text-decoration: line-through;
            color: #ef4444;
            font-weight: 600;
        }
        .cart-item-price .price-discounted {
            color: #16a34a;
            font-weight: 800;
        }
        .cart-qty-ctrl {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .qty-btn {
            width: 26px; height: 26px;
            border-radius: 50%;
            border: 1.5px solid var(--border-color);
            background: white;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            color: var(--bancha);
            font-weight: 700;
            transition: all 0.15s;
        }
        .qty-btn:hover { background: var(--bancha); color: white; border-color: var(--bancha); }
        .qty-val { font-weight: 700; font-size: 0.9rem; min-width: 20px; text-align: center; color: var(--olivenite); }

        /* Cart Footer / Checkout */
        .cart-footer {
            background: white;
            border-top: 1.5px solid var(--border-color);
            padding: 1rem 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cart-total-label { font-size: 0.9rem; color: var(--text-muted); }
        .cart-total-val { font-size: 1.2rem; font-weight: 800; color: var(--olivenite); }

        .pay-method-group {
            display: flex; gap: 0.4rem;
        }
        .pay-method-btn {
            flex: 1;
            padding: 0.45rem 0.25rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            background: none;
            font-size: 0.78rem;
            font-family: var(--font-main);
            cursor: pointer;
            text-align: center;
            font-weight: 700;
            color: var(--text-dark);
            transition: all 0.15s;
        }
        .pay-method-btn.active {
            background: var(--olivenite);
            border-color: var(--olivenite);
            color: white;
        }

        .quick-cash-btn {
            flex: 1;
            min-width: 50px;
            padding: 0.35rem 0.25rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            background: var(--bg-white);
            font-size: 0.72rem;
            font-family: var(--font-main);
            cursor: pointer;
            text-align: center;
            font-weight: 700;
            color: var(--bancha);
            transition: all 0.15s;
        }
        .quick-cash-btn:hover {
            background: var(--bancha);
            color: white;
            border-color: var(--bancha);
        }

        .pay-input-row {
            display: flex; gap: 0.5rem; align-items: center;
        }
        .pay-input-row label { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; font-weight: 600; }
        .pay-input-row input {
            flex: 1; padding: 0.5rem 0.75rem;
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: 0.9rem; font-family: var(--font-main);
            text-align: right; font-weight: 800;
            outline: none; color: var(--olivenite);
        }
        .pay-input-row input:focus { border-color: var(--bancha); }

        .change-row {
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(109,104,68,0.07);
            border-radius: var(--radius-md);
            padding: 0.5rem 0.75rem;
        }
        .change-label { font-size: 0.8rem; color: var(--bancha); font-weight: 600; }
        .change-val { font-size: 1rem; font-weight: 800; color: var(--bancha); }

        .btn-checkout {
            width: 100%;
            padding: 0.875rem;
            background: var(--terracotta);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-family: var(--font-main);
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(131,72,43,0.25);
        }
        .btn-checkout:hover { opacity: 0.9; }
        .btn-checkout:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; }

        /* Floating Chat Button */
        .chat-float-btn {
            position: fixed;
            bottom: 1.5rem;
            left: 1.5rem;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--bancha);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
            z-index: 300;
            transition: transform 0.2s;
        }
        .chat-float-btn:hover { transform: scale(1.08); }
        .chat-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--terracotta);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.72rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            z-index: 10;
        }

        /* Chat Popup */
        .chat-popup {
            position: fixed; bottom: 5.5rem; left: 1.5rem;
            width: 320px; height: 420px;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            z-index: 310;
            display: none;
            flex-direction: column;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .chat-popup.open { display: flex; }

        .chat-popup-header {
            padding: 0.875rem 1rem;
            background: var(--olivenite);
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .chat-popup-msgs {
            flex: 1; overflow-y: auto;
            padding: 0.75rem;
            background: #efeae2;
            background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
            background-blend-mode: overlay;
            background-color: rgba(239, 234, 226, 0.55);
            display: flex; flex-direction: column; gap: 0.4rem;
        }

        /* WA Style Bubble in Cashier View */
        .msg-bubble-wrapper {
            display: flex;
            width: 100%;
            margin-bottom: 2px;
        }
        .msg-bubble-wrapper.outgoing { justify-content: flex-end; }
        .msg-bubble-wrapper.incoming { justify-content: flex-start; }

        .msg-bubble {
            max-width: 75%;
            padding: 0.45rem 0.65rem 0.35rem;
            font-size: 0.82rem;
            line-height: 1.4;
            position: relative;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
            display: flex;
            flex-direction: column;
        }
        .msg-bubble.outgoing {
            background: #d9fdd3;
            color: #111b21;
            border-radius: 8px 0px 8px 8px;
        }
        .msg-bubble.incoming {
            background: #ffffff;
            color: #111b21;
            border-radius: 0px 8px 8px 8px;
        }
        .msg-text {
            word-break: break-word;
            padding-right: 42px;
        }
        .msg-time {
            align-self: flex-end;
            font-size: 0.62rem;
            color: #667781;
            margin-top: -3px;
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 2px;
            white-space: nowrap;
        }
        .msg-time i {
            font-size: 0.68rem;
            color: #53bdeb;
        }

        .chat-popup-input {
            display: flex; gap: 0.5rem;
            padding: 0.625rem;
            border-top: 1px solid var(--border-color);
            background: #f0f2f5;
            align-items: center;
        }
        .chat-popup-input input {
            flex: 1; padding: 0.55rem 0.85rem;
            border: none;
            border-radius: 8px;
            font-size: 0.85rem; font-family: var(--font-main);
            outline: none;
            background: white;
        }
        .chat-popup-input button {
            width: 36px; height: 36px;
            border-radius: 50%; background: var(--bancha);
            color: white; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        /* Success Modal */
        .success-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.52);
            display: flex; align-items: center; justify-content: center;
            z-index: 300; display: none;
            backdrop-filter: blur(4px);
        }
        .success-modal {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            text-align: center;
            max-width: 360px;
            width: 90%;
            box-shadow: 0 16px 48px rgba(0,0,0,0.25);
        }
        .success-icon { font-size: 3.5rem; color: var(--success); margin-bottom: 1rem; }
        .success-title { font-size: 1.3rem; font-weight: 800; color: var(--olivenite); margin-bottom: 0.5rem; }
        .success-change {
            font-size: 2rem; font-weight: 800; color: var(--terracotta);
            margin: 1rem 0;
        }

        #qrcode-container { margin: 1rem auto; }

        .empty-cart {
            text-align: center; color: var(--text-muted);
            padding: 3rem 1rem; font-size: 0.9rem;
        }
        .empty-cart i { font-size: 3rem; margin-bottom: 1rem; color: var(--hillside); display: block; }

        .cart-count-badge {
            background: var(--terracotta); color: white;
            border-radius: 99px; padding: 0.1rem 0.45rem;
            font-size: 0.72rem; font-weight: 800;
        }

        /* ── Mobile Responsive POS Styling ── */
        .mobile-cart-toggle-bar { display: none; }
        .mobile-cart-close { display: none; }

        @media (max-width: 768px) {
            body { overflow-x: hidden; overflow-y: auto; background: var(--bg-cream); }

            .pos-wrapper {
                display: flex;
                flex-direction: column;
                height: auto;
                min-height: 100vh;
                padding-bottom: 75px;
            }

            .pos-left {
                height: auto;
                min-height: auto;
                flex: none;
                overflow: visible;
                background: transparent;
            }

            .product-grid {
                height: auto;
                overflow: visible;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
                padding: 0.875rem 0.75rem;
            }

            .product-card {
                border-radius: 16px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            }

            .product-card-info {
                padding: 0.625rem;
            }

            .product-card-name {
                font-size: 0.82rem;
                font-weight: 700;
                line-height: 1.3;
                height: 2.3em;
            }

            .product-card-price {
                font-size: 0.88rem;
                font-weight: 800;
            }

            /* Cart Drawer */
            .pos-right {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                height: 85vh;
                z-index: 200;
                transform: translateY(100%);
                transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
                box-shadow: 0 -10px 40px rgba(0,0,0,0.3);
                border-left: none;
                border-radius: 24px 24px 0 0;
                background: var(--bg-cream);
            }
            .pos-right.open {
                transform: translateY(0);
            }

            .mobile-cart-close {
                display: block;
            }

            /* Floating Chat Button positioning on mobile */
            .chat-float-btn {
                bottom: 4.8rem;  /* above the sticky cart bar */
                right: 1.25rem;
                left: auto;
                width: 48px;
                height: 48px;
                font-size: 1.1rem;
                z-index: 300; /* always above cart bar z-index:140 */
            }

            .chat-popup {
                bottom: 9rem;
                right: 1.25rem;
                left: auto;
                width: calc(100% - 2.5rem);
                max-width: 340px;
                z-index: 300;
            }

            /* Mobile Sticky Bottom Cart Bar */
            .mobile-cart-toggle-bar {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 60px;
                background: var(--olivenite);
                color: white;
                align-items: center;
                justify-content: space-between;
                padding: 0 1.25rem;
                z-index: 140;
                cursor: pointer;
                box-shadow: 0 -4px 20px rgba(0,0,0,0.2);
            }
            .pos-topbar {
                justify-content: space-between;
                position: relative;
            }
            .pos-topbar-desktop { display: none !important; }
            .pos-mob-hamburger-btn {
                display: flex;
                align-items: center; justify-content: center;
                width: 38px; height: 38px;
                border-radius: 8px;
                background: rgba(255,255,255,0.18);
                border: 1px solid rgba(255,255,255,0.25);
                color: white;
                font-size: 1.1rem;
                cursor: pointer;
            }
            .pos-mob-header-dropdown {
                display: none;
                flex-direction: column;
                gap: 0.65rem;
                background: var(--olivenite);
                padding: 0.875rem 1.25rem;
                border-bottom: 1px solid rgba(255,255,255,0.15);
                color: white;
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
            .pos-mob-header-dropdown.open {
                display: flex;
            }
            .pos-mob-drop-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.82rem;
                font-weight: 600;
                background: rgba(255,255,255,0.12);
                padding: 0.5rem 0.85rem;
                border-radius: 10px;
            }
            .btn-logout-full {
                width: 100%;
                background: var(--terracotta);
                color: white;
                border: none;
                padding: 0.6rem;
                border-radius: 10px;
                font-weight: 700;
                font-size: 0.85rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
<div class="pos-wrapper">
    <!-- LEFT: Product Grid Panel -->
    <div class="pos-left">
        <!-- Top Bar -->
        <div class="pos-topbar">
            <div class="pos-topbar-brand">
                <i class="fa-solid fa-store" style="font-size: 1.1rem; color: white; flex-shrink: 0;"></i>
                <div style="min-width:0;">
                    <div style="font-size: 0.65rem; font-weight: 600; color: rgba(255,255,255,0.75); letter-spacing:0.03em; white-space:nowrap;">{{ $settings['app_name'] ?? 'Kasir Zamiel' }}</div>
                    <h1 style="font-size: 1rem; font-weight: 800; color: white; margin: 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $settings['store_name'] ?? 'Toko Kami' }}
                    </h1>
                </div>
            </div>

            {{-- Desktop Elements --}}
            <div class="pos-topbar-desktop">
                <div id="pos-live-clock" style="font-size: 0.82rem; font-weight: 600; background: rgba(255,255,255,0.18); padding: 0.35rem 0.85rem; border-radius: var(--radius-full); display: flex; align-items: center; gap: 0.4rem; color: white;">
                    <i class="fa-regular fa-clock"></i> <span id="pos-clock-str">Memuat jam...</span>
                </div>
                <span style="font-size: 0.8rem; opacity: 0.85;" id="pos-user-name-desktop"><i class="fa-solid fa-user"></i> {{ Auth::user()->fresh()->name ?? Auth::user()->name }}</span>
                {{-- Shift Status Badge --}}
                <span id="shift-status-badge" style="font-size: 0.75rem; font-weight: 700; background: rgba(255,255,255,0.2); color: white; padding: 0.3rem 0.7rem; border-radius: 99px; cursor: pointer;" onclick="openShiftModal()" title="Kelola Shift Kasir">
                    <i class="fa-solid fa-circle" id="shift-dot" style="font-size: 0.55rem; color: #fbbf24;"></i> <span id="shift-label">Cek Shift...</span>
                </span>
                <a href="{{ route('cashier.refunds.index') }}" class="btn-logout" style="text-decoration:none;">
                    <i class="fa-solid fa-rotate-left"></i> Retur
                </a>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
                </form>
            </div>

            {{-- Mobile Hamburger Button --}}
            <button type="button" class="pos-mob-hamburger-btn" onclick="togglePosMobileMenu()" title="Menu Kasir">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        {{-- Mobile Topbar Dropdown Menu --}}
        <div class="pos-mob-header-dropdown" id="pos-mob-header-dropdown">
            <div class="pos-mob-drop-item">
                <i class="fa-regular fa-clock" style="color: white;"></i>
                <span id="pos-mob-clock-str">Memuat jam...</span>
            </div>
            <div class="pos-mob-drop-item">
                <i class="fa-solid fa-user" style="color: white;"></i>
                <span id="pos-user-name-mobile">{{ Auth::user()->fresh()->name ?? Auth::user()->name }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0; width: 100%;">
                @csrf
                <button type="submit" class="btn-logout-full">
                    <i class="fa-solid fa-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
        <!-- Search Bar -->
        <div class="pos-search-bar">
            <input type="text" id="product-search"
                placeholder="@if(($settings['enable_barcode'] ?? '1') == '1')Cari produk atau scan barcode...@else Cari produk...@endif">
        </div>

        <!-- Category Tabs -->
        <div class="cat-tabs">
            <button class="cat-tab active" onclick="filterCategory('semua', this)">
                <i class="fa-solid fa-border-all"></i> Semua
            </button>
            @foreach($categories as $cat)
                <button class="cat-tab" onclick="filterCategory('{{ $cat->id }}', this)">
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="product-grid">
            @foreach($categories as $cat)
                @foreach($cat->products as $product)
                    <div class="product-card"
                         style="position: relative;"
                         data-cat="{{ $product->category_id }}"
                         data-name="{{ $product->name }}"
                         data-barcode="{{ $product->barcode }}"
                         data-price="{{ $product->price_sell }}"
                         data-stock="{{ $product->stock }}"
                         data-discount="{{ $product->discount_persen ?? 0 }}"
                         onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price_sell }}, {{ $product->stock }}, {{ $product->discount_persen ?? 0 }})">
                        @if(($product->discount_persen ?? 0) > 0)
                            <span style="position: absolute; top: 6px; right: 6px; background: #dc2626; color: white; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; z-index: 2; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                                -{{ number_format($product->discount_persen, 0) }}%
                            </span>
                        @endif
                        <div class="product-card-img">
                            @if($product->image)
                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}">
                            @else
                                <i class="fa-solid fa-box"></i>
                            @endif
                        </div>
                        <div class="product-card-info">
                            <div class="product-card-name">{{ $product->name }}</div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem;">
                                @php
                                    $discVal = (float)($product->discount_persen ?? 0);
                                    $priceSell = (float)$product->price_sell;
                                    $effectivePrice = $priceSell - ($priceSell * ($discVal / 100));
                                @endphp
                                @if($discVal > 0)
                                    <div>
                                        <div style="text-decoration: line-through; font-size: 0.7rem; color: #888;">Rp {{ number_format($priceSell, 0, ',', '.') }}</div>
                                        <div class="product-card-price" style="color: #dc2626; font-weight: 800;">Rp {{ number_format($effectivePrice, 0, ',', '.') }}</div>
                                    </div>
                                @else
                                    <div class="product-card-price">Rp {{ number_format($priceSell, 0, ',', '.') }}</div>
                                @endif
                                <div class="product-card-add-icon" style="color: var(--bancha); font-size: 0.95rem; background: var(--bg-cream); border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                    <i class="fa-solid fa-cart-plus"></i>
                                </div>
                            </div>
                            @if(($settings['enable_stock'] ?? '1') == '1')
                            <div class="product-card-stock">Stok: {{ $product->stock }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <!-- RIGHT: Cart Panel -->
    <div class="pos-right" id="cart-panel">
        <div class="cart-header">
            <h2><i class="fa-solid fa-cart-shopping" style="color: var(--bancha);"></i> Keranjang <span class="cart-count-badge" id="cart-count">0</span></h2>
            <button onclick="toggleMobileCart()" class="mobile-cart-close" style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:var(--text-muted); padding: 0.25rem;">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
            <button onclick="clearCart()" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:0.8rem;font-weight:700;">
                <i class="fa-solid fa-trash"></i> Kosongkan
            </button>
        </div>
        <!-- Cart Pagination Banner (shown when > 10 items) -->
        <div id="cart-slide-toggle-row" style="display: none; align-items: center; justify-content: space-between; background: linear-gradient(90deg,#fff7ed,#ffedd5); border-bottom: 1px solid #fed7aa; padding: 0.4rem 0.75rem; flex-shrink: 0; gap: 0.5rem;">
            <span style="font-size:0.72rem; font-weight:700; color:#9a3412; display:flex; align-items:center; gap:0.3rem;">
                <i class="fa-solid fa-layer-group"></i>
                <span id="cart-item-unique-count">0</span> produk
            </span>
            <div style="display:flex; align-items:center; gap:0.35rem;">
                <button type="button" onclick="cartPagePrev()" id="cart-btn-prev"
                    style="background:#ea580c; color:white; border:none; width:26px; height:26px; border-radius:6px; font-size:0.78rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:opacity .15s;">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <span id="cart-page-indicator" style="font-size:0.72rem; font-weight:800; color:#9a3412; min-width:38px; text-align:center;">1/1</span>
                <button type="button" onclick="cartPageNext()" id="cart-btn-next"
                    style="background:#ea580c; color:white; border:none; width:26px; height:26px; border-radius:6px; font-size:0.78rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:opacity .15s;">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="cart-table-header" id="cart-table-header" style="display:none;">
            <span>#</span>
            <span>Produk &amp; Harga</span>
            <span>Qty</span>
        </div>

        <div class="cart-items" id="cart-items">
            <div class="empty-cart" id="empty-cart-msg">
                <i class="fa-solid fa-cart-shopping"></i>
                Keranjang masih kosong.<br>Tap produk untuk menambahkan.
            </div>
        </div>

        <div class="cart-footer">
            <!-- Subtotal -->
            <div class="cart-total-row" style="font-size: 0.85rem; color: var(--text-muted); border-bottom: none; padding-bottom: 0; margin-bottom: 0.2rem;">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>

            <!-- Product Discount Row -->
            <div class="cart-total-row" id="cart-discount-row" style="display: none; font-size: 0.85rem; color: #d97706; border-bottom: none; padding-bottom: 0; margin-bottom: 0.2rem; font-weight: 600;">
                <span><i class="fa-solid fa-tag"></i> Diskon Produk</span>
                <span id="cart-discount-val">- Rp 0</span>
            </div>

            <!-- Total -->
            <div class="cart-total-row">
                <span class="cart-total-label">Total Belanja</span>
                <span class="cart-total-val" id="cart-total">Rp 0</span>
            </div>

            <!-- Payment method buttons -->
            <div class="pay-method-group" id="pay-method-group">
                <button class="pay-method-btn active" onclick="setPayMethod('Tunai', this)"><i class="fa-solid fa-money-bill-wave"></i> Tunai</button>
                <button class="pay-method-btn" onclick="setPayMethod('QRIS', this)"><i class="fa-brands fa-qrcode"></i> QRIS</button>
                <button class="pay-method-btn" onclick="setPayMethod('Transfer', this)"><i class="fa-solid fa-building-columns"></i> Transfer</button>
            </div>

            <!-- Non-Cash Info Banner (Shown for QRIS & Transfer) -->
            <div id="non-cash-info" style="display: none; background: #e0f2fe; border: 1.5px solid #7dd3fc; color: #0369a1; border-radius: var(--radius-md); padding: 0.65rem 0.875rem; margin: 0.5rem 0; font-size: 0.82rem; font-weight: 700; text-align: center; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class="fa-solid fa-qrcode"></i> Pembayaran <span id="non-cash-type">QRIS</span> Pas — Rp <span id="non-cash-total">0</span>
            </div>

            <!-- Pay Amount -->
            <div class="pay-input-row" id="pay-amount-row">
                <label>Bayar (Rp)</label>
                <input type="text" id="pay-amount" placeholder="0" oninput="formatNumberInput(this); calcChange();">
            </div>

            <!-- Quick Cash Buttons -->
            <div id="quick-cash-group" style="display: flex; gap: 0.25rem; margin: 0.3rem 0; width: 100%; flex-wrap: wrap;"></div>

            <!-- Change -->
            <div class="change-row" id="change-row">
                <span class="change-label"><i class="fa-solid fa-coins"></i> Kembalian</span>
                <span class="change-val" id="change-val">Rp 0</span>
            </div>

            <!-- Checkout Button -->
            <button class="btn-checkout" id="checkout-btn" onclick="processCheckout()" disabled>
                <i class="fa-solid fa-check-circle"></i> Proses Pembayaran
            </button>
        </div>
    </div>
</div>

<!-- Mobile Sticky Bottom Cart Bar -->
<div class="mobile-cart-toggle-bar" onclick="toggleMobileCart()">
    <span style="font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-cart-shopping"></i> Keranjang (<span id="mobile-cart-count">0</span>)
    </span>
    <span style="font-weight: 800; font-size: 1.15rem;" id="mobile-cart-total">Rp 0</span>
</div>

<!-- Floating Chat Button -->
<button class="chat-float-btn" onclick="toggleChat()" id="chat-float-btn" title="Chat dengan Admin">
    <i class="fa-solid fa-comment-dots"></i>
    <span id="chat-badge" class="chat-badge" style="display: none;">0</span>
</button>

<!-- Chat Popup -->
<div class="chat-popup" id="chat-popup">
    <div class="chat-popup-header">
        <span><i class="fa-solid fa-headset"></i> Chat dengan Admin</span>
        <button onclick="toggleChat()" style="background:none;border:none;color:white;cursor:pointer;font-size:1rem;">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <div class="chat-popup-msgs" id="chat-popup-msgs">
        <div style="text-align:center;color:var(--text-muted);font-size:0.8rem;margin-top:1rem;">Memuat pesan...</div>
    </div>
    <div class="chat-popup-input">
        <input type="text" id="chat-msg-input" placeholder="Ketik pesan..." onkeypress="if(event.key==='Enter') sendCashierMsg()">
        <button onclick="sendCashierMsg()"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<!-- QRIS Scan Pre-Payment Modal (Shows when QRIS chosen before checkout) -->
<div class="success-overlay" id="qris-pre-overlay" style="display: none;">
    <div class="success-modal" style="max-width: 360px; width: 92%; padding: 1.5rem; text-align: center; border-radius: 20px;">
        <div style="font-size: 2.2rem; color: #0284c7; margin-bottom: 0.25rem;"><i class="fa-solid fa-qrcode"></i></div>
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--olivenite); margin-bottom: 0.25rem;">Scan QRIS Pembayaran</h3>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.85rem;">Minta pelanggan scan QRIS di bawah ini untuk membayar.</p>

        <div style="background: white; border: 2px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1rem; display: flex; flex-direction: column; align-items: center;">
            @if(!empty($settings['store_qris']))
                <img src="{{ asset($settings['store_qris']) }}" alt="QRIS Toko" style="max-height: 190px; max-width: 190px; object-fit: contain; border-radius: 8px; border: 1px solid var(--border-color); padding: 4px; background: white;">
            @else
                <div style="font-size: 0.8rem; color: var(--terracotta); font-weight: bold; padding: 1rem; border: 1px dashed var(--border-color); border-radius: 6px;">Gambar QRIS toko belum di-upload di Settings Admin.</div>
            @endif
            <div style="margin-top: 0.75rem; font-weight: 800; font-size: 1.15rem; color: var(--olivenite);">
                Total: Rp <span id="qris-pre-total">0</span>
            </div>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn-checkout" style="flex: 1; background: #e5e7eb; color: #374151; border: none; font-size: 0.85rem; padding: 0.6rem;" onclick="closeQrisPreModal()">
                Batal
            </button>
            <button type="button" class="btn-checkout" style="flex: 1.5; background: #0284c7; border-color: #0284c7; font-size: 0.85rem; padding: 0.6rem;" onclick="confirmQrisCheckout()">
                <i class="fa-solid fa-check"></i> Sudah Bayar
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="success-overlay" id="success-overlay" onclick="closeSuccess()">
    <div class="success-modal" onclick="event.stopPropagation()" style="max-width: 380px; width: 92%; padding: 1.5rem; border-radius: 20px; text-align: center;">
        <div class="success-icon" style="margin-bottom: 0.5rem; font-size: 2.8rem; color: #16a34a;"><i class="fa-solid fa-circle-check"></i></div>
        <div class="success-title" style="font-size: 1.25rem; font-weight: 800; color: var(--olivenite); margin-bottom: 0.25rem;">Pembayaran Berhasil!</div>
        <div style="font-family: monospace; font-size: 0.8rem; font-weight: 700; color: var(--bancha); background: rgba(109,104,68,0.1); padding: 0.35rem 0.75rem; border-radius: var(--radius-full); display: inline-block; margin-bottom: 1rem;" id="modal-invoice-num">
            KZR-XXXX
        </div>

        <!-- Payment Result Summary Box -->
        <div style="background: var(--bg-cream); border: 1.5px solid var(--border-color); border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                <span>Metode Pembayaran</span>
                <strong style="color: var(--olivenite);" id="modal-pay-method">Tunai</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                <span>Total Belanja</span>
                <strong style="color: var(--olivenite);" id="modal-total-price">Rp 0</strong>
            </div>
            <div id="modal-change-container">
                <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 800; color: var(--terracotta); border-top: 1px dashed var(--border-color); padding-top: 0.4rem; margin-top: 0.4rem;">
                    <span>Kembalian</span>
                    <span id="modal-change">Rp 0</span>
                </div>
            </div>
            <div id="modal-lunas-badge" style="display: none; font-size: 0.85rem; font-weight: 700; color: #16a34a; background: #dcfce7; padding: 0.35rem; border-radius: 6px; margin-top: 0.4rem; text-align: center;">
                <i class="fa-solid fa-circle-check"></i> LUNAS PAS (Tanpa Kembalian)
            </div>
        </div>

        <!-- Kirim Struk via WhatsApp -->
        <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 12px; padding: 1rem; width: 100%; margin-bottom: 0.75rem;">
            <div style="font-size: 0.8rem; font-weight: 700; color: #15803d; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-brands fa-whatsapp"></i> Kirim Struk Digital via WhatsApp
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <input type="tel" id="wa-phone-input" placeholder="Nomor HP (contoh: 081234567890)" style="flex: 1; padding: 0.55rem 0.75rem; border: 1.5px solid #bbf7d0; border-radius: 8px; font-size: 0.85rem; background: white; outline: none;" maxlength="15">
                <button type="button" onclick="sendWhatsAppReceipt()" style="background: #16a34a; color: white; border: none; border-radius: 8px; padding: 0.55rem 0.9rem; font-weight: 700; cursor: pointer; font-size: 0.82rem; white-space: nowrap; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fa-brands fa-whatsapp"></i> Kirim WA
                </button>
            </div>
            <div style="font-size: 0.72rem; color: #4ade80; margin-top: 0.4rem;">Kosongkan jika tidak ingin mengirim struk.</div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 0.5rem; margin-top: 0.25rem; width: 100%;">
            <button type="button" class="btn-checkout" style="flex: 1; background: var(--bancha); border-color: var(--bancha); font-size: 0.82rem; padding: 0.6rem;" onclick="printReceiptDirect()">
                <i class="fa-solid fa-print"></i> Cetak Struk
            </button>
            <button type="button" class="btn-checkout" style="flex: 1; font-size: 0.82rem; padding: 0.6rem;" onclick="closeSuccess()">
                <i class="fa-solid fa-arrow-right"></i> Transaksi Baru
            </button>
        </div>
    </div>
</div>

<!-- =================== SHIFT KASIR MODAL =================== -->
<div id="shift-modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; padding:1.5rem; width:92%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="text-align:center; margin-bottom:1.25rem;">
            <div style="font-size:2rem; margin-bottom:0.5rem;">🕐</div>
            <h3 style="font-size:1.1rem; font-weight:800; color:var(--olivenite); margin:0 0 0.25rem;">Manajemen Shift Kasir</h3>
            <p style="font-size:0.82rem; color:#64748b; margin:0;">Buka shift saat mulai kerja & tutup saat selesai. Saldo laci uang akan diperiksa oleh Super Admin.</p>
        </div>

        <!-- Info shift aktif -->
        <div id="shift-active-info" style="display:none; background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:12px; padding:1rem; margin-bottom:1rem;">
            <div style="font-size:0.8rem; font-weight:700; color:#15803d; margin-bottom:0.5rem;"><i class="fa-solid fa-circle-play"></i> Shift Sedang Aktif</div>
            <div id="shift-active-detail" style="font-size:0.85rem; color:#374151;"></div>
        </div>

        <!-- Form Buka Shift -->
        <div id="shift-open-form">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:var(--olivenite); margin-bottom:0.4rem;">Modal Awal Laci (Rp) *</label>
            <input type="text" id="shift-starting-cash" placeholder="Contoh: 200.000" oninput="formatNumberInput(this)" style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid #e5e7eb; border-radius:10px; font-size:0.9rem; margin-bottom:1rem; box-sizing:border-box;">
            <button type="button" onclick="doOpenShift()" style="width:100%; background:#16a34a; color:white; border:none; border-radius:10px; padding:0.75rem; font-weight:700; font-size:0.9rem; cursor:pointer;">
                <i class="fa-solid fa-circle-play"></i> Buka Shift & Mulai Berjualan
            </button>
        </div>

        <!-- Form Tutup Shift -->
        <div id="shift-close-form" style="display:none;">
            <div style="background:#fef9c3; border:1px solid #fde047; border-radius:10px; padding:0.75rem; margin-bottom:1rem; font-size:0.82rem; color:#713f12;">
                <i class="fa-solid fa-triangle-exclamation"></i> Hitung uang tunai di laci dengan teliti sebelum mengisi saldo akhir!
            </div>
            <label style="display:block; font-size:0.85rem; font-weight:700; color:var(--olivenite); margin-bottom:0.4rem;">Saldo Uang Fisik di Laci (Rp) *</label>
            <input type="text" id="shift-actual-cash" placeholder="Total uang tunai yang ada di laci" oninput="formatNumberInput(this)" style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid #e5e7eb; border-radius:10px; font-size:0.9rem; margin-bottom:0.75rem; box-sizing:border-box;">
            <label style="display:block; font-size:0.85rem; font-weight:700; color:var(--olivenite); margin-bottom:0.4rem;">Catatan (opsional)</label>
            <textarea id="shift-notes" rows="2" placeholder="Catatan untuk laporan shift" style="width:100%; padding:0.65rem 0.85rem; border:1.5px solid #e5e7eb; border-radius:10px; font-size:0.85rem; margin-bottom:1rem; box-sizing:border-box; resize:none;"></textarea>
            <button type="button" onclick="doCloseShift()" style="width:100%; background:#dc2626; color:white; border:none; border-radius:10px; padding:0.75rem; font-weight:700; font-size:0.9rem; cursor:pointer;">
                <i class="fa-solid fa-circle-stop"></i> Tutup Shift & Selesai
            </button>
        </div>

        <button type="button" onclick="closeShiftModal()" style="width:100%; margin-top:0.75rem; background:#f1f5f9; color:#374151; border:none; border-radius:10px; padding:0.65rem; font-weight:600; cursor:pointer; font-size:0.85rem;">
            Tutup
        </button>
    </div>
</div>

<!-- Hidden Print Iframe -->
<iframe id="receipt-print-iframe" style="position: fixed; right: -1000px; bottom: -1000px; width: 0; height: 0; border: 0;"></iframe>

<script>
let cart = {};
let payMethod = 'Tunai';
let currentTotal = 0;
const stockControlEnabled = {{ ($settings['enable_stock'] ?? '1') == '1' ? 'true' : 'false' }};
let subtotal = 0;
let discountAmount = 0;

function addToCart(id, name, price, stock, discountPersen = 0) {
    const disc = parseFloat(discountPersen) || 0;
    if (cart[id]) {
        if (stockControlEnabled && cart[id].qty >= stock) {
            showNotif(`Stok ${name} hanya tersisa ${stock}.`, 'warning');
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = { id, name, price, stock, discountPersen: disc, qty: 1 };
    }
    renderCart();
    showNotif(`${name} ditambahkan`, 'success');
}

function changeQty(id, delta) {
    if (!cart[id]) return;
    cart[id].qty += delta;
    if (cart[id].qty <= 0) {
        delete cart[id];
    } else if (stockControlEnabled && cart[id].qty > cart[id].stock) {
        cart[id].qty = cart[id].stock;
        showNotif('Stok tidak mencukupi.', 'warning');
    }
    renderCart();
}

function clearCart() {
    cart = {};
    discountAmount = 0;
    renderCart();
}

const CART_PAGE_SIZE = 10;
let cartPage = 0; // current page index (0-based)

function cartPagePrev() {
    if (cartPage > 0) { cartPage--; renderCart(); }
}
function cartPageNext() {
    const ids = Object.keys(cart);
    const totalPages = Math.ceil(ids.length / CART_PAGE_SIZE);
    if (cartPage < totalPages - 1) { cartPage++; renderCart(); }
}

function renderCart() {
    const ids = Object.keys(cart);
    const countEl = document.getElementById('cart-count');
    const itemsEl = document.getElementById('cart-items');
    const btn = document.getElementById('checkout-btn');

    subtotal = Object.values(cart).reduce((s, i) => s + i.price * i.qty, 0);
    discountAmount = Object.values(cart).reduce((s, i) => {
        const itemDisc = i.price * (i.discountPersen / 100);
        return s + (itemDisc * i.qty);
    }, 0);
    currentTotal = Math.max(0, subtotal - discountAmount);

    const totalCount = ids.reduce((s, k) => s + cart[k].qty, 0);
    if (countEl) countEl.textContent = totalCount;

    document.getElementById('cart-subtotal').textContent = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');

    const discRow = document.getElementById('cart-discount-row');
    const discValEl = document.getElementById('cart-discount-val');
    if (discountAmount > 0) {
        if (discRow) discRow.style.display = 'flex';
        if (discValEl) discValEl.textContent = '- Rp ' + Math.round(discountAmount).toLocaleString('id-ID');
    } else {
        if (discRow) discRow.style.display = 'none';
    }

    document.getElementById('cart-total').textContent = 'Rp ' + Math.round(currentTotal).toLocaleString('id-ID');

    const mobileCount = document.getElementById('mobile-cart-count');
    const mobileTotal = document.getElementById('mobile-cart-total');
    if (mobileCount) mobileCount.textContent = totalCount;
    if (mobileTotal) mobileTotal.textContent = 'Rp ' + Math.round(currentTotal).toLocaleString('id-ID');

    const tableHeader = document.getElementById('cart-table-header');
    const slideBanner = document.getElementById('cart-slide-toggle-row');
    const uniqueCountEl = document.getElementById('cart-item-unique-count');
    const pageIndicator = document.getElementById('cart-page-indicator');
    const btnPrev = document.getElementById('cart-btn-prev');
    const btnNext = document.getElementById('cart-btn-next');

    // Clamp cartPage
    const totalPages = Math.max(1, Math.ceil(ids.length / CART_PAGE_SIZE));
    if (cartPage >= totalPages) cartPage = totalPages - 1;
    if (cartPage < 0) cartPage = 0;

    if (ids.length > CART_PAGE_SIZE) {
        if (slideBanner) slideBanner.style.display = 'flex';
        if (uniqueCountEl) uniqueCountEl.textContent = ids.length;
        if (pageIndicator) pageIndicator.textContent = `${cartPage + 1}/${totalPages}`;
        if (btnPrev) btnPrev.style.opacity = cartPage === 0 ? '0.4' : '1';
        if (btnNext) btnNext.style.opacity = cartPage >= totalPages - 1 ? '0.4' : '1';
    } else {
        if (slideBanner) slideBanner.style.display = 'none';
        cartPage = 0;
    }

    if (ids.length === 0) {
        if (tableHeader) tableHeader.style.display = 'none';
        btn.disabled = true;
        calcChange();
        document.getElementById('quick-cash-group').innerHTML = '';
        itemsEl.innerHTML = `<div class="empty-cart" id="empty-cart-msg"><i class="fa-solid fa-cart-shopping"></i>Keranjang masih kosong.<br>Tap produk untuk menambahkan.</div>`;
        return;
    }

    if (tableHeader) tableHeader.style.display = 'grid';

    // Paginate
    const start = cartPage * CART_PAGE_SIZE;
    const pageIds = ids.slice(start, start + CART_PAGE_SIZE);

    itemsEl.innerHTML = pageIds.map((k, localIdx) => {
        const globalIdx = start + localIdx;
        const item = cart[k];
        const itemDisc = item.price * (item.discountPersen / 100);
        const finalItemPrice = item.price - itemDisc;
        const itemTotal = finalItemPrice * item.qty;

        let priceHtml = '';
        if (item.discountPersen > 0) {
            priceHtml = `
                <span class="price-original">Rp ${Math.round(item.price).toLocaleString('id-ID')}</span>
                <span class="price-discounted"> Rp ${Math.round(finalItemPrice).toLocaleString('id-ID')}</span>
                × ${item.qty} = <strong>Rp ${Math.round(itemTotal).toLocaleString('id-ID')}</strong>
                <span style="font-size:0.65rem; background:#fef3c7; color:#b45309; padding:1px 4px; border-radius:3px; font-weight:700;">-${item.discountPersen}%</span>`;
        } else {
            priceHtml = `Rp ${Math.round(item.price).toLocaleString('id-ID')} × ${item.qty} = <strong>Rp ${Math.round(itemTotal).toLocaleString('id-ID')}</strong>`;
        }

        return `
        <div class="cart-item">
            <div class="cart-item-num">${globalIdx + 1}</div>
            <div>
                <div class="cart-item-name">${escHtml(item.name)}</div>
                <div class="cart-item-price">${priceHtml}</div>
            </div>
            <div class="cart-qty-ctrl">
                <button class="qty-btn" onclick="changeQty(${k}, -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${k}, 1)">+</button>
            </div>
        </div>`;
    }).join('');

    btn.disabled = false;
    calcChange();
    renderQuickCashButtons();
}

function applyDiscount() { renderCart(); }

function renderQuickCashButtons() {
    const container = document.getElementById('quick-cash-group');
    if (!container) return;

    if (currentTotal <= 0) {
        container.innerHTML = '';
        return;
    }

    const payNominals = new Set();
    payNominals.add(currentTotal);

    const options = [1000, 2000, 5000, 10000, 20000, 50000, 100000];
    options.forEach(opt => {
        if (opt > currentTotal && payNominals.size < 5) {
            const rounded = Math.ceil(currentTotal / opt) * opt;
            payNominals.add(rounded);
        }
    });

    [10000, 20000, 50000, 100000].forEach(val => {
        if (val > currentTotal && payNominals.size < 5) {
            payNominals.add(val);
        }
    });

    const sortedNoms = Array.from(payNominals).sort((a, b) => a - b);

    container.innerHTML = sortedNoms.map(val => {
        const label = val === currentTotal ? 'Uang Pas' : 'Rp ' + val.toLocaleString('id-ID');
        return `<button type="button" class="quick-cash-btn" onclick="setQuickCash(${val})">${label}</button>`;
    }).join('');
}

function setQuickCash(amount) {
    document.getElementById('pay-amount').value = amount.toLocaleString('id-ID');
    calcChange();
}

function toggleMobileCart() {
    const cartPanel = document.getElementById('cart-panel');
    if (cartPanel) {
        cartPanel.classList.toggle('open');
    }
}

let currentCategoryId = 'semua';

function filterCategory(catId, btn) {
    currentCategoryId = catId;
    document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.product-card').forEach(card => {
        const match = catId === 'semua' || card.dataset.cat === catId;
        card.style.display = match ? '' : 'none';
    });
}

document.getElementById('product-search').addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.product-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        const barcode = (card.dataset.barcode || '').toLowerCase();
        const matchesSearch = (!q || name.includes(q) || barcode.includes(q));
        const matchesCat = (currentCategoryId === 'semua' || card.dataset.cat === currentCategoryId);
        card.style.display = (matchesSearch && matchesCat) ? '' : 'none';
    });
});

// ── Auto Refresh Products Grid via AJAX Swapping ──
function autoRefreshProducts() {
    fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('product-grid');
            const oldGrid = document.getElementById('product-grid');
            if (newGrid && oldGrid) {
                // Save search query
                const searchVal = document.getElementById('product-search').value.toLowerCase().trim();

                // Swap product cards inside grid
                oldGrid.innerHTML = newGrid.innerHTML;

                // Re-apply current category and search filters to the newly swapped cards
                document.querySelectorAll('.product-card').forEach(card => {
                    const matchesCat = (currentCategoryId === 'semua' || card.dataset.cat === currentCategoryId);
                    const name = card.dataset.name.toLowerCase();
                    const barcode = (card.dataset.barcode || '').toLowerCase();
                    const matchesSearch = (!searchVal || name.includes(searchVal) || barcode.includes(searchVal));

                    card.style.display = (matchesCat && matchesSearch) ? '' : 'none';
                });

                // Swap user name if changed
                const newDeskName = doc.getElementById('pos-user-name-desktop');
                const oldDeskName = document.getElementById('pos-user-name-desktop');
                if (newDeskName && oldDeskName) oldDeskName.innerHTML = newDeskName.innerHTML;

                const newMobName = doc.getElementById('pos-user-name-mobile');
                const oldMobName = document.getElementById('pos-user-name-mobile');
                if (newMobName && oldMobName) oldMobName.innerHTML = newMobName.innerHTML;
            }
        })
        .catch(err => console.warn('Error syncing products:', err));
}
// Sync every 10 seconds
setInterval(autoRefreshProducts, 10000);

function setPayMethod(method, btn) {
    payMethod = method;
    document.querySelectorAll('.pay-method-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const payRow = document.getElementById('pay-amount-row');
    const quickGroup = document.getElementById('quick-cash-group');
    const changeRow = document.getElementById('change-row');
    const nonCashInfo = document.getElementById('non-cash-info');

    if (method === 'Tunai') {
        if (payRow) payRow.style.display = 'flex';
        if (quickGroup) quickGroup.style.display = 'flex';
        if (changeRow) changeRow.style.display = 'flex';
        if (nonCashInfo) nonCashInfo.style.display = 'none';
        document.getElementById('pay-amount').value = '';
        calcChange();
    } else {
        if (payRow) payRow.style.display = 'none';
        if (quickGroup) quickGroup.style.display = 'none';
        if (changeRow) changeRow.style.display = 'none';
        if (nonCashInfo) {
            nonCashInfo.style.display = 'flex';
            document.getElementById('non-cash-type').textContent = method;
            document.getElementById('non-cash-total').textContent = Math.round(currentTotal).toLocaleString('id-ID');
        }
        document.getElementById('pay-amount').value = Math.round(currentTotal).toLocaleString('id-ID');
        calcChange();
    }
}

function calcChange() {
    const rawVal = document.getElementById('pay-amount').value.replace(/\D/g, '');
    const paid = parseFloat(rawVal) || 0;
    const change = paid - currentTotal;
    document.getElementById('change-val').textContent = 'Rp ' + Math.max(0, change).toLocaleString('id-ID');
    document.getElementById('change-val').style.color = change < 0 ? 'var(--danger)' : 'var(--bancha)';
}

function processCheckout() {
    if (Object.keys(cart).length === 0) {
        showNotif('Keranjang masih kosong!', 'warning');
        return;
    }

    if (payMethod === 'QRIS') {
        document.getElementById('qris-pre-total').textContent = Math.round(currentTotal).toLocaleString('id-ID');
        document.getElementById('qris-pre-overlay').style.display = 'flex';
        return;
    }

    executeCheckoutBackend();
}

function closeQrisPreModal() {
    const el = document.getElementById('qris-pre-overlay');
    if (el) el.style.display = 'none';
}

function confirmQrisCheckout() {
    closeQrisPreModal();
    executeCheckoutBackend();
}

function executeCheckoutBackend() {
    const rawVal = document.getElementById('pay-amount').value.replace(/\D/g, '');
    const paid = parseFloat(rawVal) || 0;
    if (payMethod === 'Tunai' && paid < currentTotal) {
        showNotif('Uang bayar kurang dari total!', 'error');
        return;
    }
    const finalPayAmount = (payMethod !== 'Tunai') ? currentTotal : paid;

    const items = Object.values(cart).map(i => ({ id: i.id, qty: i.qty }));

    const checkoutBtn = document.getElementById('checkout-btn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

    fetch('/cashier/pos/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ items, payment_method: payMethod, pay_amount: finalPayAmount })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) {
            showNotif(data.error, 'error');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Proses Pembayaran';
            return;
        }
        showSuccess(data);
        clearCart();
        document.getElementById('pay-amount').value = '';
        checkoutBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Proses Pembayaran';
        showNotif('✅ Transaksi berhasil!\nTransaksi telah berhasil disimpan.', 'success');
    })
    .catch(() => {
        showNotif('Koneksi gagal. Coba lagi.', 'error');
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Proses Pembayaran';
    });
}

let currentInvoiceUrl = '';

function showSuccess(data) {
    currentInvoiceUrl = data.invoice_url;
    document.getElementById('modal-invoice-num').textContent = 'Invoice: ' + data.invoice_number;
    document.getElementById('modal-pay-method').textContent = data.payment_method;
    document.getElementById('modal-total-price').textContent = 'Rp ' + Math.round(data.total_price).toLocaleString('id-ID');

    const changeContainer = document.getElementById('modal-change-container');
    const lunasBadge = document.getElementById('modal-lunas-badge');

    if (data.payment_method === 'Tunai') {
        if (changeContainer) changeContainer.style.display = 'block';
        if (lunasBadge) lunasBadge.style.display = 'none';
        document.getElementById('modal-change').textContent = 'Rp ' + Math.round(data.change_amount).toLocaleString('id-ID');
    } else {
        if (changeContainer) changeContainer.style.display = 'none';
        if (lunasBadge) lunasBadge.style.display = 'block';
    }

    // Store invoice URL for WA sending
    const origin = window.location.origin;
    currentInvoiceUrl = `${origin}/invoice/${data.invoice_number}`;

    // Clear WA input for new transaction
    const waInput = document.getElementById('wa-phone-input');
    if (waInput) waInput.value = '';

    document.getElementById('success-overlay').style.display = 'flex';
}

function sendWhatsAppReceipt() {
    const rawPhone = (document.getElementById('wa-phone-input').value || '').replace(/\D/g, '');
    if (!rawPhone) {
        showNotif('Masukkan nomor HP WhatsApp pelanggan!', 'warning');
        return;
    }
    if (!currentInvoiceUrl) {
        showNotif('Struk belum tersedia.', 'error');
        return;
    }

    // Normalize phone: 08xx -> 628xx
    let phone = rawPhone;
    if (phone.startsWith('0')) {
        phone = '62' + phone.slice(1);
    }

    // Compose message - get data from modal elements
    const invoiceNum = document.getElementById('modal-invoice-num').textContent.trim();
    const totalPrice = document.getElementById('modal-total-price').textContent.trim();
    const payMethod = document.getElementById('modal-pay-method').textContent.trim();

    const msg = `*Struk Belanja Digital* 🛍️\n` +
        `───────────────\n` +
        `Toko: *{{ $settings['store_name'] ?? 'Toko Kami' }}*\n` +
        `No. Struk: *${invoiceNum}*\n` +
        `Metode: *${payMethod}*\n` +
        `Total: *${totalPrice}*\n` +
        `───────────────\n` +
        `📄 Lihat Struk Lengkap:\n${currentInvoiceUrl}\n\n` +
        `_Terima kasih telah berbelanja! 🙏_`;

    const waUrl = `https://wa.me/${phone}?text=${encodeURIComponent(msg)}`;
    window.open(waUrl, '_blank');
    showNotif('Membuka WhatsApp untuk kirim struk...', 'success');
}

function updatePosClock() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

    const day = days[now.getDay()];
    const date = String(now.getDate()).padStart(2, '0');
    const month = months[now.getMonth()];
    const year = now.getFullYear();
    const hours = String(now.getHours()).padStart(2, '0');
    const mins = String(now.getMinutes()).padStart(2, '0');
    const secs = String(now.getSeconds()).padStart(2, '0');

    const str = `${day}, ${date} ${month} ${year} — ${hours}:${mins}:${secs} WIB`;
    const clockEl = document.getElementById('pos-clock-str');
    if (clockEl) clockEl.textContent = str;
    
    const mobClockEl = document.getElementById('pos-mob-clock-str');
    if (mobClockEl) mobClockEl.textContent = str;
}
setInterval(updatePosClock, 1000);
updatePosClock();

function togglePosMobileMenu() {
    const dropdown = document.getElementById('pos-mob-header-dropdown');
    if (dropdown) dropdown.classList.toggle('open');
}

function toggleCartSlideMode() {
    // Legacy: replaced by pagination (cartPagePrev/cartPageNext)
    cartPageNext();
}

function closeSuccess() {
    document.getElementById('success-overlay').style.display = 'none';
    currentInvoiceUrl = '';
}

function printReceiptDirect() {
    if (!currentInvoiceUrl) {
        showNotif('Nota tidak ditemukan!', 'error');
        return;
    }
    const iframe = document.getElementById('receipt-print-iframe');
    if (iframe) {
        iframe.src = currentInvoiceUrl + '?print=1';
        showNotif('Mengirim ke printer kasir...', 'success');
    }
}

// ============ SHIFT KASIR ============
let shiftIsOpen = false;
let shiftData = null;

async function checkShiftStatus() {
    try {
        const res = await fetch('{{ route('cashier.shift.current') }}', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        shiftIsOpen = data.active;
        shiftData = data;

        const badge = document.getElementById('shift-status-badge');
        const dot = document.getElementById('shift-dot');
        const label = document.getElementById('shift-label');
        if (shiftIsOpen) {
            dot.style.color = '#4ade80';
            label.textContent = 'Shift Aktif';
            if (badge) badge.style.background = 'rgba(22,163,74,0.35)';
        } else {
            dot.style.color = '#fbbf24';
            label.textContent = 'Shift Belum Dibuka';
            if (badge) badge.style.background = 'rgba(251,191,36,0.25)';
        }
    } catch(e) {
        // silently fail
    }
}
checkShiftStatus();

function openShiftModal() {
    const overlay = document.getElementById('shift-modal-overlay');
    const openForm = document.getElementById('shift-open-form');
    const closeForm = document.getElementById('shift-close-form');
    const activeInfo = document.getElementById('shift-active-info');

    if (shiftIsOpen && shiftData) {
        openForm.style.display = 'none';
        closeForm.style.display = 'block';
        activeInfo.style.display = 'block';
        const detail = document.getElementById('shift-active-detail');
        if (detail) {
            const startTime = new Date(shiftData.shift.start_time).toLocaleString('id-ID', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' });
            const cashSales = shiftData.cash_sales ? Math.round(shiftData.cash_sales).toLocaleString('id-ID') : '0';
            const expected = shiftData.expected_ending_cash ? Math.round(shiftData.expected_ending_cash).toLocaleString('id-ID') : '0';
            detail.innerHTML = `<b>Buka:</b> ${startTime} WIB<br><b>Modal Awal:</b> Rp ${Math.round(shiftData.shift.starting_cash).toLocaleString('id-ID')}<br><b>Total Penjualan Tunai:</b> Rp ${cashSales}<br><b>Saldo Seharusnya di Laci:</b> <span style="color:#16a34a; font-weight:800;">Rp ${expected}</span>`;
        }
    } else {
        openForm.style.display = 'block';
        closeForm.style.display = 'none';
        activeInfo.style.display = 'none';
    }

    overlay.style.display = 'flex';
}

function closeShiftModal() {
    document.getElementById('shift-modal-overlay').style.display = 'none';
}

async function doOpenShift() {
    const rawVal = document.getElementById('shift-starting-cash').value.replace(/\D/g, '');
    const startingCash = parseFloat(rawVal) || 0;
    try {
        const res = await fetch('{{ route('cashier.shift.open') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ starting_cash: startingCash })
        });
        const data = await res.json();
        if (data.error) { showNotif(data.error, 'error'); return; }
        showNotif('✅ Shift berhasil dibuka! Selamat berjualan.', 'success');
        closeShiftModal();
        checkShiftStatus();
    } catch(e) {
        showNotif('Gagal membuka shift. Coba lagi.', 'error');
    }
}

async function doCloseShift() {
    const rawVal = document.getElementById('shift-actual-cash').value.replace(/\D/g, '');
    const actualCash = parseFloat(rawVal);
    if (isNaN(actualCash) || actualCash < 0) {
        showNotif('Masukkan saldo uang fisik di laci!', 'warning');
        return;
    }
    const notes = document.getElementById('shift-notes').value;
    if (!confirm(`Tutup shift dengan saldo laci: Rp ${Math.round(actualCash).toLocaleString('id-ID')}? Laporan akan dikirim ke Super Admin.`)) return;
    try {
        const res = await fetch('{{ route('cashier.shift.close') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ actual_ending_cash: actualCash, notes: notes })
        });
        const data = await res.json();
        if (data.error) { showNotif(data.error, 'error'); return; }
        const diff = Math.round(data.difference);
        let msg = `✅ Shift ditutup! Saldo seharusnya: Rp ${Math.round(data.expected).toLocaleString('id-ID')}.`;
        if (diff === 0) {
            msg += ' Saldo PAS! 🎉';
        } else if (diff < 0) {
            msg += ` ⚠️ Kurang Rp ${Math.abs(diff).toLocaleString('id-ID')}! Laporan telah dikirim ke Admin.`;
        } else {
            msg += ` Lebih Rp ${diff.toLocaleString('id-ID')}.`;
        }
        showNotif(msg, diff < 0 ? 'warning' : 'success');
        closeShiftModal();
        checkShiftStatus();
    } catch(e) {
        showNotif('Gagal menutup shift. Coba lagi.', 'error');
    }
}

let chatOpen = false;
let chatPollInterval = null;
let lastChatCount = 0;

let chatUnreadPollInterval = null;

function checkCashierUnreadCount() {
    if (chatOpen) return;
    fetch('{{ route('cashier.chat.unread-count') }}')
        .then(r => r.json())
        .then(data => {
            const count = data.unread_count || 0;
            const badge = document.getElementById('chat-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(() => {});
}

function toggleChat() {
    chatOpen = !chatOpen;
    document.getElementById('chat-popup').classList.toggle('open', chatOpen);
    const badge = document.getElementById('chat-badge');
    if (chatOpen) {
        if (badge) badge.style.display = 'none';
        loadChatMsgs();
        if (!chatPollInterval) chatPollInterval = setInterval(loadChatMsgs, 3000);
    } else {
        clearInterval(chatPollInterval);
        chatPollInterval = null;
        checkCashierUnreadCount();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    checkCashierUnreadCount();
    setInterval(checkCashierUnreadCount, 8000);
});

function loadChatMsgs() {
    fetch('/cashier/chat/messages')
        .then(r => r.json())
        .then(msgs => {
            if (msgs.length !== lastChatCount) {
                lastChatCount = msgs.length;
                renderChatMsgs(msgs);
            }
        });
}

function renderChatMsgs(msgs) {
    const container = document.getElementById('chat-popup-msgs');
    const authId = {{ Auth::id() }};
    if (msgs.length === 0) {
        container.innerHTML = `<div style="text-align:center;color:var(--text-muted);font-size:0.8rem;margin-top:1.5rem;background:#ffeebb;color:#8a6d3b;padding:0.4rem;border-radius:6px;">Belum ada pesan. Mulai chat!</div>`;
        return;
    }
    container.innerHTML = msgs.map(m => {
        const isMine = m.sender_id === authId;
        const time = new Date(m.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
        return `
        <div class="msg-bubble-wrapper ${isMine ? 'outgoing' : 'incoming'}">
            <div class="msg-bubble ${isMine ? 'outgoing' : 'incoming'}">
                <span class="msg-text">${escHtml(m.message)}</span>
                <span class="msg-time">
                    ${time}
                    ${isMine ? '<i class="fa-solid fa-check-double"></i>' : ''}
                </span>
            </div>
        </div>`;
    }).join('');
    container.scrollTop = container.scrollHeight;
}

function sendCashierMsg() {
    const input = document.getElementById('chat-msg-input');
    const msg = input.value.trim();
    if (!msg) return;
    fetch('/cashier/chat/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ message: msg })
    })
    .then(r => r.json())
    .then(() => { input.value = ''; loadChatMsgs(); });
}

function formatNumberInput(input) {
    let cursorPosition = input.selectionStart;
    let originalLength = input.value.length;
    let value = input.value.replace(/\D/g, '');
    if (value === '') { input.value = ''; return; }
    let formatted = parseInt(value, 10).toLocaleString('id-ID');
    input.value = formatted;
    let newLength = formatted.length;
    cursorPosition = cursorPosition + (newLength - originalLength);
    input.setSelectionRange(cursorPosition, cursorPosition);
}

function escHtml(t) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(t));
    return d.innerHTML;
}

let notifTimeout;
function showNotif(msg, type='success') {
    let el = document.getElementById('pos-notif');
    if (!el) {
        el = document.createElement('div');
        el.id = 'pos-notif';
        el.style.cssText = `position:fixed;bottom:5rem;right:1.25rem;padding:0.65rem 1rem;border-radius:var(--radius-md);font-size:0.85rem;font-weight:600;z-index:2100;box-shadow:var(--shadow-lg);transition:opacity 0.3s;`;
        document.body.appendChild(el);
    }
    const colors = { success: ['var(--success-bg)', 'var(--success)'], error: ['#fff0f0', 'var(--danger)'], warning: ['#fff8e0', '#b8860b'] };
    const [bg, col] = colors[type] || colors.success;
    el.style.background = bg;
    el.style.color = col;
    el.style.opacity = '1';
    el.innerHTML = msg.replace(/\n/g, '<br>');
    clearTimeout(notifTimeout);
    notifTimeout = setTimeout(() => { el.style.opacity = '0'; }, 3500);
}

document.addEventListener('keydown', function(event) {
    const isF2 = event.key === 'F2' || event.code === 'F2';
    const isF4 = event.key === 'F4' || event.code === 'F4';
    const isF9 = event.key === 'F9' || event.code === 'F9';

    if (isF2) {
        event.preventDefault();
        const searchInput = document.getElementById('product-search');
        if (searchInput) { searchInput.focus(); searchInput.select(); showNotif('Pintasan: Cari Produk (F2)', 'success'); }
    }
    if (isF4) {
        event.preventDefault();
        if (Object.keys(cart).length > 0 && confirm('Kosongkan semua item di keranjang?')) {
            clearCart(); showNotif('Pintasan: Keranjang dikosongkan (F4)', 'warning');
        }
    }
    if (isF9) {
        event.preventDefault();
        const payInput = document.getElementById('pay-amount');
        if (payInput) { payInput.focus(); payInput.select(); showNotif('Pintasan: Input Uang Bayar (F9)', 'success'); }
    }
    if (event.key === 'Escape') {
        const successOverlay = document.getElementById('success-overlay');
        if (successOverlay && successOverlay.style.display === 'flex') { closeSuccess(); } else { document.activeElement.blur(); }
    }
    if (event.key === 'Enter') {
        const successOverlay = document.getElementById('success-overlay');
        if (successOverlay && successOverlay.style.display === 'flex') { event.preventDefault(); closeSuccess(); return; }
        const checkoutBtn = document.getElementById('checkout-btn');
        if (document.activeElement.id === 'pay-amount') {
            if (checkoutBtn && !checkoutBtn.disabled) { event.preventDefault(); processCheckout(); }
        }
    }
});
</script>
</body>
</html>
