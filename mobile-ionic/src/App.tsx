import React from 'react'
import { Route, Routes, Navigate } from 'react-router-dom'
import { IonPage } from '@ionic/react'
import Login from './pages/Login'
import Dashboard from './pages/Dashboard'
import Units from './pages/Units'
import UnitForm from './pages/UnitForm'
import Drivers from './pages/Drivers'
import DriverForm from './pages/DriverForm'

function App() {
  const isLoggedIn = !!localStorage.getItem('token')

  return (
    <IonPage>
      <Routes>
        <Route path="/login" element={<Login />} />
        <Route path="/dashboard" element={isLoggedIn ? <Dashboard /> : <Navigate to="/login" />} />
        <Route path="/units" element={isLoggedIn ? <Units /> : <Navigate to="/login" />} />
        <Route path="/units/new" element={isLoggedIn ? <UnitForm /> : <Navigate to="/login" />} />
        <Route path="/units/:id/edit" element={isLoggedIn ? <UnitForm /> : <Navigate to="/login" />} />
        <Route path="/drivers" element={isLoggedIn ? <Drivers /> : <Navigate to="/login" />} />
        <Route path="/drivers/new" element={isLoggedIn ? <DriverForm /> : <Navigate to="/login" />} />
        <Route path="/drivers/:id/edit" element={isLoggedIn ? <DriverForm /> : <Navigate to="/login" />} />
        <Route path="/" element={<Navigate to={isLoggedIn ? '/dashboard' : '/login'} />} />
      </Routes>
    </IonPage>
  )
}

export default App
