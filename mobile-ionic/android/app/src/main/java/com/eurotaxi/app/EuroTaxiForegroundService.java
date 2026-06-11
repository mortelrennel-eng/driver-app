package com.eurotaxi.app;

import android.app.Notification;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.app.Service;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Build;
import android.os.IBinder;
import android.util.Log;
import androidx.core.app.NotificationCompat;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.HashSet;
import java.util.Set;
import org.json.JSONArray;
import org.json.JSONObject;

public class EuroTaxiForegroundService extends Service {
    private static final String TAG = "EuroTaxiForeground";
    private static final String CHANNEL_ID = "eurotaxi_foreground_channel";
    private Thread pollingThread;
    private boolean isRunning = false;

    @Override
    public void onCreate() {
        super.onCreate();
        Log.d(TAG, "Foreground Service onCreate called");
        createNotificationChannel();
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Log.d(TAG, "Foreground Service onStartCommand called");
        
        if (!isRunning) {
            isRunning = true;
            startForegroundNotification();
            startPollingThread();
        }
        
        return START_STICKY;
    }

    private void startForegroundNotification() {
        int iconResourceId = getResources().getIdentifier("ic_launcher", "mipmap", getPackageName());
        int smallIcon = iconResourceId > 0 ? iconResourceId : android.R.drawable.ic_dialog_info;

        Notification notification = new NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("EuroTaxi Active Tracking")
            .setContentText("EuroTaxi Background System is monitoring real-time alerts...")
            .setSmallIcon(smallIcon)
            .setOngoing(true)
            .setPriority(NotificationCompat.PRIORITY_LOW)
            .setCategory(NotificationCompat.CATEGORY_SERVICE)
            .build();

        // In Android 14+, we specify the foreground service type programmatically as well when calling startForeground
        if (Build.VERSION.SDK_INT >= 34) {
            startForeground(101, notification, android.content.pm.ServiceInfo.FOREGROUND_SERVICE_TYPE_SPECIAL_USE);
        } else {
            startForeground(101, notification);
        }
    }

    private void startPollingThread() {
        pollingThread = new Thread(new Runnable() {
            @Override
            public void run() {
                Log.d(TAG, "Foreground Polling Thread Started.");
                SharedPreferences prefs = getSharedPreferences(EuroTaxiMessagingService.PREFS_NAME, MODE_PRIVATE);
                
                while (isRunning) {
                    try {
                        // Wait 8 seconds between polls
                        Thread.sleep(8000);
                        
                        // Dynamically load user_id from shared preferences
                        String userId = prefs.getString("user_id", "1");
                        if (userId == null || userId.equals("null") || userId.equals("undefined") || userId.trim().isEmpty()) {
                            userId = "1";
                        }
                        
                        String urlString = "https://eurotaxisystem.site/web-notifications/native-poll?user_id=" + userId;
                        Log.d(TAG, "Foreground Service Polling: " + urlString);
                        URL url = new URL(urlString);
                        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                        conn.setRequestMethod("GET");
                        conn.setRequestProperty("User-Agent", "Mozilla/5.0 (Linux; Android 13; Mobile) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36");
                        conn.setRequestProperty("Accept", "application/json");
                        conn.setConnectTimeout(5000);
                        conn.setReadTimeout(5000);
                        
                        int responseCode = conn.getResponseCode();
                        if (responseCode == 200) {
                            BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                            StringBuilder response = new StringBuilder();
                            String line;
                            while ((line = in.readLine()) != null) {
                                response.append(line);
                            }
                            in.close();
                            
                            JSONObject jsonObj = new JSONObject(response.toString());
                            if (jsonObj.has("notifications")) {
                                JSONArray alerts = jsonObj.getJSONArray("notifications");
                                
                                // Load already notified alerts to prevent duplicate chimes!
                                String notifiedCsv = prefs.getString("notified_alert_ids_csv", "");
                                Set<String> notifiedIds = new HashSet<>();
                                if (!notifiedCsv.isEmpty()) {
                                    for (String id : notifiedCsv.split(",")) {
                                        if (!id.trim().isEmpty()) notifiedIds.add(id.trim());
                                    }
                                }
                                
                                boolean hasNew = false;
                                for (int i = 0; i < alerts.length(); i++) {
                                    JSONObject alert = alerts.getJSONObject(i);
                                    String id = String.valueOf(alert.getInt("id"));
                                    String title = alert.getString("title");
                                    String message = alert.getString("message");
                                    
                                    if (!notifiedIds.contains(id)) {
                                        Log.d(TAG, "Foreground Service: NEW Alert found! ID = " + id);
                                        hasNew = true;
                                        notifiedIds.add(id);
                                        
                                        // Trigger our high-priority visual notification!
                                        MainActivity.showNativeNotification(EuroTaxiForegroundService.this, title, message);
                                    }
                                }
                                
                                if (hasNew) {
                                    // Save back notified set as CSV to survive app restarts!
                                    StringBuilder sb = new StringBuilder();
                                    for (String id : notifiedIds) {
                                        if (sb.length() > 0) sb.append(",");
                                        sb.append(id);
                                    }
                                    prefs.edit().putString("notified_alert_ids_csv", sb.toString()).apply();
                                }
                            }
                        } else {
                            Log.e(TAG, "Foreground Polling got HTTP " + responseCode);
                        }
                        conn.disconnect();
                    } catch (Exception e) {
                        Log.e(TAG, "Polling loop error: " + e.getMessage());
                    }
                }
                Log.d(TAG, "Foreground Polling Thread Terminated.");
            }
        });
        pollingThread.start();
    }

    @Override
    public void onDestroy() {
        Log.d(TAG, "Foreground Service onDestroy called");
        isRunning = false;
        if (pollingThread != null) {
            pollingThread.interrupt();
        }
        super.onDestroy();
    }

    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    private void createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            CharSequence name = "EuroTaxi Active Service";
            String description = "Keep EuroTaxi App active for background notifications";
            int importance = NotificationManager.IMPORTANCE_LOW;
            NotificationChannel channel = new NotificationChannel(CHANNEL_ID, name, importance);
            channel.setDescription(description);
            
            NotificationManager notificationManager = getSystemService(NotificationManager.class);
            if (notificationManager != null) {
                notificationManager.createNotificationChannel(channel);
            }
        }
    }
}
