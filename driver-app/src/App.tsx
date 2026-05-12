import { Redirect, Route } from 'react-router-dom';
import { IonApp, IonRouterOutlet, setupIonicReact } from '@ionic/react';
import { IonReactRouter } from '@ionic/react-router';
import { Suspense, lazy } from 'react';
import { AuthProvider, useAuth } from './context/AuthContext';
import { ThemeProvider } from './context/ThemeContext';

// Lazy load pages for performance (Code Splitting)
const Login = lazy(() => import('./pages/Login'));
const Register = lazy(() => import('./pages/Register'));
const Dashboard = lazy(() => import('./pages/Dashboard'));
const Vehicle = lazy(() => import('./pages/Vehicle'));
const Notifications = lazy(() => import('./pages/Notifications'));
const Tracking = lazy(() => import('./pages/Tracking'));
const History = lazy(() => import('./pages/History'));
const Charges = lazy(() => import('./pages/Charges'));
const Support = lazy(() => import('./pages/Support'));
const Performance = lazy(() => import('./pages/Performance'));
const Settings = lazy(() => import('./pages/Settings'));
const Incidents = lazy(() => import('./pages/Incidents'));

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
              <PrivateRoute exact path="/charges" component={Charges} />
              <PrivateRoute exact path="/support" component={Support} />
              <PrivateRoute exact path="/performance" component={Performance} />
              <PrivateRoute exact path="/settings" component={Settings} />
              <PrivateRoute exact path="/incidents" component={Incidents} />
              <Route exact path="/">
                <Redirect to="/welcome" />
              </Route>
            </Suspense>
          </IonRouterOutlet>
          <NavigationWrapper />
      </IonReactRouter>
    </AuthProvider>
      </ThemeProvider>
  </IonApp>
  );
};

export default App;
