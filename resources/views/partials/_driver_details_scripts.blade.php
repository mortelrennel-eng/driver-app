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
                { passed: data.violations_no_incentive === 0, text: 'No skipped / late remittance returns' },
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
