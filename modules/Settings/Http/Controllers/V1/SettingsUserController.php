<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Users\Entities\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPMailer\PHPMailer\Exception;

class SettingsUserController extends Controller
{
    // START USER
    public function get_user(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $result = DB::table('res_user AS d1')
                    ->leftjoin('document AS d2', function (JoinClause $join) {
                        $join->on('d1.id', '=', 'd2.rel_id')
                              ->where('d2.document_type_rel', '=', 'avatar');
                    })
                    ->select("d1.id", "d1.phone", "d1.email", "d1.user_name", "d1.password", "d2.id AS avatar_id", "d1.name AS full_name")
                    ->where("d1.company_id","=", $user->company_id)
                    ->where("d1.status","=", 0)
                    ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        $this->setMessage("Lấy danh sách users thành công.");

        return $this->getResponse();
    }

    public function create_user(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'user_name' => 'required|min:6|unique:res_user',
            'password' => 'required|min:6',
            'email' => 'required|email|unique:res_user',
            'phone' => 'required|numeric|unique:res_user',
            'avatar' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();

        $name = $data['name'];
        $user_name = $data['user_name'];
        $new_password = $data['password'];
        $email = $data['email'];
        $phone = $data['phone'];

        $new = new User();

        if (!empty($new_password)) {
            $password = Hash::make($new_password);
            $new->password = $password;
        }

        if ($request->hasFile('avatar')) {
            $avatar_id = Utils::updateFile($request->avatar, Constant::FILE_AVATAR, $user->id, $user->company_id);
            if ($avatar_id instanceof Exception) {
                return [
                    'status' => false,
                    'message' => __("Lỗi cập nhật ảnh")
                ];
            }
            $new->avatar = $avatar_id;
        }

        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->name = $name;
        $new->user_name = $user_name;
        $new->company_id = $user->company_id;
        $new->status = 0;
        $new->email = $email;
        $new->phone = $phone;
        $new->date_format = "YYYY-MM-DD";
        $new->actived = 0;
        $new->save();

        $this->addData([]);
        $this->setMessage("Tạo user thành công");

        return $this->getResponse();
    }

    public function update_user(Request $request, $uuid)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256',
            'user_name' => 'required|min:6',
            //'password' => 'required|min:6',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'avatar' => 'image|max:2048',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();

        $name = $data['name'];
        $user_name = $data['user_name'];
        $new_password = $data['password'];
        $email = $data['email'];
        $phone = $data['phone'];

        $user = User::where('status',0)->find($uuid);

        if ($user == null) {
            $this->setMessage("User không tồn tại.");
        } else {
            if (!empty($new_password)) {
                $password = Hash::make($new_password);
                $user->password = $password;
            }

            if ($request->hasFile('avatar')) {
                $avatar_id = Utils::updateFile($request->avatar, Constant::FILE_AVATAR, $user->id, $user->company_id);
                if ($avatar_id instanceof Exception) {
                    return [
                        'status' => false,
                        'message' => __("Lỗi cập nhật ảnh")
                    ];
                }
                $user->avatar = $avatar_id;
            }

            $user->write_date = date('Y-m-d H:i:s');
            $user->name = $name;
            $user->user_name = $user_name;
            $user->email = $email;
            $user->phone = $phone;

            $user->save();

            $this->setMessage("Cập nhật profile thành công.");
        }

        return $this->getResponse();
    }

    public function get_user_by_id(Request $request, $uuid)
    {
        $sql = "SELECT d1.id, d1.phone, d1.email, d1.user_name, d1.password, d2.id AS avatar_id, d1.name AS full_name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='avatar')";
        $sql = $sql . " WHERE d1.id=? AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $results = DB::select($sql, [$uuid]);

        if (!empty($results)) {
            $row = $results[0];
            $arr = array();

            $arr['id'] = $row->id;
            $arr['phone'] = $row->phone;
            $arr['email'] = $row->email;
            $arr['user_name'] = $row->user_name;
            $arr['avatar_id'] = $row->avatar_id;
            $arr['password'] = '';
            $arr['full_name'] = $row->full_name;

            $this->setMessage("Lấy user by id thành công.");
            $this->addData($arr);

        } else {
            $this->setMessage("User không tồn tại.");
        }

        return $this->getResponse();
    }

    public function delete_user(Request $request, $uuid)
    {
        $user = User::where('status',0)->find($uuid);

        if ($user == null) {
            $this->setMessage("User không tồn tại.");
        } else {
            $user->status = 1;
            $user->save();

            $this->setMessage("Xóa thành công.");
        }

        return $this->getResponse();
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
        $builder->add("company_id", $user->company_id);
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
    public function get_user_department(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $p =  $param['p'] ?? 1;
        $ps =  $param['ps']??10;

        $result = DB::table('res_user_company')
            ->leftJoin('res_user', 'res_user_company.user_id', '=', 'res_user.id')
            ->leftJoin('res_user_group', 'res_user_company.group_id', '=', 'res_user_group.id')
            ->leftJoin('res_company', 'res_user_company.company_id', '=', 'res_company.id')
            ->select("res_user_company.id", "res_user_company.user_id", "res_user_company.rel_id", "res_user_company.group_id", "res_user_company.company_id", "res_user.name", "res_user.user_name","res_user.email","res_user_group.name AS group_name","res_company.name AS department_name")
            ->where(function ($query) use ($user) {
                $query->where('res_user_company.company_id', '=', $user->company_id)
                    ->orWhere('res_company.parent_id', '=', $user->company_id);
            })
            ->where("res_user_company.status","=", 0)
            ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        $this->setMessage("Thành công.");

        return $this->getResponse();
    }

    public function create_user_department(Request $request)
    {

        $data = $request->all();
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

    public function update_user_department($id, Request $request)
    {
        $data = $request->all();

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

    public function get_user_department_by_id($id)
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

    public function delete_user_department($id)
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
    public function load_permission(Request $request)
    {
        $param = $request->query->all();

        $group_id = $param['group_id'];

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 10;

        $sql = "SELECT d1.id, d1.category_id, d1.ac_value, d2.name";
        $sql = $sql . " FROM res_user_group_per d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_group_per_category d2 ON(d1.category_id = d2.id)";
        $sql = $sql . " WHERE d1.category_id != '' AND d1.group_id='" . $group_id . "'";
        $sql = $sql . " AND d1.company_id='" . $user->company_id . "' AND d1.status=0";

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
