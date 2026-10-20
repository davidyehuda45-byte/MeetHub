<?php

return [
    'domain' => env('JITSI_DOMAIN', 'meet.ffmuc.net'),
    'app_id' => env('JITSI_APP_ID', 'meethub'),
    'app_secret' => env('JITSI_APP_SECRET', 'meethub_super_secret_jwt_key_2026_dev_32bytes'),
    'jwt_algo' => env('JITSI_JWT_ALGO', 'HS256'),
    'jwt_expiry_hours' => (int) env('JITSI_JWT_EXPIRY_HOURS', 4),
];
