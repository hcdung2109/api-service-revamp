<?php

namespace Digisource\Vendors\Services\V1;

use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Vendors\Entities\VendorCommissionType;
use Digisource\Vendors\Entities\VendorType;
use Digisource\Vendors\Repositories\V1\VendorCommissionTypesRepository;
use Illuminate\Support\Facades\Auth;
use Digisource\Vendors\Contracts\VendorTypeServiceFactory;
use Digisource\Vendors\Repositories\V1\VendorTypesRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;

class VendorCommissionTypeService implements VendorTypeServiceFactory
{
    public VendorCommissionTypesRepository $vendorCommissionTypesRepository;

    public function __construct(
        VendorTypesRepository $vendorCommissionTypesRepository
    ) {
        $this->vendorCommissionTypesRepository = $vendorCommissionTypesRepository;
    }

    /**
     * @param $query
     * @param $filterBy
     * @param $sortBy
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getVendorType($page = 1, $pageSize = 10)
    {
        $user = auth()->user();

        $rs = VendorCommissionType::select('id','name')
                    ->where('status', Status::ACTIVE)
                    ->where('company_id', $user->company_id)
                    ->paginate($pageSize, ['*'], 'page', $page);

        return $rs;
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function getDetail($id): mixed
    {
        $vendorType = $this->vendorCommissionTypesRepository->where('id', $id)->findFirst();
        if (!empty($vendorType)) {
            return ["vendor_type" => $vendorType];
        }
        return [];
    }

    public function create_vendor_type($params)
    {
        $user = auth()->user();

        $id = uniqid();
        $vendorType = $this->vendorCommissionTypesRepository->create([
                'id' => $id,
                'create_uid' => $user->id,
                'write_uid' => $user->id,


                'status' => Status::ACTIVE,
                'company_id' => $user->company_id,
                'name' => $params['name'],
            ]
        );

        if ($vendorType) {
            return [
                'message' => "Tạo thành công"
            ];
        } else {
            return new Exception(__("Tạo interview thất bại."), 505);
        }
    }

    public function update_vendor_type(Request $request, $id)
    {
        $vendorType = $this->vendorCommissionTypesRepository->update($id, [

                'name' => $request->name
            ]
        );

        if ($vendorType) {
            return [
                'status' => true,
                'message' => "Cập nhật thành công."
            ];
        } else {
            return [
                'status' => false,
                'message' => "Không tìn thấy Vendor Type"
            ];
        }

        return new Exception(__("Cập nhật thất bại."), 505);
    }

    public function delete_vendor_type($id)
    {
        $seen_id = $this->vendorCommissionTypesRepository->select('id')
            ->where('status', Status::ACTIVE)
            ->where('id', $id)->first();

        if (!$seen_id) {
            $message = [
                'message' => __("Vendor Type không tồn tại.")
            ];
        } else {
            $candidate = $this->vendorCommissionTypesRepository->find($id);
            $candidate->status = Status::INACTIVE;
            $candidate->save();

            $message = [
                'status' => true,
                'message' => __("Xóa thành công.")
            ];
        }

        return $message;
    }
}
