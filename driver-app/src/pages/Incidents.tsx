import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle } from '@ionic/react';
import { 
  carOutline, 
  shieldOutline,
  warningOutline,
  documentTextOutline,
  cashOutline,
  alertCircleOutline,
  chevronBackOutline,
  chevronForwardOutline,
  statsChartOutline,
} from 'ionicons/icons';
import axios from 'axios';
import { endpoints } from '../config/api';
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

const ITEMS_PER_PAGE = 5;

const Incidents: React.FC = () => {
  const { t } = useTheme();
  const [incidents, setIncidents] = useState<IncidentRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);

  const fetchIncidents = async () => {
    try {
      const response = await axios.get(endpoints.driverIncidents);
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

  // ── Pagination ──────────────────────────────────────────
  const totalPages = Math.ceil(incidents.length / ITEMS_PER_PAGE);
  const paginated  = incidents.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  // ── Stats ───────────────────────────────────────────────
  const totalCharge    = incidents.reduce((s, i) => s + Number(i.total_charge_to_driver || 0), 0);
  const totalBalance   = incidents.reduce((s, i) => s + Number(i.remaining_balance || 0), 0);
  const criticalCount  = incidents.filter(i => i.severity.toLowerCase() === 'critical').length;
  const unpaidCount    = incidents.filter(i => i.charge_status !== 'paid' && Number(i.total_charge_to_driver) > 0).length;

  const gold   = '#eab308';
  const danger = '#ef4444';
  const info   = '#3b82f6';
  const green  = '#22c55e';
  const orange = '#fb923c';

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Incident Log</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchIncidents().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        {/* Extra bottom padding so content clears BottomNav */}
        <div style={{ minHeight: '100%', background: t.bg, padding: '20px 20px 140px 20px' }}>

          {/* Page header */}
          <div style={{ marginBottom: '24px' }}>
            <h1 style={{ color: t.textPrimary, fontSize: '28px', fontWeight: '900', margin: '0 0 6px' }}>My Incidents</h1>
            <p style={{ color: t.textSecondary, fontSize: '14px', margin: 0 }}>Review recorded behaviors and violations.</p>
          </div>

          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : incidents.length === 0 ? (
            /* ── Empty state ── */
            <div style={{ textAlign: 'center', padding: '80px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
              <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                <IonIcon icon={shieldOutline} style={{ fontSize: '40px', color: green }} />
              </div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No Incidents Found</div>
              <div style={{ color: t.textMuted, fontSize: '14px' }}>You have a clean record! Keep driving safely.</div>
            </div>
          ) : (
            <>
              {/* ── Summary Stats ──────────────────────────────── */}
              <div style={{ marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '12px' }}>
                  <IonIcon icon={statsChartOutline} style={{ fontSize: '16px', color: gold }} />
                  <span style={{ fontSize: '11px', fontWeight: '800', color: gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Summary</span>
                </div>

                {/* Row 1 */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px', marginBottom: '10px' }}>
                  {/* Total Incidents */}
                  <div style={{ padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                      <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(59,130,246,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <IonIcon icon={alertCircleOutline} style={{ fontSize: '16px', color: info }} />
                      </div>
                    </div>
                    <div style={{ fontSize: '24px', fontWeight: '900', color: t.textPrimary, lineHeight: 1 }}>{incidents.length}</div>
                    <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>Total Incidents</div>
                  </div>

                  {/* Critical */}
                  <div style={{ padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                      <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(239,68,68,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <IonIcon icon={warningOutline} style={{ fontSize: '16px', color: danger }} />
                      </div>
                    </div>
                    <div style={{ fontSize: '24px', fontWeight: '900', color: criticalCount > 0 ? danger : t.textPrimary, lineHeight: 1 }}>{criticalCount}</div>
                    <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>Critical</div>
                  </div>
                </div>

                {/* Row 2 */}
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                  {/* Total Charges */}
                  <div style={{ padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                      <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(234,179,8,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <IonIcon icon={cashOutline} style={{ fontSize: '16px', color: gold }} />
                      </div>
                    </div>
                    <div style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary, lineHeight: 1 }}>₱{totalCharge.toLocaleString()}</div>
                    <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>Total Charges</div>
                  </div>

                  {/* Unpaid */}
                  <div style={{ padding: '14px 16px', background: t.card, ...t.glass, border: t.border, borderRadius: '18px', boxShadow: t.cardShadow }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                      <div style={{ width: '32px', height: '32px', borderRadius: '10px', background: 'rgba(251,146,60,0.12)', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                        <IonIcon icon={cashOutline} style={{ fontSize: '16px', color: orange }} />
                      </div>
                    </div>
                    <div style={{ fontSize: '18px', fontWeight: '900', color: unpaidCount > 0 ? orange : green, lineHeight: 1 }}>
                      {unpaidCount > 0 ? `₱${totalBalance.toLocaleString()}` : '₱0'}
                    </div>
                    <div style={{ fontSize: '10px', color: t.textMuted, fontWeight: '700', textTransform: 'uppercase', letterSpacing: '0.5px', marginTop: '4px' }}>Balance Due</div>
                  </div>
                </div>
              </div>

              {/* ── Incident Cards ─────────────────────────────── */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                {paginated.map(incident => {
                  const ss = getSeverityStyles(incident.severity);
                  return (
                    <div key={incident.id} style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.cardShadow }}>
                      {/* Header row */}
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

                      {/* Description */}
                      <div style={{ background: t.descBg, padding: '14px', borderRadius: '14px', marginBottom: '14px' }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                          <IonIcon icon={documentTextOutline} style={{ fontSize: '14px', color: gold }} />
                          <span style={{ fontSize: '10px', fontWeight: '800', color: gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Description</span>
                        </div>
                        <div style={{ fontSize: '13px', color: t.textSecondary, lineHeight: '1.55' }}>{incident.description}</div>
                      </div>

                      {/* Info grid */}
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

              {/* ── Pagination Controls ────────────────────────── */}
              {totalPages > 1 && (
                <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '12px', marginTop: '24px' }}>
                  {/* Prev */}
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

                  {/* Page pills */}
                  <div style={{ display: 'flex', gap: '6px' }}>
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
                      <button
                        key={page}
                        onClick={() => setCurrentPage(page)}
                        style={{
                          width: '36px', height: '36px', borderRadius: '10px', border: 'none',
                          cursor: 'pointer',
                          background: page === currentPage ? `linear-gradient(135deg, ${gold}, #f59e0b)` : t.subtleBg,
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

                  {/* Next */}
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

              {/* Record count label */}
              <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
                Showing {(currentPage - 1) * ITEMS_PER_PAGE + 1}–{Math.min(currentPage * ITEMS_PER_PAGE, incidents.length)} of {incidents.length} records
              </div>
            </>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Incidents;
