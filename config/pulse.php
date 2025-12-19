<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pulse Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to completely disable Pulse data recording even
    | if it is currently enabled in your application. This is useful for
    | temporarily pausing data collection during maintenance periods.
    |
    */

    'enabled' => env('PULSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pulse Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option determines which storage driver Pulse will use
    | to store its data. The "database" driver stores data in your application's
    | primary database while the "redis" option uses Redis for better performance.
    |
    */

    'storage' => [
        'driver' => env('PULSE_STORE_DRIVER', 'database'),

        'database' => [
            'connection' => env('PULSE_DB_CONNECTION'),
            'chunk' => 1000,
        ],

        'redis' => [
            'connection' => env('PULSE_REDIS_CONNECTION'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Ingest Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the ingest driver that will be used
    | to capture entries from your application. The "storage" driver will
    | immediately write entries to storage, while other drivers will queue them.
    |
    */

    'ingest' => [
        'driver' => env('PULSE_INGEST_DRIVER', 'storage'),
        
        'buffer' => env('PULSE_INGEST_BUFFER', 5000),
        
        'trim' => [
            'lottery' => [1, 1000],
            'keep' => '7 days',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Recorders
    |--------------------------------------------------------------------------
    |
    | The following array lists the "recorders" that will be registered with
    | Pulse. Recorders gather application event data from your application
    | for display in the Pulse dashboard. You're free to disable any of these.
    |
    */

    'recorders' => [
        // Application Performance Monitoring
        \Laravel\Pulse\Recorders\CacheInteractions::class => [
            'enabled' => env('PULSE_CACHE_INTERACTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_CACHE_INTERACTIONS_SAMPLE_RATE', 1),
            'ignore' => [
                // Keys to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\Exceptions::class => [
            'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_EXCEPTIONS_SAMPLE_RATE', 1),
            'ignore' => [
                // Exception classes to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\Queues::class => [
            'enabled' => env('PULSE_QUEUES_ENABLED', true),
            'sample_rate' => env('PULSE_QUEUES_SAMPLE_RATE', 1),
            'ignore' => [
                // Queue job classes to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\SlowJobs::class => [
            'enabled' => env('PULSE_SLOW_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_JOBS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_JOBS_THRESHOLD', 1000),
            'ignore' => [
                // Job classes to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\SlowOutgoingRequests::class => [
            'enabled' => env('PULSE_SLOW_OUTGOING_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_OUTGOING_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_OUTGOING_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                // URL patterns to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\SlowQueries::class => [
            'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_QUERIES_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 1000),
            'location' => env('PULSE_SLOW_QUERIES_LOCATION', true),
            'ignore' => [
                // SQL patterns to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\SlowRequests::class => [
            'enabled' => env('PULSE_SLOW_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                // URL patterns to ignore
                '#^/admin/pulse#',
            ],
        ],

        \Laravel\Pulse\Recorders\Servers::class => [
            'server_name' => env('PULSE_SERVER_NAME', gethostname()),
            'directories' => array_filter(explode(':', env('PULSE_SERVER_DIRECTORIES', '/'))),
        ],

        \Laravel\Pulse\Recorders\UserJobs::class => [
            'enabled' => env('PULSE_USER_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_JOBS_SAMPLE_RATE', 1),
            'ignore' => [
                // Job classes to ignore
            ],
        ],

        \Laravel\Pulse\Recorders\UserRequests::class => [
            'enabled' => env('PULSE_USER_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_REQUESTS_SAMPLE_RATE', 1),
            'ignore' => [
                // URL patterns to ignore
                '#^/admin/pulse#',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the Pulse dashboard route. You may configure the
    | middleware that should be applied to the Pulse dashboard. By default,
    | Pulse is only accessible in the local environment.
    |
    */

    'middleware' => [
        'web',
        // 'auth',
        // 'can:viewPulse',
    ],

];
