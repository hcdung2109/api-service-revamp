<?php

namespace Digisource\Vendors\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Vendors\Contracts\VendorTypeRepositoryFactory;
use Digisource\Vendors\Entities\VendorType;

class VendorTypesRepository extends EloquentRepository implements VendorTypeRepositoryFactory
{
    protected $repositoryId = 'digisource.vendor_types';
    protected $model = VendorType::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


