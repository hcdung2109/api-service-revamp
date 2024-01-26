<?php

namespace Digisource\Candidates\Entities;

use Digisource\Candidates\Database\Constants\TableName;
use Digisource\Core\Contracts\TransformerItem;
use Digisource\Core\Entities\BaseModel;


/**
 * Class Package
 *
 * @package Digisource\Packages\Entities
 *
 *
 * @OA\Schema(
 *     schema="Candidate",
 *     description="Candidate",
 *     title="Candidate Entity"
 * )
 */
class CandidateFollowed extends BaseModel implements TransformerItem
{
    protected $table = TableName::CANDIDATE_FOLLOWED;
    protected $guarded = [];

}
