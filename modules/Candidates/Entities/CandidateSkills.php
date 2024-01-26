<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class CandidateSkills extends BaseModel implements TransformerItem
{
    protected $table = TableName::CANDIDATE_SKILLS;
    protected $guarded = [];

}
