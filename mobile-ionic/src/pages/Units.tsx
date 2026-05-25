import React, { useEffect, useState } from 'react'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonList, IonItem, IonLabel, IonButton } from '@ionic/react'
import { getUnits, deleteUnit } from '../api'
import { useNavigate } from 'react-router-dom'

export default function Units(){
  const [units, setUnits] = useState<any[]>([])
  const nav = useNavigate()

  async function load(){
    try{
      const res = await getUnits()
      if(res.success) setUnits(res.data)
    }catch(e){console.error(e)}
  }

  useEffect(()=>{load()},[])

  async function handleDelete(id:number){
    if(!confirm('Delete this unit?')) return
    await deleteUnit(id)
    load()
  }

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>Units</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonButton onClick={()=>nav('/units/new')}>Add Unit</IonButton>
        <IonList>
          {units.map(u=> (
            <IonItem key={u.id}>
              <IonLabel>{u.unit_number} — {u.plate_number}</IonLabel>
              <IonButton slot="end" onClick={()=>nav(`/units/${u.id}/edit`)}>Edit</IonButton>
              <IonButton slot="end" color="danger" onClick={()=>handleDelete(u.id)}>Delete</IonButton>
            </IonItem>
          ))}
        </IonList>
      </IonContent>
    </IonPage>
  )
}
