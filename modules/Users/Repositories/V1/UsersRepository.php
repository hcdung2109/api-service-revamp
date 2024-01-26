<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Users\Contracts\UsersRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Entities\User;


class UsersRepository extends EloquentRepository implements UsersRepositoryFactory
{
    protected $repositoryId = 'digisource.res_users';
    protected $model = User::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


