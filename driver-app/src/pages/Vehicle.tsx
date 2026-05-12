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
  const { t, isDark } = useTheme();
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
              {/* Vehicle Hero Card */}
              <div style={{ margin: '4px 20px 20px', padding: '28px 20px', background: t.card, ...t.glass, border: t.border, borderRadius: '20px', boxShadow: t.shadow, textAlign: 'center' }}>
                <div style={{ width: '80px', height: '80px', borderRadius: '20px', background: t.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px', boxShadow: '0 8px 24px rgba(234,179,8,0.25)' }}>
                  <IonIcon icon={carSportOutline} style={{ fontSize: '40px', color: t.textInverse }} />
                </div>
                <div style={{ fontSize: '28px', fontWeight: '900', color: t.textPrimary, letterSpacing: '-0.5px' }}>{vehicle.plate_number}</div>
                <div style={{ fontSize: '14px', color: t.textSecondary, marginTop: '4px' }}>
                  {vehicle.brand && vehicle.brand !== 'Unknown' ? vehicle.brand : ''} {vehicle.model}
                </div>
              </div>

              {/* Details */}
              <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {[
                  {
                    label: 'Plate Number',
                    value: vehicle.plate_number,
                    badge: true
                  },
                  {
                    label: 'Vehicle',
                    value: `${vehicle.brand && vehicle.brand !== 'Unknown' ? vehicle.brand : ''} ${vehicle.model || ''}`.trim() || 'N/A',
                    badge: false
                  },
                  {
                    label: 'Year',
                    value: vehicle.year && vehicle.year > 0 ? vehicle.year : (vehicle.registration_date ? new Date(vehicle.registration_date).getFullYear() : 'N/A'),
                    badge: false
                  },
                  {
                    label: 'Status',
                    value: (vehicle.maintenance_status || 'N/A').toUpperCase(),
                    badge: true,
                    color: vehicle.maintenance_status === 'active' ? '#22c55e' : '#64748b'
                  },
                  {
                    label: 'License ID',
                    value: vehicle.license_id || 'N/A',
                    badge: false
                  },
                  {
                    label: 'Odometer',
                    value: `${Number(vehicle.odo).toLocaleString()} km`,
                    badge: false
                  }
                ].map((item, i) => (
                  <div key={i} style={{ 
                    padding: '18px 20px', 
                    background: t.card, 
                    ...t.glass, 
                    border: t.border, 
                    borderRadius: '16px', 
                    display: 'flex', 
                    justifyContent: 'space-between', 
                    alignItems: 'center' 
                  }}>
                    <div style={{ fontSize: '11px', fontWeight: '800', color: t.textMuted, textTransform: 'uppercase', letterSpacing: '1px' }}>{item.label}</div>
                    <div style={{ 
                      fontSize: '15px', 
                      fontWeight: '900', 
                      color: item.color || t.textPrimary,
                      padding: item.badge ? '4px 12px' : '0',
                      background: item.badge ? (isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)') : 'none',
                      borderRadius: item.badge ? '8px' : '0',
                      textTransform: item.badge ? 'uppercase' : 'none'
                    }}>
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
