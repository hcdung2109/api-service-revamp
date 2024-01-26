<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;

class ResNotification  extends BaseModel implements TransformerItem
{
    protected $table = TableName::RES_NOTIFICATION;
    protected $guarded = [];

}
