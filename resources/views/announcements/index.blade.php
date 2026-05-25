@extends('layouts.app')

@section('title', 'EuroTaxi | Announcement Management')
@section('page-heading', 'Announcements')
@section('page-subheading', 'Manage and broadcast important updates to all drivers')

@section('content')
<div class="container py-4">
    <div class="row">
        <!-- Create Announcement Form -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-2xl overflow-hidden">
                <div class="card-header bg-gradient-to-r from-yellow-500 to-amber-600 py-4 border-0">
                    <h5 class="text-white font-bold mb-0 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        New Announcement
                    </h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <form action="{{ route('announcements.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Title</label>
                            <input type="text" name="title" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm font-bold"
                                placeholder="Enter announcement title...">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Message (Optional)</label>
                            <textarea name="message" rows="5"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm"
                                placeholder="Enter your message here..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Display Until</label>
                            <input type="date" name="valid_until" id="valid_until" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm font-bold">
                            <span class="text-[10px] text-emerald-600 mt-1 block font-bold">Duration date is required to post announcements.</span>
                        </div>


                        <button type="submit" id="submitBtn" disabled 
                            class="w-full py-4 bg-yellow-600 hover:bg-yellow-700 text-white font-black rounded-xl transition-all shadow-lg shadow-yellow-100 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none flex items-center justify-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            POST
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Announcement List -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-2xl overflow-hidden">
                <div class="card-header bg-white py-4 border-b border-gray-50 flex justify-between items-center">
                    <h5 class="text-gray-900 font-bold mb-0 flex items-center gap-2">
                        <i data-lucide="megaphone" class="w-5 h-5 text-yellow-600"></i>
                        Broadcast History
                    </h5>
                    <span class="text-[10px] font-black text-yellow-700 bg-yellow-50 px-3 py-1 rounded-full border border-yellow-100 uppercase tracking-widest">
                        {{ $announcements->total() }} Total
                    </span>
                </div>
                <div class="card-body p-0 bg-gray-50/30">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-0">Announcement</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-0 w-44">Display Until</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-0 w-40">Date Sent</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-0 text-end w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($announcements as $announcement)
                                <tr class="hover:bg-gray-50 transition-colors">

                                    <td class="px-6 py-4">
                                        <div class="max-w-md">
                                            <h6 class="text-sm font-black text-gray-900 mb-1 leading-tight">{{ $announcement->title }}</h6>
                                            <p class="text-xs font-medium text-gray-500 mb-0 leading-relaxed line-clamp-2">{{ $announcement->message }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 w-44">
                                        @if($announcement->valid_until)
                                            <div class="text-[11px] font-bold text-gray-600 uppercase tracking-tighter">
                                                {{ $announcement->valid_until->format('M d, Y') }}
                                            </div>
                                            <div class="text-[9px] font-medium {{ $announcement->valid_until->isPast() ? 'text-rose-500' : 'text-emerald-500' }}">
                                                {{ $announcement->valid_until->isPast() ? 'Expired' : 'Expires ' . $announcement->valid_until->diffForHumans() }}
                                            </div>
                                        @else
                                            <span class="text-[10px] font-black text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100 uppercase tracking-widest">
                                                Indefinite
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 w-40">
                                        <div class="text-[11px] font-bold text-gray-600 uppercase tracking-tighter">
                                            {{ $announcement->created_at->format('M d, Y') }}
                                        </div>
                                        <div class="text-[9px] font-medium text-gray-400">
                                            {{ $announcement->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-end w-32">
                                        <div class="flex items-center justify-end gap-2">

                                            <!-- View Button -->
                                            <button 
                                                class="p-2 hover:bg-yellow-50 text-gray-400 hover:text-yellow-600 rounded-xl transition-all btn-view-announcement" 
                                                title="View"
                                                data-title="{{ $announcement->title }}"
                                                data-message="{{ $announcement->message }}"
                                                data-sent="{{ $announcement->created_at->format('M d, Y') }}"
                                                data-until="{{ $announcement->valid_until ? $announcement->valid_until->format('M d, Y') : 'Indefinite' }}">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Edit Button -->
                                            <button 
                                                class="p-2 hover:bg-yellow-50 text-gray-400 hover:text-yellow-600 rounded-xl transition-all btn-edit-announcement" 
                                                title="Edit"
                                                data-id="{{ $announcement->id }}"
                                                data-title="{{ $announcement->title }}"
                                                data-message="{{ $announcement->message }}"
                                                data-until="{{ $announcement->valid_until ? $announcement->valid_until->format('Y-m-d') : '' }}">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Delete Button -->
                                            <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Sigurado ka bang burahin ito?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 hover:bg-rose-50 text-gray-400 hover:text-rose-600 rounded-xl transition-all" title="Delete">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="p-4 bg-gray-50 rounded-full mb-3">
                                                <i data-lucide="megaphone-off" class="w-8 h-8 text-gray-300"></i>
                                            </div>
                                            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No announcements found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($announcements->hasPages())
                    <div class="p-4 border-t border-gray-50">
                        {{ $announcements->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Toggle Switch Custom Styles */
    .toggle-checkbox:checked {
        @apply: right-0;
        right: 0;
        border-color: #3b82f6;
    }
    .toggle-checkbox:checked + .toggle-label {
        @apply: bg-blue-500;
        background-color: #3b82f6;
    }
    .toggle-checkbox {
        right: 4px;
        transition: all 0.3s;
    }
    .toggle-label {
        transition: all 0.3s;
    }
    
    .table > :not(caption) > * > * {
        border-bottom-width: 0;
    }
</style>

<!-- View Announcement Modal -->
<div id="viewAnnouncementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="viewModalContainer">
        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 p-4 flex justify-between items-center text-white">
            <h5 class="font-bold mb-0 flex items-center gap-2">
                <i data-lucide="megaphone" class="w-5 h-5"></i>
                View Announcement
            </h5>
            <button type="button" onclick="closeViewModal()" class="text-white hover:text-gray-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Title</label>
                <div class="text-lg font-bold text-gray-900 mb-2" id="viewTitle"></div>
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Message</label>
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-sm font-semibold text-gray-800 leading-relaxed whitespace-pre-wrap" id="viewMessage"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Date Sent</label>
                    <div class="text-sm font-bold text-gray-700" id="viewDateSent"></div>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Display Until</label>
                    <div class="text-sm font-bold text-gray-700" id="viewDisplayUntil"></div>
                </div>
            </div>
        </div>
        <div class="p-4 border-t flex justify-end bg-gray-50">
            <button type="button" onclick="closeViewModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-95 opacity-0" id="editModalContainer">
        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 p-4 flex justify-between items-center text-white">
            <h5 class="font-bold mb-0 flex items-center gap-2">
                <i data-lucide="edit-3" class="w-5 h-5"></i>
                Edit Announcement
            </h5>
            <button type="button" onclick="closeEditModal()" class="text-white hover:text-gray-100 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Title</label>
                    <input type="text" name="title" id="editTitle" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Message (Optional)</label>
                    <textarea name="message" id="editMessage" rows="5"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Display Until</label>
                    <input type="date" name="valid_until" id="editValidUntil" required
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all outline-none text-sm font-bold">
                    <span class="text-[10px] text-emerald-600 mt-1 block font-bold">Duration date is required to update announcements.</span>
                </div>
            </div>
            <div class="p-4 border-t flex justify-end gap-3 bg-gray-50">
                <button type="button" onclick="closeEditModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-lg shadow-md shadow-yellow-100 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Global Modal helpers for close
    function closeViewModal() {
        const modal = document.getElementById('viewAnnouncementModal');
        const container = document.getElementById('viewModalContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    function closeEditModal() {
        const modal = document.getElementById('editAnnouncementModal');
        const container = document.getElementById('editModalContainer');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('valid_until');
        const submitBtn = document.getElementById('submitBtn');

        function checkButtonState() {
            if (dateInput.value) {
                submitBtn.removeAttribute('disabled');
            } else {
                submitBtn.setAttribute('disabled', 'true');
            }
        }

        dateInput.addEventListener('input', checkButtonState);
        dateInput.addEventListener('change', checkButtonState);
        checkButtonState();

        // Handle View Announcement Button Click
        document.querySelectorAll('.btn-view-announcement').forEach(button => {
            button.addEventListener('click', function() {
                const title = this.getAttribute('data-title');
                const message = this.getAttribute('data-message');
                const sent = this.getAttribute('data-sent');
                const until = this.getAttribute('data-until');
                
                document.getElementById('viewTitle').innerText = title;
                document.getElementById('viewMessage').innerText = message;
                document.getElementById('viewDateSent').innerText = sent;
                document.getElementById('viewDisplayUntil').innerText = until;
                
                const modal = document.getElementById('viewAnnouncementModal');
                const container = document.getElementById('viewModalContainer');
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);
            });
        });

        // Handle Edit Announcement Button Click
        document.querySelectorAll('.btn-edit-announcement').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const message = this.getAttribute('data-message');
                const until = this.getAttribute('data-until');
                
                document.getElementById('editTitle').value = title;
                document.getElementById('editMessage').value = message;
                document.getElementById('editValidUntil').value = until;
                document.getElementById('editForm').action = `/announcements/${id}`;
                
                const modal = document.getElementById('editAnnouncementModal');
                const container = document.getElementById('editModalContainer');
                
                modal.classList.remove('hidden');
                setTimeout(() => {
                    container.classList.remove('scale-95', 'opacity-0');
                    container.classList.add('scale-100', 'opacity-100');
                }, 50);
            });
        });
    });
</script>
@endsection
