<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;
use Rakit\Validation\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = $request->get('user_name');
//        $user = $data['user_name'];
//        $pass = $data['password'];

        $sql = "SELECT d1.id, d1.password, d1.user_name, d1.name, d1.company_id, d1.date_format, d1.thousands_sep, d1.time_format, d1.decimal_point, d1.avatar, d1.lang_id, d2.group_id AS user_group_id, d4.name AS user_group_name, d5.parent_id AS parent_company_id";
        $sql = $sql . " FROM res_user d1";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d2 ON(d1.id = d2.user_id AND d2.status =0)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d4 ON(d2.group_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d5 ON(d2.company_id = d5.id)";
        $sql = $sql . " WHERE (d1.user_name='" . $user . "' OR d1.email='" . $user . "' OR d1.phone='" . $user . "') AND d1.status =0 AND d2.status=0";


        $result = [];
        if (DB::statement($sql)) {
            $result = DB::select($sql);
        }
        $numrows = count($result);

        $data = array();
        if ($numrows > 0) {
            $row = $result[0];
            $data = (array)$row;
        }
        $this->data = $data;
        return $this->getResponse();
    }

    public function getSignedJWTForUser($user_id, $company_id)
    {
        $issued_at_time = time();
        $time_to_live = TIME_TO_LIVE;
        $token_expiration = $issued_at_time + $time_to_live;
        $payload = [
            'user_id' => $user_id,
            'company_id' => $company_id,
            'iss' => URL,
            'aud' => URL,
            'iat' => 1356999524,
            'exp' => time() + (10 * 365 * 24 * 60 * 60),
        ];

        return JWT::encode($payload, SECRET_KEY, 'HS256');
    }


    public function register(Request $request)
    {
        $message = [];

        $data = $request->request->all();

        $type = $data['type'];

        $accountTypes = [
            'freelancer' => 'freelancer',
            'headhunting_agency' => 'headhunting_agency',
            'hr_internal_team' => 'hr_internal_team'
        ];


        if (!isset($accountTypes[$type])) {
            return $message = [
                'status' => false,
                'message' => "Loại tài khoản của bạn không tồn tại."
            ];
        } else {


            $commercial_name = $data['commercial_name'];
            $name = $data['name'];
            $phone = $data['phone'];
            $email = $data['email'];
            $user_name = $data['user_name'];
            $password = $data['password'];

            if ($accountTypes[$type] == 'freelancer') {
                $message = $this->create_freelancer($this->appSession, $commercial_name, $name, $phone, $email, $user_name, $password, '23');
            } elseif ($accountTypes[$type] == 'headhunting_agency') {
                $message = $this->create_headhunting_agency($this->appSession, $commercial_name, $name, $phone, $email, $user_name, $password, '23');
            } else {
                $message = $this->create_hr_internal_team($this->appSession, $commercial_name, $name, $phone, $email, $user_name, $password, '23');
            }
        }

        return $this->dataTier->response($message, $response);
    }

    public function create_headhunting_agency($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
    {
        $msg = $appSession->getTier()->createMessage();

        $sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
        if ($email != "") {
            $sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
        }
        if ($phone != "") {
            $sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
        }
        $sql = $sql . ")";
        $msg->add("query", $sql);

        $result = $appSession->getTier()->getTable($msg);
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
            $user_id = $appSession->getTool()->getId();
            $s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
            $len = $appSession->getTool()->lenght($password);
            for ($i = 0; $i < $len; $i++) {
                $s = $s . chr($i + 48);
            }
            $password = $appSession->getTool()->toHash("md5", $s);
            $company_id = $appSession->getTool()->getId();

            $sql = "INSERT INTO res_user(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", user_name";
            $sql = $sql . ", password";
            $sql = $sql . ", email";
            $sql = $sql . ", phone";
            $sql = $sql . ", thousands_sep";
            $sql = $sql . ", decimal_point";
            $sql = $sql . ", date_format";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . ", lang_id";
            $sql = $sql . ", actived";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", ','";
            $sql = $sql . ", '.'";
            $sql = $sql . ", 'YYYY-MM-DD'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $lang_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ")";


            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $sql = "INSERT INTO res_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", parent_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", commercial_name";
            $sql = $sql . ", phone";
            $sql = $sql . ", email";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "	'" . $company_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 'ROOT'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";

            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $sql = "INSERT INTO res_user_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", user_id";
            $sql = $sql . ", group_id";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", 'HEADHUNTAGENCY'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";
            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            if ($result == '1') {
                $arr_module_id = [
                    ["module_id" => "5c74c32c-a1d1-4272-9cfe-0bb4cae5b949", "module_name" => "Dashboard"],
                    ["module_id" => "cc3e072c-8490-49e7-a6f5-926da175031c", "module_name" => "Candidate View"],
                    ["module_id" => "2bf52543-f096-4e1e-f173-6698e2ca1c1e", "module_name" => "Job View"],
                    ["module_id" => "9aae23ae-287e-4221-d34a-2cab3a39a82d", "module_name" => "Company View"],
                    ["module_id" => "aeb2cf62-a9e6-408b-850d-1c03b0147512", "module_name" => "Vendor View"],
                    ["module_id" => "9eaf7919-8a5a-41f4-e8b2-11b155e5b2d7", "module_name" => "Organization View"],
                    ["module_id" => "a790cd18-9846-459f-e198-3af7c8cf8a2e", "module_name" => "Career Site"],
                    ["module_id" => "d6daeadc-f7b7-408d-e2f5-7e3b9417582a", "module_name" => "Report View"],
                    ["module_id" => "72fc461c-8003-4204-d08b-42cd18770212", "module_name" => "Subscription"],
                    ["module_id" => "b9889d98-dd27-4676-99ef-a819d5f36fd7", "module_name" => "Calendar"],
                    ["module_id" => "7ae2e3dd-0606-4bb0-e543-d6b2bd21d199", "module_name" => "Settings"],
                ];

                for ($j = 0; $j < count($arr_module_id); $j++) {
                    $module_id = $this->appSession->getTool()->getId();

                    $builder = $this->dataTier->createBuilder("ir_module_rel");
                    $builder->add("id", $module_id);
                    $builder->add("create_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("write_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("create_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("write_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("module_id", str_replace("'", "''", $arr_module_id[$j]['module_id']));
                    $builder->add("rel_id", str_replace("'", "''", $company_id));

                    $sql = $this->dataTier->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $this->dataTier->exec($this->msg);
                }
                $user_group = $this->create_user_group($company_id, $user_id, $user_id);
                $user_company = $this->create_company_vendor_company($company_id, $user_id, $user_id);
                $org_chart = $this->create_org_chart($company_id, $user_id, $user_id, $user_id, $email, $phone);
                $message = [
                    'status' => true,
                    'message' => "Đăng ký tài khoản thành công."
                ];
            }
        }

        return $message;
    }

    public function create_hr_internal_team($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
    {
        $msg = $appSession->getTier()->createMessage();
        $message = [];

        $sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
        if ($email != "") {
            $sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
        }
        if ($phone != "") {
            $sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
        }
        $sql = $sql . ")";
        $msg->add("query", $sql);

        $result = $appSession->getTier()->getTable($msg);
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
            $user_id = $appSession->getTool()->getId();
            $s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
            $len = $appSession->getTool()->lenght($password);
            for ($i = 0; $i < $len; $i++) {
                $s = $s . chr($i + 48);
            }
            $password = $appSession->getTool()->toHash("md5", $s);
            $company_id = $appSession->getTool()->getId();

            $sql = "INSERT INTO res_user(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", user_name";
            $sql = $sql . ", password";
            $sql = $sql . ", email";
            $sql = $sql . ", phone";
            $sql = $sql . ", thousands_sep";
            $sql = $sql . ", decimal_point";
            $sql = $sql . ", date_format";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . ", lang_id";
            $sql = $sql . ", actived";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", ','";
            $sql = $sql . ", '.'";
            $sql = $sql . ", 'YYYY-MM-DD'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $lang_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ")";
            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $sql = "INSERT INTO res_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", parent_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", commercial_name";
            $sql = $sql . ", phone";
            $sql = $sql . ", email";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "	'" . $company_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 'ROOT'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";

            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $id = $appSession->getTool()->getId();
            $sql = "INSERT INTO res_user_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", user_id";
            $sql = $sql . ", group_id";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", 'HRINTERNALTEAM'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";
            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            if ($result == '1') {
                $arr_module_id = [
                    ["module_id" => "5c74c32c-a1d1-4272-9cfe-0bb4cae5b949", "module_name" => "Dashboard"],
                    ["module_id" => "cc3e072c-8490-49e7-a6f5-926da175031c", "module_name" => "Candidate View"],
                    ["module_id" => "2bf52543-f096-4e1e-f173-6698e2ca1c1e", "module_name" => "Job View"],
                    ["module_id" => "9aae23ae-287e-4221-d34a-2cab3a39a82d", "module_name" => "Company View"],
                    ["module_id" => "aeb2cf62-a9e6-408b-850d-1c03b0147512", "module_name" => "Vendor View"],
                    ["module_id" => "9eaf7919-8a5a-41f4-e8b2-11b155e5b2d7", "module_name" => "Organization View"],
                    ["module_id" => "a790cd18-9846-459f-e198-3af7c8cf8a2e", "module_name" => "Career Site"],
                    ["module_id" => "d6daeadc-f7b7-408d-e2f5-7e3b9417582a", "module_name" => "Report View"],
                    ["module_id" => "72fc461c-8003-4204-d08b-42cd18770212", "module_name" => "Subscription"],
                    ["module_id" => "b9889d98-dd27-4676-99ef-a819d5f36fd7", "module_name" => "Calendar"],
                    ["module_id" => "7ae2e3dd-0606-4bb0-e543-d6b2bd21d199", "module_name" => "Settings"],
                ];

                for ($j = 0; $j < count($arr_module_id); $j++) {

                    $module_id = $this->appSession->getTool()->getId();

                    $builder = $this->dataTier->createBuilder("ir_module_rel");
                    $builder->add("id", $module_id);
                    $builder->add("create_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("write_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("create_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("write_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("module_id", str_replace("'", "''", $arr_module_id[$j]['module_id']));
                    $builder->add("rel_id", str_replace("'", "''", $company_id));

                    $sql = $this->dataTier->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $this->dataTier->exec($this->msg);
                }
                $user_group = $this->create_user_group($company_id, $user_id, $user_id);
                $user_company = $this->create_company_vendor_company($company_id, $user_id, $user_id);
                $org_chart = $this->create_org_chart($company_id, $user_id, $user_id, $user_id, $email, $phone);
                $message = [
                    'status' => true,
                    'message' => "Đăng ký tài khoản thành công."
                ];
            }
        }

        return $message;
    }

    public function create_freelancer($appSession, $commercial_name, $name, $phone, $email, $user_name, $password, $lang_id)
    {
        $msg = $appSession->getTier()->createMessage();
        $message = [];

        $sql = "SELECT id, phone, email, user_name FROM res_user WHERE (user_name='" . str_replace("'", "''", $user_name) . "'";
        if ($email != "") {
            $sql = $sql . " OR email = '" . str_replace("'", "''", $email) . "'";
        }
        if ($phone != "") {
            $sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
        }
        $sql = $sql . ")";
        $msg->add("query", $sql);

        $result = $appSession->getTier()->getTable($msg);
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
            $user_id = $appSession->getTool()->getId();
            $s = $appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
            $len = $appSession->getTool()->lenght($password);
            for ($i = 0; $i < $len; $i++) {
                $s = $s . chr($i + 48);
            }
            $password = $appSession->getTool()->toHash("md5", $s);
            $company_id = $appSession->getTool()->getId();

            $sql = "INSERT INTO res_user(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", user_name";
            $sql = $sql . ", password";
            $sql = $sql . ", email";
            $sql = $sql . ", phone";
            $sql = $sql . ", thousands_sep";
            $sql = $sql . ", decimal_point";
            $sql = $sql . ", date_format";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . ", lang_id";
            $sql = $sql . ", actived";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $user_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $password) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", ','";
            $sql = $sql . ", '.'";
            $sql = $sql . ", 'YYYY-MM-DD'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $lang_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ")";
            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $sql = "INSERT INTO res_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", parent_id";
            $sql = $sql . ", status";
            $sql = $sql . ", name";
            $sql = $sql . ", commercial_name";
            $sql = $sql . ", phone";
            $sql = $sql . ", email";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "	'" . $company_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 'ROOT'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . str_replace("'", "''", $name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $commercial_name) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $phone) . "'";
            $sql = $sql . ", '" . str_replace("'", "''", $email) . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";

            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);

            $id = $appSession->getTool()->getId();
            $sql = "INSERT INTO res_user_company(";
            $sql = $sql . "id";
            $sql = $sql . ", company_id";
            $sql = $sql . ", status";
            $sql = $sql . ", user_id";
            $sql = $sql . ", group_id";
            $sql = $sql . ", create_date";
            $sql = $sql . ", write_date";
            $sql = $sql . ", create_uid";
            $sql = $sql . ", write_uid";
            $sql = $sql . " )VALUES(";
            $sql = $sql . "'" . $user_id . "'";
            $sql = $sql . ", '" . $company_id . "'";
            $sql = $sql . ", 0";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", 'FREELANCER'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $appSession->getTier()->getDateString() . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ", '" . $user_id . "'";
            $sql = $sql . ")";
            $msg->add("query", $sql);
            $result = $appSession->getTier()->exec($msg);
            if ($result == '1') {
                $arr_module_id = [
                    ["module_id" => "5c74c32c-a1d1-4272-9cfe-0bb4cae5b949", "module_name" => "Dashboard"],
                    ["module_id" => "cc3e072c-8490-49e7-a6f5-926da175031c", "module_name" => "Candidate View"],
                    ["module_id" => "2bf52543-f096-4e1e-f173-6698e2ca1c1e", "module_name" => "Job View"],
                    ["module_id" => "9aae23ae-287e-4221-d34a-2cab3a39a82d", "module_name" => "Company View"],
                    ["module_id" => "aeb2cf62-a9e6-408b-850d-1c03b0147512", "module_name" => "Vendor View"],
                    ["module_id" => "9eaf7919-8a5a-41f4-e8b2-11b155e5b2d7", "module_name" => "Organization View"],
                    ["module_id" => "a790cd18-9846-459f-e198-3af7c8cf8a2e", "module_name" => "Career Site"],
                    ["module_id" => "d6daeadc-f7b7-408d-e2f5-7e3b9417582a", "module_name" => "Report View"],
                    ["module_id" => "72fc461c-8003-4204-d08b-42cd18770212", "module_name" => "Subscription"],
                    ["module_id" => "b9889d98-dd27-4676-99ef-a819d5f36fd7", "module_name" => "Calendar"],
                    ["module_id" => "7ae2e3dd-0606-4bb0-e543-d6b2bd21d199", "module_name" => "Settings"],
                ];

                for ($j = 0; $j < count($arr_module_id); $j++) {

                    $module_id = $this->appSession->getTool()->getId();

                    $builder = $this->dataTier->createBuilder("ir_module_rel");
                    $builder->add("id", $module_id);
                    $builder->add("create_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("write_uid", '1605bf23-e87c-4c3f-fd59-a7867e428653');
                    $builder->add("create_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("write_date", $this->dataTier->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("module_id", str_replace("'", "''", $arr_module_id[$j]['module_id']));
                    $builder->add("rel_id", str_replace("'", "''", $company_id));

                    $sql = $this->dataTier->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $this->dataTier->exec($this->msg);
                }

                $user_group = $this->create_user_group($company_id, $user_id, $user_id);
                $user_company = $this->create_company_vendor_company($company_id, $user_id, $user_id);
                $org_chart = $this->create_org_chart($company_id, $user_id, $user_id, $user_id, $email, $phone);
                $message = [
                    'status' => true,
                    'message' => "Đăng ký tài khoản thành công."
                ];
            }
        }

        return $message;
    }

    public function active($appSession, $user_id)
    {
        $msg = $appSession->getTier()->createMessage();
        $sql = "UPDATE res_user SET status =0 WHERE id='" . $user_id . "'";
        $msg->add("query", $sql);
        $result = $appSession->getTier()->exec($msg);
        return true;
    }

    public function forgot_password(Request $request, Response $response)
    {
        $data = $request->request->all();

        $email = $data['email'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'email' => ':attribute không phải là email hợp lệ.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'email' => 'required|email',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT id, email, phone FROM res_user WHERE status =0";
            if ($email != "") {
                $sql = $sql . " AND email='" . str_replace("'", "''", $email) . "'";
            }

            $this->msg->add("query", $sql);

            $values = $this->dataTier->getArray($this->msg);
            if (count($values) > 0) {
                $user_id = $values[0][0];
                $code = rand(1000, 9999);
                $sql = "INSERT INTO res_user_verification(";
                $sql = $sql . "id";
                $sql = $sql . ", create_date";
                $sql = $sql . ", write_date";
                $sql = $sql . ", status";
                $sql = $sql . ", create_uid";
                $sql = $sql . ", code";
                $sql = $sql . " )VALUES(";
                $sql = $sql . "'" . $this->appSession->getTool()->getId() . "'";
                $sql = $sql . ", '" . $this->dataTier->getDateString() . "'";
                $sql = $sql . ", '" . $this->dataTier->getDateString() . "'";
                $sql = $sql . ", 0";
                $sql = $sql . ", '" . $user_id . "'";
                $sql = $sql . ", '" . str_replace("'", "''", $code) . "'";
                $sql = $sql . ")";
                $this->msg->add("query", $sql);
                $result = $this->dataTier->exec($this->msg);

                $r = $this->appSession->getTool()->send_mail(SUPPORT_EMAIL, "INHIRING", $email, "Quên mật khẩu", "Quên mật khẩu", "Mã OTP của bạn là: " . $code);

                $message = [
                    'status' => true,
                    'data' => ['user_id' => $user_id],
                    'message' => "Gửi mail kích hoạt thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Gửi mail kích hoạt thất bại."
                ];
            }
        }

        return $this->dataTier->response($message, $response);
    }

    public function verify_user_code(Request $request, Response $response)
    {
        $data = $request->request->all();

        $user_id = $data['user_id'];
        $code = $data['code'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'email' => ':attribute không phải là email hợp lệ.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'user_id' => 'required',
            'code' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT id FROM res_user_verification WHERE (create_uid='" . $user_id . "' AND code='" . str_replace("'", "''", $code) . "') AND status =0";
            $this->msg->add("query", $sql);
            $values = $this->dataTier->getArray($this->msg);
            if (count($values) > 0) {
                $sql = "UPDATE res_user_verification SET status =1, write_date=" . $this->dataTier->getDateString() . " WHERE id='" . $values[0][0] . "'";
                $this->msg->add("query", $sql);
                $result = $this->dataTier->exec($this->msg);
                $message = [
                    'status' => true,
                    'message' => "Kích hoạt thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Kích hoạt không thành công."
                ];
            }
        }
        return $this->dataTier->response($message, $response);
    }

    public function change_password(Request $request, Response $response)
    {

        $data = $request->request->all();

        $user_id = $data['user_id'];
        $new_password = $data['new_password'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'user_id' => 'required',
            'new_password' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $new_password);
            $len = $this->appSession->getTool()->lenght($new_password);
            for ($i = 0; $i < $len; $i++) {
                $s = $s . chr($i + 48);
            }
            $new_password = $this->appSession->getTool()->toHash("md5", $s);

            $sql = "SELECT id FROM res_user WHERE id='" . $user_id . "'";
            $this->msg->add("query", $sql);
            $values = $this->dataTier->getArray($this->msg);
            if (count($values) > 0) {
                $sql = "UPDATE res_user SET write_date=" . $this->dataTier->getDateString();
                $sql = $sql . ", password='" . $new_password . "'";
                $sql = $sql . " WHERE id='" . $user_id . "'";
                $this->msg->add("query", $sql);
                $r = $this->dataTier->exec($this->msg);

                $message = [
                    'status' => true,
                    'data' => ['user_id' => $user_id],
                    'message' => "Cập nhật mật khẩu thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "User id không tồn tại."
                ];
            }
        }
        return $this->dataTier->response($message, $response);
    }

    public function create_user_group($company_id, $create_uid, $write_uid)
    {
        $arr_group_id = [
            ["group_name" => "Vendor", "type" => "vendor"],
            ["group_name" => "Company", "type" => "company"],
        ];
        for ($j = 0; $j < count($arr_group_id); $j++) {

            $user_group_id = $this->appSession->getTool()->getId();

            $builder = $this->dataTier->createBuilder("res_user_group");
            $builder->add("id", $user_group_id);
            $builder->add("create_uid", $create_uid);
            $builder->add("write_uid", $write_uid);
            $builder->add("create_date", $this->dataTier->getDateString(), 'f');
            $builder->add("write_date", $this->dataTier->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $company_id);
            $builder->add("name", str_replace("'", "''", $arr_group_id[$j]['group_name']));
            $builder->add("type", str_replace("'", "''", $arr_group_id[$j]['type']));

            $sql = $this->dataTier->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->dataTier->exec($this->msg);
        }
    }

    public function create_company_vendor_company($company_id, $create_uid, $write_uid)
    {
        $arr_department = [
            ["name" => "Vendor", "type" => "vendor"],
            ["name" => "Company", "type" => "company"],
        ];
        for ($j = 0; $j < count($arr_department); $j++) {

            $user_company_id = $this->appSession->getTool()->getId();
            $builder = $this->dataTier->createBuilder("res_company");
            $builder->add("id", $user_company_id);
            $builder->add("company_id", $user_company_id);
            $builder->add("create_uid", $create_uid);
            $builder->add("write_uid", $write_uid);
            $builder->add("create_date", $this->dataTier->getDateString(), 'f');
            $builder->add("write_date", $this->dataTier->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("parent_id", $company_id);
            $builder->add("name", str_replace("'", "''", $arr_department[$j]['name']));
            $builder->add("commercial_name", str_replace("'", "''", $arr_department[$j]['name']));
            $builder->add("type", str_replace("'", "''", $arr_department[$j]['type']));

            $sql = $this->dataTier->getInsert($builder);
            $this->msg->add("query", $sql);
            $result = $this->dataTier->exec($this->msg);
        }
    }

    public function create_org_chart($company_id, $create_uid, $write_uid, $user_id, $email, $phone)
    {
        $builder = $this->dataTier->createBuilder("organizations");
        $id = $this->appSession->getTool()->getId();
        $builder->add("id", $id);
        $builder->add("create_uid", $create_uid);
        $builder->add("write_uid", $write_uid);
        $builder->add("create_date", $this->dataTier->getDateString(), 'f');
        $builder->add("write_date", $this->dataTier->getDateString(), 'f');
        $builder->add("status", 0);
        $builder->add("company_id", $company_id);
        $builder->add("manager_id", "ROOT");
        $builder->add("email", str_replace("'", "''", $email));
        $builder->add("phone", str_replace("'", "''", $phone));
        $builder->add("user_id", str_replace("'", "''", $user_id));
        $sql = $this->dataTier->getInsert($builder);
        $this->msg->add("query", $sql);
        $result = $this->dataTier->exec($this->msg);
    }
}
