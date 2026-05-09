import React, { useEffect, useState } from 'react';
import { IonPage, IonContent } from '@ionic/react';
import { useHistory } from 'react-router-dom';

const Welcome: React.FC = () => {
  const history = useHistory();
  const [quote, setQuote] = useState('');

  const quotes = [
    "Ang tunay na driver, maingat sa biyahe, maingat din sa pamilya.",
    "Road to success is always under construction. Drive safe!",
    "Bawat kilometro, may pangarap na tinutupad.",
    "Sa kalsada, pasensya ang puhunan, kaligtasan ang balik.",
    "Drive with a purpose, return with a smile.",
    "Disiplina sa manibela, biyaya sa bulsa.",
    "Your family is waiting for you. Drive carefully.",
    "Focus on the road, your goals are ahead.",
    "Small progress is still progress. Keep going, driver!",
    "Success is a journey, not a destination. Enjoy the ride."
  ];

  useEffect(() => {
    setQuote(quotes[Math.floor(Math.random() * quotes.length)]);
    
    // Auto redirect after 3 seconds
    const timer = setTimeout(() => {
      const token = localStorage.getItem('auth_token');
      if (token) {
        history.replace('/dashboard');
      } else {
        history.replace('/login');
      }
    }, 3500);

    return () => clearTimeout(timer);
  }, [history]);

  return (
    <IonPage>
      <IonContent fullscreen>
        <div style={{
          height: '100%',
          background: '#0a0e1a',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '40px',
          textAlign: 'center'
        }}>
          {/* Animated Logo */}
          <div style={{
            marginBottom: '40px',
            animation: 'pulse 2s infinite ease-in-out'
          }}>
             <img 
               src="/assets/logo.png" 
               alt="Logo" 
               style={{ width: '120px', height: 'auto', borderRadius: '24px', boxShadow: '0 0 30px rgba(234, 179, 8, 0.2)' }}
               onError={(e) => {
                 // Fallback if logo not found
                 e.currentTarget.src = 'https://eurotaxisystem.site/image/logo.png';
               }}
             />
          </div>

          <div style={{ maxWidth: '300px' }}>
            <p style={{
              color: '#eab308',
              fontSize: '11px',
              fontWeight: '800',
              textTransform: 'uppercase',
              letterSpacing: '2px',
              marginBottom: '16px',
              opacity: 0.8
            }}>Hati ng Karunungan</p>
            
            <h2 style={{
              color: '#fff',
              fontSize: '22px',
              fontWeight: '700',
              lineHeight: '1.4',
              margin: '0 0 24px',
              fontStyle: 'italic'
            }}>"{quote}"</h2>
            
            <div style={{
              width: '40px',
              height: '3px',
              background: '#eab308',
              margin: '0 auto',
              borderRadius: '2px',
              animation: 'expand 3s ease-in-out forwards'
            }}></div>
          </div>

          <style>{`
            @keyframes pulse {
              0% { transform: scale(1); opacity: 0.8; }
              50% { transform: scale(1.05); opacity: 1; }
              100% { transform: scale(1); opacity: 0.8; }
            }
            @keyframes expand {
              0% { width: 0; }
              100% { width: 100px; }
            }
          `}</style>
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Welcome;
