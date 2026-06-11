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
  chevronForwardOutline
} from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';
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
  const ITEMS_PER_PAGE = 10;

  const fetchHistory = async () => {
    try {
      const response = await axios.get(endpoints.boundaryHistory);
      if (response.data.success) {
        const data = Array.isArray(response.data.data) ? response.data.data : [];
        setRecords(data);
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
  const filteredRecords = safeRecords.filter(r => {
    if (!r.date) return false;
    return r.date.startsWith(selectedMonth);
  });

  const totalPages = Math.ceil(filteredRecords.length / ITEMS_PER_PAGE);
  const paginatedRecords = filteredRecords.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE);

  const totalCollected = filteredRecords.reduce((a, r) => a + Number(r.actual_boundary || 0), 0);
  const totalTarget = filteredRecords.reduce((a, r) => a + Number(r.boundary_amount || 0), 0);
  const paidCount = filteredRecords.filter(r => ['paid', 'excess'].includes(r.status?.toLowerCase())).length;
  const shortCount = filteredRecords.filter(r => r.status?.toLowerCase() === 'shortage').length;

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

          {/* Month Selector Chips */}
          <div style={{ display: 'flex', gap: '8px', overflowX: 'auto', padding: '10px 20px 16px', alignItems: 'center', msOverflowStyle: 'none', scrollbarWidth: 'none', WebkitOverflowScrolling: 'touch' }}>
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

          {/* Summary Hero Card */}
          <div style={{ margin: '4px 20px 20px', padding: '24px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.shadow }}>
            <div style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '2px', marginBottom: '8px' }}>Total Collected</div>
            <div style={{ fontSize: '38px', fontWeight: '900', color: t.textPrimary, lineHeight: 1, marginBottom: '4px' }}>₱{totalCollected.toLocaleString()}</div>
            <div style={{ fontSize: '12px', color: t.textMuted, marginBottom: '20px' }}>of ₱{totalTarget.toLocaleString()} target</div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px' }}>
              {[
                { label: 'Records', value: records.length, icon: calendarOutline, color: '#3b82f6' },
                { label: 'Paid', value: paidCount, icon: checkmarkCircleOutline, color: '#22c55e' },
                { label: 'Short', value: shortCount, icon: alertCircleOutline, color: '#ef4444' }
              ].map((stat, i) => (
                <div key={i} style={{ padding: '12px', background: t.subtleBg, borderRadius: '12px', textAlign: 'center' }}>
                  <IonIcon icon={stat.icon} style={{ fontSize: '18px', color: stat.color }} />
                  <div style={{ fontSize: '20px', fontWeight: '800', color: t.textPrimary, margin: '4px 0 2px' }}>{stat.value}</div>
                  <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px' }}>{stat.label}</div>
                </div>
              ))}
            </div>
          </div>

          {/* Section Label */}
          <div style={{ padding: '0 20px', marginBottom: '12px', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <IonIcon icon={trendingUpOutline} style={{ fontSize: '16px', color: t.gold }} />
            <span style={{ fontSize: '13px', fontWeight: '800', color: t.gold, textTransform: 'uppercase', letterSpacing: '1.5px' }}>Recent Collections</span>
          </div>

          {/* List */}
          <div style={{ padding: '0 20px' }}>
            {loading ? (
              <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
                <IonSpinner name="crescent" color="warning" />
              </div>
            ) : filteredRecords.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '60px 20px' }}>
                <IonIcon icon={cashOutline} style={{ fontSize: '48px', color: '#1e293b' }} />
                <div style={{ color: '#475569', fontSize: '13px', marginTop: '12px' }}>No records found.</div>
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

                {/* Pagination Controls */}
                {totalPages > 1 && (
                  <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '16px', marginTop: '12px', padding: '10px' }}>
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
            )}
          </div>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default History;
