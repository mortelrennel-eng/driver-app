import { Capacitor } from '@capacitor/core';
import axios from 'axios';
import { endpoints } from '../config/api';
import { toast } from 'sonner'; // Need to ensure sonner is installed or just use IonToast if not.
// For Ionic apps without sonner, we can use window.dispatchEvent to show a toast, or a generic alert.

/**
 * Initialize Push Notifications for native devices (Android/iOS)
 */
export async function initPushNotifications() {
  // If running in a web browser, skip push notification registration gracefully
  if (!Capacitor.isNativePlatform()) {
    console.log('Push notifications: Standard web environment detected. Native push skipped.');
    return;
  }

  try {
    // Dynamic import to prevent bundler issues in standard web builds
    const { PushNotifications } = await import('@capacitor/push-notifications');

    // 1. Check current permission status
    let permStatus = await PushNotifications.checkPermissions();

    if (permStatus.receive === 'prompt') {
      permStatus = await PushNotifications.requestPermissions();
    }

    if (permStatus.receive !== 'granted') {
      console.warn('Push notification permission denied by user.');
      return;
    }

    // 2. Register with Apple/Google FCM Servers
    // IMPORTANT: This requires android/app/google-services.json to be present
    try {
      await PushNotifications.register();
    } catch (e) {
      console.error('Push registration failed. Ensure google-services.json is present in android/app/', e);
      return;
    }

    // 3. Listeners for Token and Errors
    await PushNotifications.addListener('registration', async (token) => {
      console.log('FCM Device Token retrieved successfully:', token.value);
      
      // Save token to Laravel backend
      try {
        const localToken = localStorage.getItem('token');
        if (localToken) {
          await axios.post(endpoints.saveNotificationToken, { token: token.value }, {
            headers: { Authorization: `Bearer ${localToken}` }
          });
          console.log('FCM Token successfully synced with Laravel database.');
        }
      } catch (err) {
        console.error('Failed to sync FCM Token with Laravel backend:', err);
      }
    });

    await PushNotifications.addListener('registrationError', (error) => {
      console.error('FCM Registration Error:', JSON.stringify(error));
    });

    // 4. Listener for Foreground Notification (app is OPEN and active)
    await PushNotifications.addListener('pushNotificationReceived', (notification) => {
      console.log('Push Notification received in Foreground:', notification);
      
      // Attempt to show alert if sonner exists or fallback
      try {
        if (typeof toast !== 'undefined' && toast.info) {
          toast.info(notification.title || 'System Alert', {
            description: notification.body || '',
            duration: 8000,
          });
        } else {
          alert(`${notification.title}\n${notification.body}`);
        }
      } catch (e) {
        // Fallback
        alert(`${notification.title}\n${notification.body}`);
      }
    });

    // 5. Listener for Action Click (user clicks the notification banner in tray)
    await PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('Push notification click action triggered:', notification);
      // Navigate to notifications/announcements screen
      if (window.location.pathname !== '/announcements') {
        window.location.href = '/announcements';
      }
    });

  } catch (error) {
    console.error('Error initializing Capacitor push notifications:', error);
  }
}
