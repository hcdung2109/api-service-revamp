<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Users\Contracts\ResUserGroupRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Entities\ResUserGroup;


class ResUserGroupRepository extends EloquentRepository implements ResUserGroupRepositoryFactory
{
    protected $repositoryId = 'digisource.res_user_group';
    protected $model = ResUserGroup::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


