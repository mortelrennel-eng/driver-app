import { useState, useEffect, useRef } from 'react';
import type { FC, FormEvent } from 'react';
import {
  IonContent,
  IonPage,
  IonInput,
  IonButton,
  IonIcon,
  IonSpinner,
  IonToast,
} from '@ionic/react';
import {
  mailOutline,
  lockClosedOutline,
  keyOutline,
  carSportOutline,
  callOutline,
  arrowBackOutline,
  refreshOutline,
  eyeOutline,
  eyeOffOutline,
} from 'ionicons/icons';
import { useAuth } from '../context/AuthContext';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';

type Screen = 'login' | 'mfa' | 'forgot_phone' | 'forgot_otp' | 'forgot_reset';

const inputStyle = {
  '--padding-start': '48px',
  '--padding-end': '16px',
  '--color': '#f8fafc',
  '--placeholder-color': '#475569',
  '--background': 'transparent',
  height: '52px',
  fontSize: '15px',
} as any;

const iconStyle = (extra?: any) => ({
  position: 'absolute' as const,
  left: '16px',
  top: '50%',
  transform: 'translateY(-50%)',
  fontSize: '18px',
  color: '#64748b',
  zIndex: 2,
  ...extra,
});

const cardStyle = {
  width: '100%',
  maxWidth: '400px',
  background: 'linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9))',
  backdropFilter: 'blur(10px)',
  WebkitBackdropFilter: 'blur(10px)',
  border: '1px solid rgba(255, 255, 255, 0.06)',
  boxShadow: '0 8px 32px rgba(0, 0, 0, 0.3)',
  borderRadius: '24px',
  padding: '32px 24px',
} as any;

const inputWrap = {
  position: 'relative' as const,
  background: 'rgba(15, 23, 42, 0.6)',
  border: '1px solid rgba(51, 65, 85, 0.8)',
  borderRadius: '16px',
  overflow: 'hidden' as const,
};

const labelStyle = {
  display: 'block',
  fontSize: '11px',
  fontWeight: '700',
  color: '#94a3b8',
  textTransform: 'uppercase' as const,
  letterSpacing: '1.5px',
  marginBottom: '8px',
  paddingLeft: '4px',
};

const primaryBtn = {
  '--border-radius': '16px',
  '--background': 'linear-gradient(135deg, #eab308, #f59e0b)',
  '--color': '#020617',
  '--box-shadow': '0 4px 16px rgba(234, 179, 8, 0.3)',
  height: '52px',
  fontWeight: '700',
  fontSize: '16px',
  margin: '0 0 16px',
} as any;

const Login: FC = () => {
  const { login, verifyOtp, sendOtp, token } = useAuth();
  const history = useHistory();

  useState(() => { if (token) history.replace('/dashboard'); });

  const [screen, setScreen] = useState<Screen>('login');
  const [loginValue, setLoginValue] = useState('');
  const [password, setPassword] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [toast, setToast] = useState<string | null>(null);
  const [quote, setQuote] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  // MFA state
  const [mfaToken, setMfaToken] = useState('');
  const [mfaOtp, setMfaOtp] = useState('');
  const [maskedPhone, setMaskedPhone] = useState('');

  // Forgot Password state
  const [fpPhone, setFpPhone] = useState('');
  const [fpOtp, setFpOtp] = useState('');
  const [fpPassword, setFpPassword] = useState('');
  const [fpConfirm, setFpConfirm] = useState('');

  // Resend countdown (shared)
  const [resendCountdown, setResendCountdown] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

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

  useState(() => { setQuote(quotes[Math.floor(Math.random() * quotes.length)]); });
  useEffect(() => () => { if (timerRef.current) clearInterval(timerRef.current); }, []);

  const startResendTimer = () => {
    setResendCountdown(60);
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(() => {
      setResendCountdown(prev => {
        if (prev <= 1) { clearInterval(timerRef.current!); return 0; }
        return prev - 1;
      });
    }, 1000);
  };

  // ── Login ──
  const handleLogin = async (e: FormEvent) => {
    e.preventDefault();
    if (isLoading) return; // Prevent double submission

    setIsLoading(true);
    setError(null);
    try {
      const result = await login({ login: loginValue, password });
      if (result.success) {
        if (result.mfa_required) {
          setMfaToken(result.user_id);
          const phone = result.phone || result.email || '';
          setMaskedPhone(phone);
          setScreen('mfa');
          // Auto-send SMS OTP
          await sendOtp(result.user_id, 'phone');
          startResendTimer();
        } else {
          history.push('/dashboard');
        }
      } else {
        setError(result.message || 'Login failed. Please check your credentials.');
      }
    } catch (err: any) {
      setError('An unexpected error occurred. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  // ── MFA ──
  const handleVerifyMfa = async () => {
    if (isLoading || mfaOtp.length !== 6) return;
    setIsLoading(true);
    setError(null);
    try {
      const result = await verifyOtp(mfaToken, mfaOtp);
      if (result.success) {
        history.push('/dashboard');
      } else {
        setError(result.message || 'Invalid verification code.');
      }
    } catch (err) {
      setError('Connection error. Please try again.');
    } finally {
      setIsLoading(false);
    }
  };

  const handleResendMfa = async () => {
    if (resendCountdown > 0) return;
    setIsLoading(true);
    await sendOtp(mfaToken, 'phone');
    startResendTimer();
    setToast('Verification code resent!');
    setIsLoading(false);
  };

  // ── Forgot Password: Step 1 — Send OTP ──
  const handleForgotSendOtp = async (e: FormEvent) => {
    e.preventDefault();
    if (!fpPhone || fpPhone.length < 11) { setError('Enter a valid 11-digit phone number.'); return; }
    setIsLoading(true);
    setError(null);
    try {
      const res = await axios.post(endpoints.forgotPassword, { identifier: fpPhone, method: 'phone' });
      if (res.data.success) {
        setScreen('forgot_otp');
        startResendTimer();
        setToast('Verification code sent via SMS!');
      } else {
        setError(res.data.message || 'Could not send OTP.');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Network error.');
    }
    setIsLoading(false);
  };

  // ── Forgot Password: Step 2 — Verify OTP ──
  const handleForgotVerifyOtp = async () => {
    if (fpOtp.length !== 6) { setError('Enter the 6-digit code.'); return; }
    setIsLoading(true);
    setError(null);
    try {
      const res = await axios.post(endpoints.verifyResetOtp, { identifier: fpPhone, otp: fpOtp });
      if (res.data.success) {
        setScreen('forgot_reset');
      } else {
        setError(res.data.message || 'Invalid or expired code.');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Network error.');
    }
    setIsLoading(false);
  };

  const handleForgotResendOtp = async () => {
    if (resendCountdown > 0) return;
    setIsLoading(true);
    try {
      await axios.post(endpoints.forgotPassword, { identifier: fpPhone, method: 'phone' });
      startResendTimer();
      setToast('Code resent!');
    } catch { setError('Failed to resend.'); }
    setIsLoading(false);
  };

  // ── Forgot Password: Step 3 — Reset ──
  const handleForgotReset = async (e: FormEvent) => {
    e.preventDefault();
    if (fpPassword.length < 8) { setError('Password must be at least 8 characters.'); return; }
    if (fpPassword !== fpConfirm) { setError('Passwords do not match.'); return; }
    setIsLoading(true);
    setError(null);
    try {
      const res = await axios.post(endpoints.resetPassword, {
        identifier: fpPhone, otp: fpOtp,
        password: fpPassword, password_confirmation: fpConfirm,
      });
      if (res.data.success) {
        setToast('Password reset! Please log in.');
        setScreen('login');
        setFpPhone(''); setFpOtp(''); setFpPassword(''); setFpConfirm('');
      } else {
        setError(res.data.message || 'Reset failed.');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Network error.');
    }
    setIsLoading(false);
  };

  const ResendRow = ({ onResend }: { onResend: () => void }) => (
    <div style={{ textAlign: 'center', marginTop: '8px' }}>
      {resendCountdown > 0 ? (
        <p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
          Resend in <span style={{ color: '#eab308', fontWeight: '700' }}>{resendCountdown}s</span>
        </p>
      ) : (
        <button onClick={onResend} disabled={isLoading} style={{
          display: 'inline-flex', alignItems: 'center', gap: '6px',
          background: 'none', border: 'none', color: '#eab308',
          fontWeight: '600', fontSize: '13px', cursor: 'pointer',
        }}>
          <IonIcon icon={refreshOutline} style={{ fontSize: '14px' }} />
          Resend Code
        </button>
      )}
    </div>
  );

  const BackBtn = ({ to }: { to: Screen }) => (
    <button type="button" onClick={() => setScreen(to)} style={{
      display: 'flex', alignItems: 'center', gap: '6px',
      background: 'none', border: 'none', color: '#94a3b8',
      fontSize: '13px', marginBottom: '20px', cursor: 'pointer',
    }}>
      <IonIcon icon={arrowBackOutline} /> Back
    </button>
  );

  return (
    <IonPage>
      <IonContent fullscreen scrollY={false}>
        <div style={{
          minHeight: '100%',
          background: 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)',
          display: 'flex', flexDirection: 'column',
          alignItems: 'center', justifyContent: 'center',
          padding: '24px 20px',
        }}>

          {/* Logo */}
          <div style={{ textAlign: 'center', marginBottom: '40px' }}>
            <div style={{
              width: '80px', height: '80px',
              background: 'linear-gradient(135deg, #eab308, #f59e0b)',
              borderRadius: '20px', display: 'flex',
              alignItems: 'center', justifyContent: 'center',
              margin: '0 auto 16px',
              boxShadow: '0 8px 24px rgba(234, 179, 8, 0.25)',
            }}>
              <IonIcon icon={carSportOutline} style={{ fontSize: '40px', color: '#020617' }} />
            </div>
            <h1 style={{ fontSize: '28px', fontWeight: '800', color: '#ffffff', margin: '0 0 4px', letterSpacing: '-0.5px' }}>EuroTaxi</h1>
            <p style={{ color: '#94a3b8', fontSize: '14px', fontWeight: '500', margin: 0 }}>Driver Portal</p>
            {screen === 'login' && (
              <div style={{ marginTop: '16px', maxWidth: '300px', margin: '16px auto 0' }}>
                <p style={{ color: '#64748b', fontSize: '13px', fontStyle: 'italic', lineHeight: '1.5', margin: 0 }}>"{quote}"</p>
              </div>
            )}
          </div>

          {/* ── LOGIN SCREEN ── */}
          {screen === 'login' && (
            <div style={cardStyle}>
              <form onSubmit={handleLogin}>
                <div style={{ marginBottom: '20px' }}>
                  <label style={labelStyle}>Account</label>
                  <div style={inputWrap}>
                    <IonIcon icon={mailOutline} style={iconStyle()} />
                    <IonInput value={loginValue} onIonInput={(e) => setLoginValue(e.detail.value!)}
                      placeholder="Email or Phone" style={inputStyle} />
                  </div>
                </div>

                <div style={{ marginBottom: '12px' }}>
                  <label style={labelStyle}>Password</label>
                  <div style={inputWrap}>
                    <IonIcon icon={lockClosedOutline} style={iconStyle()} />
                    <IonInput type={showPassword ? "text" : "password"} value={password} onIonInput={(e) => setPassword(e.detail.value!)}
                      placeholder="••••••••" style={inputStyle} />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} style={{
                      position: 'absolute',
                      right: '12px',
                      top: '50%',
                      transform: 'translateY(-50%)',
                      background: 'none',
                      border: 'none',
                      color: '#64748b',
                      fontSize: '18px',
                      zIndex: 10,
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      padding: '8px'
                    }}>
                      <IonIcon icon={showPassword ? eyeOffOutline : eyeOutline} />
                    </button>
                  </div>
                </div>

                {/* Forgot Password link */}
                <div style={{ textAlign: 'right', marginBottom: '24px' }}>
                  <button type="button" onClick={() => setScreen('forgot_phone')} style={{
                    background: 'none',
                    border: 'none',
                    color: '#eab308',
                    fontSize: '12px', 
                    fontWeight: '600', 
                    cursor: 'pointer',
                    padding: '4px 0',
                    opacity: 0.8
                  }}>
                    Forgot Password?
                  </button>
                </div>

                <IonButton expand="block" type="submit" disabled={isLoading} style={primaryBtn}>
                  {isLoading ? <IonSpinner name="crescent" /> : 'Sign In'}
                </IonButton>

                <div style={{ textAlign: 'center' }}>
                  <p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
                    New driver?{' '}
                    <button type="button" onClick={() => history.push('/register')} style={{
                      background: 'none', border: 'none', color: '#eab308',
                      fontWeight: '700', fontSize: '13px', cursor: 'pointer',
                    }}>
                      Register Now
                    </button>
                  </p>
                </div>
              </form>
            </div>
          )}

          {/* ── MFA SCREEN ── */}
          {screen === 'mfa' && (
            <div style={cardStyle}>
              <BackBtn to="login" />
              <div style={{ textAlign: 'center', marginBottom: '24px' }}>
                <h2 style={{ fontSize: '20px', fontWeight: '700', color: '#ffffff', margin: '0 0 8px' }}>Verify Identity</h2>
                <p style={{ fontSize: '13px', color: '#94a3b8', margin: 0 }}>
                  Code sent via SMS to <span style={{ color: '#eab308', fontWeight: '600' }}>{maskedPhone}</span>
                </p>
              </div>

              <label style={labelStyle}>Verification Code</label>
              <div style={{ ...inputWrap, marginBottom: '24px' }}>
                <IonIcon icon={keyOutline} style={iconStyle()} />
                <IonInput value={mfaOtp} onIonInput={(e) => setMfaOtp(e.detail.value!.replace(/[^0-9]/g, ''))}
                  placeholder="000000" maxlength={6} inputmode="numeric"
                  style={{ ...inputStyle, fontSize: '22px', fontWeight: '800', letterSpacing: '6px', '--padding-start': '48px', textAlign: 'center' } as any} />
              </div>

              <IonButton expand="block" onClick={handleVerifyMfa} disabled={isLoading || mfaOtp.length !== 6} style={primaryBtn}>
                {isLoading ? <IonSpinner name="crescent" /> : 'Verify Code'}
              </IonButton>

              <ResendRow onResend={handleResendMfa} />
            </div>
          )}

          {/* ── FORGOT: ENTER PHONE ── */}
          {screen === 'forgot_phone' && (
            <div style={cardStyle}>
              <BackBtn to="login" />
              <div style={{ marginBottom: '24px' }}>
                <h2 style={{ fontSize: '20px', fontWeight: '700', color: '#ffffff', margin: '0 0 8px' }}>Forgot Password</h2>
                <p style={{ fontSize: '13px', color: '#94a3b8', margin: 0 }}>Enter your registered phone number to receive a reset code.</p>
              </div>

              <form onSubmit={handleForgotSendOtp}>
                <label style={labelStyle}>Phone Number</label>
                <div style={{ ...inputWrap, marginBottom: '24px' }}>
                  <IonIcon icon={callOutline} style={iconStyle()} />
                  <IonInput value={fpPhone}
                    onIonInput={(e) => setFpPhone(e.detail.value!.replace(/[^0-9]/g, ''))}
                    placeholder="09123456789" maxlength={11} inputmode="tel"
                    style={inputStyle} />
                </div>

                <IonButton expand="block" type="submit" disabled={isLoading} style={primaryBtn}>
                  {isLoading ? <IonSpinner name="crescent" /> : 'Send Reset Code'}
                </IonButton>
              </form>
            </div>
          )}

          {/* ── FORGOT: ENTER OTP ── */}
          {screen === 'forgot_otp' && (
            <div style={cardStyle}>
              <BackBtn to="forgot_phone" />
              <div style={{ marginBottom: '24px' }}>
                <h2 style={{ fontSize: '20px', fontWeight: '700', color: '#ffffff', margin: '0 0 8px' }}>Enter Reset Code</h2>
                <p style={{ fontSize: '13px', color: '#94a3b8', margin: 0 }}>
                  Code sent to <span style={{ color: '#eab308', fontWeight: '600' }}>{fpPhone}</span>
                </p>
              </div>

              <label style={labelStyle}>6-Digit Code</label>
              <div style={{ ...inputWrap, marginBottom: '24px' }}>
                <IonIcon icon={keyOutline} style={iconStyle()} />
                <IonInput value={fpOtp}
                  onIonInput={(e) => setFpOtp(e.detail.value!.replace(/[^0-9]/g, ''))}
                  placeholder="000000" maxlength={6} inputmode="numeric"
                  style={{ ...inputStyle, fontSize: '22px', fontWeight: '800', letterSpacing: '6px', textAlign: 'center' } as any} />
              </div>

              <IonButton expand="block" onClick={handleForgotVerifyOtp}
                disabled={isLoading || fpOtp.length !== 6} style={primaryBtn}>
                {isLoading ? <IonSpinner name="crescent" /> : 'Verify Code'}
              </IonButton>

              <ResendRow onResend={handleForgotResendOtp} />
            </div>
          )}

          {/* ── FORGOT: NEW PASSWORD ── */}
          {screen === 'forgot_reset' && (
            <div style={cardStyle}>
              <div style={{ marginBottom: '24px' }}>
                <h2 style={{ fontSize: '20px', fontWeight: '700', color: '#ffffff', margin: '0 0 8px' }}>New Password</h2>
                <p style={{ fontSize: '13px', color: '#94a3b8', margin: 0 }}>Create a new password for your account.</p>
              </div>

              <form onSubmit={handleForgotReset}>
                <label style={labelStyle}>New Password</label>
                <div style={{ ...inputWrap, marginBottom: '16px' }}>
                  <IonIcon icon={lockClosedOutline} style={iconStyle()} />
                  <IonInput type="password" value={fpPassword}
                    onIonInput={(e) => setFpPassword(e.detail.value!)}
                    placeholder="••••••••" style={inputStyle} />
                </div>

                <label style={labelStyle}>Confirm Password</label>
                <div style={{ ...inputWrap, marginBottom: '24px' }}>
                  <IonIcon icon={lockClosedOutline} style={iconStyle()} />
                  <IonInput type="password" value={fpConfirm}
                    onIonInput={(e) => setFpConfirm(e.detail.value!)}
                    placeholder="••••••••" style={inputStyle} />
                </div>

                <IonButton expand="block" type="submit" disabled={isLoading} style={primaryBtn}>
                  {isLoading ? <IonSpinner name="crescent" /> : 'Reset Password'}
                </IonButton>
              </form>
            </div>
          )}

          <p style={{ marginTop: '32px', color: '#475569', fontSize: '12px' }}>
            &copy; 2026 EuroTaxi System. All rights reserved.
          </p>

          <IonToast isOpen={!!error} message={error || ''} duration={4000}
            onDidDismiss={() => setError(null)} color="danger" position="bottom" />
          <IonToast isOpen={!!toast} message={toast || ''} duration={2500}
            onDidDismiss={() => setToast(null)} color="success" position="bottom" />
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Login;
