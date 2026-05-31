let map;
let markers = {};
let selectedUnitId = null;
let followingUnitId = null;
let updateInterval;
let isUpdating = false;
let suppressAddressReset = false; // Flag to prevent popupopen from resetting address during auto-update

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    startTracking();
    
    // Search: listen to both the contenteditable display div AND the hidden input's dispatched keyup
    const searchDisplay = document.getElementById('unitSearchDisplay');
    const searchInput   = document.getElementById('unitSearchInput');
    const statusFilter  = document.getElementById('statusFilterSelect');

    if (searchDisplay) {
        searchDisplay.addEventListener('input', filterUnitsItems);
        searchDisplay.addEventListener('keyup', filterUnitsItems);
    }
    // The blade JS bridge dispatches 'keyup' on the hidden input — listen there too
    if (searchInput) {
        searchInput.addEventListener('keyup', filterUnitsItems);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUnitsItems);
    }
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
        maxZoom:              22,             // allow deep zoom
        maxBounds:            luzonBounds,
        maxBoundsViscosity:   1.0,            // hard pan lock at Luzon edges
    }).setView(defaultCenter, 12);

    // Store globally so the blade JS can resize & switch tiles
    window.liveMap = map;

    // Default: Google Maps with Live Traffic
    window.googleTrafficLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=m,traffic&x={x}&y={y}&z={z}', {
        attribution: '&copy; Google Maps',
        maxNativeZoom: 20,
        maxZoom: 22
    });

    // Register as defaultTileLayer so map-type-switcher in blade can restore it
    window.defaultTileLayer = window.googleTrafficLayer;

    window.googleTrafficLayer.addTo(map);

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

let geocodeQueue = [];
let isGeocoding = false;

async function processGeocodeQueue() {
    if (isGeocoding || geocodeQueue.length === 0) return;
    isGeocoding = true;
    
    const { lat, lng, unitId, resolve } = geocodeQueue.shift();
    
    try {
        const addr = await getAddress(lat, lng, unitId, false);
        resolve(addr);
    } catch (e) {
        resolve("Address service unavailable");
    }
    
    setTimeout(() => {
        isGeocoding = false;
        processGeocodeQueue();
    }, 1100); // Nominatim 1-second rate limit safety
}

function queueAddressFetch(lat, lng, unitId) {
    return new Promise(resolve => {
        geocodeQueue.push({ lat, lng, unitId, resolve });
        processGeocodeQueue();
    });
}

function startTracking() {
    updateFleetData();
    // Poll every 5 seconds to match Tracksolid API real-time push
    updateInterval = setInterval(updateFleetData, 5000);

    // ==========================================
    // THE BULLETPROOF ADDRESS GUARDIAN
    // ==========================================
    // Leaflet has a fatal flaw where setIcon() recreates the DOM and resets it to the default string.
    // This Guardian runs independently of Leaflet and forces the DOM to show the cached address.
    setInterval(() => {
        Object.keys(markers).forEach(unitId => {
            const marker = markers[unitId];
            if (!marker || !marker.isPopupOpen()) return;
            
            const addrEl = document.getElementById(`address-${unitId}`);
            if (!addrEl) return;
            
            const txt = addrEl.textContent.trim();
            
            // If it says Loading or Unavailable, try to force the cache in
            if (txt === 'Loading address...' || txt === 'Address service unavailable' || txt === '') {
                const cached = unitAddressCache[unitId];
                if (cached) {
                    addrEl.textContent = cached;
                    addrEl.style.color = '#374151'; // Ensure it doesn't look faded
                    // Also force it into the Leaflet internal string so future redraws have it
                    if (marker._popup) {
                        marker._popup._content = marker._popup._content.replace('Loading address...', cached);
                    }
                }
            } else if (unitAddressCache[unitId] && txt !== unitAddressCache[unitId]) {
                // Failsafe: if the DOM somehow has garbage, force it back to the strict cached value
                addrEl.textContent = unitAddressCache[unitId];
            }
        });
    }, 250);

    // Hardening: Pause polling when browser tab is sent to background
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(updateInterval);
        } else {
            clearInterval(updateInterval);
            updateFleetData();
            updateInterval = setInterval(updateFleetData, 5000);
        }
    });
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
            
            // Mark last successful update time
            const apiStatus = document.querySelector('.api-status-text');
            if (apiStatus) {
                apiStatus.textContent = 'API Online';
                apiStatus.className = 'api-status-text text-[10px] font-black text-green-600 uppercase';
            }

            // Auto-follow logic: pan map to followed unit
            if (followingUnitId && markers[followingUnitId]) {
                const latlng = markers[followingUnitId].getLatLng();
                map.panTo(latlng, { animate: true, duration: 1 });
            }
        } else {
            // Mark API as degraded if response comes back but not success
            const apiStatus = document.querySelector('.api-status-text');
            if (apiStatus) {
                apiStatus.textContent = 'API Error';
                apiStatus.className = 'api-status-text text-[10px] font-black text-red-500 uppercase';
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

    // Update Speed — always safe: offline units guaranteed to be 0 from server
    const speedElem = item.querySelector('.unit-speed');
    if (speedElem) {
        const safeSpeed = Math.max(0, parseFloat(unit.speed) || 0);
        speedElem.textContent = safeSpeed.toFixed(1);
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

// --- Persistent Address Cache (survives auto-refresh, backed by localStorage) ---
const addressCache = {}; // In-memory fast cache: Key "lat3,lng3" (3 decimal = ~111m tolerance)
const unitAddressCache = {}; // Per-unit last known address

// Load previously saved addresses from localStorage on startup
try {
    const saved = JSON.parse(localStorage.getItem('eurotaxi_address_cache') || '{}');
    Object.assign(addressCache, saved);
    // Explicitly wipe the poisonous unit cache from older versions
    localStorage.removeItem('eurotaxi_unit_address_cache');
} catch(e) { /* localStorage unavailable, proceed with empty cache */ }

function saveAddressCache() {
    try {
        // Only save string values (not pending Promises)
        const toSave = {};
        for (const k in addressCache) {
            if (typeof addressCache[k] === 'string') toSave[k] = addressCache[k];
        }
        localStorage.setItem('eurotaxi_address_cache', JSON.stringify(toSave));
    } catch(e) { /* ignore */ }
}

async function getAddress(lat, lng, unitId) {
    // Use 3 decimal places (~111m tolerance) to absorb GPS drift for stationary vehicles
    const cacheKey = `${Number(lat).toFixed(3)},${Number(lng).toFixed(3)}`;
    // Also check 4-decimal for moving vehicles where precision matters
    const preciseCacheKey = `${Number(lat).toFixed(4)},${Number(lng).toFixed(4)}`;

    // 1. Check precise in-memory cache first (fastest)
    if (typeof addressCache[preciseCacheKey] === 'string') {
        return Promise.resolve(addressCache[preciseCacheKey]);
    }
    // 2. Check drift-tolerant cache (covers GPS micro-drift on parked vehicles)
    if (typeof addressCache[cacheKey] === 'string') {
        addressCache[preciseCacheKey] = addressCache[cacheKey]; // promote to precise key
        return Promise.resolve(addressCache[cacheKey]);
    }
    // 3. If already fetching (Promise in-flight), wait for it
    if (addressCache[preciseCacheKey] instanceof Promise) {
        return addressCache[preciseCacheKey];
    }

    const promise = fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
        headers: { 
            'Accept-Language': 'en',
            'User-Agent': 'EuroTaxiSystem-Geocoding-Hardened'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    })
    .then(data => {
        const addr = data.display_name || "Address not found";
        addressCache[cacheKey] = addr;
        addressCache[preciseCacheKey] = addr;
        if (unitId) unitAddressCache[unitId] = addr;
        saveAddressCache(); // Persist to localStorage
        return addr;
    })
    .catch(e => {
        delete addressCache[preciseCacheKey];
        // If we have any fallback, use it rather than showing error
        if (unitId && unitAddressCache[unitId]) return unitAddressCache[unitId];
        return "Address service unavailable";
    });
    
    addressCache[preciseCacheKey] = promise;
    return promise;
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

    // === CAPTURE POPUP STATE BEFORE ANY LEAFLET OPERATIONS ===
    // setIcon() in Leaflet can temporarily change popup state, so we must save everything first.
    const wasPopupOpen = !!(markers[unit.unit_id] && markers[unit.unit_id].isPopupOpen());

    if (markers[unit.unit_id]) {
        const oldLatLng = markers[unit.unit_id].getLatLng();
        const hasMoved = oldLatLng.lat !== parseFloat(unit.latitude) || oldLatLng.lng !== parseFloat(unit.longitude);
        
        if (hasMoved) {
            markers[unit.unit_id].setLatLng([unit.latitude, unit.longitude]);
        }

        const currentIconHtml = markers[unit.unit_id].options.icon.options.html;
        if (currentIconHtml !== carIconValue) {
            markers[unit.unit_id].setIcon(carIcon);
        }

        // Keep popup in view as unit moves — re-trigger autoPan if near viewport edge
        if (markers[unit.unit_id].isPopupOpen() && hasMoved) {
            const pt  = map.latLngToContainerPoint(markers[unit.unit_id].getLatLng());
            const sz  = map.getSize();
            const POPUP_RIGHT_EDGE = 380;
            const EDGE_PAD = 60;
            const nearEdge = pt.x < EDGE_PAD
                          || pt.x > sz.x - POPUP_RIGHT_EDGE
                          || pt.y < EDGE_PAD
                          || pt.y > sz.y - EDGE_PAD;
            if (nearEdge) {
                markers[unit.unit_id].openPopup();
            }
        }
    } else {
        const marker = L.marker([unit.latitude, unit.longitude], { icon: carIcon }).addTo(map);
        marker.on('click', function() {
            followingUnitId = unit.unit_id;
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

    // Resolve the best available address for this unit RIGHT NOW
    const bestAddress = unitAddressCache[unit.unit_id]
        || addressCache[`${Number(unit.latitude).toFixed(3)},${Number(unit.longitude).toFixed(3)}`]
        || addressCache[`${Number(unit.latitude).toFixed(4)},${Number(unit.longitude).toFixed(4)}`]
        || null;

    // Popup content - always uses best available address so template never shows "Loading address..." unnecessarily
    const popupContent = `
        <div class="p-4 min-w-[280px] font-sans pro-popup-container">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-3">
                <div class="flex flex-col">
                    <div class="font-black text-gray-900 text-xl tracking-tight">${unit.plate_number}</div>
                </div>
                <div class="px-3 py-1 rounded-full bg-gray-50 text-[10px] font-black text-gray-500 uppercase border border-gray-100 popup-status-badge">${unit.gps_status}</div>
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
                        <div class="text-lg font-black text-gray-900 leading-none">
                            <span class="popup-speed-val">${Math.max(0, parseFloat(unit.speed) || 0).toFixed(1)}</span> <span class="text-xs text-gray-400 font-bold">km/h</span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-2xl border border-gray-100/50">
                        <div class="text-gray-400 font-black uppercase text-[9px] tracking-widest mb-1">Ignition</div>
                        <div class="text-lg font-black popup-ign-val ${unit.ignition_status ? 'text-green-600' : 'text-gray-400'} leading-none">${unit.ignition_status ? 'ON' : 'OFF'}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-yellow-50/30 p-3 rounded-2xl border border-yellow-100/20">
                        <div class="text-yellow-600 font-black uppercase text-[9px] tracking-widest mb-1">Today's Dist.</div>
                        <div class="text-base font-black text-gray-800 leading-none popup-dist-val">
                            ${unit.daily_dist} <span class="text-[10px] text-gray-400 font-bold">km</span>
                        </div>
                    </div>
                    <div class="bg-blue-50/20 p-3 rounded-2xl border border-blue-100/10">
                        <div class="text-blue-500 font-black uppercase text-[9px] tracking-widest mb-1">Total ODO</div>
                        <div class="text-base font-black text-gray-900 leading-none">
                            <span class="popup-odo-val">${parseFloat(unit.odo || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}</span> <span class="text-[9px] text-gray-400">km</span>
                            <div class="text-[8px] text-blue-400 font-bold mt-1 popup-age-val" id="age-${unit.unit_id}">Calculating age...</div>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50/30 p-3 rounded-2xl border border-blue-100/20">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="map-pin" class="w-3 h-3 text-blue-500"></i>
                        <div class="text-blue-400 font-black uppercase text-[9px] tracking-widest">Current Location</div>
                    </div>
                    <div class="text-[11px] font-bold text-gray-600 leading-tight address-text popup-address-val" id="address-${unit.unit_id}">
                        ${bestAddress || 'Loading address...'}
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
                        <div class="text-[10px] text-gray-400 font-bold italic popup-sync-val">
                            Sync: ${unit.last_update || 'N/A'}
                        </div>
                        <div class="text-[10px] text-red-500 font-black uppercase tracking-widest mt-0.5 popup-offline-val" style="${unit.gps_status === 'offline' && unit.offline_display ? '' : 'display:none'}">
                            Offline for: ${unit.offline_display || ''}
                        </div>
                    </div>
                    <button onclick="toggleFollow(${unit.unit_id})" class="text-[10px] font-black uppercase tracking-widest ${followingUnitId == unit.unit_id ? 'text-yellow-600' : 'text-blue-600'} hover:underline">
                        ${followingUnitId == unit.unit_id ? 'Following' : 'Follow Unit'}
                    </button>
                </div>
            </div>
        </div>
    `;

    if (markers[unit.unit_id].getPopup()) {
        const popup = markers[unit.unit_id].getPopup();
        
        // 1. Silently update Leaflet's internal content so any future redraws use the latest HTML (prevents "Loading address..." reversion)
        popup._content = popupContent;

        // 2. Perform surgical DOM update if the popup is visibly open right now
        if (wasPopupOpen && popup._contentNode) {
            const node = popup._contentNode;

            const statusBadge = node.querySelector('.popup-status-badge');
            if (statusBadge) statusBadge.textContent = unit.gps_status;

            const speedEl = node.querySelector('.popup-speed-val');
            if (speedEl) speedEl.textContent = Math.max(0, parseFloat(unit.speed) || 0).toFixed(1);
            const spdEl = node.querySelector('.popup-speed-val');
            if (spdEl) spdEl.textContent = parseFloat(unit.speed || 0).toFixed(1);

            const ignEl = node.querySelector('.popup-ign-val');
            if (ignEl) {
                ignEl.textContent = unit.ignition_status ? 'ON' : 'OFF';
                ignEl.className = `text-lg font-black popup-ign-val ${unit.ignition_status ? 'text-green-600' : 'text-gray-400'} leading-none`;
            }

            const distEl = node.querySelector('.popup-dist-val');
            if (distEl) distEl.innerHTML = `${unit.daily_dist} <span class="text-[10px] text-gray-400 font-bold">km</span>`;

            const odoEl = node.querySelector('.popup-odo-val');
            if (odoEl) odoEl.textContent = parseFloat(unit.odo || 0).toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1});

            const syncEl = node.querySelector('.popup-sync-val');
            if (syncEl) syncEl.textContent = `Sync: ${unit.last_update || 'N/A'}`;

            const offEl = node.querySelector('.popup-offline-val');
            if (offEl) {
                if (unit.gps_status === 'offline' && unit.offline_display) {
                    offEl.textContent = `Offline for: ${unit.offline_display}`;
                    offEl.style.display = '';
                } else {
                    offEl.style.display = 'none';
                }
            }

            const addrEl = node.querySelector('.popup-address-val');
            if (addrEl) {
                const addrTxt = addrEl.textContent.trim();
                if (addrTxt === 'Loading address...' || addrTxt === 'Address service unavailable' || addrTxt === '') {
                    if (bestAddress) addrEl.textContent = bestAddress;
                } else {
                    // Good address is already showing — save it to cache just in case
                    unitAddressCache[unit.unit_id] = addrTxt;
                }
                
                // If it moved significantly and we are looking at it, auto-update address
                if (hasMoved) {
                    const currentAddress = addrEl.textContent.trim();
                    if (currentAddress !== 'Loading address...') {
                        queueAddressFetch(unit.latitude, unit.longitude, unit.unit_id).then(newAddr => {
                            // Only update if it actually changed to avoid flickering
                            if (newAddr && newAddr !== "Address service unavailable" && newAddr !== currentAddress) {
                                addrEl.textContent = newAddr;
                                unitAddressCache[unit.unit_id] = newAddr;
                                popup._content = popup._content.replace(currentAddress, newAddr);
                            }
                        });
                    }
                }
            }
        } else if (!wasPopupOpen && popup.isOpen()) {
            // Edge case: popup became open during this exact tick? Force a redraw
            popup.setContent(popupContent);
        }
    } else {
        markers[unit.unit_id].bindPopup(popupContent, {
            className: 'pro-popup',
            maxWidth: 300,
            offset: [220, 200],
            autoPan: true,
            autoPanPaddingTopLeft:     L.point(60, 140),
            autoPanPaddingBottomRight: L.point(60,  60),
        });
    }

    // Register popupopen handler (only runs when popup was NOT open during this update)
    markers[unit.unit_id].off('popupopen');
    markers[unit.unit_id].on('popupopen', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        const addressEl = document.getElementById(`address-${unit.unit_id}`);
        if (addressEl) {
            const cached = unitAddressCache[unit.unit_id]
                || addressCache[`${Number(unit.latitude).toFixed(3)},${Number(unit.longitude).toFixed(3)}`]
                || addressCache[`${Number(unit.latitude).toFixed(4)},${Number(unit.longitude).toFixed(4)}`];
            if (cached) {
                addressEl.textContent = cached;
            } else {
                addressEl.textContent = 'Loading address...';
                queueAddressFetch(unit.latitude, unit.longitude, unit.unit_id).then(addr => {
                    const el = document.getElementById(`address-${unit.unit_id}`);
                    if (el) el.textContent = addr;
                });
            }
        }
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
    // Read from contenteditable display div (primary) or hidden input (fallback)
    const displayEl = document.getElementById('unitSearchDisplay');
    const hiddenEl  = document.getElementById('unitSearchInput');
    const rawSearch = (displayEl ? (displayEl.innerText || displayEl.textContent || '') : (hiddenEl ? hiddenEl.value : '')).trim();
    const search    = rawSearch.toLowerCase();
    const status    = document.getElementById('statusFilterSelect')?.value || '';

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
