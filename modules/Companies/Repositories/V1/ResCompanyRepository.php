<?php

namespace Digisource\Companies\Repositories\V1;

use Digisource\Companies\Contracts\ResCompanyRepositoryFactory;
use Digisource\Companies\Entities\ResCompany;
use Digisource\Core\Repositories\EloquentRepository;


class ResCompanyRepository extends EloquentRepository implements ResCompanyRepositoryFactory
{
    protected $repositoryId = 'digisource.res_company';
    protected $model = ResCompany::class;

    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



