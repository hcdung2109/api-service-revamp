<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Users\Contracts\IrModuleRelRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Entities\IrModuleRel;


class IrModuleRelRepository extends EloquentRepository implements IrModuleRelRepositoryFactory
{
    protected $repositoryId = 'digisource.ir_module_rel';
    protected $model = IrModuleRel::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



