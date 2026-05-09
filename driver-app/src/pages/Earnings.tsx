import { useState, useEffect } from 'react';
import type { FC } from 'react';
import { IonContent, IonPage, IonIcon, IonRefresher, IonRefresherContent, IonSpinner } from '@ionic/react';
import { cashOutline, arrowBackOutline, trendingUpOutline, calendarOutline, checkmarkCircleOutline, alertCircleOutline } from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';

interface EarningRecord {
  id: number;
  date: string;
  boundary_amount: number;
  actual_boundary: number;
  status: string;
  shortage: number;
  excess: number;
  notes: string;
}

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
};

const Earnings: FC = () => {
  const history = useHistory();
  const [earnings, setEarnings] = useState<EarningRecord[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEarnings = async () => {
    try {
      const response = await axios.get(endpoints.driverEarnings);
      if (response.data.success) {
        setEarnings(response.data.data);
        localStorage.setItem('cached_earnings_data', JSON.stringify(response.data.data));
      }
    } catch (e) {
      console.error('Failed to fetch earnings', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { 
    const cached = localStorage.getItem('cached_earnings_data');
    if (cached) {
      try {
        setEarnings(JSON.parse(cached));
        setLoading(false);
      } catch (e) {}
    }
    fetchEarnings(); 
  }, []);

  const totalEarnings = earnings.reduce((acc, cur) => acc + Number(cur.actual_boundary), 0);
  const totalTarget = earnings.reduce((acc, cur) => acc + Number(cur.boundary_amount), 0);
  const paidCount = earnings.filter(e => e.status === 'paid' || e.status === 'excess').length;

  const statusColor = (status: string) => {
    if (status === 'paid' || status === 'excess') return '#22c55e';
    if (status === 'shortage') return '#ef4444';
    return '#f59e0b';
  };

  return (
    <IonPage>
      <IonContent fullscreen scrollY>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchEarnings().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: g.bg, paddingBottom: '40px' }}>

          {/* Header */}
          <div style={{ padding: '16px 20px 12px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: 'rgba(255,255,255,0.06)', border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#94a3b8' }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: '#f8fafc' }}>Payment History</div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>Boundary collections</div>
            </div>
          </div>

          {/* Summary Hero */}
          <div style={{ margin: '4px 20px 20px', padding: '24px', background: g.card, ...g.glass, border: g.border, borderRadius: '20px', boxShadow: '0 8px 32px rgba(0,0,0,0.4)' }}>
            <div style={{ fontSize: '11px', fontWeight: '800', color: '#64748b', textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '8px' }}>Total Collected</div>
            <div style={{ fontSize: '38px', fontWeight: '900', color: '#f8fafc', lineHeight: 1, marginBottom: '4px' }}>₱{totalEarnings.toLocaleString()}</div>
            <div style={{ fontSize: '12px', color: '#64748b', marginBottom: '20px' }}>of ₱{totalTarget.toLocaleString()} target</div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
              {[
                { label: 'Records', value: earnings.length, icon: calendarOutline, color: '#3b82f6' },
                { label: 'Paid', value: paidCount, icon: checkmarkCircleOutline, color: '#22c55e' },
                { label: 'Short', value: earnings.filter(e => e.status === 'shortage').length, icon: alertCircleOutline, color: '#ef4444' }
              ].map((stat, i) => (
                <div key={i} style={{ padding: '12px', background: 'rgba(255,255,255,0.04)', borderRadius: '12px', textAlign: 'center' }}>
                  <IonIcon icon={stat.icon} style={{ fontSize: '18px', color: stat.color }} />
                  <div style={{ fontSize: '20px', fontWeight: '800', color: '#f8fafc', margin: '4px 0 2px' }}>{stat.value}</div>
                  <div style={{ fontSize: '9px', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1px' }}>{stat.label}</div>
                </div>
              ))}
            </div>
          </div>

          {/* Section Header */}
          <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={trendingUpOutline} style={{ fontSize: '16px', color: g.gold }} />
            <span style={{ fontSize: '13px', fontWeight: '800', color: g.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Recent Collections</span>
          </div>

          {/* List */}
          <div style={{ padding: '0 20px' }}>
            {loading ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
                <IonSpinner name="crescent" color="warning" />
              </div>
            ) : earnings.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                <IonIcon icon={cashOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                <div style={{ color: '#475569', fontSize: '13px', marginTop: '12px' }}>No transactions found.</div>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {earnings.map(record => (
                  <div key={record.id} style={{ padding: '16px', background: g.card, ...g.glass, border: g.border, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                      <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: `${statusColor(record.status)}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <IonIcon icon={cashOutline} style={{ fontSize: '20px', color: statusColor(record.status) }} />
                      </div>
                      <div>
                        <div style={{ fontSize: '13px', fontWeight: '700', color: '#f8fafc' }}>
                          {new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                        <div style={{ fontSize: '11px', marginTop: '2px', display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
                          <span style={{ color: statusColor(record.status), fontWeight: '700', textTransform: 'uppercase' }}>{record.status}</span>
                          {Number(record.shortage) > 0 && <span style={{ color: '#ef4444' }}>• -₱{Number(record.shortage).toLocaleString()}</span>}
                          {Number(record.excess) > 0 && <span style={{ color: '#22c55e' }}>• +₱{Number(record.excess).toLocaleString()}</span>}
                        </div>
                      </div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                      <div style={{ fontSize: '16px', fontWeight: '900', color: statusColor(record.status) }}>₱{Number(record.actual_boundary).toLocaleString()}</div>
                      <div style={{ fontSize: '10px', color: '#475569', marginTop: '2px' }}>/ ₱{Number(record.boundary_amount).toLocaleString()}</div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Earnings;
