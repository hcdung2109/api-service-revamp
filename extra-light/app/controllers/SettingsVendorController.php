<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class SettingsVendorController extends Controller
{
    public $appSession;
    public $msg;
    public $session_user_id;
    public $session_company_id;
    public function __construct()
    {
        global $appSession;
        $this->appSession = $appSession;
        $this->msg = $this->appSession->getTier()->createMessage();
        $this->session_user_id = $this->appSession->getConfig()->getProperty("session_user_id");
        $this->session_company_id = $this->appSession->getConfig()->getProperty("session_company_id");
    }

    // START SETTINGS VENDOR TYPE
    public function get_vendor_type(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ;

        $sql = "SELECT d1.id, d1.name FROM vendor_types d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
            'data' => ['vendor_types' => $data],
            'message' => "Lấy danh sách vendor type thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_vendor_type(Request $request, Response $response)
    {
        $data = $request->request->all();

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
            $builder = $this->appSession->getTier()->createBuilder("vendor_types");
            $vendor_id = $this->appSession->getTool()->getId();
            $builder->add("id", $vendor_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("name", $name);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Tạo vendor type thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_vendor_type($id, Request $request, Response $response)
    {
        $data = $request->request->all();

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
            $sql = "SELECT d1.id FROM vendor_types d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Vendor type không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("vendor_types");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật vendor type thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_vendor_type_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id FROM vendor_types d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor type không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM vendor_types d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['vendor_type' => $data],
                'message' => "Lấy vendor type by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_vendor_type($id, Response $response)
    {

        $sql = "SELECT d1.id FROM vendor_types d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor type không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("vendor_types");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa vendor type thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS VENDOR TYPE

    // START SETTINGS COMMISSION TYPE
    public function get_vendor_commission_type(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM vendor_commission_types d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
            'data' => ['vendor_commission_types' => $data],
            'message' => "Lấy danh sách vendor commisstion type thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_vendor_commission_type(Request $request, Response $response)
    {
        $data = $request->request->all();

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
            $builder = $this->appSession->getTier()->createBuilder("vendor_commission_types");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
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

    public function update_vendor_commission_type($id, Request $request, Response $response)
    {
        $data = $request->request->all();

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

    public function get_vendor_commission_type_by_id($id, Response $response)
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

    public function delete_vendor_commission_type($id, Response $response)
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
    public function get_vendor_commission_kpis(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM vendor_commission_kpis d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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

    public function create_vendor_commission_kpis(Request $request, Response $response)
    {
        $data = $request->request->all();

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
            $builder->add("company_id", $this->session_company_id);
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

    public function update_vendor_commission_kpis($id, Request $request, Response $response)
    {
        $data = $request->request->all();

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

    public function get_vendor_commission_kpis_by_id($id, Response $response)
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

    public function delete_vendor_commission_kpis($id, Response $response)
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

     // START SETTINGS COMMISSION METRIC
     public function get_vendor_commission_metric(Request $request, Response $response)
     {
         $param = $request->query->all();

         $p =  $param['p'] ?? 0;
         $ps =  $param['ps'];

         $sql = "SELECT d1.id, d1.name, d1.description FROM commissions_calculation_metric d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
             $arr['description'] = $result[$i][2];
             $data[] = $arr;
         }

         $message = [
             'status' => true,
             'total' => $arrResult->total,
             'per_page' => $arrResult->per_page,
             'current_page' => $arrResult->current_page,
             'from' => $arrResult->from,
             'to' => $arrResult->to,
             'data' => ['vendor_commission_metric' => $data],
             'message' => "Lấy danh sách vendor commisstion metric thành công."
         ];

         return $this->appSession->getTier()->response($message, $response);
     }

     public function create_vendor_commission_metric(Request $request, Response $response)
     {
         $data = $request->request->all();

         $name = $data['name'];
         $description = $data['description'];

         $validator = new Validator;
         $validator->setMessages([
             'required' => ':attribute không được để trống.',
             'max' => ':attribute không được quá 256 ký tự.',
         ]);

         $validation = $validator->make([
             'name' => $name,
             'description' => $description,
         ], [
             'name' => 'required|max:256',
             'description' => 'required|max:1000'
         ]);

         $validation->validate();

         if ($validation->fails()) {
             $message = [
                 'status' => false,
                 'message' => $validation->errors->firstOfAll(':message', true)
             ];
         } else {
             $builder = $this->appSession->getTier()->createBuilder("commissions_calculation_metric");
             $id = $this->appSession->getTool()->getId();
             $builder->add("id", $id);
             $builder->add("create_uid", $this->session_user_id);
             $builder->add("write_uid", $this->session_user_id);
             $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
             $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
             $builder->add("status", 0);
             $builder->add("company_id", $this->session_company_id);
             $builder->add("name", str_replace("'", "''", $name));
             $builder->add("description", str_replace("'", "''", $description));
             $sql = $this->appSession->getTier()->getInsert($builder);
             $this->msg->add("query", $sql);
             $this->appSession->getTier()->exec($this->msg);

             $message = [
                 'status' => true,
                 'message' => "Tạo vendor commisstion metric thành công."
             ];
         }

         return $this->appSession->getTier()->response($message, $response);
     }

     public function update_vendor_commission_metric($id, Request $request, Response $response)
     {
         $data = $request->request->all();

         $name = $data['name'];
         $description = $data['description'];

         $validator = new Validator;
         $validator->setMessages([
             'required' => ':attribute không được để trống.',
             'max' => ':attribute không được quá 256 ký tự.',
         ]);

         $validation = $validator->make([
            'name' => $name,
            'description' => $description,
        ], [
            'name' => 'max:256',
            'description' => 'max:1000'
        ]);

         $validation->validate();

         if ($validation->fails()) {
             $message = [
                 'status' => false,
                 'message' => $validation->errors->firstOfAll(':message', true)
             ];
         } else {
             $sql = "SELECT d1.id FROM commissions_calculation_metric d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
             $this->msg->add("query", $sql);
             $seen_id = $this->appSession->getTier()->getValue($this->msg);

             if ($seen_id == "") {
                 $message = [
                     'status' => true,
                     'message' => "Vendor commisstion metric không tồn tại."
                 ];
             } else {
                 $builder = $this->appSession->getTier()->createBuilder("commissions_calculation_metric");
                 $builder->update("id", $id);
                 $builder->update("name", $name);
                 $builder->update("description", $description);

                 $sql = $this->appSession->getTier()->getUpdate($builder);
                 $this->msg->add("query", $sql);
                 $result = $this->appSession->getTier()->exec($this->msg);

                 $message = [
                     'status' => true,
                     'message' => "Cập nhật vendor commisstion metric thành công."
                 ];
             }
         }

         return $this->appSession->getTier()->response($message, $response);
     }

     public function get_vendor_commission_metric_by_id($id, Response $response)
     {
         $sql = "SELECT d1.id FROM commissions_calculation_metric d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
         $this->msg->add("query", $sql);
         $seen_id = $this->appSession->getTier()->getValue($this->msg);

         if ($seen_id == "") {
             $message = [
                 'status' => true,
                 'message' => "Vendor commisstion metric không tồn tại."
             ];
         } else {
            $sql = "SELECT d1.id, d1.name, d1.description FROM commissions_calculation_metric d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
             $this->msg->add("query", $sql);
             $result =  $this->appSession->getTier()->getTable($this->msg);
             $numrows = $result->getRowCount();

             $data = array();
             if ($numrows > 0) {
                 $row = $result->getRow(0);
                 $data['id'] =   $row->getString("id");
                 $data['name'] = $row->getString("name");
                 $data['description'] = $row->getString("description");

             }

             $message = [
                 'status' => true,
                 'data' => ['vendor_commission_metric' => $data],
                 'message' => "Lấy vendor commisstion metric by id thành công."
             ];
         }

         return $this->appSession->getTier()->response($message, $response);
     }

     public function delete_vendor_commission_metric($id, Response $response)
     {

         $sql = "SELECT d1.id FROM commissions_calculation_metric d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
         $this->msg->add("query", $sql);
         $seen_id = $this->appSession->getTier()->getValue($this->msg);

         if ($seen_id == "") {
             $message = [
                 'status' => true,
                 'message' => "Vendor commisstion metric không tồn tại."
             ];
         } else {
             $builder = $this->appSession->getTier()->createBuilder("commissions_calculation_metric");
             $builder->add("id", $id);
             $builder->add("status", 1);

             $sql = $this->appSession->getTier()->getUpdate($builder);
             $this->msg->add("query", $sql);
             $result = $this->appSession->getTier()->exec($this->msg);

             $message = [
                 'status' => true,
                 'message' => "Xóa vendor commisstion metric thành công."
             ];
         }
         return $this->appSession->getTier()->response($message, $response);
     }
     // END SETTINGS COMMISSION METRIC
}
