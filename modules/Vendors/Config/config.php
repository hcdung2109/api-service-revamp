<?php

use Digisource\Vendors\Contracts\VendorTypeServiceFactory;

return [
    'name' => 'Vendors',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                VendorTypeServiceFactory::class => Digisource\Vendors\Services\V1\VendorTypeService::class,
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
