<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class SettingsOrganizationController extends Controller
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

    // START SETTINGS ROLE (USER GROUP)
    public function get_role(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM res_user_group d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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
            'data' => ['roles' => $data],
            'message' => "Lấy danh sách roles thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_role(Request $request, Response $response)
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
            $builder = $this->appSession->getTier()->createBuilder("res_user_group");
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
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo role thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo role thất bại"
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_role($id, Request $request, Response $response)
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
            $sql = "SELECT d1.id FROM res_user_group d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Role không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("res_user_group");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật role thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_role_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id FROM res_user_group d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Role không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM res_user_group d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['role' => $data],
                'message' => "Lấy role by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_role($id, Response $response)
    {

        $sql = "SELECT d1.id FROM res_user_group d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Role không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("res_user_group");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa role thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS ROLE (USER GROUP)


    // START DEPARTMENT
    public function get_department(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.parent_id, d1.name";
        $sql = $sql . " FROM res_company d1";
        $sql = $sql . " WHERE d1.status=0 AND (d1.company_id='" . $this->session_company_id . "' OR d1.parent_id='" . $this->session_company_id . "')";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date ASC");

            $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

            $this->msg->add("query", $arrResult->sql);

            $result = $this->appSession->getTier()->getArray($this->msg);
        } else {
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->getArray($this->msg);
        }
        $data = $this->printDepartment($result, 'ROOT', "");

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['departments' => $data],
            'message' => "Lấy danh sách departments thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    // START DEPARTMENT ALL
    public function get_department_all(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.parent_id, d1.name";
        $sql = $sql . " FROM res_company d1";
        $sql = $sql . " WHERE d1.status=0 AND (d1.company_id='" . $this->session_company_id . "')";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date ASC");

            $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

            $this->msg->add("query", $arrResult->sql);

            $result = $this->appSession->getTier()->getArray($this->msg);
        } else {
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->getArray($this->msg);
        }
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            if ($result[$i][1] != "ROOT") {
                $arr = array();
                $arr['id'] = $result[$i][0];
                $arr['parent_id'] = $result[$i][1];
                $arr['name'] = $result[$i][2];
                $data[] = $arr;
            }
            }

        $message = [
            'status' => true,
            'total' => $arrResult->total - 1,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to -1,
            'data' => ['departments' => $data],
            'message' => "Lấy danh sách departments thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    function printDepartment($managers, $parent_id, $func)
    {
        $count = 0;
        $line = 0;
        $data = array();
        for ($j = 0; $j < count($managers); $j++) {
            if ($managers[$j][1] == $parent_id) {
                $count += 1;
            }
        }
        for ($j = 0; $j < count($managers); $j++) {
            if ($managers[$j][1] == $parent_id) {

                $arr = array();
                $line += 1;
                $arr['id'] = $managers[$j][0];
                $arr['parent_id'] = $managers[$j][1];
                $arr['name'] = $managers[$j][2];
                $arr['children'] = $this->printDepartment($managers, $arr['id'], $func);
                $data[] = $arr;
            }
        }
        return $data;
    }

    public function create_department(Request $request, Response $response)
    {
        $data = $request->request->all();
        $id = $this->appSession->getTool()->getId();

        $name = $data['name'];
        $parent_id = $data['parent_id'];

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
            $builder = $this->appSession->getTier()->createBuilder("res_company");
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("name", str_replace("'", "''", $name));
            $builder->add("parent_id", str_replace("'", "''", $parent_id));

            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo department thành công"
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo department thất bại"
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_department($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];
        $parent_id = $data['parent_id'];

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
            $sql = "SELECT d1.id FROM res_company d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Departments không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("res_company");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("name", str_replace("'", "''", $name));
                $builder->update("parent_id", str_replace("'", "''", $parent_id));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật department thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật department thất bại"
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_department_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.name, d1.parent_id, d2.name AS parent_name";
        $sql = $sql . " FROM res_company d1";
        $sql = $sql . " LEFT OUTER JOIN res_company d2 ON(d1.parent_id = d2.id)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);
            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['name'] = $row->getString("name");
            $arr['parent_id'] = $row->getString("parent_id");
            $arr['parent_name'] = $row->getString("parent_name");


            $message = [
                'status' => true,
                'data' => ['department' => $arr],
                'message' => "Lấy danh sách department by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Departments không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_department($id, Response $response)
    {

        $sql = "SELECT d1.id FROM res_company d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Departments không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("res_company");
            $builder->update("id", $id);
            $builder->update("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa departments thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END DEPARTMENT

    // START SETTINGS PERMISSION
    public function get_permission(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM res_user_group d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

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

            $perListChecked = $this->get_group_per($arr['id']);
            $rels = $this->get_module_id();
            $lines = $this->get_module_parent_id($rels);

            $arr['per'] = $this->buildCategory($arr['id'], $lines, "", $perListChecked);

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['permissions' => $data],
            'message' => "Lấy danh sách permissions thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_permission($group_id, Request $request, Response $response)
    {
        $data = $request->request->all();
        $module_name = $data['module_name'];
        $ac_name = $data['ac_name'];
        $ac_value = $data['ac_value'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'module_name' => $module_name,
            'ac_name' => $ac_name,
            'ac_value' => $ac_value,
        ], [
            'module_name' => 'required',
            'ac_name' => 'required',
            'ac_value' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $sql = "SELECT d1.id FROM res_user_group_per d1 WHERE d1.group_id='" . $group_id . "' AND d1.module_name='" . $module_name . "' AND d1.ac_name='" . $ac_name . "'";
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->getArray($this->msg);
            $numrows = count($result);
            if ($numrows > 0) {
                $row = $result[0];
                $id = $row[0];
                $sql = "UPDATE res_user_group_per SET ac_value ='" . $ac_value . "'";
                $sql = $sql . ", write_date=NOW()";
                $sql = $sql . " WHERE id='" . $id . "'";
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật permission group thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật  permission group thất bại"
                    ];
                }
            } else {
                $builder = $this->appSession->getTier()->createBuilder("res_user_group_per");
                $id = $this->appSession->getTool()->getId();
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("group_id", str_replace("'", "''", $group_id));
                $builder->add("module_name", str_replace("'", "''", $module_name));
                $builder->add("ac_name", str_replace("'", "''", $ac_name));
                $builder->add("ac_value", str_replace("'", "''", $ac_value));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo permission group thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo permission group thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_group_per($group_id)
    {
        $sql = "SELECT d1.module_name, d1.ac_name, d1.ac_value FROM res_user_group_per d1 WHERE d1.status =0 AND d1.group_id='" . $group_id . "' ";

        $this->msg->add("query", $sql);
        $result = $this->appSession->getTier()->getArray($this->msg);
        $numrows = count($result);
        $perListChecked = array();
        for ($i = 0; $i < $numrows; $i++) {
            $row = $result[$i];
            $arr = array();
            $arr[0] = $row[0];
            $arr[1] = $row[1];
            $arr[2] = $row[2];
            $perListChecked[$i] = $arr;
        }
        return $perListChecked;
    }

    public function get_module_id()
    {

        $sql = "SELECT module_id FROM ir_module_rel d1 WHERE d1.status =0 AND d1.rel_id='" . $this->session_company_id . "'";

        $this->msg->add("query", $sql);
        $result = $this->appSession->getTier()->getArray($this->msg);
        $numrows = count($result);
        $rels = [];
        for ($i = 0; $i < $numrows; $i++) {
            $row = $result[$i];
            $module_id = $row[0];
            $rels[count($rels)] = $module_id;
        }
        return  $rels;
    }

    public function get_module_parent_id($rels)
    {
        $sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 ORDER BY sequence ASC";

        $this->msg->add("query", $sql);
        $result = $this->appSession->getTier()->getArray($this->msg);
        $numrows = count($result);
        $lines = [];
        for ($j = 0; $j < $numrows; $j++) {
            $row = $result[$j];
            if ($row[1] == "") {
                for ($n = 0; $n < count($rels); $n++) {
                    if ($rels[$n] == $row[0]) {
                        $arr = [];
                        $arr[0] = $row[0];
                        $arr[1] = $row[1];
                        $arr[2] = $row[2];
                        $lines[count($lines)] = $arr;
                        break;
                    }
                }
            } else {
                $arr = [];
                $arr[0] = $row[0];
                $arr[1] = $row[1];
                $arr[2] = $row[2];
                $lines[count($lines)] = $arr;
            }
        }
        return  $lines;
    }

    function per($perList, $module_name, $ac_name)
    {
        for ($i = 0; $i < count($perList); $i++) {
            if ($perList[$i][0] == $module_name && $perList[$i][1] == $ac_name) {
                if ($perList[$i][2] == "1") {
                    return true;
                }
                return false;
            }
        }
        return true;
    }

    public function buildCategory($rel_id, $lines, $parent_id, $perListChecked)
    {
        $count = 0;
        $line = 0;
        for ($j = 0; $j < count($lines); $j++) {

            if ($lines[$j][1] == $parent_id) {
                $count += 1;
            }
        }
        $data = array();
        for ($j = 0; $j < count($lines); $j++) {
            if ($lines[$j][1] == $parent_id) {

                $arr = array();
                $id = $lines[$j][0];
                $name = $lines[$j][2];
                $line += 1;
                $arr['id'] = $id;
                $arr['name'] = $name;

                if ($this->per($perListChecked, $lines[$j][0], "view") == true) {
                    $arr['view'] = true;
                } else {
                    $arr['view'] = false;
                };

                if ($this->per($perListChecked, $lines[$j][0], "new") == true) {
                    $arr['new'] = true;
                } else {
                    $arr['new'] = false;
                };

                if ($this->per($perListChecked, $lines[$j][0], "edit") == true) {
                    $arr['edit'] = true;
                } else {
                    $arr['edit'] = false;
                };

                if ($this->per($perListChecked, $lines[$j][0], "delete") == true) {
                    $arr['delete'] = true;
                } else {
                    $arr['delete'] = false;
                };

                $arr['children_per'] = $this->buildCategory($rel_id, $lines, $id, $perListChecked);
                $data[] = $arr;
            }
        }
        return $data;
    }
}
