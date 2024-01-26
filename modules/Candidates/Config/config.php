<?php

use Digisource\Candidates\Contracts\CandidateCommonServiceFactory;
use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Contracts\CandidateServiceFactory;
use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;
use Digisource\Candidates\Services\V1\CandidateCommonService;

return [
    'name' => 'Candidates',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                CandidateCommonServiceFactory::class => CandidateCommonService::class,
                CandidateServiceFactory::class => Digisource\Candidates\Services\V1\CandidatesService::class,
                CandidateRepositoryFactory::class => Digisource\Candidates\Repositories\V1\CandidatesRepository::class,
                InterviewsRepositoryFactory::class => Digisource\Candidates\Repositories\V1\InterviewsRepository::class
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
