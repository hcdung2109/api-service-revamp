<?php

namespace Digisource\Candidates\Repositories\V1;

use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Entities\Candidate;
use Digisource\Candidates\Entities\CandidateFollowed;
use Digisource\Core\Repositories\EloquentRepository;


class CandidateFollowedRepository extends EloquentRepository implements CandidateRepositoryFactory
{
    protected $repositoryId = 'digisource.candidate.candidate_followed';
    protected $model = CandidateFollowed::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}
