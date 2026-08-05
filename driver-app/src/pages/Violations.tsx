import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle } from '@ionic/react';
import { 
  carOutline, 
  shieldOutline,
  warningOutline,
  documentTextOutline,
  cashOutline,
  chevronBackOutline,
  chevronForwardOutline,
  filterOutline,
} from 'ionicons/icons';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

interface IncidentRecord {
  id: number;
  incident_date: string;
  incident_type: string;
  sub_classification: string;
  severity: string;
  description: string;
  total_charge_to_driver: number;
  remaining_balance: number;
  charge_status: string;
  plate_number: string;
  timestamp: string;
}

const getSeverityStyles = (severity: string) => {
  const s = severity.toLowerCase();
  if (s === 'critical') return { color: '#ef4444', bg: 'rgba(239,68,68,0.12)', border: 'rgba(239,68,68,0.25)' };
  if (s === 'high')     return { color: '#fb923c', bg: 'rgba(251,146,60,0.12)', border: 'rgba(251,146,60,0.25)' };
  if (s === 'medium')   return { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)' };
  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)' };
};

const SEVERITY_FILTERS = ['all', 'critical', 'high', 'medium', 'low'] as const;
const ITEMS_PER_PAGE = 5;

const Violations: React.FC = () => {
  const { t } = useTheme();

  const [incidents, setIncidents] = useState<IncidentRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [severityFilter, setSeverityFilter] = useState<string>('all');

  // Month Filter
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

  const fetchIncidents = async () => {
    try {
      const response = await cachedGet(endpoints.driverIncidents);
      if (response.data.success) {
        setIncidents(response.data.data);
        setCurrentPage(1);
      }
    } catch (e) {
      console.error('Failed to fetch incidents', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchIncidents(); }, []);

  // ── Filter by Month ──
  const monthFiltered = incidents.filter(i => i.incident_date?.startsWith(selectedMonth));

  // ── Filter by Severity ──
  const filtered = severityFilter === 'all'
    ? monthFiltered
    : monthFiltered.filter(i => i.severity.toLowerCase() === severityFilter);

  // ── Pagination ──
  const totalPages = Math.ceil(filtered.length / ITEMS_PER_PAGE);
  const paginated = filtered.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  // ── Stats ──
  const totalCharge   = monthFiltered.reduce((s, i) => s + Number(i.remaining_balance || 0), 0);
  const criticalCount = monthFiltered.filter(i => i.severity.toLowerCase() === 'critical').length;

  const gold   = '#eab308';
  const danger = '#ef4444';
  const info   = '#3b82f6';
  const green  = '#22c55e';

  // ── Cycle severity filter ──
  const cycleSeverityFilter = () => {
    const idx = SEVERITY_FILTERS.indexOf(severityFilter as any);
    const next = SEVERITY_FILTERS[(idx + 1) % SEVERITY_FILTERS.length];
    setSeverityFilter(next);
    setCurrentPage(1);
  };

  // ── Pagination Component ──
  const PaginationControls = ({ page, total, setPage }: { page: number, total: number, setPage: (p: number) => void }) => {
    if (total <= 1) return null;
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '12px', marginTop: '24px' }}>
        <button
          disabled={page === 1}
          onClick={() => setPage(Math.max(1, page - 1))}
          style={{
            width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: page === 1 ? 'default' : 'pointer',
            background: page === 1 ? t.subtleBg : t.card,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            opacity: page === 1 ? 0.35 : 1, boxShadow: t.cardShadow, transition: 'all 0.2s ease',
          }}
        >
          <IonIcon icon={chevronBackOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
        </button>
        <div style={{ display: 'flex', gap: '6px' }}>
          {Array.from({ length: total }, (_, i) => i + 1).map(p => (
            <button
              key={p} onClick={() => setPage(p)}
              style={{
                width: '36px', height: '36px', borderRadius: '10px', border: 'none', cursor: 'pointer',
                background: p === page ? `linear-gradient(135deg, ${gold}, #f59e0b)` : t.subtleBg,
                color: p === page ? '#000' : t.textMuted,
                fontWeight: p === page ? '900' : '600', fontSize: '13px',
                transition: 'all 0.2s ease',
                boxShadow: p === page ? '0 4px 12px rgba(234,179,8,0.3)' : 'none',
              }}
            >{p}</button>
          ))}
        </div>
        <button
          disabled={page === total}
          onClick={() => setPage(Math.min(total, page + 1))}
          style={{
            width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: page === total ? 'default' : 'pointer',
            background: page === total ? t.subtleBg : t.card,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            opacity: page === total ? 0.35 : 1, boxShadow: t.cardShadow, transition: 'all 0.2s ease',
          }}
        >
          <IonIcon icon={chevronForwardOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
        </button>
      </div>
    );
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Violations</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchIncidents().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, padding: '20px 20px 140px 20px' }}>

          {/* Month Selector Chips */}
          <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', paddingBottom: '16px', alignItems: 'center', msOverflowStyle: 'none', scrollbarWidth: 'none', WebkitOverflowScrolling: 'touch' }}>
            {months.map(m => (
              <div key={m.value}
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
            <input type="month" value={selectedMonth}
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

          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : monthFiltered.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '80px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
              <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                <IonIcon icon={shieldOutline} style={{ fontSize: '40px', color: green }} />
              </div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No Violations Found</div>
              <div style={{ color: t.textMuted, fontSize: '14px' }}>You have a clean record! Keep driving safely.</div>
            </div>
          ) : (
            <>
              {/* ── Summary: Critical (clickable filter) + Total Charges ── */}
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px', marginBottom: '20px' }}>
                {/* Critical - clickable to cycle severity filter */}
                <div
                  onClick={cycleSeverityFilter}
                  style={{
                    padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px',
                    boxShadow: severityFilter !== 'all' ? `0 0 0 2px ${getSeverityStyles(severityFilter).color}` : t.cardShadow,
                    cursor: 'pointer', transition: 'all 0.25s ease', position: 'relative',
                  }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                    <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: severityFilter !== 'all' ? getSeverityStyles(severityFilter).bg : 'rgba(239,68,68,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <IonIcon icon={filterOutline} style={{ fontSize: '16px', color: severityFilter !== 'all' ? getSeverityStyles(severityFilter).color : danger }} />
                    </div>
                  </div>
                  <div style={{ fontSize: '24px', fontWeight: '900', color: severityFilter !== 'all' ? getSeverityStyles(severityFilter).color : (criticalCount > 0 ? danger : t.textPrimary), lineHeight: 1 }}>
                    {severityFilter === 'all' ? criticalCount : filtered.length}
                  </div>
                  <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>
                    {severityFilter === 'all' ? 'Critical' : severityFilter}
                  </div>
                  {/* Filter indicator */}
                  {severityFilter !== 'all' && (
                    <div style={{
                      position: 'absolute', top: '8px', right: '8px',
                      padding: '2px 6px', borderRadius: '6px', fontSize: '8px', fontWeight: '900',
                      background: getSeverityStyles(severityFilter).bg, color: getSeverityStyles(severityFilter).color,
                      textTransform: 'uppercase', letterSpacing: '0.5px',
                    }}>TAP TO CHANGE</div>
                  )}
                </div>

                {/* Debts Balance */}
                <div style={{ padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                    <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(234,179,8,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                      <IonIcon icon={cashOutline} style={{ fontSize: '16px', color: gold }} />
                    </div>
                  </div>
                  <div style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary, lineHeight: 1 }}>₱{totalCharge.toLocaleString()}</div>
                  <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>Debts Balance</div>
                </div>
              </div>

              {/* Active filter chip */}
              {severityFilter !== 'all' && (
                <div style={{ display: 'flex', gap: '8px', marginBottom: '14px', alignItems: 'center' }}>
                  <div style={{
                    padding: '6px 14px', borderRadius: '20px', fontSize: '11px', fontWeight: '800',
                    background: getSeverityStyles(severityFilter).bg, color: getSeverityStyles(severityFilter).color,
                    border: `1px solid ${getSeverityStyles(severityFilter).border}`,
                    display: 'flex', alignItems: 'center', gap: '6px',
                  }}>
                    Showing: {severityFilter.toUpperCase()}
                    <span onClick={() => { setSeverityFilter('all'); setCurrentPage(1); }}
                      style={{ cursor: 'pointer', fontWeight: '900', fontSize: '14px', lineHeight: 1 }}>×</span>
                  </div>
                  <span style={{ fontSize: '11px', color: t.textMuted }}>{filtered.length} records</span>
                </div>
              )}

              {/* ── Violation Cards ── */}
              {filtered.length === 0 && severityFilter !== 'all' ? (
                <div style={{ textAlign: 'center', padding: '40px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
                  <div style={{ fontSize: '16px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No {severityFilter} violations</div>
                  <div style={{ color: t.textMuted, fontSize: '13px' }}>Tap the filter card to change severity level.</div>
                </div>
              ) : (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                  {paginated.map(incident => {
                    const ss = getSeverityStyles(incident.severity);
                    return (
                      <div key={incident.id} style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.cardShadow }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '14px' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: ss.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', border: `1px solid ${ss.border}` }}>
                              <IonIcon icon={warningOutline} style={{ fontSize: '22px', color: ss.color }} />
                            </div>
                            <div>
                              <div style={{ fontSize: '15px', fontWeight: '800', color: t.textPrimary }}>{incident.incident_type}</div>
                              <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>
                                {new Date(incident.incident_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                              </div>
                            </div>
                          </div>
                          <div style={{ padding: '4px 10px', background: ss.bg, borderRadius: '10px', border: `1px solid ${ss.border}` }}>
                            <span style={{ fontSize: '10px', fontWeight: '900', color: ss.color, textTransform: 'uppercase', letterSpacing: '1px' }}>{incident.severity}</span>
                          </div>
                        </div>

                        <div style={{ background: t.descBg, padding: '14px', borderRadius: '14px', marginBottom: '14px' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                            <IonIcon icon={documentTextOutline} style={{ fontSize: '14px', color: gold }} />
                            <span style={{ fontSize: '10px', fontWeight: '800', color: gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Description</span>
                          </div>
                          <div style={{ fontSize: '13px', color: t.textSecondary, lineHeight: '1.55' }}>{incident.description}</div>
                        </div>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                          <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: t.inputBorder }}>
                            <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Unit Involved</div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                              <IonIcon icon={carOutline} style={{ fontSize: '14px', color: info }} />
                              <span style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>{incident.plate_number}</span>
                            </div>
                          </div>
                          <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: t.inputBorder }}>
                            <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Charge Amount</div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                              <IonIcon icon={cashOutline} style={{ fontSize: '14px', color: green }} />
                              <span style={{ fontSize: '13px', fontWeight: '900', color: green }}>₱{Number(incident.total_charge_to_driver).toLocaleString()}</span>
                            </div>
                          </div>
                        </div>

                        {incident.total_charge_to_driver > 0 && (
                          <div style={{ marginTop: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: '12px', borderTop: t.border }}>
                            <div style={{ fontSize: '11px', color: t.textMuted }}>
                              Status: <span style={{ fontWeight: '700', color: incident.charge_status === 'paid' ? green : '#f59e0b' }}>{incident.charge_status.toUpperCase()}</span>
                            </div>
                            <div style={{ fontSize: '11px', color: t.textMuted }}>
                              Balance: <span style={{ fontWeight: '800', color: t.textPrimary }}>₱{Number(incident.remaining_balance).toLocaleString()}</span>
                            </div>
                          </div>
                        )}
                      </div>
                    );
                  })}
                </div>
              )}

              <PaginationControls page={currentPage} total={totalPages} setPage={setCurrentPage} />
              <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
                Showing {filtered.length > 0 ? (currentPage - 1) * ITEMS_PER_PAGE + 1 : 0}–{Math.min(currentPage * ITEMS_PER_PAGE, filtered.length)} of {filtered.length} records
              </div>
            </>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Violations;
