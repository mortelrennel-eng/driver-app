import { useEffect, useRef } from 'react';
import { Geolocation } from '@capacitor/geolocation';
import { Device } from '@capacitor/device';
import axios from 'axios';
import { endpoints } from '../config/api';
import { useAuth } from '../context/AuthContext';

export const useGpsTracking = (intervalMs: number = 60000) => {
  const { token } = useAuth();
  const timerRef = useRef<any>(null);

  const updateLocation = async () => {
    if (!token) return;

    try {
      const position = await Geolocation.getCurrentPosition({
        enableHighAccuracy: true,
        timeout: 10000
      });

      const deviceInfo = await Device.getId();

      if (position) {
        await axios.post(endpoints.driverLocation, {
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          speed: position.coords.speed,
          heading: position.coords.heading?.toString(),
          device_id: deviceInfo.identifier
        });
        console.log('Location updated with security context:', position.coords.latitude, position.coords.longitude);
      }
    } catch (e) {
      console.error('Error updating location:', e);
    }
  };

  useEffect(() => {
    if (token) {
      // Immediate update
      updateLocation();
      
      // Setup interval
      timerRef.current = setInterval(updateLocation, intervalMs);
    } else {
      if (timerRef.current) {
        clearInterval(timerRef.current);
      }
    }

    return () => {
      if (timerRef.current) {
        clearInterval(timerRef.current);
      }
    };
  }, [token]);

  return { updateLocation };
};
