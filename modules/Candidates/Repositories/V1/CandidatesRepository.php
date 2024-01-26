<?php

namespace Digisource\Candidates\Repositories\V1;

use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Entities\Candidates;
use Digisource\Core\Repositories\EloquentRepository;


class CandidatesRepository extends EloquentRepository implements CandidateRepositoryFactory
{
    protected $repositoryId = 'digisource.candidate.candidate';
    protected $model = Candidates::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}
