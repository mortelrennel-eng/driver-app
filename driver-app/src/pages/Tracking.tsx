import { useEffect, useState, useRef, useCallback } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonSpinner,
  useIonToast,
} from '@ionic/react';
import {
  arrowBackOutline,
  locateOutline,
  peopleOutline,
  locationOutline,
  closeOutline,
} from 'ionicons/icons';
import { MapContainer, TileLayer, Marker, Popup, useMap, Polyline } from 'react-leaflet';
import L from 'leaflet';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useHistory } from 'react-router-dom';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// ── Default Leaflet icon fix ───────────────────────────────────────
const DefaultIcon = L.icon({
  iconUrl: markerIcon,
  iconRetinaUrl: markerIconRetina,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

// ── Custom marker icons ────────────────────────────────────────────
const myUnitIcon = L.divIcon({
  className: 'custom-div-icon',
  html: `<div style="
    width:40px;height:40px;border-radius:50%;
    background:linear-gradient(135deg,#eab308,#f59e0b);
    border:3px solid white;
    box-shadow:0 4px 16px rgba(234,179,8,0.6),0 0 0 6px rgba(234,179,8,0.2);
    display:flex;align-items:center;justify-content:center;
    font-size:18px;
  ">🚕</div>`,
  iconSize: [40, 40],
  iconAnchor: [20, 20],
  popupAnchor: [0, -24],
});

const nearbyIcon = L.divIcon({
  className: 'custom-div-icon',
  html: `<div style="
    width:34px;height:34px;border-radius:50%;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    border:2px solid white;
    box-shadow:0 3px 10px rgba(59,130,246,0.5);
    display:flex;align-items:center;justify-content:center;
    font-size:16px;
  ">🚖</div>`,
  iconSize: [34, 34],
  iconAnchor: [17, 17],
  popupAnchor: [0, -20],
});

// ── MapController: handles locate-me without auto-centering ───────
const MapController: React.FC<{
  targetPos: [number, number] | null;
  onReady: (map: L.Map) => void;
}> = ({ targetPos, onReady }) => {
  const map = useMap();
  const initializedRef = useRef(false);

  useEffect(() => {
    onReady(map);
    if (!initializedRef.current && targetPos) {
      initializedRef.current = true;
      setTimeout(() => {
        map.invalidateSize();
        map.setView(targetPos, 16, { animate: false });
      }, 300);
    }
  }, [map, targetPos, onReady]);

  return null;
};

// ── OSRM road-snapping (batch all points in chunks) ────────────────
const snapToRoad = async (rawPath: [number, number][]): Promise<[number, number][]> => {
  if (rawPath.length < 2) return rawPath;

  // First, deduplicate consecutive identical points
  const deduped: [number, number][] = [rawPath[0]];
  for (let i = 1; i < rawPath.length; i++) {
    const prev = deduped[deduped.length - 1];
    if (prev[0] !== rawPath[i][0] || prev[1] !== rawPath[i][1]) {
      deduped.push(rawPath[i]);
    }
  }
  if (deduped.length < 2) return deduped;

  // Filter out obvious GPS outliers (points too far from neighbors)
  const filtered: [number, number][] = [deduped[0]];
  for (let i = 1; i < deduped.length; i++) {
    const prev = filtered[filtered.length - 1];
    const dlat = Math.abs(deduped[i][0] - prev[0]);
    const dlng = Math.abs(deduped[i][1] - prev[1]);
    // Skip points that jump more than ~5km (0.05 degrees) from previous
    if (dlat < 0.05 && dlng < 0.05) {
      filtered.push(deduped[i]);
    }
  }
  if (filtered.length < 2) return filtered;

  const CHUNK_SIZE = 25; // Smaller chunks reduce the chance of one bad point ruining the whole route
  const OVERLAP = 2;     // Overlap between chunks for smooth joins

  try {
    const allSnapped: [number, number][] = [];

    for (let start = 0; start < filtered.length; start += CHUNK_SIZE - OVERLAP) {
      const chunk = filtered.slice(start, start + CHUNK_SIZE);
      if (chunk.length < 2) break;

      const coords = chunk.map(p => `${p[1]},${p[0]}`).join(';');
      const url = `https://router.project-osrm.org/route/v1/driving/${coords}?geometries=geojson&overview=full`;

      const res = await fetch(url);
      const data = await res.json();

      if (data.routes && data.routes.length > 0) {
        // Concatenate all routing segments
        const chunkSnapped: [number, number][] = [];
        for (const route of data.routes) {
          if (route.geometry?.coordinates) {
            for (const c of route.geometry.coordinates) {
              chunkSnapped.push([c[1], c[0]] as [number, number]);
            }
          }
        }

        if (chunkSnapped.length > 0) {
          if (allSnapped.length === 0) {
            allSnapped.push(...chunkSnapped);
          } else {
            // Skip the first few points of this chunk to avoid overlap duplication
            allSnapped.push(...chunkSnapped.slice(OVERLAP > 0 ? 1 : 0));
          }
        }
      } else {
        // If OSRM fails to find a route for this chunk (e.g. unroutable point), fallback to raw points for this chunk
        const rawChunkConverted = chunk.map(p => p as [number, number]);
        if (allSnapped.length === 0) {
          allSnapped.push(...rawChunkConverted);
        } else {
          allSnapped.push(...rawChunkConverted.slice(OVERLAP > 0 ? 1 : 0));
        }
      }
    }

    return allSnapped.length >= 2 ? allSnapped : filtered;
  } catch (e) {
    // fallback to filtered raw path
  }
  return filtered;
};

// ── Status helpers ────────────────────────────────────────────────
const statusColor = (status: string, offline = false) => {
  if (offline) return '#ef4444';
  const s = (status || '').toLowerCase();
  if (['active', 'moving'].includes(s)) return '#22c55e';
  if (s === 'idle') return '#fbbf24';
  if (s === 'stopped') return '#f97316';
  return '#94a3b8';
};

const statusLabel = (status: string, offline = false) => {
  if (offline) return 'OFFLINE';
  const s = (status || '').toLowerCase();
  if (s === 'idle') return 'PARKED';
  return (status || 'N/A').toUpperCase();
};

// ── Main Component ────────────────────────────────────────────────
const Tracking: React.FC = () => {
  const history = useHistory();
  const [presentToast] = useIonToast();

  const [data, setData] = useState<any>(null);
  const [position, setPosition] = useState<[number, number]>(() => {
    const saved = localStorage.getItem('last_known_pos');
    return saved ? JSON.parse(saved) : [14.5995, 120.9842];
  });
  const [isOffline, setIsOffline] = useState(false);
  const [rawPath, setRawPath] = useState<[number, number][]>(() => {
    const savedData = localStorage.getItem('tracking_path_history');
    if (savedData) {
      try {
        const parsed = JSON.parse(savedData);
        const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
        if (parsed.date === today && Array.isArray(parsed.path)) return parsed.path;
      } catch (e) {}
    }
    return [];
  });
  const [snappedPath, setSnappedPath] = useState<[number, number][]>([]);
  const [nearbyDrivers, setNearbyDrivers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [address, setAddress] = useState('');
  const [isSearching, setIsSearching] = useState(false);
  const [showUnitCard, setShowUnitCard] = useState(false);
  const [showNearbyModal, setShowNearbyModal] = useState(false);
  const [showNearbyOnMap, setShowNearbyOnMap] = useState(false);
  const [mapType, setMapType] = useState<'default' | 'satellite'>('default');
  const [isZoomedIn, setIsZoomedIn] = useState(false);

  const mapRef = useRef<L.Map | null>(null);
  const lastGeocodedPos = useRef<[number, number] | null>(null);
  const snappingRef = useRef(false);
  const lastSnappedLenRef = useRef(0);
  const prevSnappedRef = useRef<[number, number][]>([]);

  const geoAxios = axios.create({
    transformRequest: [(data, headers) => {
      if (headers) delete headers['Authorization'];
      return data;
    }],
  });

  // ── Reverse geocode ──────────────────────────────────────────────
  const fetchAddress = useCallback(async (lat: number, lon: number) => {
    if (lastGeocodedPos.current &&
        lastGeocodedPos.current[0] === lat &&
        lastGeocodedPos.current[1] === lon) return;
    lastGeocodedPos.current = [lat, lon];
    try {
      const res = await geoAxios.get(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`,
        { headers: { 'Accept-Language': 'en' } }
      );
      if (res.data?.display_name) setAddress(res.data.display_name);
    } catch (e) {}
  }, []);

  // ── Snap to road (incremental – only snap new points) ─────────────
  const doSnap = useCallback(async (path: [number, number][]) => {
    if (snappingRef.current || path.length < 2) {
      if (path.length < 2) setSnappedPath(path);
      return;
    }

    // If the path hasn't changed in length, skip re-snapping
    if (path.length === lastSnappedLenRef.current && prevSnappedRef.current.length > 0) {
      return;
    }

    // If only 1-2 new points were added, skip to avoid spamming OSRM
    const newPointCount = path.length - lastSnappedLenRef.current;
    if (newPointCount > 0 && newPointCount < 3 && prevSnappedRef.current.length > 0) {
      return;
    }

    snappingRef.current = true;

    try {
      if (lastSnappedLenRef.current === 0 || prevSnappedRef.current.length === 0) {
        // First time — snap the entire path
        const snapped = await snapToRoad(path);
        setSnappedPath(snapped);
        prevSnappedRef.current = snapped;
        lastSnappedLenRef.current = path.length;
      } else {
        // Incremental: re-snap the tail region (last 20 previously snapped points + new ones)
        const overlapCount = 20;
        const tailStart = Math.max(0, lastSnappedLenRef.current - overlapCount);
        const tailRaw = path.slice(tailStart);

        if (tailRaw.length >= 2) {
          const snappedTail = await snapToRoad(tailRaw);
          // Keep the head of the previous snap and replace the tail
          const keepCount = Math.max(0, prevSnappedRef.current.length - overlapCount * 3);
          const head = prevSnappedRef.current.slice(0, keepCount);
          const merged = [...head, ...snappedTail];
          setSnappedPath(merged);
          prevSnappedRef.current = merged;
        }
        lastSnappedLenRef.current = path.length;
      }
    } catch (e) {
      setSnappedPath(path);
    }

    snappingRef.current = false;
  }, []);

  // ── Fetch tracking data ──────────────────────────────────────────
  const fetchTracking = useCallback(async (manual = false) => {
    if (manual) presentToast({ message: 'Syncing live location...', duration: 1000, position: 'top' });
    try {
      const response = await axios.get(endpoints.driverPerformance);
      if (response.data.success) {
        const perfData = response.data.data;
        setData(perfData);
        const status = (perfData.gps_status || '').toLowerCase();
        const hasCoords = perfData.latitude && perfData.longitude &&
          parseFloat(perfData.latitude) !== 0;
        setIsOffline(!status || status === 'offline');

        if (hasCoords) {
          const newPos: [number, number] = [parseFloat(perfData.latitude), parseFloat(perfData.longitude)];
          setPosition(newPos);
          localStorage.setItem('last_known_pos', JSON.stringify(newPos));

          if (Array.isArray(perfData.path) && perfData.path.length > 0) {
            setRawPath(perfData.path);
            const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
            localStorage.setItem('tracking_path_history', JSON.stringify({ date: today, path: perfData.path }));
          } else {
            setRawPath(prev => {
              let newPath = prev;
              if (prev.length === 0) {
                newPath = [newPos];
              } else {
                const last = prev[prev.length - 1];
                if (last[0] !== newPos[0] || last[1] !== newPos[1]) {
                  newPath = [...prev, newPos];
                }
              }
              if (newPath.length > 500) newPath = newPath.slice(-500);
              const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
              localStorage.setItem('tracking_path_history', JSON.stringify({ date: today, path: newPath }));
              return newPath;
            });
          }

          fetchAddress(newPos[0], newPos[1]);
        }
      }
    } catch (e) {
      setIsOffline(true);
    } finally {
      setLoading(false);
    }
  }, [fetchAddress, presentToast]);

  // ── Snap path whenever rawPath changes ──────────────────────────
  useEffect(() => {
    doSnap(rawPath);
  }, [rawPath, doSnap]);

  // ── Poll every 5 seconds ─────────────────────────────────────────
  useEffect(() => {
    fetchTracking();
    const interval = setInterval(() => fetchTracking(false), 5000);
    return () => clearInterval(interval);
  }, [fetchTracking]);

  // ── Locate me button handler (toggle zoom in/out) ───────────────
  const handleLocateMe = () => {
    if (mapRef.current) {
      if (isZoomedIn) {
        mapRef.current.setView(position, 14, { animate: true });
      } else {
        mapRef.current.setView(position, 18, { animate: true });
      }
      setIsZoomedIn(!isZoomedIn);
    }
  };

  // ── Find nearby drivers ──────────────────────────────────────────
  const findNearbyDrivers = async () => {
    if (isSearching) return;
    setIsSearching(true);
    presentToast({ message: 'Searching nearby units...', duration: 1500, position: 'top' });
    try {
      const response = await axios.get(endpoints.nearby, {
        params: { lat: position[0], lng: position[1] },
      });
      if (response.data.success && response.data.nearby.length > 0) {
        setNearbyDrivers(response.data.nearby);
        setShowNearbyOnMap(true);
        setShowNearbyModal(true);
        // Pan map to show nearby area
        if (mapRef.current && response.data.nearby.length > 0) {
          const nb = response.data.nearby[0];
          if (nb.latitude && nb.longitude) {
            const bounds = L.latLngBounds([position, [parseFloat(nb.latitude), parseFloat(nb.longitude)]]);
            mapRef.current.fitBounds(bounds, { padding: [60, 60], animate: true });
          }
        }
      } else {
        presentToast({ message: 'No active units found nearby.', duration: 3000, color: 'warning', position: 'top' });
        setNearbyDrivers([]);
      }
    } catch (e) {
      presentToast({ message: 'Error searching for units.', duration: 2000, color: 'danger', position: 'top' });
    } finally {
      setIsSearching(false);
    }
  };

  const tileUrls = {
    default: 'https://mt1.google.com/vt/lyrs=m,traffic&x={x}&y={y}&z={z}',
    satellite: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
  };

  const sc = statusColor(data?.gps_status, isOffline);

  return (
    <IonPage>
      <IonContent fullscreen scrollY={false}>
        <div style={{ height: '100%', width: '100%', position: 'relative', background: '#0f172a' }}>

          {/* ── COMPACT HEADER ── */}
          <div style={{
            position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10000,
            padding: 'calc(env(safe-area-inset-top) + 8px) 12px 8px',
            display: 'flex', alignItems: 'center', gap: '10px',
            background: 'transparent', pointerEvents: 'none',
          }}>
            <button
              onClick={() => history.goBack()}
              style={{
                background: '#f1f5f9', border: 'none',
                borderRadius: '10px', padding: '8px', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)', pointerEvents: 'auto',
              }}
            >
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '18px', color: '#1e293b' }} />
            </button>

            <div style={{ flex: 1, minWidth: 0, pointerEvents: 'auto' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                <div style={{
                  fontSize: '14px', fontWeight: '900', color: '#0f172a',
                  background: 'rgba(255,255,255,0.95)', padding: '4px 10px',
                  borderRadius: '8px', boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                  backdropFilter: 'blur(8px)', whiteSpace: 'nowrap'
                }}>
                  LIVE TRACKING
                </div>
                <div style={{
                  padding: '2px 6px', borderRadius: '6px', fontSize: '8px', fontWeight: '900',
                  textTransform: 'uppercase', letterSpacing: '0.5px', flexShrink: 0,
                  background: `${sc}18`, color: sc, border: `1px solid ${sc}33`,
                  display: 'flex', alignItems: 'center', gap: '3px',
                }}>
                  <div style={{
                    width: '5px', height: '5px', borderRadius: '50%',
                    background: sc,
                    boxShadow: isOffline ? 'none' : `0 0 4px ${sc}`,
                  }} />
                  {statusLabel(data?.gps_status, isOffline)}
                </div>
              </div>
            </div>

            {/* Compact stats */}
            <div style={{ display: 'flex', gap: '4px', flexShrink: 0, pointerEvents: 'auto' }}>
              <div style={{
                background: '#f0fdf4', border: '1px solid #bbf7d0',
                borderRadius: '8px', padding: '4px 8px', textAlign: 'center',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)', backdropFilter: 'blur(8px)'
              }}>
                <span style={{ fontSize: '13px', fontWeight: '900', color: '#16a34a' }}>{data?.speed || '0'}</span>
                <span style={{ fontSize: '8px', color: '#64748b', fontWeight: '700', marginLeft: '2px' }}>km/h</span>
              </div>
              <div style={{
                background: '#eff6ff', border: '1px solid #bfdbfe',
                borderRadius: '8px', padding: '4px 8px', textAlign: 'center',
                boxShadow: '0 4px 12px rgba(0,0,0,0.15)', backdropFilter: 'blur(8px)'
              }}>
                <span style={{ fontSize: '13px', fontWeight: '900', color: '#2563eb' }}>{data?.today_dist || '0.0'}</span>
                <span style={{ fontSize: '8px', color: '#64748b', fontWeight: '700', marginLeft: '2px' }}>km</span>
              </div>
            </div>
          </div>

          {/* ── MAP ── */}
          <MapContainer
            center={position}
            zoom={16}
            maxZoom={22}
            zoomControl={false}
            scrollWheelZoom={true}
            style={{ height: '100%', width: '100%', zIndex: 1 }}
          >
            <MapController
              targetPos={position}
              onReady={(map) => { mapRef.current = map; }}
            />

            <TileLayer
              attribution='&copy; Google Maps'
              url={tileUrls[mapType]}
              maxNativeZoom={20}
              maxZoom={22}
            />

            {/* Road-snapped path */}
            {snappedPath.length > 1 && (
              <>
                {/* Glow effect */}
                <Polyline
                  positions={snappedPath}
                  pathOptions={{ color: '#93c5fd', weight: 10, opacity: 0.3, lineCap: 'round', lineJoin: 'round' }}
                />
                {/* Main line */}
                <Polyline
                  positions={snappedPath}
                  pathOptions={{ color: '#3b82f6', weight: 4, opacity: 0.95, lineCap: 'round', lineJoin: 'round' }}
                />
                {/* White dashes on top */}
                <Polyline
                  positions={snappedPath}
                  pathOptions={{ color: '#fff', weight: 1.5, opacity: 0.6, lineCap: 'round', dashArray: '8 16' }}
                />
              </>
            )}

            {/* Short Red Trail directly behind the unit marker to show direction */}
            {snappedPath.length > 1 && (
              <Polyline
                positions={snappedPath.slice(-3)}
                pathOptions={{ color: '#ef4444', weight: 6, opacity: 0.9, lineCap: 'round', lineJoin: 'round' }}
              />
            )}

            {/* My unit marker — tap opens info card */}
            <Marker
              position={snappedPath.length > 0 ? snappedPath[snappedPath.length - 1] : position}
              icon={myUnitIcon}
              eventHandlers={{ click: () => setShowUnitCard(v => !v) }}
            />

            {/* Nearby units markers */}
            {showNearbyOnMap && nearbyDrivers.map((driver, i) => {
              if (!driver.latitude || !driver.longitude) return null;
              return (
                <Marker
                  key={`nearby-${i}`}
                  position={[parseFloat(driver.latitude), parseFloat(driver.longitude)]}
                  icon={nearbyIcon}
                >
                  <Popup>
                    <div style={{ padding: '6px', minWidth: '120px' }}>
                      <div style={{ fontWeight: '900', fontSize: '13px', color: '#1e3a8a' }}>
                        {driver.plate_number}
                      </div>
                      <div style={{ fontSize: '11px', color: '#3b82f6', fontWeight: '800', marginTop: '4px' }}>
                        📍 {driver.distance} km away
                      </div>
                    </div>
                  </Popup>
                </Marker>
              );
            })}
          </MapContainer>

          {/* ── GPS SIGNAL LOST BANNER ── */}
          {isOffline && (
            <div style={{
              position: 'absolute', top: '80px', left: '50%',
              transform: 'translateX(-50%)', zIndex: 10001,
              background: 'rgba(239,68,68,0.95)', color: 'white',
              padding: '6px 16px', borderRadius: '20px', fontSize: '10px', fontWeight: '900',
              display: 'flex', alignItems: 'center', gap: '6px',
              boxShadow: '0 4px 16px rgba(239,68,68,0.4)', whiteSpace: 'nowrap',
              letterSpacing: '1px',
            }}>
              <div style={{ width: '6px', height: '6px', borderRadius: '50%', background: 'white' }} />
              GPS SIGNAL LOST
            </div>
          )}

          {/* ── MAP TYPE SWITCHER (top-right) ── */}
          <div style={{
            position: 'absolute', top: '80px', right: '12px',
            zIndex: 10001, display: 'flex', flexDirection: 'column', gap: '6px',
          }}>
            {[
              { key: 'default', label: '🗺️' },
              { key: 'satellite', label: '🛰️' },
            ].map(btn => (
              <button
                key={btn.key}
                onClick={() => setMapType(btn.key as any)}
                style={{
                  width: '36px', height: '36px', borderRadius: '9px',
                  border: mapType === btn.key ? '2px solid #eab308' : '2px solid rgba(255,255,255,0.2)',
                  background: mapType === btn.key ? 'rgba(202,138,4,0.85)' : 'rgba(15,23,42,0.78)',
                  backdropFilter: 'blur(8px)', cursor: 'pointer',
                  fontSize: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center',
                  boxShadow: mapType === btn.key
                    ? '0 0 0 3px rgba(234,179,8,0.3)'
                    : '0 2px 10px rgba(0,0,0,0.3)',
                  transition: 'all 0.2s ease',
                }}
              >
                {btn.label}
              </button>
            ))}
          </div>

          {/* ── RIGHT SIDE BUTTONS ── */}
          <div style={{
            position: 'absolute', bottom: '160px', right: '12px',
            zIndex: 10001, display: 'flex', flexDirection: 'column', gap: '8px',
          }}>
            {/* Unit Info Button (opens unit modal) */}
            <button
              onClick={(e) => { e.stopPropagation(); setShowUnitCard(v => !v); }}
              style={{
                width: '44px', height: '44px',
                borderRadius: '12px', background: 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                border: 'none', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: '0 4px 16px rgba(59,130,246,0.4)', transition: 'all 0.2s',
              }}
            >
              <span style={{ fontSize: '20px' }}>🚕</span>
            </button>

            {/* Nearby Drivers Button */}
            <button
              onClick={(e) => { e.stopPropagation(); findNearbyDrivers(); }}
              disabled={isSearching}
              style={{
                width: '44px', height: '44px',
                borderRadius: '12px', background: 'linear-gradient(135deg, #eab308, #f59e0b)',
                border: 'none', cursor: isSearching ? 'not-allowed' : 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: '0 4px 16px rgba(234,179,8,0.4)', transition: 'all 0.2s',
                opacity: isSearching ? 0.6 : 1,
              }}
            >
              {isSearching
                ? <IonSpinner name="crescent" style={{ width: '20px', height: '20px', color: '#000' }} />
                : <IonIcon icon={peopleOutline} style={{ fontSize: '22px', color: '#000' }} />
              }
            </button>

            {/* Locate Me (toggle zoom in/out) */}
            <button
              onClick={handleLocateMe}
              style={{
                width: '44px', height: '44px',
                borderRadius: '12px',
                background: isZoomedIn ? 'linear-gradient(135deg, #3b82f6, #1d4ed8)' : 'rgba(255,255,255,0.95)',
                border: isZoomedIn ? 'none' : '1px solid rgba(0,0,0,0.1)', cursor: 'pointer',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: isZoomedIn ? '0 4px 16px rgba(59,130,246,0.4)' : '0 4px 16px rgba(0,0,0,0.15)',
                transition: 'all 0.2s',
              }}
            >
              <IonIcon icon={locateOutline} style={{ fontSize: '22px', color: isZoomedIn ? '#fff' : '#3b82f6' }} />
            </button>
          </div>

          {/* ── UNIT INFO CARD (shown on marker/bar click) ── */}
          {showUnitCard && (
            <>
              {/* Backdrop */}
              <div
                onClick={() => setShowUnitCard(false)}
                style={{
                  position: 'absolute', inset: 0, zIndex: 20000,
                  background: 'rgba(0,0,0,0.3)',
                  animation: 'fadeIn 0.2s ease',
                }}
              />
              <div style={{
                position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)',
                width: 'calc(100% - 32px)', maxWidth: '400px',
                zIndex: 20001, background: '#fff',
                borderRadius: '20px',
                boxShadow: '0 16px 40px rgba(0,0,0,0.3)',
                animation: 'scaleIn 0.3s cubic-bezier(0.34,1.56,0.64,1)',
              }}>
                <style>{`
                  @keyframes scaleIn {
                    from { transform: translate(-50%, -40%) scale(0.95); opacity: 0; }
                    to   { transform: translate(-50%, -50%) scale(1);    opacity: 1; }
                  }
                  @keyframes fadeIn {
                    from { opacity: 0; }
                    to   { opacity: 1; }
                  }
                `}</style>

                {/* Close button */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 20px 0' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <div style={{
                      width: '40px', height: '40px', borderRadius: '12px',
                      background: 'linear-gradient(135deg,#eab308,#f59e0b)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: '20px',
                    }}>🚕</div>
                    <div>
                      <div style={{ fontSize: '16px', fontWeight: '900', color: '#0f172a' }}>
                        {data?.unit || 'My Vehicle'}
                      </div>
                      <div style={{
                        display: 'inline-flex', alignItems: 'center', gap: '4px',
                        padding: '2px 8px', borderRadius: '6px', marginTop: '2px',
                        background: `${sc}18`, fontSize: '9px', fontWeight: '900',
                        color: sc, textTransform: 'uppercase',
                      }}>
                        <div style={{ width: '5px', height: '5px', borderRadius: '50%', background: sc, boxShadow: `0 0 4px ${sc}` }} />
                        {statusLabel(data?.gps_status, isOffline)}
                      </div>
                    </div>
                  </div>
                  <button
                    onClick={() => setShowUnitCard(false)}
                    style={{
                      width: '32px', height: '32px', borderRadius: '50%',
                      background: '#f1f5f9', border: 'none', cursor: 'pointer',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}
                  >
                    <IonIcon icon={closeOutline} style={{ fontSize: '18px', color: '#64748b' }} />
                  </button>
                </div>

                <div style={{ padding: '16px 20px 32px' }}>
                  {/* Stats row */}
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '8px', marginBottom: '14px' }}>
                    {[
                      { label: 'Speed', value: data?.speed || '0', unit: 'km/h', bg: '#f0fdf4', color: '#16a34a' },
                      { label: 'Distance', value: data?.today_dist || '0.0', unit: 'km', bg: '#eff6ff', color: '#2563eb' },
                      { label: 'Engine', value: data?.ignition ? 'ON' : 'OFF', unit: '', bg: data?.ignition ? '#f0fdf4' : '#f1f5f9', color: data?.ignition ? '#16a34a' : '#94a3b8' },
                    ].map((s, i) => (
                      <div key={i} style={{
                        background: s.bg, borderRadius: '12px', padding: '10px', textAlign: 'center',
                      }}>
                        <div style={{ fontSize: '9px', fontWeight: '700', color: '#94a3b8', textTransform: 'uppercase', marginBottom: '4px' }}>{s.label}</div>
                        <div style={{ fontSize: '18px', fontWeight: '900', color: s.color }}>{s.value}</div>
                        {s.unit && <div style={{ fontSize: '9px', fontWeight: '700', color: '#94a3b8' }}>{s.unit}</div>}
                      </div>
                    ))}
                  </div>

                  {/* Address */}
                  <div style={{
                    background: '#f8fafc', padding: '10px 14px',
                    borderRadius: '12px', border: '1px solid #e2e8f0',
                    display: 'flex', alignItems: 'center', gap: '10px',
                  }}>
                    <IonIcon icon={locationOutline} style={{ color: '#3b82f6', fontSize: '18px', flexShrink: 0 }} />
                    <div style={{ fontSize: '11px', fontWeight: '600', color: '#334155', lineHeight: '1.4' }}>
                      {address || data?.location || 'Detecting location...'}
                    </div>
                  </div>
                </div>
              </div>
            </>
          )}

          {/* ── NEARBY MODAL (centered popup with X) ── */}
          {showNearbyModal && nearbyDrivers.length > 0 && (
            <>
              {/* Backdrop */}
              <div
                onClick={() => setShowNearbyModal(false)}
                style={{
                  position: 'absolute', inset: 0, zIndex: 30000,
                  background: 'rgba(0,0,0,0.5)',
                  animation: 'fadeIn 0.2s ease',
                }}
              />
              <div style={{
                position: 'absolute', top: '50%', left: '50%',
                transform: 'translate(-50%, -50%)',
                zIndex: 30001, width: 'calc(100% - 40px)', maxWidth: '360px',
                background: '#fff', borderRadius: '20px',
                boxShadow: '0 20px 60px rgba(0,0,0,0.3)',
                animation: 'popIn 0.3s cubic-bezier(0.34,1.56,0.64,1)',
                maxHeight: '70vh', display: 'flex', flexDirection: 'column',
              }}>
                <style>{`
                  @keyframes popIn {
                    from { transform: translate(-50%, -50%) scale(0.9); opacity: 0; }
                    to   { transform: translate(-50%, -50%) scale(1);   opacity: 1; }
                  }
                `}</style>

                {/* Modal header */}
                <div style={{
                  display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                  padding: '16px 20px', borderBottom: '1px solid #f1f5f9',
                }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <div style={{
                      width: '32px', height: '32px', borderRadius: '10px',
                      background: 'linear-gradient(135deg, #eab308, #f59e0b)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      fontSize: '14px',
                    }}>📍</div>
                    <div>
                      <div style={{ fontSize: '14px', fontWeight: '900', color: '#0f172a' }}>
                        Nearby Units
                      </div>
                      <div style={{ fontSize: '10px', color: '#64748b', fontWeight: '600' }}>
                        {nearbyDrivers.length} unit{nearbyDrivers.length > 1 ? 's' : ''} found
                      </div>
                    </div>
                  </div>
                  <button
                    onClick={() => setShowNearbyModal(false)}
                    style={{
                      width: '32px', height: '32px', borderRadius: '50%',
                      background: '#f1f5f9', border: 'none', cursor: 'pointer',
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      transition: 'all 0.2s',
                    }}
                  >
                    <IonIcon icon={closeOutline} style={{ fontSize: '20px', color: '#64748b' }} />
                  </button>
                </div>

                {/* Units list */}
                <div style={{ padding: '8px 16px 16px', overflowY: 'auto', flex: 1 }}>
                  {nearbyDrivers.slice(0, 8).map((driver, i) => (
                    <div key={i} style={{
                      padding: '12px', borderRadius: '12px',
                      border: '1px solid #f1f5f9',
                      display: 'flex', justifyContent: 'space-between',
                      alignItems: 'center', marginBottom: '6px', cursor: 'pointer',
                      background: '#fafbfc',
                      transition: 'all 0.2s',
                    }}
                      onClick={() => {
                        if (mapRef.current && driver.latitude && driver.longitude) {
                          mapRef.current.setView(
                            [parseFloat(driver.latitude), parseFloat(driver.longitude)],
                            17, { animate: true }
                          );
                          setShowNearbyModal(false);
                        }
                      }}
                    >
                      <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                        <div style={{
                          width: '34px', height: '34px', borderRadius: '10px',
                          background: 'linear-gradient(135deg, #3b82f6, #1d4ed8)',
                          display: 'flex', alignItems: 'center', justifyContent: 'center',
                          color: '#fff', fontSize: '13px', fontWeight: '900',
                        }}>{i + 1}</div>
                        <div>
                          <div style={{ fontSize: '13px', fontWeight: '800', color: '#0f172a' }}>
                            {driver.plate_number}
                          </div>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '4px', marginTop: '2px' }}>
                            <div style={{ width: '5px', height: '5px', borderRadius: '50%', background: statusColor(driver.gps_status) }} />
                            <span style={{ fontSize: '10px', fontWeight: '700', color: statusColor(driver.gps_status) }}>
                              {statusLabel(driver.gps_status)}
                            </span>
                          </div>
                        </div>
                      </div>
                      <div style={{
                        background: '#eff6ff', borderRadius: '8px', padding: '4px 10px',
                        textAlign: 'center',
                      }}>
                        <div style={{ fontSize: '13px', fontWeight: '900', color: '#2563eb' }}>{driver.distance}</div>
                        <div style={{ fontSize: '8px', color: '#64748b', fontWeight: '700' }}>km</div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </>
          )}

          {/* ── LOADING OVERLAY ── */}
          {loading && (
            <div style={{
              position: 'absolute', inset: 0, background: '#fff',
              zIndex: 99999, display: 'flex', flexDirection: 'column',
              alignItems: 'center', justifyContent: 'center', gap: '16px',
            }}>
              <IonSpinner name="crescent" color="warning" />
              <div style={{ fontSize: '13px', color: '#64748b', fontWeight: '600' }}>
                Initializing GPS Tracking...
              </div>
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Tracking;

