import React, { useEffect, useState } from 'react'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonCard, IonCardHeader, IonCardTitle, IonList, IonItem } from '@ionic/react'
import { getDashboard } from '../api'

export default function Dashboard(){
  const [stats, setStats] = useState<any>({})

  useEffect(()=>{
    (async ()=>{
      try{
        const res = await getDashboard()
        if(res.success) setStats(res.data)
      }catch(e){console.error(e)}
    })()
  },[])

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>Dashboard</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonCard>
          <IonCardHeader>
            <IonCardTitle>Summary</IonCardTitle>
          </IonCardHeader>
          <IonList>
            <IonItem>Active Units: {stats.active_units ?? '—'}</IonItem>
            <IonItem>Coding Units: {stats.coding_units ?? '—'}</IonItem>
            <IonItem>Maintenance Units: {stats.maintenance_units ?? '—'}</IonItem>
            <IonItem>Today Boundary: {stats.today_boundary ?? '—'}</IonItem>
            <IonItem>Today Expenses: {stats.today_expenses ?? '—'}</IonItem>
            <IonItem>Net Income: {stats.net_income ?? '—'}</IonItem>
            <IonItem>Active Drivers: {stats.active_drivers ?? '—'}</IonItem>
          </IonList>
        </IonCard>
      </IonContent>
    </IonPage>
  )
}
