<?php

use Digisource\Companies\Contracts\CompaniesServiceFactory;
use Digisource\Companies\Contracts\ResCompanyRepositoryFactory;
use Digisource\Companies\Repositories\V1\ResCompanyRepository;
use Digisource\Companies\Services\V1\CompaniesService;
use Digisource\Settings\Contracts\SettingsCompanyServiceFactory;

return [
    'name' => 'Companies',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                CompaniesServiceFactory::class => CompaniesService::class,
                ResCompanyRepositoryFactory::class => ResCompanyRepository::class
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
