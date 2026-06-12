{{-- Driver Details Modal with Tabs --}}
<div id="driverDetailsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden h-full w-full z-50 flex items-center justify-center p-4 transition-all duration-300">
    <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full h-[90vh] overflow-hidden flex flex-col scale-95 transition-transform duration-300" id="driverDetailsModalContainer">
        {{-- Modal Header (Deep Navy) --}}
        <div class="bg-slate-800 p-5 shrink-0">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="user-check" class="w-6 h-6 text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white tracking-wide uppercase" id="driverDetailsName">Driver Details</h3>
                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 uppercase tracking-widest" id="driverDetailsSubtitle">Profiling & Performance Analysis</p>
                    </div>
                </div>
                <button type="button" onclick="closeDriverDetails()" class="text-slate-400 hover:text-white bg-slate-700/50 hover:bg-slate-700 p-2 rounded-full transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="px-6 bg-slate-50 border-b shrink-0 overflow-x-auto custom-scrollbar">
            <nav class="-mb-px flex space-x-1" aria-label="Tabs">
                <button type="button" class="driver-tab active py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-blue-500 text-blue-600 transition-all flex items-center gap-2" data-tab="basic">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i> Basic Info
                </button>
                <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="license">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> License & Documents
                </button>
                <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="incentives">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i> Incentives
                </button>
                <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="performance">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> Performance
                </button>
                <button type="button" class="driver-tab py-4 px-4 text-[10px] font-black uppercase tracking-widest border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all flex items-center gap-2" data-tab="insights">
                    <i data-lucide="brain-circuit" class="w-3.5 h-3.5"></i> Insights
                </button>
            </nav>
        </div>

        {{-- Tab Panels --}}
        <div class="p-8 overflow-y-auto flex-1 custom-scrollbar">
            <div class="driver-tab-panel" data-tab-panel="basic">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Personal & Employment Details</h4>
                </div>
                <div id="basicInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <p class="text-slate-400 animate-pulse">Synchronizing basic profile...</p>
                </div>
            </div>

            <div class="driver-tab-panel hidden" data-tab-panel="license">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-indigo-500 rounded-full"></div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">License & Credentials</h4>
                </div>
                <div id="licenseInfoContent" class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-600">
                    <p class="text-slate-400 animate-pulse">Verifying license data...</p>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100">
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200">
                        <h5 class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2 flex items-center gap-2">
                            <i data-lucide="upload-cloud" class="w-4 h-4 text-blue-500"></i> Secure Document Vault
                        </h5>
                        <p class="text-[11px] text-slate-500 mb-6 font-medium">Upload encrypted copies of NBI, Barangay, and Medical clearances. New uploads will overwrite legacy records.</p>
                        
                        <form id="driverDocumentsForm" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            <input type="hidden" name="_method" value="POST">
                            <input type="hidden" name="driver_id" id="driverDocumentsDriverId" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">License (Front)</label>
                                    <input type="file" name="license_front" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">License (Back)</label>
                                    <input type="file" name="license_back" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">NBI Clearance</label>
                                    <input type="file" name="nbi_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Barangay Clearance</label>
                                    <input type="file" name="barangay_clearance" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Medical Certificate</label>
                                    <input type="file" name="medical_certificate" accept=".jpg,.jpeg,.png,.pdf" class="block w-full text-xs text-slate-700 bg-white border border-slate-200 rounded-xl p-2 cursor-pointer focus:outline-none hover:border-blue-300 transition-colors">
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="px-8 py-3 bg-slate-800 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-slate-900 transition-all shadow-lg shadow-slate-200 active:scale-95 flex items-center gap-2">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-blue-400"></i> Commit Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="driver-tab-panel hidden" data-tab-panel="incentives">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-emerald-500 rounded-full"></div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Incentive Performance Hub</h4>
                </div>
                <div id="incentivesContent" class="text-sm text-slate-600">
                    <p class="text-slate-400 animate-pulse">Calculating reward eligibility...</p>
                </div>
            </div>

            <div class="driver-tab-panel hidden" data-tab-panel="performance">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-orange-500 rounded-full"></div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Telemetry & Metrics</h4>
                </div>
                <div id="performanceContent" class="text-sm text-slate-600 space-y-2">
                    <p class="text-slate-400 animate-pulse">Fetching operational data...</p>
                </div>
            </div>

            <div class="driver-tab-panel hidden" data-tab-panel="insights">
                <div class="flex items-center gap-2 mb-6">
                    <div class="w-1 h-6 bg-rose-500 rounded-full"></div>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Strategic Recommendation</h4>
                </div>
                <div id="insightsContent" class="text-sm text-slate-600 space-y-2">
                    <p class="text-slate-400 animate-pulse">Synthesizing AI insights...</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="p-4 border-t flex justify-end shadow-inner bg-slate-50 shrink-0">
            <button type="button" onclick="closeDriverDetails()" 
                class="px-8 py-2.5 bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300 text-sm font-black transition-all">
                Close Details
            </button>
        </div>
    </div>
</div>

<script>
    function openDriverDetails(id) {
        const modal = document.getElementById('driverDetailsModal');
        modal.classList.remove('hidden');
        
        setTimeout(() => {
            document.getElementById('driverDetailsModalContainer').classList.remove('scale-95');
        }, 10);

        document.querySelectorAll('.driver-tab').forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600', 'active');
            btn.classList.add('border-transparent', 'text-slate-400');
        });
        document.querySelectorAll('.driver-tab-panel').forEach(panel => { panel.classList.add('hidden'); });
        
        const firstTab = document.querySelector('.driver-tab[data-tab="basic"]');
        const firstPanel = document.querySelector('.driver-tab-panel[data-tab-panel="basic"]');
        if (firstTab && firstPanel) {
            firstTab.classList.add('border-blue-500', 'text-blue-600', 'active');
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
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Personal Identification</span>
                        <p class="text-base font-black text-slate-900 mt-1">${data.first_name || ''} ${data.last_name || ''}</p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Primary Contact</span>
                        <p class="text-sm font-bold text-slate-700 mt-0.5 flex items-center gap-2">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-blue-500"></i> ${data.contact_number || 'N/A'}
                        </p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Residential Address</span>
                        <p class="text-sm font-bold text-slate-700 mt-0.5 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i> ${data.address || 'N/A'}
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Emergency Nexus</span>
                        <div class="mt-2 bg-rose-50 p-3 rounded-xl border border-rose-100">
                            <p class="text-xs font-black text-rose-800">${data.emergency_contact || 'N/A'}</p>
                            <p class="text-[11px] font-bold text-rose-600 mt-0.5">${data.emergency_phone || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-50">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Outstanding Liabilities</span>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <div class="p-3 bg-red-50/50 rounded-xl border border-red-100">
                                <span class="text-[9px] font-black uppercase tracking-widest text-red-500 block mb-0.5">Unpaid Shortage</span>
                                <p class="text-xs font-black text-red-700">₱${parseFloat(data.net_shortage || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                            </div>
                            <div class="p-3 bg-orange-50/50 rounded-xl border border-orange-100">
                                <span class="text-[9px] font-black uppercase tracking-widest text-orange-500 block mb-0.5">Pending Debt</span>
                                <p class="text-xs font-black text-orange-700">₱${parseFloat(data.total_pending_debt || 0).toLocaleString('en-PH', {minimumFractionDigits:2})}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Employment Tenure</span>
                                <p class="text-sm font-black text-slate-900 mt-0.5">Joined ${data.hire_date || 'N/A'}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Fleet Status</span>
                                <div class="mt-1">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest ${data.driver_status === 'banned' ? 'bg-red-100 text-red-700 ring-1 ring-red-300 animate-pulse' : (data.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')}">
                                        ${(data.driver_status || 'Unknown')}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/50">
                                <span class="text-xs font-bold text-slate-500">Standard Daily Rate</span>
                                <span class="text-sm font-black text-slate-900">₱${data.assigned_boundary_rate ? parseFloat(data.assigned_boundary_rate).toLocaleString() : '0.00'}</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-200/50">
                                <span class="text-xs font-bold text-slate-500">Active Targeted Rate</span>
                                <span class="text-sm font-black text-blue-600">₱${data.current_pricing ? data.current_pricing.rate.toFixed(2) : '0.00'}</span>
                            </div>
                            ${data.current_pricing && data.current_pricing.label ? `
                                <div class="mt-2 text-right">
                                    <span class="text-[9px] font-black bg-blue-100 text-blue-700 px-2 py-0.5 rounded uppercase tracking-tighter">${data.current_pricing.label}</span>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            const docBase = '{{ url('/') }}/';
            const docImg = (path, label) => path
                ? `<div class="flex flex-col items-center gap-2">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">${label}</p>
                    <a href="${docBase}${path}" target="_blank" class="block">
                        <img src="${docBase}${path}" alt="${label}" class="w-[140px] h-[90px] object-cover rounded-xl border border-slate-200 shadow-sm hover:opacity-80 transition cursor-pointer" />
                    </a>
                   </div>`
                : `<div class="flex flex-col items-center gap-2">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">${label}</p>
                    <div class="w-[140px] h-[90px] flex items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 text-[9px] text-slate-400 font-black uppercase tracking-wider">Not Uploaded</div>
                   </div>`;

            document.getElementById('licenseInfoContent').innerHTML = `
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Professional License Number</span>
                        <p class="text-base font-mono font-black text-slate-900 mt-1 tracking-wider">${data.license_number || '—'}</p>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Validation Expiry Date</span>
                        <p class="text-sm font-bold ${new Date(data.license_expiry) < new Date() ? 'text-red-600' : 'text-slate-700'} mt-0.5 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i> ${data.license_expiry || 'N/A'}
                        </p>
                    </div>
                </div>
                <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100 self-start">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-1.5 bg-indigo-100 rounded-lg">
                            <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                        </div>
                        <p class="text-xs font-black text-indigo-800 uppercase tracking-widest">Integrity Guard</p>
                    </div>
                    <p class="text-xs text-indigo-600/80 leading-relaxed font-medium">Automatic system verification of driver credentials. All documents uploaded are cross-referenced with fleet security protocols.</p>
                </div>
                <div class="col-span-2 mt-2 pt-4 border-t border-slate-100">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Documents Uploaded via Driver App</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        ${docImg(data.profile_photo, 'Profile Photo')}
                        ${docImg(data.license_photo, "Driver's License")}
                        ${docImg(data.nbi_clearance_photo, 'NBI Clearance')}
                        ${docImg(data.pnp_clearance_photo, 'PNP Clearance')}
                    </div>
                </div>
            `;

            // ===================== INCENTIVES TAB =====================
            const incentiveRate = data.incentive_rate || 0;
            const rateColor = incentiveRate >= 80 ? 'text-emerald-600' : incentiveRate >= 50 ? 'text-amber-600' : 'text-rose-600';
            const rateBar  = incentiveRate >= 80 ? 'bg-emerald-500' : incentiveRate >= 50 ? 'bg-amber-400' : 'bg-rose-500';

            let incentiveRowsHtml = '';
            if (data.incentive_breakdown && data.incentive_breakdown.length > 0) {
                data.incentive_breakdown.forEach(b => {
                    const notes = (b.notes || '').toLowerCase();
                    let reason = '';
                    if (!b.has_incentive) {
                        if (notes.includes('vehicle damaged')) reason = '<span class="text-[10px] font-black uppercase text-orange-600">Damage</span>';
                        else if (notes.includes('maintenance')) reason = '<span class="text-[10px] font-black uppercase text-rose-600">Breakdown</span>';
                        else reason = '<span class="text-[10px] font-black uppercase text-slate-400">Late Turn</span>';
                    }
                    const statusColors = {paid:'text-emerald-600',shortage:'text-rose-600',excess:'text-blue-600'};
                    incentiveRowsHtml += `
                    <tr class="border-b border-slate-50 ${b.has_incentive ? '' : 'bg-rose-50/30'}">
                        <td class="p-4 font-bold text-slate-600">${new Date(b.date).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800 tracking-tight">${b.plate_number||'—'}</td>
                        <td class="p-4 font-bold text-slate-700">₱${parseFloat(b.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 font-black text-[10px] uppercase tracking-widest ${statusColors[b.status]||'text-slate-600'}">${(b.status||'')}</td>
                        <td class="p-4 text-center">${b.has_incentive ? '<span class="p-1 bg-emerald-100 text-emerald-600 rounded-lg text-[10px] font-black">EARNED</span>' : '<span class="p-1 bg-rose-100 text-rose-600 rounded-lg text-[10px] font-black">MISSED</span>'}</td>
                        <td class="p-4">${reason}</td>
                    </tr>`;
                });
            } else {
                incentiveRowsHtml = '<tr><td colspan="6" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">No active shift logs for this cycle</td></tr>';
            }

            document.getElementById('incentivesContent').innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5 transition-transform group-hover:scale-110"><i data-lucide="banknote" class="w-16 h-16 text-emerald-900"></i></div>
                        <p class="text-[9px] text-emerald-600 font-black uppercase tracking-[0.2em] mb-2">Monthly Reward</p>
                        <p class="text-2xl font-black text-emerald-900 leading-none">₱${parseFloat(data.monthly_incentive||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</p>
                        <p class="text-[10px] text-emerald-600 font-bold mt-2">5% Revenue Share</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-5 border border-blue-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5 transition-transform group-hover:scale-110"><i data-lucide="calendar-check" class="w-16 h-16 text-blue-900"></i></div>
                        <p class="text-[9px] text-blue-600 font-black uppercase tracking-[0.2em] mb-2">Service Cycles</p>
                        <p class="text-2xl font-black text-blue-900 leading-none">${data.total_shifts_month||0}</p>
                        <p class="text-[10px] text-blue-600 font-bold mt-2">${data.incentive_earned_count||0} / ${data.total_shifts_month||0} Success</p>
                    </div>
                    <div class="bg-slate-900 rounded-2xl p-5 shadow-lg shadow-slate-200 relative overflow-hidden group">
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Quality Index</p>
                        <p class="text-2xl font-black ${rateColor} leading-none">${incentiveRate}%</p>
                        <div class="w-full bg-slate-800 rounded-full h-1.5 mt-3 overflow-hidden"><div class="${rateBar} h-1.5 rounded-full transition-all duration-1000" style="width:${incentiveRate}%"></div></div>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100/50 relative overflow-hidden group">
                        <div class="absolute -right-2 -bottom-2 opacity-5"><i data-lucide="alert-triangle" class="w-16 h-16 text-rose-900"></i></div>
                        <p class="text-[9px] text-rose-600 font-black uppercase tracking-[0.2em] mb-2">Friction Points</p>
                        <div class="space-y-1 mt-1">
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Late Turn: <span>${data.late_turn_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Damage: <span>${data.damage_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Behavior: <span>${data.behavior_missed||0}</span></p>
                            <p class="text-[10px] text-rose-800 font-black uppercase tracking-tight flex justify-between">Shortage: <span>${data.shortage_missed||0}</span></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Chronological Incentive Log (Cycle: ${new Date().toLocaleString('en-PH', { month: 'long' })})</p>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th class="p-4">Timestamp</th><th class="p-4">Vessel</th><th class="p-4">Actual Coll.</th>
                                <th class="p-4">Finc. Status</th><th class="p-4 text-center">Outcome</th><th class="p-4">Factor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">${incentiveRowsHtml}</tbody>
                    </table>
                </div>`;

            // ===================== PERFORMANCE TAB =====================
            let perfRowsHtml = '';
            if (data.recent_performance && data.recent_performance.length > 0) {
                data.recent_performance.forEach(log => {
                    const shortage = parseFloat(log.shortage||0);
                    const excess   = parseFloat(log.excess||0);
                    perfRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600">${new Date(log.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800">${log.plate_number||'N/A'}</td>
                        <td class="p-4 text-slate-500 font-medium">₱${parseFloat(log.boundary_amount||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 font-black text-slate-900">₱${parseFloat(log.actual_boundary||0).toLocaleString('en-PH',{minimumFractionDigits:2})}</td>
                        <td class="p-4 text-[10px] uppercase tracking-widest ${log.status === 'paid' ? 'text-emerald-600 font-black' : log.status === 'shortage' ? 'text-rose-600 font-black' : 'text-blue-600 font-black'}">${(log.status||'')}</td>
                        <td class="p-4">${shortage > 0 ? '<span class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-lg font-black">-₱'+parseFloat(shortage).toLocaleString()+'</span>' : excess > 0 ? '<span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-lg font-black">+₱'+parseFloat(excess).toLocaleString()+'</span>' : '<span class="text-emerald-600 font-black">—</span>'}</td>
                        <td class="p-4 text-center">${log.has_incentive ? '<i data-lucide="check-circle" class="w-4 h-4 text-emerald-500 mx-auto"></i>' : '<i data-lucide="x-circle" class="w-4 h-4 text-rose-400 mx-auto"></i>'}</td>
                    </tr>`;
                });
            } else {
                perfRowsHtml = '<tr><td colspan="7" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Telemetry data unavailable</td></tr>';
            }

            // Behavior incidents section
            let incidentRowsHtml = '';
            if (data.incidents && data.incidents.length > 0) {
                const sevColors = {critical:'bg-rose-100 text-rose-700 border-rose-200',high:'bg-orange-100 text-orange-700 border-orange-200',medium:'bg-amber-100 text-amber-700 border-amber-200',low:'bg-blue-100 text-blue-700 border-blue-200'};
                data.incidents.forEach(i => {
                    incidentRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-bold text-slate-600">${new Date(i.created_at).toLocaleDateString('en-PH',{month:'short',day:'numeric'})}</td>
                        <td class="p-4 font-black text-slate-800">${i.plate_number||'—'}</td>
                        <td class="p-4"><span class="px-2 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-700">${(i.incident_type||'').replace('_',' ')}</span></td>
                        <td class="p-4"><span class="px-2 py-1 border rounded-lg text-[9px] font-black uppercase tracking-widest ${sevColors[i.severity]||'bg-slate-100 text-slate-600'}">${(i.severity||'')}</span></td>
                        <td class="p-4 text-[11px] font-bold text-slate-500 max-w-[220px] truncate" title="${i.description||''}">${i.description||''}</td>
                    </tr>`;
                });
            } else {
                incidentRowsHtml = '<tr><td colspan="5" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">No behavioral anomalies detected</td></tr>';
            }

            // Absences section
            let absenceRowsHtml = '';
            if (data.absentee_logs && data.absentee_logs.length > 0) {
                data.absentee_logs.forEach(a => {
                    absenceRowsHtml += `
                    <tr class="border-b border-slate-50 hover:bg-rose-50/20 transition-colors">
                        <td class="p-4 text-rose-600 font-black tracking-tight">${new Date(a.date).toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td>
                        <td class="p-4 text-slate-600 font-bold"><span class="px-3 py-1 bg-white border border-slate-200 rounded-xl text-[11px]">RELIEF: <strong>${a.first_name||''} ${a.last_name||''}</strong></span></td>
                        <td class="p-4 text-right"><span class="px-2 py-1 rounded-lg text-[9px] font-black tracking-[0.2em] bg-rose-100 text-rose-700 uppercase">Unattended</span></td>
                    </tr>`;
                });
            } else {
                absenceRowsHtml = '<tr><td colspan="3" class="p-8 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Perfect attendance record detected</td></tr>';
            }

            document.getElementById('performanceContent').innerHTML = `
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-slate-900 rounded-2xl p-5 border border-slate-800 shadow-xl relative overflow-hidden group">
                        <div class="absolute right-0 top-0 p-2"><i data-lucide="star" class="w-4 h-4 text-amber-400 fill-amber-400"></i></div>
                        <p class="text-[9px] text-slate-400 font-black uppercase tracking-[0.2em] mb-2">Aggregate Rating</p>
                        <p class="text-2xl font-black text-white">${data.performance_rating ? data.performance_rating.label : 'N/A'}</p>
                        <p class="text-[10px] text-amber-400 font-bold mt-1">${data.performance_rating ? '★'.repeat(data.performance_rating.stars) + '☆'.repeat(Math.max(0, 5 - data.performance_rating.stars)) : ''}</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-5 border border-rose-100 shadow-sm group">
                        <p class="text-[9px] text-rose-500 font-black uppercase tracking-[0.2em] mb-2">30D Incidents</p>
                        <p class="text-2xl font-black text-rose-900">${data.total_incidents_30d||0}</p>
                    </div>
                    <div class="bg-orange-50 rounded-2xl p-5 border border-orange-100 shadow-sm group">
                        <p class="text-[9px] text-orange-500 font-black uppercase tracking-[0.2em] mb-2">Critical Events</p>
                        <p class="text-2xl font-black text-orange-900">${data.high_severity_incidents||0}</p>
                    </div>
                </div>
                
                <div class="space-y-10">
                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="clock" class="w-3.5 h-3.5"></i> Service Continuity (Absences)</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest">
                                    <tr><th class="p-4">Schedule Date</th><th class="p-4">Relief Driver</th><th class="p-4 text-right">Status</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${absenceRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i> Telemetry History (Last 10)</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr><th class="p-4">Date</th><th class="p-4">Unit</th><th class="p-4">Target</th><th class="p-4">Actual</th><th class="p-4">Status</th><th class="p-4">Variance</th><th class="p-4 text-center">Reward</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${perfRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4 px-1">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Behavioral Logs</p>
                        </div>
                        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 text-slate-400 font-black uppercase tracking-widest border-b border-slate-100">
                                    <tr><th class="p-4">Date</th><th class="p-4">Vessel</th><th class="p-4">Classification</th><th class="p-4">Severity</th><th class="p-4">Telemetry Notes</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">${incidentRowsHtml}</tbody>
                            </table>
                        </div>
                    </div>
                </div>`;

            // ===================== INSIGHTS TAB =====================
            const score = Math.max(0, Math.min(100,
                (data.incentive_rate||0) * 0.5
                + Math.max(0, 100 - (data.total_incidents_30d||0) * 10) * 0.3
                + (data.high_severity_incidents === 0 ? 20 : 0)
            ));
            const scoreColor = score >= 80 ? 'text-emerald-600' : score >= 50 ? 'text-amber-600' : 'text-rose-600';
            const scoreBar   = score >= 80 ? 'bg-emerald-500' : score >= 50 ? 'bg-amber-400' : 'bg-rose-500';

            const eligStatus = data.is_eligible && data.is_first_week 
                ? '<div class="bg-emerald-900 border border-emerald-800 text-emerald-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="party-popper" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="sparkles" class="w-6 h-6 text-amber-400"></i> Grand Incentive Unlocked</h3><p class="text-xs font-bold text-emerald-400 tracking-wide">Driver has met all operational excellence criteria for the current cycle.</p></div>'
                : data.is_eligible && !data.is_first_week
                ? '<div class="bg-blue-900 border border-blue-800 text-blue-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="shield-check" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="timer" class="w-6 h-6 text-blue-400"></i> Excellence Track Active</h3><p class="text-xs font-bold text-blue-400 tracking-wide">Zero violations detected. Awaiting final validation during 1st cycle week.</p></div>'
                : '<div class="bg-rose-900 border border-rose-800 text-rose-50 p-6 rounded-2xl mb-6 shadow-xl relative overflow-hidden group"><div class="absolute right-0 top-0 p-4 opacity-10 group-hover:rotate-12 transition-transform"><i data-lucide="x-circle" class="w-20 h-20"></i></div><h3 class="text-xl font-black uppercase tracking-tight mb-1 flex items-center gap-2"><i data-lucide="shield-x" class="w-6 h-6 text-rose-400"></i> Eligibility Revoked</h3><p class="text-xs font-bold text-rose-400 tracking-wide">Violation anomalies detected during the evaluation lookback period.</p></div>';

            const reqList = [
                { passed: (data.violations_absences||0) === 0, text: 'Continuity: Zero Unattended Shifts' },
                { passed: data.violations_no_incentive === 0, text: 'Reliability: Perfect Boundary Discipline' },
                { passed: (!data.damage_missed && data.damage_missed === 0) && data.violations_incidents === 0, text: 'Safety: Zero Fleet Asset Damage' },
                { passed: (!data.breakdown_missed && data.breakdown_missed === 0), text: 'Maintenance: Zero Breakdown Factors' },
                { passed: data.violations_incidents === 0, text: 'Protocol: Zero Behavioral Deviations' }
            ];

            const reqsHtml = reqList.map(r => `
                <div class="flex items-center gap-4 py-3 border-b border-slate-100 last:border-0">
                    <span class="flex-shrink-0">${r.passed ? '<div class="p-1 bg-emerald-100 rounded-full"><i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i></div>' : '<div class="p-1 bg-rose-100 rounded-full"><i data-lucide="x" class="w-3.5 h-3.5 text-rose-600"></i></div>'}</span>
                    <span class="text-xs font-black uppercase tracking-widest ${r.passed ? 'text-slate-700' : 'text-rose-400 line-through'}">${r.text}</span>
                </div>
            `).join('');

            const blocksHtml = data.blocking_violations && data.blocking_violations.length > 0 
                ? '<div class="mt-6 p-4 bg-rose-50 rounded-2xl border border-rose-100 shadow-sm"><p class="text-[9px] font-black text-rose-600 uppercase tracking-[0.2em] mb-3 flex items-center gap-2"><i data-lucide="alert-octagon" class="w-3.5 h-3.5"></i> Critical Deviation Factors</p><ul class="space-y-2">' + data.blocking_violations.map(b => `<li class="text-[10px] text-rose-900 font-black uppercase tracking-tight flex items-start gap-2"><span class="w-1.5 h-1.5 bg-rose-500 rounded-full mt-1 shrink-0"></span> ${b}</li>`).join('') + '</ul></div>'
                : '<div class="mt-6 p-4 bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm"><p class="text-[9px] font-black text-emerald-700 uppercase tracking-[0.2em] text-center flex justify-center items-center gap-2"><i data-lucide="shield-check" class="w-3.5 h-3.5"></i> All Security Protocols Passed</p></div>';

            document.getElementById('insightsContent').innerHTML = `
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="space-y-4">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">Operational Excellence Dashboard</p>
                        ${eligStatus}
                        <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                            <div class="bg-amber-400 p-4 text-center shadow-lg">
                                <p class="text-slate-900 font-black text-sm uppercase tracking-[0.2em] flex justify-center items-center gap-2"><i data-lucide="gift" class="w-4 h-4"></i> Premium Reward Manifest</p>
                            </div>
                            <div class="p-6 flex gap-6 justify-center items-center">
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">🎫</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">Free<br>Coding</span></div>
                                <div class="w-px h-10 bg-slate-800"></div>
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">🍚</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">25kg Premium<br>Rice</span></div>
                                <div class="w-px h-10 bg-slate-800"></div>
                                <div class="text-center group"><span class="block text-3xl mb-1 transform group-hover:scale-125 transition-transform">💵</span><span class="text-[8px] font-black text-slate-400 uppercase leading-none">₱500 Performance<br>Cash</span></div>
                            </div>
                        </div>

                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center justify-between shadow-2xl relative overflow-hidden group">
                            <div class="absolute right-0 top-0 p-4 opacity-5 group-hover:scale-110 transition-transform"><i data-lucide="target" class="w-20 h-20 text-white"></i></div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Fleet Strategic Index</p>
                                <p class="text-xs text-slate-500 font-bold leading-tight">Composite calculation of incentive velocity<br>and safety anomalous data.</p>
                            </div>
                            <div class="text-center shrink-0">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Index Score</span>
                                <span class="text-3xl font-black ${scoreColor}">${Math.round(score)}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Excellence Verification Protocols</p>
                            <div class="divide-y divide-slate-50">${reqsHtml}</div>
                        </div>
                        ${blocksHtml}
                    </div>
                </div>
            `;

            lucide.createIcons();
        });
    }

    function closeDriverDetails() {
        document.getElementById('driverDetailsModalContainer').classList.add('scale-95');
        setTimeout(() => {
            document.getElementById('driverDetailsModal').classList.add('hidden');
        }, 150);
    }

    document.querySelectorAll('.driver-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.driver-tab').forEach(t => {
                t.classList.remove('border-blue-500', 'text-blue-600', 'active');
                t.classList.add('border-transparent', 'text-slate-400');
            });
            document.querySelectorAll('.driver-tab-panel').forEach(p => p.classList.add('hidden'));
            tab.classList.add('border-blue-500', 'text-blue-600', 'active');
            const panel = document.querySelector(`.driver-tab-panel[data-tab-panel="${tab.dataset.tab}"]`);
            if (panel) panel.classList.remove('hidden');
        });
    });
</script>
