@if(empty($items))
    <div class="text-center py-12">
        <i data-lucide="inbox" class="mx-auto h-12 w-12 text-gray-300"></i>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No archived terms</h3>
        <p class="mt-1 text-sm text-gray-500">There are no deleted driver terms documents.</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($items as $filename)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group flex flex-col">
                <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-sm truncate" title="{{ $filename }}">
                        <i data-lucide="file-text" class="w-4 h-4 text-gray-400 inline"></i>
                        Document
                    </h3>
                </div>
                <div class="p-4 flex-1 flex justify-center items-center bg-gray-50">
                    <img src="{{ asset('uploads/archives/terms/' . $filename) }}" alt="Archived Term" 
                         class="w-full h-32 object-contain rounded-lg shadow-sm border border-gray-200 cursor-zoom-in hover:shadow-md transition-shadow select-none"
                         style="-webkit-user-drag: none;" oncontextmenu="return false;" draggable="false"
                         onclick="openLightbox('{{ asset('uploads/archives/terms/' . $filename) }}')">
                </div>
                <div class="p-4 border-t border-gray-100 bg-white flex justify-between gap-2">
                    <button type="button" onclick="archiveRestore('{{ route('driver-management.terms.restore', $filename) }}')" class="flex-1 flex justify-center items-center gap-1 text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 hover:bg-emerald-100 px-3 py-2 rounded-lg transition-colors">
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Restore
                    </button>
                    <button type="button" onclick="archiveForceDelete('{{ route('driver-management.terms.force-delete', $filename) }}')" class="flex-1 flex justify-center items-center gap-1 text-[10px] font-black uppercase tracking-wider text-red-600 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg transition-colors">
                        <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                    </button>
                </div>
            </div>
        @endforeach
    </div>
@endif
