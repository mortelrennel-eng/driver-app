import { useEffect, useState } from 'react';
import type { FC } from 'react';
import { 
  IonContent, 
  IonPage, 
  IonIcon, 
  IonHeader, 
  IonToolbar, 
  IonRefresher, 
  IonRefresherContent,
  IonSpinner
} from '@ionic/react';
import { 
  arrowBackOutline, 
  notificationsOutline, 
  timeOutline, 
  cashOutline, 
  alertCircleOutline, 
  megaphoneOutline,
  sparklesOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';
import axios from 'axios';
import { endpoints } from '../config/api';

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

const Notifications: FC = () => {
  const history = useHistory();
  const { t, isDark } = useTheme();
  const [notifications, setNotifications] = useState<DriverNotification[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchNotifications = async () => {
    try {
      const response = await axios.get(endpoints.notifications);
      if (response.data.success) {
        setNotifications(response.data.notifications);
        localStorage.setItem('cached_driver_notifications', JSON.stringify(response.data.notifications));
      }
    } catch (e) {
      console.error('Failed to fetch notifications', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const cached = localStorage.getItem('cached_driver_notifications');
    if (cached) {
      try {
        setNotifications(JSON.parse(cached));
        setLoading(false);
      } catch (e) {}
    }
    fetchNotifications();
  }, []);

  const doRefresh = (event: CustomEvent) => {
    fetchNotifications().then(() => event.detail.complete());
  };

  const getIcon = (iconName: string) => {
    switch(iconName) {
      case 'cash-outline': return cashOutline;
      case 'alert-circle-outline': return alertCircleOutline;
      case 'megaphone-outline': return megaphoneOutline;
      default: return notificationsOutline;
    }
  };

  const getSeverityColor = (severity: string) => {
    switch(severity) {
      case 'success': return '#22c55e';
      case 'danger': return '#ef4444';
      case 'warning': return '#eab308';
      default: return '#3b82f6';
    }
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Notifications</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Activity & System Alerts</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, paddingBottom: '40px' }}>

          {/* Hero Banner */}
          <div style={{ margin: '4px 20px 24px', padding: '24px', background: 'linear-gradient(135deg, rgba(234,179,8,0.15), rgba(234,179,8,0.05))', border: `1px solid ${t.gold}33`, borderRadius: '24px', boxShadow: t.shadow, display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: t.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 6px 16px rgba(234,179,8,0.25)' }}>
              <IonIcon icon={sparklesOutline} style={{ fontSize: '28px', color: isDark ? '#000' : '#fff' }} />
            </div>
            <div>
              <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary, marginBottom: '2px' }}>Your Activity Feed</div>
              <div style={{ fontSize: '12px', color: t.textSecondary, lineHeight: '1.4' }}>Track your remittances, charges, and important system notices here.</div>
            </div>
          </div>

          <div style={{ padding: '0 20px 12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={notificationsOutline} style={{ fontSize: '16px', color: t.gold }} />
            <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Recent Notifications</span>
          </div>

          {loading && notifications.length === 0 ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : notifications.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '60px 20px', margin: '0 20px', background: t.subtleBg, borderRadius: '24px', border: `1px dashed ${isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'}` }}>
              <IonIcon icon={notificationsOutline} style={{ fontSize: '64px', color: t.textMuted, opacity: 0.2, marginBottom: '16px' }} />
              <h3 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>No Notifications Yet</h3>
              <p style={{ color: t.textMuted, fontSize: '13px' }}>Activity related to your profile will appear here.</p>
            </div>
          ) : (
            <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {notifications.map(notif => (
                <div key={notif.id} style={{ padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', gap: '14px' }}>
                    <div style={{ 
                      width: '42px', 
                      height: '42px', 
                      borderRadius: '14px', 
                      background: `${getSeverityColor(notif.severity)}15`, 
                      display: 'flex', 
                      alignItems: 'center', 
                      justifyContent: 'center', 
                      flexShrink: 0,
                      border: `1px solid ${getSeverityColor(notif.severity)}33`
                    }}>
                      <IonIcon icon={getIcon(notif.icon)} style={{ fontSize: '20px', color: getSeverityColor(notif.severity) }} />
                    </div>
                    <div style={{ flex: 1 }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '4px' }}>
                        <div style={{ fontSize: '14px', fontWeight: '800', color: t.textPrimary }}>{notif.title}</div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '4px', whiteSpace: 'nowrap' }}>
                           <IonIcon icon={timeOutline} style={{ fontSize: '10px', color: t.textMuted }} />
                           <span style={{ fontSize: '10px', color: t.textMuted, fontWeight: '600' }}>{notif.time_display}</span>
                        </div>
                      </div>
                      <div style={{ fontSize: '13px', color: t.textSecondary, lineHeight: '1.5' }}>{notif.message}</div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}

          {notifications.length > 0 && (
            <div style={{ textAlign: 'center', padding: '32px 20px 0', fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
              You're all caught up!
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Notifications;
