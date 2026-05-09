import { useEffect, useState } from 'react';
import type { FC } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonRefresher,
  IonRefresherContent
} from '@ionic/react';
import {
  alertCircle,
  carSportOutline,
  statsChartOutline,
  notificationsOutline,
  settingsOutline,
  shieldCheckmarkOutline,
  navigateOutline,
  cashOutline,
  warningOutline,
  chevronForwardOutline,
  trendingUpOutline,
  ribbonOutline,
  chatbubbleEllipsesOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useGpsTracking } from '../hooks/useGpsTracking';
import axios from 'axios';
import { endpoints } from '../config/api';

interface PerformanceData {
  driver_name: string;
  unit: string;
  boundary_target: number;
  boundary_actual: number;
  boundary_status: string;
  boundary_shortage: number;
  boundary_excess: number;
  progress: number;
  is_coding: boolean;
  coding_message: string;
  coding_day_name: string;
  next_coding_date: string;
  attendance_rate: number;
  efficiency_rate: number;
  message: string;
  is_blocked: boolean;
  gps_status: string;
  location: string;
  latitude: number;
  longitude: number;
  boundary_target_label?: string;
  profile_incomplete?: boolean;
  license_number?: string;
  phone?: string;
  address?: string;
  emergency_contact?: string;
  emergency_phone?: string;
}

/* ── Shared Design Tokens ── */
const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
  radius: '20px',
  shadow: '0 8px 32px rgba(0,0,0,0.4)',
};

const Dashboard: FC = () => {
  const { user, logout, refreshUser } = useAuth();
  const history = useHistory();
  const [data, setData] = useState<PerformanceData | null>(null);
  const [apiError, setApiError] = useState<string | null>(null);
  const [_isLoadingData, setIsLoadingData] = useState(true);

  useGpsTracking(60000);

  useEffect(() => {
    // Load cached data for "Instant Load" / Offline Support
    const cached = localStorage.getItem('cached_performance_data');
    if (cached) {
      try {
        setData(JSON.parse(cached));
      } catch (e) {
        console.error('Failed to parse cached data', e);
      }
    }

    refreshUser();
    fetchPerformance();
    const interval = setInterval(fetchPerformance, 5 * 60 * 1000);
    return () => clearInterval(interval);
  }, []);

  const fetchPerformance = async () => {
    try {
      setApiError(null);
      refreshUser();
      const response = await axios.get(endpoints.driverPerformance);
      if (response.data.success) {
        const newData = response.data.data;
        setData(newData);
        // Save to cache
        localStorage.setItem('cached_performance_data', JSON.stringify(newData));
      } else {
        setApiError(response.data.message || 'Failed to load performance data.');
      }
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || 'Network error';
      const status = e.response?.status;
      console.error('Failed to fetch performance', status, msg);
      if (status === 404) {
        setApiError('Driver record not linked to your account. Please contact the EuroTaxi office to link your driver profile.');
      } else if (status === 401) {
        setApiError('Session expired. Please log out and log in again.');
      } else {
        setApiError(`Connection error: ${msg}`);
      }
    } finally {
      setIsLoadingData(false);
    }
  };

  const doRefresh = (event: CustomEvent) => {
    fetchPerformance().then(() => event.detail.complete());
  };

  const [currentTime, setCurrentTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => setCurrentTime(new Date()), 1000);
    return () => clearInterval(timer);
  }, []);

  const progress = data?.progress ?? 0;
  const shortage = data?.boundary_shortage ?? 0;
  const progressColor = progress >= 100 ? '#22c55e' : shortage > 0 ? '#ef4444' : g.gold;

  const formatDate = (date: Date) => {
    const options: Intl.DateTimeFormatOptions = { 
      weekday: 'long', 
      month: 'short', 
      day: 'numeric' 
    };
    return date.toLocaleDateString(undefined, options);
  };

  if (data?.is_blocked) {
    return (
      <IonPage>
        <IonContent fullscreen>
          <div style={{ minHeight: '100vh', background: g.bg, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '32px' }}>
            <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(239,68,68,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '24px' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '40px', color: '#ef4444' }} />
            </div>
            <h1 style={{ color: '#fff', fontSize: '24px', fontWeight: '800', margin: '0 0 8px' }}>Access Restricted</h1>
            <p style={{ color: '#94a3b8', textAlign: 'center', marginBottom: '32px' }}>Please contact the EuroTaxi office regarding your account status.</p>
            <button onClick={logout} style={{ background: 'rgba(239,68,68,0.2)', border: '1px solid rgba(239,68,68,0.4)', color: '#ef4444', padding: '14px 40px', borderRadius: '16px', fontWeight: '700', fontSize: '15px', cursor: 'pointer' }}>
              Sign Out
            </button>
          </div>
        </IonContent>
      </IonPage>
    );
  }

  return (
    <IonPage>
      <IonContent fullscreen scrollY={true}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: g.bg, paddingBottom: '120px' }}>

          {/* ── Header Bar ── */}
          <div style={{ padding: '16px 20px 8px', display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div style={{ width: '48px', height: '48px', borderRadius: '16px', background: g.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: '0 6px 20px rgba(234,179,8,0.25)' }}>
                <span style={{ fontSize: '20px', fontWeight: '900', color: '#0a0e1a' }}>{(user?.name || 'D')[0].toUpperCase()}</span>
              </div>
              <div>
                <div style={{ fontSize: '16px', fontWeight: '900', color: '#f8fafc', letterSpacing: '-0.4px', lineHeight: '1.2' }}>EuroTaxi Driver</div>
                <div style={{ fontSize: '11px', color: g.gold, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px' }}>{user?.name || 'Driver'}</div>
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
               <button 
                onClick={() => history.push('/announcements')} 
                style={{ background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.05)', borderRadius: '14px', padding: '10px', cursor: 'pointer', position: 'relative' }}
              >
                <IonIcon icon={notificationsOutline} style={{ fontSize: '20px', color: '#f8fafc' }} />
                <div style={{ position: 'absolute', top: '8px', right: '8px', width: '8px', height: '8px', background: '#ef4444', borderRadius: '50%', border: '2px solid #0a0e1a' }}></div>
              </button>
            </div>
          </div>

          {/* ── Real-time Clock Bar ── */}
          <div style={{ margin: '0 20px 20px', padding: '12px 16px', background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.05)', borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div style={{ display: 'flex', flexDirection: 'column' }}>
               <span style={{ fontSize: '10px', fontWeight: '800', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1px' }}>{formatDate(currentTime)}</span>
               <span style={{ fontSize: '12px', fontWeight: '700', color: '#cbd5e1' }}>{currentTime.toLocaleDateString(undefined, { weekday: 'long' })}</span>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{ fontSize: '20px', fontWeight: '900', color: g.gold, letterSpacing: '1px' }}>
                {currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true })}
              </div>
            </div>
          </div>

          {/* ── API Error Banner ── */}
          {apiError && (
            <div style={{ margin: '0 20px 12px', padding: '14px 16px', background: 'rgba(239,68,68,0.12)', border: '1px solid rgba(239,68,68,0.3)', borderRadius: '14px', display: 'flex', alignItems: 'flex-start', gap: '10px' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '20px', color: '#ef4444', flexShrink: 0, marginTop: '1px' }} />
              <div>
                <div style={{ fontSize: '12px', fontWeight: '700', color: '#fca5a5', marginBottom: '4px' }}>Data Unavailable</div>
                <div style={{ fontSize: '11px', color: '#94a3b8', lineHeight: '1.4' }}>{apiError}</div>
              </div>
            </div>
          )}
          
          {/* ... Rest of components ... */}
          
          {/* ── Profile Incomplete Banner ── */}
          {data?.profile_incomplete && (
            <div onClick={() => history.push('/settings')} style={{ margin: '0 20px 12px', padding: '12px 16px', background: 'rgba(234,179,8,0.12)', border: '1px solid rgba(234,179,8,0.3)', borderRadius: '14px', display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '18px', color: g.gold }} />
              <span style={{ fontSize: '12px', fontWeight: '700', color: g.gold }}>Complete your profile & emergency info in Settings</span>
              <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: g.gold, marginLeft: 'auto' }} />
            </div>
          )}

          {/* ── Coding Banner ── */}
          {data && (
            <div style={{ margin: '0 20px 16px', padding: '20px 18px', borderRadius: '20px', background: data.is_coding ? 'linear-gradient(135deg, rgba(239,68,68,0.2) 0%, rgba(239,68,68,0.1) 100%)' : 'linear-gradient(135deg, rgba(34,197,94,0.15) 0%, rgba(34,197,94,0.05) 100%)', border: `1px solid ${data.is_coding ? 'rgba(239,68,68,0.4)' : 'rgba(34,197,94,0.25)'}`, display: 'flex', alignItems: 'center', gap: '14px', boxShadow: '0 4px 12px rgba(0,0,0,0.1)' }}>
              <div style={{ width: '12px', height: '12px', borderRadius: '50%', background: data.is_coding ? '#ef4444' : '#22c55e', boxShadow: `0 0 12px ${data.is_coding ? '#ef4444' : '#22c55e'}`, animation: 'pulse 2s infinite' }}></div>
              <div style={{ flex: 1 }}>
                <div style={{ fontSize: '15px', fontWeight: '800', color: data.is_coding ? '#fca5a5' : '#86efac', letterSpacing: '0.3px' }}>
                  {data.is_coding ? data.coding_message : 'No Coding Today — Drive Freely!'}
                </div>
                {data.coding_day_name && (
                  <div style={{ fontSize: '11px', color: '#94a3b8', marginTop: '4px', fontWeight: '600', opacity: 0.8 }}>
                    Your Schedule: <span style={{ color: '#e2e8f0' }}>{data.coding_day_name}</span> {data.next_coding_date && `• Next: ${new Date(data.next_coding_date).toLocaleDateString()}`}
                  </div>
                )}
              </div>
              {data.is_coding && <IonIcon icon={warningOutline} style={{ fontSize: '24px', color: '#ef4444', opacity: 0.6 }} />}
            </div>
          )}

          {/* ── Boundary Progress Hero ── */}
          <div style={{ margin: '0 20px 16px', padding: '24px 20px', background: g.card, ...g.glass, border: g.border, borderRadius: g.radius, boxShadow: g.shadow }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
              <div>
                <div style={{ fontSize: '10px', fontWeight: '800', color: '#94a3b8', textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '4px' }}>
                  Boundary Progress
                  {data?.boundary_target_label && <span style={{ marginLeft: '8px', color: g.gold }}>{data.boundary_target_label}</span>}
                </div>
                <div style={{ fontSize: '32px', fontWeight: '900', color: '#f8fafc', lineHeight: 1 }}>
                  ₱{(data?.boundary_actual ?? 0).toLocaleString()}
                </div>
                <div style={{ fontSize: '12px', color: '#64748b', marginTop: '4px' }}>
                  of ₱{(data?.boundary_target ?? 0).toLocaleString()} target
                </div>
              </div>
              <div style={{ textAlign: 'right' }}>
                <div style={{ 
                  display: 'inline-flex', 
                  alignItems: 'center', 
                  gap: '4px', 
                  padding: '4px 10px', 
                  borderRadius: '12px', 
                  background: 
                    data?.gps_status === 'Active' ? 'rgba(34,197,94,0.15)' : 
                    data?.gps_status === 'Idle' ? 'rgba(234,179,8,0.15)' :
                    data?.gps_status === 'Stopped' ? 'rgba(239,68,68,0.15)' : 'rgba(255,255,255,0.05)',
                  color: 
                    data?.gps_status === 'Active' ? '#22c55e' : 
                    data?.gps_status === 'Idle' ? '#fbbf24' :
                    data?.gps_status === 'Stopped' ? '#ef4444' : '#94a3b8',
                  fontSize: '10px',
                  fontWeight: '800',
                  border: `1px solid ${
                    data?.gps_status === 'Active' ? 'rgba(34,197,94,0.3)' : 
                    data?.gps_status === 'Idle' ? 'rgba(234,179,8,0.3)' :
                    data?.gps_status === 'Stopped' ? 'rgba(239,68,68,0.3)' : 'rgba(255,255,255,0.1)'
                  }`,
                  marginBottom: '8px'
                }}>
                  <div style={{ 
                    width: '5px', 
                    height: '5px', 
                    borderRadius: '50%', 
                    background: 
                      data?.gps_status === 'Active' ? '#22c55e' : 
                      data?.gps_status === 'Idle' ? '#fbbf24' :
                      data?.gps_status === 'Stopped' ? '#ef4444' : '#94a3b8',
                    boxShadow: data?.gps_status === 'Active' ? '0 0 6px #22c55e' : 'none'
                  }}></div>
                  {data?.gps_status || 'OFFLINE'}
                </div>
                <div style={{ fontSize: '28px', fontWeight: '900', color: progressColor }}>{progress}%</div>
                {shortage > 0 && <div style={{ fontSize: '11px', color: '#ef4444', fontWeight: '600' }}>-₱{shortage.toLocaleString()} short</div>}
              </div>
            </div>
            {/* Progress Bar */}
            <div style={{ height: '8px', background: 'rgba(255,255,255,0.08)', borderRadius: '4px', overflow: 'hidden' }}>
              <div style={{ height: '100%', width: `${Math.min(progress, 100)}%`, background: progressColor, borderRadius: '4px', transition: 'width 0.6s ease' }}></div>
            </div>
            {data?.message && (
              <div style={{ marginTop: '14px', padding: '10px 12px', background: 'rgba(255,255,255,0.04)', borderRadius: '10px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '16px', color: g.gold }} />
                <span style={{ fontSize: '11px', color: '#94a3b8', fontStyle: 'italic' }}>{data.message}</span>
              </div>
            )}
          </div>

          {/* ── Stats Row ── */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', margin: '0 20px 16px' }}>
            {[
              { label: 'Attendance', value: `${data?.attendance_rate ?? 0}%`, icon: ribbonOutline, color: '#3b82f6' },
              { label: 'Efficiency', value: `${data?.efficiency_rate ?? 0}%`, icon: trendingUpOutline, color: '#22c55e' }
            ].map((stat, i) => (
              <div key={i} style={{ padding: '16px', background: g.card, ...g.glass, border: g.border, borderRadius: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                  <div style={{ width: '28px', height: '28px', borderRadius: '8px', background: `${stat.color}20`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <IonIcon icon={stat.icon} style={{ fontSize: '14px', color: stat.color }} />
                  </div>
                  <span style={{ fontSize: '10px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1px' }}>{stat.label}</span>
                </div>
                <div style={{ fontSize: '24px', fontWeight: '900', color: '#f8fafc' }}>{stat.value}</div>
              </div>
            ))}
          </div>

          {/* ── Driver Quick Profile (Combined & Minimal) ── */}
          <div style={{ margin: '0 20px 16px', padding: '14px 16px', background: 'rgba(255,255,255,0.02)', border: '1px solid rgba(255,255,255,0.05)', borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <div style={{ width: '36px', height: '36px', borderRadius: '10px', background: 'rgba(234,179,8,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IonIcon icon={carSportOutline} style={{ fontSize: '18px', color: g.gold }} />
              </div>
              <div>
                <div style={{ fontSize: '13px', fontWeight: '800', color: '#f8fafc' }}>{data?.unit || 'No Unit'}</div>
                <div style={{ fontSize: '10px', color: '#64748b', fontWeight: '600' }}>Assigned Taxi</div>
              </div>
            </div>
            <div style={{ height: '24px', width: '1px', background: 'rgba(255,255,255,0.05)' }}></div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px', textAlign: 'right' }}>
              <div>
                <div style={{ fontSize: '13px', fontWeight: '800', color: '#f8fafc' }}>{data?.license_number || '—'}</div>
                <div style={{ fontSize: '10px', color: '#64748b', fontWeight: '600' }}>Driver License</div>
              </div>
              <div style={{ width: '36px', height: '36px', borderRadius: '10px', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '18px', color: '#3b82f6' }} />
              </div>
            </div>
          </div>

          {/* ── Driver Tools Grid (Clean & Organized) ── */}
          <div style={{ margin: '0 20px 8px' }}>
             <h3 style={{ fontSize: '11px', fontWeight: '800', color: '#475569', textTransform: 'uppercase', letterSpacing: '1.5px', marginBottom: '12px', paddingLeft: '4px' }}>Driver Toolbox</h3>
             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '10px' }}>
                {[
                  { label: 'Tracking', icon: navigateOutline, color: '#3b82f6', route: '/tracking' },
                  { label: 'Stats', icon: statsChartOutline, color: '#8b5cf6', route: '/performance' },
                  { label: 'Vehicle', icon: carSportOutline, color: '#06b6d4', route: '/vehicle' },
                  { label: 'Earnings', icon: cashOutline, color: '#22c55e', route: '/earnings' },
                  { label: 'Incidents', icon: alertCircle, color: '#ef4444', route: '/incidents' },
                  { label: 'Charges', icon: ribbonOutline, color: '#f59e0b', route: '/charges' },
                  { label: 'Support', icon: chatbubbleEllipsesOutline, color: '#10b981', route: '/support' },
                  { label: 'History', icon: trendingUpOutline, color: '#6366f1', route: '/history' },
                  { label: 'Settings', icon: settingsOutline, color: '#94a3b8', route: '/settings' }
                ].map((item, i) => (
                  <div key={i} onClick={() => history.push(item.route)} style={{ 
                    padding: '16px 8px', 
                    background: g.card, 
                    ...g.glass, 
                    border: g.border, 
                    borderRadius: '16px', 
                    display: 'flex', 
                    flexDirection: 'column', 
                    alignItems: 'center', 
                    gap: '8px',
                    textAlign: 'center',
                    cursor: 'pointer'
                  }}>
                    <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: `${item.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <IonIcon icon={item.icon} style={{ fontSize: '18px', color: item.color }} />
                    </div>
                    <span style={{ fontSize: '10px', fontWeight: '700', color: '#cbd5e1' }}>{item.label}</span>
                  </div>
                ))}
             </div>
          </div>

          <div style={{ textAlign: 'center', padding: '30px 20px', opacity: 0.5 }}>
            <div style={{ fontSize: '11px', color: '#475569', fontStyle: 'italic', marginBottom: '4px' }}>"Drive safely. Your family is waiting."</div>
            <div style={{ fontSize: '9px', color: '#334155' }}>EuroTaxi v2.0 • Powered by Advanced AI</div>
          </div>

        </div>
      </IonContent>
    </IonPage>
  );
};

export default Dashboard;
