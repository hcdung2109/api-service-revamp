<?php

namespace Digisource\Users\Entities;

use Digisource\Core\Entities\BaseModel;

class IrModuleRel extends BaseModel
{

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

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'write_date';

    protected $table = 'ir_module_rel';

}
