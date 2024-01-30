<?php

namespace Digisource\Vendors\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Settings\Entities\CommissionsCalculationMetric;
use Digisource\Users\Entities\ResUserCompany;
use Digisource\Users\Entities\User;
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
    // START SETTINGS COMMISSION METRIC
    public function get_vendor_commission_metric(Request $request)
    {
        $param = $request->all();

        $p =  $param['p'] ?? 1;
        $ps =  $param['ps'];

        $user = auth()->user();

        $result = CommissionsCalculationMetric::select('id','name','description')
                                    ->where('status', 0)
                                    ->where('company_id', $user->company_id)
                                    ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        return $this->getResponse();
    }

    public function create_vendor_commission_metric(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();

        $name = $data['name'];
        $description = $data['description'];

        $new = new CommissionsCalculationMetric();
        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->name = $name;
        $new->description = $description;
        $new->company_id = $user->company_id;
        $new->status = 0;
        $new->save();

        $this->setMessage("Tạo user thành công");
        return $this->getResponse();
    }

    public function update_vendor_commission_metric(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'description' => 'required|max:1000'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();

        $name = $data['name'];
        $description = $data['description'];

        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($user == null) {
            $this->setMessage("User không tồn tại.");
        } else {
            $item->name = $name;
            $item->description = $description;
            $item->save();
        }

        return $this->getResponse();
    }

    public function get_vendor_commission_metric_by_id(Request  $request, $id)
    {
        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion metric không tồn tại.");
        } else {
            $this->addData(['vendor_commission_metric' => $item]);
        }

        return $this->getResponse();
    }

    public function delete_vendor_commission_metric($id)
    {
        $item = CommissionsCalculationMetric::where('status',0)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion metric không tồn tại.");
        } else {
            $item->status = 1;
            $item->save();

            $this->setMessage("Xóa vendor commisstion metric thành công.");

        }

        return $this->getResponse();
    }
    // END SETTINGS COMMISSION METRIC

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

        $this->setMessage("Tạo user thành công");
        return $this->getResponse();

            $builder = $this->appSession->getTier()->createBuilder("vendor_commission_types");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $user->company_id);
            $builder->add("name", $name);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Tạo vendor commisstion type thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_vendor_commission_type($id, Request $request)
    {
        $data = $request->all();

        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'name' => $name,
        ], [
            'name' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM vendor_commission_types d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Vendor commisstion type không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("vendor_commission_types");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật vendor commisstion type thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_vendor_commission_type_by_id($id)
    {
        $sql = "SELECT d1.id FROM vendor_commission_types d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor commisstion type không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM vendor_commission_types d1 WHERE d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $result =  $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            $data = array();
            if ($numrows > 0) {
                $row = $result->getRow(0);
                $data['id'] =   $row->getString("id");
                $data['name'] = $row->getString("name");
            }

            $message = [
                'status' => true,
                'data' => ['vendor_commission_type' => $data],
                'message' => "Lấy vendor commisstion type by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_vendor_commission_type($id)
    {
        $sql = "SELECT d1.id FROM vendor_commission_types d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor commisstion type không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("vendor_commission_types");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa vendor commisstion type thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS COMMISSION TYPE

    // START SETTINGS COMMISSION KPIs
    public function get_vendor_commission_kpis(Request $request)
    {
        $param = $request->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM vendor_commission_kpis d1 WHERE d1.status=0 AND d1.company_id='" . $user->company_id . "'";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

            $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

            $this->msg->add("query", $arrResult->sql);

            $result = $this->appSession->getTier()->getArray($this->msg);
        } else {
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->getArray($this->msg);
        }
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['vendor_commission_kpis' => $data],
            'message' => "Lấy danh sách vendor commisstion kpi thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_vendor_commission_kpis(Request $request)
    {
        $data = $request->all();

        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'name' => $name,
        ], [
            'name' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("vendor_commission_kpis");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $user->company_id);
            $builder->add("name", $name);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Tạo vendor commisstion kpi thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_vendor_commission_kpis($id, Request $request)
    {
        $data = $request->all();

        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'name' => $name,
        ], [
            'name' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM vendor_commission_kpis d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Vendor commisstion kpi không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("vendor_commission_kpis");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật vendor commisstion kpi thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_vendor_commission_kpis_by_id($id)
    {
        $sql = "SELECT d1.id FROM vendor_commission_kpis d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor commisstion kpi không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM vendor_commission_kpis d1 WHERE d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $result =  $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            $data = array();
            if ($numrows > 0) {
                $row = $result->getRow(0);
                $data['id'] =   $row->getString("id");
                $data['name'] = $row->getString("name");
            }

            $message = [
                'status' => true,
                'data' => ['vendor_commission_kpi' => $data],
                'message' => "Lấy vendor commisstion kpi by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_vendor_commission_kpis($id)
    {

        $sql = "SELECT d1.id FROM vendor_commission_kpis d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor commisstion kpi không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("vendor_commission_kpis");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa vendor commisstion kpi thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS COMMISSION KPIs
}
