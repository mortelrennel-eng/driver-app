import React, { useState } from 'react'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonItem, IonLabel, IonInput, IonButton } from '@ionic/react'
import { createDriver } from '../api'
import { useNavigate } from 'react-router-dom'

export default function DriverForm(){
  const [userId, setUserId] = useState('')
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [license, setLicense] = useState('')
  const [contact, setContact] = useState('')
  const nav = useNavigate()

  async function handleSave(){
    try{
      const payload: any = { license_number: license, contact_number: contact }
      if (userId) payload.user_id = Number(userId)
      else payload.name = name; payload.email = email; payload.password = password

      await createDriver(payload)
      nav('/drivers')
    }catch(e:any){ alert(e.response?.data?.message || e.message) }
  }

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>Add Driver</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonItem>
          <IonLabel position="stacked">Existing User ID (leave blank to create new user)</IonLabel>
          <IonInput value={userId} onIonChange={(e:any)=>setUserId(e.target.value)} />
        </IonItem>

        {!userId && (
          <>
            <IonItem>
              <IonLabel position="stacked">Name</IonLabel>
              <IonInput value={name} onIonChange={(e:any)=>setName(e.target.value)} />
            </IonItem>
            <IonItem>
              <IonLabel position="stacked">Email</IonLabel>
              <IonInput value={email} onIonChange={(e:any)=>setEmail(e.target.value)} />
            </IonItem>
            <IonItem>
              <IonLabel position="stacked">Password</IonLabel>
              <IonInput type="password" value={password} onIonChange={(e:any)=>setPassword(e.target.value)} />
            </IonItem>
          </>
        )}

        <IonItem>
          <IonLabel position="stacked">License Number</IonLabel>
          <IonInput value={license} onIonChange={(e:any)=>setLicense(e.target.value)} />
        </IonItem>
        <IonItem>
          <IonLabel position="stacked">Contact Number</IonLabel>
          <IonInput value={contact} onIonChange={(e:any)=>setContact(e.target.value)} />
        </IonItem>
        <IonButton onClick={handleSave} className="ion-margin-top">Save</IonButton>
      </IonContent>
    </IonPage>
  )
}
