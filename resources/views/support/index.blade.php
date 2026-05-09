@extends('layouts.app')

@section('title', 'Driver Support Chat')

@section('content')
<div class="flex h-[calc(100vh-180px)] bg-white rounded-3xl shadow-2xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
    <!-- Left Sidebar: Driver List -->
    <div class="w-80 border-r border-gray-100 flex flex-col bg-gray-50/30">
        <div class="p-6 border-b border-gray-100 bg-white">
            <h3 class="text-xl font-black text-gray-900 flex items-center gap-2">
                <i data-lucide="message-circle" class="w-6 h-6 text-yellow-600"></i>
                Messages
            </h3>
            <div class="mt-4 relative">
                <input type="text" placeholder="Search drivers..." class="w-full pl-10 pr-4 py-2 bg-gray-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 outline-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
            @forelse($drivers as $driver)
                <a href="{{ route('support.index', ['driver_id' => $driver->id]) }}" 
                   class="flex items-center gap-3 p-3 rounded-2xl transition-all duration-200 {{ isset($selectedDriver) && $selectedDriver->id == $driver->id ? 'bg-yellow-600 text-white shadow-lg shadow-yellow-200 translate-x-1' : 'hover:bg-white hover:shadow-md text-gray-700' }}">
                    <div class="relative flex-shrink-0">
                        <div class="w-12 h-12 rounded-xl bg-gray-200 flex items-center justify-center font-bold text-lg border-2 border-white shadow-sm overflow-hidden">
                            @if($driver->profile_image)
                                <img src="{{ asset('storage/' . $driver->profile_image) }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($driver->full_name ?? 'D', 0, 1)) }}
                            @endif
                        </div>
                        @if($driver->unread_count > 0)
                            <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-white">
                                {{ $driver->unread_count }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h4 class="text-sm font-bold truncate">{{ $driver->full_name }}</h4>
                            @if($driver->latest_message_time)
                                <span class="text-[10px] {{ isset($selectedDriver) && $selectedDriver->id == $driver->id ? 'text-yellow-100' : 'text-gray-400' }}">
                                    {{ \Carbon\Carbon::parse($driver->latest_message_time)->format('h:i A') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs truncate {{ isset($selectedDriver) && $selectedDriver->id == $driver->id ? 'text-yellow-50/80' : 'text-gray-500' }}">
                            {{ $driver->latest_message ?? 'No messages yet' }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="p-8 text-center">
                    <p class="text-sm text-gray-400">No drivers found</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right: Chat Window -->
    <div class="flex-1 flex flex-col bg-white">
        @if($selectedDriver)
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-white/80 backdrop-blur-md z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700 font-bold border border-yellow-200">
                         {{ strtoupper(substr($selectedDriver->full_name ?? 'D', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-gray-900">{{ $selectedDriver->full_name }}</h3>
                        <p class="text-[10px] text-emerald-500 font-bold uppercase tracking-widest flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                            Online (Driver App)
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <!-- Icons removed per user request -->
                </div>
            </div>

            <!-- Messages Area -->
            <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50 custom-scrollbar">
                @forelse($chatMessages as $msg)
                    <div class="flex {{ $msg->sender_type == 'admin' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[70%] group">
                            <div class="flex items-center gap-2 mb-1 {{ $msg->sender_type == 'admin' ? 'flex-row-reverse' : '' }}">
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">
                                    {{ $msg->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <div class="px-4 py-3 rounded-2xl text-sm shadow-sm {{ $msg->sender_type == 'admin' ? 'bg-yellow-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none' }}">
                                {{ $msg->message }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="h-full flex flex-col items-center justify-center text-center p-8">
                        <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="message-square" class="w-8 h-8 text-yellow-600"></i>
                        </div>
                        <h4 class="text-gray-400 font-bold uppercase tracking-widest">Start a conversation</h4>
                        <p class="text-sm text-gray-400 mt-1">Send a message to {{ $selectedDriver->first_name }} to begin.</p>
                    </div>
                @endforelse
            </div>

            <!-- Message Input -->
            <div class="p-4 bg-white border-t border-gray-100">
                <form id="chatForm" action="{{ route('support.send') }}" method="POST" class="flex items-end gap-2">
                    @csrf
                    <input type="hidden" name="driver_id" value="{{ $selectedDriver->id }}">
                    <div class="flex-1 relative">
                        <textarea 
                            name="message" 
                            id="messageInput"
                            rows="1" 
                            placeholder="Type a message..." 
                            class="w-full px-4 py-3 bg-gray-100 border-none rounded-2xl text-sm focus:ring-2 focus:ring-yellow-500 outline-none resize-none max-h-32 transition-all overflow-hidden"
                            oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"
                            required
                        ></textarea>
                    </div>
                    <button type="submit" id="sendButton" class="p-3 bg-yellow-600 text-white rounded-xl hover:bg-yellow-700 shadow-lg shadow-yellow-100 transition-all flex items-center justify-center">
                        <i data-lucide="send" id="sendIcon" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        @else
            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center text-center p-12 bg-gray-50/20">
                <div class="w-24 h-24 bg-white rounded-3xl shadow-xl flex items-center justify-center mb-6 rotate-3">
                    <i data-lucide="messages-square" class="w-12 h-12 text-yellow-600"></i>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Your Conversations</h3>
                <p class="text-gray-500 text-sm max-w-xs">Select a driver from the left to start chatting or view support requests.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatContainer = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const sendIcon = document.getElementById('sendIcon');
        
        let lastMessageCount = @json($chatMessages ? count($chatMessages) : 0);
        const selectedDriverId = @json($selectedDriver ? $selectedDriver->id : null);

        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // --- Polling for New Messages ---
        if (selectedDriverId) {
            setInterval(async () => {
                try {
                    const response = await fetch(`/support-center/${selectedDriverId}/messages`);
                    const data = await response.json();
                    
                    if (data.success && data.messages.length > lastMessageCount) {
                        renderMessages(data.messages);
                        lastMessageCount = data.messages.length;
                    }
                } catch (e) {
                    console.error('Polling failed', e);
                }
            }, 1000); // Poll every 1 second for active chat
        }

        // --- Polling for Driver List Status ---
        setInterval(async () => {
            try {
                const response = await fetch('/support-center/status');
                const data = await response.json();
                if (data.success) {
                    updateDriverList(data.drivers);
                }
            } catch (e) {
                console.error('Status polling failed', e);
            }
        }, 3000); // Poll every 3 seconds for sidebar status

        function renderMessages(messages) {
            chatContainer.innerHTML = '';
            messages.forEach(msg => {
                const isSystem = msg.sender_type === 'admin';
                const div = document.createElement('div');
                div.className = `flex ${isSystem ? 'justify-end' : 'justify-start'}`;
                
                const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                div.innerHTML = `
                    <div class="max-w-[70%] group">
                        <div class="flex items-center gap-2 mb-1 ${isSystem ? 'flex-row-reverse' : ''}">
                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">${time}</span>
                        </div>
                        <div class="px-4 py-3 rounded-2xl text-sm shadow-sm ${isSystem ? 'bg-yellow-600 text-white rounded-tr-none' : 'bg-white text-gray-800 border border-gray-100 rounded-tl-none'}">
                            ${msg.message}
                        </div>
                    </div>
                `;
                chatContainer.appendChild(div);
            });
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function updateDriverList(drivers) {
            drivers.forEach(driver => {
                const badge = document.querySelector(`a[href*="driver_id=${driver.id}"] .bg-red-500`);
                const latestMsgP = document.querySelector(`a[href*="driver_id=${driver.id}"] p`);
                
                if (latestMsgP) {
                    latestMsgP.innerText = driver.latest_message || 'No messages yet';
                }

                // Handle badge visibility
                const driverLink = document.querySelector(`a[href*="driver_id=${driver.id}"] .relative`);
                if (driverLink) {
                    let existingBadge = driverLink.querySelector('.bg-red-500');
                    if (driver.unread_count > 0) {
                        if (!existingBadge) {
                            existingBadge = document.createElement('span');
                            existingBadge.className = 'absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-white';
                            driverLink.appendChild(existingBadge);
                        }
                        existingBadge.innerText = driver.unread_count;
                    } else if (existingBadge) {
                        existingBadge.remove();
                    }
                }
            });
        }

        // --- AJAX Form Submission ---
    if (chatForm) {
        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            // --- OPTIMISTIC UI: Append message immediately ---
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const tempDiv = document.createElement('div');
            tempDiv.className = 'flex justify-end opacity-70';
            tempDiv.innerHTML = `
                <div class="max-w-[70%] group">
                    <div class="flex items-center gap-2 mb-1 flex-row-reverse">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-tighter">${time} (sending...)</span>
                    </div>
                    <div class="px-4 py-3 rounded-2xl text-sm shadow-sm bg-yellow-600 text-white rounded-tr-none">
                        ${message}
                    </div>
                </div>
            `;
            chatContainer.appendChild(tempDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;

            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendButton.disabled = true;

            try {
                const formData = new FormData(chatForm);
                formData.set('message', message); // ensure message is set
                
                const response = await fetch(chatForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (response.ok) {
                    // Update state and re-fetch to sync IDs and real timestamps
                    const msgRes = await fetch(`/support-center/${selectedDriverId}/messages`);
                    const msgData = await msgRes.json();
                    if (msgData.success) {
                        renderMessages(msgData.messages);
                        lastMessageCount = msgData.messages.length;
                    }
                }
            } catch (e) {
                console.error('Send failed', e);
                tempDiv.innerHTML = '<span class="text-[9px] text-red-500 font-bold">Failed to send.</span>';
            } finally {
                sendButton.disabled = false;
            }
        });
    }
    });
</script>
@endsection
