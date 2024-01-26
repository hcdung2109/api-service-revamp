<?php

use Digisource\Settings\Contracts\CandidateLevelRepositoryFactory;
use Digisource\Settings\Contracts\SettingGlobalServiceFactory;
use Digisource\Settings\Contracts\SettingLocationServiceFactory;
use Digisource\Settings\Contracts\SettingsCandidateServiceFactory;
use Digisource\Settings\Contracts\SettingsCompanyServiceFactory;
use Digisource\Settings\Contracts\SettingsJobsServiceFactory;
use Digisource\Settings\Contracts\SettingsOrganizationServiceFactory;
use Digisource\Settings\Contracts\SkillsRepositoryFactory;
use Digisource\Settings\Contracts\SourcesRepositoryFactory;
use Digisource\Settings\Repositories\V1\CandidateLevelRepository;
use Digisource\Settings\Repositories\V1\SourcesRepository;
use Digisource\Settings\Services\V1\SettingGlobalService;
use Digisource\Settings\Services\V1\SettingLocationService;
use Digisource\Settings\Repositories\V1\SkillsRepository;
use Digisource\Settings\Services\V1\SettingsCandidateService;
use Digisource\Settings\Services\V1\SettingsCompanyService;
use Digisource\Settings\Services\V1\SettingsJobsService;
use Digisource\Settings\Services\V1\SettingsOrganizationService;

return [
    'name' => 'Settings',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                CandidateLevelRepositoryFactory::class => CandidateLevelRepository::class,
                SourcesRepositoryFactory::class => SourcesRepository::class,
                SkillsRepositoryFactory::class => SkillsRepository::class,
                SettingLocationServiceFactory::class => SettingLocationService::class,
                SettingsCandidateServiceFactory::class => SettingsCandidateService::class,
                SettingGlobalServiceFactory::class => SettingGlobalService::class,
                SettingsCompanyServiceFactory::class => SettingsCompanyService::class,
                SettingsJobsServiceFactory::class => SettingsJobsService::class,
                SettingsOrganizationServiceFactory::class => SettingsOrganizationService::class,
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
