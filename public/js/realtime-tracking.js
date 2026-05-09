let map;
let markers = {};
let selectedUnitId = null;
let followingUnitId = null; // Track if we are following a specific unit
let updateInterval;
let isUpdating = false;

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    startTracking();
    
    // Search and Filter listeners
    document.getElementById('unitSearchInput').addEventListener('keyup', filterUnitsItems);
    document.getElementById('statusFilterSelect').addEventListener('change', filterUnitsItems);
});

function initMap() {
    // Default center — Metro Manila
    const defaultCenter = [14.5995, 120.9842];

    // Luzon bounding box (SW → NE)
    // Covers all of Luzon including Batanes in the north,
    // Mindoro/Palawan border in the south.
    const luzonBounds = L.latLngBounds(
        L.latLng(11.8, 116.5),   // SW: just below Mindoro
        L.latLng(20.9, 127.5)    // NE: Batanes + east coast
    );

    map = L.map('mapViewer', {
        zoomControl:          false,
        minZoom:              8,              // zoom 8 ≈ all of Luzon visible
        maxZoom:              19,
        maxBounds:            luzonBounds,
        maxBoundsViscosity:   1.0,            // hard pan lock at Luzon edges
    }).setView(defaultCenter, 12);

    // Store globally so the blade JS can resize & switch tiles
    window.liveMap = map;

    window.defaultTileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    });
    window.defaultTileLayer.addTo(map);

    // MMDA Restricted Zones Logic (Visual lines removed as per user request)
    const restrictedZonesGroup = L.layerGroup(); // Not added to map
    drawRestrictedZones(restrictedZonesGroup);

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    // Stop following if user manually drags map
    map.on('movestart', function() {
        // We only stop following if it was a USER drag, not an automated flyTo
        // However, Leaflet doesn't easily distinguish. 
        // We skip clearing if we are in the middle of a flyTo.
    });
}

function startTracking() {
    updateFleetData();
    // Poll every 5 seconds to match Tracksolid API real-time push
    updateInterval = setInterval(updateFleetData, 5000);
}

async function updateFleetData() {
    if (isUpdating) return;
    isUpdating = true;

    try {
        const response = await fetch('/live-tracking/units-live', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        const data = await response.json();

        if (data.success) {
            updateStatsUI(data.stats);
            updateMapAndList(data.units);
            
            // Mark last successful update
            const apiStatus = document.querySelector('.api-status-text');
            if (apiStatus) {
                apiStatus.textContent = 'API Online';
                apiStatus.className = 'api-status-text text-[10px] font-black text-green-600 uppercase';
            }

            // Auto-follow logic
            if (followingUnitId && markers[followingUnitId]) {
                const latlng = markers[followingUnitId].getLatLng();
                map.panTo(latlng, { animate: true, duration: 1 });
            }
        }
    } catch (error) {
        console.error('Tracking Update Failed:', error);
    } finally {
        isUpdating = false;
    }
}

function updateStatsUI(stats) {
    document.getElementById('stat-total').textContent = stats.total;
    document.getElementById('stat-active').textContent = stats.moving;
    document.getElementById('stat-idle').textContent = stats.idle;
    document.getElementById('stat-stopped').textContent = stats.stopped;
    document.getElementById('stat-offline').textContent = stats.offline;
}

function updateMapAndList(units) {
    units.forEach(unit => {
        // 1. Update List Item Status
        updateListItemUI(unit);

        // 2. Update Map Marker
        if (unit.latitude && unit.longitude) {
            updateMarker(unit);
        } else {
            // Remove marker if it exists but unit has no coordinates
            if (markers[unit.unit_id]) {
                map.removeLayer(markers[unit.unit_id]);
                delete markers[unit.unit_id];
            }
        }
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
    
    // 3. Dynamically Sort the Sidebar List
    sortUnitList();

    // 4. Re-apply Search Filters (Persistence Fix)
    filterUnitsItems();
}

function sortUnitList() {
    const listContainer = document.getElementById('unitList');
    if (!listContainer) return;
    
    const items = Array.from(listContainer.querySelectorAll('.unit-item'));
    
    const weightMap = {
        'moving': 1,
        'idle': 2,
        'stopped': 3,
        'offline': 4 // default offline
    };
    
    items.sort((a, b) => {
        const unitIdA = a.dataset.unitId;
        const unitIdB = b.dataset.unitId;
        const statusA = a.dataset.status;
        const statusB = b.dataset.status;
        
        let wA = weightMap[statusA] || 5;
        let wB = weightMap[statusB] || 5;
        
        // If offline, check if it actually has a GPS marker right now
        if (statusA === 'offline') {
            wA = markers[unitIdA] ? 4 : 5;
        }
        if (statusB === 'offline') {
            wB = markers[unitIdB] ? 4 : 5;
        }
        
        // Primary Sort: Status Weights
        if (wA !== wB) {
            return wA - wB;
        }
        
        // Secondary Sort: Alphabetical by Plate Number
        const plateA = (a.dataset.plateNumber || '').toLowerCase();
        const plateB = (b.dataset.plateNumber || '').toLowerCase();
        return plateA.localeCompare(plateB);
    });
    
    // Re-append items to enforce completely new DOM order
    items.forEach(item => listContainer.appendChild(item));
}

function updateListItemUI(unit) {
    const item = document.querySelector(`.unit-item[data-unit-id="${unit.unit_id}"]`);
    if (!item) return;

    // Update status dataset
    item.dataset.status = unit.gps_status;
    
    const badgeContainer = item.querySelector('.status-badge');
    let badgeHtml = '';

    if (unit.gps_status === 'moving') {
        badgeHtml = `<span class="px-2 py-1 text-[10px] font-black uppercase tracking-tighter rounded-lg bg-green-50 text-green-700 border border-green-100">Moving</span>`;
    } else if (unit.gps_status === 'idle') {
        badgeHtml = `<span class="px-2 py-1 text-[10px] font-black uppercase tracking-tighter rounded-lg bg-yellow-50 text-yellow-700 border border-yellow-100">Idle</span>`;
    } else if (unit.gps_status === 'stopped') {
        badgeHtml = `<span class="px-2 py-1 text-[10px] font-black uppercase tracking-tighter rounded-lg bg-blue-50 text-blue-700 border border-blue-100">Stopped</span>`;
    } else {
        badgeHtml = `<span class="px-2 py-1 text-[10px] font-black uppercase tracking-tighter rounded-lg bg-gray-50 text-gray-500 border border-gray-100">Offline</span>`;
    }

    badgeContainer.innerHTML = badgeHtml;
    
    // Update Drivers display (Handle Dual Drivers)
    const primarySpan = item.querySelector('.driver-primary');
    const secondarySpan = item.querySelector('.driver-secondary');
    const secondaryContainer = item.querySelector('.secondary-driver-container');
    
    if (primarySpan) primarySpan.textContent = unit.driver_name || 'No Primary Driver';
    if (secondarySpan) secondarySpan.textContent = unit.secondary_driver || '';
    
    // Toggle secondary container visibility
    if (secondaryContainer) {
        if (!unit.secondary_driver || unit.secondary_driver.trim() === '') {
            secondaryContainer.classList.add('hidden');
        } else {
            secondaryContainer.classList.remove('hidden');
        }
    }

    // Update Speed
    const speedElem = item.querySelector('.unit-speed');
    if (speedElem) {
        speedElem.textContent = parseFloat(unit.speed || 0).toFixed(1);
    }

    // Update Engine Status
    const engineContainer = item.querySelector(`#engine-status-container-${unit.unit_id}`);
    if (engineContainer) {
        const zapIcon = engineContainer.querySelector('i[data-lucide="zap"]');
        const engineText = engineContainer.querySelector('span');
        
        if (unit.ignition_status) {
            if (zapIcon) zapIcon.classList.replace('text-gray-300', 'text-green-500');
            if (engineText) engineText.textContent = 'Engine ON';
        } else {
            if (zapIcon) zapIcon.classList.replace('text-green-500', 'text-gray-300');
            if (engineText) engineText.textContent = 'Engine OFF';
        }
    }
    
    // Update opacity for offline
    if (unit.gps_status === 'offline') {
        item.classList.add('opacity-70');
    } else {
        item.classList.remove('opacity-70');
    }

}

async function getAddress(lat, lng) {
// ... (rest of the file follows) ...
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
            headers: { 'Accept-Language': 'en' }
        });
        const data = await response.json();
        return data.display_name || "Address not found";
    } catch (e) {
        return "Address service unavailable";
    }
}

function updateMarker(unit) {
    const isOffline = unit.gps_status === 'offline';
    // Mute colors if offline (gray out)
    const carBodyColor = isOffline ? '#9CA3AF' : '#EAB308';
    const roofColor = isOffline ? '#D1D5DB' : '#FEF08A';
    
    // Status Indicator Dot (Green/Yellow/Red/Gray)
    let dotColor = '#9CA3AF'; // offline
    if (unit.gps_status === 'moving') dotColor = '#22c55e';
    if (unit.gps_status === 'idle') dotColor = '#eab308';
    if (unit.gps_status === 'stopped') dotColor = '#ef4444';

    const carIconValue = `
        <div class="relative flex flex-col items-center justify-center marker-wrapper" style="width: 60px; height: 60px;">
            <!-- Floating Plate Number Badge -->
            <div class="absolute -top-5 px-2 py-0.5 bg-yellow-500 border-yellow-600 text-white font-black text-[10px] rounded shadow-md border whitespace-nowrap z-50 pointer-events-none transition-transform hover:scale-110 drop-shadow-md">
                ${unit.plate_number}
                <!-- Tiny status dot -->
                <div class="absolute -right-1.5 -top-1.5 w-3 h-3 rounded-full border-2 border-white shadow-sm" style="background-color: ${dotColor};"></div>
            </div>

            <!-- Taxi Car Body (Rotates with Heading) -->
            <div style="transform: rotate(${unit.angle}deg); transition: transform 0.5s ease-out;" class="drop-shadow-lg pointer-events-auto cursor-pointer flex items-center justify-center">
                <svg width="24" height="42" viewBox="0 0 24 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Tires -->
                    <rect x="0" y="6" width="3" height="8" rx="1" fill="#1F2937"/>
                    <rect x="21" y="6" width="3" height="8" rx="1" fill="#1F2937"/>
                    <rect x="0" y="28" width="3" height="8" rx="1" fill="#1F2937"/>
                    <rect x="21" y="28" width="3" height="8" rx="1" fill="#1F2937"/>
                    
                    <!-- Main Body -->
                    <rect x="2" y="2" width="20" height="38" rx="6" fill="${carBodyColor}" stroke="#713F12" stroke-width="0.5"/>
                    
                    <!-- Front Windshield -->
                    <path d="M4 12 L20 12 L18 8 L6 8 Z" fill="#111827" opacity="0.8"/>
                    
                    <!-- Rear Windshield -->
                    <path d="M5 30 L19 30 L18 34 L6 34 Z" fill="#111827" opacity="0.8"/>
                    
                    <!-- Roof -->
                    <rect x="4" y="14" width="16" height="14" rx="2" fill="${roofColor}"/>
                    
                    <!-- Taxi Sign -->
                    <rect x="8" y="18" width="8" height="4" rx="1" fill="white" stroke="#374151" stroke-width="0.5"/>
                    
                    <!-- Headlights -->
                    <circle cx="5" cy="3" r="1.5" fill="${isOffline ? '#D1D5DB' : '#FEF08A'}"/>
                    <circle cx="19" cy="3" r="1.5" fill="${isOffline ? '#D1D5DB' : '#FEF08A'}"/>
                    
                    <!-- Taillights -->
                    <rect x="4" y="39" width="4" height="2" rx="0.5" fill="#EF4444"/>
                    <rect x="16" y="39" width="4" height="2" rx="0.5" fill="#EF4444"/>
                </svg>
            </div>
            
            ${unit.gps_status === 'moving' ? '<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-8 h-8 bg-green-400 rounded-full animate-ping opacity-30 pointer-events-none z-0"></div>' : ''}
        </div>
    `;

    const carIcon = L.divIcon({
        className: 'custom-div-icon bg-transparent border-0',
        html: carIconValue,
        iconSize: [60, 60],
        iconAnchor: [30, 30] // center
    });


    if (markers[unit.unit_id]) {
        markers[unit.unit_id].setLatLng([unit.latitude, unit.longitude]);
        markers[unit.unit_id].setIcon(carIcon);

        // Keep popup in view as unit moves — re-trigger autoPan if near viewport edge
        if (markers[unit.unit_id].isPopupOpen()) {
            const pt  = map.latLngToContainerPoint(markers[unit.unit_id].getLatLng());
            const sz  = map.getSize();
            const POPUP_RIGHT_EDGE = 380; // offset(170) + popup width(300) - buffer
            const EDGE_PAD = 60;
            const nearEdge = pt.x < EDGE_PAD
                          || pt.x > sz.x - POPUP_RIGHT_EDGE
                          || pt.y < EDGE_PAD
                          || pt.y > sz.y - EDGE_PAD;
            if (nearEdge) {
                markers[unit.unit_id].openPopup(); // re-triggers autoPan
            }
        }
    } else {
        const marker = L.marker([unit.latitude, unit.longitude], { icon: carIcon }).addTo(map);
        marker.on('click', function() {
            // Auto-lock onto the selected unit on map click without scrolling sidebar
            followingUnitId = unit.unit_id;
            
            // Wait for popup to open then update button text
            setTimeout(() => {
                const followBtn = marker._popup?._contentNode?.querySelector('button[onclick^="toggleFollow"]');
                if (followBtn) {
                    followBtn.textContent = 'Following';
                    followBtn.className = 'text-[10px] font-black uppercase tracking-widest text-yellow-600 hover:underline';
                }
            }, 100);
        });
        markers[unit.unit_id] = marker;
    }

    // Popup content - Upgraded for Pro Look
    const popupContent = `
        <div class="p-4 min-w-[280px] font-sans">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                <div class="flex flex-col">
                    <div class="font-black text-gray-900 text-xl tracking-tight">${unit.plate_number}</div>
                </div>
                <div class="px-3 py-1 rounded-full bg-gray-50 text-[10px] font-black text-gray-500 uppercase border border-gray-100">${unit.gps_status}</div>
            </div>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0 border border-blue-100">
                        <i data-lucide="user" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <div class="text-[9px] text-gray-400 font-black uppercase tracking-widest leading-none mb-1">Current Driver</div>
                        <div class="font-black text-gray-800 text-base leading-none">${unit.driver_name}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100/50">
                        <div class="text-gray-400 font-black uppercase text-[9px] tracking-widest mb-1">Speed</div>
                        <div class="text-lg font-black text-gray-900 leading-none">${unit.speed} <span class="text-xs text-gray-400 font-bold">km/h</span></div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100/50">
                        <div class="text-gray-400 font-black uppercase text-[9px] tracking-widest mb-1">Ignition</div>
                        <div class="text-lg font-black ${unit.ignition_status ? 'text-green-600' : 'text-gray-400'} leading-none">${unit.ignition_status ? 'ON' : 'OFF'}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-yellow-50/30 p-3 rounded-2xl border border-yellow-100/20">
                        <div class="text-yellow-600 font-black uppercase text-[9px] tracking-widest mb-1">Today's Dist.</div>
                        <div class="text-base font-black text-gray-800 leading-none" id="daily-dist-${unit.unit_id}">
                            ${unit.daily_dist} <span class="text-[10px] text-gray-400 font-bold">km</span>
                        </div>
                    </div>
                    <div class="bg-blue-50/20 p-3 rounded-2xl border border-blue-100/10">
                        <div class="text-blue-500 font-black uppercase text-[9px] tracking-widest mb-1">Total ODO</div>
                        <div class="text-base font-black text-gray-900 leading-none">
                            ${parseFloat(unit.odo || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})} <span class="text-[9px] text-gray-400">km</span>
                            <div class="text-[8px] text-blue-400 font-bold mt-1" id="age-${unit.unit_id}">Calculating age...</div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/30 p-3 rounded-2xl border border-blue-100/20">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="map-pin" class="w-3 h-3 text-blue-500"></i>
                        <div class="text-blue-400 font-black uppercase text-[9px] tracking-widest">Current Location</div>
                    </div>
                    <div class="text-[11px] font-bold text-gray-600 leading-tight address-text" id="address-${unit.unit_id}">
                        Loading address...
                    </div>
                </div>

                <!-- Engine Control -->
                <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-gray-100/50">
                    <button onclick="toggleEngineControl(${unit.unit_id}, 'kill', this)" class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 hover:border-red-600 transition-colors py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1 shadow-sm">
                        <i data-lucide="power-off" class="w-3 h-3"></i> Kill Engine
                    </button>
                    <button onclick="toggleEngineControl(${unit.unit_id}, 'restore', this)" class="bg-green-50 hover:bg-green-500 text-green-600 hover:text-white border border-green-200 hover:border-green-500 transition-colors py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-1 shadow-sm">
                        <i data-lucide="power" class="w-3 h-3"></i> Restore
                    </button>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-gray-50 mt-3">
                    <div class="flex flex-col">
                        <div class="text-[10px] text-gray-400 font-bold italic">
                            Sync: ${unit.last_update || 'N/A'}
                        </div>
                        ${unit.gps_status === 'offline' && unit.offline_display ? `
                        <div class="text-[10px] text-red-500 font-black uppercase tracking-widest mt-0.5">
                            Offline for: ${unit.offline_display}
                        </div>
                        ` : ''}
                    </div>
                    <button onclick="toggleFollow(${unit.unit_id})" class="text-[10px] font-black uppercase tracking-widest ${followingUnitId == unit.unit_id ? 'text-yellow-600' : 'text-blue-600'} hover:underline">
                        ${followingUnitId == unit.unit_id ? 'Following' : 'Follow Unit'}
                    </button>
                </div>
            </div>
        </div>
    `;

    if (markers[unit.unit_id].getPopup()) {
        markers[unit.unit_id].getPopup().setContent(popupContent);
    } else {
        markers[unit.unit_id].bindPopup(popupContent, {
            className: 'pro-popup',
            maxWidth: 300,
            offset: [220, 200],         // 220px right so marker stays visible, 200px down to vertically centre
            autoPan: true,
            autoPanPaddingTopLeft:     L.point(60, 140), // extra top room
            autoPanPaddingBottomRight: L.point(60,  60),
        });
    }

    // Fetch address on popup open
    markers[unit.unit_id].on('popupopen', async function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        const addressEl = document.getElementById(`address-${unit.unit_id}`);
        if (addressEl && addressEl.textContent.trim() === 'Loading address...') {
            const addr = await getAddress(unit.latitude, unit.longitude);
            addressEl.textContent = addr;
        }

        // Fetch age and sync mileage (one-time fetch per popup open)
        const ageEl = document.getElementById(`age-${unit.unit_id}`);
        if (ageEl && ageEl.textContent.trim() === 'Calculating age...') {
            syncUnitStats(unit.unit_id);
        }
    });
}

async function syncUnitStats(unitId) {
    try {
        const response = await fetch(`/live-tracking/unit-mileage/${unitId}`);
        const data = await response.json();
        
        // 1. Update Age
        const ageEl = document.getElementById(`age-${unitId}`);
        if (ageEl && data.success && data.age) {
            ageEl.textContent = `${data.age} months old`;
        } else if (ageEl) {
            ageEl.textContent = 'N/A';
        }

        // 2. Hybrid Sync: Update Today's Dist. with official API distance immediately
        const distEl = document.getElementById(`daily-dist-${unitId}`);
        if (distEl && data.success && data.mileage !== undefined) {
            distEl.innerHTML = `${data.mileage} <span class="text-[10px] text-gray-400 font-bold">km</span>`;
        }
    } catch (e) {
        console.error('Sync Error:', e);
    }
}

function selectUnitItem(unitId) {
    const previousSelection = document.querySelector('.unit-item.selected');
    if (previousSelection) previousSelection.classList.remove('selected');

    const item = document.querySelector(`.unit-item[data-unit-id="${unitId}"]`);
    if (item) {
        item.classList.add('selected');
        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    selectedUnitId = unitId;
    
    // Auto-lock onto the selected unit
    followingUnitId = unitId;
    
    // Zoom to Marker
    if (markers[unitId]) {
        const latlng = markers[unitId].getLatLng();
        map.flyTo(latlng, 16);
        markers[unitId].openPopup();
        
        // Re-render the popup content to physically show the "Following" button state
        const marker = markers[unitId];
        if (marker._popup && marker._popup._contentNode) {
            const followBtn = marker._popup._contentNode.querySelector('button[onclick^="toggleFollow"]');
            if (followBtn) {
                followBtn.textContent = 'Following';
                followBtn.className = 'text-[10px] font-black uppercase tracking-widest text-yellow-600 hover:underline';
            }
        }
    }
}

// Global scope for onclick in HTML
window.selectUnit = function(el) {
    const unitId = el.dataset.unitId;
    selectUnitItem(unitId);
};

window.toggleFollow = function(unitId) {
    if (followingUnitId == unitId) {
        followingUnitId = null; // Turn off follow
        
        // Revert button text in popup
        const marker = markers[unitId];
        if (marker && marker._popup && marker._popup._contentNode) {
            const followBtn = marker._popup._contentNode.querySelector('button[onclick^="toggleFollow"]');
            if (followBtn) {
                followBtn.textContent = 'Follow Unit';
                followBtn.className = 'text-[10px] font-black uppercase tracking-widest text-blue-600 hover:underline';
            }
        }
    } else {
        selectUnitItem(unitId); // Turn on follow (auto locks)
    }
};

function filterUnitsItems() {
    const search = document.getElementById('unitSearchInput').value.toLowerCase().trim();
    const status = document.getElementById('statusFilterSelect').value;

    document.querySelectorAll('.unit-item').forEach(el => {
        const plateNum = (el.dataset.plateNumber || '').toLowerCase();
        const driverName = (el.dataset.driverName || '').toLowerCase();
        const secondaryDriver = (el.dataset.secondaryDriver || '').toLowerCase();
        const unitStatus = el.dataset.status;

        // Search in plate number OR primary driver OR secondary driver
        const matchSearch = !search || 
                           plateNum.includes(search) || 
                           driverName.includes(search) || 
                           secondaryDriver.includes(search);
        
        let matchStatus = true;
        if (status === 'active') {
            matchStatus = ['moving', 'idle', 'stopped'].includes(unitStatus);
        } else if (status === 'offline') {
            matchStatus = unitStatus === 'offline';
        }

        el.style.display = (matchSearch && matchStatus) ? '' : 'none';
        
        // Add visual indicator if hidden by status but matches search
        if (search && !matchStatus && matchSearch) {
            // Optional: we can force show it if it matches search even if status differs
            // but for now we follow the user's logic of strict filtering.
        }
    });
}

function drawRestrictedZones(group) {
    const zones = {
        makati: [
            [14.5670, 121.0000], [14.5650, 121.0450], [14.5350, 121.0400], [14.5380, 121.0100]
        ],
        roads: {
            'EDSA': [
                [14.6575, 121.0039], [14.6349, 121.0331], [14.6186, 121.0506], [14.5880, 121.0560], [14.5540, 121.0240], [14.5370, 121.0000]
            ],
            'C5': [
                [14.6850, 121.0400], [14.6300, 121.0750], [14.5600, 121.0650], [14.5200, 121.0480], [14.4800, 121.0450]
            ],
            'Roxas Blvd': [
                [14.5900, 120.9750], [14.5500, 120.9850], [14.5200, 120.9920]
            ]
        }
    };

    // Draw Makati Polygon
    L.polygon(zones.makati, {
        color: '#ef4444',
        weight: 1,
        fillColor: '#ef4444',
        fillOpacity: 0.1,
        dashArray: '5, 5'
    }).addTo(group).bindTooltip("Makati Coding Zone (No Window)");

    // Draw Major Roads with Buffers (Simplified as thick lines)
    for (const [name, path] of Object.entries(zones.roads)) {
        L.polyline(path, {
            color: '#ef4444',
            weight: 12, // Visual buffer
            opacity: 0.15,
            lineCap: 'round'
        }).addTo(group).bindTooltip(`${name} Restricted Road`);
    }
}

// Global scope for engine control
window.toggleEngineControl = async function(unitId, action, btn) {
    const originalText = btn.innerHTML;
    const isKill = action === 'kill';
    
    // Quick double-check UI logic without password
    if (isKill && confirm("WARNING: Are you sure you want to CUT OFF the engine for this unit? Ensure the vehicle is in a safe location.") === false) {
        return;
    }

    // Set loading state
    btn.innerHTML = `<i data-lucide="loader-2" class="w-3 h-3 animate-spin"></i> Sending...`;
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');

    try {
        const response = await fetch('/live-tracking/engine-control', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ unit_id: unitId, action: action })
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Command Sent!',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Command Failed',
                text: data.error || 'The command was rejected by the API.',
            });
        }
    } catch (e) {
        console.error(e);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Could not connect to the server.'
        });
    } finally {
        // Restore button state
        btn.innerHTML = originalText;
        if (typeof lucide !== 'undefined') lucide.createIcons();
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }
};
