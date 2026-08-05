import { Redirect, Route } from 'react-router-dom';
import { IonApp, IonRouterOutlet, setupIonicReact } from '@ionic/react';
import { IonReactRouter } from '@ionic/react-router';
import { Suspense, lazy, useEffect, useState } from 'react';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';
import { TutorialProvider } from './context/TutorialContext';
import TutorialOverlay from './components/TutorialOverlay';

// Global offline banner
const OfflineBanner: React.FC = () => {
  const [isOnline, setIsOnline] = useState(navigator.onLine);
  const [showRestored, setShowRestored] = useState(false);

  useEffect(() => {
    const handleOffline = () => {
      setIsOnline(false);
      setShowRestored(false);
    };
    const handleOnline = () => {
      setIsOnline(true);
      setShowRestored(true);
      setTimeout(() => setShowRestored(false), 3000);
    };
    window.addEventListener('offline', handleOffline);
    window.addEventListener('online', handleOnline);
    return () => {
      window.removeEventListener('offline', handleOffline);
      window.removeEventListener('online', handleOnline);
    };
  }, []);

  if (isOnline && !showRestored) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      zIndex: 99999,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      gap: '8px',
      padding: '10px 16px',
      background: isOnline ? '#16a34a' : '#dc2626',
      color: '#fff',
      fontSize: '13px',
      fontWeight: 600,
      letterSpacing: '0.3px',
      boxShadow: '0 2px 8px rgba(0,0,0,0.25)',
      transition: 'background 0.3s ease',
    }}>
      <span style={{ fontSize: '16px' }}>{isOnline ? '✅' : '📵'}</span>
      {isOnline ? 'Back online! Data is up to date.' : 'No Internet Connection — Showing cached data'}
    </div>
  );
};

// Lazy load pages for performance (Code Splitting)
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Vehicle = lazy(() => import('./pages/Vehicle'));
const Notifications = lazy(() => import('./pages/Notifications'));
const Tracking = lazy(() => import('./pages/Tracking'));
const History = lazy(() => import('./pages/History'));
const Support = lazy(() => import('./pages/Support'));
const Performance = lazy(() => import('./pages/Performance'));
const Settings = lazy(() => import('./pages/Settings'));
const Violations = lazy(() => import('./pages/Violations'));
const Accidents = lazy(() => import('./pages/Accidents'));
const Debts = lazy(() => import('./pages/Debts'));
const Incentives = lazy(() => import('./pages/Incentives'));
const Announcements = lazy(() => import('./pages/Announcements'));
const Terms = lazy(() => import('./pages/Terms'));

/* Core CSS required for Ionic components to work properly */
import '@ionic/react/css/core.css';

/* Basic CSS for apps built with Ionic */
import '@ionic/react/css/normalize.css';
import '@ionic/react/css/structure.css';
import '@ionic/react/css/typography.css';

/* Optional CSS utils that can be commented out */
import '@ionic/react/css/padding.css';
import '@ionic/react/css/float-elements.css';
import '@ionic/react/css/text-alignment.css';
import '@ionic/react/css/text-transformation.css';
import '@ionic/react/css/flex-utils.css';
import '@ionic/react/css/display.css';

/* Theme variables */
import './index.css';



const Welcome = lazy(() => import('./pages/Welcome'));

setupIonicReact();

const PrivateRoute: React.FC<{ component: React.FC; path: string; exact?: boolean }> = ({ component: Component, ...rest }) => {
  const { token, isLoading } = useAuth();

  if (isLoading) return null;

  return (
    <Route
      {...rest}
      render={() =>
        token ? <Component /> : <Redirect to="/login" />
      }
    />
  );
};

import BottomNav from './components/BottomNav';
import { useLocation } from 'react-router-dom';

const NavigationWrapper: React.FC = () => {
  const location = useLocation();
  const hiddenPages = ['/login', '/register', '/welcome', '/', '/support'];
  if (!hiddenPages.includes(location.pathname)) {
    return <BottomNav />;
  }
  return null;
};

const App: React.FC = () => {
  return (
    <IonApp>
      <ThemeProvider>
      <AuthProvider>
      <TutorialProvider>
        <IonReactRouter>
          <IonRouterOutlet>
            <Suspense fallback={null}>
              <Route exact path="/welcome">
                <Welcome />
              </Route>
              <Route exact path="/login">
                <Login />
              </Route>
              <Route exact path="/register">
                <Register />
              </Route>
              <PrivateRoute exact path="/dashboard" component={Dashboard} />

              <PrivateRoute exact path="/vehicle" component={Vehicle} />
              <PrivateRoute exact path="/notifications" component={Notifications} />
              <PrivateRoute exact path="/tracking" component={Tracking} />
              <PrivateRoute exact path="/history" component={History} />
              <PrivateRoute exact path="/violations" component={Violations} />
              <PrivateRoute exact path="/accidents" component={Accidents} />
              <PrivateRoute exact path="/debts" component={Debts} />
              <PrivateRoute exact path="/incentives" component={Incentives} />
              <PrivateRoute exact path="/support" component={Support} />
              <PrivateRoute exact path="/performance" component={Performance} />
              <PrivateRoute exact path="/settings" component={Settings} />
              <PrivateRoute exact path="/announcements" component={Announcements} />
              <Route exact path="/terms">
                <Terms />
              </Route>
              <Route exact path="/">
                <Redirect to="/welcome" />
              </Route>
            </Suspense>
          </IonRouterOutlet>
          <NavigationWrapper />
          <TutorialOverlay />
          <OfflineBanner />
      </IonReactRouter>
    </TutorialProvider>
    </AuthProvider>
      </ThemeProvider>
    </IonApp>
  );
};

export default App;
