package com.eurotaxi.app;

import android.content.SharedPreferences;
import android.util.Log;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;

public class EuroTaxiMessagingService extends FirebaseMessagingService {
    public static final String TAG = "EuroTaxiFCM";
    public static final String PREFS_NAME = "EuroTaxiFCMPrefs";
    public static final String TOKEN_KEY = "fcm_token";

    @Override
    public void onNewToken(String token) {
        Log.d(TAG, "onNewToken fired natively! Token: " + token);

        // Store in SharedPreferences so MainActivity and Foreground Service can read it immediately
        SharedPreferences prefs = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        prefs.edit().putString(TOKEN_KEY, token).apply();
    }

    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        Log.d(TAG, "onMessageReceived fired natively! From: " + remoteMessage.getFrom());
        
        if (remoteMessage.getData() != null) {
            Log.d(TAG, "FCM NATIVE Data Payload: " + remoteMessage.getData().toString());
        }
        
        // Extract title and body from the FCM payload
        String title = "🔊 EuroTaxi Alert";
        String message = "May bagong alert sa EuroTaxi System!";
        
        if (remoteMessage.getNotification() != null) {
            if (remoteMessage.getNotification().getTitle() != null) {
                title = remoteMessage.getNotification().getTitle();
            }
            if (remoteMessage.getNotification().getBody() != null) {
                message = remoteMessage.getNotification().getBody();
            }
            Log.d(TAG, "Extracted from Notification object: " + title + " - " + message);
        } else if (remoteMessage.getData() != null && !remoteMessage.getData().isEmpty()) {
            title = remoteMessage.getData().getOrDefault("title", title);
            message = remoteMessage.getData().getOrDefault("message", message);
            Log.d(TAG, "Extracted from Data map: " + title + " - " + message);
        }
        
        // Trigger the native high-priority heads-up card with sound, vibration, and call category!
        // This is executed 100% in native Java, entirely independent of the Capacitor WebView state!
        try {
            Log.d(TAG, "FCM NATIVE: Relaying to MainActivity.showNativeNotification.");
            MainActivity.showNativeNotification(this, title, message);
        } catch (Exception e) {
            Log.e(TAG, "FCM NATIVE: Error displaying manual notification: " + e.getMessage());
        }
    }
}
