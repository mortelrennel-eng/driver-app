import React, { useState } from 'react'
import { IonContent, IonHeader, IonPage, IonTitle, IonToolbar, IonItem, IonLabel, IonInput, IonButton, IonToast } from '@ionic/react'
import { login } from '../api'
import { useNavigate } from 'react-router-dom'

export default function Login() {
  const [identifier, setIdentifier] = useState('')
  const [password, setPassword] = useState('')
  const [otp, setOtp] = useState('')
  const [toast, setToast] = useState<{show:boolean; message:string}>({show:false, message:''})
  const nav = useNavigate()

  async function handleLogin() {
    try {
      const res = await login(identifier, password || undefined, otp || undefined)
      if (res.success) {
        localStorage.setItem('token', res.data.token)
        setToast({show:true, message:'Login successful'})
        nav('/dashboard')
      } else {
        setToast({show:true, message: res.message || 'Login failed'})
      }
    } catch (e: any) {
      setToast({show:true, message: e.response?.data?.message || e.message})
    }
  }

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>EuroTaxi Login</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonItem>
          <IonLabel position="stacked">Email or Phone</IonLabel>
          <IonInput value={identifier} onIonChange={(e:any)=>setIdentifier(e.target.value)} />
        </IonItem>
        <IonItem>
          <IonLabel position="stacked">Password (or leave blank for OTP)</IonLabel>
          <IonInput type="password" value={password} onIonChange={(e:any)=>setPassword(e.target.value)} />
        </IonItem>
        <IonItem>
          <IonLabel position="stacked">OTP (optional)</IonLabel>
          <IonInput value={otp} onIonChange={(e:any)=>setOtp(e.target.value)} />
        </IonItem>
        <IonButton expand="block" onClick={handleLogin} className="ion-margin-top">Login</IonButton>
        <IonToast isOpen={toast.show} message={toast.message} duration={2000} onDidDismiss={()=>setToast({show:false,message:''})} />
      </IonContent>
    </IonPage>
  )
}
