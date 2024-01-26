<?php

namespace Digisource\Jobs\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Jobs\Contracts\JobsRepositoryFactory;
use Digisource\Jobs\Entities\Jobs;

class JobsRepository extends EloquentRepository implements JobsRepositoryFactory
{
    protected $repositoryId = 'digisource.jobs';
    protected $model = Jobs::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



