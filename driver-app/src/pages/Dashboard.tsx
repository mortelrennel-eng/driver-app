import { useEffect, useState } from 'react';
import type { FC } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonRefresher,
  IonRefresherContent,
  IonHeader,
  IonToolbar,
  IonModal,
  IonSpinner
} from '@ionic/react';
import {
  alertCircle,
  carSportOutline,
  statsChartOutline,
  notificationsOutline,
  shieldCheckmarkOutline,
  cashOutline,
  warningOutline,
  chevronForwardOutline,
  ribbonOutline,
  closeOutline,
  timeOutline,
  alertCircleOutline,
  megaphoneOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
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
  plate_number?: string;
  unit_model?: string;
  unit_make?: string;
}

interface DriverNotification {
  id: string;
  type: 'remittance' | 'incident' | 'system';
  title: string;
  message: string;
  timestamp: string;
  time_display: string;
  severity: 'success' | 'warning' | 'danger' | 'info';
  icon: string;
}

const Dashboard: FC = () => {
  const { user, logout, refreshUser } = useAuth();
  const { t, isDark } = useTheme();
  const history = useHistory();
  const [data, setData] = useState<PerformanceData | null>(null);
  const [apiError, setApiError] = useState<string | null>(null);
  const [_isLoadingData, setIsLoadingData] = useState(true);
  const [showNotifModal, setShowNotifModal] = useState(false);
  const [notifications, setNotifications] = useState<DriverNotification[]>([]);
  const [notifLoading, setNotifLoading] = useState(false);

  useGpsTracking(60000);

  const fetchNotifications = async () => {
    setNotifLoading(true);
    try {
      const response = await axios.get(endpoints.notifications);
      if (response.data.success) {
        setNotifications(response.data.notifications);
      }
    } catch (e) {
      console.error('Failed to fetch notifications', e);
    } finally {
      setNotifLoading(false);
    }
  };

  const getNotifIcon = (iconName: string) => {
    switch(iconName) {
      case 'cash-outline': return cashOutline;
      case 'alert-circle-outline': return alertCircleOutline;
      case 'megaphone-outline': return megaphoneOutline;
      default: return notificationsOutline;
    }
  };

  const getNotifColor = (severity: string) => {
    switch(severity) {
      case 'success': return '#22c55e';
      case 'danger': return '#ef4444';
      case 'warning': return '#eab308';
      default: return '#3b82f6';
    }
  };

  const getNotifRoute = (notif: DriverNotification) => {
    if (notif.type === 'remittance') return '/history';
    if (notif.type === 'incident') return '/incidents';
    return '/dashboard';
  };

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
  const progressColor = progress >= 100 ? '#22c55e' : shortage > 0 ? '#ef4444' : t.gold;

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
          <div style={{ minHeight: '100vh', background: t.bg, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '32px' }}>
            <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(239,68,68,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '24px' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '40px', color: '#ef4444' }} />
            </div>
            <h1 style={{ color: t.textPrimary, fontSize: '24px', fontWeight: '800', margin: '0 0 8px' }}>Access Restricted</h1>
            <p style={{ color: t.textSecondary, textAlign: 'center', marginBottom: '32px' }}>Please contact the EuroTaxi office regarding your account status.</p>
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
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
              <div style={{ width: '48px', height: '48px', borderRadius: '16px', background: t.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', boxShadow: `0 6px 20px ${isDark ? 'rgba(234,179,8,0.25)' : 'rgba(202,138,4,0.2)'}` }}>
                <span style={{ fontSize: '20px', fontWeight: '900', color: isDark ? '#0a0e1a' : '#fff' }}>{(user?.name || 'D')[0].toUpperCase()}</span>
              </div>
              <div>
                <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary, letterSpacing: '-0.4px', lineHeight: '1.2' }}>EuroTaxi Driver</div>
                <div style={{ fontSize: '11px', color: t.gold, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px' }}>{user?.name || 'Driver'}</div>
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
              <button 
                onClick={() => { setShowNotifModal(true); fetchNotifications(); }} 
                style={{ background: t.backBtnBg, border: t.borderSubtle, borderRadius: '14px', padding: '10px', cursor: 'pointer', position: 'relative' }}
              >
                <IonIcon icon={notificationsOutline} style={{ fontSize: '20px', color: t.textPrimary }} />
                <div style={{ position: 'absolute', top: '8px', right: '8px', width: '8px', height: '8px', background: '#ef4444', borderRadius: '50%', border: `2px solid ${t.bg}` }}></div>
              </button>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY={true}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: t.bg, paddingBottom: '120px' }}>

          {/* ── Real-time Clock Bar ── */}
          <div style={{ margin: '0 20px 20px', padding: '12px 16px', background: t.subtleBg, border: t.borderSubtle, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <div style={{ display: 'flex', flexDirection: 'column' }}>
               <span style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px' }}>{formatDate(currentTime)}</span>
               <span style={{ fontSize: '12px', fontWeight: '700', color: t.textSecondary }}>{currentTime.toLocaleDateString(undefined, { weekday: 'long' })}</span>
            </div>
            <div style={{ textAlign: 'right' }}>
              <div style={{ fontSize: '20px', fontWeight: '900', color: t.gold, letterSpacing: '1px' }}>
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
                <div style={{ fontSize: '11px', color: t.textSecondary, lineHeight: '1.4' }}>{apiError}</div>
              </div>
            </div>
          )}
          
          {/* ── Profile Incomplete Banner ── */}
          {data?.profile_incomplete && (
            <div onClick={() => history.push('/settings')} style={{ margin: '0 20px 12px', padding: '12px 16px', background: `${t.gold}1e`, border: `1px solid ${t.gold}4d`, borderRadius: '14px', display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer' }}>
              <IonIcon icon={warningOutline} style={{ fontSize: '18px', color: t.gold }} />
              <span style={{ fontSize: '12px', fontWeight: '700', color: t.gold }}>Complete your profile & emergency info in Settings</span>
              <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: t.gold, marginLeft: 'auto' }} />
            </div>
          )}

          {/* ── Coding Banner ── */}
          {data && (
            <div style={{ margin: '0 20px 16px', padding: '20px 18px', borderRadius: '20px', background: data.is_coding ? (isDark ? 'linear-gradient(135deg, rgba(239,68,68,0.2) 0%, rgba(239,68,68,0.1) 100%)' : 'linear-gradient(135deg, rgba(239,68,68,0.12) 0%, rgba(239,68,68,0.05) 100%)') : (isDark ? 'linear-gradient(135deg, rgba(34,197,94,0.15) 0%, rgba(34,197,94,0.05) 100%)' : 'linear-gradient(135deg, rgba(34,197,94,0.12) 0%, rgba(34,197,94,0.04) 100%)'), border: `1px solid ${data.is_coding ? 'rgba(239,68,68,0.4)' : 'rgba(34,197,94,0.3)'}`, display: 'flex', alignItems: 'center', gap: '14px', boxShadow: '0 4px 12px rgba(0,0,0,0.06)' }}>
              <div style={{ width: '12px', height: '12px', borderRadius: '50%', background: data.is_coding ? '#ef4444' : '#22c55e', boxShadow: `0 0 12px ${data.is_coding ? '#ef4444' : '#22c55e'}`, animation: 'pulse 2s infinite' }}></div>
              <div style={{ flex: 1 }}>
                <div style={{ fontSize: '15px', fontWeight: '800', color: data.is_coding ? (isDark ? '#fca5a5' : '#b91c1c') : (isDark ? '#86efac' : '#15803d'), letterSpacing: '0.3px' }}>
                  {data.is_coding ? data.coding_message : 'No Coding Today — Drive Freely!'}
                </div>
                {data.coding_day_name && (
                  <div style={{ fontSize: '11px', color: isDark ? t.textSecondary : '#374151', marginTop: '4px', fontWeight: '600' }}>
                    Your Schedule: <span style={{ color: isDark ? t.textPrimary : '#111827', fontWeight: '800' }}>{data.coding_day_name}</span> {data.next_coding_date && `• Next: ${new Date(data.next_coding_date).toLocaleDateString()}`}
                  </div>
                )}
              </div>
              {data.is_coding && <IonIcon icon={warningOutline} style={{ fontSize: '24px', color: '#ef4444', opacity: 0.6 }} />}
            </div>
          )}

          {/* ── Boundary Progress Hero ── */}
          <div style={{ margin: '0 20px 16px', padding: '24px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.shadow }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
              <div>
                <div style={{ fontSize: '10px', fontWeight: '800', color: t.textSecondary, textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '4px' }}>
                  Boundary Progress
                  {data?.boundary_target_label && <span style={{ marginLeft: '8px', color: t.gold }}>{data.boundary_target_label}</span>}
                </div>
                <div style={{ fontSize: '32px', fontWeight: '900', color: t.textPrimary, lineHeight: 1 }}>
                  ₱{(data?.boundary_actual ?? 0).toLocaleString()}
                </div>
                <div style={{ fontSize: '12px', color: t.textMuted, marginTop: '4px' }}>
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
                    ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? 'rgba(34,197,94,0.15)' : 
                    data?.gps_status?.toLowerCase() === 'idle' ? 'rgba(234,179,8,0.15)' :
                    data?.gps_status?.toLowerCase() === 'stopped' ? 'rgba(239,68,68,0.15)' : t.subtleBg,
                  color: 
                    ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? '#22c55e' : 
                    data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' :
                    data?.gps_status?.toLowerCase() === 'stopped' ? '#ef4444' : '#94a3b8',
                  fontSize: '10px',
                  fontWeight: '800',
                  border: `1px solid ${
                    ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? 'rgba(34,197,94,0.3)' : 
                    data?.gps_status?.toLowerCase() === 'idle' ? 'rgba(234,179,8,0.3)' :
                    data?.gps_status?.toLowerCase() === 'stopped' ? 'rgba(239,68,68,0.3)' : (isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)')
                  }`,
                  marginBottom: '8px'
                }}>
                  <div style={{ 
                    width: '5px', 
                    height: '5px', 
                    borderRadius: '50%', 
                    background: 
                      ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? '#22c55e' : 
                      data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' :
                      data?.gps_status?.toLowerCase() === 'stopped' ? '#ef4444' : '#94a3b8',
                    boxShadow: ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? '0 0 6px #22c55e' : 'none'
                  }}></div>
                  {
                    !data?.gps_status || data.gps_status.toLowerCase() === 'offline' ? 'OFFLINE' :
                    data.gps_status.toLowerCase() === 'idle' ? 'PARKED' :
                    data.gps_status.toLowerCase() === 'stopped' ? 'STOPPED' :
                    ['active', 'moving'].includes(data.gps_status.toLowerCase()) ? 'MOVING' : data.gps_status.toUpperCase()
                  }
                </div>
                <div style={{ fontSize: '28px', fontWeight: '900', color: progressColor }}>{progress}%</div>
                {shortage > 0 && <div style={{ fontSize: '11px', color: '#ef4444', fontWeight: '600' }}>-₱{shortage.toLocaleString()} short</div>}
              </div>
            </div>
            {/* Progress Bar */}
            <div style={{ height: '8px', background: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)', borderRadius: '4px', overflow: 'hidden' }}>
              <div style={{ height: '100%', width: `${Math.min(progress, 100)}%`, background: progressColor, borderRadius: '4px', transition: 'width 0.6s ease' }}></div>
            </div>
            {data?.message && (
              <div style={{ marginTop: '14px', padding: '10px 12px', background: t.subtleBg, borderRadius: '10px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '16px', color: t.gold }} />
                <span style={{ fontSize: '11px', color: t.textSecondary, fontStyle: 'italic' }}>{data.message}</span>
              </div>
            )}
          </div>



          {/* ── Driver Quick Profile (Combined & Minimal) ── */}
          <div style={{ margin: '0 20px 16px', padding: '12px 14px', background: t.subtleBg, border: t.borderSubtle, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '8px' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flex: 1, minWidth: 0 }}>
              <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: t.goldBg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <IonIcon icon={carSportOutline} style={{ fontSize: '16px', color: t.gold }} />
              </div>
              <div style={{ minWidth: 0 }}>
                <div style={{ fontSize: '11px', fontWeight: '800', color: t.textPrimary, lineHeight: '1.2' }}>{data?.unit_make} {data?.unit_model}</div>
                <div style={{ fontSize: '11px', fontWeight: '800', color: t.textPrimary, lineHeight: '1.2' }}>({data?.plate_number})</div>
                <div style={{ fontSize: '9px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', marginTop: '2px' }}>Assigned Taxi</div>
              </div>
            </div>
            
            <div style={{ height: '20px', width: '1px', background: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)', flexShrink: 0 }}></div>
            
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flex: 1, justifyContent: 'flex-end', minWidth: 0 }}>
              <div style={{ textAlign: 'right', minWidth: 0 }}>
                <div style={{ fontSize: '11px', fontWeight: '800', color: t.textPrimary, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{data?.license_number || '—'}</div>
                <div style={{ fontSize: '9px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase' }}>Driver License</div>
              </div>
              <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '16px', color: '#3b82f6' }} />
              </div>
            </div>
          </div>

          {/* ── Driver Tools (GCash-style Icon Grid) ── */}
          <div style={{ margin: '0 20px 8px' }}>
             <h3 style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1.5px', marginBottom: '10px', paddingLeft: '4px' }}>Driver Toolbox</h3>
             <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '6px', padding: '8px 0' }}>
                {[
                  { label: 'Stats', icon: statsChartOutline, color: '#8b5cf6', bg: '#8b5cf6', route: '/performance' },
                  { label: 'Vehicle', icon: carSportOutline, color: '#06b6d4', bg: '#06b6d4', route: '/vehicle' },
                  { label: 'History', icon: cashOutline, color: '#22c55e', bg: '#22c55e', route: '/history' },
                  { label: 'Incidents', icon: alertCircle, color: '#ef4444', bg: '#ef4444', route: '/incidents' },
                  { label: 'Charges', icon: ribbonOutline, color: '#f59e0b', bg: '#f59e0b', route: '/charges' }
                ].map((item, i) => (
                  <div key={i} onClick={() => history.push(item.route)} style={{ 
                    display: 'flex', 
                    flexDirection: 'column', 
                    alignItems: 'center', 
                    gap: '6px',
                    cursor: 'pointer',
                    padding: '8px 4px'
                  }}>
                    <div style={{ 
                      width: '40px', height: '40px', borderRadius: '14px', 
                      background: `${item.bg}18`, 
                      display: 'flex', alignItems: 'center', justifyContent: 'center'
                    }}>
                      <IonIcon icon={item.icon} style={{ fontSize: '20px', color: item.color }} />
                    </div>
                    <span style={{ fontSize: '10px', fontWeight: '700', color: t.textSecondary, textAlign: 'center', lineHeight: '1.2' }}>{item.label}</span>
                  </div>
                ))}
             </div>
          </div>

          <div style={{ textAlign: 'center', padding: '30px 20px', opacity: 0.5 }}>
            <div style={{ fontSize: '11px', color: t.textMuted, fontStyle: 'italic', marginBottom: '4px' }}>"Drive safely. Your family is waiting."</div>
            <div style={{ fontSize: '9px', color: t.textMuted }}>EuroTaxi v2.0 • Powered by Advanced AI</div>
          </div>

        </div>

        {/* ── Notification Modal ── */}
        <IonModal
          isOpen={showNotifModal}
          onDidDismiss={() => setShowNotifModal(false)}
          style={{ '--height': 'auto', '--max-height': '75vh', '--width': '92%', '--border-radius': '24px' } as any}
        >
          <div style={{ background: t.bg, padding: '20px', maxHeight: '75vh', overflowY: 'auto' }}>
            {/* Modal Header */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <IonIcon icon={notificationsOutline} style={{ fontSize: '20px', color: t.gold }} />
                <span style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary }}>Notifications</span>
              </div>
              <button onClick={() => setShowNotifModal(false)} style={{ background: t.subtleBg, border: 'none', borderRadius: '10px', padding: '8px', cursor: 'pointer', display: 'flex' }}>
                <IonIcon icon={closeOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
              </button>
            </div>

            {/* Notification List */}
            {notifLoading && notifications.length === 0 ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '30px' }}>
                <IonSpinner color="warning" />
              </div>
            ) : notifications.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '30px 10px' }}>
                <IonIcon icon={notificationsOutline} style={{ fontSize: '40px', color: t.textMuted, opacity: 0.3, marginBottom: '12px' }} />
                <div style={{ fontSize: '14px', fontWeight: '700', color: t.textSecondary }}>No notifications yet</div>
                <div style={{ fontSize: '12px', color: t.textMuted, marginTop: '4px' }}>Activity will appear here.</div>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {notifications.map(notif => {
                  const color = getNotifColor(notif.severity);
                  return (
                    <div 
                      key={notif.id} 
                      onClick={() => { setShowNotifModal(false); history.push(getNotifRoute(notif)); }}
                      style={{ 
                        padding: '12px', 
                        background: t.subtleBg, 
                        borderRadius: '14px', 
                        display: 'flex', 
                        alignItems: 'flex-start', 
                        gap: '12px', 
                        cursor: 'pointer',
                        border: isDark ? '1px solid rgba(255,255,255,0.04)' : '1px solid rgba(0,0,0,0.04)'
                      }}
                    >
                      <div style={{ 
                        width: '32px', height: '32px', borderRadius: '10px', 
                        background: `${color}15`, 
                        display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 
                      }}>
                        <IonIcon icon={getNotifIcon(notif.icon)} style={{ fontSize: '16px', color }} />
                      </div>
                      <div style={{ flex: 1, minWidth: 0 }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2px' }}>
                          <span style={{ fontSize: '13px', fontWeight: '800', color: t.textPrimary }}>{notif.title}</span>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '3px', flexShrink: 0 }}>
                            <IonIcon icon={timeOutline} style={{ fontSize: '9px', color: t.textMuted }} />
                            <span style={{ fontSize: '9px', color: t.textMuted, fontWeight: '600' }}>{notif.time_display}</span>
                          </div>
                        </div>
                        <div style={{ fontSize: '11px', color: t.textSecondary, lineHeight: '1.4', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{notif.message}</div>
                      </div>
                      <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: t.textMuted, flexShrink: 0, marginTop: '8px' }} />
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </IonModal>

      </IonContent>
    </IonPage>
  );
};

export default Dashboard;
