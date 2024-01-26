<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Contracts\ResUserVerificationRepositoryFactory;
use Digisource\Users\Entities\ResUserVerification;


class ResUserVerificationRepository extends EloquentRepository implements ResUserVerificationRepositoryFactory
{
    protected $repositoryId = 'digisource.res_user_verification';
    protected $model = ResUserVerification::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


