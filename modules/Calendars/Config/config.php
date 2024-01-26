<?php

use Digisource\Calendars\Contracts\CalendarsServiceFactory;

return [
    'name' => 'Calendars',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                CalendarsServiceFactory::class => Digisource\Calendars\Services\V1\CalendarsService::class
            ]
        ],
        /*
        |--------------------------------------------------------------------------
        | Caching Strategy
        |--------------------------------------------------------------------------
        */
        'models' => 'Entities',
        'cache' => [
            'keys_file' => storage_path('framework/cache/digisource.repository.json'),

            'lifetime' => 0,
            'clear_on' => [
                'create',
                'update',
                'delete',
            ],
            'skip_uri' => 'skipCache',
        ]
    ]
];
