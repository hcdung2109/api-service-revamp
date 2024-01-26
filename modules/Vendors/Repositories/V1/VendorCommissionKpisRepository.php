<?php

namespace Digisource\Vendors\Repositories\V1;

use Digisource\Core\Repositories\EloquentRepository;
use Digisource\Vendors\Contracts\VendorTypeRepositoryFactory;
use Digisource\Vendors\Entities\VendorCommissionKpi;

class VendorCommissionKpisRepository extends EloquentRepository implements VendorTypeRepositoryFactory
{
    protected $repositoryId = 'digisource.vendor_commission_kpis';
    protected $model = VendorCommissionKpi::class;


    public function validateAttributes($data)
    {
        // TODO: Implement validateAttributes() method.
    }
}


