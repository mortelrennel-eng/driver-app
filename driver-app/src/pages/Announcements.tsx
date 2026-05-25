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
  megaphoneOutline, 
  timeOutline, 
  calendarOutline,
  sparklesOutline,
  bookmarkOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';
import axios from 'axios';
import { endpoints } from '../config/api';

interface Announcement {
  id: string;
  title?: string;
  message: string;
  is_pinned: boolean;
  valid_until: string | null;
  created_at: string;
}

const Announcements: FC = () => {
  const history = useHistory();
  const { t, isDark } = useTheme();
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchAnnouncements = async () => {
    try {
      const response = await axios.get(endpoints.announcements);
      if (response.data.success) {
        setAnnouncements(response.data.announcements);
        localStorage.setItem('cached_driver_announcements', JSON.stringify(response.data.announcements));
      }
    } catch (e) {
      console.error('Failed to fetch announcements', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const cached = localStorage.getItem('cached_driver_announcements');
    if (cached) {
      try {
        setAnnouncements(JSON.parse(cached));
        setLoading(false);
      } catch (e) {}
    }
    fetchAnnouncements();
  }, []);

  const doRefresh = (event: CustomEvent) => {
    fetchAnnouncements().then(() => event.detail.complete());
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
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
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Broadcasts</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Important updates & announcements</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, paddingBottom: '40px' }}>

          {/* Banner */}
          <div style={{ margin: '4px 20px 24px', padding: '24px', background: 'linear-gradient(135deg, rgba(234,179,8,0.15), rgba(234,179,8,0.05))', border: `1px solid ${t.gold}33`, borderRadius: '24px', boxShadow: t.shadow, display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{ width: '56px', height: '56px', borderRadius: '18px', background: t.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 6px 16px rgba(234,179,8,0.25)' }}>
              <IonIcon icon={sparklesOutline} style={{ fontSize: '28px', color: isDark ? '#000' : '#fff' }} />
            </div>
            <div>
              <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary, marginBottom: '2px' }}>Official Announcements</div>
              <div style={{ fontSize: '12px', color: t.textSecondary, lineHeight: '1.4' }}>Stay updated with the latest regulations, announcements, and notices from management.</div>
            </div>
          </div>

          <div style={{ padding: '0 20px 12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={megaphoneOutline} style={{ fontSize: '16px', color: t.gold }} />
            <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Active Broadcasts</span>
          </div>

          {loading && announcements.length === 0 ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : announcements.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '60px 20px', margin: '0 20px', background: t.subtleBg, borderRadius: '24px', border: `1px dashed ${isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'}` }}>
              <IonIcon icon={megaphoneOutline} style={{ fontSize: '64px', color: t.textMuted, opacity: 0.2, marginBottom: '16px' }} />
              <h3 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>No Broadcasts Yet</h3>
              <p style={{ color: t.textMuted, fontSize: '13px' }}>All official updates from management will appear here.</p>
            </div>
          ) : (
            <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '14px' }}>
              {announcements.map(ann => {
                const isExpired = ann.valid_until && new Date(ann.valid_until) < new Date();
                if (isExpired) return null;

                return (
                  <div 
                    key={ann.id} 
                    style={{ 
                      padding: '18px', 
                      background: t.card, 
                      ...t.glass, 
                      border: ann.is_pinned ? `2px solid ${t.gold}` : t.border, 
                      borderRadius: '20px', 
                      boxShadow: ann.is_pinned ? `0 8px 24px rgba(234,179,8,0.12)` : t.cardShadow,
                      position: 'relative',
                      overflow: 'hidden'
                    }}
                  >
                    {ann.is_pinned && (
                      <div style={{ 
                        position: 'absolute', 
                        top: '0', 
                        right: '18px', 
                        background: t.gold, 
                        color: isDark ? '#000' : '#fff',
                        fontSize: '9px',
                        fontWeight: '900',
                        textTransform: 'uppercase',
                        padding: '4px 8px 6px',
                        borderBottomLeftRadius: '8px',
                        borderBottomRightRadius: '8px',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '3px'
                      }}>
                        <IonIcon icon={bookmarkOutline} style={{ fontSize: '10px' }} />
                        Pinned
                      </div>
                    )}

                    <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                      {/* Date & Meta */}
                      <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                          <IonIcon icon={timeOutline} style={{ fontSize: '12px', color: t.textMuted }} />
                          <span style={{ fontSize: '11px', color: t.textMuted, fontWeight: '700' }}>
                            {formatDate(ann.created_at)}
                          </span>
                        </div>
                        {ann.valid_until && (
                          <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                            <IonIcon icon={calendarOutline} style={{ fontSize: '12px', color: t.textMuted }} />
                            <span style={{ fontSize: '11px', color: t.textMuted, fontWeight: '700' }}>
                              Until {formatDate(ann.valid_until)}
                            </span>
                          </div>
                        )}
                      </div>

                      {/* Title & Message body */}
                      {ann.title && (
                        <div style={{ fontSize: '15px', fontWeight: '800', color: t.textPrimary, marginBottom: '4px' }}>
                          {ann.title}
                        </div>
                      )}
                      {ann.message && (
                        <div 
                          style={{ 
                            fontSize: '14px', 
                            color: ann.title ? t.textSecondary : t.textPrimary, 
                            lineHeight: '1.6', 
                            fontWeight: '600',
                            whiteSpace: 'pre-wrap'
                          }}
                        >
                          {ann.message}
                        </div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}

          {announcements.length > 0 && (
            <div style={{ textAlign: 'center', padding: '32px 20px 0', fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
              You're all caught up!
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Announcements;
