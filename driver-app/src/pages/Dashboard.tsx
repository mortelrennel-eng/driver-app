import React, { useEffect, useState } from 'react';
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
  calendarOutline,
  alertCircleOutline,
  megaphoneOutline,
  timeOutline,
  cameraOutline,
  documentTextOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { useTutorial } from '../context/TutorialContext';

// import { useGpsTracking } from '../hooks/useGpsTracking';

import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';

interface PerformanceData {
  driver_name: string;
  has_unit?: boolean;
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
  type: 'remittance' | 'incident' | 'system' | 'support' | 'ticket' | 'message';
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
  const { startTutorial, hasCompletedBefore } = useTutorial();
  const [data, setData] = useState<PerformanceData | null>(null);
  const [apiError, setApiError] = useState<string | null>(null);
  const [_isLoadingData, setIsLoadingData] = useState(true);
  const [showNotifModal, setShowNotifModal] = useState(false);
  const [notifications, setNotifications] = useState<DriverNotification[]>([]);
  const [notifLoading, setNotifLoading] = useState(false);
  const [announcement, setAnnouncement] = useState<any>(null);
  const [showAnnModal, setShowAnnModal] = useState(false);

  // ─── SOS Accident Alert States ───
  const [isPressingSos, setIsPressingSos] = useState(false);
  const [sosProgress, setSosProgress] = useState(0);
  const [sosLoading, setSosLoading] = useState(false);
  const [showAccidentModal, setShowAccidentModal] = useState(false);
  const [alertId, setAlertId] = useState<number | null>(null);
  const [description, setDescription] = useState('');
  const [photo, setPhoto] = useState<File | null>(null);
  const [submittingReport, setSubmittingReport] = useState(false);

  const sosTimerRef = React.useRef<any>(null);
  const sosIntervalRef = React.useRef<any>(null);

  // useGpsTracking(60000);

  const fetchLatestAnnouncement = async () => {
    try {
      const response = await cachedGet(endpoints.latestAnnouncement);
      if (response.data.success) {
        setAnnouncement(response.data.announcement);
      }
    } catch (e) {
      console.error('Failed to fetch announcement', e);
    }
  };

  const fetchNotifications = async () => {
    setNotifLoading(true);
    try {
      const response = await cachedGet(endpoints.notifications);
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
    const type = (notif.type || '').toLowerCase();
    const title = (notif.title || '').toLowerCase();
    const message = (notif.message || '').toLowerCase();

    if (type === 'remittance') return '/history';
    if (type === 'incident') return '/violations';
    
    // Smart Routing: Check type OR keywords in title/message
    if (
      type === 'support' || 
      type === 'ticket' || 
      type === 'message' ||
      title.includes('support') || 
      title.includes('ticket') || 
      title.includes('message') ||
      message.includes('support') ||
      message.includes('ticket')
    ) {
      return '/support';
    }

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
    fetchLatestAnnouncement();
    // Auto-start tutorial on first time
    if (!hasCompletedBefore()) {
      const t = setTimeout(() => {
        startTutorial();
      }, 500);
      return () => clearTimeout(t);
    }
    const interval = setInterval(() => {
      fetchPerformance();
      fetchLatestAnnouncement();
    }, 5 * 60 * 1000);
    return () => clearInterval(interval);
  }, [hasCompletedBefore, startTutorial]);

  // ─── SOS Handlers ───
  const handleSosPressStart = () => {
    setIsPressingSos(true);
    setSosProgress(0);
    
    // Animate progress ring
    let progress = 0;
    sosIntervalRef.current = setInterval(() => {
      progress += 100 / 30; // 30 intervals of 100ms = 3 seconds
      if (progress >= 100) progress = 100;
      setSosProgress(progress);
    }, 100);

    // Trigger after 3 seconds
    sosTimerRef.current = setTimeout(() => {
      clearInterval(sosIntervalRef.current);
      triggerSosAlert();
    }, 3000);
  };

  const handleSosPressEnd = () => {
    setIsPressingSos(false);
    setSosProgress(0);
    if (sosTimerRef.current) clearTimeout(sosTimerRef.current);
    if (sosIntervalRef.current) clearInterval(sosIntervalRef.current);
  };

  const triggerSosAlert = async () => {
    setIsPressingSos(false);
    setSosProgress(0);
    setSosLoading(true);
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(endpoints.sos, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({
          latitude: data?.latitude,
          longitude: data?.longitude
        })
      });

      // Safely parse JSON - server might return HTML on errors
      let resData: any = null;
      try {
        resData = await response.json();
      } catch (_) {
        // Server returned non-JSON (HTML error page) - still show accident form
        setAlertId(0);
        setShowAccidentModal(true);
        return;
      }

      if (resData && resData.success) {
        setAlertId(resData.data?.alert_id || 0);
        setShowAccidentModal(true);
      } else {
        // Even on error response, show the accident modal so driver can report
        setAlertId(0);
        setShowAccidentModal(true);
      }
    } catch (e: any) {
      // Network error - still show accident modal
      setAlertId(0);
      setShowAccidentModal(true);
    } finally {
      setSosLoading(false);
    }
  };


  const submitAccidentReport = async () => {
    if (!description.trim()) {
      alert('Please provide a brief description.');
      return;
    }
    setSubmittingReport(true);
    try {
      const formData = new FormData();
      formData.append('alert_id', String(alertId));
      formData.append('description', description);
      if (photo) {
        formData.append('photo', photo);
      }

      const token = localStorage.getItem('auth_token');
      const response = await fetch(endpoints.accidentReport, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });
      
      const resData = await response.json();
      if (resData.success) {
        setShowAccidentModal(false);
        setPhoto(null);
        setDescription('');
        alert('Accident Report Submitted. Management has been notified.');
      }
    } catch (e: any) {
      alert('Failed to submit report: ' + (e.response?.data?.message || e.message));
    } finally {
      setSubmittingReport(false);
    }
  };

  const fetchPerformance = async () => {
    try {
      setApiError(null);
      refreshUser();
      const response = await cachedGet(endpoints.driverPerformance);
      if (response.data.success) {
        const newData = response.data.data;
        setData(newData);
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
    Promise.all([fetchPerformance(), fetchLatestAnnouncement()]).then(() => event.detail.complete());
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
        <div id="dash-greeting" style={{ padding: '8px 20px', display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between' }}>
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
                id="dash-notif-btn"
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

          {/* ── Latest Announcement ── */}
          {announcement && (
            <div
              onClick={() => setShowAnnModal(true)}
              style={{
                margin: '0 20px 20px',
                padding: '14px 16px',
                background: t.goldBg,
                border: `1.5px solid ${t.gold}44`,
                borderRadius: '18px',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                WebkitTapHighlightColor: 'transparent',
              }}
            >
              {/* Icon */}
              <div style={{
                width: '44px', height: '44px', borderRadius: '13px', flexShrink: 0,
                background: t.goldGrad,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: `0 4px 12px ${t.gold}44`
              }}>
                <IonIcon icon={megaphoneOutline} style={{ fontSize: '22px', color: isDark ? '#000' : '#fff' }} />
              </div>

              {/* Title + Date */}
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: '10px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '0.8px', marginBottom: '3px' }}>
                  ADMIN ANNOUNCEMENT
                </div>
                <div style={{ fontSize: '14px', fontWeight: '900', color: t.textPrimary, whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                  {announcement.title || 'Announcement'}
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: '4px', marginTop: '4px' }}>
                  <IonIcon icon={timeOutline} style={{ fontSize: '11px', color: t.textMuted }} />
                  <span style={{ fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
                    {new Date(announcement.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                  </span>
                </div>
              </div>

              {/* Chevron */}
              <div style={{ width: '28px', height: '28px', borderRadius: '8px', flexShrink: 0, background: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: t.gold }} />
              </div>
            </div>
          )}

          {/* ── Announcement Detail Modal ── */}
          {showAnnModal && announcement && (
            <div
              onClick={() => setShowAnnModal(false)}
              style={{
                position: 'fixed', inset: 0, zIndex: 9999,
                background: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(8px)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                padding: '20px',
                animation: 'annFadeIn 0.15s ease'
              }}
            >
              <div
                onClick={e => e.stopPropagation()}
                style={{
                  width: '100%', maxWidth: '520px',
                  background: t.card, borderRadius: '28px',
                  overflow: 'hidden', boxShadow: '0 12px 48px rgba(0,0,0,0.35)',
                  animation: 'annScaleIn 0.2s cubic-bezier(.32,1.2,.4,1)',
                  maxHeight: '80vh', overflowY: 'auto'
                }}
              >
                {/* Header */}
                <div style={{ background: t.goldGrad, padding: '20px', display: 'flex', alignItems: 'center', justifyContent: 'space-between', position: 'sticky', top: 0, zIndex: 1 }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                    <IonIcon icon={megaphoneOutline} style={{ fontSize: '22px', color: isDark ? '#000' : '#fff' }} />
                    <span style={{ fontSize: '15px', fontWeight: '900', color: isDark ? '#000' : '#fff' }}>Announcement</span>
                  </div>
                  <button onClick={() => setShowAnnModal(false)} style={{ background: 'rgba(0,0,0,0.15)', border: 'none', borderRadius: '10px', padding: '8px', cursor: 'pointer', display: 'flex', alignItems: 'center' }}>
                    <IonIcon icon={closeOutline} style={{ fontSize: '20px', color: isDark ? '#000' : '#fff' }} />
                  </button>
                </div>
                {/* Body */}
                <div style={{ padding: '24px 20px 44px' }}>
                  <div style={{ fontSize: '19px', fontWeight: '900', color: t.textPrimary, marginBottom: '12px', lineHeight: '1.3' }}>
                    {announcement.title || 'Announcement'}
                  </div>
                  <div style={{ display: 'flex', gap: '16px', marginBottom: '20px', flexWrap: 'wrap' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                      <IonIcon icon={timeOutline} style={{ fontSize: '13px', color: t.gold }} />
                      <span style={{ fontSize: '12px', fontWeight: '700', color: t.textMuted }}>
                        Posted {new Date(announcement.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </span>
                    </div>
                    {announcement.valid_until && (
                      <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                        <IonIcon icon={calendarOutline} style={{ fontSize: '13px', color: t.gold }} />
                        <span style={{ fontSize: '12px', fontWeight: '700', color: t.textMuted }}>
                          Until {new Date(announcement.valid_until).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </span>
                      </div>
                    )}
                  </div>
                  <div style={{ height: '1px', background: isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)', marginBottom: '20px' }} />
                  <div style={{ fontSize: '15px', color: t.textSecondary, lineHeight: '1.8', fontWeight: '500', whiteSpace: 'pre-wrap' }}>
                    {announcement.message || 'No additional details.'}
                  </div>
                </div>
              </div>
            </div>
          )}

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
            <div
              onClick={() => history.push('/settings', { openView: 'profile' })}
              style={{
                margin: '0 20px 12px', padding: '12px 16px',
                background: 'rgba(239,68,68,0.1)',
                border: '1px solid rgba(239,68,68,0.35)',
                borderRadius: '14px',
                display: 'flex', alignItems: 'center', gap: '10px', cursor: 'pointer',
                WebkitTapHighlightColor: 'transparent',
              }}
            >
              <IonIcon icon={warningOutline} style={{ fontSize: '18px', color: '#ef4444', flexShrink: 0 }} />
              <span style={{ fontSize: '12px', fontWeight: '700', color: '#ef4444', flex: 1 }}>
                Complete your profile &amp; emergency info
              </span>
              <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: '#ef4444' }} />
            </div>
          )}

          {/* ── Coding Banner ── */}
          {data && (
            data.has_unit !== false ? (
              <div id="dash-coding-status" style={{ margin: '0 20px 16px', padding: '20px 18px', borderRadius: '20px', background: data.is_coding ? (isDark ? 'linear-gradient(135deg, rgba(239,68,68,0.2) 0%, rgba(239,68,68,0.1) 100%)' : 'linear-gradient(135deg, rgba(239,68,68,0.12) 0%, rgba(239,68,68,0.05) 100%)') : (isDark ? 'linear-gradient(135deg, rgba(34,197,94,0.15) 0%, rgba(34,197,94,0.05) 100%)' : 'linear-gradient(135deg, rgba(34,197,94,0.12) 0%, rgba(34,197,94,0.04) 100%)'), border: `1px solid ${data.is_coding ? 'rgba(239,68,68,0.4)' : 'rgba(34,197,94,0.3)'}`, display: 'flex', alignItems: 'center', gap: '14px', boxShadow: '0 4px 12px rgba(0,0,0,0.06)' }}>
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
            ) : (
              <div style={{ margin: '0 20px 16px', padding: '20px 18px', borderRadius: '20px', background: isDark ? 'linear-gradient(135deg, rgba(239,68,68,0.2) 0%, rgba(239,68,68,0.1) 100%)' : 'linear-gradient(135deg, rgba(239,68,68,0.12) 0%, rgba(239,68,68,0.05) 100%)', border: '1px solid rgba(239,68,68,0.4)', display: 'flex', alignItems: 'center', gap: '14px', boxShadow: '0 4px 12px rgba(0,0,0,0.06)' }}>
                <IonIcon icon={warningOutline} style={{ fontSize: '24px', color: '#ef4444' }} />
                <div style={{ flex: 1 }}>
                  <div style={{ fontSize: '15px', fontWeight: '800', color: isDark ? '#fca5a5' : '#b91c1c', letterSpacing: '0.3px' }}>
                    Coding Information
                  </div>
                  <div style={{ fontSize: '12px', color: isDark ? '#fca5a5' : '#ef4444', marginTop: '4px', fontWeight: '800' }}>
                    no assign unit please contact the admin
                  </div>
                </div>
              </div>
            )
          )}

          {/* ── Boundary Progress Hero ── */}
          <div id="dash-boundary-card" style={{ margin: '0 20px 16px', padding: '24px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.shadow }}>
            {data && data.has_unit === false ? (
              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', padding: '12px 0', gap: '12px' }}>
                <div style={{ width: '48px', height: '48px', borderRadius: '50%', background: 'rgba(239,68,68,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <IonIcon icon={warningOutline} style={{ fontSize: '24px', color: '#ef4444' }} />
                </div>
                <div style={{ textAlign: 'center' }}>
                  <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary, marginBottom: '4px' }}>Boundary Progress</div>
                  <div style={{ fontSize: '13px', color: '#ef4444', fontWeight: '800' }}>
                    no assign unit please contact the admin
                  </div>
                </div>
              </div>
            ) : (
              <>
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
                        t.subtleBg,
                      color: 
                        ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? '#22c55e' : 
                        data?.gps_status?.toLowerCase() === 'idle' ? '#fbbf24' :
                        '#94a3b8',
                      fontSize: '10px',
                      fontWeight: '800',
                      border: `1px solid ${
                        ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? 'rgba(34,197,94,0.3)' : 
                        data?.gps_status?.toLowerCase() === 'idle' ? 'rgba(234,179,8,0.3)' :
                        (isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)')
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
                          '#94a3b8',
                        boxShadow: ['active', 'moving'].includes(data?.gps_status?.toLowerCase() || '') ? '0 0 6px #22c55e' : 'none'
                      }}></div>
                      {
                        !data?.gps_status || ['offline', 'stopped', 'park'].includes(data.gps_status.toLowerCase()) ? 'OFFLINE' :
                        data.gps_status.toLowerCase() === 'idle' ? 'PARKED' :
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
              </>
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
          <div id="dash-toolbox" style={{ margin: '0 20px 8px' }}>
             <h3 style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1.5px', marginBottom: '10px', paddingLeft: '4px' }}>Driver Toolbox</h3>
             <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: '8px', padding: '8px 0' }}>
                {[
                  { label: 'Stats', icon: statsChartOutline, color: '#8b5cf6', bg: '#8b5cf6', route: '/performance' },
                  { label: 'Vehicle', icon: carSportOutline, color: '#06b6d4', bg: '#06b6d4', route: '/vehicle' },
                  { label: 'History', icon: cashOutline, color: '#22c55e', bg: '#22c55e', route: '/history' },
                  { label: 'Violations', icon: warningOutline, color: '#ef4444', bg: '#ef4444', route: '/violations' },
                  { label: 'Accidents', icon: megaphoneOutline, color: '#f97316', bg: '#f97316', route: '/accidents' },
                  { label: 'Debts', icon: alertCircle, color: '#f59e0b', bg: '#f59e0b', route: '/debts' },
                  { label: 'Incentives', icon: ribbonOutline, color: '#eab308', bg: '#eab308', route: '/incentives' },
                  { label: 'News', icon: documentTextOutline, color: '#ea580c', bg: '#ea580c', route: '/announcements' }
                ].map((item, i) => (
                  <div key={i} onClick={() => history.push(item.route)} style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: '6px',
                    cursor: 'pointer',
                    padding: '8px 4px',
                    borderRadius: '12px',
                    WebkitTapHighlightColor: 'transparent',
                  }}>
                    <div style={{
                      width: '40px', height: '40px', borderRadius: '12px',
                      background: `${item.bg}18`,
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                      <IonIcon icon={item.icon} style={{ fontSize: '20px', color: item.color }} />
                    </div>
                    <span style={{ fontSize: '10px', fontWeight: '700', color: t.textSecondary, textAlign: 'center', lineHeight: '1.2' }}>{item.label}</span>
                  </div>
                ))}
             </div>
             
             {/* ── Emergency Button ── */}
             <div style={{ marginTop: '80px', width: '100%' }}>
               <div 
                 id="dash-sos-btn"
                 onTouchStart={handleSosPressStart}
                 onTouchEnd={handleSosPressEnd}
                 onMouseDown={handleSosPressStart}
                 onMouseUp={handleSosPressEnd}
                 onMouseLeave={handleSosPressEnd}
                 style={{
                   position: 'relative',
                   width: '100%',
                   height: '58px',
                   borderRadius: '16px',
                   background: isPressingSos ? '#b91c1c' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                   boxShadow: isPressingSos ? '0 0 0 6px rgba(239, 68, 68, 0.4)' : '0 8px 24px rgba(239, 68, 68, 0.35)',
                   display: 'flex',
                   alignItems: 'center',
                   justifyContent: 'center',
                   gap: '10px',
                   cursor: 'pointer',
                   transition: 'all 0.2s cubic-bezier(0.4, 0, 0.2, 1)',
                   transform: isPressingSos ? 'scale(0.96)' : 'scale(1)',
                   overflow: 'hidden',
                   userSelect: 'none',
                   WebkitUserSelect: 'none'
                 }}
               >
               {/* Progress Animation Fill from Bottom */}
               {isPressingSos && (
                 <div style={{
                   position: 'absolute',
                   bottom: 0,
                   left: 0,
                   width: '100%',
                   height: `${sosProgress}%`,
                   background: 'rgba(255, 255, 255, 0.3)',
                   transition: 'height 0.1s linear'
                 }} />
               )}
               
               {sosLoading ? (
                 <IonSpinner color="light" />
               ) : (
                 <>
                   <IonIcon icon={alertCircleOutline} style={{ color: 'white', fontSize: '24px', zIndex: 2, pointerEvents: 'none' }} />
                   <span style={{ color: 'white', fontWeight: '900', fontSize: '16px', letterSpacing: '1px', zIndex: 2, pointerEvents: 'none' }}>EMERGENCY</span>
                 </>
               )}
             </div>
             </div>
          </div>

          <div style={{ textAlign: 'center', padding: '30px 20px', opacity: 0.5 }}>
            <div style={{ fontSize: '11px', color: t.textMuted, fontStyle: 'italic' }}>"Drive safely. Your family is waiting."</div>
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

        {/* ── SOS Fullscreen Overlay Countdown ── */}
        {isPressingSos && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100vw',
            height: '100vh',
            background: 'rgba(239, 68, 68, 0.95)',
            zIndex: 998,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'white',
            backdropFilter: 'blur(8px)',
            WebkitBackdropFilter: 'blur(8px)',
            animation: 'annFadeIn 0.2s ease-out'
          }}>
            <IonIcon icon={alertCircleOutline} style={{ fontSize: '80px', marginBottom: '20px' }} />
            <h2 style={{ fontSize: '28px', fontWeight: '900', margin: '0 0 10px', textTransform: 'uppercase', letterSpacing: '1px', textAlign: 'center' }}>Sending Emergency Alert</h2>
            <p style={{ fontSize: '16px', fontWeight: '600', opacity: 0.9, marginBottom: '40px' }}>Keep holding to trigger alert</p>
            <div style={{ 
              fontSize: '140px', 
              fontWeight: '900', 
              lineHeight: 1,
              textShadow: '0 10px 30px rgba(0,0,0,0.3)',
              transform: `scale(${1 + (sosProgress / 100) * 0.2})`,
              transition: 'transform 0.1s linear'
            }}>
              {Math.max(1, Math.ceil(3 - (sosProgress / 100 * 3)))}
            </div>
          </div>
        )}



        {/* ── Accident Report Modal ── */}
        <IonModal
          isOpen={showAccidentModal}
          onDidDismiss={() => setShowAccidentModal(false)}
          backdropDismiss={false}
          style={{ '--height': 'auto', '--border-radius': '24px', '--width': '92%' } as any}
        >
          <div style={{ padding: '24px', background: t.bg }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
              <div style={{ width: '48px', height: '48px', borderRadius: '14px', background: 'rgba(239,68,68,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IonIcon icon={alertCircle} style={{ fontSize: '28px', color: '#ef4444' }} />
              </div>
              <div>
                <h2 style={{ margin: 0, fontSize: '20px', fontWeight: '900', color: '#ef4444' }}>Emergency Alert Sent!</h2>
                <p style={{ margin: 0, fontSize: '12px', color: t.textSecondary }}>Management has been alerted.</p>
              </div>
            </div>

            <p style={{ fontSize: '13px', color: t.textPrimary, marginBottom: '16px', fontWeight: '600' }}>
              Are you safe? Please provide a quick report of the incident.
            </p>



            <div style={{ marginBottom: '16px' }}>
              <label style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', marginBottom: '6px', display: 'block' }}>Description (What happened?)</label>
              <textarea 
                value={description}
                onChange={(e) => setDescription(e.target.value)}
                rows={3}
                placeholder="Briefly describe the accident..."
                style={{ width: '100%', padding: '12px', borderRadius: '12px', border: t.borderSubtle, background: t.subtleBg, color: t.textPrimary, fontSize: '14px', outline: 'none', resize: 'none' }}
              />
            </div>

            <div style={{ marginBottom: '24px' }}>
              <label style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', marginBottom: '6px', display: 'block' }}>Attach Photo (Optional)</label>
              <label style={{
                display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
                padding: '20px', border: `2px dashed ${t.borderSubtle.split(' ')[2]}`, borderRadius: '16px',
                background: t.subtleBg, cursor: 'pointer'
              }}>
                <IonIcon icon={cameraOutline} style={{ fontSize: '28px', color: t.textMuted, marginBottom: '8px' }} />
                <span style={{ fontSize: '12px', fontWeight: '600', color: t.textSecondary }}>
                  {photo ? photo.name : 'Tap to upload photo'}
                </span>
                <input 
                  type="file" 
                  accept="image/*" 
                  onChange={(e) => { if(e.target.files && e.target.files[0]) setPhoto(e.target.files[0]) }}
                  style={{ display: 'none' }}
                />
              </label>
            </div>

            <div style={{ display: 'flex', gap: '12px' }}>
              <button 
                onClick={() => setShowAccidentModal(false)}
                disabled={submittingReport}
                style={{ flex: 1, padding: '14px', borderRadius: '14px', border: 'none', background: t.subtleBg, color: t.textSecondary, fontWeight: '800', fontSize: '14px' }}
              >
                Skip For Now
              </button>
              <button 
                onClick={submitAccidentReport}
                disabled={submittingReport || !description.trim()}
                style={{ flex: 2, padding: '14px', borderRadius: '14px', border: 'none', background: '#ef4444', color: 'white', fontWeight: '900', fontSize: '14px', opacity: (!description.trim() || submittingReport) ? 0.6 : 1 }}
              >
                {submittingReport ? 'Submitting...' : 'Send Report'}
              </button>
            </div>
          </div>
        </IonModal>

      </IonContent>

      <style>{`
        @keyframes annFadeIn  { from { opacity: 0 } to { opacity: 1 } }
        @keyframes annScaleIn { from { transform: scale(0.95); opacity: 0 } to { transform: scale(1); opacity: 1 } }
      `}</style>
    </IonPage>
  );
};

export default Dashboard;
