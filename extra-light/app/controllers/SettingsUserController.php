<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class SettingsUserController extends Controller
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

    // START USER
    public function get_user(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.phone, d1.email, d1.user_name, d1.password, d2.id AS avatar_id, d1.name AS full_name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='avatar')";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

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
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['phone'] = $result[$i][1];
            $arr['email'] = $result[$i][2];
            $arr['user_name'] = $result[$i][3];
            $arr['password'] = $result[$i][4];
            $arr['avatar_id'] = $result[$i][5];
            $arr['full_name'] = $result[$i][6];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['users' => $data],
            'message' => "Lấy danh sách users thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_user(Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];
        $user_name = $data['user_name'];
        $re_password = $data['password'];
        $email = $data['email'];
        $phone = $data['phone'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu 6 ký tự.',
            'max' => ':attribute không được quá 256 ký tự.',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số điện thoại hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg/png.'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'name' => 'required|max:256',
            'user_name' => 'required|min:6',
            'password' => 'required|min:6',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'avatar' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id, d1.phone, d1.email, d1.user_name FROM res_user d1 WHERE (d1.user_name='" . str_replace("'", "''", $user_name) . "'";
            if ($email != "") {
                $sql = $sql . " OR d1.email = '" . str_replace("'", "''", $email) . "'";
            }
            if ($phone != "") {
                $sql = $sql . " OR d1.phone = '" . str_replace("'", "''", $phone) . "'";
            }

            $sql = $sql . ")";
            $this->msg->add("query", $sql);

            $result = $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            if ($numrows > 0) {
                $row = $result->getRow(0);
                if ($row->getString("user_name") == $user_name) {
                    $message = [
                        'status' => false,
                        'message' => "Tên tài khoản của bạn đã tồn tại."
                    ];
                } else if ($row->getString("email") != "" && $row->getString("email") == $email) {
                    $message = [
                        'status' => false,
                        'message' => "Email của bạn đã tồn tại."
                    ];
                } else if ($row->getString("phone") != "" && $row->getString("phone") == $phone) {
                    $message = [
                        'status' => false,
                        'message' => "Số điện thoại của bạn đã tồn tại."
                    ];
                }
            } else {
                $builder = $this->appSession->getTier()->createBuilder("res_user");
                $user_id = $this->appSession->getTool()->getId();
                $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $re_password);
                $len = $this->appSession->getTool()->lenght($re_password);
                for ($i = 0; $i < $len; $i++) {
                    $s = $s . chr($i + 48);
                }
                $password = $this->appSession->getTool()->toHash("md5", $s);

                $builder->add("id", $user_id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("name", str_replace("'", "''", $name));
                $builder->add("user_name", str_replace("'", "''", $user_name));
                $builder->add("password", str_replace("'", "''", $password));
                $builder->add("email", str_replace("'", "''", $email));
                $builder->add("phone", str_replace("'", "''", $phone));
                $builder->add("date_format", 'YYYY-MM-DD');
                $builder->add("actived", 0);

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result =  $this->appSession->getTier()->exec($this->msg);

                $avatar_id = $this->create_document_user($request, $user_id);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo user thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo user thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_user($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];
        $user_name = $data['user_name'];
        $password = $data['password'];
        $email = $data['email'];
        $phone = $data['phone'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu 6 ký tự.',
            'max' => ':attribute không được quá 256 ký tự.',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số điện thoại hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg/png.'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'name' => 'max:256',
            'user_name' => 'min:6',
            'password' => 'min:6',
            'email' => 'email',
            'phone' => 'numeric',
            'avatar' => 'uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM res_user d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "User không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("res_user");

                $s =  $this->appSession->getTool()->toHash("sha256", "[" . $id . "]" . $password);
                $len =  $this->appSession->getTool()->lenght($password);
                for ($i = 0; $i < $len; $i++) {
                    $s = $s . chr($i + 48);
                }
                $password =  $this->appSession->getTool()->toHash("md5", $s);

                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("name", str_replace("'", "''", $name));
                //$builder->update("user_name", str_replace("'", "''", $user_name));
                $builder->update("password", str_replace("'", "''", $password));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("phone", str_replace("'", "''", $phone));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                $avatar_id = $this->update_document_user($request, $id);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật user thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật user thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_user_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.phone, d1.email, d1.user_name, d1.password, d2.id AS avatar_id, d1.name AS full_name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='avatar')";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";
        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);
            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['phone'] = $row->getString("phone");
            $arr['email'] = $row->getString("email");
            $arr['user_name'] = $row->getString("user_name");
            $arr['password'] = $row->getString("password");
            $arr['avatar_id'] = $row->getString("avatar_id");
            $arr['full_name'] = $row->getString("full_name");

            $message = [
                'status' => true,
                'data' => ['user' => $arr],
                'message' => "Lấy danh sách user by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "User không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_user($id, Response $response)
    {
        $sql = "SELECT d1.id FROM res_user d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "User không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("res_user");
            $builder->update("id", $id);
            $builder->update("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo user thành công"
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo user thất bại"
                ];
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_user(Request $request, $rel_id)
    {
        $file = $request->files->get('avatar');
        $fileName = $file->getClientOriginalName();

        $path = DOC_PATH;
        $dir = "";
        $current_date =  date('Y-m-d');
        if ($current_date != "") {
            $arr_date = explode("-", $current_date);
            if (count($arr_date) > 2) {
                $dir = $arr_date[0] . "/" . +$arr_date[1] . "/" . +$arr_date[2];
            }
        }
        $path = $path . "/" . $dir;
        if (is_dir($path) == false) {
            if (!mkdir($path, 0777, true)) {
                die('Failed to create folders...');
            }
        }

        $file_id = $this->appSession->getTool()->getId();

        $file_name = $path . "/";

        try {

            $file->move($file_name, $file_id);
            $file = fopen($file_name, "wb");

            fwrite($file, base64_decode($file_name));
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload file');
        }


        $extension = "";
        $arr = explode(".", $fileName);
        if (count($arr) > 0) {
            $extension = $arr[count($arr) - 1];
            $name = $arr[0];
        }
        $content_length = 0;

        $builder = $this->appSession->getTier()->createBuilder("document");
        $builder->add("id", $file_id);
        $builder->add("create_uid", $this->session_user_id);
        $builder->add("write_uid", $this->session_user_id);
        $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
        $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
        $builder->add("status", 0);
        $builder->add("company_id", $this->session_company_id);
        $builder->add("name", str_replace("'", "''", $name));
        $builder->add("type", 'file');
        $builder->add("rel_id", $rel_id);
        $builder->add("path", $dir);
        $builder->add("ext", $extension);
        $builder->add("document_type_rel", 'avatar');
        $sql = $this->appSession->getTier()->getInsert($builder);
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        return $file_id;
    }

    public function update_document_user(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='avatar'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $message = [
                'status' => true,
                'message' => "Avatar không tồn tại."
            ];
        } else {

            $file = $request->files->get('avatar');
            if (!empty($file)) {
                $fileName = $file->getClientOriginalName();

                $path = DOC_PATH;
                $dir = "";
                $current_date =  date('Y-m-d');
                if ($current_date != "") {
                    $arr_date = explode("-", $current_date);
                    if (count($arr_date) > 2) {
                        $dir = $arr_date[0] . "/" . +$arr_date[1] . "/" . +$arr_date[2];
                    }
                }
                $path = $path . "/" . $dir;
                if (is_dir($path) == false) {
                    if (!mkdir($path, 0777, true)) {
                        die('Failed to create folders...');
                    }
                }

                $file_name = $path . "/";

                try {

                    $file->move($file_name, $file_id);
                    $file = fopen($file_name, "wb");

                    fwrite($file, base64_decode($file_name));
                    fclose($file);
                } catch (\Exception $e) {
                    throw new \Exception('Failed to upload file');
                }


                $extension = "";
                $arr = explode(".", $fileName);
                if (count($arr) > 0) {
                    $extension = $arr[count($arr) - 1];
                    $name = $arr[0];
                }
                $content_length = 0;

                $builder = $this->appSession->getTier()->createBuilder("document");
                $builder->update("id", $file_id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("name", str_replace("'", "''", $name));
                $builder->update("type", 'file');
                $builder->update("rel_id", $rel_id);
                $builder->update("path", $dir);
                $builder->update("ext", $extension);
                $builder->update("document_type_rel", 'avatar');
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

                return $file_id;
            }
        }
    }

    // END USER

    // START USER DEPARTMENT
    public function get_user_department(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.user_id, d1.rel_id, d1.group_id, d1.company_id";
        $sql = $sql . ", d2.name, d2.user_name, d2.email, d3.name AS group_name, d4.name AS department_name";
        $sql = $sql . " FROM res_user_company d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d1.group_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d4 ON(d1.company_id = d4.id)";
        $sql = $sql . " WHERE d1.status=0 AND (d1.company_id='" . $this->session_company_id . "' OR d4.parent_id='" . $this->session_company_id . "')";

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
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['user_id'] = $result[$i][1];
            $arr['rel_id'] = $result[$i][2];
            $arr['group_id'] = $result[$i][3];
            $arr['department_id'] = $result[$i][4];
            $arr['name'] = $result[$i][5];
            $arr['user_name'] = $result[$i][6];
            $arr['email'] = $result[$i][7];
            $arr['group_name'] = $result[$i][8];
            $arr['department_name'] = $result[$i][9];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['user_departments' => $data],
            'message' => "Lấy danh sách users department thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_user_department(Request $request, Response $response)
    {

        $data = $request->request->all();
        $id = $this->appSession->getTool()->getId();

        $user_id = $data['user_id'];
        $rel_id = $data['rel_id'];
        $group_id = $data['group_id'];
        $department_id  = $data['department_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'user_id' => 'required',
            'group_id' => 'required',
            'department_id' => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM res_user_company d1 WHERE d1.status = 0 AND d1.user_id='" . $user_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id != "") {
                $message = [
                    'status' => false,
                    'message' => "User company đã tồn tại."
                ];
            } else {

                $builder = $this->appSession->getTier()->createBuilder("res_user_company");

                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $department_id);
                $builder->add("user_id", str_replace("'", "''", $user_id));
                $builder->add("rel_id", str_replace("'", "''", $rel_id));
                $builder->add("group_id", str_replace("'", "''", $group_id));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result =  $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo user department thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo user department thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_user_department($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $user_id = $data['user_id'];
        $group_id = $data['group_id'];
        $department_id  = $data['department_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'user_id' => 'required',
            'group_id' => 'required',
            'department_id' => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $sql = "SELECT d1.id FROM res_user_company d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "User department không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("res_user_company");

                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("company_id", str_replace("'", "''", $department_id));
                $builder->update("user_id", str_replace("'", "''", $user_id));
                $builder->update("group_id", str_replace("'", "''", $group_id));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật user department thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật user department thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_user_department_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.user_id, d1.rel_id, d1.group_id, d1.company_id";
        $sql = $sql . ", d2.name, d2.user_name, d2.email, d3.name AS group_name, d4.name AS department_name";
        $sql = $sql . " FROM res_user_company d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d1.group_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d4 ON(d1.company_id = d4.id)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";
        $this->msg->add("query", $sql);
        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);
            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['user_id'] = $row->getString("user_id");
            $arr['rel_id'] = $row->getString("rel_id");
            $arr['group_id'] = $row->getString("group_id");
            $arr['department_id'] = $row->getString("company_id");
            $arr['name'] = $row->getString("name");
            $arr['user_name'] = $row->getString("user_name");
            $arr['email'] = $row->getString("email");
            $arr['group_name'] = $row->getString("group_name");
            $arr['department_name'] = $row->getString("department_name");

            $message = [
                'status' => true,
                'data' => ['user_department' => $arr],
                'message' => "Lấy danh sách user by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "User department không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_user_department($id, Response $response)
    {

        $sql = "SELECT d1.id FROM res_user_company d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "User không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("res_user_company");
            $builder->update("id", $id);
            $builder->update("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo user thành công"
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo user thất bại"
                ];
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // END USER DEPARTMENT

    // START LOAD PERMISSION
    public function load_permission(Request $request, Response $response)
    {
        $param = $request->query->all();

        $group_id = $param['group_id'];

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 10;

        $sql = "SELECT d1.id, d1.category_id, d1.ac_value, d2.name";
        $sql = $sql . " FROM res_user_group_per d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_group_per_category d2 ON(d1.category_id = d2.id)";
        $sql = $sql . " WHERE d1.category_id != '' AND d1.group_id='" . $group_id . "'";
        $sql = $sql . " AND d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date ASC");

        $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

        $this->msg->add("query", $arrResult->sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['category_id'] = $result[$i][1];
            $arr['ac_value'] = $result[$i][2];
            $arr['group_id'] = $result[$i][3];
            $arr['name'] = $result[$i][4];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['permission' => $data],
            'message' => "Lấy danh sách user permission thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // END USER PERMISSION
}
