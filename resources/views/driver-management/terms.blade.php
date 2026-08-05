@extends('layouts.app')

@push('styles')
<style>
@media print {
    /* Override h-screen overflow-hidden on the main app layout */
    html, body, #appLayout {
        height: auto !important;
        min-height: 100% !important;
        overflow: visible !important;
        display: block !important;
    }

    /* Hide layout elements from app.blade.php */
    #appSidebar, header, #sidebarBackdrop {
        display: none !important;
    }
    
    /* Reset main layout padding/margins */
    main, #appMainContent, #appContentArea {
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        overflow: visible !important;
        display: block !important;
    }
    
    /* Image full page container */
    .print-image-container {
        page-break-after: always;
        break-after: page;
        width: 100vw;
        height: 100vh; /* Exactly 1 page */
        display: flex;
        justify-content: center;
        align-items: center;
        page-break-inside: avoid;
    }

    .print-image-container:last-child {
        page-break-after: auto;
        break-after: auto;
    }
    
    .print-image-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
}
</style>
@endpush

@section('title', 'Driver Contract of Rent - Euro System')
@section('page-heading', 'Driver Contract of Rent')
@section('page-subheading', 'Official contract of rent and policy proofs for drivers')

@section('content')
<!-- Screen Content (Hidden on Print) -->
<div class="max-w-7xl mx-auto space-y-6 print:hidden">

    <!-- Header Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-blue-50 rounded-full mix-blend-multiply filter blur-3xl opacity-70 -translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10 flex-1">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i data-lucide="file-signature" class="w-5 h-5 text-blue-600"></i>
                </div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Contract of Rent Documents</h2>
            </div>
            <p class="text-gray-500 text-sm max-w-2xl">
                This section contains the official scanned Contract of Rent documents agreed upon by the drivers. 
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
                    <form action="{{ route('driver-management.terms.delete', $image) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this document?');">
                        @csrf
                        <button type="submit" class="text-[10px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                            <i data-lucide="archive" class="w-3 h-3"></i> Archive
                        </button>
                    </form>
                </div>
                <div class="p-6 flex justify-center items-center bg-gray-50 min-h-[500px] relative">
                    <img src="{{ asset('uploads/terms/' . $image) }}" alt="Terms Page {{ $index + 1 }}" 
                         class="w-full h-auto object-contain rounded-lg shadow-sm border border-gray-200 cursor-zoom-in hover:shadow-md transition-shadow select-none"
                         style="-webkit-user-drag: none;"
                         oncontextmenu="return false;" draggable="false"
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
            <div>
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <i data-lucide="upload-cloud" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Upload Contract of Rent</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-4">Upload a scanned copy or picture of the driver's contract of rent.</p>
                    </div>
                </div>
            </div>
            
            <form action="{{ route('driver-management.terms.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="drop-zone" class="mt-1 flex flex-col items-center justify-center px-6 py-4 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative min-h-[160px]" onclick="document.getElementById('file-upload').click()">
                    
                    <div id="upload-prompt" class="space-y-1 flex flex-col items-center justify-center w-full">
                        <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                        <p class="text-sm text-gray-600 text-center leading-normal">
                            <label for="file-upload" class="relative cursor-pointer font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 rounded-md">
                                <span>Upload a file</span>
                                <input id="file-upload" name="term_image" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg" onchange="handleTermsFileUpload(this)">
                            </label>
                            <span> or drag and drop</span>
                        </p>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                    </div>

                    <div id="preview-container" class="hidden flex flex-col items-center justify-center w-full">
                        <img id="preview-img" src="" alt="Preview" class="h-24 w-auto object-contain rounded-md shadow-sm border border-gray-200 cursor-zoom-in hover:opacity-90 transition-opacity select-none" style="-webkit-user-drag: none;" oncontextmenu="return false;" draggable="false" onclick="openLightbox(this.src); event.stopPropagation();">
                        <p id="file-name" class="text-xs font-bold text-blue-600 mt-2 truncate max-w-full px-2 text-center"></p>
                        <p class="text-xs text-gray-500 mt-1">Click image to enlarge • Click box to change</p>
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

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-[100] hidden bg-black bg-opacity-90 flex items-center justify-center p-4 transition-opacity" onclick="this.classList.add('hidden')">
    <button class="absolute top-6 right-6 text-white hover:text-gray-300 transition-colors p-2" onclick="document.getElementById('lightbox').classList.add('hidden')">
        <i data-lucide="x" class="w-8 h-8"></i>
    </button>
    <img id="lightbox-img" src="" alt="Zoomed Document" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl select-none" style="-webkit-user-drag: none;" oncontextmenu="return false;" draggable="false" onclick="event.stopPropagation()">
</div>

<!-- Print-Only Content (Visible only on Print) -->
<div class="hidden print:block w-full">
    @foreach($termsImages as $image)
        <div class="print-image-container">
            <img src="{{ asset('uploads/terms/' . $image) }}" alt="Contract Page">
        </div>
    @endforeach
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-upload');

        if (dropZone && fileInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dropZone.classList.add('bg-blue-50', 'border-blue-400');
                dropZone.classList.remove('bg-gray-50', 'border-gray-300');
            }

            function unhighlight(e) {
                dropZone.classList.remove('bg-blue-50', 'border-blue-400');
                dropZone.classList.add('bg-gray-50', 'border-gray-300');
            }

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                let dt = e.dataTransfer;
                let files = dt.files;

                if (files && files.length > 0) {
                    fileInput.files = files;
                    handleTermsFileUpload(fileInput);
                }
            }
        }
    });

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
    }

    function handleTermsFileUpload(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('upload-prompt').classList.add('hidden');
                document.getElementById('preview-container').classList.remove('hidden');
                document.getElementById('file-name').textContent = input.files[0].name;
                
                let submitBtn = document.getElementById('submit-btn');
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                submitBtn.disabled = false;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
