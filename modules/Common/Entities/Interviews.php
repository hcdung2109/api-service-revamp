<?php

namespace Digisource\Common\Entities;

use Digisource\Candidates\Entities\Candidates;
use Digisource\Core\Entities\BaseModel;
use Digisource\Jobs\Entities\Jobs;

class Interviews extends BaseModel
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

    protected $table = 'interviews';

    public function candidates(){
        return $this->hasOne(Candidates::class, 'id', 'candidate_id');
    }

    public function jobs(){
        return $this->hasOne(Jobs::class, 'id', 'job_id');
    }
}
