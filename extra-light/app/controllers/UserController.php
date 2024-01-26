<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;
use Firebase\JWT\JWT;

class UserController extends Controller
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
    public function get_profile_by_id(Request $request, Response $response)
    {
        $sql = "SELECT d1.id, d1.access_token, d1.user_name, d1.name, d1.company_id, d1.date_format, d1.thousands_sep, d1.time_format, d1.decimal_point";
        $sql = $sql . " ,d1.avatar, d1.lang_id, d2.group_id AS user_group_id, d4.name AS user_group_name, d4.parent_id AS parent_company_id";
        $sql = $sql . " ,d1.phone, d1.email, d1.name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d2 ON(d1.id = d2.user_id AND d2.status =0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d2.group_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d4 ON(d2.company_id = d4.id)";
        $sql = $sql . " WHERE d1.id='" . $this->session_user_id . "' AND d1.status =0 AND d2.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {
            $row = $result->getRow(0);

            $arr = array();
            $arr['access_token'] = $row->getString("access_token");
            $arr['id'] = $row->getString("id");
            $arr['phone'] = $row->getString("phone");
            $arr['name'] = $row->getString("name");
            $arr['email'] = $row->getString("email");
            $arr['user_name'] = $row->getString("user_name");
            $arr['avatar_id'] = $row->getString("avatar");
            $arr['company_id'] = $row->getString("company_id");
            $arr['parent_company_id'] = $row->getString("parent_company_id");
            $arr['user_group_id'] = $row->getString("user_group_id");

            $perListChecked = $this->get_group_per($arr['user_group_id']);
            $rels = $this->get_module_id();
            $lines = $this->get_module_parent_id($rels);

            $arr['per'] = $this->buildCategory($arr['user_group_id'], $lines, "", $perListChecked);

            $message = [
                'status' => true,
                'data' => ['user' => $arr],
                'message' => "Lấy user by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "User không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }


    public function is_jwt_valid($jwt)
    {
        // split the jwt
        $tokenParts = explode('.', $jwt);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        // check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
        $expiration = json_decode($payload)->exp;

        $is_token_expired = ($expiration - time()) < 0;

        // build a signature based on the header and payload using the secret
        $base64_url_header = base64_encode($header);
        $base64_url_payload = base64_encode($payload);
        $signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, SECRET_KEY, true);
        $base64_url_signature = base64_encode($signature);

        // verify it matches the signature provided in the jwt
        $is_signature_valid = ($base64_url_signature === $signature_provided);

        if ($is_token_expired) {
            return FALSE;
        } else {
            return TRUE;
        }
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

    // UPDATE PROFILE
    public function update_profile($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $new_password = $data['new_password'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số điện thoại hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg, png.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'name' => 'max:150',
            'email' => 'max:150',
            'phone' => 'max:20',
            'new_password' => 'max:250',
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
                    'status' => true,
                    'message' => "User không tồn tại."
                ];
            } else {
                if ($new_password != "") {
                    $s = $this->appSession->getTool()->toHash("sha256", "[" . $id . "]" . $new_password);
                    $len = $this->appSession->getTool()->lenght($new_password);
                    for ($i = 0; $i < $len; $i++) {
                        $s = $s . chr($i + 48);
                    }
                    $new_password = $this->appSession->getTool()->toHash("md5", $s);
                }


                $avatar_id = $this->update_avatar($request, $id);

                //var_dump($avatar_id);exit;


                $builder = $this->appSession->getTier()->getBuilder("res_user");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("name", str_replace("'", "''", $name));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("phone", str_replace("'", "''", $phone));
                $builder->update("password", str_replace("'", "''", $new_password));
                $builder->update("avatar", $avatar_id);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật profile thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật profile thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_avatar(Request $request, $rel_id)
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

    public function update_avatar(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='avatar'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $file_id = $this->create_avatar($request, $rel_id);
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
}
