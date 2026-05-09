@extends('layouts.app')

@section('title', 'Respond to Ticket')
@section('page-heading', 'Ticket Details')
@section('page-subheading', 'Conversation with ' . ($ticket->driver->full_name ?? 'Driver'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('support.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            BACK TO LIST
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Ticket Thread -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Driver Message -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-yellow-600 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-yellow-100">
                                {{ strtoupper(substr($ticket->driver->full_name ?? 'D', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-base font-black text-gray-900">{{ $ticket->subject }}</h3>
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">Submitted {{ $ticket->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 text-[10px] font-black rounded-full border border-yellow-200 uppercase tracking-widest">
                            {{ str_replace('_', ' ', $ticket->category) }}
                        </span>
                    </div>
                    <div class="p-4 bg-white rounded-xl border border-gray-100 text-gray-700 text-sm leading-relaxed shadow-inner">
                        {{ $ticket->message }}
                    </div>
                </div>

                <!-- Admin Response Form -->
                <div class="p-6 bg-white">
                    <form action="{{ route('support.update', $ticket->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Admin Response</label>
                            <textarea 
                                name="admin_reply" 
                                rows="6" 
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm outline-none transition-all resize-none"
                                placeholder="Type your reply to the driver here..."
                                required
                            >{{ old('admin_reply', $ticket->admin_reply) }}</textarea>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Ticket Status</label>
                                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-yellow-500 outline-none text-sm font-bold appearance-none bg-gray-50">
                                    <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>PENDING</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>RESOLVED / CLOSED</option>
                                </select>
                            </div>
                            <div class="pt-6">
                                <button type="submit" class="px-8 py-2.5 bg-yellow-600 text-white font-black rounded-xl hover:bg-yellow-700 shadow-lg shadow-yellow-100 transition-all flex items-center gap-2">
                                    <i data-lucide="send" class="w-4 h-4"></i>
                                    SEND REPLY
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <!-- Driver Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Driver Profile</h4>
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-14 h-14 rounded-full bg-gray-100 border-4 border-gray-50 flex items-center justify-center overflow-hidden">
                        @if($ticket->driver->profile_image)
                            <img src="{{ asset('storage/' . $ticket->driver->profile_image) }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="user" class="w-6 h-6 text-gray-300"></i>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-black text-gray-900">{{ $ticket->driver->full_name ?? 'Unknown' }}</div>
                        <div class="text-[11px] text-emerald-600 font-bold uppercase tracking-tight">Active App User</div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-[11px] text-gray-500 font-bold uppercase">Phone</span>
                        <span class="text-xs font-black text-gray-900">{{ $ticket->driver->phone_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-[11px] text-gray-500 font-bold uppercase">License</span>
                        <span class="text-xs font-black text-gray-900">{{ $ticket->driver->license_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-[11px] text-gray-500 font-bold uppercase">Plate</span>
                        <span class="text-xs font-black text-gray-900">{{ $ticket->driver->plate_number ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('driver-management.show', $ticket->driver_id) }}" class="block text-center py-2 bg-gray-50 text-gray-600 text-[10px] font-black rounded-lg hover:bg-gray-100 uppercase tracking-widest border border-gray-200">
                        View Full History
                    </a>
                </div>
            </div>

            <!-- Audit Info -->
            <div class="bg-gray-900 rounded-2xl p-6 text-white">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="info" class="w-4 h-4 text-yellow-500"></i>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">System Info</h4>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Ticket ID</p>
                        <p class="text-xs font-mono">#TK-{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="text-[9px] text-gray-400 font-bold uppercase mb-1">Last Updated</p>
                        <p class="text-xs font-medium">{{ $ticket->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
