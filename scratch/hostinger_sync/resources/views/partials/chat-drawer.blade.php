{{-- ==========================================
     EUROTAXI INTERNAL STAFF CHAT DRAWER
     Floating bottom-right button + slide-up panel
     AJAX polling every 3s when open
=========================================== --}}

{{-- Floating Chat Button --}}
<div id="chatFloatingBtn"
     class="fixed bottom-6 right-6 z-[1200] md:block hidden"
     style="bottom: calc(1.5rem + env(safe-area-inset-bottom));">
    <button onclick="chatToggleDrawer()"
            class="relative w-14 h-14 bg-gradient-to-br from-yellow-500 to-amber-600 text-white rounded-full shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-110 flex items-center justify-center group">
        <i data-lucide="message-circle" class="w-6 h-6 group-hover:scale-110 transition-transform" id="chatBtnIcon"></i>
        <span id="chatUnreadBadge"
              class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 bg-red-500 text-white text-[10px] font-black leading-5 rounded-full text-center hidden">0</span>
    </button>
</div>

{{-- Chat Drawer Panel --}}
<div id="chatDrawer"
     class="fixed bottom-0 right-0 md:right-6 z-[1100] w-full md:w-96 bg-white rounded-t-3xl md:rounded-2xl shadow-2xl border border-gray-200 transform translate-y-full transition-all duration-400 ease-out overflow-hidden hidden">

    {{-- Chat Header --}}
    <div class="bg-gradient-to-r from-yellow-500 to-amber-500 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button onclick="chatBackToList()" id="chatBackBtn" class="text-white/80 hover:text-white hidden">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </button>
            <div>
                <h3 class="font-black text-white text-sm" id="chatHeaderTitle">Staff Chat</h3>
                <p class="text-yellow-100 text-[10px]" id="chatHeaderSub">Internal messaging</p>
            </div>
        </div>
        <button onclick="chatToggleDrawer()" class="text-white/80 hover:text-white p-1">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    {{-- User List View --}}
    <div id="chatUserList" class="flex flex-col" style="max-height: 420px; overflow-y: auto;">
        <div class="px-4 py-3 text-center text-gray-400 text-sm" id="chatUserListLoading">
            <i data-lucide="loader-2" class="w-4 h-4 animate-spin inline-block mr-1"></i> Loading...
        </div>
    </div>

    {{-- Message Thread View (hidden by default) --}}
    <div id="chatThread" class="hidden flex-col" style="max-height: 420px;">
        <div id="chatMessages" class="flex-1 overflow-y-auto px-4 py-3 space-y-3" style="min-height: 280px; max-height: 320px;"></div>
        <div class="border-t bg-white px-3 py-3 flex gap-2">
            <input type="text" id="chatMessageInput"
                   placeholder="Type a message..."
                   class="flex-1 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                   onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatSendMessage();}"
            >
            <button onclick="chatSendMessage()"
                    class="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl transition-colors flex-shrink-0">
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
// ───────────────────────────────────────────────────────────
//  EuroTaxi Internal Chat — AJAX Polling Architecture
// ───────────────────────────────────────────────────────────
(function() {
    let chatOpen       = false;
    let chatActiveUser = null; // { id, name }
    let chatPollTimer  = null;
    let chatSending    = false;

    // ── PUBLIC: Toggle drawer
    window.chatToggleDrawer = function() {
        chatOpen = !chatOpen;
        const drawer = document.getElementById('chatDrawer');
        const btn    = document.getElementById('chatBtnIcon');
        if (chatOpen) {
            drawer.classList.remove('hidden');
            requestAnimationFrame(() => drawer.classList.remove('translate-y-full'));
            if (btn) { btn.setAttribute('data-lucide', 'x'); lucide && lucide.createIcons(); }
            chatLoadUsers();
            chatStartPolling();
        } else {
            drawer.classList.add('translate-y-full');
            setTimeout(() => drawer.classList.add('hidden'), 400);
            if (btn) { btn.setAttribute('data-lucide', 'message-circle'); lucide && lucide.createIcons(); }
            chatStopPolling();
        }
    };

    // ── Load user list
    async function chatLoadUsers() {
        const list = document.getElementById('chatUserList');
        const loading = document.getElementById('chatUserListLoading');
        if (loading) loading.classList.remove('hidden');

        try {
            const res = await fetch('/chat/users', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const users = await res.json();
            if (loading) loading.classList.add('hidden');

            if (!users.length) {
                list.innerHTML = '<div class="px-4 py-6 text-center text-gray-400 text-sm">No other staff members online.</div>';
                return;
            }

            list.innerHTML = users.map(u => `
                <button onclick="chatOpenThread(${u.id}, '${u.name.replace(/'/g, "\\'")}')"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-yellow-50 transition-colors text-left border-b border-gray-50 w-full">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                        ${u.avatar}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">${u.name}</p>
                        <p class="text-[10px] text-gray-500 truncate">${u.last_msg || u.role}</p>
                    </div>
                    ${u.unread > 0 ? `<span class="bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0">${u.unread}</span>` : ''}
                </button>
            `).join('');

            // Update total unread badge
            const totalUnread = users.reduce((s, u) => s + u.unread, 0);
            const badge = document.getElementById('chatUnreadBadge');
            if (badge) {
                badge.textContent = totalUnread;
                totalUnread > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
            }
        } catch(e) {
            if (loading) loading.innerHTML = '<span class="text-red-400 text-xs">Could not load chat.</span>';
        }
    }

    // ── Open a conversation thread
    window.chatOpenThread = function(userId, userName) {
        chatActiveUser = { id: userId, name: userName };
        document.getElementById('chatUserList').classList.add('hidden');
        const thread = document.getElementById('chatThread');
        thread.classList.remove('hidden');
        thread.classList.add('flex');
        document.getElementById('chatHeaderTitle').textContent = userName;
        document.getElementById('chatHeaderSub').textContent = 'Direct message';
        document.getElementById('chatBackBtn').classList.remove('hidden');
        chatFetchMessages();
    };

    // ── Back to user list
    window.chatBackToList = function() {
        chatActiveUser = null;
        document.getElementById('chatThread').classList.add('hidden');
        document.getElementById('chatThread').classList.remove('flex');
        document.getElementById('chatUserList').classList.remove('hidden');
        document.getElementById('chatHeaderTitle').textContent = 'Staff Chat';
        document.getElementById('chatHeaderSub').textContent = 'Internal messaging';
        document.getElementById('chatBackBtn').classList.add('hidden');
        chatLoadUsers();
    };

    // ── Fetch messages for active thread
    async function chatFetchMessages() {
        if (!chatActiveUser) return;
        try {
            const res = await fetch(`/chat/messages/${chatActiveUser.id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            const messages = await res.json();
            const container = document.getElementById('chatMessages');
            container.innerHTML = messages.map(m => `
                <div class="flex ${m.is_mine ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[75%] ${m.is_mine
                        ? 'bg-yellow-500 text-white rounded-2xl rounded-br-sm'
                        : 'bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm'
                    } px-3 py-2">
                        <p class="text-sm">${escapeHtml(m.message)}</p>
                        <p class="text-[9px] mt-0.5 ${m.is_mine ? 'text-yellow-100' : 'text-gray-400'} text-right">${m.time}</p>
                    </div>
                </div>
            `).join('');
            container.scrollTop = container.scrollHeight;
        } catch(e) {}
    }

    // ── Send a message
    window.chatSendMessage = async function() {
        if (!chatActiveUser || chatSending) return;
        const input = document.getElementById('chatMessageInput');
        const msg = input.value.trim();
        if (!msg) return;

        chatSending = true;
        input.value = '';

        try {
            await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ to_user_id: chatActiveUser.id, message: msg })
            });
            chatFetchMessages();
        } catch(e) { input.value = msg; }
        finally { chatSending = false; }
    };

    // ── Polling
    function chatStartPolling() {
        chatStopPolling();
        chatPollTimer = setInterval(() => {
            if (chatActiveUser) { chatFetchMessages(); }
            else if (chatOpen) { chatLoadUsers(); }
        }, 3000);

        if (!window._chatVisibilityAdded) {
            window._chatVisibilityAdded = true;
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) { chatStopPolling(); }
                else if (chatOpen) { chatStartPolling(); }
            });
        }
    }

    function chatStopPolling() {
        if (chatPollTimer) { clearInterval(chatPollTimer); chatPollTimer = null; }
    }

    // ── HTML escape utility
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // ── Poll unread count globally (even when drawer is closed)
    function pollUnreadCount() {
        fetch('/chat/unread', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => {
            const badge = document.getElementById('chatUnreadBadge');
            if (badge) {
                badge.textContent = data.count;
                data.count > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
            }
        }).catch(() => {});
    }

    // Start global unread polling every 10 seconds
    setInterval(pollUnreadCount, 10000);

})();
</script>
