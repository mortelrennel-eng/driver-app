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
  useIonToast,
  useIonRouter
} from '@ionic/react';
import {
  personOutline,
  callOutline,
  homeOutline,
  cardOutline,
  calendarOutline,
  peopleOutline,
  saveOutline,
  lockClosedOutline,
  documentTextOutline,
  logOutOutline,
  arrowBackOutline,
  chevronForwardOutline,
  sunnyOutline,
  moonOutline
} from 'ionicons/icons';
import React, { useState } from 'react';
import type { FC } from 'react';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useAuth } from '../context/AuthContext';
import { useTheme } from '../context/ThemeContext';

/* ── Shared Styles moved inside component ── */

const Settings: FC = () => {
  const [presentToast] = useIonToast();
  const ionRouter = useIonRouter();
  const { refreshUser, logout } = useAuth();
  const { t, isDark, toggleTheme } = useTheme();

  /* ── Dynamic Theme Styles ── */
  const styles = {
    sectionCard: {
      background: t.card,
      backdropFilter: 'blur(10px)',
      WebkitBackdropFilter: 'blur(10px)',
      border: t.border,
      boxShadow: t.cardShadow,
      borderRadius: '24px',
      padding: '20px',
      marginBottom: '16px',
    } as React.CSSProperties,
    label: {
      display: 'block',
      fontSize: '11px',
      fontWeight: '700',
      color: t.textMuted,
      textTransform: 'uppercase' as const,
      letterSpacing: '1.5px',
      marginBottom: '8px',
      paddingLeft: '4px',
    },
    inputWrap: {
      position: 'relative' as const,
      background: t.inputBg,
      border: `1px solid ${t.inputBorder}`,
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
      color: t.gold,
      zIndex: 2,
    },
    sectionDot: {
      width: '8px',
      height: '8px',
      borderRadius: '50%',
      background: t.goldGrad,
    },
    sectionLabel: {
      fontSize: '11px',
      fontWeight: '800',
      color: t.gold,
      textTransform: 'uppercase' as const,
      letterSpacing: '2px',
    },
  };

  const inputStyle = {
    paddingLeft: '48px',
    paddingRight: '16px',
    color: t.textPrimary,
    background: 'transparent',
    height: '52px',
    fontSize: '15px',
    border: 'none',
    width: '100%',
    outline: 'none',
    fontWeight: '500'
  } as any;
  
  const [view, setView] = useState<'main' | 'profile' | 'password'>('main');

  // Profile State
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [address, setAddress] = useState('');
  const [licenseNumber, setLicenseNumber] = useState('');
  const [licenseExpiry, setLicenseExpiry] = useState('');
  const [emergencyContact, setEmergencyContact] = useState('');
  const [emergencyPhone, setEmergencyPhone] = useState('');

  // Document States
  const [licensePhoto, setLicensePhoto] = useState<string | null>(null);
  const [nbiPhoto, setNbiPhoto] = useState<string | null>(null);
  const [pnpPhoto, setPnpPhoto] = useState<string | null>(null);

  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(true);
  const [uploading, setUploading] = useState<string | null>(null);

  // Password State
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  React.useEffect(() => {
    const fetchProfile = async () => {
      try {
        const response = await axios.get(endpoints.getProfile);
        if (response.data.success) {
          const profile = response.data.data;
          setName(profile.name || '');
          setPhone(profile.phone || '');
          setAddress(profile.address || '');
          setLicenseNumber(profile.license_number || '');
          setLicenseExpiry(profile.license_expiry || '');
          setEmergencyContact(profile.emergency_contact || '');
          setEmergencyPhone(profile.emergency_phone || '');
          
          // Documents
          setLicensePhoto(profile.license_photo || null);
          setNbiPhoto(profile.nbi_clearance_photo || null);
          setPnpPhoto(profile.pnp_clearance_photo || null);
        }
      } catch (e) {
        console.error('Failed to fetch profile', e);
      } finally {
        setFetching(false);
      }
    };
    fetchProfile();
  }, []);

  const handleFileUpload = async (type: 'license' | 'nbi' | 'pnp' | 'profile', file: File) => {
    setUploading(type);
    const formData = new FormData();
    formData.append('file', file);
    formData.append('document_type', type);

    try {
      const response = await axios.post(endpoints.uploadDocument, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      if (response.data.success) {
        presentToast({ message: response.data.message, color: 'success', duration: 2000 });
        if (type === 'license') setLicensePhoto(response.data.path);
        if (type === 'nbi') setNbiPhoto(response.data.path);
        if (type === 'pnp') setPnpPhoto(response.data.path);
        refreshUser();
      }
    } catch (e: any) {
      presentToast({ message: e.response?.data?.message || 'Upload failed', color: 'danger', duration: 3000 });
    } finally {
      setUploading(null);
    }
  };

  const [errors, setErrors] = useState<any>({});

  const validateProfile = () => {
    const newErrors: any = {};
    
    // Name
    if (!name || name.trim().length === 0) {
      newErrors.name = 'Full name is required';
    } else if (!/^[a-zA-ZñÑ\s]+$/.test(name)) {
      newErrors.name = 'Name should only contain letters and spaces';
    }

    // Phone
    const phoneRegex = /^09\d{9}$/;
    if (!phoneRegex.test(phone)) {
      newErrors.phone = 'Must be 09XXXXXXXXX (11 digits)';
    }

    // Address
    if (!address || address.trim().length < 5) {
      newErrors.address = 'Provide a more detailed address';
    } else if (/^\d+$/.test(address)) {
      newErrors.address = 'Address cannot be just numbers';
    } else if (!/^[a-zA-Z0-9]/.test(address)) {
      newErrors.address = 'Address must start with a letter or number';
    }

    // Emergency Phone
    if (emergencyPhone && !phoneRegex.test(emergencyPhone)) {
      newErrors.emergencyPhone = 'Invalid phone format (09XXXXXXXXX)';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleUpdateProfile = async () => {
    if (!validateProfile()) {
      presentToast({ message: 'Please fix the errors in the form', duration: 2000, color: 'warning', position: 'top' });
      return;
    }

    setLoading(true);
    try {
      const response = await axios.post(endpoints.updateProfile, {
        name: name.trim(),
        phone,
        address: address.trim(),
        license_number: licenseNumber.toUpperCase(),
        license_expiry: licenseExpiry,
        emergency_contact: emergencyContact.trim(),
        emergency_phone: emergencyPhone
      });

      if (response.data.success) {
        await refreshUser();
        presentToast({
          message: 'Profile updated successfully!',
          duration: 2000,
          color: 'success'
        });
        setErrors({});
        setView('main');
      }
    } catch (e: any) {
      presentToast({
        message: e.response?.data?.message || 'Failed to update profile',
        duration: 3000,
        color: 'danger'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleChangePassword = async () => {
    if (!currentPassword || !newPassword || !confirmPassword) {
      presentToast({ message: 'Please fill in all password fields', duration: 2000, color: 'warning' });
      return;
    }

    if (newPassword !== confirmPassword) {
      presentToast({ message: 'Passwords do not match', duration: 2000, color: 'warning' });
      return;
    }

    if (newPassword.length < 8) {
      presentToast({ message: 'New password must be at least 8 characters', duration: 2000, color: 'warning' });
      return;
    }

    setLoading(true);
    try {
      const response = await axios.post(endpoints.changePassword, {
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmPassword
      });

      if (response.data.success) {
        presentToast({
          message: 'Password updated successfully!',
          duration: 2000,
          color: 'success'
        });
        setCurrentPassword('');
        setNewPassword('');
        setConfirmPassword('');
        setView('main');
      }
    } catch (e: any) {
      presentToast({
        message: e.response?.data?.message || 'Failed to update password',
        duration: 3000,
        color: 'danger'
      });
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAccount = async () => {
    const confirm = window.confirm('Are you sure you want to permanently delete your account? This cannot be undone.');
    if (!confirm) return;

    setLoading(true);
    try {
      const response = await axios.post(endpoints.deleteAccount);
      if (response.data.success) {
        presentToast({ message: 'Account deleted successfully.', duration: 2000, color: 'success' });
        setTimeout(() => logout(), 1500);
      } else {
        presentToast({ message: response.data.message || 'Failed to delete account.', duration: 3000, color: 'danger' });
      }
    } catch (e: any) {
      console.error('Delete account failed', e);
      presentToast({ message: e.response?.data?.message || 'Failed to delete account. Please try again.', duration: 3000, color: 'danger' });
    } finally {
      setLoading(false);
    }
  };

  const renderField = (
    icon: string,
    label: string,
    value: string,
    setter: (v: string) => void,
    placeholder: string,
    error?: string,
    type: string = 'text'
  ) => (
    <div style={{ marginBottom: '16px' }}>
      <label style={styles.label}>{label}</label>
      <div style={{ ...styles.inputWrap, border: error ? '1px solid #ef4444' : styles.inputWrap.border, marginBottom: '4px' }}>
        <IonIcon icon={icon} style={{ ...styles.inputIcon, color: error ? '#ef4444' : styles.inputIcon.color }} />
        <input
          type={type === 'password' ? 'password' : (type === 'date' ? 'date' : 'text')}
          value={value}
          maxLength={
            label.includes('Phone') ? 11 : 
            label.includes('Name') ? 50 : 
            label.includes('Address') ? 100 : 
            label.includes('License') ? 15 : undefined
          }
          inputMode={label.includes('Phone') ? 'tel' : 'text'}
          onInput={(e: any) => {
            const val = e.target.value;
            let sanitized = val;
            if (label === 'Full Name' || label === 'Contact Name') {
              sanitized = val.replace(/[^a-zA-ZñÑ\s.]/g, '');
            } else if (label.includes('Phone')) {
              sanitized = val.replace(/[^\d]/g, '').slice(0, 11);
            } else if (label === 'License Number') {
              sanitized = val.replace(/[^a-zA-Z0-9-]/g, '').toUpperCase();
            }
            
            e.target.value = sanitized; // Direct DOM update
            setter(sanitized); // React state update
          }}
          placeholder={placeholder}
          style={inputStyle}
        />
      </div>
      {error && <div style={{ fontSize: '10px', color: '#ef4444', fontWeight: '800', paddingLeft: '4px' }}>{error}</div>}
    </div>
  );

  return (
    <IonPage>
      <IonHeader className="ion-no-border">
        <IonToolbar style={{ '--background': t.headerBg, '--color': t.headerText }}>
          <IonButtons slot="start">
            <IonButton 
              color="warning" 
              onClick={() => view === 'main' ? ionRouter.back() : setView('main')}
            >
              <IonIcon icon={arrowBackOutline} />
            </IonButton>
          </IonButtons>
          <IonTitle style={{ fontWeight: 'bold' }}>
            {view === 'main' ? 'Settings' : (view === 'profile' ? 'Profile Update' : 'Change Password')}
          </IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen>
        <div style={{
          minHeight: '100%',
          background: isDark ? 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)' : 'linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%)',
          padding: '16px 20px 100px',
        }}>
          
          {fetching ? (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '200px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : (
            <>
              {/* ─── MAIN SETTINGS MENU ─── */}
              {view === 'main' && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                  
                  {/* Account Identity Card */}
                  <div style={{ ...styles.sectionCard, background: t.sectionCardBg, border: t.border, boxShadow: t.cardShadow, textAlign: 'center', padding: '40px 20px' }}>
                    <div style={{ 
                      width: '84px', height: '84px', borderRadius: '50%', 
                      background: t.goldGrad,
                      display: 'flex', alignItems: 'center', justifyContent: 'center',
                      margin: '0 auto 16px', border: isDark ? '4px solid rgba(255,255,255,0.05)' : '4px solid rgba(0,0,0,0.05)',
                      boxShadow: '0 8px 24px rgba(234, 179, 8, 0.2)'
                    }}>
                      <IonIcon icon={personOutline} style={{ fontSize: '44px', color: isDark ? '#020617' : '#fff' }} />
                    </div>
                    <h2 style={{ margin: '0 0 6px', color: t.textPrimary, fontSize: '22px', fontWeight: '800', letterSpacing: '-0.5px' }}>{name}</h2>
                    <p style={{ margin: 0, color: t.textSecondary, fontSize: '13px', fontWeight: '600' }}>{phone}</p>
                  </div>

                  {/* Settings Options */}
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                    {/* Theme Toggle */}
                    <div
                      onClick={toggleTheme}
                      style={{
                        display: 'flex', alignItems: 'center', padding: '16px',
                        background: t.menuBg, border: `1px solid ${t.menuBorder}`,
                        borderRadius: '20px', cursor: 'pointer', height: '72px',
                        transition: 'all 0.3s ease'
                      }}
                    >
                      <div style={{ display: 'flex', alignItems: 'center', width: '100%', gap: '16px' }}>
                        <div style={{ width: '40px', height: '40px', borderRadius: '12px', background: isDark ? 'rgba(251,191,36,0.1)' : 'rgba(99,102,241,0.1)', color: isDark ? '#fbbf24' : '#6366f1', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '20px', transition: 'all 0.3s' }}>
                          <IonIcon icon={isDark ? sunnyOutline : moonOutline} />
                        </div>
                        <span style={{ flex: 1, textAlign: 'left', fontSize: '15px', fontWeight: '700', color: t.textPrimary }}>
                          {isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'}
                        </span>
                        <div style={{
                          width: '52px', height: '28px', borderRadius: '14px',
                          background: isDark ? 'rgba(255,255,255,0.1)' : 'linear-gradient(135deg, #6366f1, #8b5cf6)',
                          position: 'relative', transition: 'background 0.3s',
                          border: isDark ? '1px solid rgba(255,255,255,0.1)' : '1px solid rgba(99,102,241,0.3)'
                        }}>
                          <div style={{
                            width: '22px', height: '22px', borderRadius: '50%',
                            background: isDark ? '#64748b' : '#fff',
                            position: 'absolute', top: '2px',
                            left: isDark ? '3px' : '25px',
                            transition: 'all 0.3s ease',
                            boxShadow: '0 2px 6px rgba(0,0,0,0.2)'
                          }} />
                        </div>
                      </div>
                    </div>



                    <IonButton 
                      expand="block" fill="clear"
                      onClick={() => setView('profile')}
                      style={{ ...menuItemStyle, '--background': t.menuBg, '--border-color': t.menuBorder } as any}
                    >
                      <div style={menuBtnInner}>
                        <div style={{ ...menuBtnIconWrap, background: t.goldBg, color: t.gold }}><IonIcon icon={personOutline} /></div>
                        <span style={{ ...menuBtnText, color: t.textPrimary }}>Update Profile</span>
                        <IonIcon icon={chevronForwardOutline} style={{ fontSize: '18px', opacity: 0.4 }} />
                      </div>
                    </IonButton>

                    <IonButton 
                      expand="block" fill="clear"
                      onClick={() => setView('password')}
                      style={{ ...menuItemStyle, '--background': t.menuBg, '--border-color': t.menuBorder } as any}
                    >
                      <div style={menuBtnInner}>
                        <div style={{ ...menuBtnIconWrap, background: t.goldBg, color: t.gold }}><IonIcon icon={lockClosedOutline} /></div>
                        <span style={{ ...menuBtnText, color: t.textPrimary }}>Change Password</span>
                        <IonIcon icon={chevronForwardOutline} style={{ fontSize: '18px', opacity: 0.4 }} />
                      </div>
                    </IonButton>

                    <IonButton 
                      expand="block" fill="clear"
                      onClick={() => {
                        const confirm = window.confirm('Log out from EuroTaxi?');
                        if (confirm) logout();
                      }}
                      style={{ ...menuItemStyle, '--background': 'rgba(239, 68, 68, 0.03)' } as any}
                    >
                      <div style={menuBtnInner}>
                        <div style={{ ...menuBtnIconWrap, background: 'rgba(239, 68, 68, 0.1)', color: '#ef4444' }}><IonIcon icon={logOutOutline} /></div>
                        <span style={{ ...menuBtnText, color: '#ef4444' }}>Log Out</span>
                      </div>
                    </IonButton>

                    <div style={{ height: '24px' }}></div>

                    <IonButton 
                      expand="block" fill="clear"
                      onClick={handleDeleteAccount}
                      style={{ ...menuItemStyle, '--background': 'rgba(239, 68, 68, 0.01)', height: '64px' } as any}
                    >
                      <div style={menuBtnInner}>
                        <div style={{ ...menuBtnIconWrap, background: 'rgba(239, 68, 68, 0.05)', color: '#ef4444', width: '32px', height: '32px', fontSize: '16px' }}><IonIcon icon={logOutOutline} /></div>
                        <span style={{ ...menuBtnText, color: '#ef4444', opacity: 0.7, fontSize: '14px' }}>Permanently Delete Account</span>
                      </div>
                    </IonButton>
                  </div>
                </div>
              )}

              {/* ─── PROFILE UPDATE VIEW ─── */}
              {view === 'profile' && (
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                  
                  {/* Part 1: Personal Info */}
                  <div style={styles.sectionCard}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                      <div style={styles.sectionDot}></div>
                      <span style={styles.sectionLabel}>Personal Info</span>
                    </div>
                    {renderField(personOutline, 'Full Name', name, setName, 'Enter full name', errors.name)}
                    {renderField(callOutline, 'Phone Number', phone, setPhone, '09123456789', errors.phone, 'tel')}
                    {renderField(homeOutline, 'Residential Address', address, setAddress, 'Street, City, Province', errors.address)}
                  </div>

                  {/* Part 2: License */}
                  <div style={styles.sectionCard}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                      <div style={styles.sectionDot}></div>
                      <span style={styles.sectionLabel}>License Details</span>
                    </div>
                    {renderField(cardOutline, 'License Number', licenseNumber, setLicenseNumber, 'e.g. N01-12-123456', errors.licenseNumber)}
                    {renderField(calendarOutline, 'License Expiry', licenseExpiry, setLicenseExpiry, '', undefined, 'date')}
                  </div>

                  {/* Part 3: Emergency Contact */}
                  <div style={styles.sectionCard}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                      <div style={styles.sectionDot}></div>
                      <span style={styles.sectionLabel}>Emergency Contact</span>
                    </div>
                    {renderField(peopleOutline, 'Contact Name', emergencyContact, setEmergencyContact, 'Name of relative')}
                    {renderField(callOutline, 'Emergency Phone', emergencyPhone, setEmergencyPhone, '09123456789', errors.emergencyPhone, 'tel')}
                  </div>

                  {/* Part 4: Documents & Verification */}
                  <div style={styles.sectionCard}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                      <div style={styles.sectionDot}></div>
                      <span style={styles.sectionLabel}>Documents & Verification</span>
                    </div>
                    {[
                      { id: 'license', label: 'Driver License', icon: cardOutline, path: licensePhoto },
                      { id: 'nbi', label: 'NBI Clearance', icon: documentTextOutline, path: nbiPhoto },
                      { id: 'pnp', label: 'PNP Clearance', icon: documentTextOutline, path: pnpPhoto },
                    ].map((doc) => (
                      <div key={doc.id} style={{ marginBottom: '12px', padding: '14px 16px', background: t.inputBg, borderRadius: '16px', border: `1px solid ${t.inputBorder}` }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                          <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                            <IonIcon icon={doc.icon} style={{ fontSize: '18px', color: '#eab308' }} />
                            <span style={{ fontSize: '13px', fontWeight: '700', color: t.textPrimary }}>{doc.label}</span>
                          </div>
                          <label style={{ cursor: 'pointer' }}>
                            <input type="file" accept="image/*" style={{ display: 'none' }} onChange={(e) => e.target.files?.[0] && handleFileUpload(doc.id as any, e.target.files[0])} />
                            <div style={{ 
                              fontSize: '11px', fontWeight: '800', 
                              color: doc.path ? '#22c55e' : '#eab308', 
                              textTransform: 'uppercase',
                              background: doc.path ? 'rgba(34,197,94,0.1)' : 'rgba(234,179,8,0.1)',
                              padding: '4px 8px', borderRadius: '6px'
                            }}>
                              {uploading === doc.id ? '...' : (doc.path ? 'Verified ✓' : 'Upload +')}
                            </div>
                          </label>
                        </div>
                      </div>
                    ))}
                  </div>

                  <IonButton 
                    expand="block" onClick={handleUpdateProfile} disabled={loading}
                    style={saveBtnStyle as any}
                  >
                    <IonIcon slot="start" icon={saveOutline} />
                    Save Profile Changes
                  </IonButton>
                </div>
              )}

              {/* ─── PASSWORD CHANGE VIEW ─── */}
              {view === 'password' && (
                <div style={styles.sectionCard}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '24px' }}>
                    <div style={styles.sectionDot}></div>
                    <span style={styles.sectionLabel}>Security Update</span>
                  </div>
                  {renderField(lockClosedOutline, 'Current Password', currentPassword, setCurrentPassword, 'Enter current password', undefined, 'password')}
                  <div style={{ height: '8px' }}></div>
                  {renderField(lockClosedOutline, 'New Password', newPassword, setNewPassword, 'Min. 8 characters', undefined, 'password')}
                  {renderField(lockClosedOutline, 'Confirm New Password', confirmPassword, setConfirmPassword, 'Repeat new password', undefined, 'password')}
                  
                  <IonButton 
                    expand="block" onClick={handleChangePassword} disabled={loading}
                    style={saveBtnStyle as any}
                  >
                    Update Password
                  </IonButton>
                </div>
              )}
            </>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
};

/* ── Menu Styles ── */
const menuItemStyle = {
  '--padding-top': '16px',
  '--padding-bottom': '16px',
  '--padding-start': '16px',
  '--padding-end': '16px',
  '--border-radius': '20px',
  '--border-width': '1px',
  '--border-color': 'rgba(255,255,255,0.06)',
  '--border-style': 'solid',
  height: '72px',
  margin: 0,
};

const menuBtnInner = {
  display: 'flex', alignItems: 'center', width: '100%', gap: '16px',
};

const menuBtnIconWrap = {
  width: '40px', height: '40px', borderRadius: '12px',
  background: 'rgba(234, 179, 8, 0.1)', color: '#eab308',
  display: 'flex', alignItems: 'center', justifyContent: 'center',
  fontSize: '20px'
};

const menuBtnText = {
  flex: 1, textAlign: 'left' as const, fontSize: '15px', fontWeight: '700', color: '#f8fafc' as string
};

const saveBtnStyle = {
  '--border-radius': '16px',
  '--background': 'linear-gradient(135deg, #eab308, #f59e0b)',
  '--color': '#020617',
  height: '56px',
  fontWeight: '800',
  marginTop: '16px',
  boxShadow: '0 8px 24px rgba(234, 179, 8, 0.25)'
};

export default Settings;
