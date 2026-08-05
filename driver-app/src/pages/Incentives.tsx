import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar } from '@ionic/react';
import { 
  arrowBackOutline, giftOutline, starOutline,
  chevronBackOutline, chevronForwardOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

interface IncentiveRecord {
  id: number;
  date: string;
  boundary_amount: number;
  actual_boundary: number;
}

const ITEMS_PER_PAGE = 10;

const Incentives: React.FC = () => {
  const history = useHistory();
  const { t } = useTheme();
  const [incentives, setIncentives] = useState<IncentiveRecord[]>([]);
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
        setIncentives(r.data.incentives);
      }
    } catch (e) { console.error(e); }
    finally { setLoading(false); }
  };

  useEffect(() => { fetchData(); }, []);

  useEffect(() => { setCurrentPage(1); }, [selectedMonth]);

  const filteredIncentives = incentives.filter(i => i.date?.startsWith(selectedMonth));

  const totalPages = Math.ceil(filteredIncentives.length / ITEMS_PER_PAGE);
  const paginatedList = filteredIncentives.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Incentives</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Your earned boundary incentives</div>
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
          {filteredIncentives.length > 0 && (
            <div style={{ margin: '16px 20px 16px', padding: '16px', background: 'rgba(234,179,8,0.1)', border: '1px solid rgba(234,179,8,0.2)', borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <div>
                <div style={{ fontSize: '10px', color: t.gold, textTransform: 'uppercase', letterSpacing: '1px', fontWeight: '700' }}>Eligible Incentives</div>
                <div style={{ fontSize: '24px', fontWeight: '900', color: t.gold }}>{filteredIncentives.length} Days</div>
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
            ) : filteredIncentives.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                <IonIcon icon={giftOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                <div style={{ fontSize: '14px', fontWeight: '600', color: '#475569', marginTop: '12px' }}>No incentives recorded yet.</div>
                <div style={{ fontSize: '12px', color: '#334155', marginTop: '6px' }}>Meet your boundary daily to earn incentives.</div>
              </div>
            ) : (
              <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                {paginatedList.map(c => (
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

export default Incentives;
