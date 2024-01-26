<?php

namespace Digisource\Candidates\Repositories\V1;

use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;
use Digisource\Common\Entities\Interviews;
use Digisource\Core\Repositories\EloquentRepository;


class InterviewsRepository extends EloquentRepository implements InterviewsRepositoryFactory
{
    protected $repositoryId = 'digisource.interviews';
    protected $model = Interviews::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



