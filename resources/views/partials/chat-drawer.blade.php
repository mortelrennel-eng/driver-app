{{-- ==========================================
     EUROTAXI INTERNAL STAFF CHAT
     v6 — Premium Draggable Unified Widget
     - Unified Container: panel and button are locked together
     - Flawless Click-vs-Drag: threshold prevents accidental triggers
     - Draggable in both open and closed states
     - Premium fluid bouncy scaling transitions
=========================================== --}}

{{-- Single Draggable Wrapper: contains BOTH the panel and the button --}}
<div id="chatWidgetContainer"
     class="fixed z-[1200] md:flex hidden flex-col items-end gap-3"
     style="bottom: 1.5rem; right: 1.5rem; width: 340px; pointer-events: none; touch-action: none;">

    {{-- ① Chat Panel (shown/hidden above the button) --}}
    <div id="chatDrawer"
         class="w-full bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden pointer-events-auto flex flex-col transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] transform origin-bottom-right opacity-0 pointer-events-none scale-95 translate-y-4"
         style="height: 460px; max-height: 460px;">

        {{-- Drag Handle (yellow header) --}}
        <div id="chatDragHandle"
             class="bg-gradient-to-r from-yellow-500 to-amber-500 px-4 py-3 flex items-center justify-between select-none shrink-0"
             style="cursor: grab;">
            <div class="flex items-center gap-3">
                <button onclick="chatBackToList()"
                        id="chatBackBtn"
                        class="text-white/85 hover:text-white transition-colors hidden"
                        style="cursor:pointer; pointer-events:auto;">
                    <i data-lucide="arrow-left" class="w-5 h-5 pointer-events-none"></i>
                </button>
                <div>
                    <h3 class="font-black text-white text-sm flex items-center gap-1.5 pointer-events-none" id="chatHeaderTitle">
                        <i data-lucide="grip-horizontal" class="w-3.5 h-3.5 text-yellow-100 opacity-75"></i>
                        Staff Chat
                    </h3>
                    <p class="text-yellow-100 text-[10px] pointer-events-none opacity-90" id="chatHeaderSub">Hold header to drag • Internal</p>
                </div>
            </div>
            <button onclick="chatToggleDrawer()"
                    class="text-white/85 hover:text-white p-1 transition-colors rounded-lg"
                    style="cursor:pointer; pointer-events:auto;">
                <i data-lucide="x" class="w-5 h-5 pointer-events-none"></i>
            </button>
        </div>

        {{-- Panel Body --}}
        <div class="bg-white flex-1 overflow-hidden flex flex-col border-t border-gray-100">
            {{-- User List --}}
            <div id="chatUserList" class="flex flex-col flex-1 overflow-y-auto" style="max-height: 400px;">
                <div class="px-4 py-8 text-center text-gray-400 text-sm" id="chatUserListLoading">
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin inline-block mr-2 text-yellow-500"></i> Loading...
                </div>
            </div>

            {{-- Message Thread --}}
            <div id="chatThread" class="hidden flex-col flex-1 overflow-hidden">
                <div id="staffChatMessages"
                     class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50"
                     style="min-height: 280px;"></div>
                <div class="border-t bg-white px-3 py-3 flex gap-2 shrink-0">
                    <input type="text"
                           id="staffChatMessageInput"
                           placeholder="Type a message..."
                           class="flex-1 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400"
                           onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();chatSendMessage();}">
                    <button onclick="chatSendMessage()"
                            class="px-3.5 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl transition-colors flex-shrink-0 flex items-center justify-center">
                        <i data-lucide="send" class="w-4 h-4 pointer-events-none"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ② Floating Chat Button (Draggable & Clickable) --}}
    <div class="pointer-events-auto shrink-0 select-none">
        <button id="chatOpenBtn"
                class="relative w-14 h-14 bg-gradient-to-br from-yellow-500 to-amber-600 text-white rounded-full shadow-2xl hover:shadow-yellow-500/20 transition-all duration-200 hover:scale-105 flex items-center justify-center"
                style="cursor: grab; touch-action: none;">
            <i data-lucide="message-circle" class="w-6 h-6 transition-transform duration-200" id="chatBtnIcon"></i>
            <span id="chatUnreadBadge"
                  class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-[10px] font-black leading-5 rounded-full text-center hidden shadow-md animate-pulse">0</span>
        </button>
    </div>
</div>

<script>
(function () {
    let chatOpen       = false;
    let chatActiveUser = null;
    let chatPollTimer  = null;
    let chatSending    = false;

    // ─── Toggle Open / Close ───────────────────────────────────
    window.chatToggleDrawer = function () {
        chatOpen = !chatOpen;
        const drawer = document.getElementById('chatDrawer');
        const icon   = document.getElementById('chatBtnIcon');

        if (chatOpen) {
            // Smooth bouncy open
            drawer.classList.remove('opacity-0', 'pointer-events-none', 'scale-95', 'translate-y-4');
            drawer.classList.add('opacity-100', 'pointer-events-auto', 'scale-100', 'translate-y-0');
            if (icon) {
                icon.setAttribute('data-lucide', 'chevron-down');
                if (window.lucide) window.lucide.createIcons();
            }
            chatLoadUsers();
            chatStartPolling();
        } else {
            // Smooth close
            drawer.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100', 'translate-y-0');
            drawer.classList.add('opacity-0', 'pointer-events-none', 'scale-95', 'translate-y-4');
            if (icon) {
                icon.setAttribute('data-lucide', 'message-circle');
                if (window.lucide) window.lucide.createIcons();
            }
            chatStopPolling();
        }
    };

    // ─── Load Staff Users ──────────────────────────────────────
    async function chatLoadUsers() {
        const list    = document.getElementById('chatUserList');
        const loading = document.getElementById('chatUserListLoading');
        if (loading) loading.classList.remove('hidden');

        try {
            const res   = await fetch('/chat/users', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const users = await res.json();
            if (loading) loading.classList.add('hidden');

            if (!users.length) {
                list.innerHTML = '<div class="px-4 py-8 text-center text-gray-400 text-sm">No other staff online.</div>';
                return;
            }

            list.innerHTML = users.map(u => `
                <button onclick="chatOpenThread(${u.id}, '${u.name.replace(/'/g, "\\'")}')"
                        class="flex items-center gap-3 px-4 py-3.5 hover:bg-yellow-50/50 transition-colors text-left border-b border-gray-100 w-full last:border-b-0">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-sm border border-white">
                        ${u.avatar}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">${u.name}</p>
                        <p class="text-[10px] text-gray-500 truncate mt-0.5">${u.last_msg || u.role}</p>
                    </div>
                    ${u.unread > 0
                        ? `<span class="bg-red-500 text-white text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">${u.unread}</span>`
                        : ''}
                </button>
            `).join('');

            const totalUnread = users.reduce((s, u) => s + u.unread, 0);
            const badge = document.getElementById('chatUnreadBadge');
            if (badge) {
                badge.textContent = totalUnread;
                totalUnread > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
            }
        } catch (e) {
            if (loading) loading.innerHTML = '<span class="text-red-400 text-xs px-4">Could not load chat list.</span>';
        }
    }

    // ─── Open conversation thread ──────────────────────────────
    window.chatOpenThread = function (userId, userName) {
        chatActiveUser = { id: userId, name: userName };
        document.getElementById('chatUserList').classList.add('hidden');
        const thread = document.getElementById('chatThread');
        thread.classList.remove('hidden');
        thread.classList.add('flex');
        document.getElementById('chatHeaderTitle').innerHTML =
            '<i data-lucide="grip-horizontal" class="w-3.5 h-3.5 text-yellow-100 opacity-75 pointer-events-none"></i> ' + userName;
        document.getElementById('chatHeaderSub').textContent = 'Hold header to drag • Direct chat';
        document.getElementById('chatBackBtn').classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
        chatFetchMessages();
    };

    // ─── Back to list view ─────────────────────────────────────
    window.chatBackToList = function () {
        chatActiveUser = null;
        document.getElementById('chatThread').classList.add('hidden');
        document.getElementById('chatThread').classList.remove('flex');
        document.getElementById('chatUserList').classList.remove('hidden');
        document.getElementById('chatHeaderTitle').innerHTML =
            '<i data-lucide="grip-horizontal" class="w-3.5 h-3.5 text-yellow-100 opacity-75 pointer-events-none"></i> Staff Chat';
        document.getElementById('chatHeaderSub').textContent = 'Hold header to drag • Internal';
        document.getElementById('chatBackBtn').classList.add('hidden');
        if (window.lucide) window.lucide.createIcons();
        chatLoadUsers();
    };

    // ─── Fetch Thread Messages ─────────────────────────────────
    async function chatFetchMessages() {
        if (!chatActiveUser) return;
        try {
            const res = await fetch(`/chat/messages/${chatActiveUser.id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const messages = await res.json();
            const box = document.getElementById('staffChatMessages');
            box.innerHTML = messages.map(m => `
                <div class="flex ${m.is_mine ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[78%] px-3 py-2 shadow-sm ${m.is_mine
                        ? 'bg-gradient-to-r from-yellow-500 to-amber-500 text-white rounded-2xl rounded-br-none'
                        : 'bg-white text-gray-800 rounded-2xl rounded-bl-none border border-gray-100'}">
                        <p class="text-sm leading-relaxed">${escapeHtml(m.message)}</p>
                        <p class="text-[9px] mt-1 text-right ${m.is_mine ? 'text-yellow-100' : 'text-gray-400'}">${m.time}</p>
                    </div>
                </div>
            `).join('');
            box.scrollTop = box.scrollHeight;
        } catch (e) {}
    }

    // ─── Send Message ──────────────────────────────────────────
    window.chatSendMessage = async function () {
        if (!chatActiveUser || chatSending) return;
        const input = document.getElementById('staffChatMessageInput');
        const msg   = input.value.trim();
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
        } catch (e) { input.value = msg; }
        finally { chatSending = false; }
    };

    // ─── Polling ───────────────────────────────────────────────
    function chatStartPolling() {
        chatStopPolling();
        chatPollTimer = setInterval(() => {
            if (chatActiveUser) chatFetchMessages();
            else if (chatOpen)  chatLoadUsers();
        }, 3000);
        if (!window._chatVisAdded) {
            window._chatVisAdded = true;
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) chatStopPolling();
                else if (chatOpen)   chatStartPolling();
            });
        }
    }

    function chatStopPolling() {
        if (chatPollTimer) { clearInterval(chatPollTimer); chatPollTimer = null; }
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    // ─── Global unread badge periodic update ───────────────────
    function pollUnread() {
        fetch('/chat/unread', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                const badge = document.getElementById('chatUnreadBadge');
                if (!badge) return;
                badge.textContent = d.count;
                d.count > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
            })
            .catch(() => {});
    }
    setInterval(pollUnread, 10000);

    // ─── Drag & Click Event System ─────────────────────────────
    function initWidgetDrag() {
        const container = document.getElementById('chatWidgetContainer');
        const header    = document.getElementById('chatDragHandle');
        const button    = document.getElementById('chatOpenBtn');
        if (!container || !header || !button) return;

        let startX = 0, startY = 0;
        let initialLeft = 0, initialTop = 0;
        let hasDragged = false;
        let activeDrag = false;

        function getCoords(e) {
            if (e.touches && e.touches.length > 0) {
                return { x: e.touches[0].clientX, y: e.touches[0].clientY };
            }
            return { x: e.clientX, y: e.clientY };
        }

        function toLeftTop() {
            if (container.style.right !== 'auto') {
                const rect = container.getBoundingClientRect();
                container.style.left = rect.left + 'px';
                container.style.top = rect.top + 'px';
                container.style.right = 'auto';
                container.style.bottom = 'auto';
            }
        }

        function dragStart(e) {
            // Ignore if user clicked close or back button inside the header
            if (e.target.closest('button') && e.target.closest('button') !== button) {
                return;
            }

            activeDrag = true;
            hasDragged = false;

            const coords = getCoords(e);
            startX = coords.x;
            startY = coords.y;

            toLeftTop();
            initialLeft = parseFloat(container.style.left) || 0;
            initialTop  = parseFloat(container.style.top)  || 0;

            if (e.type === 'touchstart') {
                document.addEventListener('touchmove', dragMove, { passive: false });
                document.addEventListener('touchend',  dragEnd);
            } else {
                document.addEventListener('mousemove', dragMove);
                document.addEventListener('mouseup',   dragEnd);
            }
        }

        function dragMove(e) {
            if (!activeDrag) return;

            const coords = getCoords(e);
            const dx = coords.x - startX;
            const dy = coords.y - startY;

            // Threshold: 6px of movement makes it a drag
            if (!hasDragged && Math.hypot(dx, dy) > 6) {
                hasDragged = true;
                header.style.cursor = 'grabbing';
                button.style.cursor = 'grabbing';
            }

            if (hasDragged) {
                e.preventDefault(); // Prevent touch-screen scrolling
                let nl = initialLeft + dx;
                let nt = initialTop  + dy;

                // Screen boundaries lock
                nl = Math.max(0, Math.min(nl, window.innerWidth  - container.offsetWidth));
                nt = Math.max(0, Math.min(nt, window.innerHeight - container.offsetHeight));

                container.style.left = nl + 'px';
                container.style.top  = nt + 'px';
            }
        }

        function dragEnd(e) {
            activeDrag = false;
            header.style.cursor = 'grab';
            button.style.cursor = 'grab';

            if (e.type === 'touchend') {
                document.removeEventListener('touchmove', dragMove);
                document.removeEventListener('touchend',  dragEnd);
            } else {
                document.removeEventListener('mousemove', dragMove);
                document.removeEventListener('mouseup',   dragEnd);
            }
        }

        // Mouse listeners
        header.addEventListener('mousedown', dragStart);
        button.addEventListener('mousedown', dragStart);

        // Touch listeners
        header.addEventListener('touchstart', dragStart, { passive: true });
        button.addEventListener('touchstart', dragStart, { passive: true });

        // Handle standard tap/click on button safely
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            // If the user actually dragged the widget, block the click event to avoid opening
            if (hasDragged) {
                return;
            }
            chatToggleDrawer();
        });

        // Ensure resizing window doesn't place the widget offscreen
        window.addEventListener('resize', function () {
            if (container.style.left && container.style.left !== 'auto') {
                let nl = parseFloat(container.style.left) || 0;
                let nt = parseFloat(container.style.top)  || 0;
                nl = Math.max(0, Math.min(nl, window.innerWidth  - container.offsetWidth));
                nt = Math.max(0, Math.min(nt, window.innerHeight - container.offsetHeight));
                container.style.left = nl + 'px';
                container.style.top  = nt + 'px';
            }
        });
    }

    // Start everything on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidgetDrag);
    } else {
        initWidgetDrag();
    }
})();
</script>
