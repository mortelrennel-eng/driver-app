
@include('partials._driver_details_scripts')
// ─── Global Scoping & Initialization ───
window.switchTab = function(name) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name)?.classList.remove('hidden');
    document.getElementById('tab-btn-' + name)?.classList.add('active');
    if(window.lucide) lucide.createIcons();
};

// ─── Incident Classification Management ────────────────────────────────────
 window.openClassificationSettings = function() {
     const modal = document.getElementById('classificationSettingsModal');
     if (!modal) return;
     modal.style.display = 'flex';
     if(window.lucide) lucide.createIcons();
 };

 window.closeClassificationSettings = function() {
     const modal = document.getElementById('classificationSettingsModal');
     if (!modal) return;
     modal.style.display = 'none';
 };

 window.editClassification = function(id, name, severity) {
     document.getElementById('quickClsId').value = id;
     document.getElementById('quickClsName').value = name;
     document.getElementById('quickClsSeverity').value = severity;
     document.getElementById('quickClsTitle').textContent = 'Edit Classification';

     // Fetch full metadata for this classification via XHR
     fetch(`/api/incidents/classification/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
         .then(r => r.json()).then(data => {
             if (data && data.behavior_mode) {
                 document.getElementById('quickClsMode').value = data.behavior_mode;
                 toggleClsModeFields();
                 if (data.sub_options && Array.isArray(data.sub_options)) {
                     document.getElementById('quickClsSubOptions').value = data.sub_options.join('\n');
                 }
                 document.getElementById('quickClsAutoBan').checked = !!data.auto_ban_trigger;
                 toggleBanValueField();
                 document.getElementById('quickClsBanValue').value = data.ban_trigger_value || '';
             }
         }).catch(() => {});
 };

 window.resetClsForm = function() {
     document.getElementById('quickClsId').value = '';
     document.getElementById('quickClsName').value = '';
     document.getElementById('quickClsTitle').textContent = 'Add New Classification';
     document.getElementById('quickClsMode').value = 'narrative';
     document.getElementById('quickClsSubOptions').value = '';
     document.getElementById('quickClsAutoBan').checked = false;
     document.getElementById('quickClsBanValue').value = '';
     toggleClsModeFields();
 };

 window.toggleClsModeFields = function() {
     const mode = document.getElementById('quickClsMode').value;
     const subRow = document.getElementById('clsSubOptionsRow');
     const banRow = document.getElementById('clsBanRow');
     if (['complaint','traffic'].includes(mode)) {
         subRow.classList.remove('hidden');
         banRow.classList.toggle('hidden', mode !== 'complaint');
     } else {
         subRow.classList.add('hidden');
         banRow.classList.add('hidden');
     }
 };

 window.toggleBanValueField = function() {
     const checked = document.getElementById('quickClsAutoBan').checked;
     document.getElementById('clsBanValueRow').classList.toggle('hidden', !checked);
 };

 window.saveQuickClassification = async function() {
     const id = document.getElementById('quickClsId').value;
     const name = document.getElementById('quickClsName').value.trim();
     const severity = document.getElementById('quickClsSeverity').value;
     const mode = document.getElementById('quickClsMode').value;
     const subOptsRaw = document.getElementById('quickClsSubOptions').value;
     const autoBan = document.getElementById('quickClsAutoBan').checked;
     const banValue = document.getElementById('quickClsBanValue').value.trim();

     if (!name) return alert('Please enter a classification name.');

     const subOptions = subOptsRaw ? subOptsRaw.split('\n').map(s => s.trim()).filter(Boolean) : [];
     const url = id ? `/super-admin/incident-classifications/${id}/update` : '/super-admin/incident-classifications';

     try {
         const res = await fetch(url, {
             method: 'POST',
             headers: { 
                 'Content-Type': 'application/json', 
                 'Accept': 'application/json',
                 'X-CSRF-TOKEN': '{{ csrf_token() }}' 
             },
             body: JSON.stringify({
                 name, default_severity: severity, color: 'gray', icon: 'alert-circle',
                 behavior_mode: mode, sub_options: subOptions,
                 auto_ban_trigger: autoBan, ban_trigger_value: banValue || null
             })
         });
         const result = await res.json();
         if (res.ok && result.success) {
             location.reload();
         } else {
             const errorMsg = result.message || result.error || 'Validation Failed: Please check your inputs.';
             alert(errorMsg);
         }
     } catch(e) { 
         console.error(e);
         alert('Error saving classification. Check the console for details.'); 
     }
 };

 window.archiveClassification = async function(id) {
     if (!confirm('Archive this classification?')) return;
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}/archive`, {
             method: 'POST',
             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
         });
         const data = await res.json();
         if (data.success) location.reload();
     } catch (e) { alert('Error archiving.'); }
 };

 window.restoreClassification = async function(id) {
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}/restore`, {
             method: 'POST',
             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
         });
         const data = await res.json();
         if (data.success) location.reload();
     } catch (e) { alert('Error restoring.'); }
 };

 window.deleteClassification = async function(id) {
     if (!confirm('Permanently delete this?')) return;
     try {
         const res = await fetch(`/super-admin/incident-classifications/${id}`, {
             method: 'DELETE',
             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
         });
         const data = await res.json();
         if (data.success) location.reload();
     } catch (e) { alert('Error deleting.'); }
 };

 window.switchClsList = function(type) {
     document.querySelectorAll('.cls-list-content').forEach(l => l.classList.add('hidden'));
     document.querySelectorAll('.cls-tab-btn').forEach(b => {
         b.classList.remove('active', 'text-gray-900');
         b.classList.add('text-gray-400');
     });
     
     document.getElementById('clsList-' + type).classList.remove('hidden');
     const btn = document.getElementById('clsTabBtn-' + type);
     btn.classList.add('active', 'text-gray-900');
     btn.classList.remove('text-gray-400');
 };

window.openIncidentModal = function() {
    const modal = document.getElementById('incidentModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if(window.lucide) lucide.createIcons();
    
    // Always re-init to ensure fresh state
    initializeSearchDropdowns();
    initPartSearch();
};

window.closeIncidentModal = function() {
    const modal = document.getElementById('incidentModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
};

window.openQuickAddPart = function() {
    const modal = document.getElementById('quickAddPartModal');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    if(window.lucide) lucide.createIcons();
};


window.closeQuickAddPart = function() {
    const modal = document.getElementById('quickAddPartModal');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('quickPartName').value = '';
    document.getElementById('quickPartPrice').value = '';
};

// ─── Constants & State ───
let partsCatalog = @json($spare_parts ?? []);
let incidentPartsCart = [];
let incidentServices = [];
let partyIndex = 0;
let classificationsMap = @json($classifications->pluck('default_severity', 'name'));

// Full classification metadata for the smart modal
let classificationsMeta = {};
@foreach($classifications as $c)
classificationsMeta["{{ $c->name }}"] = {
    mode: "{{ $c->behavior_mode ?? 'narrative' }}",
    subOptions: @json($c->sub_options ?? $c->getDefaultSubOptions($c->behavior_mode ?? 'narrative')),
    autoBan: {{ $c->auto_ban_trigger ? 'true' : 'false' }},
    banValue: "{{ addslashes($c->ban_trigger_value ?? '') }}"
};
@endforeach

window.handleTypeChange = function(val) {
    // 1. Auto-set severity
    const severitySelect = document.getElementById('severitySelect') || document.getElementById('edit_severity');
    if (severitySelect && classificationsMap[val]) severitySelect.value = classificationsMap[val];

    const meta = classificationsMeta[val] || { mode: 'narrative', subOptions: [], autoBan: false, banValue: '' };
    const mode = meta.mode;

    // 2. Hide all mode-specific sections
    ['complaint','traffic','damage'].forEach(s => {
        const el = document.getElementById('section-' + s);
        if (el) el.classList.add('hidden');
    });

    // 3. Clear any previous sub_classification inputs
    const subInput = document.getElementById('subClassificationInput');
    const trafInput = document.getElementById('trafficSubClassificationInput');
    if (subInput) subInput.value = '';
    if (trafInput) trafInput.value = '';

    // 4. Show relevant section
    if (mode === 'complaint') {
        const sec = document.getElementById('section-complaint');
        if (sec) sec.classList.remove('hidden');
        _renderSubOptions('subOptionsContainer', meta.subOptions, 'subClassificationInput', meta.autoBan, meta.banValue, 'blue');
        _updateBanWarning(meta.autoBan, meta.banValue, '');
    } else if (mode === 'traffic') {
        const sec = document.getElementById('section-traffic');
        if (sec) sec.classList.remove('hidden');
        _renderSubOptions('trafficSubOptionsContainer', meta.subOptions, 'trafficSubClassificationInput', false, '', 'orange');
    } else if (mode === 'damage') {
        const sec = document.getElementById('section-damage');
        if (sec) sec.classList.remove('hidden');
    }

    // 5. Update narrative label hint
    const hint = document.getElementById('narrativeModeLabel');
    if (hint) {
        const labels = { complaint:'(Describe the passenger complaint)', traffic:'(Describe the traffic violation)', damage:'(Describe the accident/damage)', narrative:'(Describe the incident)' };
        hint.textContent = labels[mode] || '(Describe the incident)';
    }

    if (window.lucide) lucide.createIcons();
    window._checkAutoBanState();
};

// Render pill buttons for sub-options
function _renderSubOptions(containerId, options, inputId, autoBan, banValue, color) {
    const container = document.getElementById(containerId);
    const input = document.getElementById(inputId);
    if (!container || !input) return;
    if (!options || options.length === 0) { container.innerHTML = ''; return; }

    const colors = {
        blue: { base: 'bg-white border-blue-200 text-blue-700 hover:bg-blue-600 hover:text-white hover:border-blue-600', active: 'bg-blue-600 border-blue-600 text-white' },
        orange: { base: 'bg-white border-orange-200 text-orange-700 hover:bg-orange-500 hover:text-white hover:border-orange-500', active: 'bg-orange-500 border-orange-500 text-white' }
    };
    const c = colors[color] || colors.blue;

    container.innerHTML = options.map(opt => `
        <button type="button" data-value="${opt}"
            class="sub-option-btn px-3 py-2.5 border rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all ${c.base}"
            onclick="window._selectSubOption(this, '${inputId}', ${autoBan ? `'${banValue}'` : 'null'}, '${color}')">
            ${opt}
        </button>
    `).join('');
}

window._selectSubOption = function(btn, inputId, banValue, color) {
    const colors = {
        blue: 'bg-blue-600 border-blue-600 text-white',
        orange: 'bg-orange-500 border-orange-500 text-white'
    };
    // Deselect siblings
    btn.closest('div').querySelectorAll('.sub-option-btn').forEach(b => {
        b.className = b.className.replace(/bg-(blue|orange)-\d+\s+border-(blue|orange)-\d+\s+text-white/g, '').trim();
    });
    btn.classList.add(...(colors[color] || colors.blue).split(' '));

    const val = btn.dataset.value;
    const input = document.getElementById(inputId);
    if (input) input.value = val;

    window._checkAutoBanState();
};

window._checkAutoBanState = function() {
    const typeVal = document.getElementById('incidentTypeSelect').value;
    const sevVal = document.getElementById('severitySelect').value;
    const subInput = document.getElementById('subClassificationInput').value;
    
    const meta = classificationsMeta[typeVal] || { autoBan: false, banValue: '' };
    const warning = document.getElementById('autoBanWarning');
    const label = document.getElementById('banTriggerLabel');
    if (!warning) return;

    let shouldBan = false;
    let reason = '';

    if (sevVal === 'critical') {
        shouldBan = true;
        reason = "Critical severity triggers an automatic driver ban.";
    } else if (meta.autoBan && meta.banValue && subInput === meta.banValue) {
        shouldBan = true;
        reason = `Selecting "${meta.banValue}" triggers an automatic driver ban.`;
    }

    if (shouldBan) {
        warning.classList.remove('hidden');
        if (label) label.textContent = reason;
    } else {
        warning.classList.add('hidden');
    }
};

// ─── Searchable Dropdowns (Unit/Driver) ───
function initializeSearchDropdowns() {
    const searchConfig = [
        { display: 'unitSearchDisplay', hidden: 'incidentUnitId', dropdown: 'unitSearchDropdown', options: 'unit-search-option' },
        { display: 'driverSearchDisplay', hidden: 'incidentDriverId', dropdown: 'driverSearchDropdown', options: 'driver-search-option' }
    ];

    searchConfig.forEach(({ display, hidden, dropdown, options }) => {
        const dInput = document.getElementById(display);
        const hInput = document.getElementById(hidden);
        const drop = document.getElementById(dropdown);
        if (!dInput || !drop) return;

        drop.onmousedown = (e) => {
            const opt = e.target.closest('.' + options);
            if (!opt) return;
            
            hInput.value = opt.dataset.id;
            dInput.value = opt.dataset.name;
            drop.classList.add('hidden');
            drop.classList.remove('flex');

            // Robust Unit -> Driver Auto-fill
            if (options === 'unit-search-option') {
                const driverHidden = document.getElementById('incidentDriverId');
                const driverDisplay = document.getElementById('driverSearchDisplay');
                const drvId = opt.dataset.driverId;

                if (drvId && drvId !== 'null' && drvId !== '' && drvId !== '0') {
                    const driverOpt = document.querySelector(`.driver-search-option[data-id="${drvId}"]`);
                    if (driverOpt && driverHidden && driverDisplay) {
                        driverHidden.value = drvId;
                        driverDisplay.value = driverOpt.dataset.name;
                        driverDisplay.dispatchEvent(new Event('input'));
                    }
                } else {
                    if (driverHidden) driverHidden.value = '';
                    if (driverDisplay) driverDisplay.value = '';
                }
            }
        };

        dInput.onfocus = () => { filterDropdown(dInput, options); drop.classList.remove('hidden'); drop.classList.add('flex'); };
        dInput.oninput = () => { filterDropdown(dInput, options); drop.classList.remove('hidden'); drop.classList.add('flex'); };
        dInput.onblur = () => { setTimeout(() => { if (drop) { drop.classList.add('hidden'); drop.classList.remove('flex'); } }, 200); };
    });
}

function filterDropdown(input, optClass) {
    const q = input.value.toLowerCase().trim();
    document.querySelectorAll('.' + optClass).forEach(opt => {
        const text = opt.innerText.toLowerCase();
        opt.style.display = (!q || text.includes(q)) ? 'block' : 'none';
    });
}

// ─── Spare Parts & Catalog Management ───
window.saveQuickPart = async function() {
    const name = document.getElementById('quickPartName').value;
    const price = document.getElementById('quickPartPrice').value;
    if(!name || !price) return alert('Please fill in both name and price.');
    
    try {
        const res = await fetch("{{ route('spare-parts.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name, price })
        });
        const result = await res.json();
        if(result.success) {
            partsCatalog.push(result.data);
            addPartToIncidentCart({
                id: result.data.id,
                name: result.data.name,
                price: parseFloat(result.data.price) || 0,
                qty: 1,
                isCharged: true
            });
            refreshPartSearchDropdown();
            window.closeQuickAddPart();
        }
    } catch(e) { alert('Failed to save part to catalog.'); }
};

function refreshPartSearchDropdown() {
    const dropdown = document.getElementById('incidentPartDropdown');
    if(!dropdown) return;
    dropdown.innerHTML = partsCatalog.map(p => {
        const isAvailable = (parseInt(p.stock_quantity) || 0) > 0;
        return `
            <div class="search-option part-search-option group ${!isAvailable ? 'opacity-60 cursor-not-allowed bg-gray-50' : ''}" 
                data-id="${p.id}" data-name="${p.name}" data-price="${p.price}" data-available="${isAvailable ? '1' : '0'}">
                <div class="flex justify-between items-start w-full">
                    <div>
                        <div class="font-black text-xs ${isAvailable ? 'text-gray-900' : 'text-gray-400'}">${p.name}</div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded ${isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-500 text-white shadow-sm'}">
                                ${isAvailable ? 'STOCK: ' + p.stock_quantity : 'UNAVAILABLE'}
                            </span>
                            <span class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter italic">Supplier: ${p.supplier || 'Unknown'}</span>
                        </div>
                    </div>
                    <div class="text-[10px] font-black text-purple-600">₱${parseFloat(p.price).toFixed(2)}</div>
                </div>
            </div>
        `;
    }).join('');
}

function initPartSearch() {
    const input = document.getElementById('incidentPartSearch');
    const dropdown = document.getElementById('incidentPartDropdown');
    if(!input || !dropdown) return;

    input.onfocus = () => { refreshPartSearchDropdown(); dropdown.classList.remove('hidden'); };
    input.oninput = () => { 
        const q = input.value.toLowerCase();
        dropdown.querySelectorAll('.part-search-option').forEach(opt => {
            opt.style.display = opt.dataset.name.toLowerCase().includes(q) ? 'block' : 'none';
        });
        dropdown.classList.remove('hidden');
    };
    dropdown.onmousedown = (e) => {
        const opt = e.target.closest('.part-search-option');
        if (!opt) return;
        
        // Anti-Unavailable Lock
        if (opt.dataset.available === '0') {
            e.preventDefault();
            return;
        }

        addPartToIncidentCart({ id: opt.dataset.id, name: opt.dataset.name, price: parseFloat(opt.dataset.price) || 0, qty: 1, isCharged: true });
        input.value = ''; dropdown.classList.add('hidden');
    };
    input.onblur = () => { setTimeout(() => dropdown.classList.add('hidden'), 200); };
}

function addPartToIncidentCart(part) {
    const existing = incidentPartsCart.find(p => p.id === part.id);
    if(existing) existing.qty++;
    else incidentPartsCart.push(part);
    refreshPartsCart();
}

function refreshPartsCart() {
    const container = document.getElementById('partsCartContainer');
    if(!container) return;
    if(incidentPartsCart.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No parts selected yet.</div>`;
        document.getElementById('partsTotalLabel').textContent = '₱0.00';
        computeTotal(); return;
    }
    let partsTotal = 0;
    container.innerHTML = incidentPartsCart.map((p, i) => {
        const sub = p.price * p.qty; partsTotal += sub;
        return `<div class="flex items-center gap-4 bg-gray-50/50 p-4 rounded-2xl border border-gray-100 animate-in slide-in-from-right duration-200">
            <input type="hidden" name="parts[${i}][spare_part_id]" value="${p.id}">
            <input type="hidden" name="parts[${i}][unit_price]" value="${p.price}">
            <div class="flex-1"><p class="text-[10px] font-black text-gray-800 uppercase">${p.name}</p></div>
            <div class="w-16"><input type="number" name="parts[${i}][quantity]" value="${p.qty}" onchange="window.updatePartQty(${i}, this.value)" class="w-full text-center py-2 bg-white border border-gray-100 rounded-xl text-[10px] font-black"></div>
            <div class="w-24 text-right"><p class="text-[10px] font-black text-gray-900">₱${sub.toLocaleString()}</p></div>
            <div class="flex items-center"><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="parts[${i}][is_charged_to_driver]" value="1" ${p.isCharged ? 'checked' : ''} onchange="window.togglePartCharge(${i}, this.checked)" class="sr-only peer"><div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-red-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div></label></div>
            <button type="button" onclick="window.removePartFromIncident(${i})" class="text-gray-300 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></div>`;
    }).join('');
    document.getElementById('partsTotalLabel').innerText = '₱' + partsTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    if(window.lucide) lucide.createIcons();
    computeTotal();
}

window.updatePartQty = (i, val) => { incidentPartsCart[i].qty = parseInt(val) || 1; refreshPartsCart(); };
window.togglePartCharge = (i, val) => { incidentPartsCart[i].isCharged = val; computeTotal(); };
window.removePartFromIncident = (i) => { incidentPartsCart.splice(i, 1); refreshPartsCart(); };

// ─── Service Costs Logic ───
window.addServiceRow = () => { incidentServices.push({ description: '', price: 0, isCharged: true }); refreshServices(); };
function refreshServices() {
    const container = document.getElementById('servicesContainer');
    if(!container || incidentServices.length === 0) {
        if(container) container.innerHTML = `<div class="text-center py-4 text-gray-300 italic text-[10px] uppercase tracking-widest">No services recorded.</div>`;
        computeTotal(); return;
    }
    const startIndex = incidentPartsCart.length;
    container.innerHTML = incidentServices.map((s, i) => {
        const fullIndex = startIndex + i;
        return `<div class="flex items-center gap-4 bg-white p-4.5 rounded-2xl border border-orange-100 shadow-sm animate-in zoom-in duration-200">
            <div class="flex-1"><p class="text-[8px] font-black text-orange-400 uppercase tracking-widest">Description</p><input type="text" name="parts[${fullIndex}][custom_part_name]" value="${s.description}" oninput="window.updateServiceDesc(${i}, this.value)" class="w-full text-xs font-bold border-none bg-transparent p-0 focus:ring-0"></div>
            <div class="w-24 border-l border-orange-50 pl-4"><p class="text-[8px] font-black text-orange-400 uppercase tracking-widest">Price</p><input type="number" name="parts[${fullIndex}][unit_price]" value="${s.price || ''}" oninput="window.updateServicePrice(${i}, this.value)" class="w-full text-xs font-black text-orange-600 border-none bg-transparent p-0 focus:ring-0"></div>
            <div class="flex items-center pt-3"><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="parts[${fullIndex}][is_charged_to_driver]" value="1" ${s.isCharged ? 'checked' : ''} onchange="window.toggleServiceCharge(${i}, this.checked)" class="sr-only peer"><div class="w-8 h-4 bg-gray-200 rounded-full peer peer-checked:bg-red-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div></label></div>
            <button type="button" onclick="window.removeService(${i})" class="text-orange-200 hover:text-red-500 pt-3"><i data-lucide="x-circle" class="w-5 h-5"></i></button></div>`;
    }).join('');
    if(window.lucide) lucide.createIcons();
    computeTotal();
}
window.updateServiceDesc = (i, val) => { incidentServices[i].description = val; };
window.updateServicePrice = (i, val) => { incidentServices[i].price = parseFloat(val) || 0; computeTotal(); };
window.toggleServiceCharge = (i, val) => { incidentServices[i].isCharged = val; computeTotal(); };
window.removeService = (i) => { incidentServices.splice(i, 1); refreshServices(); };

// ─── Financial Calculations ───
function computeTotal() {
    let grandTotal = 0, driverCharge = 0;
    incidentPartsCart.forEach(p => { const sub = p.price * p.qty; grandTotal += sub; if (p.isCharged) driverCharge += sub; });
    incidentServices.forEach(s => { grandTotal += s.price; if (s.isCharged) driverCharge += s.price; });
    const tDamage = parseFloat(document.getElementById('thirdDamage')?.value) || 0;
    if (document.getElementById('faultCheck')?.checked) { grandTotal += tDamage; driverCharge += tDamage; } else { grandTotal += tDamage; }
    document.getElementById('totalDamageLabel').textContent = '₱' + grandTotal.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('driverChargeLabel').textContent = '₱' + driverCharge.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('totalChargeValue').value = driverCharge;
}
window.computeTotal = computeTotal;

// ─── Incident Manager (Edit/Archive Actions) ───
window.IncidentManager = {
    openEdit: async function(id) {
        try {
            const res = await fetch(`/api/incidents/${id}/details`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (data.error) throw new Error(data.error);

            // Populate Modal
            document.getElementById('edit_incident_type').value = data.incident_type;
            document.getElementById('edit_severity').value = data.severity;
            document.getElementById('edit_incident_date').value = data.incident_date || data.timestamp.split(' ')[0];
            document.getElementById('edit_description').value = data.description;
            document.getElementById('edit_total_charge').value = data.total_charge_to_driver;
            document.getElementById('edit_is_driver_fault').checked = !!data.is_driver_fault;
            document.getElementById('editInfoDisplay').textContent = `${data.driver_name} • ${data.plate_number}`;
            
            // Set Form action
            const form = document.getElementById('editIncidentForm');
            form.action = `/api/incidents/${id}/update`;
            
            // Set Archive button for this specific ID
            const archiveBtn = document.getElementById('editModalArchiveBtn');
            if (archiveBtn) {
                archiveBtn.onclick = () => this.archive(id);
            }

            // Show Modal
            const modal = document.getElementById('editIncidentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if(window.lucide) lucide.createIcons();
        } catch (e) {
            console.error(e);
            alert('Failed to fetch incident details: ' + e.message);
        }
    },

    archive: async function(id) {
        if (!confirm('Are you sure you want to move this incident to Archive?')) return;
        try {
            const res = await fetch(`/api/incidents/${id}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const result = await res.json();
            if (result.success) {
                window.location.reload();
            } else {
                alert(result.message || 'Failed to archive record.');
            }
        } catch (e) {
            console.error(e);
            alert('Error connecting to server.');
        }
    }
};

window.closeEditIncidentModal = function() {
    const modal = document.getElementById('editIncidentModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
};

// Handle Edit Form Submission via AJAX
document.addEventListener('DOMContentLoaded', () => {
    const editForm = document.getElementById('editIncidentForm');
    if (editForm) {
        editForm.onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            
            // Manual check for checkbox because FormData might omit if unchecked or use "on"
            if (!formData.has('is_driver_fault')) {
                formData.append('is_driver_fault', '0');
            }

            try {
                const res = await fetch(editForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to update record.');
                }
            } catch (e) {
                console.error(e);
                alert('Error updating record.');
            }
        };
    }
});

