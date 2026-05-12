import React from 'react';
import { IonIcon } from '@ionic/react';
import { homeOutline, home, locateOutline, locate, chatbubbleOutline, chatbubble, settingsOutline, settings } from 'ionicons/icons';
import { useLocation, useHistory } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';
import { useState, useEffect } from 'react';
import axios from 'axios';
import { endpoints } from '../config/api';

const BottomNav: React.FC = () => {
  const location = useLocation();
  const history = useHistory();
  const { t, isDark } = useTheme();

  const tabs = [
    { name: 'Home', path: '/dashboard', iconOutline: homeOutline, iconSolid: home },
    { name: 'Tracking', path: '/tracking', iconOutline: locateOutline, iconSolid: locate },
    { name: 'Messages', path: '/support', iconOutline: chatbubbleOutline, iconSolid: chatbubble },
    { name: 'Settings', path: '/settings', iconOutline: settingsOutline, iconSolid: settings }
  ];

  const [unread, setUnread] = useState(0);

  useEffect(() => {
    const checkUnread = async () => {
      try {
        const response = await axios.get(endpoints.supportUnreadCount);
        if (response.data.success) {
           setUnread(response.data.count);
        }
      } catch (e) {
        // Silently fail if not logged in or offline
      }
    };
    checkUnread();
    // Check every 10 seconds to avoid hitting the server too hard but still stay responsive
    const interval = setInterval(checkUnread, 10000);
    return () => clearInterval(interval);
  }, []);

  return (
    <div style={{
      position: 'fixed',
      bottom: '20px',
      left: '50%',
      transform: 'translateX(-50%)',
      width: 'calc(100% - 40px)',
      maxWidth: '400px',
      background: isDark ? 'rgba(30, 41, 59, 0.95)' : 'rgba(255, 255, 255, 0.95)',
      backdropFilter: 'blur(10px)',
      WebkitBackdropFilter: 'blur(10px)',
      borderRadius: '30px',
      display: 'flex',
      justifyContent: 'space-around',
      alignItems: 'center',
      padding: '12px 10px',
      boxShadow: isDark ? '0 8px 32px rgba(0,0,0,0.4)' : '0 8px 32px rgba(0,0,0,0.1)',
      border: t.border,
      zIndex: 9999
    }}>
      {tabs.map((tab) => {
        const isActive = location.pathname === tab.path;
        return (
          <div 
            key={tab.name}
            onClick={() => history.push(tab.path)}
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              gap: '4px',
              cursor: 'pointer',
              flex: 1
            }}
          >
            <div style={{ position: 'relative', display: 'flex', justifyContent: 'center' }}>
              <IonIcon 
                icon={isActive ? tab.iconSolid : tab.iconOutline} 
                style={{ 
                  fontSize: '24px', 
                  color: isActive ? '#10b981' : (isDark ? '#94a3b8' : '#64748b'),
                  transition: 'all 0.3s ease'
                }} 
              />
              {tab.name === 'Messages' && unread > 0 && (
                <div style={{ 
                  position: 'absolute', 
                  top: '-4px', 
                  right: '-6px', 
                  background: '#ef4444', 
                  color: 'white', 
                  fontSize: '9px', 
                  fontWeight: '900', 
                  padding: '2px 5px', 
                  borderRadius: '10px', 
                  border: `2px solid ${isDark ? '#1e293b' : '#ffffff'}` 
                }}>
                  {unread > 9 ? '9+' : unread}
                </div>
              )}
            </div>
            <span style={{ 
              fontSize: '11px', 
              fontWeight: isActive ? '700' : '500', 
              color: isActive ? '#10b981' : (isDark ? '#94a3b8' : '#64748b'),
              transition: 'all 0.3s ease'
            }}>
              {tab.name}
            </span>
          </div>
        );
      })}
    </div>
  );
};

export default BottomNav;
