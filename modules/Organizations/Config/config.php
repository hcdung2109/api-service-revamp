<?php

use Digisource\Organizations\Contracts\OrganizationsRepositoryFactory;
use Digisource\Organizations\Contracts\OrganizationsServiceFactory;
use Digisource\Organizations\Repositories\V1\OrganizationsRepository;
use Digisource\Organizations\Services\V1\OrganizationsService;

return [
    'name' => 'Organizations',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                OrganizationsServiceFactory::class => OrganizationsService::class,
                OrganizationsRepositoryFactory::class => OrganizationsRepository::class
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
