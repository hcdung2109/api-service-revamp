<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class ResStatusLine  extends BaseModel implements TransformerItem
{
    protected $table = TableName::RES_STATUS_LINE;
    protected $guarded = [];

}
