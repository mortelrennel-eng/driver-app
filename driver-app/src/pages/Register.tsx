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
  personOutline,
  mailOutline,
  callOutline,
  lockClosedOutline,
  carSportOutline,
  arrowBackOutline,
  personAddOutline,
  eyeOutline,
  eyeOffOutline,
  keyOutline,
  checkmarkCircleOutline,
  refreshOutline,
} from 'ionicons/icons';
import { useState, useEffect, useRef } from 'react';
import type { FC } from 'react';
import { useHistory } from 'react-router-dom';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';
import { Device } from '@capacitor/device';

/* ── Shared Styles (now generated inside component to use theme) ── */

const Register: FC = () => {
  const { t, isDark } = useTheme();
  const history = useHistory();
  const { loginFromData } = useAuth();

  const styles = {
    sectionCard: {
      background: isDark ? 'linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9))' : '#ffffff',
      backdropFilter: 'blur(10px)',
      WebkitBackdropFilter: 'blur(10px)',
      border: isDark ? '1px solid rgba(255, 255, 255, 0.06)' : '1px solid #e2e8f0',
      boxShadow: isDark ? '0 8px 32px rgba(0, 0, 0, 0.3)' : '0 4px 16px rgba(0, 0, 0, 0.06)',
      borderRadius: '24px',
      padding: '24px 20px',
      marginBottom: '16px',
    } as React.CSSProperties,
    label: {
      display: 'block',
      fontSize: '11px',
      fontWeight: '700',
      color: isDark ? '#94a3b8' : '#64748b',
      textTransform: 'uppercase' as const,
      letterSpacing: '1.5px',
      marginBottom: '8px',
      paddingLeft: '4px',
    },
    inputWrap: {
      position: 'relative' as const,
      background: isDark ? 'rgba(15, 23, 42, 0.6)' : '#f1f5f9',
      border: isDark ? '1px solid rgba(51, 65, 85, 0.8)' : '1px solid #cbd5e1',
      borderRadius: '16px',
      overflow: 'hidden' as const,
      marginBottom: '16px',
    },
    inputIcon: {
      position: 'absolute' as const,
      left: '16px',
      top: '50%',
      transform: 'translateY(-50%)',
      fontSize: '18px',
      color: isDark ? '#64748b' : '#94a3b8',
      zIndex: 2,
    },
    eyeIcon: {
      position: 'absolute' as const,
      right: '16px',
      top: '50%',
      transform: 'translateY(-50%)',
      fontSize: '18px',
      color: isDark ? '#64748b' : '#94a3b8',
      zIndex: 3,
      cursor: 'pointer',
    },
    sectionDot: {
      width: '8px',
      height: '8px',
      borderRadius: '50%',
      background: 'linear-gradient(135deg, #eab308, #f59e0b)',
    },
    sectionLabel: {
      fontSize: '11px',
      fontWeight: '800',
      color: '#eab308',
      textTransform: 'uppercase' as const,
      letterSpacing: '2px',
    },
  };

  const inputStyle = {
    '--padding-start': '48px',
    '--padding-end': '48px',
    '--color': isDark ? '#f8fafc' : '#1e293b',
    '--placeholder-color': isDark ? '#475569' : '#94a3b8',
    '--background': 'transparent',
    height: '52px',
    fontSize: '15px',
  } as any;

  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [showPassword, setShowPassword] = useState(false);
  const [step, setStep] = useState<'form' | 'otp' | 'success'>('form');
  const [pendingPhone, setPendingPhone] = useState('');
  const [otp, setOtp] = useState('');
  const [resendCountdown, setResendCountdown] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    plate_number: ''
  });

  useEffect(() => {
    return () => { if (timerRef.current) clearInterval(timerRef.current); };
  }, []);

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

  const validateEmail = (email: string) => {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return { valid: false, message: 'Invalid email format.' };
    if (email.endsWith('@gmail.com')) {
      const prefix = email.split('@')[0];
      if (prefix.length < 6) return { valid: false, message: 'Gmail address must have at least 6 characters before @gmail.com' };
    }
    return { valid: true, message: '' };
  };

  const handleRegister = async (e: React.FormEvent) => {
    e.preventDefault();
    
    // Name validation
    if (formData.name.trim() === '') { setError('Name cannot be just spaces.'); return; }
    if (!formData.name.match(/^[a-zA-ZñÑ\s]*$/)) { setError('Name must only contain letters.'); return; }
    
    // Email validation
    const emailCheck = validateEmail(formData.email);
    if (!emailCheck.valid) { setError(emailCheck.message); return; }
    
    // Phone validation
    if (!formData.phone.startsWith('09')) { setError('Phone number must start with 09.'); return; }
    if (formData.phone.length !== 11) { setError('Phone number must be exactly 11 digits.'); return; }
    
    // Password validation
    if (formData.password !== formData.password_confirmation) { setError('Passwords do not match.'); return; }
    if (formData.password.length < 8) { setError('Password must be at least 8 characters.'); return; }

    setIsLoading(true);
    setError(null);
    try {
      const response = await axios.post(endpoints.register, formData);
      if (response.data.success && response.data.otp_sent) {
        setPendingPhone(formData.phone);
        setStep('otp');
        startResendTimer();
      } else {
        setError(response.data.message || 'Registration failed. Please try again.');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Registration failed. Please try again.');
    }
    setIsLoading(false);
  };

  const handleVerifyOtp = async () => {
    if (otp.length !== 6) { setError('Please enter the 6-digit code.'); return; }
    setIsLoading(true);
    setError(null);
    try {
      const deviceInfo = await Device.getInfo();
      const deviceId = await Device.getId();
      const deviceName = `${deviceInfo.manufacturer} ${deviceInfo.model}`;

      const response = await axios.post(endpoints.verifyRegistrationOtp, {
        phone: pendingPhone,
        otp,
        device_name: deviceName,
        device_id: deviceId.identifier
      });

      if (response.data.success) {
        setStep('success');
        
        // Auto-login using the token and user data returned from registration
        if (response.data.token && response.data.user) {
          loginFromData(response.data.token, response.data.user);
          setTimeout(() => history.push('/dashboard'), 2000);
        } else {
          setTimeout(() => history.push('/login'), 2000);
        }
      } else {
        setError(response.data.message || 'Invalid code. Please try again.');
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Verification failed. Please try again.');
    }
    setIsLoading(false);
  };

  const handleResendOtp = async () => {
    if (resendCountdown > 0) return;
    setIsLoading(true);
    setError(null);
    try {
      await axios.post(endpoints.resendRegistrationOtp, { phone: pendingPhone });
      startResendTimer();
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to resend code.');
    }
    setIsLoading(false);
  };

  const field = (
    icon: string,
    label: string,
    key: keyof typeof formData,
    placeholder: string,
    type: string = 'text',
    maxLen: number = 50
  ) => (
    <div>
      <label style={styles.label}>{label}</label>
      <div style={styles.inputWrap}>
        <IonIcon icon={icon} style={styles.inputIcon} />
        <IonInput
          type={type === 'password' ? (showPassword ? 'text' : 'password') : (type as any)}
          value={formData[key]}
          onIonInput={(e) => {
            let val = e.detail.value!;
            if (key === 'name') val = val.replace(/[^a-zA-Z\s]/g, '');
            if (key === 'phone') val = val.replace(/[^0-9]/g, '');
            setFormData({ ...formData, [key]: val });
          }}
          placeholder={placeholder}
          required
          maxlength={maxLen}
          style={inputStyle}
        />
        {type === 'password' && (
          <IonIcon
            icon={showPassword ? eyeOffOutline : eyeOutline}
            style={styles.eyeIcon}
            onClick={() => setShowPassword(!showPassword)}
          />
        )}
      </div>
    </div>
  );

  return (
    <IonPage>
      <IonContent fullscreen>
        <div style={{
          minHeight: '100%',
          background: isDark ? 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)' : 'linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%)',
          padding: '16px 20px 40px',
        }}>

          {/* Back Button */}
          <button
            type="button"
            onClick={() => step === 'otp' ? setStep('form') : history.push('/login')}
            style={{
              display: 'flex', alignItems: 'center', gap: '8px',
              background: 'none', border: 'none', color: t.textSecondary,
              fontSize: '13px', fontWeight: '500', padding: '8px 0',
              marginBottom: '16px', cursor: 'pointer',
            }}
          >
            <IonIcon icon={arrowBackOutline} style={{ fontSize: '18px' }} />
            {step === 'otp' ? 'Back to Form' : 'Back to Login'}
          </button>

          {/* Header */}
          <div style={{ textAlign: 'center', marginBottom: '28px' }}>
            <div style={{
              width: '72px', height: '72px',
              background: step === 'success'
                ? 'linear-gradient(135deg, #22c55e, #16a34a)'
                : 'linear-gradient(135deg, #eab308, #f59e0b)',
              borderRadius: '20px', display: 'flex',
              alignItems: 'center', justifyContent: 'center',
              margin: '0 auto 14px',
              boxShadow: step === 'success'
                ? '0 8px 24px rgba(34, 197, 94, 0.25)'
                : '0 8px 24px rgba(234, 179, 8, 0.25)',
              transition: 'all 0.3s ease',
            }}>
              <IonIcon
                icon={step === 'success' ? checkmarkCircleOutline : step === 'otp' ? keyOutline : personAddOutline}
                style={{ fontSize: '36px', color: '#020617' }}
              />
            </div>
            <h1 style={{ fontSize: '24px', fontWeight: '900', color: t.textPrimary, margin: '0 0 4px' }}>
              {step === 'success' ? 'Registration Complete' : step === 'otp' ? 'Verify Phone' : 'Create Account'}
            </h1>
            <p style={{ fontSize: '13px', color: t.textSecondary, margin: 0 }}>
              {step === 'success'
                ? 'Redirecting to login...'
                : step === 'otp'
                  ? `Code sent to ${pendingPhone}`
                  : 'Create your driver account'}
            </p>
          </div>

          {/* Step: Registration Form */}
          {step === 'form' && (
            <form onSubmit={handleRegister}>
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Account</span>
                </div>
                {field(personOutline, 'Full Name', 'name', 'e.g. Juan Dela Cruz', 'text', 50)}
                {field(mailOutline, 'Email', 'email', 'email@example.com', 'email', 50)}
                {field(callOutline, 'Phone', 'phone', '09123456789', 'tel', 11)}
                {field(lockClosedOutline, 'Password', 'password', '••••••••', 'password', 20)}
                {field(lockClosedOutline, 'Confirm Password', 'password_confirmation', '••••••••', 'password', 20)}
              </div>

              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Vehicle</span>
                </div>
                {field(carSportOutline, 'Plate Number', 'plate_number', 'ABC 1234', 'text', 10)}
              </div>

              <IonButton
                expand="block" type="submit" disabled={isLoading}
                style={{
                  '--border-radius': '16px',
                  '--background': 'linear-gradient(135deg, #eab308, #f59e0b)',
                  '--color': '#020617',
                  '--box-shadow': '0 4px 16px rgba(234, 179, 8, 0.3)',
                  height: '52px', fontWeight: '700', fontSize: '16px',
                  margin: '8px 0 16px',
                } as any}
              >
                {isLoading ? <IonSpinner name="crescent" /> : 'Send Verification Code'}
              </IonButton>

              <p style={{ textAlign: 'center', color: '#475569', fontSize: '11px', margin: 0, padding: '0 16px', lineHeight: '1.5' }}>
                By registering, you agree to EuroTaxi's Terms of Service and Privacy Policy.
              </p>
            </form>
          )}

          {/* Step: OTP Verification */}
          {step === 'otp' && (
            <div style={styles.sectionCard}>
              <div style={{ textAlign: 'center', marginBottom: '24px' }}>
                <p style={{ fontSize: '13px', color: '#94a3b8', margin: 0, lineHeight: '1.6' }}>
                  Enter the 6-digit code sent via SMS to <br />
                  <span style={{ color: '#eab308', fontWeight: '700' }}>{pendingPhone}</span>
                </p>
              </div>

              {/* OTP Input */}
              <label style={styles.label}>Verification Code</label>
              <div style={{ ...styles.inputWrap, marginBottom: '24px' }}>
                <IonIcon icon={keyOutline} style={styles.inputIcon} />
                <IonInput
                  value={otp}
                  onIonInput={(e) => setOtp(e.detail.value!.replace(/[^0-9]/g, ''))}
                  placeholder="000000"
                  maxlength={6}
                  inputmode="numeric"
                  style={{
                    '--padding-start': '48px',
                    '--padding-end': '16px',
                    '--color': '#f8fafc',
                    '--placeholder-color': '#475569',
                    '--background': 'transparent',
                    height: '56px',
                    fontSize: '24px',
                    fontWeight: '800',
                    letterSpacing: '8px',
                    textAlign: 'center',
                  } as any}
                />
              </div>

              <IonButton
                expand="block"
                onClick={handleVerifyOtp}
                disabled={isLoading || otp.length !== 6}
                style={{
                  '--border-radius': '16px',
                  '--background': 'linear-gradient(135deg, #eab308, #f59e0b)',
                  '--color': '#020617',
                  '--box-shadow': '0 4px 16px rgba(234, 179, 8, 0.3)',
                  height: '52px', fontWeight: '700', fontSize: '16px',
                  margin: '0 0 20px',
                } as any}
              >
                {isLoading ? <IonSpinner name="crescent" /> : 'Verify & Create Account'}
              </IonButton>

              {/* Resend */}
              <div style={{ textAlign: 'center' }}>
                {resendCountdown > 0 ? (
                  <p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
                    Resend code in <span style={{ color: '#eab308', fontWeight: '700' }}>{resendCountdown}s</span>
                  </p>
                ) : (
                  <button
                    onClick={handleResendOtp}
                    disabled={isLoading}
                    style={{
                      display: 'inline-flex', alignItems: 'center', gap: '6px',
                      background: 'none', border: 'none',
                      color: '#eab308', fontWeight: '600', fontSize: '13px',
                      cursor: 'pointer',
                    }}
                  >
                    <IonIcon icon={refreshOutline} style={{ fontSize: '14px' }} />
                    Resend Code
                  </button>
                )}
              </div>
            </div>
          )}

          {/* Step: Success */}
          {step === 'success' && (
            <div style={{ ...styles.sectionCard, textAlign: 'center', padding: '40px 20px' }}>
              <div style={{
                width: '64px', height: '64px',
                background: 'rgba(34, 197, 94, 0.15)',
                borderRadius: '50%',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                margin: '0 auto 16px',
              }}>
                <IonIcon icon={checkmarkCircleOutline} style={{ fontSize: '36px', color: '#22c55e' }} />
              </div>
              <h2 style={{ color: t.textPrimary, fontSize: '20px', fontWeight: '700', margin: '0 0 8px' }}>
                Welcome to EuroTaxi!
              </h2>
              <p style={{ color: t.textSecondary, fontSize: '14px', margin: 0 }}>
                Your account has been created. Redirecting to dashboard...
              </p>
            </div>
          )}

          <IonToast
            isOpen={!!error}
            message={error || ''}
            duration={4000}
            onDidDismiss={() => setError(null)}
            color="danger"
            position="bottom"
          />
        </div>
      </IonContent>
    </IonPage>
  );
};

export default Register;
