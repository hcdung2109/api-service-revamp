<?php

namespace Digisource\Vendors\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Vendors\Contracts\VendorTypeRepositoryFactory;
use Digisource\Vendors\Entities\VendorCommissionType;

class VendorCommissionTypesRepository extends EloquentRepository implements VendorTypeRepositoryFactory
{
    protected $repositoryId = 'digisource.vendor_commission_types';
    protected $model = VendorCommissionType::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


