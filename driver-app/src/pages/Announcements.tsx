import type { FC } from 'react';
import { IonContent, IonPage, IonIcon } from '@ionic/react';
import { arrowBackOutline, megaphoneOutline, notificationsOutline, timeOutline } from 'ionicons/icons';
import { useHistory } from 'react-router-dom';

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
};

const announcements = [
  {
    id: 1,
    title: 'Welcome to the App!',
    body: 'Welcome to the new EuroTaxi Driver System. Please complete your profile in Settings to receive full system benefits.',
    date: 'May 7, 2026',
    type: 'info',
    color: '#3b82f6'
  }
];

const Announcements: FC = () => {
  const history = useHistory();

  return (
    <IonPage>
      <IonContent fullscreen scrollY>
        <div style={{ minHeight: '100vh', background: g.bg, paddingBottom: '40px' }}>

          {/* Header */}
          <div style={{ padding: '16px 20px 12px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: 'rgba(255,255,255,0.06)', border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#94a3b8' }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: '#f8fafc' }}>Announcements</div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>From EuroTaxi Management</div>
            </div>
          </div>

          {/* Hero Banner */}
          <div style={{ margin: '4px 20px 24px', padding: '24px', background: 'linear-gradient(135deg, rgba(59,130,246,0.2), rgba(139,92,246,0.15))', border: '1px solid rgba(99,102,241,0.2)', borderRadius: '20px', boxShadow: '0 8px 32px rgba(0,0,0,0.4)', display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{ width: '56px', height: '56px', borderRadius: '16px', background: g.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, boxShadow: '0 6px 16px rgba(234,179,8,0.25)' }}>
              <IonIcon icon={megaphoneOutline} style={{ fontSize: '28px', color: '#0a0e1a' }} />
            </div>
            <div>
              <div style={{ fontSize: '16px', fontWeight: '800', color: '#f8fafc', marginBottom: '4px' }}>Official Notices</div>
              <div style={{ fontSize: '12px', color: '#94a3b8', lineHeight: '1.5' }}>Stay updated with the latest news, policy changes, and important notices from EuroTaxi management.</div>
            </div>
          </div>

          {/* Announcements List */}
          <div style={{ padding: '0 20px 0', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={notificationsOutline} style={{ fontSize: '16px', color: g.gold }} />
            <span style={{ fontSize: '13px', fontWeight: '800', color: g.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Latest Updates</span>
          </div>

          <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
            {announcements.map(ann => (
              <div key={ann.id} style={{ padding: '18px', background: g.card, ...g.glass, border: g.border, borderRadius: '16px', boxShadow: '0 4px 16px rgba(0,0,0,0.2)' }}>
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: '12px' }}>
                  <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: `${ann.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0, marginTop: '2px' }}>
                    <IonIcon icon={megaphoneOutline} style={{ fontSize: '20px', color: ann.color }} />
                  </div>
                  <div style={{ flex: 1 }}>
                    <div style={{ fontSize: '14px', fontWeight: '800', color: '#f8fafc', marginBottom: '6px' }}>{ann.title}</div>
                    <div style={{ fontSize: '12px', color: '#94a3b8', lineHeight: '1.6', marginBottom: '12px' }}>{ann.body}</div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                      <IonIcon icon={timeOutline} style={{ fontSize: '12px', color: '#475569' }} />
                      <span style={{ fontSize: '11px', color: '#475569', fontWeight: '500' }}>{ann.date}</span>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Empty state footer */}
          <div style={{ textAlign: 'center', padding: '32px 20px 0', fontSize: '12px', color: '#334155' }}>
            You're all caught up! No more announcements.
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Announcements;
