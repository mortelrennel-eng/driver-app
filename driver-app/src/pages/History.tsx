import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner } from '@ionic/react';
import { arrowBackOutline, statsChartOutline, checkmarkCircleOutline, alertCircleOutline, timeOutline, carOutline } from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';

interface BoundaryRecord {
  id: number;
  date: string;
  plate_number: string;
  boundary_amount: number;
  actual_boundary: number;
  status: string;
  is_extra: number;
}

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
};

const statusConfig = (status: string) => {
  if (status === 'paid' || status === 'excess') return { color: '#22c55e', icon: checkmarkCircleOutline, bg: 'rgba(34,197,94,0.12)' };
  if (status === 'shortage') return { color: '#ef4444', icon: alertCircleOutline, bg: 'rgba(239,68,68,0.12)' };
  return { color: '#f59e0b', icon: timeOutline, bg: 'rgba(245,158,11,0.12)' };
};

const History: React.FC = () => {
  const history = useHistory();
  const [records, setRecords] = useState<BoundaryRecord[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchHistory = async () => {
    try {
      const response = await axios.get(endpoints.boundaryHistory);
      if (response.data.success) setRecords(response.data.data);
    } catch (e) {
      console.error('Failed to fetch history', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchHistory(); }, []);

  const totalCollected = records.reduce((a, r) => a + Number(r.actual_boundary), 0);
  const totalTarget = records.reduce((a, r) => a + Number(r.boundary_amount), 0);

  return (
    <IonPage>
      <IonContent fullscreen scrollY>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchHistory().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: g.bg, paddingBottom: '40px' }}>
          {/* Header */}
          <div style={{ padding: '16px 20px 12px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: 'rgba(255,255,255,0.06)', border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#94a3b8' }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: '#f8fafc' }}>Boundary History</div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>{records.length} records found</div>
            </div>
          </div>

          {/* Summary */}
          <div style={{ margin: '4px 20px 20px', padding: '20px', background: g.card, ...g.glass, border: g.border, borderRadius: '20px', boxShadow: '0 8px 32px rgba(0,0,0,0.4)', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
            <div>
              <div style={{ fontSize: '10px', fontWeight: '800', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1.5px', marginBottom: '6px' }}>Total Collected</div>
              <div style={{ fontSize: '26px', fontWeight: '900', color: '#22c55e' }}>₱{totalCollected.toLocaleString()}</div>
            </div>
            <div>
              <div style={{ fontSize: '10px', fontWeight: '800', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1.5px', marginBottom: '6px' }}>Total Target</div>
              <div style={{ fontSize: '26px', fontWeight: '900', color: '#f8fafc' }}>₱{totalTarget.toLocaleString()}</div>
            </div>
          </div>

          {/* Section Label */}
          <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={statsChartOutline} style={{ fontSize: '16px', color: g.gold }} />
            <span style={{ fontSize: '13px', fontWeight: '800', color: g.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>All Records</span>
          </div>

          {/* List */}
          <div style={{ padding: '0 20px' }}>
            {loading ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                <IonSpinner name="crescent" color="warning" />
              </div>
            ) : records.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                <IonIcon icon={statsChartOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                <div style={{ color: '#475569', fontSize: '13px', marginTop: '12px' }}>No boundary history found.</div>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {records.map(record => {
                  const sc = statusConfig(record.status);
                  return (
                    <div key={record.id} style={{ padding: '16px', background: g.card, ...g.glass, border: g.border, borderRadius: '16px' }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '10px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                          <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: sc.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <IonIcon icon={sc.icon} style={{ fontSize: '20px', color: sc.color }} />
                          </div>
                          <div>
                            <div style={{ fontSize: '13px', fontWeight: '700', color: '#f8fafc' }}>
                              {new Date(record.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginTop: '2px' }}>
                              <IonIcon icon={carOutline} style={{ fontSize: '11px', color: '#64748b' }} />
                              <span style={{ fontSize: '11px', color: '#64748b' }}>{record.plate_number || 'N/A'}</span>
                              {record.is_extra === 1 && (
                                <span style={{ padding: '1px 6px', background: 'rgba(139,92,246,0.15)', color: '#a78bfa', borderRadius: '6px', fontSize: '9px', fontWeight: '700' }}>EXTRA</span>
                              )}
                            </div>
                          </div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                          <div style={{ fontSize: '16px', fontWeight: '900', color: sc.color }}>₱{Number(record.actual_boundary).toLocaleString()}</div>
                          <div style={{ fontSize: '10px', color: '#475569', marginTop: '2px' }}>/ ₱{Number(record.boundary_amount).toLocaleString()}</div>
                        </div>
                      </div>
                      <div style={{ padding: '6px 10px', background: sc.bg, borderRadius: '8px', display: 'inline-block' }}>
                        <span style={{ fontSize: '10px', fontWeight: '800', color: sc.color, textTransform: 'uppercase', letterSpacing: '1px' }}>{record.status}</span>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default History;
