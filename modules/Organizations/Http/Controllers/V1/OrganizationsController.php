<?php

namespace Digisource\Organizations\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Organizations\Entities\Organizations;
use Digisource\Users\Entities\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrganizationsController extends Controller
{
    public function get_organization_manager(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $p =  $param['p'] ?? 1;
        $ps =  $param['ps'];
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";

        $result = DB::table('res_user')
            ->leftjoin('res_user_company','res_user.id','=','res_user_company.user_id')
            ->leftjoin('res_user_group','res_user_company.group_id','=','res_user_group.id')
            ->select("res_user.id", "res_user.name", "res_user_group.name AS group_name")
            ->where("res_user.company_id","=", $user->company_id)
            ->where("res_user.status","=", 0)
            ->orderBy($soft_column, $soft_by)
            ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        $this->setMessage("Lấy danh sách users thành công.");

        return $this->getResponse();

    }
    // CHANGE PASSWORD
    public function update_organization_password_member(Request $request, $id)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'old_password'  => 'required|max:256',
            'password'  => 'required|max:256',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();

        $password = $data['password'];
        $old_password = $data['old_password'];

        $organizations = Organizations::where('status',0)->find($id);

        if ($organizations == null) {
            $this->setMessage("Organizations không tồn tại.");
        } else {
            $user_id = $organizations->user_id;
            $user = User::find($user_id);

            if ($user == null) {
                $this->setMessage("User không tồn tại.");
            }

            $password = Hash::make($password);
            $user->write_date = date('Y-m-d H:i:s');
            $user->password = $password;
            $user->save();

            $this->setMessage("Cập nhật password organizations thành công.");

        }
    }

    // GET LIST ORGANIZATION
    public function get_organization_org_chart(Request $request)
    {
        $user = auth()->user();

        $sql = "SELECT d1.id, d1.manager_id, d2.name, d4.id AS logo_id, d6.name AS group_name, d1.user_id";
        $sql = $sql . " FROM organizations d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id AND d2.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.manager_id = d3.id AND d2.status=0)";
        $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d5 ON(d1.user_id = d5.user_id AND d5.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d6 ON(d5.group_id = d6.id AND d6.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $user->company_id . "' AND d1.status=0";

        $results = DB::select($sql);

        if (!empty($results)) {
            $data = $this->printDepartment($results, 'ROOT', "");
            $this->addData(['organization_org_chart' => $data]);
        }

        return $this->getResponse();
    }

    function printDepartment($managers, $parent_id, $func)
    {
        $count = 0;
        $line = 0;
        $data = array();

        for ($j = 0; $j < count($managers); $j++) {
            $item = $managers[$j];

            if ($item->manager_id == $parent_id) {

                $arr = array();
                $line += 1;
                $arr['id'] = $item->id;
                $arr['manager_id'] = $item->manager_id;
                $arr['name'] = $item->name;
                $arr['logo_id'] = $item->logo_id;
                $arr['group_name'] = $item->group_name;
                $arr['user_id'] = $item->user_id;
                $arr['children'] = $this->printDepartment($managers, $arr['user_id'], $func);
                $data[] = $arr;
            }
        }
        return $data;
    }
}
