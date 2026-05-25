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
  timeOutline,
  cameraOutline,
  attachOutline
} from 'ionicons/icons';
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';
import axios from 'axios';
import { endpoints, API_BASE_URL } from '../config/api';
import { useTheme } from '../context/ThemeContext';

interface SupportMessage {
  id: number;
  sender_type: 'driver' | 'admin';
  message: string;
  created_at: string;
  sender_name?: string;
  sender_role?: string;
  attachment?: string;
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
  const { t, isDark } = useTheme();
  const [messages, setMessages] = useState<SupportMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [showToast, setShowToast] = useState(false);
  const [toastMsg, setToastMsg] = useState('');
  
  // Chat State
  const [newMessage, setNewMessage] = useState('');
  const [selectedImage, setSelectedImage] = useState<File | null>(null);
  const [imagePreview, setImagePreview] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);
  
  const contentRef = useRef<HTMLIonContentElement>(null);
  const lastMessageCount = useRef(0);

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
        const parsed = JSON.parse(cached);
        setMessages(parsed);
        lastMessageCount.current = parsed.length;
        setLoading(false);
      } catch (e) {}
    }

    fetchMessages();
    // Poll for new messages every 2 seconds (slightly slower to be less aggressive)
    const interval = setInterval(fetchMessages, 2000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (contentRef.current && messages.length > lastMessageCount.current) {
      // Only auto-scroll if message count increased
      contentRef.current.scrollToBottom(300);
      lastMessageCount.current = messages.length;
    }
  }, [messages]);

  const takePhoto = async () => {
    try {
      const image = await Camera.getPhoto({
        quality: 90,
        allowEditing: false,
        resultType: CameraResultType.Base64,
        source: CameraSource.Camera
      });

      if (image.base64String) {
        const blob = await fetch(`data:image/${image.format};base64,${image.base64String}`).then(res => res.blob());
        const file = new File([blob], `photo_${Date.now()}.${image.format}`, { type: `image/${image.format}` });
        setSelectedImage(file);
        setImagePreview(`data:image/${image.format};base64,${image.base64String}`);
      }
    } catch (e) {
      console.error('Camera cancelled or failed', e);
    }
  };

  const handleImagePick = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setToastMsg('Image is too large (max 5MB)');
        setShowToast(true);
        return;
      }
      setSelectedImage(file);
      const reader = new FileReader();
      reader.onloadend = () => setImagePreview(reader.result as string);
      reader.readAsDataURL(file);
    }
  };

  const handleSendMessage = async (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    if (!newMessage.trim() && !selectedImage) return;

    setSubmitting(true);
    try {
      const formData = new FormData();
      if (newMessage.trim()) formData.append('message', newMessage);
      if (selectedImage) formData.append('image', selectedImage);

      const response = await axios.post(endpoints.sendSupportMessage, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      if (response.data.success) {
        setNewMessage('');
        setSelectedImage(null);
        setImagePreview(null);
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
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" />
          </IonButtons>
          <IonTitle style={{ fontWeight: '800', fontSize: '18px' }}>Support Chat</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent ref={contentRef} fullscreen style={{ '--background': t.bg }}>
        <IonRefresher slot="fixed" onIonRefresh={doRefresh}>
          <IonRefresherContent></IonRefresherContent>
        </IonRefresher>

        <div style={{ background: t.bg, minHeight: '100%', padding: '20px 16px 80px' }}>
          
          {loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: '40px' }}>
              <IonSpinner color="warning" />
            </div>
          ) : messages.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '60px 20px', background: t.subtleBg, borderRadius: '24px', border: `1px dashed ${isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)'}` }}>
              <IonIcon icon={chatbubbleEllipsesOutline} style={{ fontSize: '64px', color: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)', marginBottom: '16px' }} />
              <h3 style={{ color: t.textPrimary, fontSize: '18px', fontWeight: '700', margin: '0 0 8px' }}>Start a Conversation</h3>
              <p style={{ color: t.textMuted, fontSize: '14px' }}>Send a message below to chat with our support team.</p>
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
                    
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '2px', maxWidth: '75%' }}>
                      {!isDriver && (
                        <div style={{ fontSize: '9px', fontWeight: '800', color: t.gold, marginLeft: '4px', textTransform: 'uppercase', letterSpacing: '0.5px' }}>
                          {msg.sender_role ? `${msg.sender_role.replace('_', ' ')}: ` : ''}{msg.sender_name || 'Support'}
                        </div>
                      )}
                      <div style={{ 
                        padding: '12px 16px', 
                        background: isDriver ? t.gold : t.subtleBg,
                        borderRadius: isDriver ? '18px 18px 2px 18px' : '18px 18px 18px 2px',
                        color: isDriver ? (isDark ? '#000' : '#fff') : t.textPrimary,
                        fontSize: '14px',
                        fontWeight: isDriver ? '700' : '500',
                        boxShadow: isDriver ? '0 4px 12px rgba(234,179,8,0.2)' : 'none',
                        position: 'relative'
                      }}>
                        {msg.attachment && (
                          <div style={{ marginBottom: '8px', borderRadius: '12px', overflow: 'hidden', border: isDriver ? '1px solid rgba(0,0,0,0.1)' : t.borderSubtle }}>
                            <img 
                              src={`${API_BASE_URL.replace('/api', '')}/${msg.attachment}`} 
                              alt="attachment" 
                              style={{ width: '100%', maxHeight: '250px', objectFit: 'cover', display: 'block' }} 
                            />
                          </div>
                        )}
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
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </IonContent>

      <IonFooter className="ion-no-border" style={{ background: t.bg }}>
        {/* Image Preview Area */}
        {imagePreview && (
          <div style={{ padding: '10px 16px', background: t.subtleBg, borderTop: t.borderSubtle, display: 'flex', alignItems: 'center', gap: '12px' }}>
            <div style={{ position: 'relative', width: '60px', height: '60px' }}>
              <img src={imagePreview} style={{ width: '100%', height: '100%', borderRadius: '12px', objectFit: 'cover' }} />
              <button onClick={() => { setSelectedImage(null); setImagePreview(null); }} style={{ position: 'absolute', top: '-6px', right: '-6px', width: '20px', height: '20px', borderRadius: '50%', background: '#ef4444', color: '#fff', border: 'none', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '12px' }}>×</button>
            </div>
            <div style={{ fontSize: '12px', color: t.textSecondary }}>Image attached</div>
          </div>
        )}

        <div style={{ padding: '12px 16px calc(env(safe-area-inset-bottom) + 12px)', background: t.headerBg, borderTop: t.borderSubtle }}>
          <form onSubmit={handleSendMessage} style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
            <input 
              type="file" 
              ref={fileInputRef} 
              style={{ display: 'none' }} 
              accept="image/*" 
              onChange={handleImagePick} 
            />
            <button 
              type="button" 
              onClick={takePhoto}
              style={{ background: t.subtleBg, border: t.borderSubtle, borderRadius: '14px', width: '44px', height: '44px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: t.gold }}
            >
              <IonIcon icon={cameraOutline} style={{ fontSize: '20px' }} />
            </button>

            <button 
              type="button" 
              onClick={() => fileInputRef.current?.click()}
              style={{ background: t.subtleBg, border: t.borderSubtle, borderRadius: '14px', width: '44px', height: '44px', display: 'flex', alignItems: 'center', justifyContent: 'center', color: t.textSecondary }}
            >
              <IonIcon icon={attachOutline} style={{ fontSize: '20px' }} />
            </button>

            <div style={{ flex: 1, background: t.subtleBg, borderRadius: '14px', padding: '2px 12px', border: t.borderSubtle }}>
              <input 
                value={newMessage}
                onChange={(e) => setNewMessage(e.target.value)}
                placeholder="Type a message..."
                style={{ 
                  width: '100%', 
                  background: 'none', 
                  border: 'none', 
                  padding: '10px 0', 
                  color: t.textPrimary, 
                  fontSize: '14px',
                  outline: 'none'
                }}
              />
            </div>
            <IonButton 
              type="submit"
              disabled={submitting || (!newMessage.trim() && !selectedImage)}
              style={{ '--border-radius': '14px', '--background': t.gold, '--color': '#000', margin: 0, height: '44px', width: '44px' }}
            >
              {submitting ? <IonSpinner name="crescent" style={{ width: '20px', height: '20px' }} /> : <IonIcon icon={sendOutline} />}
            </IonButton>
          </form>
        </div>
      </IonFooter>

      <IonToast 
        isOpen={showToast} 
        message={toastMsg} 
        duration={3000} 
        onDidDismiss={() => setShowToast(false)}
        color="danger"
      />
    </IonPage>
  );
};

export default Support;
