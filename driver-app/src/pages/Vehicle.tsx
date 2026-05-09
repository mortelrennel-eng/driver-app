import { useState, useEffect } from 'react';
import type { FC } from 'react';
import { IonContent, IonPage, IonIcon, IonSpinner } from '@ionic/react';
import { arrowBackOutline, carSportOutline, constructOutline, documentTextOutline, speedometerOutline, checkmarkCircleOutline, alertCircleOutline } from 'ionicons/icons';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';

interface VehicleData {
  plate_number: string;
  model: string;
  brand: string;
  odo: number;
  maintenance_status: string;
  registration_date: string;
}

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  goldGrad: 'linear-gradient(135deg, #eab308, #f59e0b)',
};

const Vehicle: FC = () => {
  const history = useHistory();
  const [vehicle, setVehicle] = useState<VehicleData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // 1. Instant load from cache
    const cached = localStorage.getItem('cached_vehicle_data');
    if (cached) {
      try {
        setVehicle(JSON.parse(cached));
        setLoading(false); // Hide spinner if we have cache
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

  const maintenanceOk = vehicle?.maintenance_status === 'good' || vehicle?.maintenance_status === 'none';

  return (
    <IonPage>
      <IonContent fullscreen scrollY>
        <div style={{ minHeight: '100vh', background: g.bg, paddingBottom: '40px' }}>

          {/* Header */}
          <div style={{ padding: '16px 20px 12px', display: 'flex', alignItems: 'center', gap: '12px' }}>
            <button onClick={() => history.goBack()} style={{ background: 'rgba(255,255,255,0.06)', border: 'none', borderRadius: '12px', padding: '10px', cursor: 'pointer' }}>
              <IonIcon icon={arrowBackOutline} style={{ fontSize: '20px', color: '#94a3b8' }} />
            </button>
            <div>
              <div style={{ fontSize: '18px', fontWeight: '800', color: '#f8fafc' }}>Vehicle Info</div>
              <div style={{ fontSize: '11px', color: '#64748b' }}>Assigned unit details</div>
            </div>
          </div>

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
              <div style={{ margin: '4px 20px 20px', padding: '28px 20px', background: g.card, ...g.glass, border: g.border, borderRadius: '20px', boxShadow: '0 8px 32px rgba(0,0,0,0.4)', textAlign: 'center' }}>
                <div style={{ width: '80px', height: '80px', borderRadius: '20px', background: g.goldGrad, display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 16px', boxShadow: '0 8px 24px rgba(234,179,8,0.25)' }}>
                  <IonIcon icon={carSportOutline} style={{ fontSize: '40px', color: '#0a0e1a' }} />
                </div>
                <div style={{ fontSize: '28px', fontWeight: '900', color: '#f8fafc', letterSpacing: '-0.5px' }}>{vehicle.plate_number}</div>
                <div style={{ fontSize: '14px', color: '#94a3b8', marginTop: '4px' }}>{vehicle.brand} {vehicle.model}</div>
              </div>

              {/* Details */}
              <div style={{ padding: '0 20px', display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {[
                  {
                    label: 'Maintenance Status',
                    value: vehicle.maintenance_status || 'Good',
                    icon: constructOutline,
                    color: maintenanceOk ? '#22c55e' : '#ef4444',
                    badge: maintenanceOk ? checkmarkCircleOutline : alertCircleOutline
                  },
                  {
                    label: 'Odometer',
                    value: `${Number(vehicle.odo).toLocaleString()} km`,
                    icon: speedometerOutline,
                    color: '#3b82f6',
                    badge: null
                  },
                  {
                    label: 'Registration Date',
                    value: vehicle.registration_date ? new Date(vehicle.registration_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A',
                    icon: documentTextOutline,
                    color: '#8b5cf6',
                    badge: null
                  }
                ].map((item, i) => (
                  <div key={i} style={{ padding: '16px', background: g.card, ...g.glass, border: g.border, borderRadius: '16px', display: 'flex', alignItems: 'center', gap: '14px' }}>
                    <div style={{ width: '44px', height: '44px', borderRadius: '14px', background: `${item.color}15`, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                      <IonIcon icon={item.icon} style={{ fontSize: '22px', color: item.color }} />
                    </div>
                    <div style={{ flex: 1 }}>
                      <div style={{ fontSize: '10px', fontWeight: '700', color: '#64748b', textTransform: 'uppercase', letterSpacing: '1px', marginBottom: '4px' }}>{item.label}</div>
                      <div style={{ fontSize: '14px', fontWeight: '700', color: '#f8fafc', textTransform: 'capitalize' }}>{item.value}</div>
                    </div>
                    {item.badge && <IonIcon icon={item.badge} style={{ fontSize: '22px', color: item.color }} />}
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
