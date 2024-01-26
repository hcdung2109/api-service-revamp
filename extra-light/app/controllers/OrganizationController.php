<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;
use Firebase\JWT\JWT;

class OrganizationController extends Controller
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

    public function get_organization_member(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];

        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.location_id, d1.first_name, d1.last_name, d1.title, d1.phone, d1.email, d1.create_date";
        $sql = $sql . ", d8.user_name, d9.id AS manager_id, d12.name AS group_name";
        $sql = $sql . ", d1.national_id, d1.city_id, d1.district_id, d1.ward_id";
        $sql = $sql . ", d3.name AS location_name, d4.name AS national_name, d5.name AS city_name, d6.name AS district_name, d7.name AS ward_name";
        $sql = $sql . " FROM organizations d1";
        $sql = $sql . " LEFT OUTER JOIN res_address d3 ON(d1.location_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_country d4 ON(d1.national_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.city_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.district_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d7 ON(d1.ward_id = d7.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d8 ON(d1.user_id = d8.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d9 ON(d1.manager_id = d9.id)";
        $sql = $sql . " LEFT OUTER JOIN document d10 ON(d8.id = d10.rel_id AND d10.document_type_rel='avatar')";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d11 ON(d1.user_id = d11.user_id AND d11.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d12 ON(d11.group_id = d12.id AND d12.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.title", "d1.phone", "d1.email", "d8.user_name"], $search) . ")";
        }


        $soft = "{$soft_column}" . " " . "{$soft_by}";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1." . $soft);

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
            $arr_managers = array();
            $arr_location = array();
            $arr_national = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();

            $arr['id'] = $result[$i][0];
            $arr['first_name'] = $result[$i][2];
            $arr['last_name'] = $result[$i][3];
            $arr['title'] = $result[$i][4];
            $arr['phone'] = $result[$i][5];
            $arr['email'] = $result[$i][6];
            $arr['create_date'] = $result[$i][7];
            $arr['user_name'] = $result[$i][8];
            $arr['manager_id'] = $result[$i][9];
            $arr['group_name'] = $result[$i][10];

            $arr_location['id'] = $result[$i][1];
            $arr_location['name'] = $result[$i][15];
            $arr['location'] = $arr_location;

            $arr_national['id'] = $result[$i][11];
            $arr_national['name'] = $result[$i][16];
            $arr['national'] = $arr_national;

            $arr_city['id'] = $result[$i][12];
            $arr_city['name'] = $result[$i][17];
            $arr['city'] = $arr_city;

            $arr_district['id'] = $result[$i][13];
            $arr_district['name'] = $result[$i][18];
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $result[$i][14];
            $arr_ward['name'] = $result[$i][19];
            $arr['ward'] = $arr_ward;

            $sql = "SELECT d1.id, d1.name, d2.id AS logo_id FROM res_user d1";
            $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.id='" . $arr['manager_id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_managers = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_managers); $j++) {
                $arr_manager = array();
                $arr_manager['id'] = $result_managers[$j][0];
                $arr_manager['name'] = $result_managers[$j][1];
                $arr_manager['logo_id'] = $result_managers[$j][2];
                $arr_managers[] = $arr_manager;
            }

            $arr['report_to'] = $arr_managers;
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['organizations' => $data],
            'message' => "Lấy danh sách organizations thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    public function create_organization_member(Request $request, Response $response)
    {
        $data = $request->request->all();

        $location_id = $data['location_id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $title = $data['title'];
        $phone = $data['phone'];
        $email = $data['email'];
        $birth_day = $data['birth_day'];
        $national_id = $data['national_id'];
        $department_id = $data['department_id'];
        $role_id = $data['role_id'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $manager_id = $data['manager_id'];
        $user_name = $data['user_name'];
        $password = $data['password'];

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
            'location_id' => 'required',
            'first_name' => 'required|max:1000',
            'last_name' => 'required|max:1000',
            'title' => 'required|max:10000',
            'phone' => 'required|max:256',
            'email' => 'required|email',
            'birth_day' => 'required|date:Y-m-d',
            'national_id' => 'required',
            'department_id' => 'required',
            'role_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required|max:10000',
            'manager_id' => 'required',
            'user_name' => 'required|max:256',
            'password'  => 'required|max:256',
            'confirm_password' => 'required|same:password',
            'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',

        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
            if ($email != "") {
                $sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
            }
            if ($phone != "") {
                $sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
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

                $full_name = $first_name + $last_name;

                $user_id = $this->appSession->getTool()->getId();

                $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
                $len = $this->appSession->getTool()->lenght($password);
                for ($i = 0; $i < $len; $i++) {
                    $s = $s . chr($i + 48);
                }
                $password = $this->appSession->getTool()->toHash("md5", $s);

                $builder = $this->appSession->getTier()->createBuilder("res_user");
                $builder->add("id", $user_id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("name", str_replace("'", "''", $full_name));
                $builder->add("user_name", str_replace("'", "''", $user_name));
                $builder->add("email", str_replace("'", "''", $email));
                $builder->add("phone", str_replace("'", "''", $phone));
                $builder->add("password", str_replace("'", "''", $password));
                $builder->add("lang_id", '23');
                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $res_user_company_id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("res_user_company");
                $builder->add("id", $res_user_company_id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $department_id);
                $builder->add("user_id", $user_id);
                $builder->add("group_id", $role_id);

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);


                $builder = $this->appSession->getTier()->createBuilder("organizations");
                $id = $this->appSession->getTool()->getId();
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("location_id", $location_id);
                $builder->add("first_name", str_replace("'", "''", $first_name));
                $builder->add("last_name", str_replace("'", "''", $last_name));
                $builder->add("title", str_replace("'", "''", $title));
                $builder->add("phone", str_replace("'", "''", $phone));
                $builder->add("email", str_replace("'", "''", $email));
                $builder->add("birthday", str_replace("'", "''", $birth_day));
                $builder->add("national_id", str_replace("'", "''", $national_id));
                $builder->add("department_id", str_replace("'", "''", $department_id));
                $builder->add("city_id", str_replace("'", "''", $city_id));
                $builder->add("district_id", str_replace("'", "''", $district_id));
                $builder->add("ward_id", str_replace("'", "''", $ward_id));
                $builder->add("address", str_replace("'", "''", $address));
                $builder->add("manager_id", str_replace("'", "''", $manager_id));
                $builder->add("user_id", str_replace("'", "''", $user_id));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result =  $this->appSession->getTier()->exec($this->msg);

                $logo_id = $this->create_image_profile($request, $user_id);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo organization thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo organization thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    public function update_organization_member($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $location_id = $data['location_id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $title = $data['title'];
        $phone = $data['phone'];
        $email = $data['email'];
        $birth_day = $data['birth_day'];
        $national_id = $data['national_id'];
        $department_id = $data['department_id'];
        $role_id = $data['role_id'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $manager_id = $data['manager_id'];

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
            'location_id' => 'required',
            'first_name' => 'max:1000',
            'last_name' => 'max:1000',
            'title' => 'max:10000',
            'phone' => 'max:256',
            'email' => 'email',
            'birth_day' => 'date:Y-m-d',
            'national_id' => 'required',
            'department_id' => 'required',
            'role_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'max:10000',
            'manager_id' => 'required',
            'logo' => 'uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM organizations d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Organizations không tồn tại."
                ];
            } else {
                $sql = "SELECT d1.user_id FROM organizations d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
                $this->msg->add("query", $sql);
                $user_id = $this->appSession->getTier()->getValue($this->msg);

                $sql = "SELECT d1.id FROM res_user_company d1 WHERE d1.status=0 AND d1.user_id='" . $user_id . "'";
                $this->msg->add("query", $sql);
                $res_user_company_id = $this->appSession->getTier()->getValue($this->msg);

                $full_name = $first_name + $last_name;

                $builder = $this->appSession->getTier()->createBuilder("res_user");
                $builder->update("id", $user_id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("name", str_replace("'", "''", $full_name));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("phone", str_replace("'", "''", $phone));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);


                $builder = $this->appSession->getTier()->createBuilder("res_user_company");
                $builder->update("id", $res_user_company_id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("company_id", $department_id);
                $builder->update("user_id", $user_id);
                $builder->update("group_id", $role_id);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $builder = $this->appSession->getTier()->createBuilder("organizations");
                $builder->update("id", $id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("location_id", $location_id);
                $builder->update("first_name", str_replace("'", "''", $first_name));
                $builder->update("last_name", str_replace("'", "''", $last_name));
                $builder->update("title", str_replace("'", "''", $title));
                $builder->update("phone", str_replace("'", "''", $phone));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("birthday", str_replace("'", "''", $birth_day));
                $builder->update("national_id", str_replace("'", "''", $national_id));
                $builder->update("department_id", str_replace("'", "''", $department_id));
                $builder->update("city_id", str_replace("'", "''", $city_id));
                $builder->update("district_id", str_replace("'", "''", $district_id));
                $builder->update("ward_id", str_replace("'", "''", $ward_id));
                $builder->update("address", str_replace("'", "''", $address));
                $builder->update("manager_id", str_replace("'", "''", $manager_id));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result =  $this->appSession->getTier()->exec($this->msg);

                $logo_id = $this->update_image_profile($request, $user_id);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật organizations thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật organizations thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    public function get_organization_by_id_member($id, Request $request, Response $response)
    {
        $sql = "SELECT d1.id, d1.location_id, d1.first_name, d1.last_name, d1.title, d1.phone, d1.email, d1.create_date";
        $sql = $sql . ", d8.user_name, d9.id AS manager_id, d12.name AS group_name";
        $sql = $sql . ", d1.national_id, d1.city_id, d1.district_id, d1.ward_id";
        $sql = $sql . ", d3.name AS location_name, d4.name AS national_name, d5.name AS city_name, d6.name AS district_name, d7.name AS ward_name";
        $sql = $sql . " FROM organizations d1";
        $sql = $sql . " LEFT OUTER JOIN res_address d3 ON(d1.location_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_country d4 ON(d1.national_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.city_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.district_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d7 ON(d1.ward_id = d7.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d8 ON(d1.user_id = d8.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d9 ON(d1.manager_id = d9.id)";
        $sql = $sql . " LEFT OUTER JOIN document d10 ON(d8.id = d10.rel_id AND d10.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d11 ON(d1.user_id = d11.user_id AND d11.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d12 ON(d11.group_id = d12.id AND d12.status=0)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();
        if ($numrows > 0) {

            $row = $result->getRow(0);
            $arr = array();
            $arr_managers = array();
            $arr_location = array();
            $arr_national = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();

            $arr['id'] = $row->getString("id");
            $arr['location_id'] = $row->getString("location_id");
            $arr['first_name'] = $row->getString("first_name");
            $arr['last_name'] = $row->getString("last_name");
            $arr['title'] = $row->getString("title");
            $arr['phone'] = $row->getString("phone");
            $arr['email'] = $row->getString("email");
            $arr['user_name'] = $row->getString("user_name");
            $arr['create_date'] = $row->getString("create_date");
            $arr['manager_id'] = $row->getString("manager_id");
            $arr['group_name'] = $row->getString("group_name");

            $arr_location['id'] = $row->getString("location_id");
            $arr_location['name'] = $row->getString("location_name");
            $arr['location'] = $arr_location;

            $arr_national['id'] = $row->getString("national_id");
            $arr_national['name'] = $row->getString("national_name");
            $arr['national'] = $arr_national;

            $arr_city['id'] = $row->getString("city_id");
            $arr_city['name'] = $row->getString("city_name");
            $arr['city'] = $arr_city;

            $arr_district['id'] = $row->getString("district_id");
            $arr_district['name'] = $row->getString("district_name");
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $row->getString("ward_id");
            $arr_ward['name'] = $row->getString("ward_name");
            $arr['ward'] = $arr_ward;

            $sql = "SELECT d1.id, d1.name, d2.id AS logo_id FROM res_user d1";
            $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='logo')";
            $sql = $sql . " WHERE d1.id='" . $arr['manager_id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_managers = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_managers); $j++) {
                $arr_manager = array();
                $arr_manager['id'] = $result_managers[$j][0];
                $arr_manager['name'] = $result_managers[$j][1];
                $arr_manager['logo_id'] = $result_managers[$j][2];
                $arr_managers[] = $arr_manager;
            }

            $arr['report_to'] = $arr_managers;

            $message = [
                'status' => true,
                'data' => ['organizations' => $arr],
                'message' => "Lấy danh sách organization by id thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    public function delete_organization_member($id, Request $request, Response $response)
    {
        $sql = "SELECT d1.id FROM organizations d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Organizations không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("organizations");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa organizations thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    public function create_image_profile(Request $request, $rel_id)
    {
        $file = $request->files->get('logo');
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
    public function update_image_profile(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='avatar'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $file_id = $this->create_image_profile($request, $rel_id);
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
    // GET MANAGER
    public function get_organization_manager(Request $request, Response $response)
    {
        $param = $request->query->all();
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name, d3.name AS group_name";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d2 ON(d1.id = d2.user_id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d3 ON(d2.group_id = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $soft = "{$soft_column}" . " " . "{$soft_by}";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1." . $soft);

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
            $arr['group_name'] = $result[$i][2];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['organizations_manager' => $data],
            'message' => "Lấy danh sách organizations manager thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }
    // CHANGE PASSWORD
    public function update_organization_password_member($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $password = $data['password'];
        $old_password = $data['old_password'];

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
            'old_password'  => 'required|max:256',
            'password'  => 'required|max:256',
            'confirm_password' => 'required|same:password',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM organizations d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Organizations không tồn tại."
                ];
            } else {
                $sql = "SELECT d1.user_id FROM organizations d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
                $this->msg->add("query", $sql);
                $user_id = $this->appSession->getTier()->getValue($this->msg);

                $sql = "SELECT d1.id, d1.phone, d1.email, d1.user_name, d1.password FROM res_user d1 WHERE (id='" . str_replace("'", "''", $user_id) . "'";
                $sql = $sql . ")";
                $this->msg->add("query", $sql);

                $result = $this->appSession->getTier()->getTable($this->msg);
                $numrows = $result->getRowCount();

                if ($numrows > 0) {
                    $row = $result->getRow(0);

                    $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $old_password);
                    $len = $this->appSession->getTool()->lenght($old_password);
                    for ($i = 0; $i < $len; $i++) {
                        $s = $s . chr($i + 48);
                    }
                    $old_password = $this->appSession->getTool()->toHash("md5", $s);

                    if ($old_password == $row->getString("password")) {

                        $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
                        $len = $this->appSession->getTool()->lenght($password);
                        for ($i = 0; $i < $len; $i++) {
                            $s = $s . chr($i + 48);
                        }
                        $password = $this->appSession->getTool()->toHash("md5", $s);

                        $builder = $this->appSession->getTier()->createBuilder("res_user");
                        $builder->update("id", $user_id);
                        $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                        $builder->update("password", str_replace("'", "''", $password));

                        $sql = $this->appSession->getTier()->getUpdate($builder);
                        $this->msg->add("query", $sql);
                        $result = $this->appSession->getTier()->exec($this->msg);

                        if ($result == '1') {
                            $message = [
                                'status' => true,
                                'message' => "Cập nhật password organizations thành công."
                            ];
                        } else {
                            $message = [
                                'status' => false,
                                'message' => "Cập nhật password organizations thất bại."
                            ];
                        }
                    } else {
                        $message = [
                            'status' => false,
                            'message' => "Mật khẩu cũ không đúng. Vui lòng nhập lại"
                        ];
                    }
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // GET LIST ORGANIZATION
    public function get_organization_org_chart(Request $request, Response $response)
    {
        $sql = "SELECT d1.id, d1.manager_id, d2.name, d4.id AS logo_id, d6.name AS group_name, d1.user_id";
        $sql = $sql . " FROM organizations d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id AND d2.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.manager_id = d3.id AND d2.status=0)";
        $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d5 ON(d1.user_id = d5.user_id AND d5.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d6 ON(d5.group_id = d6.id AND d6.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
        $this->msg->add("query", $sql);

        $managers = $this->appSession->getTier()->getArray($this->msg);

        $data = $this->printDepartment($managers, 'ROOT', "");

        $message = [
            'status' => true,
            'data' => ['organization_org_chart' => $data],
            'message' => "Lấy danh sách organizations org chart thành công."
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
                $arr['manager_id'] = $managers[$j][1];
                $arr['name'] = $managers[$j][2];
                $arr['logo_id'] = $managers[$j][3];
                $arr['group_name'] = $managers[$j][4];
                $arr['user_id'] = $managers[$j][5];
                $arr['children'] = $this->printDepartment($managers, $arr['user_id'], $func);
                $data[] = $arr;
            }
        }
        return $data;
    }
}
