import {
  IonContent,
  IonHeader,
  IonPage,
  IonTitle,
  IonToolbar,
  IonButton,
  IonButtons,
  IonIcon,
  IonSpinner,
  useIonRouter,
  IonRefresher,
  IonRefresherContent
} from '@ionic/react';
import { arrowBackOutline, documentTextOutline } from 'ionicons/icons';
import React, { useState, useEffect } from 'react';
import { endpoints } from '../config/api';
import { cachedGet } from '../utils/cachedGet';
import { useTheme } from '../context/ThemeContext';

const Terms: React.FC = () => {
  const ionRouter = useIonRouter();
  const { t, isDark } = useTheme();
  
  const [images, setImages] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchImages = async () => {
    try {
      const response = await cachedGet(endpoints.termsImages);
      if (response.data.success) {
        setImages(response.data.images || []);
      }
    } catch (error) {
      console.error('Error fetching terms images:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchImages();
  }, []);

  const doRefresh = async (event: CustomEvent) => {
    await fetchImages();
    event.detail.complete();
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonButton color="warning" onClick={() => ionRouter.back()}>
              <IonIcon icon={arrowBackOutline} />
            </IonButton>
          </IonButtons>
          <IonTitle style={{ fontWeight: 'bold' }}>Terms & Conditions</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent />
        </IonRefresher>

        <div style={{
          minHeight: '100%',
          background: isDark ? 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)' : 'linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%)',
          padding: '16px 20px 100px',
        }}>
          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '60vh' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : images.length === 0 ? (
            <div style={{ 
              textAlign: 'center', 
              padding: '60px 20px',
              background: t.card,
              borderRadius: '24px',
              border: t.border,
              marginTop: '20px'
            }}>
              <div style={{ 
                width: '64px', height: '64px', borderRadius: '50%', 
                background: t.goldBg, color: t.gold,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                margin: '0 auto 16px', fontSize: '32px'
              }}>
                <IonIcon icon={documentTextOutline} />
              </div>
              <h2 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: 'bold', margin: '0 0 8px' }}>
                No Terms Available
              </h2>
              <p style={{ color: t.textSecondary, fontSize: '14px', margin: 0 }}>
                There are currently no terms and conditions documents uploaded.
              </p>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px', marginTop: '8px' }}>
              {images.map((imgUrl, index) => (
                <div key={index} style={{
                  background: t.card,
                  borderRadius: '16px',
                  overflow: 'hidden',
                  border: t.border,
                  boxShadow: t.cardShadow
                }}>
                  <img 
                    src={imgUrl} 
                    alt={`Terms Document ${index + 1}`} 
                    style={{ 
                      width: '100%', 
                      height: 'auto', 
                      display: 'block' 
                    }} 
                    onError={(e) => {
                      (e.target as HTMLImageElement).style.display = 'none';
                    }}
                  />
                </div>
              ))}
            </div>
          )}
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Terms;
