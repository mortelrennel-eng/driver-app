import React, { useEffect, useState } from 'react'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonList, IonItem, IonLabel, IonButton } from '@ionic/react'
import { getDrivers, deleteDriver } from '../api'
import { useNavigate } from 'react-router-dom'

export default function Drivers(){
  const [drivers, setDrivers] = useState<any[]>([])
  const nav = useNavigate()

  async function load(){
    try{
      const res = await getDrivers()
      if(res.success) setDrivers(res.data)
    }catch(e){console.error(e)}
  }

  useEffect(()=>{load()},[])

  async function handleDelete(id:number){
    if(!confirm('Delete this driver?')) return
    await deleteDriver(id)
    load()
  }

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>Drivers</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonButton onClick={()=>nav('/drivers/new')}>Add Driver</IonButton>
        <IonList>
          {drivers.map(d=> (
            <IonItem key={d.id}>
              <IonLabel>{d.name} — {d.email}</IonLabel>
              <IonButton slot="end" onClick={()=>nav(`/drivers/${d.id}/edit`)}>Edit</IonButton>
              <IonButton slot="end" color="danger" onClick={()=>handleDelete(d.id)}>Delete</IonButton>
            </IonItem>
          ))}
        </IonList>
      </IonContent>
    </IonPage>
  )
}
