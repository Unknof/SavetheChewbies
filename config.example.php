<?php

// Copy this file to config.php (DO NOT commit config.php)
// and fill in values from your Tiltify Developer Dashboard.

return [
    // Tiltify OAuth (Client Credentials)
    'tiltify_client_id' => 'YOUR_TILTIFY_CLIENT_ID',
    'tiltify_client_secret' => 'YOUR_TILTIFY_CLIENT_SECRET',

    // Webhook signature verification key (from Tiltify Webhook Endpoint overview)
    // This is a long hex string.
    'tiltify_webhook_signing_key' => 'YOUR_TILTIFY_WEBHOOK_SIGNING_KEY',

    // Your Webhook Relay ID (from the Relay you create in Tiltify dashboard)
    'tiltify_webhook_relay_id' => 'YOUR_TILTIFY_WEBHOOK_RELAY_ID',

    // Donation form base URL, e.g.:
    // https://donate.tiltify.com/@yourusername/your-campaign-slug
    // or https://donate.tiltify.com/cause-slug
    'tiltify_donation_url' => 'https://donate.tiltify.com/@yourusername/your-campaign-slug',

    // Storage (file-based MVP)
    // This folder must be writable by PHP on your host.
    'data_dir' => __DIR__ . DIRECTORY_SEPARATOR . 'data',

    // For https://yourdomain.example/admin.php?key=...
    'admin_key' => 'CHANGE_ME_TO_A_RANDOM_SECRET',
];
