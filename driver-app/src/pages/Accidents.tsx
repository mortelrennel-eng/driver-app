import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle, useIonToast } from '@ionic/react';
import { 
  carOutline, 
  shieldOutline,
  warningOutline,
  documentTextOutline,
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
} from 'ionicons/icons';
import axios from 'axios';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

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

const getStatusStyles = (status: string) => {
  const s = (status || '').toLowerCase();
  if (s === 'pending')    return { color: '#f59e0b', bg: 'rgba(245,158,11,0.12)', border: 'rgba(245,158,11,0.25)', icon: hourglassOutline };
  if (s === 'responding') return { color: '#3b82f6', bg: 'rgba(59,130,246,0.12)', border: 'rgba(59,130,246,0.25)', icon: medkitOutline };
  if (s === 'resolved')   return { color: '#22c55e', bg: 'rgba(34,197,94,0.12)',  border: 'rgba(34,197,94,0.25)',  icon: checkmarkCircleOutline };
  if (s === 'cancelled')  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)', icon: closeCircleOutline };
  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)', border: 'rgba(148,163,184,0.2)', icon: hourglassOutline };
};

const ITEMS_PER_PAGE = 5;

const Accidents: React.FC = () => {
  const { t } = useTheme();
  const [presentToast] = useIonToast();

  const [accidents, setAccidents] = useState<AccidentReport[]>([]);
  const [loading, setLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);

  // Edit Modal State
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

  const fetchAccidents = async () => {
    try {
      const response = await cachedGet(endpoints.accidentReports);
      if (response.data.success) {
        setAccidents(response.data.data);
        setCurrentPage(1);
      }
    } catch (e) {
      console.error('Failed to fetch accident reports', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchAccidents(); }, []);

  const filtered = accidents.filter(a => a.created_at?.startsWith(selectedMonth));

  const totalPages = Math.ceil(filtered.length / ITEMS_PER_PAGE);
  const paginated = filtered.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

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

  const isEditableToday = (createdAt: string) => {
    const created = new Date(createdAt);
    const now = new Date();
    const createdDate = created.toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
    const todayDate = now.toLocaleDateString('en-PH', { timeZone: 'Asia/Manila' });
    return createdDate === todayDate;
  };

  const openEdit = (report: AccidentReport) => {
    setEditingReport(report);
    setEditDescription(report.notes || '');
  };

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
                background: p === page ? `linear-gradient(135deg, ${danger}, #dc2626)` : t.subtleBg,
                color: p === page ? '#fff' : t.textMuted,
                fontWeight: p === page ? '900' : '600', fontSize: '13px',
                transition: 'all 0.2s ease',
                boxShadow: p === page ? '0 4px 12px rgba(239,68,68,0.3)' : 'none',
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
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>SOS / Accidents</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={e => fetchAccidents().then(() => e.detail.complete())}>
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
              <IonSpinner name="crescent" color="danger" />
            </div>
          ) : filtered.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '80px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
              <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                <IonIcon icon={shieldOutline} style={{ fontSize: '40px', color: green }} />
              </div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No SOS / Accident Reports</div>
              <div style={{ color: t.textMuted, fontSize: '14px' }}>No emergency reports found for this month. Drive safe!</div>
            </div>
          ) : (
            <>
              {/* ── Accident Report Cards ── */}
              <div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
                {paginated.map(report => {
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

                      {/* Location */}
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

              <PaginationControls page={currentPage} total={totalPages} setPage={setCurrentPage} />
              <div style={{ textAlign: 'center', marginTop: '12px', color: t.textMuted, fontSize: '12px', fontWeight: '600' }}>
                Showing {filtered.length > 0 ? (currentPage - 1) * ITEMS_PER_PAGE + 1 : 0}–{Math.min(currentPage * ITEMS_PER_PAGE, filtered.length)} of {filtered.length} records
              </div>
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

export default Accidents;
