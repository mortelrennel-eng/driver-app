import { useEffect, useState } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonModal,
  useIonToast,
  IonSpinner
} from '@ionic/react';
import { locate, people, locationOutline, arrowBackOutline, speedometerOutline, trendingUpOutline, closeOutline } from 'ionicons/icons';
import { MapContainer, TileLayer, Marker, Popup, useMap, Polyline } from 'react-leaflet';
import L from 'leaflet';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useTheme } from '../context/ThemeContext';
import { useHistory } from 'react-router-dom';

// Fix for Leaflet marker icon issue in React
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';



const DefaultIcon = L.icon({
  iconUrl: markerIcon,
  iconRetinaUrl: markerIconRetina,
  shadowUrl: markerShadow,
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41]
});

const NearbyIcon = L.icon({
  iconUrl: markerIcon,
  iconRetinaUrl: markerIconRetina,
  shadowUrl: markerShadow,
  iconSize: [22, 36],
  iconAnchor: [11, 36],
  popupAnchor: [1, -32],
  shadowSize: [36, 36],
  className: 'nearby-marker-icon'
});

L.Marker.prototype.options.icon = DefaultIcon;

const ChangeView: React.FC<{ center: [number, number] }> = ({ center }) => {
  const map = useMap();
  useEffect(() => {
    setTimeout(() => {
      map.invalidateSize();
      map.setView(center, map.getZoom());
    }, 250);
  }, [center, map]);
  return null;
};

const LocateButton: React.FC<{ position: [number, number] }> = ({ position }) => {
  const map = useMap();
  return (
    <div style={{ position: 'absolute', bottom: '180px', right: '20px', zIndex: 9999, pointerEvents: 'auto' }}>
      <button
        onClick={(e) => { 
          e.stopPropagation(); 
          map.setView(position, 18, { animate: true });
        }}
        style={{ width: '56px', height: '56px', borderRadius: '18px', background: 'rgba(255,255,255,0.95)', border: '1px solid rgba(0,0,0,0.1)', color: '#000', display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 10px 25px rgba(0,0,0,0.2)', cursor: 'pointer', outline: 'none' }}
      >
        <IonIcon icon={locate} style={{ fontSize: '26px' }} />
      </button>
    </div>
  );
};

const Tracking: React.FC = () => {
  const history = useHistory();
  const { t } = useTheme();
  const [data, setData] = useState<any>(null);
  const [pos, setPos] = useState<{lat:number;lng:number}|null>(null);
  const [position, setPosition] = useState<[number, number]>(() => {
    const saved = localStorage.getItem('last_known_pos');
    return saved ? JSON.parse(saved) : [14.5995, 120.9842];
  });
  const [isOffline, setIsOffline] = useState(false);
  const [path, setPath] = useState<[number, number][]>(() => {
    const savedData = localStorage.getItem('tracking_path_history');
    if (savedData) {
      try {
        const parsed = JSON.parse(savedData);
        // Check if the saved path belongs to today
        const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
        if (parsed.date === today && Array.isArray(parsed.path)) {
          return parsed.path;
        }
      } catch(e) {}
    }
    return [];
  });
  const [nearbyDrivers, setNearbyDrivers] = useState<any[]>([]);
  const [presentToast] = useIonToast();
  const [loading, setLoading] = useState(true);
  const [address, setAddress] = useState<string>('');
  const [isSearching, setIsSearching] = useState(false);
  const [showModal, setShowModal] = useState(false);

  const geoAxios = axios.create({
    transformRequest: [(data, headers) => {
      if (headers) delete headers['Authorization'];
      return data;
    }]
  });

  const [lastGeocodedPos, setLastGeocodedPos] = useState<[number, number] | null>(null);

  const fetchAddress = async (lat: number, lon: number) => {
    if (lastGeocodedPos && lastGeocodedPos[0] === lat && lastGeocodedPos[1] === lon) {
      return;
    }
    setLastGeocodedPos([lat, lon]);

    try {
      const res = await geoAxios.get(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, {
        headers: { 'Accept-Language': 'en' }
      });
      if (res.data && res.data.display_name) {
        setAddress(res.data.display_name);
      }
    } catch (e) {
      console.error('Failed to geocode location', e);
    }
  };

  const fetchTracking = async (manual = false) => {
    if (manual) {
      presentToast({ message: 'Syncing live location...', duration: 1000, position: 'top' });
    }
    try {
      const response = await axios.get(endpoints.driverPerformance);
      if (response.data.success) {
        const perfData = response.data.data;
        setData(perfData);
        
        // Determine offline status:
        const status = (perfData.gps_status || '').toLowerCase();
        const hasCoords = perfData.latitude && perfData.longitude && parseFloat(perfData.latitude) !== 0;
        const isReallyOffline = (!status || status === 'offline') && !hasCoords;
        setIsOffline(isReallyOffline);

          if (hasCoords) {
            const newPos: [number, number] = [parseFloat(perfData.latitude), parseFloat(perfData.longitude)];
            setPosition(newPos);
            setPos({lat: newPos[0], lng: newPos[1]});
            localStorage.setItem('last_known_pos', JSON.stringify(newPos));

            setPath(prev => {
              let newPath = prev;
              if (prev.length === 0) {
                newPath = [newPos];
              } else {
                const last = prev[prev.length - 1];
                // Only add if the position actually changed
                if (last[0] !== newPos[0] || last[1] !== newPos[1]) {
                  newPath = [...prev, newPos];
                }
              }
              // Keep only the last 500 points to prevent localStorage from getting too heavy
              if (newPath.length > 500) newPath = newPath.slice(-500);
              
              const today = new Date().toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
              localStorage.setItem('tracking_path_history', JSON.stringify({
                date: today,
                path: newPath
              }));
              
              return newPath;
            });

            fetchAddress(newPos[0], newPos[1]);
          }
      }
    } catch (e) {
      console.error('Failed to fetch tracking data', e);
      setIsOffline(true);
    } finally {
      setLoading(false);
    }
  };

  const findNearbyDrivers = async () => {
    if (isSearching) return;
    setIsSearching(true);
    presentToast({ message: 'Searching for nearby drivers...', duration: 1500, position: 'top' });

    try {
      const response = await axios.get(endpoints.nearby, {
        params: { lat: position[0], lng: position[1] }
      });
      if (response.data.success && response.data.nearby.length > 0) {
        setNearbyDrivers(response.data.nearby);
        presentToast({
          message: `Found ${response.data.nearby.length} active drivers near you!`,
          duration: 3000,
          color: 'success',
          position: 'top'
        });
        setShowModal(true);
      } else {
        presentToast({
          message: 'No active drivers found nearby.',
          duration: 3000,
          color: 'warning',
          position: 'top'
        });
        setNearbyDrivers([]);
      }
    } catch (e) {
      console.error('Failed to fetch nearby drivers', e);
      presentToast({ message: 'Error searching for drivers.', duration: 2000, color: 'danger', position: 'top' });
    } finally {
      setIsSearching(false);
    }
  };

  useEffect(() => {
    fetchTracking();
    const interval = setInterval(() => fetchTracking(false), 5000);
    return () => clearInterval(interval);
  }, []);

  return (
    <IonPage>
      <div style={{ 
        position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10000, 
        padding: 'calc(env(safe-area-inset-top) + 16px) 20px 16px', 
        display: 'flex', alignItems: 'center', gap: '12px', pointerEvents: 'none'
      }}>
        <button onClick={() => history.goBack()} style={{ pointerEvents: 'auto', background: 'rgba(255,255,255,0.95)', border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer', boxShadow: '0 2px 8px rgba(0,0,0,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
          <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#1e293b' }} />
        </button>
        <div style={{ pointerEvents: 'none', background: 'rgba(255,255,255,0.95)', padding: '8px 14px', borderRadius: '12px', boxShadow: '0 2px 8px rgba(0,0,0,0.15)' }}>
          <div style={{ fontSize: '16px', fontWeight: '800', color: '#0f172a' }}>Live Tracking</div>
          <div style={{ fontSize: '10px', color: '#64748b', fontWeight: '600' }}>GPS position & location</div>
        </div>
      </div>

      <IonContent fullscreen scrollY={false}>
        <div style={{ height: '100%', width: '100%', position: 'relative' }}>

          <MapContainer
            center={position}
            zoom={16}
            zoomControl={false}
            scrollWheelZoom={true}
            style={{ height: '100%', width: '100%', zIndex: 1 }}
          >
            <ChangeView center={position} />
            <TileLayer
              attribution='&copy; OpenStreetMap contributors'
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            />

            {path.length > 1 && (
              <Polyline 
                positions={path} 
                pathOptions={{ color: '#3b82f6', weight: 5, opacity: 0.8, lineCap: 'round', lineJoin: 'round' }} 
              />
            )}

            <Marker position={position}>
              <Popup>
                <div style={{ textAlign: 'center', padding: '8px' }}>
                  <strong style={{ color: '#1e3a8a', fontSize: '14px' }}>{data?.unit || 'Taxi Unit'}</strong><br />
                  <div style={{ 
                    fontSize: '12px', 
                    color: isOffline ? '#94a3b8' : (
                      ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? '#22c55e' : 
                      data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' : 
                      data?.gps_status?.toLowerCase() === 'stopped' ? '#ef4444' : '#94a3b8'
                    ), 
                    marginTop: '4px', 
                    fontWeight: '800' 
                  }}>
                    {isOffline ? '⚠️ SIGNAL LOST' : `● ${data?.gps_status?.toLowerCase() === 'idle' ? 'PARKED' : (data?.gps_status?.toUpperCase() || 'N/A')}`}
                  </div>
                  <div style={{ fontSize: '11px', color: '#475569', marginTop: '4px', maxWidth: '150px' }}>
                    {address || 'Detecting place...'}
                  </div>
                </div>
              </Popup>
            </Marker>

            {isOffline && (
              <div style={{
                position: 'absolute',
                top: '90px',
                left: '50%',
                transform: 'translateX(-50%)',
                zIndex: 1000,
                background: 'rgba(239, 68, 68, 0.95)',
                color: 'white',
                padding: '10px 20px',
                borderRadius: '24px',
                fontSize: '11px',
                fontWeight: '900',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                boxShadow: '0 8px 24px rgba(239, 68, 68, 0.3)',
                whiteSpace: 'nowrap',
                letterSpacing: '1px'
              }}>
                <div style={{ width: '8px', height: '8px', borderRadius: '50%', background: 'white', animation: 'pulse 1.5s infinite' }}></div>
                GPS SIGNAL LOST
              </div>
            )}

            {nearbyDrivers.map((driver, idx) => (
              <Marker
                key={idx}
                position={[parseFloat(driver.latitude), parseFloat(driver.longitude)]}
                icon={NearbyIcon}
              >
                <Popup>
                  <div style={{ textAlign: 'center', padding: '5px' }}>
                    <strong style={{ color: '#b45309' }}>{driver.plate_number}</strong><br />
                    <span style={{ fontSize: '11px', color: t.textMuted }}>{pos ? `${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}` : 'N/A'}</span>
                  </div>
                </Popup>
              </Marker>
            ))}

            <LocateButton position={position} />
          </MapContainer>

          {/* Floating Action Button to Open Stats */}
          {!showModal && (
            <div 
              onClick={() => setShowModal(true)}
              style={{
                position: 'absolute',
                bottom: '100px',
                right: '20px',
                zIndex: 1000,
                background: t.gold,
                color: '#000',
                padding: '14px 20px',
                borderRadius: '24px',
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                fontWeight: '800',
                fontSize: '13px',
                boxShadow: '0 8px 24px rgba(234, 179, 8, 0.4)',
                cursor: 'pointer'
              }}
            >
              <IonIcon icon={speedometerOutline} style={{ fontSize: '18px' }} />
              Trip Info
            </div>
          )}

          <IonModal
            isOpen={showModal}
            onDidDismiss={() => setShowModal(false)}
            style={{ 
              '--height': 'auto', 
              '--max-height': '80vh', 
              '--width': '90%', 
              '--border-radius': '24px', 
              zIndex: 20000 
            }}
          >
            <div style={{ background: t.bg, padding: '24px 20px', overflowY: 'auto', maxHeight: '80vh' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <h2 style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary, margin: 0 }}>Trip Info</h2>
                <IonIcon 
                  icon={closeOutline} 
                  onClick={() => setShowModal(false)} 
                  style={{ fontSize: '24px', color: t.textMuted, cursor: 'pointer', background: t.subtleBg, padding: '6px', borderRadius: '50%' }} 
                />
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '4px' }}>Assigned Unit</div>
                  <h2 style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary, margin: 0, letterSpacing: '-0.3px' }}>
                    {data?.unit || '---'}
                  </h2>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <div style={{
                    padding: '6px 14px',
                    borderRadius: '12px',
                    fontSize: '11px',
                    fontWeight: '900',
                    textTransform: 'uppercase' as const,
                    backgroundColor: 
                      isOffline ? 'rgba(239,68,68,0.15)' :
                      ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? 'rgba(34,197,94,0.15)' : 
                      data?.gps_status?.toLowerCase() === 'idle' ? 'rgba(234,179,8,0.15)' :
                      data?.gps_status?.toLowerCase() === 'stopped' ? 'rgba(249,115,22,0.15)' : 'rgba(148,163,184,0.15)',
                    color: 
                      isOffline ? '#ef4444' :
                      ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? '#22c55e' : 
                      data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' :
                      data?.gps_status?.toLowerCase() === 'stopped' ? '#f97316' : '#94a3b8',
                    border: `1px solid ${
                      isOffline ? 'rgba(239,68,68,0.3)' :
                      ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? 'rgba(34,197,94,0.3)' : 
                      data?.gps_status?.toLowerCase() === 'idle' ? 'rgba(234,179,8,0.3)' :
                      data?.gps_status?.toLowerCase() === 'stopped' ? 'rgba(249,115,22,0.3)' : 'rgba(148,163,184,0.2)'
                    }`,
                    display: 'flex',
                    alignItems: 'center',
                    gap: '6px'
                  }}>
                    <div style={{ 
                      width: '6px', height: '6px', borderRadius: '50%', 
                      background: isOffline ? '#ef4444' :
                        ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? '#22c55e' : 
                        data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' :
                        data?.gps_status?.toLowerCase() === 'stopped' ? '#f97316' : '#94a3b8',
                      boxShadow: ['active', 'moving'].includes(data?.gps_status?.toLowerCase()) ? '0 0 8px #22c55e' : 'none'
                    }}></div>
                    {isOffline ? 'OFFLINE' : (data?.gps_status?.toLowerCase() === 'idle' ? 'PARKED' : (data?.gps_status?.toUpperCase() || 'N/A'))}
                  </div>
                  <div style={{ fontSize: '10px', color: t.textMuted, marginTop: '6px', fontWeight: '600' }}>
                    {data?.last_update || 'Syncing...'}
                  </div>
                </div>
              </div>

              {/* Stats Grid */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginBottom: '24px' }}>
                {[
                  { label: 'Live Speed', value: data?.speed || '0', unit: 'km/h', icon: speedometerOutline, color: '#22c55e' },
                  { label: 'Trip Dist.', value: data?.today_dist || '0.0', unit: 'km', icon: trendingUpOutline, color: '#3b82f6' }
                ].map((stat, i) => (
                  <div key={i} style={{ background: t.card, ...t.glass, padding: '16px', borderRadius: '16px', border: t.border }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '8px' }}>
                      <IonIcon icon={stat.icon} style={{ fontSize: '14px', color: stat.color }} />
                      <span style={{ fontSize: '10px', fontWeight: '700', color: t.textMuted, textTransform: 'uppercase' }}>{stat.label}</span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px' }}>
                      <span style={{ fontSize: '22px', fontWeight: '900', color: t.textPrimary }}>{stat.value}</span>
                      {stat.unit && <span style={{ fontSize: '11px', fontWeight: '700', color: t.textMuted }}>{stat.unit}</span>}
                    </div>
                  </div>
                ))}
              </div>

              {/* Location Bar */}
              <div style={{ background: 'rgba(59,130,246,0.1)', padding: '14px 16px', borderRadius: '16px', border: '1px solid rgba(59,130,246,0.2)', display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px' }}>
                <div style={{ width: '36px', height: '36px', borderRadius: '10px', background: 'rgba(59,130,246,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <IonIcon icon={locationOutline} style={{ color: '#3b82f6', fontSize: '20px' }} />
                </div>
                <div style={{ flex: 1, overflow: 'hidden' }}>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: '#3b82f6', textTransform: 'uppercase', letterSpacing: '1px' }}>Current Address</div>
                  <div style={{ fontSize: '12px', fontWeight: '700', color: t.textPrimary, whiteSpace: 'normal' }}>
                    {address || data?.location || 'Detecting accurate location...'}
                  </div>
                </div>
              </div>

              {/* Nearby Drivers */}
              {nearbyDrivers.length > 0 && (
                <div style={{ marginBottom: '24px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <IonIcon icon={people} style={{ fontSize: '16px', color: t.gold }} />
                    <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Top 5 Closest Drivers</span>
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {nearbyDrivers.slice(0, 5).map((driver, i) => (
                      <div key={i} style={{ background: t.card, ...t.glass, padding: '12px 16px', borderRadius: '14px', border: t.border, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                          <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: 'rgba(234,179,8,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <span style={{ fontSize: '14px', fontWeight: '900', color: t.gold }}>{i + 1}</span>
                          </div>
                          <div>
                            <div style={{ fontSize: '14px', fontWeight: '800', color: t.textPrimary }}>{driver.plate_number}</div>
                            <div style={{ fontSize: '11px', display: 'flex', alignItems: 'center', gap: '6px', marginTop: '2px' }}>
                              <span style={{
                                width: '6px', height: '6px', borderRadius: '50%',
                                background: ['active', 'moving'].includes(driver.gps_status?.toLowerCase()) ? '#22c55e' : driver.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' : driver.gps_status?.toLowerCase() === 'stopped' ? '#ef4444' : '#94a3b8',
                                boxShadow: ['active', 'moving'].includes(driver.gps_status?.toLowerCase()) ? '0 0 4px #22c55e' : 'none'
                              }}></span>
                              <span style={{ 
                                color: ['active', 'moving'].includes(driver.gps_status?.toLowerCase()) ? '#22c55e' : driver.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' : driver.gps_status?.toLowerCase() === 'stopped' ? '#ef4444' : '#94a3b8',
                                fontWeight: '700', letterSpacing: '0.5px'
                              }}>
                                {driver.gps_status?.toLowerCase() === 'idle' ? 'PARKED' : (driver.gps_status?.toUpperCase() || 'N/A')}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                          <div style={{ fontSize: '13px', fontWeight: '900', color: '#3b82f6' }}>{driver.distance} km</div>
                          <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase' }}>Distance</div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Action Button */}
              <button
                onClick={(e) => { e.stopPropagation(); findNearbyDrivers(); }}
                disabled={isSearching}
                style={{ width: '100%', padding: '18px', background: 'linear-gradient(135deg, #eab308, #f59e0b)', border: 'none', borderRadius: '16px', color: '#000', fontWeight: '900', fontSize: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', cursor: 'pointer', boxShadow: '0 8px 24px rgba(234,179,8,0.2)', letterSpacing: '0.5px' }}
              >
                {isSearching ? <IonSpinner name="crescent" style={{ width: '20px', height: '20px', '--color': '#000' }} /> : <IonIcon icon={people} style={{ fontSize: '20px' }} />}
                {isSearching ? 'SEARCHING...' : 'FIND NEARBY DRIVERS'}
              </button>

            </div>
          </IonModal>

          {loading && (
            <div style={{ position: 'absolute', inset: 0, background: t.bg, zIndex: 100000, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
              <IonSpinner name="crescent" color="warning" />
              <div style={{ marginTop: '16px', fontSize: '13px', color: t.textMuted, fontWeight: '600' }}>Initializing Tracking...</div>
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Tracking;
