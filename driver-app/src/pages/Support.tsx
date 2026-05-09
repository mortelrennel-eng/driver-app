import { useEffect, useState, useRef } from 'react';
import type { FC } from 'react';
import {
  IonContent,
  IonPage,
  IonIcon,
  IonHeader,
  IonToolbar,
  IonButtons,
  IonBackButton,
  IonTitle,
  IonRefresher,
  IonRefresherContent,
  IonButton,
  IonToast,
  IonSpinner,
  IonFooter
} from '@ionic/react';
import { 
  sendOutline, 
  chatbubbleEllipsesOutline,
  personOutline,
  timeOutline
} from 'ionicons/icons';
import axios from 'axios';
import { endpoints } from '../config/api';

interface SupportMessage {
  id: number;
  sender_type: 'driver' | 'admin';
  message: string;
  created_at: string;
}

const g = {
  bg: '#0a0e1a',
  card: 'linear-gradient(145deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.85))',
  glass: { backdropFilter: 'blur(16px)', WebkitBackdropFilter: 'blur(16px)' } as React.CSSProperties,
  border: '1px solid rgba(255,255,255,0.06)',
  gold: '#eab308',
  radius: '20px',
};

const Support: FC = () => {
  const [messages, setMessages] = useState<SupportMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [showToast, setShowToast] = useState(false);
  const [toastMsg, setToastMsg] = useState('');
  
  // Chat State
  const [newMessage, setNewMessage] = useState('');
  const [submitting, setSubmitting] = useState(false);
  
  const contentRef = useRef<HTMLIonContentElement>(null);

  const fetchMessages = async () => {
    try {
      const response = await axios.get(endpoints.supportMessages);
      if (response.data.success) {
        setMessages(response.data.messages);
        localStorage.setItem('cached_support_messages', JSON.stringify(response.data.messages));
      }
    } catch (e) {
      console.error('Failed to fetch messages', e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    // 1. Instant load from cache
    const cached = localStorage.getItem('cached_support_messages');
    if (cached) {
      try {
        setMessages(JSON.parse(cached));
        setLoading(false);
      } catch (e) {}
    }

    fetchMessages();
    // Poll for new messages every 1 second
    const interval = setInterval(fetchMessages, 1000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (contentRef.current) {
      contentRef.current.scrollToBottom(300);
    }
  }, [messages]);

  const handleSendMessage = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!newMessage.trim()) return;

    setSubmitting(true);
    try {
      const response = await axios.post(endpoints.sendSupportMessage, {
        message: newMessage
      });
      if (response.data.success) {
        setNewMessage('');
        fetchMessages();
      }
    } catch (e) {
      setToastMsg('Failed to send message.');
      setShowToast(true);
    } finally {
      setSubmitting(false);
    }
  };

  const doRefresh = (event: CustomEvent) => {
    fetchMessages().then(() => event.detail.complete());
  };

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': g.bg, '--color': '#fff' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Support Chat</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent ref={contentRef} fullscreen style={{ '--background': g.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ background: g.bg, minHeight: '100%', padding: '20px 16px 80px' }}>
          
          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : messages.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '60px 20px', background: 'rgba(255,255,255,0.02)', borderRadius: '24px', border: '1px dashed rgba(255,255,255,0.1)' }}>
              <IonIcon icon={chatbubbleEllipsesOutline} style={{ fontSize: '64px', color: 'rgba(255,255,255,0.1)', marginBottom: '16px' }} />
              <h3 style={{ color: '#f8fafc', fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>Start a Conversation</h3>
              <p style={{ color: '#64748b', fontSize: '14px' }}>Send a message below to chat with our support team.</p>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              {messages.map((msg, index) => {
                const isDriver = msg.sender_type === 'driver';
                const showAvatar = index === 0 || messages[index-1].sender_type !== msg.sender_type;

                return (
                  <div key={msg.id} style={{ display: 'flex', justifyContent: isDriver ? 'flex-end' : 'flex-start', alignItems: 'flex-end', gap: '8px' }}>
                    {!isDriver && showAvatar && (
                      <div style={{ width: '28px', height: '28px', borderRadius: '8px', background: g.gold, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                        <IonIcon icon={personOutline} style={{ fontSize: '14px', color: '#000' }} />
                      </div>
                    )}
                    {!isDriver && !showAvatar && <div style={{ width: '28px' }} />}
                    
                    <div style={{ 
                      maxWidth: '75%', 
                      padding: '12px 16px', 
                      background: isDriver ? g.gold : 'rgba(255,255,255,0.06)',
                      borderRadius: isDriver ? '18px 18px 2px 18px' : '18px 18px 18px 2px',
                      color: isDriver ? '#000' : '#f8fafc',
                      fontSize: '14px',
                      fontWeight: isDriver ? '700' : '500',
                      boxShadow: isDriver ? '0 4px 12px rgba(234,179,8,0.2)' : 'none',
                      position: 'relative'
                    }}>
                      {msg.message}
                      <div style={{ 
                        fontSize: '9px', 
                        opacity: 0.5, 
                        marginTop: '4px', 
                        textAlign: 'right',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'flex-end',
                        gap: '2px'
                      }}>
                        <IonIcon icon={timeOutline} style={{ fontSize: '8px' }} />
                        {new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>

        <IonToast
          isOpen={showToast}
          onDidDismiss={() => setShowToast(false)}
          message={toastMsg}
          duration={3000}
          position="bottom"
          style={{ '--background': '#1e293b', '--color': '#f8fafc' }}
        />
      </IonContent>

      <IonFooter className="ion-no-border" style={{ background: g.bg }}>
        <div style={{ padding: '12px 16px', background: 'rgba(15, 23, 42, 0.95)', backdropFilter: 'blur(10px)', borderTop: '1px solid rgba(255,255,255,0.05)' }}>
          <form onSubmit={handleSendMessage} style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
            <div style={{ flex: 1, background: 'rgba(255,255,255,0.05)', borderRadius: '24px', padding: '4px 16px', border: '1px solid rgba(255,255,255,0.1)' }}>
              <input 
                type="text" 
                value={newMessage}
                onChange={e => setNewMessage(e.target.value)}
                placeholder="Type your message..."
                style={{ width: '100%', background: 'none', border: 'none', padding: '10px 0', color: '#fff', fontSize: '14px', outline: 'none' }}
              />
            </div>
            <IonButton 
              type="submit" 
              disabled={submitting || !newMessage.trim()}
              style={{ '--background': g.gold, '--color': '#000', '--border-radius': '50%', width: '44px', height: '44px', '--padding-start': '0', '--padding-end': '0' }}
            >
              {submitting ? <IonSpinner name="crescent" style={{ width: '20px', height: '20px' }} /> : <IonIcon icon={sendOutline} />}
            </IonButton>
          </form>
        </div>
      </IonFooter>
    </IonPage>
  );
};

export default Support;
