@extends('layouts.app')

@section('title', 'Inventory Management - Euro System')
@section('page-heading', 'Inventory Management')
@section('page-subheading', 'Manage spare parts, stock levels, suppliers, and purchase history')

@section('content')
<div class="w-full space-y-6">
    <!-- Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Total Parts</p>
                <h3 class="text-3xl font-black text-gray-900">{{ number_format($totalParts) }}</h3>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-inner">
                <i data-lucide="package" class="w-7 h-7"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Total Stock Value</p>
                <h3 class="text-3xl font-black text-green-600">₱{{ number_format($totalStockValue, 2) }}</h3>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 shadow-inner">
                <i data-lucide="banknote" class="w-7 h-7"></i>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1.5">Out of Stock</p>
                <h3 class="text-3xl font-black {{ $outOfStock > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($outOfStock) }}</h3>
            </div>
            <div class="w-14 h-14 {{ $outOfStock > 0 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-400' }} rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="alert-triangle" class="w-7 h-7"></i>
            </div>
        </div>
    </div>

    <!-- Tabs & Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between p-5 gap-4">
        <div class="flex gap-2 w-full md:w-auto overflow-x-auto custom-scrollbar pb-2 md:pb-0">
            <button onclick="switchTab('active')" id="tab-active" class="inventory-tab px-5 py-2.5 rounded-xl text-sm font-bold bg-gray-900 text-white transition-all shadow-sm whitespace-nowrap">
                Active Parts
            </button>
            <button onclick="switchTab('history')" id="tab-history" class="inventory-tab px-5 py-2.5 rounded-xl text-sm font-bold bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all whitespace-nowrap">
                Purchase History
            </button>
            <button onclick="switchTab('archived')" id="tab-archived" class="inventory-tab px-5 py-2.5 rounded-xl text-sm font-bold bg-white text-red-600 hover:bg-red-50 hover:text-red-700 transition-all whitespace-nowrap">
                Archived
            </button>
        </div>
        
        <div class="flex gap-3 w-full md:w-auto">
            <button onclick="openSuppliersModal()" class="flex-1 md:flex-none px-5 py-2.5 bg-purple-50 text-purple-700 font-bold rounded-xl text-sm hover:bg-purple-100 transition-all flex items-center justify-center gap-2">
                <i data-lucide="book-user" class="w-4 h-4"></i> Suppliers
            </button>
            <button onclick="openPartMiniModal()" class="flex-1 md:flex-none px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-200 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Part
            </button>
        </div>
    </div>

    <!-- Content Sections -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative min-h-[500px]">
        <!-- Search bar (Only for Active and Archived) -->
        <div id="searchContainer" class="p-5 border-b border-gray-100 bg-gray-50/50">
            <div class="relative max-w-lg">
                <!-- Fake hidden inputs to trap aggressive Chrome autofill -->
                <input type="text" style="display:none" aria-hidden="true">
                <input type="password" style="display:none" aria-hidden="true">
                
                <i data-lucide="search" class="absolute left-4 top-3.5 w-5 h-5 text-gray-400"></i>
                <input type="text" role="presentation" autocomplete="off" data-lpignore="true" data-form-type="other" id="partsSearchInput" oninput="filterTables()" placeholder="Search by part name or supplier..." 
                    class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all shadow-sm font-medium">
            </div>
        </div>

        <!-- 1. Active Parts -->
        <div id="section-active" class="inventory-section">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Part Name</th>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                            <th class="px-8 py-4 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Price (₱)</th>
                            <th class="px-8 py-4 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest">In Stock</th>
                            <th class="px-8 py-4 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="activePartsTable" class="divide-y divide-gray-50/80">
                        <tr><td colspan="5" class="text-center py-12 text-gray-400"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-3"></i>Loading inventory...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Purchase History -->
        <div id="section-history" class="inventory-section hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                            <th class="px-8 py-4 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="historyTable" class="divide-y divide-gray-50/80">
                        <tr><td colspan="3" class="text-center py-12 text-gray-400"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-3"></i>Loading history...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Archived Parts -->
        <div id="section-archived" class="inventory-section hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Part Name</th>
                            <th class="px-8 py-4 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                            <th class="px-8 py-4 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="archivedTable" class="divide-y divide-gray-50/80">
                        <tr><td colspan="3" class="text-center py-12 text-gray-400"><i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto mb-3"></i>Loading archived parts...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->

<!-- 1. Add/Edit Part Modal -->
<div id="partMiniModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 overflow-hidden">
        <div class="flex justify-between items-start mb-5">
            <div class="flex items-center gap-3">
                <div id="miniModalIcon" class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h3 id="miniModalTitle" class="text-lg font-black text-gray-900 leading-tight">Add New Part</h3>
                    <p id="miniModalSubtitle" class="text-xs text-gray-500 font-medium">Create a new item in the spare parts catalog</p>
                </div>
            </div>
            <button onclick="closePartMiniModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="space-y-4">
            <input type="hidden" id="newPartId">
            <input type="hidden" id="newPartCurrentStock" value="0">
            
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Part Name <span class="text-red-500">*</span></label>
                <input type="text" id="newPartName" maxlength="35" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="e.g. Brake Pad">
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Price (₱) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" id="newPartPrice" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0.00">
                </div>
                <div>
                    <label id="lblQtyMode" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Initial Qty</label>
                    <input type="number" id="newPartQty" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors" placeholder="0">
                    <p id="qtyError" class="hidden text-xs text-red-500 mt-1 font-medium">Cannot reduce stock here. Only add.</p>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Supplier (Optional)</label>
                <select id="newPartSupplier" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors cursor-pointer">
                    <option value="">-- No Supplier --</option>
                    @foreach(\App\Models\Supplier::orderBy('name')->get() as $s)
                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="pt-2 flex gap-3">
                <button onclick="closePartMiniModal()" class="flex-1 py-3 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                <button onclick="saveNewPart()" class="flex-1 py-3 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md shadow-blue-200 transition-colors flex justify-center items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> <span id="txtSavePart">Save Part</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Suppliers Modal -->
<div id="suppliersModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[85vh] flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl font-black text-gray-900">Manage Suppliers</h3>
                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest mt-0.5">Directory of Parts Sources</p>
            </div>
            <button onclick="closeSuppliersModal()" class="p-2 hover:bg-gray-50 rounded-full transition text-gray-400">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="flex flex-col gap-3 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
            <input type="hidden" id="supplierId">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Supplier Name</label>
                <input type="text" id="supplierName" maxlength="35" placeholder="Enter supplier name..." 
                    class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Contact Person</label>
                    <input type="text" id="supplierContact" maxlength="25" placeholder="Full name" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Phone Number</label>
                    <input type="text" id="supplierPhone" maxlength="11" placeholder="09xxxxxxxxx" 
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white shadow-sm">
                </div>
            </div>
            <div class="flex gap-2 mt-1">
                <button id="btnSaveSupplier" onclick="saveSupplier()" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold transition flex items-center justify-center gap-2 shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i> <span id="txtSaveSupplier">Save Supplier</span>
                </button>
                <button id="btnCancelSupplierEdit" onclick="resetSupplierForm()" class="hidden px-4 py-2 bg-gray-200 text-gray-600 rounded-lg hover:bg-gray-300 text-sm font-bold transition">
                    Cancel
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            <table class="min-w-full divide-y divide-gray-100">
                <tbody id="suppliersTableBody" class="divide-y divide-gray-50">
                    <tr><td class="text-center py-4 text-gray-400 text-sm">Loading suppliers...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let activeParts = [];
    let archivedParts = [];
    let purchaseHistory = [];
    let suppliersList = [];
    let currentTab = 'active';

    document.addEventListener('DOMContentLoaded', () => {
        loadActiveParts();
        loadSuppliers();
        if(typeof lucide !== 'undefined') lucide.createIcons();

        // Phone Number format
        const sPhone = document.getElementById('supplierPhone');
        if (sPhone) {
            sPhone.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11);
            });
        }
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('appContentArea');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `alert-slide mb-4 p-4 rounded-lg border flex items-center gap-3 shadow-md transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
        }`;
        toast.innerHTML = `
            <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5 flex-shrink-0"></i>
            <div class="flex-1 font-bold text-sm tracking-tight">${message}</div>
            <button onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;
        container.prepend(toast);
        lucide.createIcons();
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.inventory-tab').forEach(el => {
            el.className = 'inventory-tab px-4 py-2 rounded-lg text-sm font-bold bg-white text-gray-600 hover:bg-gray-50 transition whitespace-nowrap';
        });
        document.querySelectorAll('.inventory-section').forEach(el => el.classList.add('hidden'));
        
        let activeClass = '';
        if (tab === 'active') activeClass = 'bg-gray-900 text-white';
        else if (tab === 'history') activeClass = 'bg-blue-600 text-white';
        else if (tab === 'archived') activeClass = 'bg-red-600 text-white';

        document.getElementById(`tab-${tab}`).className = `inventory-tab px-4 py-2 rounded-lg text-sm font-bold transition whitespace-nowrap ${activeClass}`;
        document.getElementById(`section-${tab}`).classList.remove('hidden');

        // Search container visibility
        document.getElementById('searchContainer').classList.toggle('hidden', tab === 'history');

        if (tab === 'history' && purchaseHistory.length === 0) loadHistory();
        if (tab === 'archived' && archivedParts.length === 0) loadArchivedParts();
    }

    function filterTables() {
        const query = document.getElementById('partsSearchInput').value.toLowerCase();
        
        if (currentTab === 'active') {
            const filtered = activeParts.filter(p => (p.name || '').toLowerCase().includes(query) || (p.supplier || '').toLowerCase().includes(query));
            renderActiveParts(filtered);
        } else if (currentTab === 'archived') {
            const filtered = archivedParts.filter(p => (p.name || '').toLowerCase().includes(query) || (p.supplier || '').toLowerCase().includes(query));
            renderArchivedParts(filtered);
        }
    }

    // --- Loading Data ---
    async function loadActiveParts() {
        try {
            const res = await fetch("{{ route('spare-parts.index') }}");
            const result = await res.json();
            if (result.success) {
                activeParts = result.data;
                renderActiveParts(activeParts);
            }
        } catch(e) { console.error(e); }
    }

    async function loadHistory() {
        try {
            const res = await fetch("{{ route('spare-parts.history') }}");
            const result = await res.json();
            if (result.success) {
                purchaseHistory = result.data;
                renderHistory(purchaseHistory);
            }
        } catch(e) { console.error(e); }
    }

    async function loadArchivedParts() {
        try {
            const res = await fetch("{{ route('spare-parts.archived') }}");
            const result = await res.json();
            if (result.success) {
                archivedParts = result.data;
                renderArchivedParts(archivedParts);
            }
        } catch(e) { console.error(e); }
    }

    async function loadSuppliers() {
        try {
            const res = await fetch("{{ route('suppliers.index') }}");
            const result = await res.json();
            if(result.success) {
                suppliersList = result.data;
                renderSuppliers(suppliersList);
            }
        } catch(e) { console.error(e); }
    }

    // --- Rendering Tables ---
    function renderActiveParts(data) {
        const tbody = document.getElementById('activePartsTable');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-16 text-gray-500 text-sm font-medium">No parts found.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(p => {
            const isOut = p.stock_quantity <= 0;
            const isLow = !isOut && p.stock_quantity <= 5;
            let badgeClass = 'bg-green-50 text-green-700';
            if (isOut) badgeClass = 'bg-red-50 text-red-600';
            else if (isLow) badgeClass = 'bg-yellow-50 text-yellow-600';

            return `
            <tr class="hover:bg-gray-50/80 transition-colors group">
                <td class="px-8 py-4">
                    <div class="text-sm font-black text-gray-900 tracking-tight">${p.name}</div>
                </td>
                <td class="px-8 py-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider ${p.supplier ? 'bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-500'}">
                        ${p.supplier || 'Unspecified'}
                    </span>
                </td>
                <td class="px-8 py-4 text-right">
                    <div class="text-sm font-bold text-gray-900">₱${parseFloat(p.price).toFixed(2)}</div>
                </td>
                <td class="px-8 py-4 text-center">
                    <span class="inline-flex px-3 py-1.5 rounded-lg text-xs font-black ${badgeClass}">
                        ${p.stock_quantity}
                    </span>
                </td>
                <td class="px-8 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="editPart(${p.id}, '${addslashes(p.name)}', ${p.price}, ${p.stock_quantity}, '${addslashes(p.supplier||'')}')" class="p-2 text-blue-600 hover:bg-blue-100 rounded-xl transition-all" title="Purchase / Edit Part">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </button>
                        <button onclick="archivePart(${p.id})" class="p-2 text-red-500 hover:bg-red-100 rounded-xl transition-all" title="Archive Part">
                            <i data-lucide="trash" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
            `;
        }).join('');
        lucide.createIcons();
    }

    function renderHistory(data) {
        const tbody = document.getElementById('historyTable');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-16 text-gray-500 text-sm font-medium">No purchase records found.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(ph => {
            const dateStr = new Date(ph.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            return `
            <tr class="hover:bg-gray-50/80 transition-colors">
                <td class="px-8 py-5 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-700">${dateStr}</div>
                </td>
                <td class="px-8 py-5">
                    <div class="text-sm font-black text-gray-800 tracking-tight">${ph.description}</div>
                    <div class="text-xs text-blue-500 font-bold uppercase mt-1 tracking-wider">${ph.category}</div>
                </td>
                <td class="px-8 py-5 text-right">
                    <div class="text-base font-black text-green-600">₱${parseFloat(ph.amount).toFixed(2)}</div>
                </td>
            </tr>
            `;
        }).join('');
    }

    function renderArchivedParts(data) {
        const tbody = document.getElementById('archivedTable');
        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="3" class="text-center py-16 text-gray-500 text-sm font-medium">No archived parts found.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(p => `
            <tr class="hover:bg-gray-50/80 transition-colors group">
                <td class="px-8 py-4 text-sm font-medium text-gray-600">${p.name}</td>
                <td class="px-8 py-4 text-xs font-bold text-gray-400 uppercase">${p.supplier || 'Unspecified'}</td>
                <td class="px-8 py-4 text-right flex justify-end gap-2">
                    <button onclick="restorePart(${p.id})" title="Restore Item" class="p-2 text-green-600 hover:bg-green-100 rounded-xl transition-all">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </button>
                    <button onclick="forceDeletePart(${p.id})" title="Delete Permanently" class="p-2 text-red-500 hover:bg-red-100 rounded-xl transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
    }

    // --- Actions ---
    function openPartMiniModal(isEdit = false) {
        const modal = document.getElementById('partMiniModal');
        modal.classList.remove('hidden');
        document.getElementById('qtyError').classList.add('hidden');
        
        if (!isEdit) {
            document.getElementById('newPartId').value = '';
            document.getElementById('newPartName').value = '';
            document.getElementById('newPartPrice').value = '';
            document.getElementById('newPartQty').value = '';
            document.getElementById('newPartSupplier').value = '';
            
            document.getElementById('miniModalTitle').innerText = 'Add New Part';
            document.getElementById('miniModalSubtitle').innerText = 'Create a new item in the spare parts catalog';
            document.getElementById('lblQtyMode').innerText = 'Initial Qty';
            document.getElementById('txtSavePart').innerText = 'Save Part';
            document.getElementById('newPartName').readOnly = false;
            
            const iconContainer = document.getElementById('miniModalIcon');
            iconContainer.className = 'w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center';
            iconContainer.innerHTML = '<i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>';
            lucide.createIcons();
        }
    }

    function closePartMiniModal() {
        document.getElementById('partMiniModal').classList.add('hidden');
    }

    function editPart(id, name, price, qty, supplier) {
        openPartMiniModal(true);
        document.getElementById('newPartId').value = id;
        document.getElementById('newPartCurrentStock').value = qty;
        document.getElementById('newPartName').value = name;
        document.getElementById('newPartPrice').value = price;
        document.getElementById('newPartQty').value = ''; 
        document.getElementById('newPartSupplier').value = supplier || '';

        document.getElementById('miniModalTitle').innerText = 'Purchase / Edit Part';
        document.getElementById('miniModalSubtitle').innerText = 'Add stock or update part details';
        document.getElementById('lblQtyMode').innerHTML = `Add Stock <span class="text-gray-400 font-normal ml-1">(Current: ${qty})</span>`;
        document.getElementById('txtSavePart').innerText = 'Save Changes';
        document.getElementById('newPartName').readOnly = true;

        const iconContainer = document.getElementById('miniModalIcon');
        iconContainer.className = 'w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center';
        iconContainer.innerHTML = '<i data-lucide="shopping-cart" class="w-5 h-5 text-blue-600"></i>';
        
        lucide.createIcons();
    }

    async function saveNewPart() {
        const id = document.getElementById('newPartId').value;
        const name = document.getElementById('newPartName').value;
        const price = document.getElementById('newPartPrice').value;
        const qty_to_add = parseInt(document.getElementById('newPartQty').value) || 0;
        const supplier = document.getElementById('newPartSupplier').value;

        if(!name || !price) {
            showToast('Part Name and Price are required.', 'error');
            return;
        }

        if (id && qty_to_add < 0) {
            document.getElementById('qtyError').classList.remove('hidden');
            return;
        }

        try {
            const res = await fetch("{{ route('spare-parts.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ id, name, price, qty_to_add, supplier })
            });
            const result = await res.json();
            if(result.success) {
                showToast(result.message, 'success');
                closePartMiniModal();
                loadActiveParts();
                if(qty_to_add > 0) {
                    loadHistory();
                    setTimeout(() => window.location.reload(), 1500); // Reload to update metric cards
                }
            } else {
                showToast(result.message || 'Something went wrong.', 'error');
            }
        } catch(e) { console.error(e); }
    }

    async function archivePart(id) {
        if(!confirm('Are you sure you want to archive this part?')) return;
        try {
            const res = await fetch(`{{ url('spare-parts') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const result = await res.json();
            if(result.success) {
                showToast('Part archived successfully', 'success');
                loadActiveParts();
                loadArchivedParts();
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch(e) { console.error(e); }
    }

    async function restorePart(id) {
        try {
            const res = await fetch(`{{ url('spare-parts/restore') }}/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                loadActiveParts();
                loadArchivedParts();
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch(e) { console.error(e); }
    }

    async function forceDeletePart(id) {
        if (!confirm('🛑 WARNING: This will permanently delete the part record. Proceed?')) return;
        try {
            const res = await fetch(`{{ url('spare-parts/permanent') }}/${id}`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ archive_password: 'bypass' }) // If you use auth modal, adapt this
            });
            const result = await res.json();
            if (result.success) {
                showToast(result.message, 'success');
                loadArchivedParts();
            } else {
                showToast(result.message, 'error');
            }
        } catch(e) { console.error(e); }
    }

    // --- Suppliers Logic ---
    function openSuppliersModal() {
        document.getElementById('suppliersModal').classList.remove('hidden');
        resetSupplierForm();
    }
    function closeSuppliersModal() {
        document.getElementById('suppliersModal').classList.add('hidden');
    }
    function resetSupplierForm() {
        document.getElementById('supplierId').value = '';
        document.getElementById('supplierName').value = '';
        document.getElementById('supplierContact').value = '';
        document.getElementById('supplierPhone').value = '';
        document.getElementById('btnSaveSupplier').classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
        document.getElementById('btnSaveSupplier').classList.add('bg-blue-600', 'hover:bg-blue-700');
        document.getElementById('txtSaveSupplier').innerText = 'Save Supplier';
        document.getElementById('btnCancelSupplierEdit').classList.add('hidden');
    }
    function renderSuppliers(data) {
        const tbody = document.getElementById('suppliersTableBody');
        if(data.length === 0) {
            tbody.innerHTML = `<tr><td class="text-center py-4 text-gray-400 text-sm">No suppliers added yet.</td></tr>`;
            return;
        }
        tbody.innerHTML = data.map(s => `
            <tr class="hover:bg-gray-50 transition group">
                <td class="py-3 pr-4">
                    <div class="text-sm font-black text-gray-800 tracking-tight">${s.name}</div>
                    ${(s.contact_person || s.phone_number) ? `<div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">${s.contact_person || '—'} · ${s.phone_number || '—'}</div>` : ''}
                </td>
                <td class="py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <button onclick="editSupplier(${s.id}, '${addslashes(s.name)}', '${addslashes(s.contact_person||'')}', '${s.phone_number||''}')" class="p-1.5 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                        <button onclick="deleteSupplier(${s.id})" class="p-1.5 text-red-300 hover:text-red-600 hover:bg-red-50 rounded transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        lucide.createIcons();
        
        // Update new part dropdown
        const select = document.getElementById('newPartSupplier');
        if(select) {
            const currentVal = select.value;
            select.innerHTML = '<option value="">-- No Supplier --</option>' + data.map(s => `<option value="${s.name}">${s.name}</option>`).join('');
            select.value = currentVal;
        }
    }
    async function saveSupplier() {
        const id = document.getElementById('supplierId').value;
        const name = document.getElementById('supplierName').value;
        const contact = document.getElementById('supplierContact').value;
        const phone = document.getElementById('supplierPhone').value;
        if(!name) return showToast('Supplier name is required.', 'error');
        
        try {
            const res = await fetch("{{ route('suppliers.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ id, name, contact_person: contact, phone_number: phone })
            });
            const result = await res.json();
            if(result.success) {
                showToast(result.message, 'success');
                resetSupplierForm();
                loadSuppliers();
            } else {
                showToast(result.message, 'error');
            }
        } catch(e) { console.error(e); }
    }
    function editSupplier(id, name, contact, phone) {
        document.getElementById('supplierId').value = id;
        document.getElementById('supplierName').value = name;
        document.getElementById('supplierContact').value = contact;
        document.getElementById('supplierPhone').value = phone;
        document.getElementById('txtSaveSupplier').innerText = 'Update Supplier';
        document.getElementById('btnCancelSupplierEdit').classList.remove('hidden');
    }
    async function deleteSupplier(id) {
        if(!confirm('Are you sure you want to delete this supplier?')) return;
        try {
            const res = await fetch(`{{ url('suppliers') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const result = await res.json();
            if(result.success) {
                showToast(result.message, 'success');
                loadSuppliers();
            } else {
                showToast(result.message, 'error');
            }
        } catch(e) { console.error(e); }
    }

    function addslashes(str) {
        return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }
</script>
@endpush
