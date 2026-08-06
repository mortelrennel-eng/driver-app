import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar } from '@ionic/react';
import { 
  arrowBackOutline, alertCircleOutline, checkmarkCircleOutline,
  warningOutline,
  chevronBackOutline, chevronForwardOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

interface ChargeRecord {
  id: number;
  incident_date: string;
  incident_type: string;
  description: string;
  total_charge_to_driver: number;
  remaining_balance: number;
  charge_status: string;
  severity: string;
}

const ITEMS_PER_PAGE = 10;

const Debts: React.FC = () => {
  const history = useHistory();
  const { t } = useTheme();
  const [charges, setCharges] = useState<ChargeRecord[]>([]);
  const [loading, setLoading] = useState(true);

  // Month Filter Logic
  const [selectedMonth, setSelectedMonth] = useState(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
  });

  const months = React.useMemo(() => {
    const result = [];
    for (let i = 0; i < 3; i++) {
      const d = new Date();
      d.setMonth(d.getMonth() - i);
      result.push({
        value: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`,
        label: d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
      });
    }
    return result;
  }, []);

  const [currentPage, setCurrentPage] = useState(1);

  const fetchData = async () => {
    try {
      const r = await cachedGet(endpoints.chargesIncentives);
      if (r.data.success) {
        setCharges(r.data.charges);
      }
    } catch (e) { console.error(e); }
    finally { setLoading(false); }
  };

  useEffect(() => { fetchData(); }, []);

  useEffect(() => { setCurrentPage(1); }, [selectedMonth]);

  const filteredCharges = charges.filter(c => c.incident_date?.startsWith(selectedMonth));

  const totalPages = Math.ceil(filteredCharges.length / ITEMS_PER_PAGE);
  const paginatedList = filteredCharges.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const totalCharges = filteredCharges.reduce((a, c) => a + Number(c.remaining_balance), 0);
  const totalOverallCharges = charges.reduce((a, c) => a + Number(c.remaining_balance), 0);

  const severityConfig = (s: string) =>
    s === 'high' ? { color: '#ef4444', bg: 'rgba(239,68,68,0.12)', icon: alertCircleOutline } :
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
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Pending Debts</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Your outstanding balances</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchData().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: t.bg, paddingBottom: '120px' }}>

          {/* Month Selector Chips */}
          <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', padding: '10px 20px 4px', alignItems: 'center', msOverflowStyle: 'none', scrollbarWidth: 'none', WebkitOverflowScrolling: 'touch' }}>
            {months.map(m => (
              <div 
                key={m.value}
                onClick={() => { setSelectedMonth(m.value); setCurrentPage(1); }}
                style={{
                  padding: '8px 16px', borderRadius: '20px', whiteSpace: 'nowrap', fontSize: '12px', fontWeight: '800',
                  background: selectedMonth === m.value ? '#3b82f6' : t.subtleBg,
                  color: selectedMonth === m.value ? '#ffffff' : t.textPrimary,
                  boxShadow: selectedMonth === m.value ? '0 4px 12px rgba(59,130,246,0.3)' : 'none',
                  border: selectedMonth === m.value ? 'none' : t.border, cursor: 'pointer', transition: 'all 0.2s',
                }}
              >{m.label}</div>
            ))}
            <input 
              type="month" value={selectedMonth}
              max={`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`}
              onChange={(e) => { if (e.target.value) { setSelectedMonth(e.target.value); setCurrentPage(1); } }}
              style={{
                padding: '7px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: '800',
                background: !months.find(m => m.value === selectedMonth) ? '#3b82f6' : t.subtleBg,
                color: !months.find(m => m.value === selectedMonth) ? '#ffffff' : t.textPrimary,
                border: !months.find(m => m.value === selectedMonth) ? 'none' : t.border,
                outline: 'none', cursor: 'pointer', fontFamily: 'inherit', flexShrink: 0
              }}
            />
          </div>

          {/* Summary Banner */}
          <div style={{ display: 'flex', gap: '12px', margin: '16px 20px 16px' }}>
            <div style={{ flex: 1, padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
              <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>This Month</div>
              <div style={{ fontSize: '24px', fontWeight: '900', color: '#ef4444' }}>₱{totalCharges.toLocaleString()}</div>
              <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>{filteredCharges.length} debt(s)</div>
            </div>
            
            <div style={{ flex: 1, padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
              <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Total Overall</div>
              <div style={{ fontSize: '24px', fontWeight: '900', color: '#f43f5e' }}>₱{totalOverallCharges.toLocaleString()}</div>
              <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>{charges.length} debt(s)</div>
            </div>
          </div>

          {/* Content */}
          <div style={{ padding: '0 20px' }}>
            {loading ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                <IonSpinner name="crescent" color="warning" />
              </div>
            ) : filteredCharges.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                <div style={{ width: '72px', height: '72px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                  <IonIcon icon={checkmarkCircleOutline} style={{ fontSize: '36px', color: '#22c55e' }} />
                </div>
                <div style={{ fontSize: '16px', fontWeight: '700', color: '#22c55e' }}>No pending debts!</div>
                <div style={{ fontSize: '12px', color: '#475569', marginTop: '6px' }}>You're in good standing.</div>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {paginatedList.map(charge => {
                  const sc = severityConfig(charge.severity);
                  return (
                    <div key={charge.id} style={{ 
                      padding: '16px', background: t.card, ...t.glass, 
                      border: t.border, borderRadius: '20px', boxShadow: t.shadow 
                    }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', gap: '14px', alignItems: 'center' }}>
                          <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: sc.bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                            <IonIcon icon={sc.icon} style={{ fontSize: '20px', color: sc.color }} />
                          </div>
                          <div>
                            <div style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>
                              {new Date(charge.incident_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            </div>
                            <div style={{ fontSize: '12px', color: sc.color, fontWeight: '600', marginTop: '2px' }}>{charge.incident_type}</div>
                          </div>
                        </div>
                        <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', flexShrink: 0 }}>
                          <div style={{ fontSize: '18px', fontWeight: '900', color: charge.charge_status === 'paid' ? '#22c55e' : '#ef4444' }}>
                            ₱{Number(charge.total_charge_to_driver).toLocaleString()}
                          </div>
                          <div style={{ fontSize: '11px', fontWeight: '800', padding: '3px 8px', borderRadius: '6px', background: charge.charge_status === 'paid' ? 'rgba(34,197,94,0.12)' : 'rgba(239,68,68,0.12)', color: charge.charge_status === 'paid' ? '#22c55e' : '#ef4444', marginTop: '6px', letterSpacing: '0.5px' }}>
                            {charge.charge_status === 'paid' ? 'PAID' : `BAL: ₱${Number(charge.remaining_balance).toLocaleString()}`}
                          </div>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            {/* Pagination Controls */}
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

export default Debts;
