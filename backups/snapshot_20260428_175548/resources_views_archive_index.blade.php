@extends('layouts.app')

@section('title', 'Archive Management | Euro Taxi System')
@section('page-heading', 'Archive Management')
@section('page-subheading', 'View and restore archived records from various modules')

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Archive Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100">
            <nav class="flex -mb-px px-6 space-x-8" aria-label="Tabs" id="archive-tabs">
                <button onclick="switchTab('units')" class="tab-btn active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="units">
                    Units ({{ count($archivedUnits) }})
                </button>
                <button onclick="switchTab('drivers')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="drivers">
                    Drivers ({{ count($archivedDrivers) }})
                </button>
                <button onclick="switchTab('expenses')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="expenses">
                    Expenses ({{ count($archivedExpenses) }})
                </button>
                <button onclick="switchTab('maintenance')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="maintenance">
                    Maintenance ({{ count($archivedMaintenance) }})
                </button>
                <button onclick="switchTab('boundaries')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="boundaries">
                    Boundaries ({{ count($archivedBoundaries) }})
                </button>
                <button onclick="switchTab('staff')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="staff">
                    Staff ({{ count($archivedStaff) }})
                </button>
                <button onclick="switchTab('incidents')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="incidents">
                    Incidents ({{ count($archivedIncidents) }})
                </button>

            </nav>
        </div>

        <div class="p-6">
            <!-- Units Tab -->
            <div id="tab-units" class="tab-content">
                @include('archive.partials._units_table', ['items' => $archivedUnits])
            </div>

            <!-- Drivers Tab -->
            <div id="tab-drivers" class="tab-content hidden">
                @include('archive.partials._drivers_table', ['items' => $archivedDrivers])
            </div>

            <!-- Expenses Tab -->
            <div id="tab-expenses" class="tab-content hidden">
                @include('archive.partials._expenses_table', ['items' => $archivedExpenses])
            </div>

            <!-- Maintenance Tab -->
            <div id="tab-maintenance" class="tab-content hidden">
                @include('archive.partials._maintenance_table', ['items' => $archivedMaintenance])
            </div>

            <!-- Boundaries Tab -->
            <div id="tab-boundaries" class="tab-content hidden">
                @include('archive.partials._boundaries_table', ['items' => $archivedBoundaries])
            </div>

            <!-- Staff Tab -->
            <div id="tab-staff" class="tab-content hidden">
                @include('archive.partials._staff_table', ['items' => $archivedStaff])
            </div>

            <!-- Incidents Tab -->
            <div id="tab-incidents" class="tab-content hidden">
                @include('archive.partials._incidents_table', ['items' => $archivedIncidents])
            </div>


        </div>
    </div>
</div>

<script>
    function switchTab(tabId) {
        // Hide all tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Show selected tab content
        document.getElementById('tab-' + tabId).classList.remove('hidden');

        // Update tab button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
            activeBtn.classList.add('border-blue-500', 'text-blue-600');
        }
    }

    // Handle initial tab from URL query parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab) {
            switchTab(initialTab);
        }
    });
</script>
@endsection
