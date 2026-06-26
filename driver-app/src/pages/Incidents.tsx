import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle, useIonToast } from '@ionic/react';
import { 
  carOutline, 
  shieldOutline,
  warningOutline,
  documentTextOutline,
  cashOutline,
  chevronBackOutline,
  chevronForwardOutline,
  locationOutline,
  timeOutline,
  megaphoneOutline,
  medkitOutline,
  checkmarkCircleOutline,
  closeCircleOutline,
  hourglassOutline,
  createOutline,
  saveOutline,
  closeOutline,
  filterOutline,
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

interface AccidentReport {
  id: number;
  status: string;
  notes: string;
  latitude: number | null;
  longitude: number | null;
  created_at: string;
  updated_at: string;
  plate_number: string | null;
  type: string;
  address: string | null;
}

const getSeverityStyles = (severity: string) => {
  const s = severity.toLowerCase();
  if (s === 'critical') return { color: '#ef4444', bg: 'rgba(239,68,68,0.12)', border: 'rgba(239,68,68,0.25)' };
  if (s === 'high')     return { color: '#fb923c', bg: 'rgba(251,146,60,0.12)', border: 'rgba(251,146,60,0.25)' };
  if (s === 'medium')   return { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)' };
  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)' };
};

const getStatusStyles = (status: string) => {
  const s = (status || '').toLowerCase();
  if (s === 'pending')    return { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)', icon: hourglassOutline };
  if (s === 'responding') return { color: '#3b82f6', bg: 'rgba(59,130,246,0.12)', border: 'rgba(59,130,246,0.25)', icon: medkitOutline };
  if (s === 'resolved')   return { color: '#22c55e', bg: 'rgba(34,197,94,0.12)',  border: 'rgba(34,197,94,0.25)',  icon: checkmarkCircleOutline };
  if (s === 'cancelled')  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)', icon: closeCircleOutline };
  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)', icon: hourglassOutline };
};

const SEVERITY_FILTERS = ['all', 'critical', 'high', 'medium', 'low'] as const;

const ITEMS_PER_PAGE = 5;

const Incidents: React.FC = () => {
  const { t } = useTheme();
  const [presentToast] = useIonToast();
  const [activeTab, setActiveTab] = useState<'incidents' | 'accidents'>('incidents');

  // ── Incidents State ──
  const [incidents, setIncidents] = useState<IncidentRecord[]>([]);
  const [loadingIncidents, setLoadingIncidents] = useState(true);
  const [incidentPage, setIncidentPage] = useState(1);
  const [severityFilter, setSeverityFilter] = useState<string>('all');

  // ── Accident Reports State ──
  const [accidents, setAccidents] = useState<AccidentReport[]>([]);
  const [loadingAccidents, setLoadingAccidents] = useState(true);
  const [accidentPage, setAccidentPage] = useState(1);

  // ── Edit Modal State ──
  const [editingReport, setEditingReport] = useState<AccidentReport | null>(null);
  const [editDescription, setEditDescription] = useState('');
  const [saving, setSaving] = useState(false);

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
      const response = await axios.get(endpoints.driverIncidents);
      if (response.data.success) {
        setIncidents(response.data.data);
        setIncidentPage(1);
      }
    } catch (e) {
      console.error('Failed to fetch incidents', e);
    } finally {
      setLoadingIncidents(false);
    }
  };

  const fetchAccidents = async () => {
    try {
      const response = await axios.get(endpoints.accidentReports);
      if (response.data.success) {
        setAccidents(response.data.data);
        setAccidentPage(1);
      }
    } catch (e) {
      console.error('Failed to fetch accident reports', e);
    } finally {
      setLoadingAccidents(false);
    }
  };

  useEffect(() => {
    fetchIncidents();
    fetchAccidents();
  }, []);

  const handleRefresh = async () => {
    await Promise.all([fetchIncidents(), fetchAccidents()]);
  };

  // ── Filter by Month ──
  const monthFilteredIncidents = incidents.filter(i => i.incident_date?.startsWith(selectedMonth));
  const filteredAccidents = accidents.filter(a => a.created_at?.startsWith(selectedMonth));

  // ── Filter by Severity ──
  const filteredIncidents = severityFilter === 'all'
    ? monthFilteredIncidents
    : monthFilteredIncidents.filter(i => i.severity.toLowerCase() === severityFilter);

  // ── Pagination ──
  const incidentTotalPages = Math.ceil(filteredIncidents.length / ITEMS_PER_PAGE);
  const paginatedIncidents = filteredIncidents.slice((incidentPage - 1) * ITEMS_PER_PAGE, incidentPage * ITEMS_PER_PAGE);

  const accidentTotalPages = Math.ceil(filteredAccidents.length / ITEMS_PER_PAGE);
  const paginatedAccidents = filteredAccidents.slice((accidentPage - 1) * ITEMS_PER_PAGE, accidentPage * ITEMS_PER_PAGE);

  // ── Incident Stats (only Critical + Total Charges remain) ──
  const totalCharge    = monthFilteredIncidents.reduce((s, i) => s + Number(i.total_charge_to_driver || 0), 0);
  const criticalCount  = monthFilteredIncidents.filter(i => i.severity.toLowerCase() === 'critical').length;

  const gold   = '#eab308';
  const danger = '#ef4444';
  const info   = '#3b82f6';
  const green  = '#22c55e';


  // ── Parse notes into structured data ──
  const parseNotes = (notes: string | null) => {
    if (!notes) return { description: 'No details provided', damageLevel: '' };
    const dmMatch = notes.match(/Damage Level:\s*(.+)/i);
    const descMatch = notes.match(/Description:\s*([\s\S]+)/i);
    return {
      damageLevel: dmMatch ? dmMatch[1].trim().replace(/\n.*/, '') : '',
      description: descMatch ? descMatch[1].trim() : notes.replace(/---\s*ACCIDENT REPORT\s*---\s*/gi, '').replace(/Damage Level:\s*.+\n?/gi, '').trim() || notes,
    };
  };

  const getDamageLevelStyles = (level: string | null) => {
    if (!level) return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)' };
    const l = level.toLowerCase();
    if (l === 'total_loss') return { color: '#ef4444', bg: 'rgba(239,68,68,0.12)', border: 'rgba(239,68,68,0.25)' };
    if (l === 'major')      return { color: '#fb923c', bg: 'rgba(251,146,60,0.12)', border: 'rgba(251,146,60,0.25)' };
    if (l === 'moderate')   return { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)' };
    return { color: '#22c55e', bg: 'rgba(34,197,94,0.12)', border: 'rgba(34,197,94,0.25)' };
  };

  // ── Check if report was created today (editable) ──
  const isEditableToday = (createdAt: string) => {
    const created = new Date(createdAt);
    const now = new Date();
    // Compare dates in Manila timezone
    const createdDate = created.toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
    const todayDate = now.toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
    return createdDate === todayDate;
  };

  // ── Open edit modal ──
  const openEdit = (report: AccidentReport) => {
    setEditingReport(report);
    setEditDescription(report.notes || '');
  };

  // ── Save edit ──
  const saveEdit = async () => {
    if (!editingReport) return;
    setSaving(true);
    try {
      const res = await axios.put(endpoints.updateAccidentReport(editingReport.id), {
        description: editDescription,
      });
      if (res.data.success) {
        presentToast({ message: 'Report updated!', duration: 2000, color: 'success' });
        setEditingReport(null);
        fetchAccidents();
      } else {
        presentToast({ message: res.data.message || 'Failed to update', duration: 3000, color: 'danger' });
      }
    } catch (e: any) {
      const msg = e?.response?.data?.message || 'Failed to update report';
      presentToast({ message: msg, duration: 3000, color: 'danger' });
    } finally {
      setSaving(false);
    }
  };

  // ── Cycle severity filter ──
  const cycleSeverityFilter = () => {
    const idx = SEVERITY_FILTERS.indexOf(severityFilter as any);
    const next = SEVERITY_FILTERS[(idx + 1) % SEVERITY_FILTERS.length];
    setSeverityFilter(next);
    setIncidentPage(1);
  };

  // ── Pagination Component ──
  const PaginationControls = ({ currentPage, totalPages, setPage }: { currentPage: number, totalPages: number, setPage: (p: number) => void }) => {
    if (totalPages <= 1) return null;
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '12px', marginTop: '24px' }}>
        <button
          disabled={currentPage === 1}
          onClick={() => setPage(Math.max(1, currentPage - 1))}
          style={{
            width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: currentPage === 1 ? 'default' : 'pointer',
            background: currentPage === 1 ? t.subtleBg : t.card,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            opacity: currentPage === 1 ? 0.35 : 1, boxShadow: t.cardShadow, transition: 'all 0.2s ease',
          }}
        >
          <IonIcon icon={chevronBackOutline} style={{ fontSize: '18px', color: t.textPrimary }} />
        </button>
        <div style={{ display: 'flex', gap: '6px' }}>
          {Array.from({ length: totalPages }, (_, i) => i + 1).map(page => (
            <button
              key={page} onClick={() => setPage(page)}
              style={{
                width: '36px', height: '36px', borderRadius: '10px', border: 'none', cursor: 'pointer',
                background: page === currentPage ? `linear-gradient(135deg, ${gold}, #f59e0b)` : t.subtleBg,
                color: page === currentPage ? '#000' : t.textMuted,
                fontWeight: page === currentPage ? '900' : '600', fontSize: '13px',
                transition: 'all 0.2s ease',
                boxShadow: page === currentPage ? '0 4px 12px rgba(234,179,8,0.3)' : 'none',
              }}
            >{page}</button>
          ))}
        </div>
        <button
          disabled={currentPage === totalPages}
          onClick={() => setPage(Math.min(totalPages, currentPage + 1))}
          style={{
            width: '40px', height: '40px', borderRadius: '12px', border: 'none', cursor: currentPage === totalPages ? 'default' : 'pointer',
            background: currentPage === totalPages ? t.subtleBg : t.card,
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            opacity: currentPage === totalPages ? 0.35 : 1, boxShadow: t.cardShadow, transition: 'all 0.2s ease',
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
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Incident Log</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => handleRefresh().then(() => e.detail.complete())}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{ minHeight: '100%', background: t.bg, padding: '20px 20px 140px 20px' }}>


          {/* ── Tab Switcher ── */}
          <div style={{
            display: 'flex', gap: '4px', marginBottom: '16px',
            background: t.subtleBg, borderRadius: '14px', padding: '4px', border: t.border,
          }}>
            <button
              onClick={() => { setActiveTab('incidents'); setIncidentPage(1); }}
              style={{
                flex: 1, padding: '10px 0', borderRadius: '11px', border: 'none',
                fontSize: '13px', fontWeight: '800', cursor: 'pointer', transition: 'all 0.25s ease',
                background: activeTab === 'incidents' ? `linear-gradient(135deg, ${gold}, #f59e0b)` : 'transparent',
                color: activeTab === 'incidents' ? '#000' : t.textMuted,
                boxShadow: activeTab === 'incidents' ? '0 4px 12px rgba(234,179,8,0.3)' : 'none',
              }}
            >
              <IonIcon icon={warningOutline} style={{ fontSize: '14px', marginRight: '6px', verticalAlign: 'middle' }} />
              Violations
              {monthFilteredIncidents.length > 0 && (
                <span style={{
                  marginLeft: '6px', padding: '2px 7px', borderRadius: '8px', fontSize: '10px', fontWeight: '900',
                  background: activeTab === 'incidents' ? 'rgba(0,0,0,0.15)' : 'rgba(234,179,8,0.15)',
                  color: activeTab === 'incidents' ? '#000' : gold,
                }}>{monthFilteredIncidents.length}</span>
              )}
            </button>
            <button
              onClick={() => { setActiveTab('accidents'); setAccidentPage(1); }}
              style={{
                flex: 1, padding: '10px 0', borderRadius: '11px', border: 'none',
                fontSize: '13px', fontWeight: '800', cursor: 'pointer', transition: 'all 0.25s ease',
                background: activeTab === 'accidents' ? `linear-gradient(135deg, ${danger}, #dc2626)` : 'transparent',
                color: activeTab === 'accidents' ? '#fff' : t.textMuted,
                boxShadow: activeTab === 'accidents' ? '0 4px 12px rgba(239,68,68,0.3)' : 'none',
              }}
            >
              <IonIcon icon={megaphoneOutline} style={{ fontSize: '14px', marginRight: '6px', verticalAlign: 'middle' }} />
              SOS / Accident
              {filteredAccidents.length > 0 && (
                <span style={{
                  marginLeft: '6px', padding: '2px 7px', borderRadius: '8px', fontSize: '10px', fontWeight: '900',
                  background: activeTab === 'accidents' ? 'rgba(255,255,255,0.25)' : 'rgba(239,68,68,0.15)',
                  color: activeTab === 'accidents' ? '#fff' : danger,
                }}>{filteredAccidents.length}</span>
              )}
            </button>
          </div>

          {/* Month Selector Chips */}
          <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', paddingBottom: '16px', alignItems: 'center', msOverflowStyle: 'none', scrollbarWidth: 'none', WebkitOverflowScrolling: 'touch' }}>
            {months.map(m => (
              <div key={m.value}
                onClick={() => { setSelectedMonth(m.value); setIncidentPage(1); setAccidentPage(1); }}
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
              onChange={(e) => { if (e.target.value) { setSelectedMonth(e.target.value); setIncidentPage(1); setAccidentPage(1); } }}
              style={{
                padding: '7px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: '800',
                background: !months.find(m => m.value === selectedMonth) ? '#3b82f6' : t.subtleBg,
                color: !months.find(m => m.value === selectedMonth) ? '#ffffff' : t.textPrimary,
                border: !months.find(m => m.value === selectedMonth) ? 'none' : t.border,
                outline: 'none', cursor: 'pointer', fontFamily: 'inherit', flexShrink: 0
              }}
            />
          </div>

          {/* ═══════════════════ VIOLATIONS TAB ═══════════════════ */}
          {activeTab === 'incidents' && (
            <>
              {loadingIncidents ? (
                <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                  <IonSpinner name="crescent" color="warning" />
                </div>
              ) : monthFilteredIncidents.length === 0 ? (
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
                        {severityFilter === 'all' ? criticalCount : filteredIncidents.length}
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
                        <span onClick={() => { setSeverityFilter('all'); setIncidentPage(1); }}
                          style={{ cursor: 'pointer', fontWeight: '900', fontSize: '14px', lineHeight: 1 }}>×</span>
                      </div>
                      <span style={{ fontSize: '11px', color: t.textMuted }}>{filteredIncidents.length} records</span>
                    </div>
                  )}

                  {/* ── Violation Cards ── */}
                  {filteredIncidents.length === 0 && severityFilter !== 'all' ? (
                    <div style={{ textAlign: 'center', padding: '40px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
                      <div style={{ fontSize: '16px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No {severityFilter} violations</div>
                      <div style={{ color: t.textMuted, fontSize: '13px' }}>Tap the filter card to change severity level.</div>
                    </div>
                  ) : (
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                      {paginatedIncidents.map(incident => {
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

                  <PaginationControls currentPage={incidentPage} totalPages={incidentTotalPages} setPage={setIncidentPage} />
                  <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
                    Showing {filteredIncidents.length > 0 ? (incidentPage - 1) * ITEMS_PER_PAGE + 1 : 0}–{Math.min(incidentPage * ITEMS_PER_PAGE, filteredIncidents.length)} of {filteredIncidents.length} records
                  </div>
                </>
              )}
            </>
          )}

          {/* ═══════════════════ SOS / ACCIDENT TAB ═══════════════════ */}
          {activeTab === 'accidents' && (
            <>
              {loadingAccidents ? (
                <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                  <IonSpinner name="crescent" color="danger" />
                </div>
              ) : filteredAccidents.length === 0 ? (
                <div style={{ textAlign: 'center', padding: '80px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
                  <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                    <IonIcon icon={shieldOutline} style={{ fontSize: '40px', color: green }} />
                  </div>
                  <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No SOS / Accident Reports</div>
                  <div style={{ color: t.textMuted, fontSize: '14px' }}>No emergency reports found for this month. Drive safe!</div>
                </div>
              ) : (
                <>
                  {/* ── Accident Report Cards (NO summary cards) ── */}
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                    {paginatedAccidents.map(report => {
                      const ss = getStatusStyles(report.status);
                      const parsed = parseNotes(report.notes);
                      const dls = getDamageLevelStyles(parsed.damageLevel || null);
                      const isAccident = report.type === 'accident';
                      const typeColor = isAccident ? danger : '#f59e0b';
                      const typeBg = isAccident ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)';
                      const editable = isEditableToday(report.created_at);

                      return (
                        <div key={report.id} style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.cardShadow, position: 'relative' }}>
                          {/* Edit button (only if editable) */}
                          {editable && (
                            <button onClick={() => openEdit(report)} style={{
                              position: 'absolute', top: '14px', right: '14px', width: '32px', height: '32px',
                              borderRadius: '10px', border: 'none', cursor: 'pointer',
                              background: 'rgba(59,130,246,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center',
                              transition: 'all 0.2s',
                            }}>
                              <IonIcon icon={createOutline} style={{ fontSize: '16px', color: info }} />
                            </button>
                          )}

                          {/* Header row */}
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '14px', paddingRight: editable ? '40px' : '0' }}>
                            <div style={{
                              width: '44px', height: '44px', borderRadius: '14px', background: typeBg,
                              display: 'flex', alignItems: 'center', justifyContent: 'center', border: `1px solid ${typeColor}33`, flexShrink: 0,
                            }}>
                              <IonIcon icon={isAccident ? warningOutline : megaphoneOutline} style={{ fontSize: '22px', color: typeColor }} />
                            </div>
                            <div style={{ flex: 1, minWidth: 0 }}>
                              <div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
                                <span style={{ fontSize: '15px', fontWeight: '800', color: t.textPrimary }}>
                                  {isAccident ? 'Accident Report' : 'SOS Alert'}
                                </span>
                                <div style={{ padding: '3px 8px', background: ss.bg, borderRadius: '8px', border: `1px solid ${ss.border}` }}>
                                  <span style={{ fontSize: '9px', fontWeight: '900', color: ss.color, textTransform: 'uppercase', letterSpacing: '1px' }}>{report.status}</span>
                                </div>
                              </div>
                              <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '3px' }}>
                                <IonIcon icon={timeOutline} style={{ fontSize: '11px', marginRight: '4px', verticalAlign: 'middle' }} />
                                {new Date(report.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                {!editable && <span style={{ marginLeft: '6px', fontSize: '9px', color: t.textMuted, fontStyle: 'italic' }}>(Read-only)</span>}
                              </div>
                            </div>
                          </div>

                          {/* Notes / Description */}
                          <div style={{ background: t.descBg, padding: '14px', borderRadius: '14px', marginBottom: '14px' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                              <IonIcon icon={documentTextOutline} style={{ fontSize: '14px', color: danger }} />
                              <span style={{ fontSize: '10px', fontWeight: '800', color: danger, textTransform: 'uppercase', letterSpacing: '1px' }}>Details</span>
                            </div>
                            <div style={{ fontSize: '13px', color: t.textSecondary, lineHeight: '1.55', whiteSpace: 'pre-wrap' }}>{parsed.description}</div>
                          </div>

                          {/* Info grid */}
                          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
                            <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: t.inputBorder }}>
                              <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Unit</div>
                              <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                <IonIcon icon={carOutline} style={{ fontSize: '14px', color: info }} />
                                <span style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>{report.plate_number || 'N/A'}</span>
                              </div>
                            </div>

                            {parsed.damageLevel ? (
                              <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: t.inputBorder }}>
                                <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Damage Level</div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                  <div style={{ width: '8px', height: '8px', borderRadius: '50%', background: dls.color }} />
                                  <span style={{ fontSize: '13px', fontWeight: '800', color: dls.color, textTransform: 'capitalize' }}>{parsed.damageLevel.replace(/_/g, ' ')}</span>
                                </div>
                              </div>
                            ) : (
                              <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: t.inputBorder }}>
                                <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Type</div>
                                <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                                  <div style={{ width: '8px', height: '8px', borderRadius: '50%', background: typeColor }} />
                                  <span style={{ fontSize: '13px', fontWeight: '800', color: typeColor, textTransform: 'capitalize' }}>{report.type || 'SOS'}</span>
                                </div>
                              </div>
                            )}
                          </div>

                          {/* Location as text address */}
                          {(report.address || (report.latitude && report.longitude)) && (
                            <div style={{ marginTop: '12px', display: 'flex', alignItems: 'flex-start', gap: '8px', paddingTop: '12px', borderTop: t.border }}>
                              <IonIcon icon={locationOutline} style={{ fontSize: '14px', color: t.textMuted, marginTop: '1px', flexShrink: 0 }} />
                              <span style={{ fontSize: '11px', color: t.textMuted, lineHeight: '1.4' }}>
                                {report.address || 'Location recorded'}
                              </span>
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>

                  <PaginationControls currentPage={accidentPage} totalPages={accidentTotalPages} setPage={setAccidentPage} />
                  <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
                    Showing {filteredAccidents.length > 0 ? (accidentPage - 1) * ITEMS_PER_PAGE + 1 : 0}–{Math.min(accidentPage * ITEMS_PER_PAGE, filteredAccidents.length)} of {filteredAccidents.length} records
                  </div>
                </>
              )}
            </>
          )}
        </div>

        {/* ═══════════════════ EDIT MODAL ═══════════════════ */}
        {editingReport && (
          <div style={{
            position: 'fixed', top: 0, left: 0, right: 0, bottom: 0,
            background: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(6px)',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
            zIndex: 9999, padding: '20px',
          }}
            onClick={(e) => { if (e.target === e.currentTarget) setEditingReport(null); }}
          >
            <div style={{
              background: t.card, borderRadius: '24px', padding: '28px', width: '100%', maxWidth: '420px',
              boxShadow: '0 25px 50px rgba(0,0,0,0.25)', border: t.border, maxHeight: '80vh', overflowY: 'auto',
            }}>
              {/* Modal Header */}
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <div>
                  <div style={{ fontSize: '18px', fontWeight: '900', color: t.textPrimary }}>Edit Report</div>
                  <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>
                    {new Date(editingReport.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                  </div>
                </div>
                <button onClick={() => setEditingReport(null)} style={{
                  width: '36px', height: '36px', borderRadius: '12px', border: 'none', cursor: 'pointer',
                  background: 'rgba(239,68,68,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                  <IonIcon icon={closeOutline} style={{ fontSize: '20px', color: danger }} />
                </button>
              </div>



              {/* Description */}
              <div style={{ marginBottom: '20px' }}>
                <label style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '8px', display: 'block' }}>
                  Description
                </label>
                <textarea
                  value={editDescription}
                  onChange={(e) => setEditDescription(e.target.value)}
                  rows={5}
                  style={{
                    width: '100%', padding: '14px', borderRadius: '14px', border: t.inputBorder || '1px solid #e2e8f0',
                    background: t.inputBg, color: t.textPrimary, fontSize: '14px', fontFamily: 'inherit',
                    resize: 'vertical', outline: 'none', lineHeight: '1.5',
                    boxSizing: 'border-box',
                  }}
                  placeholder="Describe the incident..."
                />
              </div>

              {/* Save Button */}
              <button onClick={saveEdit} disabled={saving} style={{
                width: '100%', padding: '14px', borderRadius: '14px', border: 'none', cursor: saving ? 'default' : 'pointer',
                background: saving ? '#94a3b8' : `linear-gradient(135deg, ${info}, #1d4ed8)`,
                color: '#fff', fontSize: '15px', fontWeight: '800', transition: 'all 0.2s',
                display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px',
                boxShadow: saving ? 'none' : '0 4px 12px rgba(59,130,246,0.3)',
              }}>
                {saving ? <IonSpinner name="crescent" style={{ width: '18px', height: '18px' }} /> : <IonIcon icon={saveOutline} style={{ fontSize: '18px' }} />}
                {saving ? 'Saving...' : 'Save Changes'}
              </button>
            </div>
          </div>
        )}
      </IonContent>
    </IonPage>
  );
};

export default Incidents;
