<?php

return [
    'defaults' => [
        'whm_account' => env('EMAIL_MONITOR_DEFAULT_WHM_ACCOUNT'),
        'domain' => env('EMAIL_MONITOR_DEFAULT_DOMAIN'),
    ],
    'ingest' => [
        'shared_secret' => env('PYTHON_INGEST_SHARED_SECRET'),
        'max_events_per_request' => 5000,
    ],
    'swagger' => [
        'enabled' => env('SWAGGER_ENABLED', true),
    ],
];
