package com.nucleapp.na_76ba41;

import android.content.pm.ActivityInfo;
import android.net.Uri;
import android.os.Bundle;

import androidx.browser.customtabs.CustomTabsIntent;
import androidx.browser.trusted.TrustedWebActivityIntentBuilder;

import android.app.Activity;
import android.content.Intent;

import java.util.Arrays;
import java.util.List;

public class LauncherActivity extends Activity {

    // Domain yang tetap dibuka di dalam app (TWA)
    private static final String TWA_HOST = "inikah.pages.dev";
    
    // Domain yang harus dibuka di browser eksternal
    private static final List<String> EXTERNAL_DOMAINS = Arrays.asList(
        "docs.google.com",
        "drive.google.com",
        "wa.me",
        "api.whatsapp.com",
        "script.google.com",
        "forms.gle"
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        Uri launchUrl = Uri.parse("https://inikah.pages.dev/");

        // Check if opened from deep link
        Intent intent = getIntent();
        if (intent != null && intent.getData() != null) {
            Uri data = intent.getData();
            if (isExternalUrl(data)) {
                openInExternalBrowser(data);
                finish();
                return;
            }
            launchUrl = data;
        }

        // Launch TWA
        TrustedWebActivityIntentBuilder builder = new TrustedWebActivityIntentBuilder(launchUrl);
        
        // Set additional origins that should stay in TWA
        // All other URLs will open in external browser automatically
        
        CustomTabsIntent customTabsIntent = builder.build(new CustomTabsIntent.Builder().build().intent);
        customTabsIntent.launchUrl(this, launchUrl);
        finish();
    }

    private boolean isExternalUrl(Uri uri) {
        if (uri == null || uri.getHost() == null) return false;
        String host = uri.getHost().toLowerCase();
        
        // If it's our own domain, keep in TWA
        if (host.equals(TWA_HOST)) return false;
        
        // Everything else is external
        return true;
    }

    private void openInExternalBrowser(Uri uri) {
        Intent browserIntent = new Intent(Intent.ACTION_VIEW, uri);
        browserIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(browserIntent);
    }
}
