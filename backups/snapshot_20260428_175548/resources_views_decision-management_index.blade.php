@extends('layouts.app')

@section('page-heading', 'Franchise Decision Management')
@section('page-subheading', 'Monitor and manage franchise case status, renewals, and expiration tracking.')


@section('content')

<!-- Add/Edit Case Modal (Moved out of flow) -->
<div id="caseModal" class="<?php echo $edit_case ? 'flex' : 'hidden'; ?> fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm overflow-y-auto print:static print:bg-white print:block">
    <div class="relative w-full max-w-7xl bg-white rounded-2xl shadow-2xl overflow-hidden my-8 print:shadow-none print:my-0 flex flex-col max-h-[95vh]">
        <!-- Modal Header (Dark Navy matching Unit Management) -->
        <div class="bg-slate-800 p-4 shrink-0 print:hidden">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-white bg-opacity-20 rounded-lg">
                        <i data-lucide="<?php echo $edit_case ? 'edit' : 'plus-circle'; ?>" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight"><?php echo $edit_case ? 'Edit Franchise Case' : 'New Franchise Case'; ?></h3>
                        <p class="text-sm text-blue-100 leading-tight"><?php echo $edit_case ? 'Enter case details and authorize motor vehicles' : 'Register a new franchise case to the system'; ?></p>
                    </div>
                </div>
                <button type="button" onclick="closeCaseModal()" class="text-white hover:text-gray-200 transition-colors cursor-pointer" title="Close">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 flex-1 overflow-y-auto print:overflow-visible">
            <div id="decisionPrintArea">
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="action" value="save_case">
                    <input type="hidden" name="case_id" value="<?php echo $edit_case['id'] ?? 0; ?>">

                    <div class="flex items-center gap-2 mb-6">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900">Case Information</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Name of Applicant</label>
                            <input type="text" name="applicant_name"
                                   value="<?php echo htmlspecialchars($edit_case['applicant_name'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">CASE NO.</label>
                            <input type="text" name="case_no"
                                   value="<?php echo htmlspecialchars($edit_case['case_no'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Type of Application</label>
                            <input type="text" name="type_of_application"
                                   value="<?php echo htmlspecialchars($edit_case['type_of_application'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Denomination</label>
                            <input type="text" name="denomination"
                                   value="<?php echo htmlspecialchars($edit_case['denomination'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Date Filed</label>
                            <input type="date" name="date_filed"
                                   value="<?php echo htmlspecialchars($edit_case['date_filed'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-gray-700">Expiry Date</label>
                            <input type="date" name="expiry_date"
                                   value="<?php echo htmlspecialchars($edit_case['expiry_date'] ?? ''); ?>"
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <div class="mt-10">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <i data-lucide="car" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Authorized Units (maximum 20)</h3>
                        </div>
                        <div class="overflow-x-auto border-2 border-gray-100 rounded-xl shadow-sm">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">#</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">MAKE</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">MOTOR NO.</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">CHASIS NO.</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">PLATE NO.</th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">YEAR MODEL</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php
                                    $existing_units = $edit_units ?? [];
                                    for ($i = 0; $i < 20; $i++):
                                        $u = $existing_units[$i] ?? null;
                                    ?>
                                    <tr class="<?php echo $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'; ?> hover:bg-blue-50/30 transition-colors">
                                        <td class="px-4 py-3 text-gray-400 font-bold"><?php echo $i + 1; ?></td>
                                        <td class="px-2 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][make]"
                                                   value="<?php echo htmlspecialchars($u['make'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   placeholder="MAKE">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][motor_no]"
                                                   value="<?php echo htmlspecialchars($u['motor_no'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   placeholder="MOTOR NO.">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][chasis_no]"
                                                   value="<?php echo htmlspecialchars($u['chasis_no'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   placeholder="CHASIS NO.">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][plate_no]"
                                                   value="<?php echo htmlspecialchars($u['plate_no'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-bold text-blue-700"
                                                   placeholder="PLATE NO.">
                                        </td>
                                        <td class="px-2 py-2">
                                            <input type="text"
                                                   name="units[<?php echo $i; ?>][year_model]"
                                                   value="<?php echo htmlspecialchars($u['year_model'] ?? ''); ?>"
                                                   class="w-full px-3 py-2 border-2 border-gray-100 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                                   placeholder="YEAR">
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="p-4 border-t flex justify-end gap-3 shadow-inner bg-gray-50 shrink-0 print:hidden mt-8 -mx-6 -mb-6">
                        <button type="button"
                                onclick="printDecisionCase()"
                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-100 shadow-sm flex items-center gap-2 mr-auto border-dashed">
                            <i data-lucide="printer" class="w-4 h-4"></i>
                            <span>Print Form</span>
                        </button>
                        <button type="button"
                                onclick="closeCaseModal()"
                                class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm font-bold transition-all">
                           Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-bold shadow-lg shadow-blue-200/50 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span><?php echo $edit_case ? 'Update Case' : 'Save Franchise Case'; ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Container -->
<div class="space-y-6">

<?php
$totalCount = count($cases);
$expiredCount = 0;
$expiringSoonCount = 0;
$activeCount = 0;
$now = time();
$soon = strtotime('+1 year');

foreach ($cases as $c) {
    if (empty($c['expiry_date'])) {
        $activeCount++;
        continue;
    }
    $ts = strtotime($c['expiry_date']);
    if ($ts < $now) $expiredCount++;
    elseif ($ts <= $soon) $expiringSoonCount++;
    else $activeCount++;
}
?>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex flex-col 2xl:flex-row 2xl:items-center justify-between gap-4 mb-6">
            <!-- Left Side: Title & Stats -->
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="shrink-0">
                    <h3 class="text-xl font-black text-gray-800 tracking-tight leading-none mb-1">Franchise Directory</h3>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none mt-1">Fleet Monitoring</p>
                </div>
                
                <!-- Compact Internal Stats Row (Expanded) -->
                <div class="flex flex-wrap items-center gap-2 bg-gray-50/80 p-2 rounded-xl border border-gray-100 shrink-0">
                    <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse shadow-[0_0_5px_rgba(59,130,246,0.3)]"></div>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wide">Total</span>
                        <span class="text-sm font-black text-gray-800" id="stat-total-cases"><?php echo $totalCount; ?></span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse shadow-[0_0_5px_rgba(34,197,94,0.3)]"></div>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wide">Active</span>
                        <span class="text-sm font-black text-green-600"><?php echo $activeCount; ?></span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-400 animate-pulse shadow-[0_0_5px_rgba(251,146,60,0.3)]"></div>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wide">Soon</span>
                        <span class="text-sm font-black text-orange-600"><?php echo $expiringSoonCount; ?></span>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-xl shadow-sm border border-red-500/20">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-600 animate-pulse shadow-[0_0_8px_rgba(220,38,38,0.6)]"></div>
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-wide text-red-600/70">Expired</span>
                        <span class="text-sm font-black text-red-600"><?php echo $expiredCount; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Right Side: Search & Actions -->
            <div class="flex flex-col sm:flex-row items-center gap-3 w-full 2xl:w-auto">
                <!-- Advanced Search Bar -->
                <div class="relative w-full sm:w-auto sm:min-w-[260px] flex-grow 2xl:flex-grow-0">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" id="franchiseSearch" 
                           placeholder="Search Case #, Applicant, Plate..." 
                           class="w-full h-10 pl-10 pr-4 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:bg-white transition-all shadow-sm outline-none"
                           onkeyup="filterFranchiseItems()">
                </div>
                
                <!-- Status Filter -->
                <div class="relative w-full sm:w-auto min-w-[140px]">
                    <select id="statusFilter" 
                            class="w-full h-10 px-4 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:bg-white transition-all shadow-sm appearance-none cursor-pointer pr-10 outline-none"
                            onchange="filterFranchiseItems()">
                        <option value="all">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="expiring">Expiring Soon</option>
                        <option value="expired">Expired Only</option>
                    </select>
                    <i data-lucide="filter" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                </div>

                <button onclick="openCaseModal()" class="w-full sm:w-auto h-10 px-5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg hover:from-yellow-600 hover:to-yellow-700 flex items-center justify-center gap-2 text-sm font-bold shadow-md transition-all shrink-0">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>NEW CASE</span>
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">CASE NO.</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Applicant</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Type</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Denomination</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Date Filed</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Expiry Date</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"># Units</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500 bg-white">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="folder-open" class="w-10 h-10 text-gray-300 mb-2"></i>
                                    <p>No franchise cases found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: 
                        // Group cases by Expiry Date for better organization
                        $groupedCases = [];
                        foreach ($cases as $c) {
                            $expiry = empty($c['expiry_date']) ? 'No Expiry Date Set' : date('F j, Y', strtotime($c['expiry_date']));
                            $groupedCases[$expiry][] = $c;
                        }

                        // Sort the groups by date, pushing 'No Expiry Date' to the end or beginning
                        uksort($groupedCases, function($a, $b) {
                            if ($a === 'No Expiry Date Set') return 1;
                            if ($b === 'No Expiry Date Set') return -1;
                            return strtotime($a) - strtotime($b);
                        });
                    ?>
                        <?php foreach ($groupedCases as $expiry => $group): 
                            // Calculate status relative to today
                            $isExpired = false;
                            $isExpiringSoon = false;
                            if ($expiry !== 'No Expiry Date Set') {
                                $ts = strtotime($expiry);
                                $now = time();
                                if ($ts < $now) {
                                    $isExpired = true;
                                } elseif ($ts <= strtotime('+1 year')) {
                                    $isExpiringSoon = true;
                                }
                            }
                            
                            // Determine styles based on status
                            $bgGradient = 'from-yellow-50 to-white';
                            $borderColor = 'border-yellow-200';
                            $iconBg = 'bg-yellow-100';
                            $iconColor = 'text-yellow-700';
                            $textColor = 'text-yellow-600';
                            $badgeColor = 'border-yellow-200 text-yellow-700';
                            $animation = '';
                            $statusLabel = '';

                            if ($isExpired) {
                                $bgGradient = 'from-red-100 to-white';
                                $borderColor = 'border-red-400';
                                $iconBg = 'bg-red-200';
                                $iconColor = 'text-red-700';
                                $textColor = 'text-red-600';
                                $badgeColor = 'border-red-400 text-red-800 bg-red-50';
                                $animation = 'animate-pulse shadow-[0_0_15px_rgba(239,68,68,0.5)]'; // Blinking red glow effect
                                $statusLabel = '<span class="ml-3 px-2 py-0.5 text-[10px] uppercase font-black bg-red-600 text-white rounded animate-bounce">EXPIRED</span>';
                            } elseif ($isExpiringSoon) {
                                $bgGradient = 'from-orange-100 to-white';
                                $borderColor = 'border-orange-300';
                                $iconBg = 'bg-orange-200';
                                $iconColor = 'text-orange-700';
                                $textColor = 'text-orange-600';
                                $badgeColor = 'border-orange-300 text-orange-800 bg-orange-50';
                                $statusLabel = '<span class="ml-3 px-2 py-0.5 text-[10px] uppercase font-bold bg-orange-500 text-white rounded">RENEWAL ADVANCE ALERT</span>';
                            }
                        ?>
                            <!-- Group Header for Expiry Date -->
                            <tr class="bg-gradient-to-r <?php echo $bgGradient; ?> border-y <?php echo $borderColor; ?> <?php echo $isExpired ? 'relative z-10' : ''; ?> group-header" data-expiry-group="<?php echo htmlspecialchars($expiry); ?>">
                                <td colspan="8" class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 <?php echo $iconBg; ?> <?php echo $iconColor; ?> rounded my-1 shadow-sm border <?php echo $borderColor; ?> <?php echo $animation; ?>">
                                            <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center">
                                                <span class="text-xs <?php echo $textColor; ?> font-bold uppercase tracking-wider">EXPIRY DATE</span>
                                                <?php echo $statusLabel; ?>
                                            </div>
                                            <span class="text-base font-black text-gray-900"><?php echo htmlspecialchars($expiry); ?></span>
                                        </div>
                                        <div class="ml-auto flex items-center gap-2">
                                            <span class="px-3 py-1 text-xs font-bold bg-white border <?php echo $badgeColor; ?> rounded-full shadow-sm">
                                                <?php echo count($group); ?> <?php echo count($group) === 1 ? 'Case' : 'Cases'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                             <!-- Display Cases under this Expiry Date -->
                            <?php foreach ($group as $c): 
                                $statusKey = 'active';
                                if ($isExpired) $statusKey = 'expired';
                                elseif ($isExpiringSoon) $statusKey = 'expiring';
                                
                                // Collect all plate numbers for this case to make them searchable
                                $plates = implode(' ', array_column($c['units'] ?? [], 'plate_no'));
                            ?>
                                <tr class="border-b transition-colors hover:bg-yellow-50/50 cursor-pointer bg-white franchise-row"
                                    data-case-no="<?php echo strtolower($c['case_no']); ?>"
                                    data-applicant="<?php echo strtolower($c['applicant_name']); ?>"
                                    data-plates="<?php echo strtolower($plates); ?>"
                                    data-status="<?php echo $statusKey; ?>"
                                    data-expiry-group="<?php echo htmlspecialchars($expiry); ?>"
                                    onclick="document.getElementById('units-<?php echo $c['id']; ?>').classList.toggle('hidden')">
                                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($c['case_no']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-blue-700"><?php echo htmlspecialchars($c['applicant_name']); ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($c['type_of_application']); ?></td>
                                    <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($c['denomination']); ?></td>
                                    <td class="px-4 py-3 text-xs text-gray-500 font-medium whitespace-nowrap">
                                        <?php echo !empty($c['date_filed']) ? date('M d, Y', strtotime($c['date_filed'])) : 'N/A'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-bold text-gray-700 whitespace-nowrap">
                                        <?php echo !empty($c['expiry_date']) ? date('M d, Y', strtotime($c['expiry_date'])) : '---'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-gray-700 font-bold text-xs ring-1 ring-gray-200">
                                            <?php echo (int)($c['unit_count'] ?? 0); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <!-- Document Print Preview (Printer) -->
                                            <button type="button" 
                                                    class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" 
                                                    title="Print Preview Document" 
                                                    onclick="event.stopPropagation(); openDocPreviewModal(<?php
                                                        $previewData = [
                                                            'case_no'       => $c['case_no'] ?? '',
                                                            'applicant'     => $c['applicant_name'] ?? '',
                                                            'type'          => $c['type_of_application'] ?? '',
                                                            'denomination'  => $c['denomination'] ?? '',
                                                            'date_filed'    => !empty($c['date_filed']) ? date('F j, Y', strtotime($c['date_filed'])) : 'N/A',
                                                            'expiry_date'   => !empty($c['expiry_date']) ? date('F j, Y', strtotime($c['expiry_date'])) : 'N/A',
                                                            'units'         => $c['units'] ?? [],
                                                        ];
                                                        echo htmlspecialchars(json_encode($previewData), ENT_QUOTES, 'UTF-8');
                                                    ?>);">
                                                <i data-lucide="printer" class="w-4 h-4 pointer-events-none"></i>
                                            </button>

                                            <button type="button" class="px-3 py-1.5 text-[10px] uppercase font-bold tracking-wider bg-transparent text-slate-600 border border-slate-300 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-all inline-flex items-center gap-1.5 whitespace-nowrap">
                                                <i data-lucide="chevron-down" class="w-3 h-3"></i> Units
                                            </button>
                                            <span onclick="event.stopPropagation(); window.location.href='<?php echo base_url('decision-management?id=' . $c['id']); ?>'" class="px-3 py-1.5 text-[10px] uppercase font-bold tracking-wider bg-transparent text-slate-600 border border-slate-300 rounded-lg hover:border-slate-400 hover:bg-slate-50 transition-all inline-flex items-center gap-1.5 whitespace-nowrap cursor-pointer">
                                                Edit
                                            </span>
                                            <form method="POST" onsubmit="event.stopPropagation(); return confirm('Are you sure you want to completely delete this franchise case?');">
                                                <input type="hidden" name="action" value="delete_case">
                                                <input type="hidden" name="case_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Delete Case">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Dropdown Units Row -->
                                <tr id="units-<?php echo $c['id']; ?>" class="hidden bg-slate-50 border-b-2 border-slate-200 shadow-inner">
                                    <td colspan="8" class="p-4">
                                        <div class="pl-8 border-l-4 border-yellow-400">
                                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Attached Units (<?php echo count($c['units'] ?? []); ?>)</h4>
                                            
                                            <?php if(empty($c['units'])): ?>
                                                <p class="text-sm text-gray-500 italic py-2">No units attached to this case.</p>
                                            <?php else: ?>
                                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                                                    <table class="min-w-full text-xs text-left">
                                                        <thead class="bg-gray-100 text-gray-600">
                                                            <tr>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">MAKE</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">MOTOR NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">CHASSIS NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider">PLATE NO.</th>
                                                                <th class="px-4 py-2 font-black uppercase tracking-wider text-center">YEAR</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-100">
                                                            <?php foreach($c['units'] as $u): ?>
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($u['make'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-700 font-mono"><?php echo htmlspecialchars($u['motor_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-700 font-mono"><?php echo htmlspecialchars($u['chasis_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-blue-700 font-black tracking-wider"><?php echo htmlspecialchars($u['plate_no'] ?? ''); ?></td>
                                                                <td class="px-4 py-2 text-gray-900 font-bold text-center"><?php echo htmlspecialchars($u['year_model'] ?? ''); ?></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Document Print Preview Modal -->
<div id="docPreviewModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[200] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[95vh] flex flex-col overflow-hidden">
        <div class="bg-slate-800 px-5 py-4 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <i data-lucide="file-text" class="w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="text-white font-black text-base leading-tight" id="docPreviewTitle">FRANCHISE DOCUMENT</p>
                    <p class="text-blue-200 text-xs leading-tight" id="docPreviewSubtitle">Print Preview</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="printDocPreview()" class="flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-400 text-white text-xs font-black rounded-lg transition-colors">
                    <i data-lucide="printer" class="w-4 h-4"></i> PRINT
                </button>
                <button onclick="closeDocPreviewModal()" class="p-2 text-white hover:text-gray-200 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto bg-gray-100 p-6">
            <div id="docPreviewContent" class="bg-white shadow-lg mx-auto" style="max-width:660px; min-height:900px; padding:48px; font-family: 'Times New Roman', serif;">
                <div style="text-align:center; border-bottom:4px double #1e293b; padding-bottom:16px; margin-bottom:24px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#64748b;">Republic of the Philippines</p>
                    <p style="font-size:11px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#64748b;">Department of Transportation</p>
                    <p style="font-size:18px; font-weight:900; letter-spacing:.05em; text-transform:uppercase; color:#0f172a; margin-top:4px;">Land Transportation Franchising</p>
                    <p style="font-size:18px; font-weight:900; letter-spacing:.05em; text-transform:uppercase; color:#0f172a;">&amp; Regulatory Board</p>
                    <p style="font-size:11px; letter-spacing:.05em; color:#64748b; margin-top:4px;">NCR &mdash; East Cluster</p>
                </div>
                <div style="text-align:center; margin-bottom:32px;">
                    <p style="font-size:10px; font-weight:700; letter-spacing:.15em; text-transform:uppercase; color:#94a3b8; margin-bottom:4px;">Certificate of Public Convenience</p>
                    <h2 style="font-size:26px; font-weight:900; color:#0f172a; letter-spacing:.05em; margin:0;" id="dp-case-no">CASE NO.</h2>
                    <p style="font-size:12px; color:#64748b; margin-top:4px;" id="dp-type">Type of Application</p>
                </div>
                <table style="width:100%; border-collapse:collapse; margin-bottom:32px; font-size:13px;">
                    <tr style="background:#f8fafc;">
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:700; color:#475569; width:35%; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Applicant / Operator</td>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:900; color:#0f172a;" id="dp-applicant">&mdash;</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:700; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Denomination</td>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:600; color:#1e293b;" id="dp-denomination">&mdash;</td>
                    </tr>
                    <tr style="background:#f8fafc;">
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:700; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Date Filed</td>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:600; color:#1e293b;" id="dp-date-filed">&mdash;</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:700; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em;">Expiry Date</td>
                        <td style="padding:10px 14px; border:1px solid #e2e8f0; font-weight:900; color:#dc2626;" id="dp-expiry">&mdash;</td>
                    </tr>
                </table>
                <p style="font-size:11px; font-weight:900; letter-spacing:.1em; text-transform:uppercase; color:#64748b; margin-bottom:8px;">Authorized Motor Vehicles</p>
                <table style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom:40px;">
                    <thead>
                        <tr style="background:#1e293b; color:white;">
                            <th style="padding:8px 12px; text-align:left; font-size:10px; letter-spacing:.05em;">#</th>
                            <th style="padding:8px 12px; text-align:left; font-size:10px; letter-spacing:.05em;">MAKE</th>
                            <th style="padding:8px 12px; text-align:left; font-size:10px; letter-spacing:.05em;">PLATE NO.</th>
                            <th style="padding:8px 12px; text-align:left; font-size:10px; letter-spacing:.05em;">MOTOR NO.</th>
                            <th style="padding:8px 12px; text-align:left; font-size:10px; letter-spacing:.05em;">CHASSIS NO.</th>
                            <th style="padding:8px 12px; text-align:center; font-size:10px; letter-spacing:.05em;">YEAR</th>
                        </tr>
                    </thead>
                    <tbody id="dp-units-tbody">
                        <tr><td colspan="6" style="padding:16px; text-align:center; color:#94a3b8; font-style:italic;">No units attached.</td></tr>
                    </tbody>
                </table>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:48px; margin-top:48px;">
                    <div style="text-align:center;">
                        <div style="border-top:2px solid #1e293b; padding-top:8px;">
                            <p style="font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; color:#1e293b;">Applicant Signature</p>
                            <p style="font-size:10px; color:#64748b; margin-top:2px;">Over Printed Name</p>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        <div style="border-top:2px solid #1e293b; padding-top:8px;">
                            <p style="font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.05em; color:#1e293b;">Authorized Official</p>
                            <p style="font-size:10px; color:#64748b; margin-top:2px;">LTFRB Regional Director</p>
                        </div>
                    </div>
                </div>
                <div style="text-align:center; margin-top:48px; border-top:1px solid #e2e8f0; padding-top:12px;">
                    <p style="font-size:10px; color:#94a3b8;">This document is computer-generated. Validity is subject to LTFRB terms and conditions.</p>
                    <p style="font-size:10px; color:#cbd5e1; margin-top:2px;">EuroTaxi Fleet Management System &mdash; Franchise Directory</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openCaseModal() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        window.location.href = '<?php echo base_url('decision-management'); ?>';
        return;
    }
    document.getElementById('caseModal').classList.remove('hidden');
    document.getElementById('caseModal').classList.add('flex');
}

function closeCaseModal() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('id')) {
        window.location.href = '<?php echo base_url('decision-management'); ?>';
    } else {
        document.getElementById('caseModal').classList.add('hidden');
        document.getElementById('caseModal').classList.remove('flex');
    }
}

function printDecisionCase() {
    const area = document.getElementById('decisionPrintArea');
    if (!area) {
        window.print();
        return;
    }

    const printContents = area.innerHTML;
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        window.print();
        return;
    }

    printWindow.document.write(`<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decision Case Print</title>
    <style>
        body { background-color: #fff; padding: 24px; font-family: system-ui, sans-serif; }
        .print\\:hidden { display: none !important; }
        input { border: none !important; background: transparent !important; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div style="max-width: 900px; margin: 0 auto; text-align: center; margin-bottom: 20px;">
        <h2>FRANCHISE CASE DETAILS</h2>
    </div>
    <div style="max-width: 900px; margin: 0 auto;">
        ${printContents}
    </div>
</body>
</html>`);

    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => { printWindow.print(); }, 200);
}

function openDocPreviewModal(data) {
    document.getElementById('dp-case-no').textContent = data.case_no || 'N/A';
    document.getElementById('dp-applicant').textContent = data.applicant || 'N/A';
    document.getElementById('dp-type').textContent = data.type || 'N/A';
    document.getElementById('dp-denomination').textContent = data.denomination || 'N/A';
    document.getElementById('dp-date-filed').textContent = data.date_filed || 'N/A';
    document.getElementById('dp-expiry').textContent = data.expiry_date || 'N/A';

    const tbody = document.getElementById('dp-units-tbody');
    tbody.innerHTML = '';

    if (data.units && data.units.length > 0) {
        data.units.forEach((u, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9;">${index + 1}</td>
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9;">${u.make || ''}</td>
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9; font-weight:700;">${u.plate_no || ''}</td>
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9;">${u.motor_no || ''}</td>
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9;">${u.chasis_no || ''}</td>
                <td style="padding:8px 12px; border-bottom:1px solid #f1f5f9; text-align:center;">${u.year_model || ''}</td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="6" style="padding:16px; text-align:center; color:#94a3b8; font-style:italic;">No units attached.</td></tr>';
    }

    document.getElementById('docPreviewModal').classList.remove('hidden');
    document.getElementById('docPreviewModal').classList.add('flex');
}

function closeDocPreviewModal() {
    document.getElementById('docPreviewModal').classList.add('hidden');
    document.getElementById('docPreviewModal').classList.remove('flex');
}

function printDocPreview() {
    const content = document.getElementById('docPreviewContent').innerHTML;
    const win = window.open('', '_blank');
    win.document.write(`<!DOCTYPE html><html><head><title>Franchise Document</title>
    <style>body{margin:0;padding:0;font-family:'Times New Roman',serif;} @media print{body{margin:0;}}</style>
    </head><body>${content}</body></html>`);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); }, 300);
}

document.getElementById('docPreviewModal').addEventListener('click', function(e) {
    if (e.target === this) closeDocPreviewModal();
});

document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") {
        closeCaseModal();
        closeDocPreviewModal();
    }
});

function filterFranchiseItems() {
    const query = document.getElementById('franchiseSearch').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('.franchise-row');
    const headers = document.querySelectorAll('.group-header');
    
    const visibleGroups = new Set();
    let visibleCount = 0;

    rows.forEach(row => {
        const caseNo = row.dataset.caseNo;
        const applicant = row.dataset.applicant;
        const plates = row.dataset.plates;
        const status = row.dataset.status;
        const group = row.dataset.expiryGroup;

        const matchesQuery = caseNo.includes(query) || applicant.includes(query) || plates.includes(query);
        const matchesStatus = statusFilter === 'all' || status === statusFilter;

        if (matchesQuery && matchesStatus) {
            row.classList.remove('hidden');
            visibleGroups.add(group);
            visibleCount++;
        } else {
            row.classList.add('hidden');
            const detailID = row.id.split('-')[1];
            const detailRow = document.getElementById(`units-${detailID}`);
            if (detailRow) detailRow.classList.add('hidden');
        }
    });

    headers.forEach(header => {
        const groupName = header.dataset.expiryGroup;
        if (visibleGroups.has(groupName)) {
            header.classList.remove('hidden');
        } else {
            header.classList.add('hidden');
        }
    });

    const totalBadge = document.getElementById('stat-total-cases');
    if (totalBadge) totalBadge.textContent = visibleCount;
}
</script>
@endsection
