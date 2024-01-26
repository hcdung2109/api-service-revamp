<?php

namespace Digisource\Settings\Repositories\V1;

use Digisource\Candidates\Entities\CandidateLevel;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Settings\Contracts\CandidateLevelRepositoryFactory;


class CandidateLevelRepository extends EloquentRepository implements CandidateLevelRepositoryFactory
{
    protected $repositoryId = 'digisource.candidate_level';
    protected $model = CandidateLevel::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



