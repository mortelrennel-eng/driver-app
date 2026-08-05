@extends('layouts.app')
@section('title', 'Accident Reports - Euro System')
@section('page-heading', 'Accident / SOS Reports')
@section('page-subheading', 'Real-time incident reports sent from driver mobile app')

@section('content')
<style>
    .stat-card-premium { @apply transition-all duration-500 cursor-default; }
    
    /* Dashboard Wave CSS */
    @keyframes drawChart { 0% { clip-path: inset(0 100% 0 0); opacity: 0; } 100% { clip-path: inset(0 0 0 0); opacity: 1; } }
    .card-hover::after {
        content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 75px; background-size: 100% 100%; background-repeat: no-repeat; opacity: 0; transition: none !important; z-index: 0;
    }
    .wave-red::after { background-image: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 50" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><polygon fill="rgba(239,68,68,0.15)" stroke="rgba(239,68,68,0.4)" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="miter" points="0,50 0,35 15,20 30,30 45,10 60,25 75,5 90,15 100,0 100,50" /></svg>'); }
    .card-hover.in-view::after { animation: drawChart 1s ease-out forwards !important; }
</style>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card-hover in-view wave-red cursor-default group relative overflow-hidden rounded-2xl shadow-sm border border-red-100 bg-gradient-to-br from-red-50 to-rose-50/70">
        <div class="relative p-3.5 sm:p-5 flex items-center justify-between z-20">
            <div class="flex-1 min-w-0">
                <p class="text-red-400 text-[9px] sm:text-[10px] font-black uppercase tracking-widest mb-1">Total Accident Reports</p>
                <p class="text-gray-900 text-xl sm:text-3xl font-black leading-none mb-1">{{ count($accident_reports) }}</p>
            </div>
            <div class="p-1.5 sm:p-3 bg-red-100 rounded-xl sm:rounded-2xl border border-red-200 shadow-sm flex-shrink-0">
                <i data-lucide="alert-octagon" class="w-5 h-5 sm:w-7 sm:h-7 text-red-600"></i>
            </div>
        </div>
        <i data-lucide="alert-octagon" stroke-width="1" class="absolute right-0 bottom-0 w-24 h-24 -rotate-12 pointer-events-none" style="opacity: 0.15 !important; color: #ef4444 !important; z-index: 5 !important;"></i>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <i data-lucide="shield-alert" class="w-4 h-4 text-red-500"></i>
            <h3 class="font-black text-sm text-gray-800 uppercase tracking-widest">Accident Reports Feed</h3>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-64">
                <input type="search" id="accidentSearchInput" class="block w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-red-500 focus:border-red-500" placeholder="Search driver, description...">
                <i data-lucide="search" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
            </div>
            <div class="relative w-full sm:w-48">
                <input type="date" id="accidentDateFilter" class="block w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-red-500 focus:border-red-500">
                <i data-lucide="calendar" class="absolute left-3 top-2.5 w-4 h-4 text-gray-400"></i>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-50 border-separate" style="border-spacing: 0 8px; padding: 0 10px;">
            <thead class="bg-transparent">
                <tr>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Date / Time</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver & Unit</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Type</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Description</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Location</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-5 py-3 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y-0">
                @forelse($accident_reports as $r)
                @php 
                    $finalDescription = $r->description ?: $r->notes; 
                    // Remove the prefix that API creates for SOS combined reports
                    $finalDescription = preg_replace('/Emergency Alert triggered by driver.*?Description:\s*/s', '', $finalDescription);
                    // Also clean up if it was a direct accident report without SOS
                    $finalDescription = preg_replace('/Damage Level:.*?Description:\s*/s', '', $finalDescription);
                @endphp
                <tr class="bg-white shadow-sm border border-gray-100 rounded-xl cursor-pointer hover:-translate-y-1 hover:shadow-lg hover:border-red-200 transition-all duration-300 {{ $r->status === 'pending' ? 'bg-red-50/30 border-red-100' : '' }}" 
                    onclick="openAccidentModal({{ json_encode([
                        'id' => $r->id,
                        'date' => \Carbon\Carbon::parse($r->created_at)->format('M d, Y h:i A'),
                        'driver' => trim(($r->driver->first_name ?? '') . ' ' . ($r->driver->last_name ?? '')),
                        'unit' => $r->unit->plate_number ?? 'UNKNOWN',
                        'type' => $r->accident_type ?? 'Emergency',
                        'description' => trim($finalDescription) ?: 'No description provided.',
                        'status' => strtoupper($r->status),
                        'latitude' => $r->latitude,
                        'longitude' => $r->longitude,
                        'photo_path' => $r->photo_path ? asset($r->photo_path) : null
                    ]) }})">
                    <td class="px-5 py-4 rounded-l-xl border-y border-l border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        <div class="text-xs font-black text-gray-800">{{ \Carbon\Carbon::parse($r->created_at)->format('M d, Y') }}</div>
                        <div class="text-[10px] text-gray-500">{{ \Carbon\Carbon::parse($r->created_at)->format('h:i A') }}</div>
                    </td>
                    <td class="px-5 py-4 border-y border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        <div class="text-xs font-black text-gray-800">{{ $r->driver->first_name ?? '' }} {{ $r->driver->last_name ?? '' }}</div>
                        <div class="text-[10px] font-black text-blue-600 uppercase">{{ $r->unit->plate_number ?? 'UNKNOWN' }}</div>
                    </td>
                    <td class="px-5 py-4 border-y border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full border border-red-200 bg-red-100 text-red-700">
                            {{ $r->accident_type ?? 'Emergency' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 border-y border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        <div class="text-xs text-gray-600 max-w-[200px] truncate">{{ $finalDescription ?: 'No description' }}</div>
                        @if($r->photo_path)
                            <div class="text-[9px] text-blue-500 font-bold flex items-center gap-1 mt-1"><i data-lucide="image" class="w-3 h-3"></i> Has Photo</div>
                        @endif
                    </td>
                    <td class="px-5 py-4 border-y border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        @if($r->latitude && $r->longitude)
                            <div class="text-xs text-gray-800 font-medium mb-1 max-w-[200px] truncate reverse-geocode" data-lat="{{ $r->latitude }}" data-lng="{{ $r->longitude }}" id="addr-{{ $r->id }}">Fetching address...</div>
                            <a href="https://www.google.com/maps?q={{ $r->latitude }},{{ $r->longitude }}" target="_blank" onclick="event.stopPropagation()" class="text-[10px] font-black uppercase tracking-widest text-blue-600 hover:underline flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3"></i> View Map
                            </a>
                        @else
                            <span class="text-[10px] font-medium text-gray-400">No Location</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 border-y border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        @if($r->status === 'pending')
                            <span class="flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span> PENDING</span>
                        @else
                            <span class="text-[10px] font-black uppercase tracking-widest text-green-600">✓ {{ $r->status }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right rounded-r-xl border-y border-r border-gray-100 {{ $r->status === 'pending' ? 'border-red-100' : '' }}">
                        <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                            @if($r->status === 'pending')
                            <form action="{{ route('accident-alerts.acknowledge', $r->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-green-100 hover:scale-105 transition-all">Ack ✓</button>
                            </form>
                            @endif
                            <form action="{{ route('driver-behavior.archive-accident', $r->id) }}" method="POST" onsubmit="return confirm('Archive this accident report?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors">
                                    <i data-lucide="archive" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center">
                        <i data-lucide="check-circle" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
                        <p class="text-sm font-medium text-gray-500">No accident reports found.</p>
                        <p class="text-xs text-gray-400">Accident SOS alerts from the driver app will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Accident Report Modal -->
<div id="accidentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="accidentModalContent">
        <div class="bg-red-600 px-6 py-4 flex items-center justify-between">
            <h3 class="text-white font-black text-lg tracking-wider uppercase flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                Accident Report Details
            </h3>
            <button onclick="closeAccidentModal()" class="text-white/80 hover:text-white transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[80vh]">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Driver</p>
                    <p class="text-sm font-bold text-gray-800" id="modDriver">-</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Unit / Plate</p>
                    <p class="text-sm font-bold text-blue-600 uppercase" id="modUnit">-</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Date & Time</p>
                    <p class="text-sm font-bold text-gray-800" id="modDate">-</p>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</p>
                    <p class="text-sm font-black" id="modStatus">-</p>
                </div>
            </div>
            
            <div class="mb-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Description</p>
                <div class="bg-red-50/50 border border-red-100 p-4 rounded-xl text-sm text-gray-700 leading-relaxed font-medium" id="modDesc">
                    -
                </div>
            </div>
            
            <div class="mb-6" id="modPhotoContainer" style="display: none;">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Attached Photo</p>
                <div class="rounded-xl overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center">
                    <img id="modPhoto" src="" alt="Accident Photo" class="max-w-full h-auto max-h-[300px] object-contain">
                </div>
            </div>
            
            <div id="modLocationContainer" style="display: none;" class="mb-6">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Location Address</p>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-3">
                    <p class="text-sm font-bold text-gray-800" id="modAddress">Fetching address...</p>
                </div>
                <a id="modLocationLink" href="#" target="_blank" class="w-full flex items-center justify-center gap-2 bg-blue-50 text-blue-700 py-3 rounded-xl border border-blue-200 hover:bg-blue-100 transition-colors font-black text-xs uppercase tracking-widest">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> View Exact Location on Google Maps
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function openAccidentModal(data) {
        document.getElementById('modDriver').textContent = data.driver;
        document.getElementById('modUnit').textContent = data.unit;
        document.getElementById('modDate').textContent = data.date;
        document.getElementById('modDesc').textContent = data.description;
        
        const statusEl = document.getElementById('modStatus');
        statusEl.textContent = data.status;
        if (data.status === 'PENDING') {
            statusEl.className = 'text-sm font-black text-red-600';
        } else {
            statusEl.className = 'text-sm font-black text-green-600';
        }
        
        const photoContainer = document.getElementById('modPhotoContainer');
        const photoEl = document.getElementById('modPhoto');
        if (data.photo_path) {
            photoEl.src = data.photo_path;
            photoContainer.style.display = 'block';
        } else {
            photoContainer.style.display = 'none';
        }
        
        const locationContainer = document.getElementById('modLocationContainer');
        const locationLink = document.getElementById('modLocationLink');
        const addressEl = document.getElementById('modAddress');
        
        if (data.latitude && data.longitude) {
            locationLink.href = `https://www.google.com/maps?q=${data.latitude},${data.longitude}`;
            locationContainer.style.display = 'block';
            
            // Get cached address from table row
            const tableAddr = document.getElementById('addr-' + data.id);
            if (tableAddr && tableAddr.textContent !== 'Fetching address...') {
                addressEl.textContent = tableAddr.textContent;
            } else {
                addressEl.textContent = 'Fetching address...';
                // Fallback fetch if not yet loaded in table
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${data.latitude}&lon=${data.longitude}&zoom=18&addressdetails=1`)
                    .then(res => res.json())
                    .then(resData => {
                        addressEl.textContent = resData.display_name || 'Address not found';
                    })
                    .catch(() => {
                        addressEl.textContent = 'Coordinates: ' + data.latitude + ', ' + data.longitude;
                    });
            }
        } else {
            locationContainer.style.display = 'none';
        }
        
        const modal = document.getElementById('accidentModal');
        const modalContent = document.getElementById('accidentModalContent');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger animations
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modalContent.classList.remove('scale-95');
        }, 10);
    }
    
    function closeAccidentModal() {
        const modal = document.getElementById('accidentModal');
        const modalContent = document.getElementById('accidentModalContent');
        
        modal.classList.add('opacity-0');
        modalContent.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    async function processTableGeocoding() {
        const elements = document.querySelectorAll('.reverse-geocode');
        for (let i = 0; i < elements.length; i++) {
            const el = elements[i];
            const lat = el.getAttribute('data-lat');
            const lng = el.getAttribute('data-lng');
            if (lat && lng) {
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
                    const data = await response.json();
                    el.textContent = data.display_name || 'Address not found';
                    el.setAttribute('title', data.display_name);
                } catch (e) {
                    el.textContent = 'Coordinates: ' + lat + ', ' + lng;
                }
                // Wait 1.1s to respect Nominatim rate limit (max 1 req/sec)
                await new Promise(r => setTimeout(r, 1100));
            }
        }
    }
    document.addEventListener('DOMContentLoaded', processTableGeocoding);

    // Filter Logic
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('accidentSearchInput');
        const dateFilter = document.getElementById('accidentDateFilter');
        const tableBody = document.querySelector('table tbody');
        const rows = tableBody.querySelectorAll('tr:not(.no-results)');

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const dateTerm = dateFilter.value; // YYYY-MM-DD format
            
            let visibleCount = 0;

            rows.forEach(row => {
                // If this is the "No reports" row, ignore it
                if (row.querySelector('td[colspan]')) return;

                const textContent = row.textContent.toLowerCase();
                const dateText = row.querySelector('td:first-child')?.textContent || '';
                
                // For date filtering: convert 'Jul 02, 2026' to YYYY-MM-DD
                let matchesDate = true;
                if (dateTerm) {
                    const parsedRowDate = new Date(dateText.split('\n')[0]);
                    if (!isNaN(parsedRowDate)) {
                        const rowDateStr = parsedRowDate.getFullYear() + '-' + 
                                        String(parsedRowDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                        String(parsedRowDate.getDate()).padStart(2, '0');
                        matchesDate = (rowDateStr === dateTerm);
                    }
                }

                const matchesSearch = textContent.includes(searchTerm);

                if (matchesSearch && matchesDate) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle no results
            let noResultsRow = tableBody.querySelector('.no-results-filter');
            if (visibleCount === 0 && rows.length > 0 && !rows[0].querySelector('td[colspan]')) {
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results-filter';
                    noResultsRow.innerHTML = `
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500 text-sm">
                            No accident reports match your search criteria.
                        </td>
                    `;
                    tableBody.appendChild(noResultsRow);
                }
                noResultsRow.style.display = '';
            } else if (noResultsRow) {
                noResultsRow.style.display = 'none';
            }
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (dateFilter) dateFilter.addEventListener('change', filterTable);
    });
</script>
@endsection
