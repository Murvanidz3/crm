<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Office (Georgia) Configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'api_key' => env('SMS_API_KEY'),
        'sender' => env('SMS_SENDER', 'ONECAR.GE'),
        'enabled' => env('SMS_ENABLED', true),
        'base_url' => 'https://smsoffice.ge/api/v2/send/',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 10240), // KB
        'thumbnail_width' => env('THUMBNAIL_WIDTH', 1280),
        'thumbnail_quality' => env('THUMBNAIL_QUALITY', 70),
        'allowed_image_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_video_types' => ['mp4', 'mov'],
        'allowed_document_types' => ['pdf', 'doc', 'docx'],
    ],
];
