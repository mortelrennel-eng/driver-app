@extends('layouts.app')

@section('title', 'Driver Terms & Conditions - Euro System')
@section('page-heading', 'Driver Terms & Conditions')
@section('page-subheading', 'Official company policies and contractual terms for drivers')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10 flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i data-lucide="file-signature" class="w-5 h-5 text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Company Terms & Policy Proofs</h2>
            </div>
            <p class="text-gray-500 text-sm max-w-2xl">
                This section contains the official scanned documents and proof of terms agreed upon by the drivers. 
                You can upload new pages or documents here.
            </p>
        </div>
        <div class="relative z-10 flex gap-3">
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-all transform active:scale-95">
                <i data-lucide="upload" class="w-4 h-4"></i>
                Upload Document
            </button>
            <button onclick="window.print()" class="flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold rounded-xl shadow-sm transition-all transform active:scale-95">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Print
            </button>
        </div>
    </div>

    <!-- Terms Documents Gallery -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($termsImages as $index => $image)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group">
                <div class="p-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i>
                        Document Page {{ $index + 1 }}
                    </h3>
                    <form action="{{ route('driver-management.terms.delete', $image) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[10px] font-black uppercase tracking-wider text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                        </button>
                    </form>
                </div>
                <div class="p-6 flex justify-center items-center bg-gray-50 min-h-[500px] relative">
                    <img src="{{ asset('uploads/terms/' . $image) }}" alt="Terms Page {{ $index + 1 }}" 
                         class="w-full h-auto object-contain rounded-lg shadow-sm border border-gray-200 cursor-zoom-in hover:shadow-md transition-shadow"
                         onclick="openLightbox('{{ asset('uploads/terms/' . $image) }}')">
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-2 bg-white rounded-2xl shadow-sm border border-dashed border-gray-300 p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="folder-open" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No Documents Uploaded</h3>
                <p class="text-gray-500 mb-6 max-w-sm mx-auto">There are currently no terms and conditions documents uploaded to the system.</p>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="px-5 py-2 bg-blue-50 text-blue-700 font-semibold rounded-lg hover:bg-blue-100 transition-colors inline-flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i> Upload First Document
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 backdrop-blur-sm" aria-hidden="true" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-gray-100">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" class="text-gray-400 bg-white rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="document.getElementById('uploadModal').classList.add('hidden')">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <i data-lucide="upload-cloud" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Upload Terms Document</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-4">Upload a scanned copy or picture of the terms and conditions.</p>
                        <form action="{{ route('driver-management.terms.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative" onclick="document.getElementById('file-upload').click()">
                                <div class="space-y-1 text-center">
                                    <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-1">
                                            <span>Upload a file</span>
                                            <input id="file-upload" name="term_image" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg" onchange="document.getElementById('file-name').textContent = this.files[0].name; document.getElementById('submit-btn').classList.remove('opacity-50', 'cursor-not-allowed'); document.getElementById('submit-btn').disabled = false;">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                                    <p id="file-name" class="text-xs font-bold text-blue-600 mt-2 truncate"></p>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse gap-3">
                                <button type="submit" id="submit-btn" disabled class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm opacity-50 cursor-not-allowed transition-all">
                                    Upload Document
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-all" onclick="document.getElementById('uploadModal').classList.add('hidden')">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-[100] hidden bg-black bg-opacity-90 flex items-center justify-center p-4 transition-opacity" onclick="this.classList.add('hidden')">
    <button class="absolute top-6 right-6 text-white hover:text-gray-300 transition-colors p-2" onclick="document.getElementById('lightbox').classList.add('hidden')">
        <i data-lucide="x" class="w-8 h-8"></i>
    </button>
    <img id="lightbox-img" src="" alt="Zoomed Document" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
    }
</script>
@endpush
