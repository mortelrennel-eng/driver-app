import { Capacitor } from '@capacitor/core';
import api from './api';
import { toast } from 'sonner';

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
    await PushNotifications.register();

    // 3. Listeners for Token and Errors
    await PushNotifications.addListener('registration', async (token) => {
      console.log('FCM Device Token retrieved successfully:', token.value);
      
      // Save token to Laravel backend
      try {
        const response = await api.post('/notifications/save-token', {
          token: token.value
        });
        if (response.data.success) {
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
      
      // Show beautiful visual alert inside the active app
      toast.info(notification.title || 'System Alert', {
        description: notification.body || '',
        duration: 8000,
      });
    });

    // 5. Listener for Action Click (user clicks the notification banner in tray)
    await PushNotifications.addListener('pushNotificationActionPerformed', (notification) => {
      console.log('Push notification click action triggered:', notification);
      // Navigate to notifications screen if necessary
      if (window.location.pathname !== '/dashboard') {
        window.location.href = '/dashboard';
      }
    });

  } catch (error) {
    console.error('Error initializing Capacitor push notifications:', error);
  }
}
