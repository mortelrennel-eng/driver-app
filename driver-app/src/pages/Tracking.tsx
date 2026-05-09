import { useEffect, useState } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonModal,
  useIonToast,
  IonSpinner
} from '@ionic/react';
import { locate, people, locationOutline, arrowBackOutline, speedometerOutline, trendingUpOutline } from 'ionicons/icons';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import L from 'leaflet';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useHistory } from 'react-router-dom';

// Fix for Leaflet marker icon issue in React
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIconRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.95))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.08)',
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
};

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
  const [data, setData] = useState<any>(null);
  const [position, setPosition] = useState<[number, number]>(() => {
    const saved = localStorage.getItem('last_known_pos');
    return saved ? JSON.parse(saved) : [14.5995, 120.9842];
  });
  const [isOffline, setIsOffline] = useState(false);
  const [nearbyDrivers, setNearbyDrivers] = useState<any[]>([]);
  const [presentToast] = useIonToast();
  const [loading, setLoading] = useState(true);
  const [address, setAddress] = useState<string>('');
  const [isSearching, setIsSearching] = useState(false);
  const [modalBreakpoint, setModalBreakpoint] = useState(0.15);

  const geoAxios = axios.create({
    transformRequest: [(data, headers) => {
      if (headers) delete headers['Authorization'];
      return data;
    }]
  });

  const [lastGeocodedPos, setLastGeocodedPos] = useState<[number, number] | null>(null);

  const fetchAddress = async (lat: number, lon: number) => {
    // Prevent spamming Nominatim if position hasn't changed
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
        setIsOffline(perfData.gps_status === 'Offline' || !perfData.latitude);

          if (perfData.latitude && perfData.longitude && parseFloat(perfData.latitude) !== 0) {
            const newPos: [number, number] = [parseFloat(perfData.latitude), parseFloat(perfData.longitude)];
            setPosition(newPos);
            localStorage.setItem('last_known_pos', JSON.stringify(newPos));

            // Force live OSM reverse-geocoding every 5 secs instead of relying on stale backend address
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
        // Expand modal to show list
        setModalBreakpoint(0.45);
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
      {/* Absolute Transparent Header */}
      <div style={{ position: 'absolute', top: 0, left: 0, right: 0, zIndex: 10000, padding: '16px 20px', display: 'flex', alignItems: 'center', gap: '12px', pointerEvents: 'none' }}>
        <button
          onClick={() => history.goBack()}
          style={{ pointerEvents: 'auto', background: 'rgba(255, 255, 255, 0.9)', border: '1px solid rgba(0,0,0,0.1)', borderRadius: '12px', padding: '10px', cursor: 'pointer', boxShadow: '0 4px 12px rgba(0,0,0,0.1)' }}
        >
          <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#000' }} />
        </button>
        <div style={{ pointerEvents: 'none' }}>
          <div style={{ fontSize: '18px', fontWeight: '800', color: '#0f172a', textShadow: '0 1px 2px rgba(255,255,255,0.8)' }}>Live Tracking</div>
          <div style={{ fontSize: '11px', color: '#475569', fontWeight: '700' }}>System Online</div>
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

            <Marker position={position}>
              <Popup>
                <div style={{ textAlign: 'center', padding: '8px' }}>
                  <strong style={{ color: '#1e3a8a', fontSize: '14px' }}>{data?.unit || 'Taxi Unit'}</strong><br />
                  <div style={{ fontSize: '12px', color: isOffline ? '#ef4444' : (data?.gps_status === 'Moving' ? '#22c55e' : '#fbbf24'), marginTop: '4px', fontWeight: '800' }}>
                    {isOffline ? '⚠️ SIGNAL LOST' : `🟢 ${data?.gps_status?.toUpperCase() || 'ACTIVE'}`}
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
                    <span style={{ fontSize: '11px', color: '#64748b' }}>{driver.distance} km away</span>
                  </div>
                </Popup>
              </Marker>
            ))}

            <LocateButton position={position} />
          </MapContainer>

          {/* Sliding Bottom Sheet Modal */}
          <IonModal
            isOpen={true}
            initialBreakpoint={modalBreakpoint}
            breakpoints={[0.15, 0.45, 0.9]}
            backdropBreakpoint={0.5}
            backdropDismiss={false}
            keyboardClose={false}
            onIonBreakpointDidChange={(e) => setModalBreakpoint(e.detail.breakpoint)}
            style={{ '--background': g.bg, '--border-radius': '24px 24px 0 0', zIndex: 20000 }}
          >
            <div style={{ background: g.bg, height: '100%', padding: '24px 20px', overflowY: 'auto' }}>

              <div style={{ width: '40px', height: '4px', background: 'rgba(255,255,255,0.1)', borderRadius: '2px', margin: '-12px auto 20px' }}></div>

              {/* Main Info */}
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: '#64748b', textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '4px' }}>Assigned Unit</div>
                  <h2 style={{ fontSize: '28px', fontWeight: '900', color: '#f8fafc', margin: 0, letterSpacing: '-0.5px' }}>
                    {data?.unit || '---'}
                  </h2>
                </div>
                <div style={{ textAlign: 'right' }}>
                  <div style={{
                    padding: '6px 14px',
                    borderRadius: '12px',
                    fontSize: '11px',
                    fontWeight: '900',
                    textTransform: 'uppercase',
                    backgroundColor: 
                      data?.gps_status === 'Moving' ? 'rgba(34,197,94,0.15)' : 
                      data?.gps_status === 'Idle' ? 'rgba(234,179,8,0.15)' :
                      data?.gps_status === 'Stopped' ? 'rgba(239,68,68,0.15)' : 'rgba(255,255,255,0.05)',
                    color: 
                      data?.gps_status === 'Moving' ? '#22c55e' : 
                      data?.gps_status === 'Idle' ? '#fbbf24' :
                      data?.gps_status === 'Stopped' ? '#ef4444' : '#94a3b8',
                    border: `1px solid ${
                      data?.gps_status === 'Moving' ? 'rgba(34,197,94,0.3)' : 
                      data?.gps_status === 'Idle' ? 'rgba(234,179,8,0.3)' :
                      data?.gps_status === 'Stopped' ? 'rgba(239,68,68,0.3)' : 'rgba(255,255,255,0.1)'
                    }`,
                    display: 'flex',
                    alignItems: 'center',
                    gap: '6px'
                  }}>
                    <div style={{ 
                      width: '6px', 
                      height: '6px', 
                      borderRadius: '50%', 
                      background: 
                        data?.gps_status === 'Moving' ? '#22c55e' : 
                        data?.gps_status === 'Idle' ? '#fbbf24' :
                        data?.gps_status === 'Stopped' ? '#ef4444' : '#94a3b8',
                      boxShadow: data?.gps_status === 'Moving' ? '0 0 8px #22c55e' : 'none'
                    }}></div>
                    {data?.gps_status || 'OFFLINE'}
                  </div>
                  <div style={{ fontSize: '10px', color: '#475569', marginTop: '6px', fontWeight: '600' }}>
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
                  <div key={i} style={{ background: 'rgba(255,255,255,0.03)', padding: '16px', borderRadius: '16px', border: g.border }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '8px' }}>
                      <IonIcon icon={stat.icon} style={{ fontSize: '14px', color: stat.color }} />
                      <span style={{ fontSize: '10px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase' }}>{stat.label}</span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px' }}>
                      <span style={{ fontSize: '22px', fontWeight: '900', color: '#f8fafc' }}>{stat.value}</span>
                      {stat.unit && <span style={{ fontSize: '11px', fontWeight: '700', color: '#475569' }}>{stat.unit}</span>}
                    </div>
                  </div>
                ))}
              </div>

              {/* Nearby Drivers Section (Top 5) */}
              {nearbyDrivers.length > 0 && (
                <div style={{ marginBottom: '24px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                    <IonIcon icon={people} style={{ fontSize: '16px', color: g.gold }} />
                    <span style={{ fontSize: '12px', fontWeight: '800', color: g.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Top 5 Closest Drivers</span>
                  </div>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {nearbyDrivers.slice(0, 5).map((driver, i) => (
                      <div key={i} style={{ background: 'rgba(255,255,255,0.03)', padding: '12px 16px', borderRadius: '14px', border: g.border, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                          <div style={{ width: '32px', height: '32px', borderRadius: '8px', background: 'rgba(234,179,8,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <span style={{ fontSize: '14px', fontWeight: '900', color: g.gold }}>{i + 1}</span>
                          </div>
                          <div>
                            <div style={{ fontSize: '14px', fontWeight: '800', color: '#f8fafc' }}>{driver.plate_number}</div>
                            <div style={{ fontSize: '11px', display: 'flex', alignItems: 'center', gap: '6px', marginTop: '2px' }}>
                              <span style={{
                                width: '6px',
                                height: '6px',
                                borderRadius: '50%',
                                background: driver.gps_status === 'Moving' ? '#22c55e' : driver.gps_status === 'Idle' ? '#fbbf24' : driver.gps_status === 'Stopped' ? '#ef4444' : '#94a3b8',
                                boxShadow: driver.gps_status === 'Moving' ? '0 0 4px #22c55e' : 'none'
                              }}></span>
                              <span style={{ 
                                color: driver.gps_status === 'Moving' ? '#4ade80' : driver.gps_status === 'Idle' ? '#fde047' : driver.gps_status === 'Stopped' ? '#f87171' : '#94a3b8',
                                fontWeight: '700',
                                letterSpacing: '0.5px'
                              }}>
                                {driver.gps_status?.toUpperCase() || 'OFFLINE'}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                          <div style={{ fontSize: '13px', fontWeight: '900', color: '#3b82f6' }}>{driver.distance} km</div>
                          <div style={{ fontSize: '9px', color: '#475569', textTransform: 'uppercase' }}>Distance</div>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Location Bar */}
              <div style={{ background: 'rgba(59,130,246,0.1)', padding: '14px 16px', borderRadius: '16px', border: '1px solid rgba(59,130,246,0.2)', display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px' }}>
                <div style={{ width: '36px', height: '36px', borderRadius: '10px', background: 'rgba(59,130,246,0.2)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <IonIcon icon={locationOutline} style={{ color: '#3b82f6', fontSize: '20px' }} />
                </div>
                <div style={{ flex: 1, overflow: 'hidden' }}>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: '#3b82f6', textTransform: 'uppercase', letterSpacing: '1px' }}>Current Address</div>
                  <div style={{ fontSize: '12px', fontWeight: '700', color: '#e2e8f0', whiteSpace: 'normal', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                    {address || 'Detecting accurate location...'}
                  </div>
                </div>
              </div>

              {/* Action Button */}
              <button
                onClick={(e) => { e.stopPropagation(); findNearbyDrivers(); }}
                disabled={isSearching}
                style={{ width: '100%', padding: '18px', background: g.goldGrad, border: 'none', borderRadius: '16px', color: '#000', fontWeight: '900', fontSize: '15px', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '10px', cursor: 'pointer', boxShadow: '0 8px 24px rgba(234,179,8,0.2)', letterSpacing: '0.5px' }}
              >
                {isSearching ? <IonSpinner name="crescent" style={{ width: '20px', height: '20px', '--color': '#000' }} /> : <IonIcon icon={people} style={{ fontSize: '20px' }} />}
                {isSearching ? 'SEARCHING...' : 'FIND NEARBY DRIVERS'}
              </button>

            </div>
          </IonModal>

          {loading && (
            <div style={{ position: 'absolute', inset: 0, background: g.bg, zIndex: 100000, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
              <IonSpinner name="crescent" color="warning" />
              <div style={{ marginTop: '16px', fontSize: '13px', color: '#64748b', fontWeight: '600' }}>Initializing Tracking...</div>
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Tracking;
