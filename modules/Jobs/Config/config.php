<?php

use Digisource\Jobs\Contracts\JobsRepositoryFactory;

return [
    'name' => 'Jobs',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                JobsRepositoryFactory::class => Digisource\Jobs\Repositories\V1\JobsRepository::class
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
