<?php

return [

    'default' => env('FILESYSTEM_DISK', 'public'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        // Disk public: file upload luu tai storage/app/public/*,
        // truy cap qua /storage/* sau khi chay `php artisan storage:link`.
        'public' => [
            'driver' => 'local',
            'root' => (env('PUBLIC_STORAGE_PATH') ?: storage_path('app/public')),
            'url' => env('PUBLIC_STORAGE_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/storage'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => (env('PUBLIC_STORAGE_PATH') ?: storage_path('app/public')),
    ],

];
