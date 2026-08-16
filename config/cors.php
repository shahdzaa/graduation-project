<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines which domains are allowed to access your
    | application's resources from a different domain.
    |
    | You may enable CORS for all origins and all HTTP methods.
    |
    */

  'paths' => ['api/*', 'testimonials', 'placement/*'],

'allowed_origins' => ['http://localhost:3000'],

'allowed_methods' => ['*'],

'allowed_headers' => ['*'],

'supports_credentials' => false,

];
