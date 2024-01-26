<?php

namespace Digisource\Vendors\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Vendors\Contracts\VendorTypeServiceFactory;
use Digisource\Vendors\Entities\VendorType;
use Digisource\Vendors\Services\V1\VendorTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Contracts\Providers\Auth;


class VendorTypesController extends Controller
{

    public VendorTypeService $vendorTypeService;

    public function __construct(VendorTypeServiceFactory $vendorTypeServiceFactory)
    {
        $this->vendorTypeService = $vendorTypeServiceFactory;
    }

    public function get_vendor_type(Request $request)
    {
        $page = $request->get('p', 1);
        $pageSize = $request->get('ps', 10);
        $data = $this->vendorTypeService->getVendorType($page, $pageSize);
        $this->addData($data);

        return $this->getResponse();
    }

    public function get_vendor_type_by_id(Request $request, $uuid)
    {
        $data = $this->vendorTypeService->getDetail($uuid);
        $this->addData($data);
        return $this->getResponse();
    }

    public function create_vendor_type(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256|unique:vendor_types',
        ],[
            'name.required' => 'Tên không được để trống',
            'name.max' => 'Tên không được quá 256 ký tự'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->vendorTypeService->create_vendor_type($request->all());
        return $this->getResponse();
    }

    public function update_vendor_type(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
        ],[
            'name.required' => 'Tên không được để trống',
            'name.max' => 'Tên không được quá 256 ký tự'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->vendorTypeService->update_vendor_type($request, $id);
        return $this->getResponse();
    }

    public function delete_vendor_type($id)
    {
        $data = $this->vendorTypeService->delete_vendor_type($id);
        $this->addData($data);
        return $this->getResponse();
    }
}
