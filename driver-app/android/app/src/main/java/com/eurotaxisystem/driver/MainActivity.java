package com.eurotaxisystem.driver;

import android.os.Bundle;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.media.AudioAttributes;
import android.net.Uri;
import android.content.ContentResolver;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        createNotificationChannel();
    }

    private void createNotificationChannel() {
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
            String channelId = "driver_alerts_v7";
            CharSequence name = "EuroTaxi Urgent Alerts";
            String description = "Critical alerts for drivers (New Bookings/Support)";
            int importance = NotificationManager.IMPORTANCE_HIGH;
            NotificationChannel channel = new NotificationChannel(channelId, name, importance);
            channel.setDescription(description);
            channel.enableLights(true);
            channel.enableVibration(true);
            channel.setVibrationPattern(new long[]{0, 500, 200, 500, 200, 500});
            channel.setLockscreenVisibility(android.app.Notification.VISIBILITY_PUBLIC);

            // Set custom sound using Resource ID (more reliable)
            Uri soundUri = Uri.parse("android.resource://" + getPackageName() + "/raw/driver_alert");
            AudioAttributes audioAttributes = new AudioAttributes.Builder()
                    .setContentType(AudioAttributes.CONTENT_TYPE_SONIFICATION)
                    .setUsage(AudioAttributes.USAGE_ALARM) // Use ALARM for louder/more persistent sound
                    .build();
            channel.setSound(soundUri, audioAttributes);

            NotificationManager notificationManager = getSystemService(NotificationManager.class);
            if (notificationManager != null) {
                // Wipe all old IDs to prevent conflicts
                notificationManager.deleteNotificationChannel("urgent_alerts");
                notificationManager.deleteNotificationChannel("custom_alerts");
                notificationManager.deleteNotificationChannel("eurotaxi_final_v1");
                notificationManager.deleteNotificationChannel("driver_alerts_v5");
                notificationManager.deleteNotificationChannel("driver_alerts_urgent_v2");
                notificationManager.deleteNotificationChannel(channelId);

                notificationManager.createNotificationChannel(channel);
                android.util.Log.d("EuroTaxi", "Notification channel 'driver_alerts_v6' created successfully!");
            }
        }
    }
}
