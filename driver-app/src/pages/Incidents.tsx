import React, { useEffect, useState } from 'react';
import { IonPage, IonContent, IonIcon, IonRefresher, IonRefresherContent, IonSpinner, IonHeader, IonToolbar, IonButtons, IonBackButton, IonTitle } from '@ionic/react';
import { 
  carOutline, 
  shieldOutline,
  warningOutline,
  documentTextOutline,
  cashOutline
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

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  danger: '#ef4444',
  warning: '#f59e0b',
  info: '#3b82f6',
};

const getSeverityStyles = (severity: string) => {
  const s = severity.toLowerCase();
  if (s === 'critical') return { color: g.danger, bg: 'rgba(239,68,68,0.12)' };
  if (s === 'high') return { color: '#fb923c', bg: 'rgba(251,146,60,0.12)' };
  if (s === 'medium') return { color: g.warning, bg: 'rgba(245,158,11,0.12)' };
  return { color: '#94a3b8', bg: 'rgba(148,163,184,0.12)' };
};

const Incidents: React.FC = () => {
  const { t } = useTheme();
  const [incidents, setIncidents] = useState<IncidentRecord[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchIncidents = async () => {
    try {
      const response = await axios.get(endpoints.driverIncidents);
      if (response.data.success) setIncidents(response.data.data);
    } catch (e) {
      console.error('Failed to fetch incidents', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchIncidents(); }, []);

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

        <div style={{ minHeight: '100%', background: t.bg, padding: '20px' }}>
          
          <div style={{ marginBottom: '24px' }}>
            <h1 style={{ color: t.textPrimary, fontSize: '28px', fontWeight: '900', margin: '0 0 8px' }}>My Incidents</h1>
            <p style={{ color: t.textSecondary, fontSize: '14px', margin: 0 }}>Review recorded behaviors and violations.</p>
          </div>

          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '60px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : incidents.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '80px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '24px' }}>
              <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(34,197,94,0.1)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 20px' }}>
                <IonIcon icon={shieldOutline} style={{ fontSize: '40px', color: '#22c55e' }} />
              </div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary, marginBottom: '8px' }}>No Incidents Found</div>
              <div style={{ color: t.textMuted, fontSize: '14px' }}>You have a clean record! Keep driving safely.</div>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              {incidents.map(incident => {
                const ss = getSeverityStyles(incident.severity);
                return (
                  <div key={incident.id} style={{ padding: '20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.cardShadow }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: ss.bg, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                          <IonIcon icon={warningOutline} style={{ fontSize: '22px', color: ss.color }} />
                        </div>
                        <div>
                          <div style={{ fontSize: '15px', fontWeight: '800', color: t.textPrimary }}>{incident.incident_type}</div>
                          <div style={{ fontSize: '11px', color: t.textMuted, marginTop: '2px' }}>
                            {new Date(incident.incident_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                          </div>
                        </div>
                      </div>
                      <div style={{ padding: '4px 10px', background: ss.bg, borderRadius: '10px', border: `1px solid ${ss.color}30` }}>
                        <span style={{ fontSize: '10px', fontWeight: '900', color: ss.color, textTransform: 'uppercase', letterSpacing: '1px' }}>{incident.severity}</span>
                      </div>
                    </div>

                    <div style={{ background: t.descBg, padding: '14px', borderRadius: '14px', marginBottom: '16px' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '8px' }}>
                        <IonIcon icon={documentTextOutline} style={{ fontSize: '14px', color: g.gold }} />
                        <span style={{ fontSize: '10px', fontWeight: '800', color: g.gold, textTransform: 'uppercase', letterSpacing: '1px' }}>Description</span>
                      </div>
                      <div style={{ fontSize: '13px', color: t.textSecondary, lineHeight: '1.5' }}>{incident.description}</div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                      <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: `1px solid ${t.inputBorder}` }}>
                        <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Unit Involved</div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                          <IonIcon icon={carOutline} style={{ fontSize: '14px', color: g.info }} />
                          <span style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>{incident.plate_number}</span>
                        </div>
                      </div>
                      <div style={{ padding: '12px', background: t.inputBg, borderRadius: '14px', border: `1px solid ${t.inputBorder}` }}>
                        <div style={{ fontSize: '9px', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>Charge Amount</div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
                          <IonIcon icon={cashOutline} style={{ fontSize: '14px', color: '#22c55e' }} />
                          <span style={{ fontSize: '13px', fontWeight: '900', color: '#22c55e' }}>₱{Number(incident.total_charge_to_driver).toLocaleString()}</span>
                        </div>
                      </div>
                    </div>

                    {incident.total_charge_to_driver > 0 && (
                      <div style={{ marginTop: '12px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: '12px', borderTop: t.border }}>
                        <div style={{ fontSize: '11px', color: t.textMuted }}>Status: <span style={{ fontWeight: '700', color: incident.charge_status === 'paid' ? '#22c55e' : g.warning }}>{incident.charge_status.toUpperCase()}</span></div>
                        <div style={{ fontSize: '11px', color: t.textMuted }}>Balance: <span style={{ fontWeight: '800', color: t.textPrimary }}>₱{Number(incident.remaining_balance).toLocaleString()}</span></div>
                      </div>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Incidents;
