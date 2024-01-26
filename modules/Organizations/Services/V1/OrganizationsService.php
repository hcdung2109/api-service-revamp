<?php

namespace Digisource\Organizations\Services\V1;


use Digisource\Organizations\Contracts\OrganizationsServiceFactory;
use Digisource\Organizations\Repositories\V1\OrganizationsRepository;

class OrganizationsService implements OrganizationsServiceFactory
{
    public OrganizationsRepository $organizationsRepository;

    public function __construct(OrganizationsServiceFactory $organizationsServiceFactory)
    {
        $this->organizationsRepository = $organizationsServiceFactory;
    }

}
