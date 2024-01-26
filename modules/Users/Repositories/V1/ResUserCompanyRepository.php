<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Users\Contracts\ResUserCompanyRepositoryFactory;
use Digisource\Users\Contracts\ResUserGroupRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Entities\ResUserCompany;
use Digisource\Users\Entities\User;


class ResUserCompanyRepository extends EloquentRepository implements ResUserCompanyRepositoryFactory
{
    protected $repositoryId = 'digisource.res_user_company';
    protected $model = ResUserCompany::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


