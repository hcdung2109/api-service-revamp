<?php

namespace Digisource\Users\Services\V1;


use Digisource\Users\Contracts\IrModuleRepositoryFactory;
use Digisource\Users\Contracts\IrModuleServiceFactory;
use Digisource\Users\Repositories\V1\IrModuleRepository;

class IrModuleService implements IrModuleServiceFactory
{
    public IrModuleRepository $irModuleRepository;

    public function __construct(IrModuleRepositoryFactory $irModuleRepositoryFactory)
    {
        $this->irModuleRepository = $irModuleRepositoryFactory;
    }


    /**
     * @param $query
     * @param $filterBy
     * @param $sortBy
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function listDefaultIrModule()
    {
        $where = [];
        $query = $this->irModuleRepository->where($where);
        $query->where('publish', 1);
        $query->where('parent_id', '');
        $query->select('id');
        return $query->get();
    }
}
