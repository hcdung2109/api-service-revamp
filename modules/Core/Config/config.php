<?php

return [
    'name' => 'Core',

    'repository' => [
        /*
        |--------------------------------------------------------------------------
        | Caching Strategy
        |--------------------------------------------------------------------------
        */
            /*
        |--------------------------------------------------------------------------
        | Models Directory
        |--------------------------------------------------------------------------
        |
        | Here you may specify the default models directory, just write
        | directory name, like 'Models' not the full path.
        |
        | Default: 'Models'
        |
        */

        'models' => 'Entities',
        'cache' => [

            /*
            |--------------------------------------------------------------------------
            | Cache Keys File
            |--------------------------------------------------------------------------
            |
            | Here you may specify the cache keys file that is used only with cache
            | drivers that does not support cache tags. It is mandatory to keep
            | track of cache keys for later usage on cache flush process.
            |
            | Default: storage_path('framework/cache/digisource.repository.json')
            |
            */

            'keys_file' => storage_path('framework/cache/digisource.repository.json'),

            /*
            |--------------------------------------------------------------------------
            | Cache Lifetime
            |--------------------------------------------------------------------------
            |
            | Here you may specify the number of minutes that you wish the cache
            | to be remembered before it expires. If you want the cache to be
            | remembered forever, set this option to -1. 0 means disabled.
            |
            | Default: -1
            |
            */

            'lifetime' => 0,

            /*
            |--------------------------------------------------------------------------
            | Cache Clear
            |--------------------------------------------------------------------------
            |
            | Specify which actions would you like to clear cache upon success.
            | All repository cached data will be cleared accordingly.
            |
            | Default: ['create', 'update', 'delete']
            |
            */

            'clear_on' => [
                'create',
                'update',
                'delete',
            ],

            /*
            |--------------------------------------------------------------------------
            | Cache Skipping URI
            |--------------------------------------------------------------------------
            |
            | For testing purposes, or maybe some certain situations, you may wish
            | to skip caching layer and get fresh data result set just for the
            | current request. This option allows you to specify custom
            | URL parameter for skipping caching layer easily.
            |
            | Default: 'skipCache'
            |
            */

            'skip_uri' => 'skipCache',

        ],
    ]


];
