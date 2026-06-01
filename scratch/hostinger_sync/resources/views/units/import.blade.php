@extends('layouts.app')

@section('title', 'Import Units - Euro System')
@section('page-heading', 'Import Units')
@section('page-subheading', 'Bulk import units from CSV or Excel file')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Import Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-blue-900 mb-3">📋 Import Instructions</h3>
        <div class="space-y-2 text-sm text-blue-800">
            <p>• <strong>CSV Format:</strong> plate_number, make, model, status</p>
            <p>• <strong>Excel Format:</strong> Same column order as CSV</p>
            <p>• <strong>File Size:</strong> Maximum 10MB</p>
            <p>• <strong>Required Columns:</strong> Plate Number, Make, Model</p>
            <p>• <strong>Optional:</strong> Status (defaults to 'active' if not provided)</p>
        </div>
    </div>

    <!-- Import Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('units.import.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <!-- File Upload -->
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                    📁 Select File to Import
                </label>
                <input type="file" 
                       name="file" 
                       id="file" 
                       accept=".csv,.xlsx,.xls"
                       required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-4">
                <button type="submit" 
                        class="flex-1 bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 flex items-center justify-center gap-2 font-medium">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                    Import Units
                </button>
                <a href="{{ route('units.index') }}" 
                   class="flex-1 bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 flex items-center justify-center gap-2 font-medium text-center">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    Back to Units
                </a>
            </div>
        </form>
    </div>

    <!-- Sample Format -->
    <div class="bg-gray-50 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-bold text-gray-900 mb-3">📝 Sample CSV Format</h3>
        <div class="bg-white border border-gray-200 rounded p-4">
            <code class="text-sm">
                plate_number,make,model,status<br>
                ABC-123,Toyota,Vios,active<br>
                DEF-456,Honda,City,maintenance<br>
                GHI-789,Nissan,Sentra,coding
            </code>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // File validation
    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileExt = file.name.split('.').pop().toLowerCase();
        const validExts = ['csv', 'xlsx', 'xls'];
        
        if (!validExts.includes(fileExt)) {
            e.target.value = '';
            alert('Please select a valid file format (CSV, XLSX, or XLS)');
        }
        
        if (file.size > 10 * 1024 * 1024) { // 10MB
            e.target.value = '';
            alert('File size must be less than 10MB');
        }
    });
</script>
@endpush
@endsection
