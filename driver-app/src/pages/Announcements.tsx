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
  chevronForwardOutline,
  archiveOutline,
  chevronBackOutline
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
  is_active: boolean;
  valid_until: string | null;
  created_at: string;
}

const Announcements: FC = () => {
  const history = useHistory();
  const { t, isDark } = useTheme();
  const [announcements, setAnnouncements] = useState<Announcement[]>([]);
  const [loading, setLoading] = useState(true);
  const [selected, setSelected] = useState<Announcement | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const ITEMS_PER_PAGE = 5;

  const handleClear = () => {
    setAnnouncements([]);
    localStorage.removeItem('cached_driver_announcements');
    setCurrentPage(1);
  };

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

  const isExpired = (ann: Announcement) =>
    !ann.is_active || (ann.valid_until !== null && new Date(ann.valid_until) < new Date());

  const totalPages = Math.ceil(announcements.length / ITEMS_PER_PAGE);
  const paginatedAnnouncements = announcements.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const activeAnnouncements = paginatedAnnouncements.filter(a => !isExpired(a));
  const pastAnnouncements   = paginatedAnnouncements.filter(a =>  isExpired(a));

  const AnnouncementCard = ({ ann, muted = false }: { ann: Announcement; muted?: boolean }) => (
    <div
      key={ann.id}
      onClick={() => setSelected(ann)}
      style={{
        padding: '14px 16px',
        background: muted
          ? (isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.03)')
          : t.card,
        ...(muted ? {} : t.glass),
        border: ann.is_pinned
          ? `2px solid ${t.gold}`
          : muted
            ? (isDark ? '1px solid rgba(255,255,255,0.07)' : '1px solid rgba(0,0,0,0.07)')
            : t.border,
        borderRadius: '18px',
        boxShadow: ann.is_pinned ? `0 8px 24px rgba(234,179,8,0.15)` : (muted ? 'none' : t.cardShadow),
        position: 'relative',
        overflow: 'hidden',
        cursor: 'pointer',
        display: 'flex',
        alignItems: 'center',
        gap: '14px',
        WebkitTapHighlightColor: 'transparent',
        transition: 'transform 0.1s ease, box-shadow 0.1s ease',
        opacity: muted ? 0.7 : 1,
      }}
    >
      {/* Pinned badge */}
      {ann.is_pinned && !muted && (
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

      {/* Icon */}
      <div style={{
        width: '48px', height: '48px', borderRadius: '15px', flexShrink: 0,
        background: muted
          ? (isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)')
          : `linear-gradient(135deg, rgba(234,179,8,0.2), rgba(234,179,8,0.07))`,
        border: muted
          ? (isDark ? '1.5px solid rgba(255,255,255,0.08)' : '1.5px solid rgba(0,0,0,0.08)')
          : `1.5px solid ${t.gold}55`,
        display: 'flex', alignItems: 'center', justifyContent: 'center'
      }}>
        <IonIcon
          icon={muted ? archiveOutline : megaphoneOutline}
          style={{ fontSize: '24px', color: muted ? t.textMuted : t.gold }}
        />
      </div>

      {/* Title + Date */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{
          fontSize: '14px', fontWeight: '800',
          color: muted ? t.textMuted : t.textPrimary,
          marginBottom: '5px',
          whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis'
        }}>
          {ann.title || 'Announcement'}
        </div>
        <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
          <IonIcon icon={timeOutline} style={{ fontSize: '11px', color: t.textMuted }} />
          <span style={{ fontSize: '11px', color: t.textMuted, fontWeight: '600' }}>
            {formatDate(ann.created_at)}
          </span>
          {muted && ann.valid_until && (
            <span style={{ fontSize: '10px', color: t.textMuted, fontWeight: '500', marginLeft: '4px' }}>
              · Expired {formatDate(ann.valid_until)}
            </span>
          )}
        </div>
      </div>

      {/* Chevron */}
      <div style={{
        width: '28px', height: '28px', borderRadius: '8px', flexShrink: 0,
        background: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)',
        display: 'flex', alignItems: 'center', justifyContent: 'center'
      }}>
        <IonIcon icon={chevronForwardOutline} style={{ fontSize: '14px', color: t.textMuted }} />
      </div>
    </div>
  );

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
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Announcements</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Important updates from management</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, paddingBottom: '40px' }}>

          {loading && announcements.length === 0 ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '80px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : announcements.length === 0 ? (
            /* ── Truly empty state ── */
            <div style={{
              textAlign: 'center', padding: '60px 20px', margin: '20px',
              background: t.subtleBg, borderRadius: '24px',
              border: `1px dashed ${isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'}`
            }}>
              <IonIcon icon={megaphoneOutline} style={{ fontSize: '64px', color: t.textMuted, opacity: 0.2, marginBottom: '16px' }} />
              <h3 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>No Announcements Yet</h3>
              <p style={{ color: t.textMuted, fontSize: '13px' }}>All official updates from management will appear here.</p>
            </div>
          ) : (
            <>
              {/* ── Active / Current Announcements ── */}
              <div style={{ padding: '0 20px 12px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <IonIcon icon={megaphoneOutline} style={{ fontSize: '16px', color: t.gold }} />
                  <span style={{ fontSize: '12px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>
                    Announcements
                  </span>
                  {activeAnnouncements.length > 0 && (
                    <span style={{
                      background: t.gold, color: isDark ? '#000' : '#fff',
                      fontSize: '10px', fontWeight: '900',
                      padding: '2px 7px', borderRadius: '99px'
                    }}>{activeAnnouncements.length}</span>
                  )}
                </div>

                <button 
                  onClick={handleClear}
                  style={{ 
                    background: 'transparent', border: 'none', color: t.textSecondary, 
                    fontSize: '11px', fontWeight: '800', cursor: 'pointer', padding: '4px 8px', borderRadius: '8px'
                  }}
                >
                  CLEAR ALL
                </button>
              </div>

              {activeAnnouncements.length === 0 ? (
                <div style={{
                  margin: '0 20px 16px', padding: '20px',
                  background: t.subtleBg, borderRadius: '16px',
                  border: `1px dashed ${isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)'}`,
                  textAlign: 'center'
                }}>
                  <p style={{ color: t.textMuted, fontSize: '13px', margin: 0 }}>No active announcements right now.</p>
                </div>
              ) : (
                <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
                  {activeAnnouncements.map(ann => (
                    <AnnouncementCard key={ann.id} ann={ann} muted={false} />
                  ))}
                </div>
              )}

              {/* ── Previous / Past Announcements ── */}
              {pastAnnouncements.length > 0 && (
                <>
                  <div style={{ padding: '24px 20px 12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <IonIcon icon={archiveOutline} style={{ fontSize: '16px', color: t.textMuted }} />
                    <span style={{ fontSize: '12px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px' }}>
                      Previous Announcements
                    </span>
                    <span style={{
                      background: isDark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.08)',
                      color: t.textMuted,
                      fontSize: '10px', fontWeight: '900',
                      padding: '2px 7px', borderRadius: '99px'
                    }}>{pastAnnouncements.length}</span>
                  </div>

                  <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {pastAnnouncements.map(ann => (
                      <AnnouncementCard key={ann.id} ann={ann} muted={true} />
                    ))}
                  </div>
                </>
              )}
            </>
          )}

          {/* ── Pagination Controls ────────────────────────── */}
          {totalPages > 1 && (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '12px', marginTop: '24px' }}>
              <button
                disabled={currentPage === 1}
                onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                style={{
                  width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: currentPage === 1 ? 'default' : 'pointer',
                  background: currentPage === 1 ? t.subtleBg : t.card,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  opacity: currentPage === 1 ? 0.35 : 1,
                  boxShadow: t.cardShadow,
                  transition: 'all 0.2s ease',
                }}
              >
                <IonIcon icon={chevronBackOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
              </button>

              <div style={{ display: 'flex', gap: '6px' }}>
                {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                  <button
                    key={page}
                    onClick={() => setCurrentPage(page)}
                    style={{
                      width: '36px', height: '36px', borderRadius: '10px', border: 'none',
                      cursor: 'pointer',
                      background: page === currentPage ? `linear-gradient(135deg, ${t.gold}, #f59e0b)` : t.subtleBg,
                      color: page === currentPage ? '#000' : t.textMuted,
                      fontWeight: page === currentPage ? '900' : '600',
                      fontSize: '13px',
                      transition: 'all 0.2s ease',
                      boxShadow: page === currentPage ? `0 4px 12px rgba(234,179,8,0.3)` : 'none',
                    }}
                  >
                    {page}
                  </button>
                ))}
              </div>

              <button
                disabled={currentPage === totalPages}
                onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                style={{
                  width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: currentPage === totalPages ? 'default' : 'pointer',
                  background: currentPage === totalPages ? t.subtleBg : t.card,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  opacity: currentPage === totalPages ? 0.35 : 1,
                  boxShadow: t.cardShadow,
                  transition: 'all 0.2s ease',
                }}
              >
                <IonIcon icon={chevronForwardOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
              </button>
            </div>
          )}

          {announcements.length > 0 && (
            <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
              Showing {(currentPage - 1) * ITEMS_PER_PAGE + 1}–{Math.min(currentPage * ITEMS_PER_PAGE, announcements.length)} of {announcements.length} records
            </div>
          )}

        </div>
      </IonContent>

      {/* ── Detail Modal ── */}
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
            {/* Modal header */}
            <div style={{
              background: isExpired(selected)
                ? (isDark ? 'linear-gradient(135deg,#3a3a3a,#222)' : 'linear-gradient(135deg,#9ca3af,#6b7280)')
                : 'linear-gradient(135deg, #EAB308, #D97706)',
              padding: '20px 20px 18px',
              display: 'flex', alignItems: 'center', justifyContent: 'space-between',
              position: 'sticky', top: 0, zIndex: 1
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <IonIcon
                  icon={isExpired(selected) ? archiveOutline : megaphoneOutline}
                  style={{ fontSize: '22px', color: '#fff' }}
                />
                <span style={{ fontSize: '15px', fontWeight: '900', color: '#fff' }}>
                  {isExpired(selected) ? 'Previous Announcement' : 'Announcement'}
                </span>
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

            {/* Modal body */}
            <div style={{ padding: '24px 20px 44px' }}>
              <div style={{
                fontSize: '19px', fontWeight: '900', color: t.textPrimary,
                marginBottom: '12px', lineHeight: '1.3'
              }}>
                {selected.title || 'Announcement'}
              </div>

              {/* Meta row */}
              <div style={{ display: 'flex', gap: '16px', marginBottom: '20px', flexWrap: 'wrap' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                  <IonIcon icon={timeOutline} style={{ fontSize: '13px', color: t.gold }} />
                  <span style={{ fontSize: '12px', fontWeight: '700', color: t.textMuted }}>
                    Posted {formatDate(selected.created_at)}
                  </span>
                </div>
                {selected.valid_until && (
                  <div style={{ display: 'flex', alignItems: 'center', gap: '5px' }}>
                    <IonIcon icon={calendarOutline} style={{ fontSize: '13px', color: isExpired(selected) ? '#ef4444' : t.gold }} />
                    <span style={{ fontSize: '12px', fontWeight: '700', color: isExpired(selected) ? '#ef4444' : t.textMuted }}>
                      {isExpired(selected) ? 'Expired' : 'Until'} {formatDate(selected.valid_until)}
                    </span>
                  </div>
                )}
              </div>

              <div style={{
                height: '1px',
                background: isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)',
                marginBottom: '20px'
              }} />

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
