<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | API ini dipakai oleh: (1) aplikasi mobile Flutter — tidak butuh CORS
    | sama sekali karena bukan browser; (2) dashboard admin (anha-laravel-app)
    | — memanggil API dari sisi server (PHP), juga tidak melewati CORS browser.
    | CORS di sini hanya relevan kalau ada JavaScript di browser yang memanggil
    | API ini langsung secara cross-origin. Dibatasi by-default; tambahkan
    | origin lain via CORS_ALLOWED_ORIGINS di .env (pisahkan dengan koma) kalau
    | benar-benar dibutuhkan (mis. storefront web terpisah di masa depan).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', ''))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
