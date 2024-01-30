<?php

namespace Digisource\Vendors\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Settings\Entities\CommissionsCalculationMetric;
use Digisource\Users\Entities\ResUserCompany;
use Digisource\Users\Entities\User;
use Digisource\Vendors\Entities\VendorCommissionKpi;
use Digisource\Vendors\Entities\VendorCommissionType;
use Digisource\Vendors\Entities\VendorType;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPMailer\PHPMailer\Exception;

class SettingsVendorController extends Controller
{
    // START SETTINGS COMMISSION TYPE
    public function get_vendor_commission_type(Request $request)
    {
        $param = $request->all();

        $page =  $param['p'] ?? 0;
        $pageSize =  $param['ps'];

        $user = auth()->user();

        $rs = VendorCommissionType::select('id','name')
            ->where('status', 0)
            ->where('company_id', $user->company_id)
            ->paginate($pageSize, ['*'], 'page', $page);

        $this->addData($rs);
        return $this->getResponse();
    }

    public function create_vendor_commission_type(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();
        $name = $data['name'];

        $new = new VendorCommissionType();
        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->name = $name;
        $new->company_id = $user->company_id;
        $new->status = 0;
        $new->save();

        $this->setMessage("Tạo thành công");
        return $this->getResponse();
    }

    public function update_vendor_commission_type(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }


        $user = auth()->user();
        $data = $request->all();
        $name = $data['name'];

        $item = VendorCommissionType::where('status', 0)->where('company_id', $user->company_id)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion type không tồn tại.");
        } else {
            $item->name = $name;
            $item->save();
        }

        return $this->getResponse();
    }

    public function get_vendor_commission_type_by_id($id)
    {
        $item = VendorCommissionType::where('status', 0)->findOrFail($id);

        $this->addData(['vendor_commission_type' => $item]);
        return $this->getResponse();
    }

    public function delete_vendor_commission_type($id)
    {
        $item = VendorCommissionType::where('status', 0)->findOrFail($id);
        $item->status = 1;
        $item->save();

        $this->setMessage("Xóa vendor commisstion type thành công.");
        return $this->getResponse();
    }
    // END SETTINGS COMMISSION TYPE

    // START SETTINGS COMMISSION KPIs
    public function get_vendor_commission_kpis(Request $request)
    {
        $param = $request->all();

        $page =  $param['p'] ?? 0;
        $pageSize =  $param['ps'];

        $user = auth()->user();

        $rs = VendorCommissionKpi::select('id','name')
            ->where('status', 0)
            ->where('company_id', $user->company_id)
            ->orderBy('name')
            ->paginate($pageSize, ['*'], 'page', $page);

        $this->addData($rs);
        return $this->getResponse();
    }

    public function create_vendor_commission_kpis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();
        $name = $data['name'];

        $new = new VendorCommissionKpi();
        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->name = $name;
        $new->company_id = $user->company_id;
        $new->status = 0;
        $new->save();

        $this->setMessage("Tạo thành công");
        return $this->getResponse();
    }

    public function update_vendor_commission_kpis($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }


        $user = auth()->user();
        $data = $request->all();
        $name = $data['name'];

        $item = VendorCommissionKpi::where('status', 0)->where('company_id', $user->company_id)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion type không tồn tại.");
        } else {
            $item->name = $name;
            $item->save();
        }

        return $this->getResponse();
    }

    public function get_vendor_commission_kpis_by_id($id)
    {
        $item = VendorCommissionKpi::where('status', 0)->findOrFail($id);

        $this->addData(['vendor_commission_kpi' => $item]);
        return $this->getResponse();
    }

    public function delete_vendor_commission_kpis($id)
    {
        $item = VendorCommissionKpi::where('status', 0)->findOrFail($id);
        $item->status = 1;
        $item->save();

        $this->setMessage("Xóa vendor commisstion kpi thành công.");
        return $this->getResponse();
    }
    // END SETTINGS COMMISSION KPIs
}
