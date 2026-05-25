import React, { useState, useEffect } from 'react'
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonItem, IonLabel, IonInput, IonButton } from '@ionic/react'
import { createUnit, updateUnit, getUnits } from '../api'
import { useNavigate, useParams } from 'react-router-dom'

export default function UnitForm(){
  const { id } = useParams()
  const isEdit = !!id
  const [unitNumber, setUnitNumber] = useState('')
  const [plate, setPlate] = useState('')
  const nav = useNavigate()

  useEffect(()=>{
    if(isEdit){
      (async ()=>{
        const res = await getUnits()
        const u = res.data.find((x:any)=>String(x.id)===id)
        if(u){ setUnitNumber(u.unit_number); setPlate(u.plate_number) }
      })()
    }
  },[id])

  async function handleSave(){
    const payload = { unit_number: unitNumber, plate_number: plate }
    try{
      if(isEdit) await updateUnit(Number(id), payload)
      else await createUnit(payload)
      nav('/units')
    }catch(e:any){ alert(e.response?.data?.message || e.message) }
  }

  return (
    <IonPage>
      <IonHeader>
        <IonToolbar>
          <IonTitle>{isEdit? 'Edit Unit':'Add Unit'}</IonTitle>
        </IonToolbar>
      </IonHeader>
      <IonContent className="ion-padding">
        <IonItem>
          <IonLabel position="stacked">Unit Number</IonLabel>
          <IonInput value={unitNumber} onIonChange={(e:any)=>setUnitNumber(e.target.value)} />
        </IonItem>
        <IonItem>
          <IonLabel position="stacked">Plate Number</IonLabel>
          <IonInput value={plate} onIonChange={(e:any)=>setPlate(e.target.value)} />
        </IonItem>
        <IonButton onClick={handleSave} className="ion-margin-top">Save</IonButton>
      </IonContent>
    </IonPage>
  )
}
