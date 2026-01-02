<?php
// Stripe Configuration
// Replace with your actual Stripe keys

return [
    'publishable_key' => 'pk_test_your_publishable_key_here', // Replace with your Stripe publishable key
    'secret_key' => 'sk_test_your_secret_key_here', // Replace with your Stripe secret key
    'webhook_secret' => 'whsec_your_webhook_secret_here', // Replace with your webhook secret
    'currency' => 'usd',
    'success_url' => 'https://appcraftservices.com/payment/success',
    'cancel_url' => 'https://appcraftservices.com/payment/cancel'
];
?>