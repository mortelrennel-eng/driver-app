import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar } from '@ionic/react';
import { 
  arrowBackOutline, 
  checkmarkCircleOutline, 
  alertCircleOutline, 
  timeOutline, 
  calendarOutline,
  trendingUpOutline,
  cashOutline,
  chevronBackOutline,
  chevronForwardOutline,
  filterOutline,
  shieldCheckmarkOutline,
  walletOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

interface BoundaryRecord {
  id: number;
  date: string;
  plate_number: string;
  boundary_amount: number;
  actual_boundary: number;
  status: string;
  is_extra: number;
  shortage?: number;
  excess?: number;
  record_type?: string;
}

interface DebtPayment {
  id: number;
  date: string;
  incident_type: string;
  description: string;
  boundary_amount: number;
  actual_boundary: number;
  plate_number: string;
  record_type: string;
}

const statusConfig = (status: string) => {
  const s = status?.toLowerCase();
  if (s === 'paid' || s === 'excess') return { color: '#22c55e', icon: checkmarkCircleOutline, bg: 'rgba(34,197,94,0.12)' };
  if (s === 'shortage') return { color: '#ef4444', icon: alertCircleOutline, bg: 'rgba(239,68,68,0.12)' };
  return { color: '#f59e0b', icon: timeOutline, bg: 'rgba(245,158,11,0.12)' };
};



const History: React.FC = () => {
  const history = useHistory();
  const { t } = useTheme();

  const [records, setRecords] = useState<BoundaryRecord[]>([]);
  const [debtPayments, setDebtPayments] = useState<DebtPayment[]>([]);
  const [loading, setLoading] = useState(true);

  // Filter Logic
  const [selectedMonth, setSelectedMonth] = useState(() => {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
  });
  const [statusFilter, setStatusFilter] = useState<'all' | 'paid' | 'shortage'>('all');
  const [activeTab, setActiveTab] = useState<'boundaries' | 'debts'>('boundaries');

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
  const ITEMS_PER_PAGE = 10;

  const fetchHistory = async () => {
    try {
      const response = await cachedGet(endpoints.boundaryHistory);
      if (response.data.success) {
        const data = Array.isArray(response.data.data) ? response.data.data : [];
        setRecords(data);
        const debts = Array.isArray(response.data.debt_payments) ? response.data.debt_payments : [];
        setDebtPayments(debts);
        setCurrentPage(1); // Reset to page 1 on refresh
      }
    } catch (e) {
      console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { 
    fetchHistory(); 
  }, []);

  const safeRecords = Array.isArray(records) ? records : [];
  
  // First, filter by month
  const monthFilteredRecords = safeRecords.filter(r => {
    if (!r.date) return false;
    return r.date.startsWith(selectedMonth);
  });

  // Filter debt payments by month
  const monthFilteredDebts = debtPayments.filter(d => d.date?.startsWith(selectedMonth));

  // Calculate totals based ONLY on the selected month (before status filtering)
  const totalCollected = monthFilteredRecords.reduce((a, r) => a + Number(r.actual_boundary || 0), 0);
  const totalShortage = monthFilteredRecords.reduce((a, r) => a + Number(r.shortage || 0), 0);
  const paidCount = monthFilteredRecords.filter(r => ['paid', 'excess'].includes(r.status?.toLowerCase())).length;
  const shortCount = monthFilteredRecords.filter(r => r.status?.toLowerCase() === 'shortage').length;

  // Now, apply status filter
  const filteredRecords = monthFilteredRecords.filter(r => {
    if (statusFilter === 'all') return true;
    if (statusFilter === 'paid') return ['paid', 'excess'].includes(r.status?.toLowerCase());
    if (statusFilter === 'shortage') return r.status?.toLowerCase() === 'shortage';
    return true;
  });

  const totalPages = Math.ceil(
    (activeTab === 'boundaries' ? filteredRecords.length : monthFilteredDebts.length) / ITEMS_PER_PAGE
  );
  const paginatedRecords = filteredRecords.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);
  const paginatedDebts = monthFilteredDebts.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const handleFilterClick = (filter: 'all' | 'paid' | 'shortage') => {
    setStatusFilter(filter);
    setCurrentPage(1);
  };

  // Total debt payments for this month
  const totalDebtPaid = monthFilteredDebts.reduce((a, d) => a + Number(d.actual_boundary || 0), 0);
  const totalOverallDebtPaid = debtPayments.reduce((a, d) => a + Number(d.actual_boundary || 0), 0);

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Payment History</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Collection & boundary records</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchHistory().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100vh', background: t.bg, paddingBottom: '120px' }}>

          {/* Tab Switcher */}
          <div style={{ display: 'flex', gap: '4px', padding: '10px 20px 0', background: t.bg }}>
            {([
              { key: 'boundaries' as const, label: 'Boundary', icon: cashOutline },
              { key: 'debts' as const, label: 'Debt Payments', icon: walletOutline }
            ]).map(tab => (
              <button
                key={tab.key}
                onClick={() => { setActiveTab(tab.key); setCurrentPage(1); }}
                style={{
                  flex: 1, padding: '10px 12px', border: 'none', borderRadius: '12px',
                  background: activeTab === tab.key ? 'linear-gradient(135deg, #3b82f6, #2563eb)' : t.subtleBg,
                  color: activeTab === tab.key ? '#fff' : t.textMuted,
                  fontSize: '12px', fontWeight: '800', cursor: 'pointer',
                  display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '6px',
                  boxShadow: activeTab === tab.key ? '0 4px 12px rgba(59,130,246,0.3)' : 'none',
                  transition: 'all 0.2s'
                }}
              >
                <IonIcon icon={tab.icon} style={{ fontSize: '16px' }} />
                {tab.label}
              </button>
            ))}
          </div>

          {/* Month Selector Chips */}
          <div id="history-month" style={{ display: 'flex', gap: '8px', overflowX: 'auto', padding: '10px 20px 16px', alignItems: 'center', msOverflowStyle: 'none', scrollbarWidth: 'none', WebkitOverflowScrolling: 'touch' }}>
            {months.map(m => (
              <div 
                key={m.value}
                onClick={() => { setSelectedMonth(m.value); setCurrentPage(1); }}
                style={{
                  padding: '8px 16px',
                  borderRadius: '20px',
                  whiteSpace: 'nowrap',
                  fontSize: '12px',
                  fontWeight: '800',
                  background: selectedMonth === m.value ? '#3b82f6' : t.subtleBg,
                  color: selectedMonth === m.value ? '#ffffff' : t.textPrimary,
                  boxShadow: selectedMonth === m.value ? '0 4px 12px rgba(59,130,246,0.3)' : 'none',
                  border: selectedMonth === m.value ? 'none' : t.border,
                  cursor: 'pointer',
                  transition: 'all 0.2s',
                }}
              >
                {m.label}
              </div>
            ))}
            
            {/* Custom Month Picker */}
            <input 
              type="month" 
              value={selectedMonth}
              max={`${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}`}
              onChange={(e) => { if (e.target.value) { setSelectedMonth(e.target.value); setCurrentPage(1); } }}
              style={{
                padding: '7px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: '800',
                background: !months.find(m => m.value === selectedMonth) ? '#3b82f6' : t.subtleBg,
                color: !months.find(m => m.value === selectedMonth) ? '#ffffff' : t.textPrimary,
                border: !months.find(m => m.value === selectedMonth) ? 'none' : t.border,
                outline: 'none', cursor: 'pointer', fontFamily: 'inherit',
                flexShrink: 0
              }}
            />
          </div>

          {activeTab === 'boundaries' ? (
            <>
              {/* Summary Hero Card */}
              <div id="history-summary" style={{ margin: '4px 20px 20px', padding: '24px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.shadow }}>
                
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '20px' }}>
                  <div>
                    <div style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '8px' }}>Collected This Month</div>
                    <div style={{ fontSize: '32px', fontWeight: '900', color: t.textPrimary, lineHeight: 1 }}>₱{totalCollected.toLocaleString()}</div>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <div style={{ fontSize: '11px', fontWeight: '800', color: '#ef4444', textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '8px' }}>Shortage This Month</div>
                    <div style={{ fontSize: '24px', fontWeight: '900', color: '#ef4444', lineHeight: 1 }}>₱{totalShortage.toLocaleString()}</div>
                  </div>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
                  {[
                    { label: 'Records', value: monthFilteredRecords.length, icon: calendarOutline, color: '#3b82f6', filterValue: 'all' as const },
                    { label: 'Paid', value: paidCount, icon: checkmarkCircleOutline, color: '#22c55e', filterValue: 'paid' as const },
                    { label: 'Short', value: shortCount, icon: alertCircleOutline, color: '#ef4444', filterValue: 'shortage' as const }
                  ].map((stat, i) => {
                    const isActive = statusFilter === stat.filterValue;
                    return (
                      <div 
                        key={i} 
                        onClick={() => handleFilterClick(stat.filterValue)}
                        style={{ 
                          padding: '12px', 
                          background: isActive ? stat.color + '22' : t.subtleBg, 
                          border: isActive ? `1.5px solid ${stat.color}` : '1.5px solid transparent',
                          borderRadius: '12px', 
                          textAlign: 'center',
                          cursor: 'pointer',
                          transition: 'all 0.2s'
                        }}
                      >
                        <IonIcon icon={stat.icon} style={{ fontSize: '18px', color: stat.color }} />
                        <div style={{ fontSize: '20px', fontWeight: '800', color: isActive ? stat.color : t.textPrimary, margin: '4px 0 2px' }}>{stat.value}</div>
                        <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px' }}>{stat.label}</div>
                      </div>
                    );
                  })}
                </div>
              </div>

              {/* Active Filter Indicator */}
              {statusFilter !== 'all' && (
                <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <IonIcon icon={filterOutline} style={{ fontSize: '14px', color: t.textMuted }} />
                    <span style={{ fontSize: '12px', fontWeight: '800', color: t.textPrimary }}>
                      Showing {statusFilter === 'paid' ? 'Paid / Excess' : 'Shortage'} Records
                    </span>
                  </div>
                  <button 
                    onClick={() => handleFilterClick('all')}
                    style={{ background: 'transparent', border: 'none', color: '#3b82f6', fontSize: '12px', fontWeight: '800', cursor: 'pointer' }}
                  >
                    Clear Filter
                  </button>
                </div>
              )}

              {/* Section Label */}
              <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <IonIcon icon={trendingUpOutline} style={{ fontSize: '16px', color: t.gold }} />
                <span style={{ fontSize: '13px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Recent Collections</span>
              </div>

              {/* Boundary List */}
              <div style={{ padding: '0 20px' }}>
                {loading ? (
                  <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                    <IonSpinner name="crescent" color="warning" />
                  </div>
                ) : filteredRecords.length === 0 ? (
                  <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                    <IonIcon icon={cashOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                    <div style={{ color: '#475569', fontSize: '13px', marginTop: '12px' }}>No records found for this filter.</div>
                  </div>
                ) : (
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {paginatedRecords.map(record => {
                      const sc = statusConfig(record.status);
                      return (
                        <div key={record.id} style={{ padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: sc.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                              <IonIcon icon={cashOutline} style={{ fontSize: '20px', color: sc.color }} />
                            </div>
                            <div>
                              <div style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>
                                {new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </div>
                              <div style={{ fontSize: '11px', marginTop: '2px', display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
                                <span style={{ color: sc.color, fontWeight: '700', textTransform: 'uppercase' }}>{record.status}</span>
                                {record.plate_number && <span style={{ color: t.textPrimary, fontWeight: '800' }}>• UNIT: {record.plate_number}</span>}
                                {record.is_extra === 1 && <span style={{ color: t.gold, fontWeight: '800' }}>• EXTRA</span>}
                              </div>
                            </div>
                          </div>
                          <div style={{ textAlign: 'right' }}>
                            <div style={{ fontSize: '16px', fontWeight: '900', color: sc.color }}>₱{Number(record.actual_boundary).toLocaleString()}</div>
                            <div style={{ fontSize: '10px', color: t.textMuted, marginTop: '2px' }}>/ ₱{Number(record.boundary_amount).toLocaleString()}</div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                )}
              </div>
            </>
          ) : (
            <>
              {/* Debt Payments Summary */}
              <div style={{ display: 'flex', gap: '12px', margin: '4px 20px 20px' }}>
                <div style={{ flex: 1, padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>This Month</div>
                  <div style={{ fontSize: '24px', fontWeight: '900', color: '#22c55e' }}>₱{totalDebtPaid.toLocaleString()}</div>
                  <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>{monthFilteredDebts.length} payment(s)</div>
                </div>
                <div style={{ flex: 1, padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
                  <div style={{ fontSize: '10px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Total Overall</div>
                  <div style={{ fontSize: '24px', fontWeight: '900', color: '#16a34a' }}>₱{totalOverallDebtPaid.toLocaleString()}</div>
                  <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>{debtPayments.length} payment(s)</div>
                </div>
              </div>

              {/* Section Label */}
              <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
                <IonIcon icon={walletOutline} style={{ fontSize: '16px', color: t.gold }} />
                <span style={{ fontSize: '13px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Settled Debts</span>
              </div>

              {/* Debt Payments List */}
              <div style={{ padding: '0 20px' }}>
                {loading ? (
                  <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                    <IonSpinner name="crescent" color="warning" />
                  </div>
                ) : monthFilteredDebts.length === 0 ? (
                  <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                    <div style={{ width: '72px', height: '72px', borderRadius: '50%', background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                      <IonIcon icon={walletOutline} style={{ fontSize: '36px', color: '#3b82f6' }} />
                    </div>
                    <div style={{ fontSize: '14px', fontWeight: '700', color: t.textMuted }}>No debt payments this month</div>
                    <div style={{ fontSize: '12px', color: t.textMuted, marginTop: '6px' }}>Settled debts will appear here.</div>
                  </div>
                ) : (
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {paginatedDebts.map(debt => (
                      <div key={`debt-${debt.id}`} style={{ padding: '16px', background: t.card, ...t.glass, border: t.border, borderRadius: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                          <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: 'rgba(34,197,94,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <IonIcon icon={shieldCheckmarkOutline} style={{ fontSize: '20px', color: '#22c55e' }} />
                          </div>
                          <div>
                            <div style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>
                              {new Date(debt.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                            </div>
                            <div style={{ fontSize: '11px', marginTop: '2px', display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
                              <span style={{ color: '#f59e0b', fontWeight: '700' }}>{debt.incident_type}</span>
                              {debt.plate_number && <span style={{ color: t.textPrimary, fontWeight: '800' }}>• {debt.plate_number}</span>}
                            </div>
                          </div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                          <div style={{ fontSize: '16px', fontWeight: '900', color: '#22c55e' }}>₱{Number(debt.actual_boundary).toLocaleString()}</div>
                          <div style={{ fontSize: '10px', fontWeight: '700', color: '#22c55e', marginTop: '4px', padding: '2px 8px', borderRadius: '6px', background: 'rgba(34,197,94,0.12)' }}>SETTLED</div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </>
          )}

          {/* Pagination Controls (shared) */}
          {totalPages > 1 && (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '16px', marginTop: '12px', padding: '10px 20px' }}>
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
      </IonContent>
    </IonPage>
  );
};

export default History;

