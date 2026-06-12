    {{-- Unit Details Modal --}}
    <div id="unitDetailsModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl h-[95vh] sm:h-[90vh] flex flex-col overflow-hidden animate-fade-in">
            {{-- Modal Header (Deep Navy matching login page) --}}
            <div class="bg-slate-800 p-3 sm:p-4 shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="p-1.5 sm:p-2 bg-white bg-opacity-20 rounded-lg">
                            <i data-lucide="info" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-lg font-black text-white leading-tight uppercase tracking-wider">Unit Details</h3>
                            <p class="text-[10px] sm:text-sm text-slate-300 leading-tight mt-0.5">Complete unit information and management</p>
                        </div>
                    </div>
                    <button onclick="closeUnitDetailsModal()" class="text-white hover:text-gray-200 transition-colors p-1 bg-white bg-opacity-5 hover:bg-opacity-10 rounded-lg">
                        <i data-lucide="x" class="w-4.5 h-4.5 sm:w-5 sm:h-5"></i>
                    </button>
                </div>
            </div>

            {{-- Single dynamic content area --}}
            <div id="unitDetailsContent" class="p-3 sm:p-4 overflow-y-auto flex-1">
                {{-- Loading state --}}
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            </div>
        </div>
    </div>

<script>

        // =============================================
        // VIEW UNIT DETAILS - Matching backup's 8-tab structure
        // =============================================
        let currentViewUnitId = null;

        function viewUnitDetails(id) {
            currentViewUnitId = id;
            document.getElementById('unitDetailsModal').classList.remove('hidden');

            // Show loading state inside content div (same as backup)
            document.getElementById('unitDetailsContent').innerHTML = `
                <div class="text-center py-8">
                    <i data-lucide="loader-2" class="w-8 h-8 mx-auto mb-4 text-gray-300 animate-spin"></i>
                    <p class="text-gray-500">Loading unit details...</p>
                </div>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            fetch('{{ route("units.details") }}?id=' + id, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => {
                if (!r.ok) throw new Error('Server returned HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>${data.error}</p></div>`;
                    lucide.createIcons();
                    return;
                }
                const unit = data.unit;
                if (!unit) {
                    document.getElementById('unitDetailsContent').innerHTML = `<div class="text-center py-8 text-red-500"><i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4"></i><p>Unit not found or failed to load.</p></div>`;
                    lucide.createIcons();
                    return;
                }

                const assignedDrivers = data.assigned_drivers || [];
                const roi = data.roi_data || {};
                const bHist = data.boundary_history || [];
                const maint = data.maintenance_records || [];
                const locInfo = data.location_info || {};
                const dashcam = data.dashcam_info || {};

                // --- Coding calculations (matching backup logic) ---
                const plate = unit.plate_number || '';
                const lastChar = plate.replace(/[^A-Z0-9]/gi, '').slice(-1).toUpperCase();
                const lastDigit = /[0-9]/.test(lastChar) ? parseInt(lastChar) : (lastChar.charCodeAt(0) - 64);
                const codingSchedule = { Monday:[1,2], Tuesday:[3,4], Wednesday:[5,6], Thursday:[7,8], Friday:[9,0] };
                let codingDay = data.coding_day || 'Not Set';
                let nextCodingDate = '', daysUntilCoding = 0;
                const today = new Date();
                const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                const todayName = dayNames[today.getDay()];
                if (codingDay && codingDay !== 'Not Set') {
                    if (todayName === codingDay) {
                        nextCodingDate = today.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = 0;
                    } else {
                        const cdIdx = dayNames.indexOf(codingDay);
                        let diff = (cdIdx - today.getDay() + 7) % 7;
                        if (diff === 0) diff = 7;
                        const nextDate = new Date(today);
                        nextDate.setDate(today.getDate() + diff);
                        nextCodingDate = nextDate.toLocaleDateString('en-US', {month:'short', day:'2-digit', year:'numeric'});
                        daysUntilCoding = diff;
                    }
                }

                // --- Build the 8-tab HTML matching backup's unit_details_modal.php ---
                const roiPct = parseFloat(roi.roi_percentage || 0);
                const roiColor = roiPct > 0 ? 'green' : 'red';

                let driversOverviewHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversOverviewHtml += `<div class="bg-gray-50 p-3 rounded">
                            <div class="font-medium">${d.full_name || ''}</div>
                            <div class="text-sm text-gray-600">${d.license_number || ''}</div>
                            <div class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</div>
                        </div>`;
                    });
                }

                let driversTabHtml = '';
                if (assignedDrivers.length > 0) {
                    assignedDrivers.forEach(d => {
                        driversTabHtml += `<div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h5 class="font-semibold text-gray-900">${d.full_name || ''}</h5>
                                    <p class="text-sm text-gray-600">License: ${d.license_number || ''}</p>
                                    <p class="text-sm text-gray-600">Contact: ${d.contact_number || 'N/A'}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Active</span>
                            </div>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-600">License Number:</span><p class="font-medium">${d.license_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Contact:</span><p class="font-medium">${d.contact_number || 'N/A'}</p></div>
                                <div><span class="text-gray-600">Daily Target:</span><p class="font-medium">₱${parseFloat(d.daily_boundary_target || 1100).toLocaleString('en-PH', {minimumFractionDigits:2})}</p></div>
                                <div><span class="text-gray-600">Hire Date:</span><p class="font-medium">${d.hire_date || 'Not set'}</p></div>
                                <div><span class="text-gray-600">License Expiry:</span><p class="font-medium">${d.license_expiry || 'Not set'}</p></div>
                            </div>
                        </div>`;
                    });
                } else {
                    driversTabHtml = `<div class="text-center py-8 text-gray-500"><i data-lucide="users" class="w-12 h-12 mx-auto mb-4 text-gray-300"></i><p>No drivers assigned to this unit</p></div>`;
                }

                let boundaryRowsHtml = '';
                if (bHist.length > 0) {
                    bHist.forEach((bh, idx) => {
                        const rawRemarks = (bh.remarks || bh.notes || '').trim();
                        const safeRemarks = rawRemarks.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        
                        boundaryRowsHtml += `
                        <tr onclick="toggleBndHistoryRemarks(${idx})" class="hover:bg-slate-50 transition-colors cursor-pointer sm:cursor-default">
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-gray-700 font-medium">
                                <div class="flex items-center gap-1.5">
                                    ${rawRemarks ? `<i data-lucide="chevron-down" id="chevron-bnd-js-${idx}" class="w-3.5 h-3.5 text-blue-500 transition-transform duration-200 sm:hidden"></i>` : ''}
                                    <span>${bh.date || ''}</span>
                                </div>
                            </td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-gray-700 font-bold">${bh.full_name || 'N/A'}</td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-gray-500 hidden sm:table-cell">${rawRemarks || '---'}</td>
                            <td class="px-3 sm:px-6 py-3 whitespace-nowrap text-xs text-right font-extrabold text-green-600">
                                <span>₱${parseFloat(bh.actual_boundary || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                            </td>
                        </tr>
                        ${rawRemarks ? `
                        <tr id="remarks-bnd-js-${idx}" class="hidden bg-blue-50/40">
                            <td colspan="3" class="px-3.5 py-2.5 text-xs text-slate-700">
                                <div class="flex items-start gap-2 bg-white p-2.5 rounded-lg border border-blue-100 shadow-xs text-left">
                                    <div class="p-1 bg-blue-100 rounded text-blue-700 mt-0.5">
                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <span class="text-[8px] text-blue-600 font-black uppercase tracking-widest block mb-0.5">Remarks / Notes</span>
                                        <p class="font-semibold text-slate-800 leading-relaxed">${safeRemarks}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>` : ''}
                        `;
                    });
                }

                let maintHtml = '';
                if (maint.length > 0) {
                    maint.forEach(m => {
                        // Build parts details HTML if available
                        let partsDetailsHtml = '';
                        if (m.parts_details && m.parts_details.length > 0) {
                            const parts = m.parts_details.filter(p => p.part_id != null);
                            const others = m.parts_details.filter(p => p.part_id == null);

                            partsDetailsHtml = '<div class="mt-4 space-y-3">';

                            if (parts.length > 0) {
                                partsDetailsHtml += '<div class="bg-blue-50 rounded-xl border border-blue-100 overflow-hidden"><div class="px-3 py-2 bg-blue-100 border-b border-blue-200"><span class="text-[10px] font-black text-blue-800 uppercase tracking-widest">🔧 Parts Replaced</span></div><div class="divide-y divide-blue-100">';
                                parts.forEach(p => {
                                    const supplierBadge = p.supplier_name
                                        ? `<span class="inline-block mt-0.5 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[9px] font-bold truncate max-w-[120px]" title="${p.supplier_name}">${p.supplier_name}</span>`
                                        : '';
                                    partsDetailsHtml += `<div class="px-3 py-2 flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-900 truncate leading-tight" title="${p.part_name}">${p.part_name}</p>
                                            <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                ${p.quantity > 1 ? `<span class="text-[9px] text-gray-500 font-bold">x${p.quantity}</span>` : ''}
                                                ${supplierBadge}
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-blue-700 flex-shrink-0 ml-2">₱${parseFloat(p.total || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                    </div>`;
                                });
                                const partsTotal = parts.reduce((sum, p) => sum + parseFloat(p.total || 0), 0);
                                partsDetailsHtml += `</div><div class="flex justify-between items-center px-3 py-2 bg-blue-100 border-t border-blue-200">
                                    <span class="text-[10px] font-black text-blue-800 uppercase">Parts Subtotal</span>
                                    <span class="text-xs font-black text-blue-700">₱${partsTotal.toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                </div></div>`;
                            }

                            if (others.length > 0) {
                                partsDetailsHtml += '<div class="bg-orange-50 rounded-xl border border-orange-100 overflow-hidden"><div class="px-3 py-2 bg-orange-100 border-b border-orange-200"><span class="text-[10px] font-black text-orange-800 uppercase tracking-widest">🛠 Other Costs & Services</span></div><div class="divide-y divide-orange-100">';
                                others.forEach(o => {
                                    partsDetailsHtml += `<div class="px-3 py-2 flex items-start justify-between gap-2">
                                        <p class="text-xs font-semibold text-gray-900 flex-1 min-w-0 truncate leading-tight" title="${o.part_name}">${o.part_name}</p>
                                        <span class="text-xs font-black text-orange-700 flex-shrink-0 ml-2">₱${parseFloat(o.total || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                    </div>`;
                                });
                                const othersTotal = others.reduce((sum, o) => sum + parseFloat(o.total || 0), 0);
                                partsDetailsHtml += `</div><div class="flex justify-between items-center px-3 py-2 bg-orange-100 border-t border-orange-200">
                                    <span class="text-[10px] font-black text-orange-800 uppercase">Services Subtotal</span>
                                    <span class="text-xs font-black text-orange-700">₱${othersTotal.toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                </div></div>`;
                            }

                            partsDetailsHtml += '</div>';
                        }

                        const statusBadge = m.status === 'completed'
                            ? `<span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-[9px] font-black uppercase tracking-widest">✓ Completed</span>`
                            : m.status === 'cancelled'
                                ? `<span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-[9px] font-black uppercase tracking-widest">✗ Cancelled</span>`
                                : `<span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-[9px] font-black uppercase tracking-widest">⏳ Pending</span>`;

                        const driverHtml = m.driver_name
                            ? `<div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-black text-gray-400 uppercase w-20 flex-shrink-0">Driver</span>
                                <span class="text-xs font-semibold text-gray-800 truncate" title="${m.driver_name}">${m.driver_name}</span>
                               </div>`
                            : '';

                        maintHtml += `
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                            {{-- Card Header --}}
                            <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-4 py-3 flex items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-black text-sm truncate leading-tight" title="${m.maintenance_type || m.type || 'Maintenance'}">${m.maintenance_type || m.type || 'Maintenance'}</p>
                                    <p class="text-slate-300 text-[10px] font-medium">${m.date_started || m.date || 'No date'}</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <p class="text-orange-300 font-black text-base leading-tight">₱${parseFloat(m.total_cost || m.cost || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                    <p class="text-slate-400 text-[9px] uppercase font-bold">Total Cost</p>
                                </div>
                            </div>

                            {{-- Card Body: 2-column info grid --}}
                            <div class="px-4 py-3">
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs mb-2">
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Mechanic</span>
                                        <p class="font-semibold text-gray-800 truncate" title="${m.mechanic_name || 'N/A'}">${m.mechanic_name || 'N/A'}</p>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Status</span>
                                        <div class="mt-0.5">${statusBadge}</div>
                                    </div>
                                    ${driverHtml ? `<div class="col-span-2 min-w-0">${driverHtml}</div>` : ''}
                                    <div class="col-span-2 min-w-0">
                                        <span class="text-[9px] font-black text-gray-400 uppercase block">Description</span>
                                        <p class="font-medium text-gray-700 text-xs leading-snug line-clamp-3">${m.description || m.notes || 'No description provided.'}</p>
                                    </div>
                                </div>

                                ${partsDetailsHtml}
                            </div>
                        </div>`;
                    });
                } else {
                    maintHtml = `<div class="text-center py-12 text-gray-400"><i data-lucide="wrench" class="w-12 h-12 mx-auto mb-3 text-gray-200"></i><p class="font-semibold text-sm">No maintenance records found</p></div>`;
                }

                const roiPrgW = Math.min(100, Math.max(0, roiPct)).toFixed(1);
                const invPerMonth = parseFloat(roi.total_investment || 0) / 12;
                const mthBnd = parseFloat(roi.actual_monthly_revenue || roi.monthly_revenue || roi.monthly_boundary || 0);
                const mthExp = parseFloat(roi.monthly_theoretical_target || 0);
                
                // Primary: Operational Efficiency (Actual Collection vs Theoretical Month Max)
                // Fallback: Financial Target (Actual Collection vs Investment/12)
                const targetAmount = mthExp > 0 ? mthExp : invPerMonth;
                const bndPrgW = targetAmount > 0 ? Math.min(100, (mthBnd / targetAmount) * 100).toFixed(1) : 0;

                document.getElementById('unitDetailsContent').innerHTML = `
                <div class="space-y-4 sm:space-y-6">
                    <!-- Unit Summary Card (Top) -->
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 sm:p-6 rounded-2xl text-white shadow-lg">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="p-2.5 sm:p-3 bg-white bg-opacity-10 rounded-xl">
                                    <i data-lucide="car" class="w-6 h-6 sm:w-8 sm:h-8 text-white"></i>
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-3 mb-1">
                                        <h3 class="text-base sm:text-2xl font-black tracking-tight leading-none">${unit.plate_number || ''}</h3>
                                        ${unit.status !== 'at_risk' ? `<span class="px-2 py-0.5 bg-white bg-opacity-10 rounded text-[9px] font-bold uppercase tracking-wider">${unit.status || ''}</span>` : ''}
                                        <span class="px-2 py-0.5 bg-white bg-opacity-10 rounded text-[9px] font-bold uppercase tracking-wider">${unit.unit_type || 'Standard'}</span>
                                        ${unit.status === 'at_risk' ? `<span class="px-2 py-0.5 bg-red-500 text-white rounded text-[9px] font-bold uppercase tracking-wider animate-pulse">🚨 AT RISK</span>` : ''}
                                    </div>
                                    <p class="text-slate-300 text-xs font-semibold">${(unit.make || '') + ' ' + (unit.model || '') + ' (' + (unit.year || '') + ')'}</p>
                                </div>
                            </div>
                            <div class="sm:text-right flex sm:flex-col justify-between items-center sm:items-end bg-white bg-opacity-5 p-2.5 rounded-xl sm:p-0 sm:bg-transparent">
                                <p class="text-slate-400 text-[9px] font-black uppercase tracking-widest sm:hidden">Daily Boundary Rate</p>
                                <div class="text-right">
                                    <div class="text-base sm:text-2xl font-black text-blue-400 sm:text-white">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                                    <p class="text-slate-300 text-[10px] sm:text-xs font-bold hidden sm:block">Daily Boundary Rate</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Swipeable Tabs Navigation on Mobile -->
                    <div class="border-b border-gray-200">
                        <div class="overflow-x-auto scrollbar-none" style="-webkit-overflow-scrolling: touch;">
                            <nav class="-mb-px flex space-x-1 min-w-max px-1">
                                <button onclick="showTab('overview')" class="tab-btn py-3 px-3.5 border-b-2 border-blue-600 font-black text-[10px] uppercase tracking-widest text-blue-600 transition-all duration-200 whitespace-nowrap" data-tab="overview">Overview</button>
                                <button onclick="showTab('drivers')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="drivers">Drivers</button>
                                <button onclick="showTab('coding')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="coding">Coding</button>
                                <button onclick="showTab('boundary')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="boundary">Boundary</button>
                                <button onclick="showTab('maintenance')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="maintenance">Maintenance</button>
                                <button onclick="showTab('roi')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="roi">ROI</button>
                                <button onclick="showTab('location')" class="tab-btn py-3 px-3.5 border-b-2 border-transparent font-black text-[10px] uppercase tracking-widest text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-all duration-200 whitespace-nowrap" data-tab="location">Location</button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tab Content Area -->
                    <div id="tabContent" class="min-h-[480px]">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content animate-in fade-in duration-300">
                            <!-- Quick Stats Grid -->
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-blue-50 rounded-xl"><i data-lucide="users" class="w-5 h-5 text-blue-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Drivers</p><p class="text-xl font-black text-gray-900">${assignedDrivers.length}/2</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-green-50 rounded-xl"><i data-lucide="calendar" class="w-5 h-5 text-green-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Next Coding</p><p class="text-xl font-black text-gray-900">${daysUntilCoding === 0 ? 'Today' : daysUntilCoding + 'd'}</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-purple-50 rounded-xl"><i data-lucide="trending-up" class="w-5 h-5 text-purple-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">ROI</p><p class="text-xl font-black text-gray-900">${roiPct.toFixed(1)}%</p></div>
                                    </div>
                                </div>
                                <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-2.5 bg-orange-50 rounded-xl"><i data-lucide="wrench" class="w-5 h-5 text-orange-600"></i></div>
                                        <div><p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">Maint Jobs</p><p class="text-xl font-black text-gray-900">${maint.length}</p></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Info Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Basic Info Card -->
                                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                    <h4 class="text-xs font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i> Basic Information
                                    </h4>
                                    <div class="space-y-4 text-sm">
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Plate Number</span><span class="font-black text-gray-900 bg-gray-50 px-2 py-1 rounded">${unit.plate_number || ''}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Vehicle</span><span class="font-black text-gray-700">${(unit.make || '') + ' ' + (unit.model || '')}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Year</span><span class="font-black text-gray-700">${unit.year || ''}</span></div>
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-50"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Created By</span><span class="font-bold text-gray-600 text-xs">${unit.created_by_name || 'System'}</span></div>
                                        <div class="flex justify-between items-center"><span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Last Update</span><span class="font-bold text-gray-600 text-xs">${unit.updated_at_fmt || 'N/A'}</span></div>
                                        <div class="flex justify-between items-center pt-4 border-t border-gray-50 mt-4">
                                            <span class="text-gray-900 font-black uppercase text-[11px] tracking-widest">Active Rate</span>
                                            <span class="text-2xl font-black text-blue-600">₱${parseFloat(unit.boundary_rate || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assignment Card -->
                                <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                    <h4 class="text-xs font-black text-gray-900 mb-5 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3">
                                        <i data-lucide="users" class="w-4 h-4 text-blue-600"></i> Driver Assignment
                                    </h4>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-gray-400 font-bold uppercase text-[10px] tracking-tight">Status</span>
                                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase ${assignedDrivers.length >= 2 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100'}">${assignedDrivers.length >= 2 ? 'Full' : 'Available'}</span>
                                        </div>
                                        ${driversOverviewHtml ? '<div class="space-y-3">' + driversOverviewHtml.replace(/bg-gray-50/g, 'bg-gray-50 border border-gray-100 rounded-xl p-4') + '</div>' : `
                                            <div class="text-center py-10">
                                                <div class="bg-gray-50 w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                                                    <i data-lucide="user-x" class="w-6 h-6 text-gray-300"></i>
                                                </div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Drivers Assigned</p>
                                            </div>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Drivers Tab -->
                        <div id="drivers-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="users" class="w-5 h-5 text-blue-600"></i> Assigned Drivers Details
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    ${driversTabHtml.replace(/border border-gray-200/g, 'bg-gray-50 border border-gray-100 shadow-sm').replace(/p-4/g, 'p-6').replace(/rounded-lg/g, 'rounded-2xl')}
                                </div>
                            </div>
                        </div>

                        <!-- Coding Tab -->
                        <div id="coding-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i> MMDA Coding Schedule
                                </h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                                        <h5 class="font-black text-xs text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-200 pb-2">Current Unit Status</h5>
                                        <div class="space-y-4 text-sm">
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Coding Day</span><span class="px-3 py-1 bg-blue-600 text-white rounded-full text-[10px] font-black uppercase tracking-widest">${codingDay}</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Plate Ending</span><span class="font-black text-gray-900 text-lg">${lastChar || '-'}</span></div>
                                            <div class="flex justify-between items-center"><span class="text-gray-500 font-bold uppercase text-[10px]">Next Schedule</span><span class="font-black text-gray-900">${nextCodingDate || '-'}</span></div>
                                            <div class="flex justify-between items-center pt-4 border-t border-gray-200"><span class="text-gray-500 font-bold uppercase text-[10px]">Remaining</span><span class="font-black ${daysUntilCoding === 0 ? 'text-red-600' : 'text-green-600'} text-lg">${daysUntilCoding === 0 ? 'TODAY' : daysUntilCoding + ' Days'}</span></div>
                                        </div>
                                    </div>
                                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-inner">
                                        <h5 class="font-black text-xs text-gray-400 uppercase tracking-widest mb-4">Standard MMDA Reference</h5>
                                        <div class="space-y-2">
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Monday</span>
                                                <span class="font-black text-blue-600">1, 2</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Tuesday</span>
                                                <span class="font-black text-blue-600">3, 4</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Wednesday</span>
                                                <span class="font-black text-blue-600">5, 6</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Thursday</span>
                                                <span class="font-black text-blue-600">7, 8</span>
                                            </div>
                                            <div class="flex justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
                                                <span class="font-black text-gray-600 text-[10px] uppercase">Friday</span>
                                                <span class="font-black text-blue-600">9, 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boundary Tab -->
                        <div id="boundary-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-4 sm:p-6 shadow-sm">
                                <h4 class="text-xs sm:text-sm font-black text-gray-900 mb-4 sm:mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-3 sm:pb-4">
                                    <i data-lucide="coins" class="w-5 h-5 text-blue-600"></i> Boundary Collection History
                                </h4>
                                ${boundaryRowsHtml ? `
                                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 sm:px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                                                    <th class="px-3 sm:px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                                                    <th class="px-3 sm:px-6 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest hidden sm:table-cell">Remarks</th>
                                                    <th class="px-3 sm:px-6 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-100">
                                                ${boundaryRowsHtml}
                                            </tbody>
                                        </table>
                                    </div>
                                ` : `
                                    <div class="text-center py-20 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                                        <div class="p-4 bg-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4 shadow-sm">
                                            <i data-lucide="banknote" class="w-8 h-8 text-gray-300"></i>
                                        </div>
                                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">No transaction history found</p>
                                    </div>
                                `}
                            </div>
                        </div>

                        <!-- Maintenance Tab -->
                        <div id="maintenance-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center justify-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="wrench" class="w-5 h-5 text-blue-600"></i> Vehicle Maintenance Records
                                    ${parseFloat(roi.total_expenses || 0) > 0 ? `<span class="ml-2 px-2 py-0.5 bg-orange-50 text-orange-600 border border-orange-100 rounded text-[10px] font-black uppercase">Total: ₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</span>` : ''}
                                </h4>
                                <div class="flex flex-col gap-6 max-w-3xl mx-auto">
                                    ${maintHtml.replace(/border border-gray-200 bg-white/g, 'bg-gray-50 border border-gray-100 shadow-sm').replace(/p-4/g, 'p-6').replace(/rounded-lg/g, 'rounded-2xl')}
                                </div>
                            </div>
                        </div>

                        <!-- ROI Tab -->
                        <div id="roi-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="space-y-6">
                                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 rounded-2xl text-white shadow-xl relative overflow-hidden">
                                    <div class="absolute top-0 right-0 p-8 opacity-10">
                                        <i data-lucide="trending-up" class="w-32 h-32"></i>
                                    </div>
                                    <h4 class="text-xl font-black mb-6 uppercase tracking-widest flex items-center gap-3">
                                        <i data-lucide="bar-chart-3" class="w-6 h-6"></i> ROI Performance Analysis
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Investment</p>
                                            <p class="text-2xl font-black">₱${parseFloat(roi.total_investment || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Net Revenue</p>
                                            <p class="text-2xl font-black text-green-300">₱${parseFloat(roi.total_revenue || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                        <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                                            <p class="text-indigo-100 text-[10px] font-black uppercase tracking-widest mb-1">Total Expenses</p>
                                            <p class="text-2xl font-black text-red-300">₱${parseFloat(roi.total_expenses || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                                        <h4 class="text-xs font-black text-gray-900 mb-6 uppercase tracking-widest border-b border-gray-50 pb-3">Key Metrics</h4>
                                        <div class="space-y-6">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">ROI Percentage</span>
                                                <span class="text-2xl font-black text-${roiColor}-600">${roiPct.toFixed(1)}%</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">Payback Period</span>
                                                <span class="text-2xl font-black text-blue-600">${parseFloat(roi.payback_period || 0).toFixed(1)} <span class="text-sm font-bold text-gray-400 uppercase">mths</span></span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">Avg Monthly Revenue</span>
                                                <span class="text-2xl font-black text-green-600">₱${mthBnd.toLocaleString('en-PH', {minimumFractionDigits:2})}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white border border-gray-100 rounded-2xl p-8 shadow-sm">
                                        <h4 class="text-xs font-black text-gray-900 mb-8 uppercase tracking-widest border-b border-gray-50 pb-3">Goal Progress</h4>
                                        <div class="space-y-8">
                                            <div>
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Investment Achievement</span>
                                                    <span class="text-sm font-black text-indigo-600">${roiPct.toFixed(1)}%</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-4 p-1 shadow-inner">
                                                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 h-2 rounded-full shadow-sm" style="width:${roiPrgW}%"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Monthly Target Efficiency</span>
                                                    <span class="text-sm font-black text-green-600">₱${targetAmount.toLocaleString('en-PH', {minimumFractionDigits:0})} Target</span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-4 p-1 shadow-inner">
                                                    <div class="bg-gradient-to-r from-green-400 to-emerald-600 h-2 rounded-full shadow-sm" style="width:${bndPrgW}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content hidden animate-in fade-in duration-300">
                            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                                <h4 class="text-sm font-black text-gray-900 mb-6 flex items-center gap-2 uppercase tracking-widest border-b border-gray-50 pb-4">
                                    <i data-lucide="map-pin" class="w-5 h-5 text-blue-600"></i> Real-time GPS Location
                                </h4>
                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Current Address</p>
                                            <p class="text-xs font-black text-gray-900">${locInfo.current_location || 'Not Available'}</p>
                                        </div>
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Last Update</p>
                                            <p class="text-xs font-black text-gray-900">${locInfo.last_location_update || 'Never'}</p>
                                        </div>
                                        <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 flex items-center justify-between">
                                            <div>
                                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">GPS Status</p>
                                                <span class="px-2 py-1 text-[9px] font-black uppercase rounded-full ${locInfo.gps_enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${locInfo.gps_enabled ? 'Active' : 'Offline'}</span>
                                            </div>
                                            <i data-lucide="satellite" class="w-6 h-6 ${locInfo.gps_enabled ? 'text-green-500' : 'text-red-400'}"></i>
                                        </div>
                                    </div>
                                    <div id="unitDetailMapContainer" class="relative rounded-2xl overflow-hidden border border-gray-100 bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-12 text-center shadow-inner" style="height: 400px;">
                                        <div class="mb-4 sm:mb-6 p-4 sm:p-6 bg-blue-100 rounded-full shadow-sm animate-pulse">
                                            <i data-lucide="navigation" class="w-8 h-8 sm:w-12 sm:h-12 text-blue-600"></i>
                                        </div>
                                        <h4 class="text-base sm:text-lg font-black text-gray-900 mb-1.5 sm:mb-2 uppercase tracking-tight">Tracksolid Pro Enterprise</h4>
                                        <p class="text-xs sm:text-sm text-gray-500 mb-6 sm:mb-8 max-w-md mx-auto">This unit is tracked via real-time satellite identification. Access the full live map for movement history and geofencing.</p>
                                        
                                        <a href="/live-tracking?unit=${unit.id}" class="inline-flex items-center gap-2.5 sm:gap-3 px-6 sm:px-8 py-3.5 sm:py-4 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-black rounded-2xl transition-all shadow-lg hover:shadow-blue-200 hover:-translate-y-1 uppercase tracking-widest">
                                            <i data-lucide="map" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                            Open Live Tracking Map
                                        </a>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                    </div>
                </div>
                `;

                // Re-init lucide icons
                if (typeof lucide !== 'undefined') lucide.createIcons();
                // Show overview tab by default
                setTimeout(() => { showTab('overview'); }, 50);
            })
            .catch(err => {
                document.getElementById('unitDetailsContent').innerHTML = `
                    <div class="text-center py-8">
                        <i data-lucide="alert-circle" class="w-12 h-12 mx-auto mb-4 text-red-500"></i>
                        <p class="text-red-500">Failed to load unit details</p>
                    </div>
                `;
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        }

        function closeUnitDetailsModal() {
            document.getElementById('unitDetailsModal').classList.add('hidden');
        }

        function toggleBndHistoryRemarks(idx) {
            if (window.innerWidth >= 640) {
                return; // Do nothing on Desktop/Web! "wag mo gagalwin ang web ko"
            }
            const row = document.getElementById('remarks-bnd-js-' + idx);
            const chev = document.getElementById('chevron-bnd-js-' + idx);
            if (row) {
                const isHidden = row.classList.contains('hidden');
                if (isHidden) {
                    row.classList.remove('hidden');
                    if (chev) chev.style.transform = 'rotate(180deg)';
                } else {
                    row.classList.add('hidden');
                    if (chev) chev.style.transform = 'rotate(0deg)';
                }
            }
        }
</script>
