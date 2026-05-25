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
  bookmarkOutline,
  closeOutline,
  chevronForwardOutline
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
  const [selected, setSelected] = useState<Announcement | null>(null);

  const fetchAnnouncements = async () => {
    try {
      const response = await axios.get(endpoints.announcements);
      if (response.data.success) {
        const fetched: Announcement[] = response.data.announcements;
        setAnnouncements(fetched);
        localStorage.setItem('cached_driver_announcements', JSON.stringify(fetched));
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

  const openDetail = (ann: Announcement) => {
    setSelected(ann);
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button
              onClick={() => history.goBack()}
              style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}
            >
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Broadcasts</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Important updates &amp; announcements</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, paddingBottom: '40px' }}>



          {/* Section label */}
          <div style={{ padding: '0 20px 12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={megaphoneOutline} style={{ fontSize: '16px', color: t.gold }} />
            <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>
              Active Broadcasts
            </span>
          </div>

          {/* List */}
          {loading && announcements.length === 0 ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : announcements.length === 0 ? (
            <div style={{
              textAlign: 'center', padding: '60px 20px', margin: '0 20px',
              background: t.subtleBg, borderRadius: '24px',
              border: `1px dashed ${isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'}`
            }}>
              <IonIcon icon={megaphoneOutline} style={{ fontSize: '64px', color: t.textMuted, opacity: 0.2, marginBottom: '16px' }} />
              <h3 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>No Broadcasts Yet</h3>
              <p style={{ color: t.textMuted, fontSize: '13px' }}>All official updates from management will appear here.</p>
            </div>
          ) : (
            <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {announcements.map(ann => {
                const isExpired = ann.valid_until && new Date(ann.valid_until) < new Date();
                if (isExpired) return null;

                return (
                  <div
                    key={ann.id}
                    onClick={() => openDetail(ann)}
                    style={{
                      padding: '14px 16px',
                      background: t.card,
                      ...t.glass,
                      border: ann.is_pinned ? `2px solid ${t.gold}` : t.border,
                      borderRadius: '18px',
                      boxShadow: ann.is_pinned ? `0 8px 24px rgba(234,179,8,0.15)` : t.cardShadow,
                      position: 'relative',
                      overflow: 'hidden',
                      cursor: 'pointer',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '14px',
                      WebkitTapHighlightColor: 'transparent',
                      transition: 'transform 0.1s ease, box-shadow 0.1s ease',
                    }}
                  >
                    {/* Pinned badge */}
                    {ann.is_pinned && (
                      <div style={{
                        position: 'absolute', top: 0, right: '14px',
                        background: t.gold, color: isDark ? '#000' : '#fff',
                        fontSize: '9px', fontWeight: '900', textTransform: 'uppercase',
                        padding: '4px 8px 6px',
                        borderBottomLeftRadius: '8px', borderBottomRightRadius: '8px',
                        display: 'flex', alignItems: 'center', gap: '3px'
                      }}>
                        <IonIcon icon={bookmarkOutline} style={{ fontSize: '10px' }} />
                        Pinned
                      </div>
                    )}

                    {/* Megaphone icon */}
                    <div style={{
                      width: '48px', height: '48px', borderRadius: '15px', flexShrink: 0,
                      background: `linear-gradient(135deg, rgba(234,179,8,0.2), rgba(234,179,8,0.07))`,
                      border: `1.5px solid ${t.gold}55`,
                      display: 'flex', alignItems: 'center', justifyContent: 'center'
                    }}>
                      <IonIcon icon={megaphoneOutline} style={{ fontSize: '24px', color: t.gold }} />
                    </div>

                    {/* Title + Posted date */}
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <div style={{
                        fontSize: '14px', fontWeight: '800', color: t.textPrimary,
                        marginBottom: '5px',
                        whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis'
                      }}>
                        {ann.title || 'Announcement'}
                      </div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                        <IonIcon icon={timeOutline} style={{ fontSize: '11px', color: t.gold }} />
                        <span style={{ fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
                          {formatDate(ann.created_at)}
                        </span>
                      </div>
                    </div>

                    {/* Chevron arrow */}
                    <div style={{
                      width: '28px', height: '28px', borderRadius: '8px', flexShrink: 0,
                      background: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)',
                      display: 'flex', alignItems: 'center', justifyContent: 'center'
                    }}>
                      <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: t.textMuted }} />
                    </div>
                  </div>
                );
              })}
            </div>
          )}

          {announcements.length > 0 && (
            <div style={{ textAlign: 'center', padding: '32px 20px 0', fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
              You're all caught up! 🎉
            </div>
          )}
        </div>
      </IonContent>

      {/* ── Detail Bottom Sheet ── */}
      {selected && (
        <div
          onClick={() => setSelected(null)}
          style={{
            position: 'fixed', inset: 0, zIndex: 9999,
            background: 'rgba(0,0,0,0.6)',
            backdropFilter: 'blur(8px)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            padding: '20px',
            animation: 'annFadeIn 0.15s ease'
          }}
        >
          <div
            onClick={e => e.stopPropagation()}
            style={{
              width: '100%', maxWidth: '520px',
              background: t.card,
              borderRadius: '28px',
              overflow: 'hidden',
              boxShadow: '0 12px 48px rgba(0,0,0,0.35)',
              animation: 'annScaleIn 0.2s cubic-bezier(.32,1.2,.4,1)',
              maxHeight: '80vh',
              overflowY: 'auto'
            }}
          >
            {/* Sheet header */}
            <div style={{
              background: 'linear-gradient(135deg, #EAB308, #D97706)',
              padding: '20px 20px 18px',
              display: 'flex', alignItems: 'center', justifyContent: 'space-between',
              position: 'sticky', top: 0, zIndex: 1
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <IonIcon icon={megaphoneOutline} style={{ fontSize: '22px', color: '#fff' }} />
                <span style={{ fontSize: '15px', fontWeight: '900', color: '#fff' }}>Announcement</span>
              </div>
              <button
                onClick={() => setSelected(null)}
                style={{
                  background: 'rgba(255,255,255,0.22)', border: 'none',
                  borderRadius: '10px', padding: '8px', cursor: 'pointer',
                  display: 'flex', alignItems: 'center'
                }}
              >
                <IonIcon icon={closeOutline} style={{ fontSize: '20px', color: '#fff' }} />
              </button>
            </div>

            {/* Sheet body */}
            <div style={{ padding: '24px 20px 44px' }}>
              {/* Title */}
              <div style={{
                fontSize: '19px', fontWeight: '900', color: t.textPrimary,
                marginBottom: '12px', lineHeight: '1.3'
              }}>
                {selected.title || 'Announcement'}
              </div>

              {/* Date meta */}
              <div style={{ display: 'flex', gap: '16px', marginBottom: '20px', flexWrap: 'wrap' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                  <IonIcon icon={timeOutline} style={{ fontSize: '13px', color: t.gold }} />
                  <span style={{ fontSize: '12px', fontWeight: '700', color: t.textMuted }}>
                    Posted {formatDate(selected.created_at)}
                  </span>
                </div>
                {selected.valid_until && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                    <IonIcon icon={calendarOutline} style={{ fontSize: '13px', color: t.gold }} />
                    <span style={{ fontSize: '12px', fontWeight: '700', color: t.textMuted }}>
                      Until {formatDate(selected.valid_until)}
                    </span>
                  </div>
                )}
              </div>

              {/* Divider */}
              <div style={{
                height: '1px',
                background: isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)',
                marginBottom: '20px'
              }} />

              {/* Full message */}
              <div style={{
                fontSize: '15px', color: t.textSecondary,
                lineHeight: '1.8', fontWeight: '500', whiteSpace: 'pre-wrap'
              }}>
                {selected.message || 'No additional details.'}
              </div>
            </div>
          </div>
        </div>
      )}

      <style>{`
        @keyframes annFadeIn  { from { opacity: 0 } to { opacity: 1 } }
        @keyframes annScaleIn { from { transform: scale(0.95); opacity: 0 } to { transform: scale(1); opacity: 1 } }
      `}</style>
    </IonPage>
  );
};

export default Announcements;
