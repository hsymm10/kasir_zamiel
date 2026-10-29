@extends('layouts.admin')

@section('title', 'Chat Kasir')

@section('content')
<style>
    /* Premium Rentfy-inspired Chat Design (Gambar 3 Reference) */
    :root {
        --chat-primary: {{ $settings['color_primary'] ?? '#05472A' }};
        --chat-secondary: {{ $settings['color_secondary'] ?? '#6D6844' }};
        --chat-accent: #22c55e;
        --chat-bg-light: #ffffff;
        --chat-bg-bubble-in: #f3f4f6;
        --chat-text-dark: #111b21;
        --chat-text-muted: #667781;
        --chat-border: #e9edef;
    }

    .rentfy-container {
        display: grid;
        grid-template-columns: 340px 1fr;
        height: calc(100vh - 165px);
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        border: 1px solid var(--chat-border);
        overflow: hidden;
    }

    /* Left Sidebar: Threads and Active list */
    .rentfy-sidebar {
        background: #ffffff;
        border-right: 1px solid var(--wa-chat-border);
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .rentfy-sidebar-header {
        padding: 1.25rem 1.25rem 0.5rem;
    }

    .rentfy-sidebar-header h2 {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--chat-primary);
        margin: 0 0 0.75rem;
    }

    /* Rentfy Search Box */
    .rentfy-search-box {
        display: flex;
        align-items: center;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 0.55rem 0.875rem;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .rentfy-search-box input {
        border: none;
        background: transparent;
        font-size: 0.85rem;
        outline: none;
        width: 100%;
        color: var(--chat-text-dark);
        font-family: inherit;
    }
    .rentfy-search-box i {
        color: var(--chat-text-muted);
        font-size: 0.85rem;
    }

    /* Active Horizontal scrolling (Gambar 3 style) */
    .rentfy-active-section {
        padding: 0.5rem 1.25rem;
        border-bottom: 1px solid var(--chat-border);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .rentfy-section-title {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--chat-text-muted);
        margin-bottom: 0.25rem;
    }
    .rentfy-active-row {
        display: flex;
        gap: 0.875rem;
        overflow-x: auto;
        padding: 0.25rem 0;
    }
    .rentfy-active-row::-webkit-scrollbar { height: 0px; }
    .rentfy-active-user {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
        cursor: pointer;
        background: none;
        border: none;
    }
    .rentfy-active-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: white;
        border: 2px solid var(--chat-accent);
        padding: 2px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .rentfy-active-avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--chat-secondary), var(--chat-primary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
    }
    .rentfy-active-name {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--chat-text-dark);
        max-width: 52px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Messages list styling */
    .rentfy-msg-section {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem 1.25rem;
    }
    .rentfy-thread-item {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 0.5rem;
        background: none;
        border: none;
        border-bottom: 1px solid var(--chat-border);
        cursor: pointer;
        transition: background 0.2s;
        text-align: left;
        border-radius: 12px;
        margin-bottom: 0.25rem;
    }
    .rentfy-thread-item:hover {
        background: #f8fafc;
    }
    .rentfy-thread-item.active {
        background: #f1f5f9;
    }
    .rentfy-thread-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--chat-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.05rem;
        position: relative;
        flex-shrink: 0;
    }
    .rentfy-online-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid white;
    }
    .rentfy-thread-info {
        flex: 1;
        min-width: 0;
    }
    .rentfy-thread-name-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 0.2rem;
    }
    .rentfy-thread-name {
        font-weight: 700;
        font-size: 0.88rem;
        color: var(--chat-text-dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .rentfy-thread-time {
        font-size: 0.7rem;
        color: var(--chat-text-muted);
    }
    .rentfy-thread-bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .rentfy-thread-snippet {
        font-size: 0.78rem;
        color: var(--chat-text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        padding-right: 0.5rem;
    }
    .rentfy-unread-badge {
        background: var(--chat-accent);
        color: white;
        font-size: 0.65rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Right Panel: Chat Room */
    .rentfy-chat-room {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        background: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .rentfy-chat-header {
        padding: 0.875rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--chat-border);
        background: #ffffff;
        z-index: 2;
    }
    .rentfy-chat-header-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .rentfy-back-btn {
        display: none;
        background: none;
        border: none;
        font-size: 1.1rem;
        cursor: pointer;
        color: var(--chat-text-dark);
        padding: 0.25rem 0.5rem 0.25rem 0;
    }


    /* WhatsApp Style Message Bubbles Area (Matched with Cashier View) */
    .rentfy-messages {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1rem 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        background: #efeae2;
        background-image: url("https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png");
        background-blend-mode: overlay;
        background-color: rgba(239, 234, 226, 0.65);
        z-index: 1;
    }

    .msg-bubble-wrapper {
        display: flex;
        width: 100%;
        margin-bottom: 2px;
    }
    .msg-bubble-wrapper.outgoing { justify-content: flex-end; }
    .msg-bubble-wrapper.incoming { justify-content: flex-start; }

    .msg-bubble {
        max-width: 65%;
        padding: 0.5rem 0.75rem 0.4rem;
        font-size: 0.85rem;
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
        font-size: 0.64rem;
        color: #667781;
        margin-top: -4px;
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
    }
    .msg-time i {
        font-size: 0.68rem;
        color: #53bdeb;
    }

    /* Rentfy Input Footer */
    .rentfy-input-footer {
        padding: 0.875rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-top: 1px solid var(--chat-border);
        background: #ffffff;
        z-index: 2;
    }

    .rentfy-input-wrapper {
        flex: 1;
        position: relative;
        display: flex;
        align-items: center;
    }
    .rentfy-input-box {
        width: 100%;
        background: #f1f5f9;
        border-radius: 20px;
        border: none;
        padding: 0.6rem 2.25rem 0.6rem 1rem;
        font-size: 0.88rem;
        outline: none;
        font-family: inherit;
        color: var(--chat-text-dark);
    }

    .rentfy-send-btn {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--chat-primary);
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.95rem;
        transition: transform 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .rentfy-send-btn:hover {
        transform: scale(1.05);
    }
    .rentfy-send-btn:disabled {
        background: #cccccc;
        cursor: not-allowed;
        box-shadow: none;
    }

    .rentfy-welcome {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
        padding: 2rem;
        color: var(--chat-text-muted);
        z-index: 1;
        background: #f8fafc;
    }
    .rentfy-welcome i {
        font-size: 3.5rem;
        color: var(--chat-primary);
        margin-bottom: 1.25rem;
        opacity: 0.8;
    }

    /* ═══════════════════════════ RESPONSIVE ═══════════════════════════ */
    @media (max-width: 768px) {
        .rentfy-container {
            grid-template-columns: 1fr;
            height: calc(100vh - 180px);
        }

        .rentfy-sidebar {
            display: flex;
        }

        .rentfy-chat-room {
            display: none;
            position: fixed;
            top: 56px; /* below mobile top header */
            left: 0;
            right: 0;
            bottom: 74px; /* above bottom navigation */
            width: 100%;
            height: calc(100vh - 56px - 74px);
            z-index: 200;
        }

        /* When cashier selected, show chat room pane fully on mobile */
        .rentfy-container.chat-active .rentfy-sidebar {
            display: none;
        }

        .rentfy-container.chat-active .rentfy-chat-room {
            display: flex;
        }

        .rentfy-back-btn {
            display: block;
        }
    }
</style>

<div style="margin-bottom: 1.25rem;">
    <h1 style="margin-bottom: 0.25rem;"><i class="fa-solid fa-comments" style="color: var(--bancha);"></i> Chat Admin Center</h1>
    <p style="color: var(--text-muted); font-size: 0.92rem;">Hubungi kasir aktif Anda langsung dengan antarmuka yang bersih dan interaktif.</p>
</div>

<div class="rentfy-container" id="rentfy-container">
    <!-- Left Sidebar: Threads -->
    <div class="rentfy-sidebar">
        <div class="rentfy-sidebar-header">
            <h2>Pesan</h2>
            <div class="rentfy-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="wa-search" placeholder="Search..." oninput="filterCashiers()">
            </div>
        </div>

        <!-- Active Horizontal Avatars (Gambar 3 style) -->
        <div class="rentfy-active-section">
            <div class="rentfy-section-title">Aktif</div>
            <div class="rentfy-active-row">
                @foreach($cashiers as $cashier)
                <button class="rentfy-active-user" onclick="selectCashier({{ $cashier->id }}, '{{ $cashier->name }}')">
                    <div class="rentfy-active-avatar" style="border-color: {{ $cashier->isOnline() ? 'var(--chat-accent)' : 'transparent' }}">
                        <div class="rentfy-active-avatar-inner">
                            {{ strtoupper(substr($cashier->name, 0, 1)) }}
                        </div>
                    </div>
                    <span class="rentfy-active-name">{{ $cashier->name }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <!-- Chat thread list -->
        <div class="rentfy-msg-section">
            <div class="rentfy-section-title" style="margin-bottom:0.5rem;">Percakapan</div>
            <div class="wa-list" id="cashier-list">
                @forelse($cashiers as $cashier)
                <button onclick="selectCashier({{ $cashier->id }}, '{{ $cashier->name }}')"
                        id="cashier-btn-{{ $cashier->id }}"
                        class="rentfy-thread-item">
                    <div class="rentfy-thread-avatar">
                        {{ strtoupper(substr($cashier->name, 0, 1)) }}
                        <div class="rentfy-online-indicator" style="background: {{ $cashier->isOnline() ? 'var(--chat-accent)' : 'var(--chat-text-muted)' }};"></div>
                    </div>
                    <div class="rentfy-thread-info">
                        <div class="rentfy-thread-name-row">
                            <span class="rentfy-thread-name">{{ $cashier->name }}</span>
                            <span class="rentfy-thread-time" id="last-time-{{ $cashier->id }}">-</span>
                        </div>
                        <div class="rentfy-thread-bottom-row">
                            <span class="rentfy-thread-snippet" id="last-msg-{{ $cashier->id }}">{{ $cashier->isOnline() ? 'Online' : 'Offline' }}</span>
                            <span class="rentfy-unread-badge" id="unread-{{ $cashier->id }}" style="display: {{ ($cashier->unread_count ?? 0) > 0 ? 'inline-block' : 'none' }};">{{ $cashier->unread_count ?? 0 }}</span>
                        </div>
                    </div>
                </button>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--chat-text-muted); font-size: 0.85rem;">
                    Belum ada kasir terdaftar.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right: Chat Room Panel -->
    <div class="rentfy-chat-room">
        <!-- Welcome View -->
        <div id="welcome-view" class="rentfy-welcome">
            <i class="fa-solid fa-message-dots"></i>
            <h2 style="font-weight: 800; color: var(--chat-text-dark); margin-bottom: 0.5rem; font-size: 1.25rem;">Kotak Masuk Chat</h2>
            <p style="font-size: 0.85rem; max-width: 280px; line-height: 1.5; color: var(--chat-text-muted);">Pilih kasir aktif di panel kiri untuk melihat percakapan dan berkirim pesan.</p>
        </div>

        <!-- Chat View Area -->
        <div id="chat-view" style="display: none; flex-direction: column; height: 100%; min-height: 0; overflow: hidden;">
            <div class="rentfy-chat-header">
                <div class="rentfy-chat-header-info">
                    <button class="rentfy-back-btn" onclick="backToSidebar()"><i class="fa-solid fa-chevron-left"></i></button>
                    <div class="rentfy-thread-avatar" id="room-avatar">K</div>
                    <div>
                        <div id="room-name" style="font-weight: 800; font-size: 0.92rem; color: var(--chat-text-dark);">Nama Kasir</div>
                        <div id="room-status" style="font-size: 0.72rem; color: var(--chat-accent); font-weight:600;">Online</div>
                    </div>
                </div>

            </div>

            <!-- Messages List -->
            <div class="rentfy-messages" id="wa-messages-container"></div>

            <!-- Input Footer -->
            <div class="rentfy-input-footer">
                <div class="rentfy-input-wrapper">
                    <input type="text" id="wa-input" class="rentfy-input-box" placeholder="Tulis pesan..." onkeypress="if(event.key==='Enter') sendWaMessage()">
                </div>
                <button id="wa-send-btn" class="rentfy-send-btn" onclick="sendWaMessage()">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let selectedCashierId = null;
let pollInterval = null;
let lastMessageCount = 0;

function selectCashier(id, name) {
    selectedCashierId = id;
    lastMessageCount = 0;

    // Responsive toggle container class
    document.getElementById('rentfy-container').classList.add('chat-active');

    // Toggle views
    document.getElementById('welcome-view').style.display = 'none';
    document.getElementById('chat-view').style.display = 'flex';

    // Update Header
    const avatar = document.getElementById('room-avatar');
    avatar.textContent = name.charAt(0).toUpperCase();
    document.getElementById('room-name').textContent = name;

    // Set initial status then refresh via polling
    const roomStatus = document.getElementById('room-status');
    const indicator = document.querySelector(`#cashier-btn-${id} .rentfy-online-indicator`);
    if (indicator) {
        const isGreen = indicator.style.background.includes('22c55e') || 
                         indicator.style.background === 'var(--chat-accent)' ||
                         indicator.style.background === 'rgb(34, 197, 94)';
        roomStatus.textContent = isGreen ? '● Online' : '● Offline';
        roomStatus.style.color = isGreen ? 'var(--chat-accent)' : 'var(--chat-text-muted)';
    } else {
        roomStatus.textContent = '● Memeriksa...';
        roomStatus.style.color = 'var(--chat-text-muted)';
    }

    // Highlight list
    document.querySelectorAll('.rentfy-thread-item').forEach(btn => btn.classList.remove('active'));
    const btn = document.getElementById(`cashier-btn-${id}`);
    if (btn) btn.classList.add('active');

    // Start Polling
    if (pollInterval) clearInterval(pollInterval);
    loadMessages();
    pollInterval = setInterval(loadMessages, 3000);
}

function backToSidebar() {
    selectedCashierId = null;
    document.getElementById('rentfy-container').classList.remove('chat-active');
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
}

function loadMessages() {
    if (!selectedCashierId) return;
    fetch(`/admin/chat/messages/${selectedCashierId}`)
        .then(r => r.json())
        .then(messages => {
            if (messages.length !== lastMessageCount) {
                lastMessageCount = messages.length;
                renderMessages(messages);
                updateSidebarPreview(messages);
            }
        });
}

function renderMessages(messages) {
    const container = document.getElementById('wa-messages-container');
    const authId = {{ Auth::id() }};

    if (messages.length === 0) {
        container.innerHTML = `
            <div style="margin: 2rem auto; text-align: center; background: #f1f5f9; color: var(--chat-text-muted); padding: 0.5rem 1rem; border-radius: 99px; font-size: 0.72rem; z-index: 1; max-width: 260px; font-weight:700;">
                Belum ada pesan. Mulai percakapan!
            </div>`;
        return;
    }

    container.innerHTML = messages.map(msg => {
        const isMine = msg.sender_id === authId;
        const time = new Date(msg.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
        
        return `
            <div class="msg-bubble-wrapper ${isMine ? 'outgoing' : 'incoming'}">
                <div class="msg-bubble ${isMine ? 'outgoing' : 'incoming'}">
                    <span class="msg-text">${escapeHtml(msg.message)}</span>
                    <span class="msg-time">
                        ${time}
                        ${isMine ? '<i class="fa-solid fa-check-double"></i>' : ''}
                    </span>
                </div>
            </div>`;
    }).join('');

    container.scrollTop = container.scrollHeight;
}

function updateSidebarPreview(messages) {
    if (messages.length === 0) return;
    const lastMsg = messages[messages.length - 1];
    const time = new Date(lastMsg.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
    
    const lastMsgEl = document.getElementById(`last-msg-${selectedCashierId}`);
    const timeEl = document.getElementById(`last-time-${selectedCashierId}`);
    
    if (lastMsgEl) lastMsgEl.textContent = lastMsg.message;
    if (timeEl) timeEl.textContent = time;
}

function sendWaMessage() {
    const input = document.getElementById('wa-input');
    const msg = input.value.trim();
    if (!msg || !selectedCashierId) return;

    input.disabled = true;
    document.getElementById('wa-send-btn').disabled = true;

    fetch('/admin/chat/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({receiver_id: selectedCashierId, message: msg})
    })
    .then(r => r.json())
    .then(() => {
        input.value = '';
        input.disabled = false;
        document.getElementById('wa-send-btn').disabled = false;
        input.focus();
        loadMessages();
    })
    .catch(() => {
        input.disabled = false;
        document.getElementById('wa-send-btn').disabled = false;
    });
}

function filterCashiers() {
    const q = document.getElementById('wa-search').value.toLowerCase().trim();
    document.querySelectorAll('.rentfy-thread-item').forEach(item => {
        const name = item.querySelector('.rentfy-thread-name').textContent.toLowerCase();
        if (name.includes(q)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

/* ═══════════════ REAL-TIME ONLINE STATUS POLLING ═══════════════ */
let onlineStatusInterval = null;

function startOnlineStatusPolling() {
    updateOnlineStatuses(); // immediate first call
    if (onlineStatusInterval) clearInterval(onlineStatusInterval);
    onlineStatusInterval = setInterval(updateOnlineStatuses, 10000); // every 10s
}

function updateOnlineStatuses() {
    fetch('/admin/chat/online-status')
        .then(r => r.json())
        .then(statuses => {
            statuses.forEach(s => {
                const isOnline = s.is_online;
                const color = isOnline ? 'var(--chat-accent)' : 'var(--chat-text-muted)';
                const accentBorder = isOnline ? 'var(--chat-accent)' : 'transparent';
                const statusText = isOnline ? 'Online' : 'Offline';

                // Update thread list online indicator dot
                const indicator = document.querySelector(`#cashier-btn-${s.id} .rentfy-online-indicator`);
                if (indicator) indicator.style.background = color;

                // Update unread count badge
                const unreadBadge = document.getElementById(`unread-${s.id}`);
                if (unreadBadge) {
                    const count = s.unread_count || 0;
                    if (selectedCashierId && selectedCashierId == s.id) {
                        unreadBadge.style.display = 'none';
                    } else if (count > 0) {
                        unreadBadge.textContent = count;
                        unreadBadge.style.display = 'inline-block';
                    } else {
                        unreadBadge.style.display = 'none';
                    }
                }

                // Update snippet text if no message yet
                const snippet = document.getElementById(`last-msg-${s.id}`);
                if (snippet && (snippet.textContent === 'Online' || snippet.textContent === 'Offline')) {
                    snippet.textContent = statusText;
                }

                // Update active avatar border
                const activeUsers = document.querySelectorAll('.rentfy-active-user');
                activeUsers.forEach(btn => {
                    if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(`selectCashier(${s.id},`)) {
                        const avatar = btn.querySelector('.rentfy-active-avatar');
                        if (avatar) avatar.style.borderColor = accentBorder;
                    }
                });

                // Update room header status if this cashier is currently selected
                if (selectedCashierId && selectedCashierId == s.id) {
                    const roomStatus = document.getElementById('room-status');
                    if (roomStatus) {
                        roomStatus.textContent = isOnline ? '● Online' : '● Offline';
                        roomStatus.style.color = isOnline ? 'var(--chat-accent)' : 'var(--chat-text-muted)';
                    }
                }
            });
        })
        .catch(() => {}); // silently ignore polling errors
}

// Start polling immediately when page loads
document.addEventListener('DOMContentLoaded', startOnlineStatusPolling);

</script>
@endsection
