<?php

use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;

return [
    'name' => 'Common',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
//                InterviewsRepositoryFactory::class => \Digisource\Candidates\Repositories\V1\InterviewsRepository::class
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
