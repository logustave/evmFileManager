<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => 'ftp.ijok9711.odns.fr',
            'username' => 'embo@boqa.eveilmedia.tv',
            'password' => 'Ingenieur2022@@',
            'visibility' => 'public',
            'permPublic' => 0766,
            'port' => 21,
            'root' => '/home/ijok9711/'.env('FTP_ROOT').'/'.storage_path('app/public/data/medias'),
        ],

        'sftp' => [
            'driver' => 'sftp',
            'host' => 'metal.o2switch.net',
            'username' => 'ijok9711',
            'password' => '!EdNK7;}]YbSgqrC+d',
            'visibility' => 'public',
            'permPublic' => 0766,
            'root' => '/home/ijok9711/'.env('FTP_ROOT').'/storage/app/public/data/medias'
        ],

        'affiches' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/affiches'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'partenaires' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/partenaires'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'medias' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/medias'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'proverbes' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/proverbes'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'categories' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/categories'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'sous-categories' => [
            'driver' => 'local',
            'root' => storage_path('app/public/data/sous-categories'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
