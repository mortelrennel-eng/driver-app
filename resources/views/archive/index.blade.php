@extends('layouts.app')

@section('title', 'Archive Management | Euro Taxi System')
@section('page-heading', 'Archive Management')
@section('page-subheading', 'View and restore archived records from various modules')

@push('styles')
<style>
    /* ── Folder UI Tabs ── */
    .folder-tabs-container {
        background-color: #f1f5f9;
        padding: 0.75rem 0.75rem 0 0.75rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        border-bottom: none;
    }

    .folder-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .folder-tab {
        padding: 0.6rem 1.25rem;
        background-color: #e2e8f0;
        color: #64748b;
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        font-size: 0.85rem;
        font-weight: 700;
        transition: all 0.2s;
        border: 1px solid transparent;
        border-bottom: none;
        margin-bottom: -1px;
        position: relative;
        z-index: 1;
    }

    .folder-tab.active {
        background-color: #ffffff;
        color: #2563eb;
        border-color: #e2e8f0;
        z-index: 2;
    }

    .folder-tab:hover:not(.active) {
        background-color: #cbd5e1;
        color: #334155;
    }
    
    .folder-content-area {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush

@section('content')
<div class="px-4 py-6 mx-auto max-w-[95rem] sm:px-6 lg:px-8">
    <!-- Archive Tabs -->
    <div class="folder-tabs-container">
        <nav class="folder-tabs" aria-label="Tabs" id="archive-tabs">
            <button onclick="switchTab('units')" class="folder-tab active" data-tab="units">
                Units ({{ count($archivedUnits) }})
            </button>
            <button onclick="switchTab('drivers')" class="folder-tab" data-tab="drivers">
                Drivers ({{ count($archivedDrivers) }})
            </button>
            <button onclick="switchTab('user_accounts')" class="folder-tab" data-tab="user_accounts">
                User Accounts ({{ count($archivedUserAccounts) }})
            </button>
            <button onclick="switchTab('expenses')" class="folder-tab" data-tab="expenses">
                Expenses ({{ count($archivedExpenses) }})
            </button>
            <button onclick="switchTab('maintenance')" class="folder-tab" data-tab="maintenance">
                Maintenance ({{ count($archivedMaintenance) }})
            </button>
            <button onclick="switchTab('boundaries')" class="folder-tab" data-tab="boundaries">
                Boundaries ({{ count($archivedBoundaries) }})
            </button>
            <button onclick="switchTab('staff')" class="folder-tab" data-tab="staff">
                Staff ({{ count($archivedStaff) }})
            </button>
            <button onclick="switchTab('incidents')" class="folder-tab" data-tab="incidents">
                Incidents ({{ count($archivedIncidents) }})
            </button>
            <button onclick="switchTab('accidents')" class="folder-tab" data-tab="accidents">
                Accidents/SOS ({{ count($archivedAccidents) }})
            </button>
            <button onclick="switchTab('pricing_rules')" class="folder-tab" data-tab="pricing_rules">
                Pricing Rules ({{ count($archivedPricingRules) }})
            </button>
            <button onclick="switchTab('suppliers')" class="folder-tab" data-tab="suppliers">
                Suppliers ({{ count($archivedSuppliers) }})
            </button>
            <button onclick="switchTab('spare_parts')" class="folder-tab" data-tab="spare_parts">
                Spare Parts ({{ count($archivedSpareParts) }})
            </button>
            <button onclick="switchTab('franchise_cases')" class="folder-tab" data-tab="franchise_cases">
                Franchise Cases ({{ count($archivedFranchiseCases) }})
            </button>
            <button onclick="switchTab('driver_terms')" class="folder-tab" data-tab="driver_terms">
                Driver Terms ({{ count($archivedDriverTerms) }})
            </button>
        </nav>
    </div>

    <div class="folder-content-area p-6 mb-8">
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

            <!-- Accidents Tab -->
            <div id="tab-accidents" class="tab-content hidden">
                @include('archive.partials._accidents_table', ['items' => $archivedAccidents])
            </div>

            <!-- Pricing Rules Tab -->
            <div id="tab-pricing_rules" class="tab-content hidden">
                @include('archive.partials._pricing_rules_table', ['items' => $archivedPricingRules])
            </div>

            <!-- Suppliers Tab -->
            <div id="tab-suppliers" class="tab-content hidden">
                @include('archive.partials._suppliers_table', ['items' => $archivedSuppliers])
            </div>

            <!-- Spare Parts Tab -->
            <div id="tab-spare_parts" class="tab-content hidden">
                @include('archive.partials._spare_parts_table', ['items' => $archivedSpareParts])
            </div>

            <!-- Franchise Cases Tab -->
            <div id="tab-franchise_cases" class="tab-content hidden">
                @include('archive.partials._franchise_cases_table', ['items' => $archivedFranchiseCases])
            </div>

            <!-- Driver Terms Tab -->
            <div id="tab-driver_terms" class="tab-content hidden">
                @include('archive.partials._driver_terms_table', ['items' => $archivedDriverTerms])
            </div>

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
        document.querySelectorAll('.folder-tab').forEach(btn => {
            btn.classList.remove('active');
        });

        const activeBtn = document.querySelector(`[data-tab="${tabId}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }

    // Lightbox Functionality
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
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
