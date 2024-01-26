<?php

namespace Digisource\Users\Repositories\V1;

use Digisource\Users\Contracts\IrModuleRepositoryFactory;
use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Users\Entities\IrModule;


class IrModuleRepository extends EloquentRepository implements IrModuleRepositoryFactory
{
    protected $repositoryId = 'digisource.ir_module';
    protected $model = IrModule::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}



