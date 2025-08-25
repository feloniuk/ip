<?php

return [
    'api' => [
        'url' => env('GEOLOCATION_API_URL', 'http://ip-api.com/json/'),
        'timeout' => env('GEOLOCATION_API_TIMEOUT', 10),
        'retry_attempts' => env('GEOLOCATION_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('GEOLOCATION_API_RETRY_DELAY', 1000),
    ],
    
    'cache' => [
        'ttl' => env('GEOLOCATION_CACHE_TTL', 3600),
        'prefix' => env('GEOLOCATION_CACHE_PREFIX', 'geo:ip:'),
    ],
    
    'rate_limiting' => [
        'enabled' => env('GEOLOCATION_RATE_LIMIT_ENABLED', true),
        'max_requests_per_minute' => env('GEOLOCATION_RATE_LIMIT_MAX', 45),
    ],
];