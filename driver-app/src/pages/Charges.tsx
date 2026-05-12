import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar } from '@ionic/react';
import { 
  arrowBackOutline, alertCircleOutline, giftOutline, checkmarkCircleOutline,
  warningOutline, ribbonOutline, flameOutline, starOutline,
  chevronBackOutline, chevronForwardOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useTheme } from '../context/ThemeContext';

interface ChargeRecord {
  id: number;
  incident_date: string;
  incident_type: string;
  description: string;
  remaining_balance: number;
  severity: string;
}
interface IncentiveRecord {
  id: number;
  date: string;
  boundary_amount: number;
  actual_boundary: number;
}



const Charges: React.FC = () => {
  const history = useHistory();
  const { t } = useTheme();
  const [charges, setCharges] = useState<ChargeRecord[]>([]);
  const [incentives, setIncentives] = useState<IncentiveRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState<'charges' | 'incentives'>('charges');
  const [currentPage, setCurrentPage] = useState(1);
  const ITEMS_PER_PAGE = 10;

  const fetchData = async () => {
    try {
      const r = await axios.get(endpoints.chargesIncentives);
      if (r.data.success) {
        setCharges(r.data.charges);
        setIncentives(r.data.incentives);
      }
    } catch (e) { console.error(e); }
    finally { setLoading(false); }
  };

  useEffect(() => { fetchData(); }, []);

  // Reset pagination when tab changes
  useEffect(() => {
    setCurrentPage(1);
  }, [tab]);

  const currentList = tab === 'charges' ? charges : incentives;
  const totalPages = Math.ceil(currentList.length / ITEMS_PER_PAGE);
  const paginatedList = currentList.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const totalCharges = charges.reduce((a, c) => a + Number(c.remaining_balance), 0);

  const severityConfig = (s: string) =>
    s === 'high' ? { color: '#ef4444', bg: 'rgba(239,68,68,0.12)', icon: flameOutline } :
    s === 'medium' ? { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', icon: warningOutline } :
    { color: '#64748b', bg: 'rgba(100,116,139,0.12)', icon: alertCircleOutline };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Charges & Incentives</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Your financial record</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchData().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: t.bg, paddingBottom: '120px' }}>

          {/* Tab Switcher */}
          <div style={{ margin: '4px 20px 20px', display: 'grid', gridTemplateColumns: '1fr 1fr', background: t.subtleBg, borderRadius: '14px', padding: '4px', border: t.border }}>
            {(['charges', 'incentives'] as const).map(tabKey => (
              <button key={tabKey} onClick={() => setTab(tabKey)} style={{
                padding: '10px', borderRadius: '10px', border: 'none', fontWeight: '700', fontSize: '13px', cursor: 'pointer',
                background: tab === tabKey ? (tabKey === 'charges' ? 'linear-gradient(135deg, #ef4444, #b91c1c)' : 'linear-gradient(135deg, #eab308, #f59e0b)') : 'transparent',
                color: tab === tabKey ? '#fff' : '#64748b', transition: 'all 0.2s'
              }}>
                <IonIcon icon={tabKey === 'charges' ? alertCircleOutline : ribbonOutline} style={{ marginRight: '6px', verticalAlign: 'middle' }} />
                {tabKey === 'charges' ? 'Pending Charges' : 'Incentives'}
              </button>
            ))}
          </div>

          {/* Summary Banner */}
          {tab === 'charges' && charges.length > 0 && (
            <div style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px' }}>
              <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '4px' }}>Total Charges</div>
              <div style={{ fontSize: '32px', fontWeight: '900', color: '#ef4444' }}>₱{totalCharges.toLocaleString()}</div>
              <div style={{ fontSize: '12px', color: t.textMuted, marginTop: '2px' }}>{charges.length} charge(s)</div>
            </div>
          )}

          {tab === 'incentives' && incentives.length > 0 && (
            <div style={{ margin: '0 20px 16px', padding: '16px', background: 'rgba(234,179,8,0.1)', border: '1px solid rgba(234,179,8,0.2)', borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <div style={{ fontSize: '10px', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px', fontWeight: '700' }}>Eligible Incentives</div>
                <div style={{ fontSize: '24px', fontWeight: '900', color: t.gold }}>{incentives.length} Days</div>
              </div>
              <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: 'rgba(234,179,8,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                <IonIcon icon={starOutline} style={{ fontSize: '24px', color: t.gold }} />
              </div>
            </div>
          )}

          {/* Content */}
          <div style={{ padding: '0 20px' }}>
            {loading ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                <IonSpinner name="crescent" color="warning" />
              </div>
            ) : tab === 'charges' ? (
              charges.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                  <div style={{ width: '72px', height: '72px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                    <IonIcon icon={checkmarkCircleOutline} style={{ fontSize: '36px', color: '#22c55e' }} />
                  </div>
                  <div style={{ fontSize: '16px', fontWeight: '700', color: '#22c55e' }}>No pending charges!</div>
                  <div style={{ fontSize: '12px', color: '#475569', marginTop: '6px' }}>You're in good standing.</div>
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                  {(paginatedList as ChargeRecord[]).map(charge => {
                    const sc = severityConfig(charge.severity);
                    return (
                      <div key={charge.id} style={{ 
                        padding: '16px', background: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))', 
                        border: '1px solid rgba(255,255,255,0.06)', borderRadius: '20px', boxShadow: '0 8px 24px rgba(0,0,0,0.2)' 
                      }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <div style={{ display: 'flex', gap: '14px', alignItems: 'center' }}>
                            <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: sc.bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                              <IonIcon icon={sc.icon} style={{ fontSize: '20px', color: sc.color }} />
                            </div>
                            <div>
                              <div style={{ fontSize: '13px', fontWeight: '700', color: '#f8fafc' }}>
                                {new Date(charge.incident_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </div>
                              <div style={{ fontSize: '12px', color: sc.color, fontWeight: '600', marginTop: '2px' }}>{charge.incident_type}</div>
                            </div>
                          </div>
                          <div style={{ fontSize: '18px', fontWeight: '900', color: '#ef4444', flexShrink: 0 }}>
                            -₱{Number(charge.remaining_balance).toLocaleString()}
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )
            ) : (
              incentives.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                  <IonIcon icon={giftOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                  <div style={{ fontSize: '14px', fontWeight: '600', color: '#475569', marginTop: '12px' }}>No incentives recorded yet.</div>
                  <div style={{ fontSize: '12px', color: '#334155', marginTop: '6px' }}>Meet your boundary daily to earn incentives.</div>
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                  {(paginatedList as IncentiveRecord[]).map(c => (
                    <div key={c.id} style={{ padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                        <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: 'rgba(234,179,8,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                          <IonIcon icon={starOutline} style={{ fontSize: '20px', color: t.gold }} />
                        </div>
                        <div>
                          <div style={{ fontSize: '14px', fontWeight: '700', color: t.textPrimary }}>Boundary Incentive</div>
                          <div style={{ fontSize: '11px', color: t.textMuted }}>{new Date(c.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</div>
                        </div>
                      </div>
                      <span style={{ padding: '4px 10px', background: 'rgba(34,197,94,0.12)', color: '#22c55e', borderRadius: '8px', fontSize: '11px', fontWeight: '800' }}>ELIGIBLE</span>
                    </div>
                  ))}
                </div>
              )
            )}

            {/* Common Pagination Controls */}
            {totalPages > 1 && (
              <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '16px', marginTop: '20px', padding: '10px' }}>
                <button 
                  onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
                  disabled={currentPage === 1}
                  style={{ 
                    background: t.subtleBg, border: t.border, borderRadius: '10px', padding: '8px', 
                    opacity: currentPage === 1 ? 0.4 : 1, cursor: 'pointer', display: 'flex', alignItems: 'center' 
                  }}
                >
                  <IonIcon icon={chevronBackOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
                </button>
                
                <span style={{ fontSize: '12px', fontWeight: '800', color: t.textSecondary }}>
                  Page {currentPage} of {totalPages}
                </span>

                <button 
                  onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
                  disabled={currentPage === totalPages}
                  style={{ 
                    background: t.subtleBg, border: t.border, borderRadius: '10px', padding: '8px', 
                    opacity: currentPage === totalPages ? 0.4 : 1, cursor: 'pointer', display: 'flex', alignItems: 'center' 
                  }}
                >
                  <IonIcon icon={chevronForwardOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
                </button>
              </div>
            )}
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Charges;
