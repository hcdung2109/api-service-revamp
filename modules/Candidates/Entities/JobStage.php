<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class JobStage extends BaseModel implements TransformerItem
{
    protected $table = TableName::JOB_STATE;
    protected $guarded = [];

}

{

}
