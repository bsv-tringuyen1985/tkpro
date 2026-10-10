<?php

// Origin duoc phep goi API — lay tu FRONTEND_URL (phan tach bang dau phay neu nhieu).
$frontendOrigins = array_values(array_filter(array_map(
    fn ($o) => trim($o),
    explode(',', (string) env('FRONTEND_URL', 'http://localhost:3000'))
)));

return [

    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $frontendOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
