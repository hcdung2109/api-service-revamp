<?php

namespace Digisource\Vendors\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Vendors\Contracts\VendorTypeRepositoryFactory;
use Digisource\Vendors\Entities\Vendor;

class VendorRepository extends EloquentRepository implements VendorTypeRepositoryFactory
{
    protected $repositoryId = 'digisource.vendors';
    protected $model = Vendor::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


