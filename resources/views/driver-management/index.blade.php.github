@extends('layouts.app')

@section('title', 'Driver Management - Euro System')
@section('page-heading', 'Driver Management')
@section('page-subheading', 'Centralized driver records, incentives, and performance analytics')

@section('content')

<style>
    @keyframes shortage-blink {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    @keyframes shortage-text-pulse {
        0% { color: #dc2626; }
        50% { color: #991b1b; }
        100% { color: #dc2626; }
    }
    .shortage-blink {
        animation: shortage-blink 1.5s infinite ease-in-out;
    }
    .shortage-text-blink {
        animation: shortage-blink 1.5s infinite ease-in-out, shortage-text-pulse 1.5s infinite ease-in-out;
        font-weight: 800 !important;
    }
    .modern-table-sep {
        border-collapse: separate;
        border-spacing: 0 0.6rem;
    }
    .modern-row {
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease-in-out;
    }
    .modern-row:hover {
        box-shadow: 0 10px 15px -3px rgba(234, 179, 8, 0.2), 0 4px 6px -2px rgba(234, 179, 8, 0.1);
        transform: translateY(-2px);
    }
    .modern-row td:first-child {
        border-top-left-radius: 0.75rem;
        border-bottom-left-radius: 0.75rem;
        border-left: 4px solid transparent;
    }
    .modern-row:hover td:first-child {
        border-left-color: #eab308;
    }
    .modern-row td:last-child {
        border-top-right-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }
    .modern-row.shortage-row {
        background-color: #fef2f2;
    }
    .modern-row.shortage-row:hover td:first-child {
        border-left-color: #ef4444;
    }
</style>

    {{-- Search and Filters --}}
    <div class="bg-white rounded-lg shadow p-2 mb-1">
        <form method="GET" action="{{ route('driver-management.index') }}" class="flex flex-col md:flex-row gap-2">
            <div class="md:w-48">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="arrow-up-z-a" class="h-4 w-4 text-gray-400"></i>
                    </div>
                    <select name="sort" onchange="this.form.submit()"
                        class="block w-full pl-9 pr-3 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none appearance-none">
                        <option value="alphabetical" {{ ($sort ?? '') === 'alphabetical' ? 'selected' : '' }}>A-Z (Name)</option>
                        <option value="newest" {{ ($sort ?? '') === 'newest' ? 'selected' : '' }}>Newest Joined</option>
                        <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest Joined</option>
                        <option value="status" {{ ($sort ?? '') === 'status' ? 'selected' : '' }}>Status (Active first)</option>
                    </select>
                </div>
            </div>
            <div class="flex-1">
                <div class="relative group">
                    <input type="text" name="search" id="tableSearchInput" value="{{ $search ?? '' }}"
                        class="block w-full pl-3 pr-10 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none"
                        placeholder="Search by driver name or license...">
                    <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-yellow-600 transition-colors">
                        <i data-lucide="search" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>

            <div class="md:w-48">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full px-3 py-1 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 focus:outline-none">
                    <option value="">All Status</option>
                    <option value="active" {{ ($status_filter ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ ($status_filter ?? '') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    <option value="no_unit" {{ ($status_filter ?? '') === 'no_unit' ? 'selected' : '' }}>Available (No Unit)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" onclick="openAddDriverModal()"
                    class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 text-xs font-semibold whitespace-nowrap">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Driver
                </button>
            </div>
        </form>
    </div>

    {{-- Driver List Container --}}
    <div id="driversTableContainer">
        @include('driver-management.partials._drivers_table')
    </div>

    {{-- Add/Edit Driver Modal --}}
    <div id="addDriverModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl h-[90vh] flex flex-col overflow-hidden">

            {{-- Modal Header (Dark Navy, matching Edit Unit) --}}
            <div class="bg-slate-800 p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="user" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white leading-tight" id="driverModalTitle">Add Driver</h3>
                            <p class="text-sm text-blue-100 leading-tight" id="driverModalSubtitle">Fill in the driver's information below</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeAddDriverModal()" class="text-white hover:text-gray-200 transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Form --}}
            <form id="driverForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="_method" id="driverFormMethod" value="POST">
                <input type="hidden" name="driver_id" id="editDriverId" value="">

                {{-- Scrollable Content --}}
                <div class="p-6 flex-1 overflow-y-auto space-y-8">

                    {{-- Section 1: Personal Information --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Personal Information</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">First Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="first_name" id="driverFirstName" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Juan">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="last_name" id="driverLastName" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Dela Cruz">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="contact_number" id="driverContact" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XX-XXX-XXXX">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Driver Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="activity" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <select name="is_active" id="editIsActive"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700">Address <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <textarea name="address" id="driverAddress" required rows="2"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        placeholder="Complete address..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: License & Employment --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i data-lucide="credit-card" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">License & Employment</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Number <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="license_number" id="driverLicense" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono uppercase"
                                        placeholder="e.g., TBD-123456">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">License Expiry <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="license_expiry" id="driverLicenseExpiry" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Hire Date <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="date" name="hire_date" id="driverHireDate" required value="{{ date('Y-m-d') }}"
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700 flex justify-between items-center">
                                    <span>Daily Boundary Target</span>
                                    <span id="unitDerivedLabel" class="text-[10px] text-gray-500 font-bold hidden"></span>
                                    <span id="codingBoundaryAlert" class="text-[10px] text-red-600 font-bold hidden"></span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-bold">₱</span>
                                    </div>
                                    <input type="number" name="daily_boundary_target" id="driverBoundaryTarget" step="0.01" readonly
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed focus:outline-none"
                                        placeholder="Auto-synced from Unit Management">
                                </div>
                                <p class="text-xs text-gray-400 italic">Automatically synchronized from Unit Management.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3: Emergency Contact --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Emergency Contact</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Name <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="text" name="emergency_contact" id="driverEmergencyContact" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., Maria Dela Cruz">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-gray-700">Contact Phone <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="phone-call" class="w-4 h-4 text-gray-400"></i>
                                    </div>
                                    <input type="tel" name="emergency_phone" id="driverEmergencyPhone" required
                                        class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="e.g., 09XX-XXX-XXXX">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>{{-- End Scrollable Content --}}

                {{-- Fixed Footer --}}
                <div class="p-4 border-t flex justify-between items-center gap-3 shadow-inner bg-gray-50 shrink-0">
                    <button type="button" id="archiveDriverButton" onclick="confirmArchiveDriver()"
                        class="hidden px-5 py-2 bg-orange-100 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-200 text-sm font-bold transition-all flex items-center gap-2">
                        <i data-lucide="archive" class="w-4 h-4"></i> Archive Driver
                    </button>
                    <div class="flex gap-3 ml-auto">
                        <button type="button" onclick="closeAddDriverModal()"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Save Driver
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Driver Details Modal with Tabs --}}
    <div id="driverDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center p-6 border-b">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="driverDetailsName">Driver Details</h3>
                    <p class="text-sm text-gray-500" id="driverDetailsSubtitle"></p>
                </div>
                <button onclick="closeDriverDetails()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="px-6 pt-4 border-b">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button type="button" class="driver-tab active border-yellow-500 text-yellow-600 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="basic">
                        Basic Info
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="license">
                        License & Documents
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="incentives">
                        Incentives
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="performance">
                        Performance
                    </button>
                    <button type="button" class="driver-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-3 border-b-2 text-sm font-medium" data-tab="insights">
                        Insights
                    </button>
                </nav>
            </div>

            {{-- Tab Panels --}}
            <div class="p-6 space-y-6">
                <div class="driver-tab-panel" data-tab-panel="basic">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Personal & Employment Details</h4>
                    <div id="basicInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="license">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">License Information</h4>
                    <div id="licenseInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                        <p class="text-gray-400">Loading...</p>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <h5 class="text-sm font-semibold text-gray-800 mb-2">Upload Driver Documents</h5>
                        <p class="text-xs text-gray-500 mb-3">Accepted file types: JPG, PNG, PDF. Uploads replace any existing document for the same type.</p>
                        <form id="driverDocumentsForm" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="driver_id" id="driverDocumentsDriverId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Front)</label>
                                    <input type="file" name="license_front" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Driver's License (Back)</label>
                                    <input type="file" name="license_back" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">NBI Clearance</label>
                                    <input type="file" name="nbi_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Barangay Clearance</label>
                                    <input type="file" name="barangay_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Medical Certificate</label>
                                    <input type="file" name="medical_certificate" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none">
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                                    Save Documents
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="incentives">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Rule-based Incentives</h4>
                    <p class="text-sm text-gray-600 mb-4">This section shows the breakdown of incentives based on rules like no damage, no absence, complete boundary payments, and no late return.</p>
                    <div id="incentivesContent" class="text-sm text-gray-700">
                        <p class="text-gray-400">Loading incentive data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="performance">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Performance Analytics</h4>
                    <div id="performanceContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading performance data...</p>
                    </div>
                </div>

                <div class="driver-tab-panel hidden" data-tab-panel="insights">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">System Insights & Recommendations</h4>
                    <div id="insightsContent" class="text-sm text-gray-700 space-y-2">
                        <p class="text-gray-400">Loading insights...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
window.boundaryRules = @json($boundary_rules ?? []);
function openAddDriverModal() {
    document.getElementById('driverModalTitle').textContent = 'Add Driver';
    document.getElementById('driverFormMethod').value = 'POST';
    document.getElementById('driverForm').action = '{{ route('driver-management.store') }}';
    document.getElementById('editDriverId').value = '';
    document.getElementById('driverFirstName').value = '';
    document.getElementById('driverLastName').value = '';

    document.getElementById('driverContact').value = '';
    document.getElementById('driverLicense').value = '';
    document.getElementById('driverLicenseExpiry').value = '';
    document.getElementById('driverHireDate').value = '{{ date('Y-m-d') }}';
    document.getElementById('driverAddress').value = '';
    document.getElementById('driverEmergencyContact').value = '';
    document.getElementById('driverEmergencyPhone').value = '';
    const targetInput = document.getElementById('driverBoundaryTarget');
    const codingAlert = document.getElementById('codingBoundaryAlert');
    targetInput.value = '0';
    if (document.getElementById('unitDerivedLabel')) {
        const derivedLabel = document.getElementById('unitDerivedLabel');
        derivedLabel.classList.add('hidden');
    }
    
    if (codingAlert) {
        codingAlert.classList.remove('hidden');
        codingAlert.classList.remove('text-red-600');
        codingAlert.classList.add('text-gray-500');
        codingAlert.textContent = '(Pending Dispatch)';
    }

    document.getElementById('editIsActive').value = '1';
    document.getElementById('archiveDriverButton').classList.add('hidden');
    document.getElementById('addDriverModal').classList.remove('hidden');
    lucide.createIcons();
}

function openEditDriverModal(id) {
    fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('driverModalTitle').textContent = 'Edit Driver';
        document.getElementById('driverFormMethod').value = 'PUT';
        document.getElementById('driverForm').action = '{{ url('driver-management') }}/' + id;
        document.getElementById('editDriverId').value = id;
        document.getElementById('driverFirstName').value = data.first_name || '';
        document.getElementById('driverLastName').value = data.last_name || '';

        document.getElementById('driverContact').value = data.contact_number || '';
        document.getElementById('driverLicense').value = data.license_number || '';
        document.getElementById('driverLicenseExpiry').value = data.license_expiry || '';
        document.getElementById('driverHireDate').value = data.hire_date || '{{ date('Y-m-d') }}';
        document.getElementById('driverAddress').value = data.address || '';
        document.getElementById('driverEmergencyContact').value = data.emergency_contact || '';
        document.getElementById('driverEmergencyPhone').value = data.emergency_phone || '';
        
        // Dynamic Boundary Automation (Sync with Controller Trait)
        const targetInput = document.getElementById('driverBoundaryTarget');
        const codingAlert = document.getElementById('codingBoundaryAlert');
        
        if (data.current_pricing) {
            targetInput.value = data.current_pricing.rate.toFixed(2);
            
            // Show inheritance label if assigned
            const derivedLabel = document.getElementById('unitDerivedLabel');
            if (data.assigned_unit) {
                if (derivedLabel) {
                    derivedLabel.textContent = `(Inherited from ${data.assigned_unit})`;
                    derivedLabel.classList.remove('hidden');
                }
            } else {
                if (derivedLabel) derivedLabel.classList.add('hidden');
            }

            if (data.current_pricing.label && data.current_pricing.type !== 'regular') {
                codingAlert.classList.remove('hidden');
                codingAlert.textContent = data.current_pricing.label;
                codingAlert.className = data.current_pricing.type === 'coding' ? 'text-[11px] text-red-600 font-bold' : 'text-[11px] text-blue-600 font-bold';
            } else {
                codingAlert.classList.add('hidden');
            }
        } else {
            targetInput.value = data.daily_boundary_target || '0.00';
            const derivedLabel = document.getElementById('unitDerivedLabel');
            if (derivedLabel) derivedLabel.classList.add('hidden');
            codingAlert.classList.add('hidden');
        }
        document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
        document.getElementById('archiveDriverButton').classList.remove('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    })
    .catch(() => {
        // Fallback: just show empty edit modal
        document.getElementById('driverModalTitle').textContent = 'Edit Driver';
        document.getElementById('driverFormMethod').value = 'PUT';
        document.getElementById('driverForm').action = '{{ url('driver-management') }}/' + id;
        document.getElementById('editDriverId').value = id;
        document.getElementById('archiveDriverButton').classList.remove('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    });
}

function closeAddDriverModal() {
    document.getElementById('addDriverModal').classList.add('hidden');
}

function confirmArchiveDriver() {
    const id = document.getElementById('editDriverId').value;
    const firstName = document.getElementById('driverFirstName').value || '';
    const lastName = document.getElementById('driverLastName').value || '';
    const name = (firstName + ' ' + lastName).trim() || 'this driver';
    archiveDriver(id, name);
}

function archiveDriver(id, name) {
    if (!id) return;
    if (confirm('Are you sure you want to archive ' + name + '? They will be moved to the Archive page.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url('driver-management') }}/' + id;
        form.innerHTML = '@csrf' +
                         '<input type="hidden" name="_method" value="DELETE">';
        document.body.appendChild(form);
        form.submit();
    }
}

function openDriverDetails(id) {
    const modal = document.getElementById('driverDetailsModal');
    modal.classList.remove('hidden');

    // Reset tab state
    document.querySelectorAll('.driver-tab').forEach(btn => {
        btn.classList.remove('border-yellow-500', 'text-yellow-600', 'active');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    document.querySelectorAll('.driver-tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });
    const firstTab = document.querySelector('.driver-tab[data-tab="basic"]');
    const firstPanel = document.querySelector('.driver-tab-panel[data-tab-panel="basic"]');
    if (firstTab && firstPanel) {
        firstTab.classList.add('border-yellow-500', 'text-yellow-600', 'active');
        firstTab.classList.remove('border-transparent', 'text-gray-500');
        firstPanel.classList.remove('hidden');
    }

    document.getElementById('driverDocumentsDriverId').value = id;
    document.getElementById('driverDocumentsForm').action = '{{ url('driver-management/upload-documents') }}/' + id;

    // Fetch basic details
    fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('driverDetailsName').textContent = data.full_name || 'Driver Details';

        document.getElementById('basicInfoContent').innerHTML = `
            <div>
                <p><span class="font-semibold">First Name:</span> ${data.first_name || ''}</p>
                <p><span class="font-semibold">Last Name:</span> ${data.last_name || ''}</p>

                <p><span class="font-semibold">Contact:</span> ${data.contact_number || 'N/A'}</p>
                <p><span class="font-semibold">Address:</span> ${data.address || 'N/A'}</p>
                <p><span class="font-semibold">Emergency Contact:</span> ${data.emergency_contact || 'N/A'}</p>
                <p><span class="font-semibold">Emergency Phone:</span> ${data.emergency_phone || 'N/A'}</p>
            </div>
            <div>
                <p><span class="font-semibold">Hire Date:</span> ${data.hire_date || 'N/A'}</p>
                <p><span class="font-semibold">Daily Boundary Target:</span> ₱${data.current_pricing ? data.current_pricing.rate.toFixed(2) : data.daily_boundary_target}</p>
                ${data.current_pricing && data.current_pricing.type !== 'regular' ? `<p class="text-[10px] text-blue-600 font-bold">${data.current_pricing.label}</p>` : ''}
                <p><span class="font-semibold">Status:</span> ${data.is_active ? 'Active' : 'Inactive'}</p>
                <div class="mt-4 pt-2 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Record Credit</p>
                    <p class="text-xs text-gray-600"><span class="font-medium text-gray-500">Input by:</span> ${data.creator_name || 'System'}</p>
                    <p class="text-xs text-gray-600"><span class="font-medium text-gray-500">Last Edit:</span> ${data.editor_name || 'System'}</p>
                </div>
            </div>
        `;

        document.getElementById('licenseInfoContent').innerHTML = `
            <div>
                <p><span class="font-semibold">License Number:</span> ${data.license_number || ''}</p>
                <p><span class="font-semibold">License Expiry:</span> ${data.license_expiry || ''}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">License status and reminders will be computed here (e.g., expiring soon).</p>
            </div>
        `;

        lucide.createIcons();
    })
    .catch(() => {
        document.getElementById('basicInfoContent').innerHTML = '<p class="text-red-500">Failed to load details.</p>';
    });

    lucide.createIcons();
}
</script>

@push('scripts')
<script>
    window.boundaryRules = @json($boundary_rules ?? []);
    function openAddDriverModal() {
        document.getElementById('driverModalTitle').textContent = 'Add Driver';
        document.getElementById('driverFormMethod').value = 'POST';
        document.getElementById('driverForm').action = '{{ route('driver-management.store') }}';
        document.getElementById('editDriverId').value = '';
        document.getElementById('driverFirstName').value = '';
        document.getElementById('driverLastName').value = '';

        document.getElementById('driverContact').value = '';
        document.getElementById('driverLicense').value = '';
        document.getElementById('driverLicenseExpiry').value = '';
        document.getElementById('driverHireDate').value = '{{ date('Y-m-d') }}';
        document.getElementById('driverAddress').value = '';
        document.getElementById('driverEmergencyContact').value = '';
        document.getElementById('driverEmergencyPhone').value = '';
        const targetInput = document.getElementById('driverBoundaryTarget');
        const codingAlert = document.getElementById('codingBoundaryAlert');
        
        targetInput.value = '';
        targetInput.placeholder = 'Please dispatch to appear boundary';
        if (codingAlert) {
            codingAlert.classList.remove('hidden');
            codingAlert.classList.remove('text-red-600');
            codingAlert.classList.add('text-gray-500');
            codingAlert.textContent = '(Pending Dispatch)';
        }

        document.getElementById('editIsActive').value = '1';
        document.getElementById('archiveDriverButton').classList.add('hidden');
        document.getElementById('addDriverModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function openEditDriverModal(id) {
        fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('driverModalTitle').textContent = 'Edit Driver';
            document.getElementById('driverFormMethod').value = 'PUT';
            document.getElementById('driverForm').action = '{{ url('driver-management') }}/' + id;
            document.getElementById('editDriverId').value = id;
            document.getElementById('driverFirstName').value = data.first_name || '';
            document.getElementById('driverLastName').value = data.last_name || '';

            document.getElementById('driverContact').value = data.contact_number || '';
            document.getElementById('driverLicense').value = data.license_number || '';
            document.getElementById('driverLicenseExpiry').value = data.license_expiry || '';
            document.getElementById('driverHireDate').value = data.hire_date || '{{ date('Y-m-d') }}';
            document.getElementById('driverAddress').value = data.address || '';
            document.getElementById('driverEmergencyContact').value = data.emergency_contact || '';
            document.getElementById('driverEmergencyPhone').value = data.emergency_phone || '';
            
            const targetInput = document.getElementById('driverBoundaryTarget');
            const codingAlert = document.getElementById('codingBoundaryAlert');
            
            if (data.current_pricing) {
                targetInput.value = data.current_pricing.rate.toFixed(2);
                targetInput.placeholder = '0.00';
                
                if (data.current_pricing.label && data.current_pricing.type !== 'regular') {
                    codingAlert.classList.remove('hidden');
                    codingAlert.textContent = data.current_pricing.label;
                    codingAlert.className = data.current_pricing.type === 'coding' ? 'text-[11px] text-red-600 font-bold' : 'text-[11px] text-blue-600 font-bold';
                } else {
                    codingAlert.classList.add('hidden');
                }
            } else {
                targetInput.value = data.daily_boundary_target || '';
                targetInput.placeholder = 'Enter boundary target...';
                codingAlert.classList.add('hidden');
            }
            document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
            document.getElementById('archiveDriverButton').classList.remove('hidden');
            document.getElementById('addDriverModal').classList.remove('hidden');
            lucide.createIcons();
        });
    }

    function closeAddDriverModal() {
        document.getElementById('addDriverModal').classList.add('hidden');
    }

    function confirmArchiveDriver() {
        const id = document.getElementById('editDriverId').value;
        const firstName = document.getElementById('driverFirstName').value || '';
        const lastName = document.getElementById('driverLastName').value || '';
        const name = (firstName + ' ' + lastName).trim() || 'this driver';
        archiveDriver(id, name);
    }

    function archiveDriver(id, name) {
        if (!id) return;
        if (confirm('Are you sure you want to archive ' + name + '? They will be moved to the Archive page.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('driver-management') }}/' + id;
            form.innerHTML = '@csrf' +
                            '<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openDriverDetails(id) {
        const modal = document.getElementById('driverDetailsModal');
        modal.classList.remove('hidden');

        document.querySelectorAll('.driver-tab').forEach(btn => {
            btn.classList.remove('border-yellow-500', 'text-yellow-600', 'active');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        document.querySelectorAll('.driver-tab-panel').forEach(panel => { panel.classList.add('hidden'); });
        
        const firstTab = document.querySelector('.driver-tab[data-tab="basic"]');
        const firstPanel = document.querySelector('.driver-tab-panel[data-tab-panel="basic"]');
        if (firstTab && firstPanel) {
            firstTab.classList.add('border-yellow-500', 'text-yellow-600', 'active');
            firstPanel.classList.remove('hidden');
        }

        document.getElementById('driverDocumentsDriverId').value = id;
        document.getElementById('driverDocumentsForm').action = '{{ url('driver-management/upload-documents') }}/' + id;

        fetch('{{ route('driver-management.index') }}/' + id + '?format=json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('driverDetailsName').textContent = data.full_name || 'Driver Details';
            document.getElementById('driverDetailsSubtitle').textContent = data.assigned_unit ? `Assigned to ${data.assigned_unit}` : 'Not currently assigned';

            document.getElementById('basicInfoContent').innerHTML = `
                <div>
                    <p><span class="font-semibold text-gray-500">First Name:</span> ${data.first_name || ''}</p>
                    <p><span class="font-semibold text-gray-500">Last Name:</span> ${data.last_name || ''}</p>

                    <p><span class="font-semibold text-gray-500">Contact:</span> ${data.contact_number || 'N/A'}</p>
                    <p><span class="font-semibold text-gray-500">Address:</span> ${data.address || 'N/A'}</p>
                    <p><span class="font-semibold text-gray-500">Emergency Contact:</span> ${data.emergency_contact || 'N/A'}</p>
                    <p><span class="font-semibold text-gray-500">Emergency Phone:</span> ${data.emergency_phone || 'N/A'}</p>
                </div>
                <div>
                    <p><span class="font-semibold text-gray-500">Hire Date:</span> ${data.hire_date || 'N/A'}</p>
                    <p><span class="font-semibold text-gray-500">Standard Rate:</span> ₱${data.assigned_boundary_rate ? parseFloat(data.assigned_boundary_rate).toLocaleString() : '0.00'}</p>
                    <p><span class="font-semibold text-gray-500">Active Target:</span> ₱${data.current_pricing ? data.current_pricing.rate.toFixed(2) : '0.00'}</p>
                    ${data.current_pricing && data.current_pricing.label ? `<p class="text-[10px] text-blue-600 font-bold">${data.current_pricing.label}</p>` : ''}
                    <p><span class="font-semibold text-gray-500">Status:</span> 
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${data.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                            ${data.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </p>
                </div>
            `;

            document.getElementById('licenseInfoContent').innerHTML = `
                <div>
                    <p><span class="font-semibold text-gray-500">License Number:</span> ${data.license_number || ''}</p>
                    <p><span class="font-semibold text-gray-500">License Expiry:</span> ${data.license_expiry || ''}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                    <p class="text-[11px] text-blue-700 font-medium">Auto-Status Detection</p>
                    <p class="text-xs text-blue-600 mt-1">Based on expiry date: No active issues detected.</p>
                </div>
            `;

            // ===================== INCENTIVES TAB =====================
            const incentiveRate = data.incentive_rate || 0;
            const rateColor = incentiveRate >= 80 ? 'text-green-600' : incentiveRate >= 50 ? 'text-yellow-600' : 'text-red-600';
            const rateBar  = incentiveRate >= 80 ? 'bg-green-500' : incentiveRate >= 50 ? 'bg-yellow-400' : 'bg-red-500';

            let incentiveRowsHtml = '';
            if (data.incentive_breakdown && data.incentive_breakdown.length > 0) {
                data.incentive_breakdown.forEach(b => {
                    const notes = (b.notes || '').toLowerCase();
                    let reason = '';
                    if (!b.has_incentive) {
                        if (notes.includes('vehicle damaged')) reason = '<span class="text-orange-600 font-bold">Vehicle Damage</span>';
                        else if (notes.includes('maintenance')) reason = '<span class="text-red-600 font-bold">Breakdown</span>';
                        else reason = '<span class="text-gray-500">Late Turn</span>';
                    }
                    const statusColors = {paid:'text-green-600',shortage:'text-red-600',excess:'text-blue-600'};
                    incentiveRowsHtml += `
                    <tr class="border-b border-gray-50 ${b.has_incentive ? '' : 'bg-red-50/40'}">
                        <td class="p-2">${new Date(b.date).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-2 font-bold">${b.plate_number||'—'}</td>
                        <td class="p-2">₱${parseFloat(b.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-2 font-bold ${statusColors[b.status]||'text-gray-600'}">${(b.status||'').toUpperCase()}</td>
                        <td class="p-2 text-center">${b.has_incentive ? '<span class="text-green-600 font-black">✓</span>' : '<span class="text-red-500 font-black">✗</span>'}</td>
                        <td class="p-2">${reason}</td>
                    </tr>`;
                });
            } else {
                incentiveRowsHtml = '<tr><td colspan="6" class="p-4 text-center text-gray-400">No shifts recorded this month.</td></tr>';
            }

            document.getElementById('incentivesContent').innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                        <p class="text-[10px] text-green-500 font-black uppercase tracking-widest mb-1">Monthly Incentive</p>
                        <p class="text-xl font-black text-green-700">₱${parseFloat(data.monthly_incentive||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        <p class="text-[10px] text-green-500 mt-0.5">5% of eligible collections</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                        <p class="text-[10px] text-blue-500 font-black uppercase tracking-widest mb-1">Shifts This Month</p>
                        <p class="text-xl font-black text-blue-700">${data.total_shifts_month||0}</p>
                        <p class="text-[10px] text-blue-500 mt-0.5">${data.incentive_earned_count||0} earned / ${data.incentive_missed_count||0} missed</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                        <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-1">Incentive Rate</p>
                        <p class="text-xl font-black ${rateColor}">${incentiveRate}%</p>
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1"><div class="${rateBar} h-1.5 rounded-full" style="width:${incentiveRate}%"></div></div>
                    </div>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                        <p class="text-[10px] text-red-500 font-black uppercase tracking-widest mb-1">Missed Reasons</p>
                        <p class="text-[11px] text-red-700 font-bold">Late Turn: ${data.late_turn_missed||0}</p>
                        <p class="text-[11px] text-orange-600 font-bold">Vehicle Damage: ${data.damage_missed||0}</p>
                        <p class="text-[11px] text-red-600 font-bold">Breakdown: ${data.breakdown_missed||0}</p>
                    </div>
                </div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Per-Shift Incentive Log (Last 15)</p>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 font-bold">
                            <tr>
                                <th class="p-2">Date</th><th class="p-2">Unit</th><th class="p-2">Actual</th>
                                <th class="p-2">Status</th><th class="p-2 text-center">Incentive</th><th class="p-2">Reason (if missed)</th>
                            </tr>
                        </thead>
                        <tbody>${incentiveRowsHtml}</tbody>
                    </table>
                </div>`;

            // ===================== PERFORMANCE TAB =====================
            let perfRowsHtml = '';
            if (data.recent_performance && data.recent_performance.length > 0) {
                data.recent_performance.forEach(log => {
                    const statusColors = {paid:'text-green-600',shortage:'text-red-600',excess:'text-blue-600'};
                    const shortage = parseFloat(log.shortage||0);
                    const excess   = parseFloat(log.excess||0);
                    perfRowsHtml += `
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="p-2">${new Date(log.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-2 font-bold">${log.plate_number||'N/A'}</td>
                        <td class="p-2">₱${parseFloat(log.boundary_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-2 font-bold">₱${parseFloat(log.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-2 font-bold ${statusColors[log.status]||''}">${(log.status||'').toUpperCase()}</td>
                        <td class="p-2">${shortage > 0 ? '<span class="text-red-600">-₱'+parseFloat(shortage).toLocaleString()+'</span>' : excess > 0 ? '<span class="text-blue-600">+₱'+parseFloat(excess).toLocaleString()+'</span>' : '<span class="text-green-600">—</span>'}</td>
                        <td class="p-2 text-center">${log.has_incentive ? '<span class="text-green-500 font-black">✓</span>' : '<span class="text-red-400 font-black">✗</span>'}</td>
                    </tr>`;
                });
            } else {
                perfRowsHtml = '<tr><td colspan="7" class="p-4 text-center text-gray-400">No performance records found.</td></tr>';
            }

            // Behavior incidents section
            let incidentRowsHtml = '';
            if (data.incidents && data.incidents.length > 0) {
                const sevColors = {critical:'bg-red-100 text-red-700',high:'bg-orange-100 text-orange-700',medium:'bg-yellow-100 text-yellow-700',low:'bg-blue-100 text-blue-700'};
                data.incidents.forEach(i => {
                    incidentRowsHtml += `
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="p-2">${new Date(i.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-2 font-bold">${i.plate_number||'—'}</td>
                        <td class="p-2"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">${(i.incident_type||'').replace('_',' ').toUpperCase()}</span></td>
                        <td class="p-2"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${sevColors[i.severity]||'bg-gray-100 text-gray-600'}">${(i.severity||'').toUpperCase()}</span></td>
                        <td class="p-2 text-[10px] text-gray-500 max-w-[180px] truncate" title="${i.description||''}">${i.description||''}</td>
                    </tr>`;
                });
            } else {
                incidentRowsHtml = '<tr><td colspan="5" class="p-4 text-center text-gray-400">No behavior incidents recorded.</td></tr>';
            }

            // Absences section
            let absenceRowsHtml = '';
            if (data.absentee_logs && data.absentee_logs.length > 0) {
                data.absentee_logs.forEach(a => {
                    absenceRowsHtml += `
                    <tr class="border-b border-gray-50 hover:bg-red-50/50">
                        <td class="p-2 text-red-600 font-bold">${new Date(a.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-2 text-gray-600"><span class="px-2 py-0.5 bg-gray-100 rounded text-xs">Covered by: <strong>${a.first_name||''} ${a.last_name||''}</strong></span></td>
                        <td class="p-2"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">ABSENT</span></td>
                    </tr>`;
                });
            } else {
                absenceRowsHtml = '<tr><td colspan="3" class="p-4 text-center text-gray-400">No unattended shifts (absences) on record.</td></tr>';
            }

            document.getElementById('performanceContent').innerHTML = `
                <div class="flex gap-3 mb-4">
                    <div class="flex-1 bg-gray-50 rounded-xl p-3 border border-gray-100 text-center">
                        <p class="text-[10px] text-gray-400 uppercase font-black tracking-wider mb-1">Performance Rating</p>
                        <p class="text-lg font-black text-yellow-600">${data.performance_rating||'—'}</p>
                    </div>
                    <div class="flex-1 bg-red-50 rounded-xl p-3 border border-red-100 text-center">
                        <p class="text-[10px] text-red-400 uppercase font-black tracking-wider mb-1">Incidents (30 days)</p>
                        <p class="text-lg font-black text-red-600">${data.total_incidents_30d||0}</p>
                    </div>
                    <div class="flex-1 bg-orange-50 rounded-xl p-3 border border-orange-100 text-center">
                        <p class="text-[10px] text-orange-400 uppercase font-black tracking-wider mb-1">High Severity</p>
                        <p class="text-lg font-black text-orange-600">${data.high_severity_incidents||0}</p>
                    </div>
                </div>
                
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Unattended Shifts / Absences (Last 10)</p>
                <div class="overflow-x-auto rounded-xl border border-gray-100 mb-5">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 font-bold">
                            <tr><th class="p-2">Expected Date</th><th class="p-2">Actual Driver</th><th class="p-2">Status</th></tr>
                        </thead>
                        <tbody>${absenceRowsHtml}</tbody>
                    </table>
                </div>

                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Boundary History (Last 10)</p>
                <div class="overflow-x-auto rounded-xl border border-gray-100 mb-5">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 font-bold">
                            <tr><th class="p-2">Date</th><th class="p-2">Unit</th><th class="p-2">Target</th><th class="p-2">Actual</th><th class="p-2">Status</th><th class="p-2">Diff</th><th class="p-2 text-center">Incentive</th></tr>
                        </thead>
                        <tbody>${perfRowsHtml}</tbody>
                    </table>
                </div>

                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Behavior Incidents (Last 10)</p>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 text-gray-500 font-bold">
                            <tr><th class="p-2">Date</th><th class="p-2">Unit</th><th class="p-2">Type</th><th class="p-2">Severity</th><th class="p-2">Description</th></tr>
                        </thead>
                        <tbody>${incidentRowsHtml}</tbody>
                    </table>
                </div>`;

            // ===================== INSIGHTS TAB =====================
            const score = Math.max(0, Math.min(100,
                (data.incentive_rate||0) * 0.5
                + Math.max(0, 100 - (data.total_incidents_30d||0) * 10) * 0.3
                + (data.high_severity_incidents === 0 ? 20 : 0)
            ));
            const scoreColor = score >= 80 ? 'text-green-600' : score >= 50 ? 'text-yellow-600' : 'text-red-600';
            const scoreBar   = score >= 80 ? 'bg-green-500' : score >= 50 ? 'bg-yellow-400' : 'bg-red-500';

            const eligStatus = data.is_eligible && data.is_first_week 
                ? '<div class="bg-green-100 border border-green-300 text-green-800 p-4 rounded-xl mb-4 text-center"><h3 class="text-xl font-black uppercase mb-1">🎉 GRAND INCENTIVE SECURED</h3><p class="text-sm font-bold">Driver is eligible for the 1st Week Reward.</p></div>'
                : data.is_eligible && !data.is_first_week
                ? '<div class="bg-blue-100 border border-blue-300 text-blue-800 p-4 rounded-xl mb-4 text-center"><h3 class="text-lg font-black uppercase mb-1">✅ On Track for Grand Incentive</h3><p class="text-sm font-bold">Driver has 0 violations. Awaiting 1st week of the month.</p></div>'
                : '<div class="bg-red-100 border border-red-300 text-red-800 p-4 rounded-xl mb-4 text-center"><h3 class="text-lg font-black uppercase mb-1">❌ Not Eligible for Grand Incentive</h3><p class="text-sm font-bold">Driver has violations in the evaluation period.</p></div>';

            const reqList = [
                { passed: (data.violations_absences||0) === 0, text: 'No unattended shifts (Zero Absences)' },
                { passed: data.violations_no_incentive === 0, text: 'No skipped / late boundary returns' },
                { passed: (!data.damage_missed && data.damage_missed === 0) && data.violations_incidents === 0, text: 'Zero vehicle damage incidents' },
                { passed: (!data.breakdown_missed && data.breakdown_missed === 0), text: 'Zero breakdown incidents' },
                { passed: data.violations_incidents === 0, text: 'Zero behavioral / traffic violations' }
            ];

            const reqsHtml = reqList.map(r => `
                <div class="flex items-center gap-3 py-2 border-b border-gray-100 last:border-0">
                    <span class="text-lg">${r.passed ? '<i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>' : '<i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>'}</span>
                    <span class="text-sm font-bold ${r.passed ? 'text-gray-700' : 'text-red-600 line-through'}">${r.text}</span>
                </div>
            `).join('');

            const blocksHtml = data.blocking_violations && data.blocking_violations.length > 0 
                ? '<div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-100"><p class="text-[10px] font-black text-red-600 uppercase tracking-widest mb-2">Blocking Violations Found</p><ul class="list-disc pl-4 text-xs text-red-800 font-bold space-y-1">' + data.blocking_violations.map(b => `<li>${b}</li>`).join('') + '</ul></div>'
                : '<div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-100"><p class="text-xs font-black text-green-700 uppercase tracking-widest text-center">No blocking violations</p></div>';

            document.getElementById('insightsContent').innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Grand Incentive Package (1st Week)</p>
                        ${eligStatus}
                        <div class="bg-gray-50 border border-gray-200 rounded-xl overflow-hidden">
                            <div class="bg-yellow-500 p-3 text-center">
                                <p class="text-white font-black text-lg uppercase tracking-tight shadow-sm">Reward Package</p>
                            </div>
                            <div class="p-4 flex gap-4 justify-center items-center">
                                <div class="text-center"><span class="block text-3xl mb-1">🎫</span><span class="text-[10px] font-black uppercase">Free<br>Coding</span></div>
                                <div class="w-px h-10 bg-gray-200"></div>
                                <div class="text-center"><span class="block text-3xl mb-1">🍚</span><span class="text-[10px] font-black uppercase">25kg<br>Rice</span></div>
                                <div class="w-px h-10 bg-gray-200"></div>
                                <div class="text-center"><span class="block text-3xl mb-1">💵</span><span class="text-[10px] font-black uppercase">₱500<br>Cash</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Eligibility Criteria</p>
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-black uppercase tracking-wider">${data.is_dual_driver ? '2 Months (Dual Driver)' : '1 Month (Solo Driver)'} Lookback</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4 leading-relaxed tracking-wide">Driver is evaluated strictly against the last <strong class="text-gray-800">${data.lookback_days} days</strong>. Must have zero violations to claim.</p>
                        
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            ${reqsHtml}
                        </div>
                        ${blocksHtml}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white border border-gray-100 rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Overall Core Score</p>
                            <p class="text-[10px] text-gray-400">Based on incentive rate and total incidents.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-black ${scoreColor}">${Math.round(score)}<span class="text-base font-medium text-gray-400">/100</span></p>
                        </div>
                    </div>
                </div>
            `;

            lucide.createIcons();
        });
    }

    function closeDriverDetails() {
        document.getElementById('driverDetailsModal').classList.add('hidden');
    }

    document.querySelectorAll('.driver-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.driver-tab').forEach(t => {
                t.classList.remove('border-yellow-500', 'text-yellow-600', 'active');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            document.querySelectorAll('.driver-tab-panel').forEach(p => p.classList.add('hidden'));
            tab.classList.add('border-yellow-500', 'text-yellow-600', 'active');
            const panel = document.querySelector(`.driver-tab-panel[data-tab-panel="${tab.dataset.tab}"]`);
            if (panel) panel.classList.remove('hidden');
        });
    });

    let searchTimer;
    const searchInput = document.getElementById('tableSearchInput');
    const statusFilter = document.querySelector('select[name="status"]');
    const sortFilter = document.querySelector('select[name="sort"]');
    const tableContainer = document.getElementById('driversTableContainer');

    function performSearch(page = 1) {
        const query = searchInput.value;
        const status = statusFilter.value;
        const sort = sortFilter.value;

        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        fetch(`{{ route('driver-management.index') }}?search=${encodeURIComponent(query)}&status=${status}&sort=${sort}&page=${page}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => performSearch(1), 300);
        });
    }
    if (statusFilter) statusFilter.addEventListener('change', () => performSearch(1));
    if (sortFilter) sortFilter.addEventListener('change', () => performSearch(1));

    window.changePage = function(page) {
        performSearch(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
</script>
@endpush