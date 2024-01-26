<?php

namespace Digisource\Companies\Services\V1;


use Digisource\Companies\Contracts\CompaniesServiceFactory;
use Digisource\Companies\Contracts\ResCompanyRepositoryFactory;
use Digisource\Companies\Repositories\V1\ResCompanyRepository;

class CompaniesService implements CompaniesServiceFactory
{
    public ResCompanyRepository $resCompanyRepository;

    public function __construct(ResCompanyRepositoryFactory $resCompanyRepositoryFactory)
    {
        $this->resCompanyRepository = $resCompanyRepositoryFactory;
    }

}
