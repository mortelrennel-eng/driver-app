package com.eurotaxi.app;

import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.webkit.WebView;
import com.getcapacitor.BridgeActivity;
import com.google.firebase.messaging.FirebaseMessaging;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        
        fetchAndInjectToken();
    }

    private void fetchAndInjectToken() {
        FirebaseMessaging.getInstance().getToken()
            .addOnCompleteListener(task -> {
                if (!task.isSuccessful()) {
                    Log.w("FCM_NATIVE", "Fetching FCM registration token failed", task.getException());
                    return;
                }

                String token = task.getResult();
                Log.d("FCM_NATIVE", "Native FCM Token: " + token);

                // Inject token into WebView, retry every 2 seconds if not ready
                final Handler handler = new Handler(Looper.getMainLooper());
                Runnable injectRunnable = new Runnable() {
                    int attempts = 0;
                    @Override
                    public void run() {
                        try {
                            if (bridge != null && bridge.getWebView() != null) {
                                WebView webView = bridge.getWebView();
                                String js = "window.localStorage.setItem('fcm_token_native', '" + token + "');" +
                                            "window.dispatchEvent(new CustomEvent('native_fcm_token_ready', { detail: { token: '" + token + "' } }));";
                                webView.evaluateJavascript(js, null);
                                Log.d("FCM_NATIVE", "Token successfully injected into WebView JS context.");
                            } else if (attempts < 10) {
                                attempts++;
                                handler.postDelayed(this, 2000);
                            }
                        } catch (Exception e) {
                            e.printStackTrace();
                        }
                    }
                };
                
                handler.postDelayed(injectRunnable, 3000);
            });
    }
}
