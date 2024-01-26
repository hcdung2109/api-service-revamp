<?php

namespace Digisource\Settings\Entities;

use Digisource\Core\Entities\BaseModel;

class Skill extends BaseModel
{

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'write_date';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';
    protected $table = 'skills';

}
