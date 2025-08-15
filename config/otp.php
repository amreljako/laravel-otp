<?php
return [
    'default_digits' => 6,
    'default_ttl' => 300,
    'delete_on_verify' => false,
    'rate_limits' => [
        'send_per_destination_per_minute' => 3,
        'verify_per_destination_per_minute' => 10,
    ],
    'signature' => [
        'enabled' => true,
        'key' => env('OTP_HMAC_KEY', 'change-me'),
        'context' => ['purpose', 'destination'],
    ],
    'channels' => [
        'mail' => \Amreljako\Otp\Channels\MailChannel::class,
        // Add sms/whatsapp drivers in your app or separate packages
    ],
];
