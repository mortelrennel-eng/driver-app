import {
  IonContent,
  IonHeader,
  IonPage,
  IonToolbar,
  IonTitle,
  IonButtons,
  IonBackButton,
  IonList,
  IonItem,
  IonLabel,
  IonNote,
  IonIcon,
  IonSegment,
  IonSegmentButton,
  IonSpinner,
  IonBadge
} from '@ionic/react';
import { cashOutline } from 'ionicons/icons';
import { useState, useEffect } from 'react';
import type { FC } from 'react';
import axios from 'axios';
import { endpoints } from '../config/api';

interface EarningRecord {
  id: number;
  date: string;
  boundary_amount: number;
  actual_boundary: number;
  status: string;
  shortage: number;
  excess: number;
  notes: string;
}

const Earnings: FC = () => {
  const [segment, setSegment] = useState<'daily' | 'monthly'>('daily');
  const [earnings, setEarnings] = useState<EarningRecord[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchEarnings = async () => {
    try {
      const response = await axios.get(endpoints.driverEarnings);
      if (response.data.success) {
        setEarnings(response.data.data);
      }
    } catch (e) {
      console.error('Failed to fetch earnings', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEarnings();
  }, []);

  const totalEarnings = earnings.reduce((acc, cur) => acc + Number(cur.actual_boundary), 0);

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar color="primary" style={{ '--background': '#1e3a8a' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle>Payment History</IonTitle>
        </IonToolbar>
        <IonToolbar color="primary" style={{ '--background': '#1e3a8a' }}>
          <IonSegment value={segment} onIonChange={(e) => setSegment(e.detail.value as any)} mode="ios" style={{ padding: '0 10px 10px' }}>
            <IonSegmentButton value="daily">
              <IonLabel>Daily Records</IonLabel>
            </IonSegmentButton>
            <IonSegmentButton value="monthly">
              <IonLabel>Summaries</IonLabel>
            </IonSegmentButton>
          </IonSegment>
        </IonToolbar>
      </IonHeader>

      <IonContent className="ion-padding" style={{ '--background': '#f4f5f8' }}>
        <div className="ion-text-center" style={{ background: 'white', padding: '30px 20px', borderRadius: '24px', boxShadow: '0 4px 15px rgba(0,0,0,0.05)', marginBottom: '20px' }}>
          <IonNote color="medium" style={{ fontSize: '12px', fontWeight: 'bold', textTransform: 'uppercase', letterSpacing: '1px' }}>Total Boundary Paid</IonNote>
          <h1 style={{ fontSize: '42px', fontWeight: '900', margin: '5px 0', color: '#1e3a8a' }}>
            ₱{totalEarnings.toLocaleString()}
          </h1>
          <IonBadge color="success" style={{ padding: '6px 12px', borderRadius: '8px' }}>Active Driver</IonBadge>
        </div>

        <h2 style={{ fontWeight: '800', color: '#1e3a8a', paddingLeft: '5px', marginBottom: '15px' }}>Recent Collections</h2>

        {loading ? (
          <div className="ion-text-center ion-padding">
            <IonSpinner name="crescent" />
          </div>
        ) : (
          <IonList className="ion-no-padding" style={{ background: 'transparent' }}>
            {earnings.map((record) => (
              <IonItem key={record.id} className="ion-margin-bottom" style={{ '--padding-start': '0', '--inner-padding-end': '0', '--background': 'transparent' }}>
                <div style={{ background: 'white', width: '100%', padding: '15px', borderRadius: '16px', border: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <div style={{ display: 'flex', gap: '12px', alignItems: 'center' }}>
                    <div style={{ background: '#eff6ff', padding: '10px', borderRadius: '12px' }}>
                      <IonIcon icon={cashOutline} color="primary" style={{ fontSize: '24px' }} />
                    </div>
                    <div>
                      <div style={{ fontWeight: 'bold', color: '#1e3a8a', fontSize: '16px' }}>
                        {new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                      </div>
                      <div style={{ fontSize: '12px', color: '#64748b', display: 'flex', gap: '8px' }}>
                        <span style={{ color: record.status === 'paid' ? '#2dd36f' : '#f59e0b', fontWeight: 'bold', textTransform: 'uppercase' }}>{record.status}</span>
                        {Number(record.shortage) > 0 && <span style={{ color: '#ef4444' }}>• Short: ₱{Number(record.shortage)}</span>}
                        {Number(record.excess) > 0 && <span style={{ color: '#2dd36f' }}>• Over: ₱{Number(record.excess)}</span>}
                      </div>
                    </div>
                  </div>
                  <div style={{ textAlign: 'right' }}>
                    <div style={{ fontWeight: '900', fontSize: '18px', color: '#1e3a8a' }}>
                      ₱{Number(record.actual_boundary).toLocaleString()}
                    </div>
                    <div style={{ fontSize: '10px', color: '#94a3b8' }}>Target: ₱{Number(record.boundary_amount).toLocaleString()}</div>
                  </div>
                </div>
              </IonItem>
            ))}
            
            {earnings.length === 0 && (
              <div className="ion-text-center ion-padding">
                <IonIcon icon={cashOutline} style={{ fontSize: '48px', opacity: 0.1 }} />
                <p style={{ color: '#999', fontSize: '13px' }}>No transactions found.</p>
              </div>
            )}
          </IonList>
        )}
      </IonContent>
    </IonPage>
  );
};

export default Earnings;
