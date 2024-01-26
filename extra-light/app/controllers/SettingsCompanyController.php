<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class SettingsCompanyController extends Controller
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

    // START SETTINGS COMPANY INDUSTRIES
    public function get_company_industries(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM industries d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
            'data' => ['company_industries' => $data],
            'message' => "Lấy danh sách company industries thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_company_industries(Request $request, Response $response)
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
            $builder = $this->appSession->getTier()->createBuilder("industries");
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
                'message' => "Tạo company industries thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_company_industries($id, Request $request, Response $response)
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
            $sql = "SELECT d1.id FROM industries d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Company industries không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("industries");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật company industries thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_company_industries_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id FROM industries d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Company industries không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM industries d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['company_industrie' => $data],
                'message' => "Lấy company industries by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_company_industries($id, Response $response)
    {

        $sql = "SELECT d1.id FROM industries d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Company industries không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("industries");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa company industries thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS COMPANY INDUSTRIES

    // START SETTINGS COMPANY SOURCE
    public function get_company_sources(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM sources d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
            'data' => ['company_sources' => $data],
            'message' => "Lấy danh sách company sources thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_company_sources(Request $request, Response $response)
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

            $builder = $this->appSession->getTier()->createBuilder("sources");
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
                'message' => "Tạo company sources thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_company_sources($id, Request $request, Response $response)
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

            $sql = "SELECT d1.id FROM sources d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Company sources không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("sources");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật company sources thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_company_sources_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id FROM sources d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "company sources không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM sources d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['company_source' => $data],
                'message' => "Lấy company source by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_company_sources($id, Response $response)
    {

        $sql = "SELECT d1.id FROM sources d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Company source không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("sources");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa company sources thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS COMPANY SOURCE

}
