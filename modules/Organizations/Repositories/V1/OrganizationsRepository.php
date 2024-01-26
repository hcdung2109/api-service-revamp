<?php

namespace Digisource\Organizations\Repositories\V1;

use Digisource\Organizations\Contracts\OrganizationsRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Organizations\Entities\Organizations;


class OrganizationsRepository extends EloquentRepository implements OrganizationsRepositoryFactory
{
    protected $repositoryId = 'digisource.organizations';
    protected $model = Organizations::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



