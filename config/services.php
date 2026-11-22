<?php

return [
    'brevo' => [
        'key' => env('BREVO_API_KEY'),
    ],
    'resend' => [
        'key' => env('RESEND_API_KEY'),
        'from' => env('RESEND_FROM'),
    ],
];
