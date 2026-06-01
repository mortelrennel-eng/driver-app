import { useState, useEffect } from 'react';
import type { FC } from 'react';
import { IonContent, IonPage, IonIcon, IonSpinner, IonHeader, IonToolbar } from '@ionic/react';
import { arrowBackOutline, carSportOutline } from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useTheme } from '../context/ThemeContext';

interface VehicleData {
  plate_number: string;
  model: string;
  brand: string;
  year: number;
  odo: number;
  maintenance_status: string;
  registration_date: string;
  license_id: string;
}



const Vehicle: FC = () => {
  const history = useHistory();
  const { t } = useTheme();
  const [vehicle, setVehicle] = useState<VehicleData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // 1. Instant load from cache
    const cached = localStorage.getItem('cached_vehicle_data');
    if (cached) {
      try {
        const parsed = JSON.parse(cached);
        // Force refresh if brand is missing or unknown
        if (!parsed.brand || parsed.brand === 'Unknown' || !parsed.year || parsed.year === 0) {
            localStorage.removeItem('cached_vehicle_data');
        } else {
            setVehicle(parsed);
            setLoading(false);
        }
      } catch (e) {}
    }

    // 2. Fetch fresh data in background
    axios.get(endpoints.driverVehicle)
      .then(r => { 
        if (r.data.success) {
          setVehicle(r.data.data); 
          localStorage.setItem('cached_vehicle_data', JSON.stringify(r.data.data));
        }
      })
      .catch(e => console.error('Failed to fetch vehicle', e))
      .finally(() => setLoading(false));
  }, []);


  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.bg, '--padding-top': '8px', '--padding-bottom': '4px' }}>
          <div style={{ padding: '8px 20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: t.backBtnBg, border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: t.backBtnColor }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: t.textPrimary }}>Vehicle Info</div>
              <div style={{ fontSize: '11px', color: t.textMuted }}>Assigned unit details</div>
            </div>
          </div>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen scrollY>
        <div style={{ minHeight: '100vh', background: t.bg, paddingBottom: '40px' }}>

          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '80px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : !vehicle ? (
            <div style={{ margin: '60px 20px', textAlign: 'center' }}>
              <div style={{ width: '80px', height: '80px', borderRadius: '50%', background: 'rgba(255,255,255,0.04)', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px' }}>
                <IonIcon icon={carSportOutline} style={{ fontSize: '36px', color: '#334155' }} />
              </div>
              <div style={{ fontSize: '15px', fontWeight: '600', color: '#475569' }}>No vehicle assigned</div>
              <div style={{ fontSize: '12px', color: '#334155', marginTop: '6px' }}>Contact dispatch for assignment.</div>
            </div>
          ) : (
            <>
              {/* ── Vehicle Hero Card ── */}
              <div style={{
                margin: '4px 20px 20px', padding: '28px 20px 24px',
                background: t.card, ...t.glass, border: t.border,
                borderRadius: '24px', boxShadow: t.shadow, textAlign: 'center'
              }}>
                {/* Icon */}
                <div style={{
                  width: '80px', height: '80px', borderRadius: '22px',
                  background: t.goldGrad,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  margin: '0 auto 16px',
                  boxShadow: '0 8px 24px rgba(234,179,8,0.28)'
                }}>
                  <IonIcon icon={carSportOutline} style={{ fontSize: '40px', color: t.textInverse }} />
                </div>

                {/* Plate */}
                <div style={{ fontSize: '30px', fontWeight: '900', color: t.textPrimary, letterSpacing: '-0.5px', lineHeight: 1 }}>
                  {vehicle.plate_number}
                </div>

                {/* Make + Model */}
                <div style={{ fontSize: '15px', color: t.textSecondary, marginTop: '6px', fontWeight: '600' }}>
                  {[vehicle.brand && vehicle.brand !== 'Unknown' ? vehicle.brand : '', vehicle.model].filter(Boolean).join(' ')}
                </div>

                {/* Status badge */}
                <div style={{ marginTop: '14px', display: 'flex', justifyContent: 'center' }}>
                  <span style={{
                    fontSize: '11px', fontWeight: '900',
                    textTransform: 'uppercase', letterSpacing: '1px',
                    padding: '5px 14px', borderRadius: '99px',
                    background: vehicle.maintenance_status === 'active'
                      ? 'rgba(34,197,94,0.15)' : 'rgba(100,116,139,0.15)',
                    color: vehicle.maintenance_status === 'active' ? '#22c55e' : '#64748b',
                    border: `1px solid ${vehicle.maintenance_status === 'active'
                      ? 'rgba(34,197,94,0.3)' : 'rgba(100,116,139,0.25)'}`,
                  }}>
                    {(vehicle.maintenance_status || 'Unknown').toUpperCase()}
                  </span>
                </div>
              </div>

              {/* ── Details Grid ── */}
              <div style={{ padding: '0 20px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                {[
                  {
                    label: 'Year',
                    value: vehicle.year && vehicle.year > 0
                      ? vehicle.year
                      : (vehicle.registration_date ? new Date(vehicle.registration_date).getFullYear() : '—'),
                  },
                  {
                    label: 'License ID',
                    value: vehicle.license_id || '—',
                  },
                  {
                    label: 'Odometer',
                    value: `${Number(vehicle.odo).toLocaleString()} km`,
                    wide: true,
                  },
                ].map((item, i) => (
                  <div
                    key={i}
                    style={{
                      gridColumn: (item as any).wide ? 'span 2' : 'span 1',
                      padding: '16px 18px',
                      background: t.card,
                      ...t.glass,
                      border: t.border,
                      borderRadius: '16px',
                    }}
                  >
                    <div style={{
                      fontSize: '10px', fontWeight: '800', color: t.textMuted,
                      textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '6px'
                    }}>
                      {item.label}
                    </div>
                    <div style={{ fontSize: '16px', fontWeight: '900', color: t.textPrimary }}>
                      {item.value}
                    </div>
                  </div>
                ))}
              </div>
            </>

          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Vehicle;
