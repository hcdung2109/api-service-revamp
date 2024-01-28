<?php

namespace Digisource\Users\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Users\Entities\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PHPMailer\PHPMailer\Exception;

class UsersController extends Controller
{
    public function get_profile_by_id(Request $request)
    {
        $user = auth()->user();

        $sql = "SELECT d1.id, d1.access_token, d1.user_name, d1.name, d1.company_id, d1.date_format, d1.thousands_sep, d1.time_format, d1.decimal_point";
        $sql = $sql . " ,d1.avatar, d1.lang_id, d2.group_id AS user_group_id, d4.name AS user_group_name, d4.parent_id AS parent_company_id";
        $sql = $sql . " ,d1.phone, d1.email, d1.name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d2 ON(d1.id = d2.user_id AND d2.status =0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d2.group_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d4 ON(d2.company_id = d4.id)";
        $sql = $sql . " WHERE d1.id=? AND d1.status =0 AND d2.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $results = DB::select($sql, [$user->id]);

        if (!empty($results)) {
            $row = $results[0];
            $arr = array();
            $arr['access_token'] = $row->access_token;
            $arr['id'] = $row->id;
            $arr['phone'] = $row->phone;
            $arr['name'] = $row->name;
            $arr['email'] = $row->email;
            $arr['user_name'] = $row->user_name;
            $arr['avatar_id'] = $row->avatar;
            $arr['company_id'] = $row->company_id;
            $arr['parent_company_id'] = $row->parent_company_id;
            $arr['user_group_id'] = $row->user_group_id;

            $perListChecked = $this->get_group_per($arr['user_group_id']);
            $rels = $this->get_module_id($user->company_id);
            $lines = $this->get_module_parent_id($rels);
            $arr['per'] = $this->buildCategory($arr['user_group_id'], $lines, "", $perListChecked);

            $this->setMessage("Lấy user by id thành công.");
            $this->addData($arr);


        } else {
            $this->setMessage("User không tồn tại.");
        }

        return $this->getResponse();
    }

    public function get_group_per($group_id)
    {
        $sql = "SELECT d1.module_name, d1.ac_name, d1.ac_value FROM res_user_group_per d1 WHERE d1.status =0 AND d1.group_id=? ";

        $result = DB::select($sql, [$group_id]);
        $result = json_decode(json_encode($result), true);
        $numrows = count($result);
        $perListChecked = array();
        for ($i = 0; $i < $numrows; $i++) {
            $row = $result[$i];
            $arr = array();
            $arr[0] = $row["module_name"];
            $arr[1] = $row["ac_name"];
            $arr[2] = $row["ac_value"];
            $perListChecked[$i] = $arr;
        }
        return $perListChecked;
    }

    public function get_module_id($company_id)
    {

        $sql = "SELECT module_id FROM ir_module_rel d1 WHERE d1.status =0 AND d1.rel_id=?";

        $result = DB::select($sql, [$company_id]);
        $result = json_decode(json_encode($result), true);
        $numrows = count($result);
        $rels = [];
        for ($i = 0; $i < $numrows; $i++) {
            $row = $result[$i];
            $module_id = $row["module_id"];
            $rels[count($rels)] = $module_id;
        }
        return  $rels;
    }

    public function get_module_parent_id($rels)
    {
        $sql = "SELECT id, parent_id, name FROM ir_module WHERE status =0 ORDER BY sequence ASC";

        $result = DB::select($sql);
        $result = json_decode(json_encode($result), true);
        $numrows = count($result);
        $lines = [];
        for ($j = 0; $j < $numrows; $j++) {
            $row = $result[$j];
            if ($row["parent_id"] == "") {
                for ($n = 0; $n < count($rels); $n++) {
                    if ($rels[$n] == $row["id"]) {
                        $arr = [];
                        $arr[0] = $row["id"];
                        $arr[1] = $row["parent_id"];
                        $arr[2] = $row["name"];
                        $lines[count($lines)] = $arr;
                        break;
                    }
                }
            } else {
                $arr = [];
                $arr[0] = $row["id"];
                $arr[1] = $row["parent_id"];
                $arr[2] = $row["name"];
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

    public function update_profile(Request $request, $uuid)
    {
        $data = $request->all();

        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $new_password = $data['new_password'];

        $validator = Validator::make($request->all(), [
            'name' => 'max:150',
            'email' => 'max:150',
            'phone' => 'max:20',
            'new_password' => 'max:250',
            'avatar' => 'file|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $user = User::where('status',0)->findOrFail($uuid);

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
            $user->email = $email;
            $user->phone = $phone;

            $user->save();

            $this->setMessage("Cập nhật profile thành công.");
        }

        return $this->getResponse();
    }
}
