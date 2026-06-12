import { useEffect, useState, useRef, useCallback } from 'react';
import { App } from '@capacitor/app';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonSpinner,
  useIonToast,
  useIonViewDidEnter,
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
import { useAuth } from '../context/AuthContext';
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

// ── AnimatedMarker: Animates marker position changes smoothly ──────
const AnimatedMarker: React.FC<{
  position: [number, number];
  icon: L.DivIcon | L.Icon;
  eventHandlers?: any;
  duration?: number;
}> = ({ position, icon, eventHandlers, duration = 4500 }) => {
  const [currentPos, setCurrentPos] = useState<[number, number]>(position);
  const prevPosRef = useRef<[number, number]>(position);
  const targetPosRef = useRef<[number, number]>(position);
  const animationFrameRef = useRef<number | null>(null);
  const startTimeRef = useRef<number | null>(null);

  useEffect(() => {
    // If target position changes
    if (position[0] !== targetPosRef.current[0] || position[1] !== targetPosRef.current[1]) {
      const start = prevPosRef.current;
      const target = position;
      
      // Calculate distance (rough degrees)
      const distance = Math.sqrt(
        Math.pow(target[0] - start[0], 2) + Math.pow(target[1] - start[1], 2)
      );

      // If the jump is too far (e.g. initial load or sudden huge GPS jump), skip animation
      if (distance > 0.05) {
        setCurrentPos(position);
        prevPosRef.current = position;
        targetPosRef.current = position;
      } else {
        prevPosRef.current = currentPos;
        targetPosRef.current = position;
        startTimeRef.current = performance.now();

        const animate = (time: number) => {
          if (!startTimeRef.current) return;
          const elapsed = time - startTimeRef.current;
          const progress = Math.min(elapsed / duration, 1);

          const startVal = prevPosRef.current;
          const targetVal = targetPosRef.current;
          
          const lat = startVal[0] + (targetVal[0] - startVal[0]) * progress;
          const lng = startVal[1] + (targetVal[1] - startVal[1]) * progress;
          
          setCurrentPos([lat, lng]);

          if (progress < 1) {
            animationFrameRef.current = requestAnimationFrame(animate);
          }
        };

        if (animationFrameRef.current) {
          cancelAnimationFrame(animationFrameRef.current);
        }
        animationFrameRef.current = requestAnimationFrame(animate);
      }
    } else {
      setCurrentPos(position);
    }
  }, [position, duration]);

  useEffect(() => {
    return () => {
      if (animationFrameRef.current) {
        cancelAnimationFrame(animationFrameRef.current);
      }
    };
  }, []);

  return <Marker position={currentPos} icon={icon} eventHandlers={eventHandlers} />;
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
  if (s === 'idle' || s === 'stopped') return 'PARK';
  return (status || 'N/A').toUpperCase();
};

// ── Main Component ────────────────────────────────────────────────
const Tracking: React.FC = () => {
  const history = useHistory();
  const [presentToast] = useIonToast();
  const { user } = useAuth();

  const [data, setData] = useState<any>(null);
  const [position, setPosition] = useState<[number, number]>([14.5995, 120.9842]);
  const [isOffline, setIsOffline] = useState(false);
  const [rawPath, setRawPath] = useState<[number, number][]>([]);
  const [snappedPath, setSnappedPath] = useState<[number, number][]>([]);

  // ── Load user-specific tracking history on mount or user change ───
  useEffect(() => {
    const userId = user?.id || 'guest';

    // 1. Load last known position
    const savedPos = localStorage.getItem(`last_known_pos_${userId}`);
    if (savedPos) {
      try {
        setPosition(JSON.parse(savedPos));
      } catch (e) {}
    } else {
      const globalSaved = localStorage.getItem('last_known_pos');
      if (globalSaved) {
        try {
          setPosition(JSON.parse(globalSaved));
        } catch (e) {}
      }
    }

    // 2. Load raw path history
    const savedRaw = localStorage.getItem(`tracking_path_history_${userId}`);
    let loadedRaw: [number, number][] = [];
    if (savedRaw) {
      try {
        const parsed = JSON.parse(savedRaw);
        const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
        if (parsed.date === today && Array.isArray(parsed.path)) {
          loadedRaw = parsed.path;
        }
      } catch (e) {}
    }
    setRawPath(loadedRaw);

    // 3. Load snapped path history
    const savedSnapped = localStorage.getItem(`tracking_snapped_path_history_${userId}`);
    let loadedSnapped: [number, number][] = [];
    if (savedSnapped) {
      try {
        const parsed = JSON.parse(savedSnapped);
        const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
        if (parsed.date === today && Array.isArray(parsed.path)) {
          loadedSnapped = parsed.path;
        }
      } catch (e) {}
    } else if (loadedRaw.length > 0) {
      loadedSnapped = loadedRaw;
    }
    setSnappedPath(loadedSnapped);
  }, [user]);


  const [nearbyDrivers, setNearbyDrivers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [isSearching, setIsSearching] = useState(false);
  const [showUnitCard, setShowUnitCard] = useState(false);
  const [showNearbyModal, setShowNearbyModal] = useState(false);
  const [showNearbyOnMap, setShowNearbyOnMap] = useState(false);
  const [mapType, setMapType] = useState<'default' | 'satellite'>('default');
  const [isZoomedIn, setIsZoomedIn] = useState(false);

  const mapRef = useRef<L.Map | null>(null);
  const initialCenterDone = useRef(false);

  // ── Fetch tracking data ──────────────────────────────────────────
  const fetchTracking = useCallback(async (manual = false) => {
    if (manual) presentToast({ message: 'Syncing live location...', duration: 1000, position: 'top' });
    try {
      const response = await axios.get(endpoints.driverPerformance, {
        params: {
          _t: new Date().getTime(),
        },
        headers: {
          'Cache-Control': 'no-cache, no-store, must-revalidate',
          'Pragma': 'no-cache',
          'Expires': '0',
        },
      });
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
          const userId = user?.id || 'guest';
          localStorage.setItem(`last_known_pos_${userId}`, JSON.stringify(newPos));

          // Auto-center map if it hasn't been centered yet for this session/tab entry
          if (mapRef.current && !initialCenterDone.current) {
            initialCenterDone.current = true;
            const target = perfData.path && perfData.path.length > 0 
              ? perfData.path[perfData.path.length - 1] 
              : newPos;
            setTimeout(() => {
              mapRef.current?.invalidateSize();
              mapRef.current?.setView(target, 16, { animate: true });
            }, 100);
          }

          if (Array.isArray(perfData.path) && perfData.path.length > 0) {
            setRawPath(perfData.path);
            const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
            localStorage.setItem(`tracking_path_history_${userId}`, JSON.stringify({ date: today, path: perfData.path }));
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
              localStorage.setItem(`tracking_path_history_${userId}`, JSON.stringify({ date: today, path: newPath }));
              return newPath;
            });
          }
        }
      }
    } catch (e) {
      setIsOffline(true);
    } finally {
      setLoading(false);
    }
  }, [presentToast, user]);

  const latestPosRef = useRef<[number, number]>(position);
  useEffect(() => {
    latestPosRef.current = snappedPath.length > 0 ? snappedPath[snappedPath.length - 1] : position;
  }, [snappedPath, position]);

  // ── Auto-center map when returning to tab ────────────────────────
  useIonViewDidEnter(() => {
    initialCenterDone.current = false; // Reset center flag so it recenters on fresh fetch
    if (mapRef.current) {
      setTimeout(() => {
        mapRef.current?.invalidateSize();
        mapRef.current?.setView(latestPosRef.current, 16, { animate: false });
      }, 100);
    }
    fetchTracking(false).then(() => {
      setTimeout(() => {
        if (mapRef.current) {
          mapRef.current?.invalidateSize();
          mapRef.current?.setView(latestPosRef.current, 16, { animate: true });
        }
      }, 300);
    });
  });

  // ── Snap path whenever rawPath changes ──────────────────────────
  useEffect(() => {
    setSnappedPath(rawPath);
  }, [rawPath]);

  // ── Poll every 5 seconds & instantly on resume ───────────────────
  useEffect(() => {
    fetchTracking();
    const interval = setInterval(() => fetchTracking(false), 5000);
    
    const handleVisibilityChange = () => {
      if (document.visibilityState === 'visible') fetchTracking(false);
    };
    document.addEventListener('visibilitychange', handleVisibilityChange);

    const appStateListener = App.addListener('appStateChange', ({ isActive }) => {
      if (isActive) fetchTracking(false);
    });

    return () => {
      clearInterval(interval);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      appStateListener.then(l => l.remove()).catch(() => {});
    };
  }, [fetchTracking]);

  // ── Locate me button handler (toggle zoom in/out) ───────────────
  const handleLocateMe = () => {
    if (mapRef.current) {
      const targetPos = snappedPath.length > 0 ? snappedPath[snappedPath.length - 1] : position;
      if (isZoomedIn) {
        mapRef.current.setView(targetPos, 14, { animate: true });
      } else {
        mapRef.current.setView(targetPos, 18, { animate: true });
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

            {/* Road-snapped or raw path */}
            {(snappedPath.length > 1 || rawPath.length > 1) && (
              <>
                {/* Glow effect */}
                <Polyline
                  positions={snappedPath.length > 1 ? snappedPath : rawPath}
                  pathOptions={{ color: '#93c5fd', weight: 10, opacity: 0.3, lineCap: 'round', lineJoin: 'round' }}
                />
                {/* Main line */}
                <Polyline
                  positions={snappedPath.length > 1 ? snappedPath : rawPath}
                  pathOptions={{ color: '#3b82f6', weight: 4, opacity: 0.95, lineCap: 'round', lineJoin: 'round' }}
                />
                {/* White dashes on top */}
                <Polyline
                  positions={snappedPath.length > 1 ? snappedPath : rawPath}
                  pathOptions={{ color: '#fff', weight: 1.5, opacity: 0.6, lineCap: 'round', dashArray: '8 16' }}
                />
              </>
            )}

            {/* Short Red Trail directly behind the unit marker to show direction */}
            {(snappedPath.length > 1 || rawPath.length > 1) && (
              <Polyline
                positions={snappedPath.length > 1 ? snappedPath.slice(-3) : rawPath.slice(-3)}
                pathOptions={{ color: '#ef4444', weight: 6, opacity: 0.9, lineCap: 'round', lineJoin: 'round' }}
              />
            )}

            {/* My unit marker — tap opens info card */}
            <AnimatedMarker
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
                      {data?.location || 'Detecting location...'}
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

