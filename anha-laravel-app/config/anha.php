<?php

return [
    'api_base_url' => env('ANHA_API_BASE_URL', env('API_BASE_URL', env('NEXT_PUBLIC_API_BASE_URL', 'https://api.anhagroup.com/api/v1'))),
    'asset_base_url' => env('ANHA_ASSET_BASE_URL', 'https://api.anhagroup.com'),
    'play_store_url' => env('ANHA_PLAY_STORE_URL'),
    'request_timeout' => (int) env('ANHA_API_TIMEOUT', 30),
];
