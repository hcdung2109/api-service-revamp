<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class CandidateLevel  extends BaseModel implements TransformerItem
{
    protected $table = TableName::CANDIDATE_LEVEL;
    protected $guarded = [];

}
