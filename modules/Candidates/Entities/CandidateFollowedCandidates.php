<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class CandidateFollowedCandidates extends BaseModel implements TransformerItem
{
    protected $table = TableName::CANDIDATE_FOLLOW_CANDIDATES;
    protected $guarded = [];

}

{

}
