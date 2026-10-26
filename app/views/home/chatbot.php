<?php
// Ambil data sesi pengguna yang sedang login di SantriFit
$chatCurrentUser = [
    'id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? 'Pengguna',
    'roles' => $_SESSION['user_roles'] ?? ['Pengguna']
];
?>
<script>
// Data pengguna aktif yang diteruskan ke chatbot
const santriFitCurrentUser = <?= json_encode($chatCurrentUser); ?>;
</script>

<!-- ========================================== -->
<!-- SANFTRIFIT AI CHATBOT (NODE.JS + NOUS RESEARCH + NOTIFIKASI) -->
<!-- ========================================== -->

<!-- Floating Chat Button with Chat Icon & Notification Counter -->
<div id="santrifit-chat-toggle-btn" class="santrifit-chat-btn shadow-lg" title="Buka Asisten AI SantriFit" onclick="toggleSantriFitChat()">
    <div class="chat-btn-inner position-relative">
        <i class="bi bi-chat-dots-fill fs-3 text-white"></i>
        <span id="chat-floating-badge" class="chat-badge pulse-badge">AI</span>
        <span id="chat-floating-notif-count" class="notif-floating-pill badge bg-danger rounded-pill d-none">0</span>
    </div>
</div>

<!-- Chatbot Window -->
<div id="santrifit-chat-window" class="santrifit-chat-box shadow-lg d-none">
    <!-- Header -->
    <div class="chat-box-header d-flex align-items-center justify-content-between p-3 text-white">
        <div class="d-flex align-items-center gap-2">
            <div class="chat-header-avatar position-relative">
                <i class="bi bi-robot fs-4"></i>
                <span class="online-indicator"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-1">
                    <span>SantriFit AI — GPT</span>
                </h6>
                <small class="text-white-50" style="font-size: 0.72rem;">
                    <i class="bi bi-cpu"></i> Engine: Nous Research / Node.js
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Bell Notification Button with Counter -->
            <button type="button" id="chatbot-notif-btn" class="btn btn-sm btn-outline-light border-0 position-relative py-0 px-2" title="Notifikasi Chatbot" onclick="toggleChatNotifPanel(event)">
                <i class="bi bi-bell-fill fs-5"></i>
                <span id="chat-notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow-sm d-none" style="font-size: 0.65rem;">
                    0
                </span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-light border-0 py-0 px-2" title="Bersihkan Percakapan" onclick="clearSantriFitChat()">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-light border-0 py-0 px-2" title="Tutup Chat" onclick="toggleSantriFitChat()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Status Bar -->
    <div id="chat-server-status" class="chat-status-bar px-3 py-1 bg-light border-bottom d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72rem; cursor: pointer;" onclick="checkChatbotServerStatus()" title="Klik untuk cek ulang status koneksi">
        <span><i class="bi bi-hdd-network text-success"></i> Server: <span id="server-status-text">Memeriksa Node.js...</span></span>
        <span class="badge bg-teal-subtle text-teal">Port 3001</span>
    </div>

    <!-- NOTIFICATION PANEL (Dropdown/Overlay) -->
    <div id="santrifit-notif-panel" class="chat-notif-panel d-none flex-column">
        <!-- Panel Header -->
        <div class="notif-panel-header p-2 px-3 bg-white border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark" style="font-size: 0.85rem;"><i class="bi bi-bell text-teal"></i> Notifikasi</span>
                <span id="notif-unread-pill" class="badge bg-teal-subtle text-teal rounded-pill" style="font-size: 0.7rem;">0 Baru</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.7rem;" onclick="loadNotifications()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button type="button" class="btn-close" style="font-size: 0.7rem;" aria-label="Close" onclick="toggleChatNotifPanel(event, false)"></button>
            </div>
        </div>

        <!-- Filter Chips Bar -->
        <div class="notif-filter-bar px-2 py-2 bg-light border-bottom d-flex gap-1 overflow-x-auto text-nowrap" style="scrollbar-width: thin;">
            <button type="button" class="btn btn-xs rounded-pill notif-chip active" data-filter="all" onclick="filterNotifs('all', this)">Semua</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="unread" onclick="filterNotifs('unread', this)">Belum Dibaca</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="pemeriksaan" onclick="filterNotifs('pemeriksaan', this)">🩺 Pemeriksaan</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="perhatian" onclick="filterNotifs('perhatian', this)">⚠️ Perhatian</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="rekomendasi" onclick="filterNotifs('rekomendasi', this)">💊 Rekomendasi</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="obat" onclick="filterNotifs('obat', this)">💊 Obat</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="ai" onclick="filterNotifs('ai', this)">🤖 AI</button>
            <button type="button" class="btn btn-xs rounded-pill notif-chip" data-filter="sistem" onclick="filterNotifs('sistem', this)">ℹ️ Sistem</button>
        </div>

        <!-- Notification Items List -->
        <div id="santrifit-notif-list" class="notif-panel-body p-2 flex-grow-1 overflow-y-auto">
            <div class="text-center py-4 text-muted">
                <div class="spinner-border spinner-border-sm text-teal mb-2" role="status"></div>
                <div style="font-size: 0.78rem;">Memuat notifikasi...</div>
            </div>
        </div>

        <!-- Panel Footer -->
        <div class="notif-panel-footer p-2 bg-white border-top d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-sm btn-link text-teal text-decoration-none p-0 fw-semibold" style="font-size: 0.75rem;" onclick="markAllNotifsRead()">
                <i class="bi bi-check2-all"></i> Tandai semua sudah dibaca
            </button>
            <button type="button" class="btn btn-xs btn-outline-teal rounded-pill px-2 py-1" style="font-size: 0.72rem;" onclick="toggleChatNotifPanel(event, false)">
                Kembali ke Chat
            </button>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="santrifit-chat-messages" class="chat-box-messages p-3">
        <!-- Welcome Message -->
        <div class="chat-msg chat-msg-bot d-flex gap-2 mb-3">
            <div class="chat-avatar-mini bg-teal text-white rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div class="chat-bubble-bot p-3 rounded-3 shadow-sm">
                <p class="mb-1 fw-semibold text-teal" style="font-size: 0.82rem;">Assalamu'alaikum Warahmatullahi Wabarakatuh!</p>
                <p class="mb-2" style="font-size: 0.85rem;">Saya <strong>Asisten AI SantriFit</strong>. Saya siap membantu Anda mengakses data santri, pemeriksaan medis, obat, dan riwayat kesehatan pesantren.</p>
                <div class="chat-quick-suggestions mt-2">
                    <span class="d-block text-muted mb-1" style="font-size: 0.75rem;"><i class="bi bi-lightbulb"></i> Rekomendasi Pertanyaan Cepat:</span>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-outline-teal btn-xs py-1 px-2 rounded-pill quick-chip" onclick="sendQuickPrompt('Ada berapa santri?')">
                            👥 Ada Berapa Santri?
                        </button>
                        <button type="button" class="btn btn-outline-teal btn-xs py-1 px-2 rounded-pill quick-chip" onclick="sendQuickPrompt('Daftar obat yang tersedia?')">
                            💊 Daftar Obat
                        </button>
                        <button type="button" class="btn btn-outline-teal btn-xs py-1 px-2 rounded-pill quick-chip" onclick="sendQuickPrompt('Apakah ada notifikasi baru?')">
                            🔔 Cek Notifikasi
                        </button>
                        <button type="button" class="btn btn-outline-teal btn-xs py-1 px-2 rounded-pill quick-chip" onclick="sendQuickPrompt('Siapa saya?')">
                            👤 Siapa Saya?
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Typing Indicator (Hidden by default) -->
    <div id="santrifit-typing-indicator" class="chat-typing px-3 py-2 d-none d-flex align-items-center gap-2 text-muted" style="font-size: 0.78rem;">
        <span class="spinner-grow spinner-grow-sm text-teal" role="status"></span>
        <span>SantriFit AI sedang membaca data & menganalisis...</span>
    </div>

    <!-- Input Footer -->
    <div class="chat-box-footer p-2 bg-white border-top">
        <form id="santrifit-chat-form" onsubmit="handleSendChat(event)" class="d-flex align-items-center gap-2">
            <input type="text" id="santrifit-chat-input" class="form-control form-control-sm rounded-pill px-3" placeholder="Ketik keluhan atau pertanyaan Anda..." autocomplete="off">
            <button type="submit" id="santrifit-chat-send-btn" class="btn btn-teal btn-sm rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 38px; height: 38px;" title="Kirim Pesan">
                <i class="bi bi-send-fill text-white"></i>
            </button>
        </form>
        <div class="text-center mt-1">
            <small class="text-muted" style="font-size: 0.68rem;">
                <i class="bi bi-shield-check text-success"></i> Konsultasi edukasi kesehatan. Bukan pengganti pemeriksaan dokter langsung.
            </small>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- STYLES -->
<!-- ========================================== -->
<style>
/* Floating Button */
.santrifit-chat-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0c8c8c 0%, #065e5e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1060;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 10px 25px rgba(12, 140, 140, 0.45);
}

.santrifit-chat-btn:hover {
    transform: scale(1.08) translateY(-3px);
    box-shadow: 0 14px 30px rgba(12, 140, 140, 0.55);
}

.chat-btn-inner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-badge {
    position: absolute;
    top: -10px;
    right: -12px;
    background-color: #ef4444;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid #fff;
}

.notif-floating-pill {
    position: absolute;
    bottom: -12px;
    left: -12px;
    font-size: 0.65rem;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}

.pulse-badge {
    animation: pulseGlow 2s infinite;
}

@keyframes pulseGlow {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1.08); box-shadow: 0 0 0 7px rgba(239, 68, 68, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

/* Chat Window */
.santrifit-chat-box {
    position: fixed;
    bottom: 98px;
    right: 25px;
    width: 410px;
    max-width: calc(100vw - 35px);
    height: 560px;
    max-height: calc(100vh - 120px);
    background-color: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    z-index: 1060;
    border: 1px solid rgba(0, 0, 0, 0.08);
    animation: slideUpFade 0.3s ease-out;
}

@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.chat-box-header {
    background: linear-gradient(135deg, #0c8c8c 0%, #075e5e 100%);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.chat-header-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.online-indicator {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 10px;
    height: 10px;
    background-color: #10b981;
    border: 2px solid #075e5e;
    border-radius: 50%;
}

.chat-box-messages {
    flex: 1;
    overflow-y: auto;
    background-color: #f8fafc;
    display: flex;
    flex-direction: column;
}

.chat-avatar-mini {
    width: 32px;
    height: 32px;
    font-size: 0.85rem;
}

.chat-bubble-bot {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    max-width: 86%;
    font-size: 0.86rem;
    line-height: 1.45;
}

.chat-bubble-user {
    background: linear-gradient(135deg, #0c8c8c 0%, #097070 100%);
    color: #ffffff;
    font-size: 0.86rem;
    line-height: 1.4;
    word-break: break-word;
}

.quick-chip {
    font-size: 0.73rem !important;
    background-color: #ffffff;
    border-color: #0c8c8c;
    color: #0c8c8c;
    transition: all 0.2s;
}

.quick-chip:hover {
    background-color: #0c8c8c;
    color: #ffffff;
}

.chat-typing {
    background-color: #f1f5f9;
    border-top: 1px solid #e2e8f0;
}

/* ========================================== */
/* NOTIFICATION PANEL STYLES */
/* ========================================== */
.chat-notif-panel {
    position: absolute;
    top: 56px;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #f8fafc;
    z-index: 1065;
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.notif-chip {
    font-size: 0.7rem !important;
    padding: 2px 10px !important;
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    color: #475569;
    transition: all 0.15s ease-in-out;
}

.notif-chip:hover {
    background-color: #e2e8f0;
    color: #0f172a;
}

.notif-chip.active {
    background-color: #0c8c8c !important;
    border-color: #0c8c8c !important;
    color: #ffffff !important;
    font-weight: 600;
}

.notif-card {
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.notif-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}

.notif-card.unread {
    background-color: #f0fdfa;
    border-left: 4px solid #0c8c8c;
    border-color: #ccfbf1 #e2e8f0 #e2e8f0 #0c8c8c;
}

.notif-unread-dot {
    width: 8px;
    height: 8px;
    background-color: #0c8c8c;
    border-radius: 50%;
    display: inline-block;
}

.notif-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
</style>

<!-- ========================================== -->
<!-- JAVASCRIPT LOGIC -->
<!-- ========================================== -->
<script>
const CHATBOT_PORT = 3001;
const CHATBOT_HOST = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') ? window.location.hostname : '127.0.0.1';
const CHATBOT_API_URL = 'http://' + CHATBOT_HOST + ':' + CHATBOT_PORT + '/api/chat';
const CHATBOT_HEALTH_URL = 'http://' + CHATBOT_HOST + ':' + CHATBOT_PORT + '/health';
const NOTIF_API_URL = '<?= BASEURL; ?>/api_notifications.php';

let chatHistory = [];
let isChatOpen = false;
let isNotifPanelOpen = false;
let currentNotifFilter = 'all';

function toggleSantriFitChat() {
    const box = document.getElementById('santrifit-chat-window');
    isChatOpen = !isChatOpen;
    if (isChatOpen) {
        box.classList.remove('d-none');
        checkChatbotServerStatus();
        fetchNotifCount();
        setTimeout(() => {
            const input = document.getElementById('santrifit-chat-input');
            if (input) input.focus();
        }, 200);
    } else {
        box.classList.add('d-none');
        // Tutup juga notif panel saat window ditutup
        toggleChatNotifPanel(null, false);
    }
}

async function checkChatbotServerStatus() {
    const statusText = document.getElementById('server-status-text');
    if (!statusText) return;
    try {
        const res = await fetch(CHATBOT_HEALTH_URL, { method: 'GET', mode: 'cors' });
        if (res.ok) {
            const data = await res.json();
            statusText.innerHTML = '<span class="text-success fw-bold">Online</span>';
        } else {
            statusText.innerHTML = '<span class="text-warning">Server HTTP ' + res.status + '</span>';
        }
    } catch (e) {
        statusText.innerHTML = '<span class="text-danger fw-bold">Node.js Offline (Jalankan server)</span>';
    }
}

function clearSantriFitChat() {
    chatHistory = [];
    const container = document.getElementById('santrifit-chat-messages');
    container.innerHTML = `
        <div class="chat-msg chat-msg-bot d-flex gap-2 mb-3">
            <div class="chat-avatar-mini bg-teal text-white rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
                <i class="bi bi-heart-pulse-fill"></i>
            </div>
            <div class="chat-bubble-bot p-3 rounded-3 shadow-sm">
                <p class="mb-1 fw-semibold text-teal" style="font-size: 0.82rem;">Percakapan Telah Direset</p>
                <p class="mb-0" style="font-size: 0.85rem;">Silakan tanyakan keluhan atau pertanyaan seputar data SantriFit.</p>
            </div>
        </div>
    `;
}

function formatMarkdown(text) {
    if (!text) return '';
    let out = text;
    out = out.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    out = out.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    out = out.replace(/\*(.*?)\*/g, '<em>$1</em>');
    out = out.replace(/\n/g, '<br>');
    return out;
}

function appendUserMessage(msg) {
    const container = document.getElementById('santrifit-chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-msg chat-msg-user d-flex justify-content-end mb-3';
    msgDiv.innerHTML = `
        <div class="chat-bubble-user p-3 rounded-3 shadow-sm">
            ${formatMarkdown(msg)}
        </div>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function appendBotMessage(reply, metaInfo = '') {
    const container = document.getElementById('santrifit-chat-messages');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-msg chat-msg-bot d-flex gap-2 mb-3';
    msgDiv.innerHTML = `
        <div class="chat-avatar-mini bg-teal text-white rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center">
            <i class="bi bi-robot"></i>
        </div>
        <div class="chat-bubble-bot p-3 rounded-3 shadow-sm">
            <div>${formatMarkdown(reply)}</div>
            ${metaInfo ? `<div class="mt-2 text-muted text-end" style="font-size: 0.68rem;"><i class="bi bi-check-all text-teal"></i> ${metaInfo}</div>` : ''}
        </div>
    `;
    container.appendChild(msgDiv);
    container.scrollTop = container.scrollHeight;
}

function sendQuickPrompt(promptText) {
    const input = document.getElementById('santrifit-chat-input');
    input.value = promptText;
    document.getElementById('santrifit-chat-form').dispatchEvent(new Event('submit'));
}

async function handleSendChat(event) {
    event.preventDefault();
    const input = document.getElementById('santrifit-chat-input');
    const message = input.value.trim();
    if (!message) return;

    input.value = '';
    appendUserMessage(message);

    // Cek jika user minta membuka notifikasi lewat percakapan
    const lower = message.toLowerCase();
    if (lower === 'ya' || lower === 'tampilkan notifikasi' || lower === 'buka notifikasi') {
        // Jika ada konteks notifikasi sebelumnya
        if (chatHistory.length > 0 && chatHistory[chatHistory.length - 1].content.includes('notifikasi')) {
            toggleChatNotifPanel(null, true);
            appendBotMessage('Panel notifikasi telah dibuka untuk Anda. Silakan lihat daftar pemberitahuan di atas.', 'Sistem Notifikasi');
            return;
        }
    }

    const typing = document.getElementById('santrifit-typing-indicator');
    const sendBtn = document.getElementById('santrifit-chat-send-btn');
    typing.classList.remove('d-none');
    sendBtn.disabled = true;

    const container = document.getElementById('santrifit-chat-messages');
    container.scrollTop = container.scrollHeight;

    try {
        const response = await fetch(CHATBOT_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: message,
                history: chatHistory,
                user: typeof santriFitCurrentUser !== "undefined" ? santriFitCurrentUser : null
            })
        });

        const data = await response.json();
        typing.classList.add('d-none');
        sendBtn.disabled = false;

        if (data.success && data.reply) {
            const statusEl = document.getElementById("server-status-text");
            if (statusEl) {
                statusEl.innerHTML = '<span class="text-success fw-bold">Online</span>';
            }
            appendBotMessage(data.reply, (data.provider || 'Nous Research') + ' • ' + (data.model || 'AI'));
            chatHistory.push({ role: 'user', content: message });
            chatHistory.push({ role: 'assistant', content: data.reply });
            
            // Perbarui unread notification counter
            fetchNotifCount();
        } else {
            appendBotMessage(data.reply || data.error || 'Terjadi kesalahan sistem AI.', 'Error');
        }
    } catch (err) {
        typing.classList.add('d-none');
        sendBtn.disabled = false;
        appendBotMessage('Maaf, tidak dapat terhubung ke server chatbot Node.js (http://' + CHATBOT_HOST + ':3001). Pastikan server chatbot sedang aktif.', 'Koneksi Gagal');
    }

    input.focus();
}

// ==========================================
// NOTIFICATION LOGIC (POLLING, BADGES, RBAC)
// ==========================================

async function fetchNotifCount() {
    try {
        const res = await fetch(NOTIF_API_URL + '?action=unread_count');
        if (res.ok) {
            const json = await res.json();
            const count = json.unread_count || 0;
            updateNotifBadgeUI(count);
        }
    } catch (e) {
        // Silent fail on network check
    }
}

function updateNotifBadgeUI(count) {
    const chatBadge = document.getElementById('chat-notif-badge');
    const floatingNotif = document.getElementById('chat-floating-notif-count');
    const unreadPill = document.getElementById('notif-unread-pill');

    if (count > 0) {
        if (chatBadge) {
            chatBadge.innerText = count > 99 ? '99+' : count;
            chatBadge.classList.remove('d-none');
        }
        if (floatingNotif) {
            floatingNotif.innerText = count > 99 ? '99+' : count;
            floatingNotif.classList.remove('d-none');
        }
        if (unreadPill) {
            unreadPill.innerText = count + ' Baru';
            unreadPill.className = 'badge bg-danger text-white rounded-pill';
        }
    } else {
        if (chatBadge) chatBadge.classList.add('d-none');
        if (floatingNotif) floatingNotif.classList.add('d-none');
        if (unreadPill) {
            unreadPill.innerText = 'Semua Dibaca';
            unreadPill.className = 'badge bg-secondary-subtle text-secondary rounded-pill';
        }
    }
}

function toggleChatNotifPanel(event, forceState = null) {
    if (event) event.stopPropagation();
    const panel = document.getElementById('santrifit-notif-panel');
    if (!panel) return;

    if (forceState !== null) {
        isNotifPanelOpen = forceState;
    } else {
        isNotifPanelOpen = !isNotifPanelOpen;
    }

    if (isNotifPanelOpen) {
        panel.classList.remove('d-none');
        panel.classList.add('d-flex');
        loadNotifications(currentNotifFilter);
    } else {
        panel.classList.add('d-none');
        panel.classList.remove('d-flex');
    }
}

function filterNotifs(filterType, element) {
    currentNotifFilter = filterType;
    document.querySelectorAll('.notif-chip').forEach(btn => btn.classList.remove('active'));
    if (element) element.classList.add('active');
    loadNotifications(filterType);
}

async function loadNotifications(filter = 'all') {
    const listContainer = document.getElementById('santrifit-notif-list');
    listContainer.innerHTML = `
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm text-teal mb-2" role="status"></div>
            <div style="font-size: 0.78rem;">Memuat notifikasi...</div>
        </div>
    `;

    try {
        const res = await fetch(NOTIF_API_URL + '?action=list&filter=' + encodeURIComponent(filter));
        if (res.ok) {
            const json = await res.json();
            updateNotifBadgeUI(json.unread_count || 0);
            renderNotificationList(json.data || []);
        } else {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-exclamation-circle text-warning fs-3 d-block mb-1"></i>
                    <span style="font-size: 0.78rem;">Gagal memuat notifikasi.</span>
                </div>
            `;
        }
    } catch (e) {
        listContainer.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-wifi-off text-muted fs-3 d-block mb-1"></i>
                <span style="font-size: 0.78rem;">Koneksi API notifikasi terputus.</span>
            </div>
        `;
    }
}

function renderNotificationList(items) {
    const listContainer = document.getElementById('santrifit-notif-list');
    if (!items.length) {
        listContainer.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-2 d-block mb-2 text-black-50"></i>
                <div class="fw-semibold" style="font-size: 0.82rem;">Tidak ada notifikasi</div>
                <div style="font-size: 0.74rem;">Semua pemberitahuan telah dibaca atau belum ada data baru.</div>
            </div>
        `;
        return;
    }

    let html = '';
    items.forEach(item => {
        const isUnread = parseInt(item.is_read) === 0;
        html += `
            <div class="notif-card ${isUnread ? 'unread' : ''}" onclick="handleNotifClick(${item.id}, this)">
                <div class="d-flex align-items-start gap-2">
                    <div class="notif-icon-box ${item.bg_class || 'bg-light'}">
                        <span>${item.icon || 'ℹ️'}</span>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.8rem;">
                                ${item.title}
                            </h6>
                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                ${isUnread ? '<span class="notif-unread-dot" title="Belum dibaca"></span>' : ''}
                                <span class="text-muted" style="font-size: 0.68rem;">${item.time_ago}</span>
                            </div>
                        </div>
                        <p class="mb-0 text-muted" style="font-size: 0.76rem; line-height: 1.35;">
                            ${item.message}
                        </p>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

async function handleNotifClick(notifId, cardElement) {
    if (!cardElement.classList.contains('unread')) return;

    // Optimistic UI update
    cardElement.classList.remove('unread');
    const dot = cardElement.querySelector('.notif-unread-dot');
    if (dot) dot.remove();

    try {
        const formData = new FormData();
        formData.append('id', notifId);
        const res = await fetch(NOTIF_API_URL + '?action=mark_read', {
            method: 'POST',
            body: formData
        });
        if (res.ok) {
            const json = await res.json();
            updateNotifBadgeUI(json.unread_count || 0);
        }
    } catch (e) {
        // Silent fail
    }
}

async function markAllNotifsRead() {
    try {
        const res = await fetch(NOTIF_API_URL + '?action=mark_all_read', { method: 'POST' });
        if (res.ok) {
            updateNotifBadgeUI(0);
            loadNotifications(currentNotifFilter);
        }
    } catch (e) {
        // Silent fail
    }
}

// Auto check server status & start notification polling (every 30 seconds)
document.addEventListener('DOMContentLoaded', () => {
    checkChatbotServerStatus();
    fetchNotifCount();
    // Polling setiap 30 detik
    setInterval(fetchNotifCount, 30000);
});
</script>