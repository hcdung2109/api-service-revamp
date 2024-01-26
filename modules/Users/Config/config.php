<?php

use Digisource\Companies\Contracts\ResCompanyRepositoryFactory;
use Digisource\Companies\Repositories\V1\ResCompanyRepository;
use Digisource\Organizations\Contracts\OrganizationsRepositoryFactory;
use Digisource\Organizations\Repositories\V1\OrganizationsRepository;
use Digisource\Users\Contracts\IrModuleRelRepositoryFactory;
use Digisource\Users\Contracts\IrModuleRepositoryFactory;
use Digisource\Users\Contracts\IrModuleServiceFactory;
use Digisource\Users\Contracts\ResUserCompanyRepositoryFactory;
use Digisource\Users\Contracts\ResUserGroupRepositoryFactory;
use Digisource\Users\Contracts\ResUserVerificationRepositoryFactory;
use Digisource\Users\Contracts\UsersRepositoryFactory;
use Digisource\Users\Contracts\UsersServiceFactory;

return [
    'name' => 'Users',
    'repository' => [
        'contractBindings' => [
            'default' => 'V1',
            'V1' => [
                UsersServiceFactory::class => Digisource\Users\Services\V1\UsersService::class,
                IrModuleServiceFactory::class => Digisource\Users\Services\V1\IrModuleService::class,
                UsersRepositoryFactory::class => Digisource\Users\Repositories\V1\UsersRepository::class,
                IrModuleRepositoryFactory::class => Digisource\Users\Repositories\V1\IrModuleRepository::class,
                IrModuleRelRepositoryFactory::class => Digisource\Users\Repositories\V1\IrModuleRelRepository::class,
                ResUserGroupRepositoryFactory::class => Digisource\Users\Repositories\V1\ResUserGroupRepository::class,
                ResUserVerificationRepositoryFactory::class => Digisource\Users\Repositories\V1\ResUserVerificationRepository::class,
                ResUserCompanyRepositoryFactory::class => Digisource\Users\Repositories\V1\ResUserCompanyRepository::class
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
