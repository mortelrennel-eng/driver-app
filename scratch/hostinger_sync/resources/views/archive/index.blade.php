@extends('layouts.app')

@section('title', 'Archive Management | Euro Taxi System')
@section('page-heading', 'Archive Management')
@section('page-subheading', 'View and restore archived records from various modules')

@section('content')
<div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
    <!-- Archive Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 overflow-x-auto custom-scrollbar">
            <nav class="flex -mb-px px-6 space-x-8 min-w-max" aria-label="Tabs" id="archive-tabs">
                <button onclick="switchTab('units')" class="tab-btn active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="units">
                    Units ({{ count($archivedUnits) }})
                </button>
                <button onclick="switchTab('drivers')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="drivers">
                    Drivers ({{ count($archivedDrivers) }})
                </button>
                <button onclick="switchTab('user_accounts')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="user_accounts">
                    User Accounts ({{ count($archivedUserAccounts) }})
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
                <button onclick="switchTab('pricing_rules')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="pricing_rules">
                    Pricing Rules ({{ count($archivedPricingRules) }})
                </button>
                <button onclick="switchTab('suppliers')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="suppliers">
                    Suppliers ({{ count($archivedSuppliers) }})
                </button>
                <button onclick="switchTab('franchise_cases')" class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all" data-tab="franchise_cases">
                    Franchise Cases ({{ count($archivedFranchiseCases) }})
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

            <!-- User Accounts Tab -->
            <div id="tab-user_accounts" class="tab-content hidden">
                @include('archive.partials._user_accounts_table', ['items' => $archivedUserAccounts])
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

            <!-- Pricing Rules Tab -->
            <div id="tab-pricing_rules" class="tab-content hidden">
                @include('archive.partials._pricing_rules_table', ['items' => $archivedPricingRules])
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="tab-content hidden">
                @include('archive.partials._suppliers_table', ['items' => $archivedSuppliers])
            </div>

            <!-- Franchise Cases Tab -->
            <div id="tab-franchise_cases" class="tab-content hidden">
                @include('archive.partials._franchise_cases_table', ['items' => $archivedFranchiseCases])
            </div>

        </div>
    </div>
</div>

<script>
    // ─── Global Archive Force-Delete Handler ─────────────────────────────────────
    // Called by all archive partial "Delete Permanently" buttons.
    // Hooks into the globalArchiveSecurityModal defined in app.blade.php.
    async function archiveRestore(restoreUrl) {
        if (!confirm('Are you sure you want to restore this item?')) return;

        try {
            const response = await fetch(restoreUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const result = await response.json();

            if (response.ok && result.success !== false) {
                window.location.reload();
            } else {
                alert(result.message || 'Error occurred. Please try again.');
            }
        } catch (err) {
            alert('A network error occurred. Please try again.');
        }
    }

    async function archiveForceDelete(deleteUrl) {
        if (typeof window.promptArchiveDeletionPassword !== 'function') {
            alert('Security modal is not available. Please refresh the page.');
            return;
        }

        const password = await window.promptArchiveDeletionPassword();
        if (!password) return; // User cancelled

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ archive_password: password }),
            });

            const result = await response.json();

            if (response.ok && result.success !== false) {
                window.location.reload();
            } else {
                alert(result.message || 'Invalid password or error occurred. Please try again.');
            }
        } catch (err) {
            alert('A network error occurred. Please try again.');
        }
    }

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
    function initArchive() {
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab');
        if (initialTab) {
            switchTab(initialTab);
        }
        if(window.lucide) lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', initArchive);
    document.addEventListener('page:loaded', initArchive);
    initArchive();
</script>
@endsection
