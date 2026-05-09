import {
  IonContent,
  IonHeader,
  IonPage,
  IonTitle,
  IonToolbar,
  IonButton,
  IonButtons,
  IonBackButton,
  IonIcon,
  IonSpinner,
  useIonToast
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
  cloudUploadOutline,
  documentTextOutline,
  checkmarkCircleOutline,
  logOutOutline
} from 'ionicons/icons';
import React, { useState } from 'react';
import type { FC } from 'react';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useAuth } from '../context/AuthContext';

/* ── Shared Styles ── */
const styles = {
  sectionCard: {
    background: 'linear-gradient(145deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.9))',
    backdropFilter: 'blur(10px)',
    WebkitBackdropFilter: 'blur(10px)',
    border: '1px solid rgba(255, 255, 255, 0.06)',
    boxShadow: '0 8px 32px rgba(0, 0, 0, 0.3)',
    borderRadius: '24px',
    padding: '20px',
    marginBottom: '16px',
  } as React.CSSProperties,
  label: {
    display: 'block',
    fontSize: '11px',
    fontWeight: '700',
    color: '#94a3b8',
    textTransform: 'uppercase' as const,
    letterSpacing: '1.5px',
    marginBottom: '8px',
    paddingLeft: '4px',
  },
  inputWrap: {
    position: 'relative' as const,
    background: 'rgba(15, 23, 42, 0.6)',
    border: '1px solid rgba(51, 65, 85, 0.8)',
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
    color: '#eab308',
    zIndex: 2,
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
  paddingLeft: '48px',
  paddingRight: '16px',
  color: '#f8fafc',
  background: 'transparent',
  height: '52px',
  fontSize: '15px',
  border: 'none',
  width: '100%',
  outline: 'none',
  fontWeight: '500'
} as any;

const Settings: FC = () => {
  const [presentToast] = useIonToast();
  const { refreshUser, logout } = useAuth();
  
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

  const validate = () => {
    const newErrors: any = {};
    
    // Name
    if (!name || name.trim().length < 3) {
      newErrors.name = 'Full name must be at least 3 characters';
    } else if (/\d/.test(name)) {
      newErrors.name = 'Name should not contain numbers';
    }

    // Phone
    const phoneRegex = /^09\d{9}$/;
    if (!phoneRegex.test(phone)) {
      newErrors.phone = 'Must be 09XXXXXXXXX (11 digits)';
    }

    // Address
    if (!address || address.trim().length < 10) {
      newErrors.address = 'Provide a more detailed address';
    }

    // License (More flexible to allow TBD- formats)
    const licenseRegex = /^[A-Z0-9-]+$/i;
    if (licenseNumber && !licenseRegex.test(licenseNumber)) {
      newErrors.licenseNumber = 'Invalid characters (Use A-Z, 0-9, and - only)';
    }

    // Emergency Phone
    if (emergencyPhone && !phoneRegex.test(emergencyPhone)) {
      newErrors.emergencyPhone = 'Invalid phone format';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleUpdateProfile = async () => {
    if (!validate()) {
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
    if (!currentPassword || !newPassword) {
      presentToast({ message: 'Please fill in both password fields', duration: 2000, color: 'warning' });
      return;
    }

    setLoading(true);
    try {
      const response = await axios.post(endpoints.changePassword, {
        current_password: currentPassword,
        new_password: newPassword
      });

      if (response.data.success) {
        presentToast({
          message: 'Password updated successfully!',
          duration: 2000,
          color: 'success'
        });
        setCurrentPassword('');
        setNewPassword('');
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
          type={type === 'date' ? 'date' : 'text'}
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
              sanitized = val.replace(/[^a-zA-Z\s.]/g, '');
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
        <IonToolbar style={{ '--background': '#0f172a', '--color': 'white' }}>
          <IonButtons slot="start">
            <IonBackButton defaultHref="/dashboard" color="warning" />
          </IonButtons>
          <IonTitle style={{ fontWeight: 'bold' }}>Settings</IonTitle>
        </IonToolbar>
      </IonHeader>

      <IonContent fullscreen>
        <div style={{
          minHeight: '100%',
          background: 'linear-gradient(180deg, #0f172a 0%, #1e293b 100%)',
          padding: '16px 20px 40px',
        }}>
          
          {fetching ? (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '200px' }}>
              <IonSpinner name="crescent" color="warning" />
            </div>
          ) : (
            <>
              {/* Profile Info Section */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Personal Information</span>
                </div>
                
                {renderField(personOutline, 'Full Name', name, setName, 'Enter full name', errors.name)}
                {renderField(callOutline, 'Phone Number', phone, setPhone, '09123456789', errors.phone, 'tel')}
                {renderField(homeOutline, 'Residential Address', address, setAddress, 'Street, City, Province', errors.address)}
              </div>

              {/* License Section */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>License Details</span>
                </div>
                {renderField(cardOutline, 'License Number', licenseNumber, setLicenseNumber, 'e.g. N01-12-123456', errors.licenseNumber)}
                {renderField(calendarOutline, 'License Expiry', licenseExpiry, setLicenseExpiry, '', undefined, 'date')}
              </div>

              {/* Emergency Section */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Emergency Contact</span>
                </div>
                {renderField(peopleOutline, 'Contact Name', emergencyContact, setEmergencyContact, 'Name of relative')}
                {renderField(callOutline, 'Contact Phone', emergencyPhone, setEmergencyPhone, '09123456789', errors.emergencyPhone, 'tel')}
              </div>

              {/* Documents & Verification Section */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Documents & Verification</span>
                </div>

                {[
                  { id: 'license', label: 'Professional License', icon: cardOutline, path: licensePhoto },
                  { id: 'nbi', label: 'NBI Clearance', icon: documentTextOutline, path: nbiPhoto },
                  { id: 'pnp', label: 'PNP Clearance', icon: documentTextOutline, path: pnpPhoto },
                ].map((doc) => (
                  <div key={doc.id} style={{ marginBottom: '20px', padding: '16px', background: 'rgba(15, 23, 42, 0.4)', borderRadius: '16px', border: '1px solid rgba(255,255,255,0.04)' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                        <IonIcon icon={doc.icon} style={{ fontSize: '18px', color: '#eab308' }} />
                        <span style={{ fontSize: '14px', fontWeight: '700', color: '#f8fafc' }}>{doc.label}</span>
                      </div>
                      {doc.path ? (
                        <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#22c55e' }}>
                          <IonIcon icon={checkmarkCircleOutline} />
                          <span style={{ fontSize: '10px', fontWeight: '800', textTransform: 'uppercase' }}>Uploaded</span>
                        </div>
                      ) : (
                        <div style={{ color: '#f59e0b', fontSize: '10px', fontWeight: '800', textTransform: 'uppercase' }}>Missing</div>
                      )}
                    </div>
                    
                    <div style={{ display: 'flex', gap: '10px' }}>
                      <label style={{ flex: 1 }}>
                        <input 
                          type="file" 
                          accept="image/*" 
                          style={{ display: 'none' }} 
                          onChange={(e) => e.target.files?.[0] && handleFileUpload(doc.id as any, e.target.files[0])}
                        />
                        <div style={{ 
                          width: '100%', 
                          padding: '10px', 
                          background: 'rgba(234,179,8,0.1)', 
                          border: '1px dashed rgba(234,179,8,0.3)', 
                          borderRadius: '12px', 
                          color: '#eab308', 
                          fontSize: '12px', 
                          fontWeight: '700', 
                          textAlign: 'center',
                          display: 'flex',
                          alignItems: 'center',
                          justifyContent: 'center',
                          gap: '6px',
                          cursor: 'pointer'
                        }}>
                          {uploading === doc.id ? <IonSpinner name="crescent" style={{ width: '14px', height: '14px' }} /> : <><IonIcon icon={cloudUploadOutline} /> {doc.path ? 'Update Photo' : 'Upload Photo'}</>}
                        </div>
                      </label>
                    </div>
                  </div>
                ))}
              </div>

              <IonButton 
                expand="block" 
                onClick={handleUpdateProfile} 
                disabled={loading}
                style={{
                  '--border-radius': '16px',
                  '--background': 'linear-gradient(135deg, #eab308, #f59e0b)',
                  '--color': '#020617',
                  height: '52px',
                  fontWeight: '700',
                  marginBottom: '24px'
                } as any}
              >
                <IonIcon slot="start" icon={saveOutline} />
                Save Profile Changes
              </IonButton>

              {/* Security Section */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Security</span>
                </div>
                {renderField(lockClosedOutline, 'Current Password', currentPassword, setCurrentPassword, '••••••••', 'password')}
                {renderField(lockClosedOutline, 'New Password', newPassword, setNewPassword, '••••••••', 'password')}
                
                <IonButton 
                  expand="block" 
                  fill="outline"
                  onClick={handleChangePassword} 
                  disabled={loading}
                  style={{
                    '--border-radius': '16px',
                    '--color': '#eab308',
                    '--border-color': '#eab308',
                    height: '52px',
                    fontWeight: '700',
                    marginTop: '8px'
                  } as any}
                >
                  Update Password
                </IonButton>
              </div>

              {/* Account Actions */}
              <div style={styles.sectionCard}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '20px' }}>
                  <div style={styles.sectionDot}></div>
                  <span style={styles.sectionLabel}>Account Session</span>
                </div>
                
                <IonButton 
                  expand="block" 
                  onClick={() => {
                    const confirm = window.confirm('Are you sure you want to log out?');
                    if (confirm) logout();
                  }}
                  style={{
                    '--border-radius': '16px',
                    '--background': 'rgba(239, 68, 68, 0.1)',
                    '--color': '#ef4444',
                    '--border-color': '#ef4444',
                    '--border-style': 'solid',
                    '--border-width': '1px',
                    height: '52px',
                    fontWeight: '700',
                  } as any}
                >
                  <IonIcon slot="start" icon={logOutOutline} />
                  Log Out from App
                </IonButton>
              </div>

              {/* Danger Zone */}
              <div style={{ marginTop: '32px', textAlign: 'center' }}>
                <button 
                  onClick={handleDeleteAccount}
                  style={{
                    background: 'transparent',
                    border: 'none',
                    color: '#ef4444',
                    fontSize: '13px',
                    fontWeight: '600',
                    textDecoration: 'underline',
                    cursor: 'pointer',
                    opacity: 0.8
                  }}
                >
                  Permanently Delete My Account
                </button>
                <div style={{ color: '#475569', fontSize: '10px', marginTop: '8px' }}>
                  Deleting your account will unlink your profile from the system.
                </div>
              </div>
            </>
          )}

        </div>
      </IonContent>
    </IonPage>
  );
};

export default Settings;
