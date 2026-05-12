import { Capacitor } from '@capacitor/core';
import { PushNotifications } from '@capacitor/push-notifications';
import axios from 'axios';
import { endpoints } from '../config/api';

export const initPushNotifications = async () => {
  // Only register if on a native platform (Android/iOS)
  if (!Capacitor.isNativePlatform()) {
    console.log('Push notifications skipped: Not on a native device platform.');
    return;
  }

  try {
    let permStatus = await PushNotifications.checkPermissions();

    if (permStatus.receive === 'prompt') {
      permStatus = await PushNotifications.requestPermissions();
    }

    if (permStatus.receive !== 'granted') {
      console.warn('User denied push notification permissions.');
      return;
    }

    console.log('Notification channels are now managed natively in MainActivity.java');

    // Register with FCM
    await PushNotifications.register();

    // On success, we should be able to receive notifications
    PushNotifications.addListener('registration', (token) => {
      console.log('Push registration success, token: ' + token.value);
      saveToken(token.value);
    });

    // Some issue with our setup and push will not work
    PushNotifications.addListener('registrationError', (error: any) => {
      console.error('Error on registration: ' + JSON.stringify(error));
    });

    // Handle notification received in foreground
    PushNotifications.addListener('pushNotificationReceived', (notification) => {
      console.log('Push received in foreground:', notification);
      
      // Play custom sound manually for foreground notifications
      try {
        const audio = new Audio('assets/sounds/driver_alert.mp3');
        audio.play().catch(e => console.error('Audio play failed:', e));
      } catch (e) {
        console.error('Failed to play foreground sound:', e);
      }
    });

    // Method called when tapping on a notification
    PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('Push action performed: ' + JSON.stringify(notification));
    });

  } catch (e) {
    console.error('Push notification initialization failed', e);
  }
};

const saveToken = async (token: string) => {
  try {
    const authHeader = localStorage.getItem('auth_token');
    if (!authHeader) return;

    await axios.post(endpoints.saveNotificationToken, { token }, {
      headers: { Authorization: `Bearer ${authHeader}` }
    });
    console.log('FCM Token saved to server successfully');
  } catch (e) {
    console.error('Failed to save FCM token to server', e);
  }
};
