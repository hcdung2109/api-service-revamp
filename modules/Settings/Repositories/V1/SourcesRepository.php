<?php

namespace Digisource\Settings\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Settings\Contracts\SkillsRepositoryFactory;
use Digisource\Settings\Contracts\SourcesRepositoryFactory;
use Digisource\Settings\Entities\Skill;
use Digisource\Settings\Entities\Sources;


class SourcesRepository extends EloquentRepository implements SourcesRepositoryFactory
{
    protected $repositoryId = 'digisource.sources';
    protected $model = Sources::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



