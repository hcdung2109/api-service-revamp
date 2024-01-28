<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Users\Entities\ResUserCompany;
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'group_id' => 'required',
            'department_id' => 'required'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();

        $user_id = $data['user_id'];
        $rel_id = $data['rel_id'];
        $group_id = $data['group_id'];
        $department_id  = $data['department_id']; // Hà nội

        $user = ResUserCompany::where('status',0)->where('user_id', $user_id)->first();

        if ($user != null) {
            $this->setMessage("User company đã tồn tại.");
        } else {
            $new = new ResUserCompany();
            $new->id = uniqid();
            $new->create_uid = $user->id;
            $new->write_uid = $user->id;
            $new->create_date = date('Y-m-d H:i:s');
            $new->write_date = date('Y-m-d H:i:s');
            $new->company_id = $department_id;
            $new->rel_id = $rel_id;
            $new->group_id = $group_id;
            $new->status = 0;
            $new->user_id = $user_id;
            $new->save();

            $this->setMessage("Tạo thành công.");
        }

        return $this->getResponse();
    }

    public function update_user_department(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'group_id' => 'required',
            'department_id' => 'required'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();

        $user_id = $data['user_id'];
        $group_id = $data['group_id'];
        $department_id  = $data['department_id'];

        $user = ResUserCompany::where('status',0)->where('id', $id)->first();

        if ($user == null) {
            $this->setMessage("User department không tồn tại.");
        } else {

            $user->company_id = $department_id;
            $user->group_id = $group_id;
            $user->user_id = $user_id;
            $user->save();

            $this->setMessage("Cập nhật user department thành công");
        }

        return $this->getResponse();
    }

    public function get_user_department_by_id(Request $request, $id)
    {
        $sql = "SELECT d1.id, d1.user_id, d1.rel_id, d1.group_id, d1.company_id";
        $sql = $sql . ", d2.name, d2.user_name, d2.email, d3.name AS group_name, d4.name AS department_name";
        $sql = $sql . " FROM res_user_company d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d1.group_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d4 ON(d1.company_id = d4.id)";
        $sql = $sql . " WHERE d1.id=? AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $results = DB::select($sql, [$id]);

        if (!empty($results)) {
            $row = $results[0];
            $arr = array();

            $arr['id'] = $row->id;
            $arr['user_id'] = $row->user_id;
            $arr['rel_id'] = $row->rel_id;
            $arr['group_id'] = $row->group_id;
            $arr['department_id'] = $row->company_id;
            $arr['name'] = $row->name;
            $arr['user_name'] = $row->user_name;
            $arr['email'] = $row->email;
            $arr['group_name'] = $row->group_name;
            $arr['department_name'] = $row->department_name;

            $this->addData(['user_department' => $arr]);

        } else {
            $this->setMessage("User department không tồn tại.");
        }

        return $this->getResponse();
    }

    public function delete_user_department(Request $request,$id)
    {
        $user = ResUserCompany::where('status',0)->where('id', $id)->first();

        if ($user == null) {
            $this->setMessage("User không tồn tại.");
        } else {

            $user->status = 1;
            $user->save();
        }

        return $this->getResponse();
    }

    public function load_permission(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];
        $group_id = $param['group_id'];

        $result = DB::table('res_user_group_per AS d1')
            ->leftjoin('res_user_group_per_category AS d2','res_user_group_per.category_id','=','res_user_group_per_category.id')
            ->select("res_user_group_per.id", "res_user_group_per.category_id", "res_user_group_per.ac_value", "res_user_group_per_category.name","res_user_group_per.group_id")
            ->where("res_user_group_per.category_id","!=", '')
            ->where("res_user_group_per.group_id","=", $group_id)
            ->where("res_user_group_per.company_id","=", $user->company_id)
            ->where("res_user_group_per.status","=", 0)
            ->orderBy('res_user_group_per.create_date', 'ASC')
            ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        $this->setMessage("Lấy danh sách users thành công.");

        return $this->getResponse();
    }



}
