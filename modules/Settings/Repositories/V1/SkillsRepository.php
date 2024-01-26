<?php

namespace Digisource\Settings\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Settings\Contracts\SkillsRepositoryFactory;
use Digisource\Settings\Entities\Skill;


class SkillsRepository extends EloquentRepository implements SkillsRepositoryFactory
{
    protected $repositoryId = 'digisource.skills';
    protected $model = Skill::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



