<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

require_once(ABSPATH . 'app/services/WebService.php');
require_once(ABSPATH . 'app/services/ServiceJob.php');

class JobController extends Controller
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

    // START JOB
    public function get_job(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_companies = $param['in_companies'];
        $location_id = $param['location_id'];
        $handling_by = $param['handling_by'];
        $in_job_types = $param['in_job_types'];
        $start_salary = $param['start_salary'];
        $end_salary = $param['end_salary'];
        $in_job_categories = $param['in_job_categories'];
        $followed = $param['followed'] ?? "0";
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.title, d1.companies_id, d1.department_id, d1.job_type_id, d1.recruitment_period, d1.experience_in_years";
        $sql = $sql . ", d1.salary, d1.expect_location_id, d1.job_categories_id";
        $sql = $sql . ", d2.company_name AS company_name, d3.name AS department_name, d4.name AS job_type_name";
        $sql = $sql . ", d5.name AS location_name, d6.name AS job_category_name, d7.id AS logo_id, d1.description, d8.followed_id";
        $sql = $sql . ", d1.deadline, d11.total_candidate, d12.total_commission";
        $sql = $sql . " FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN departments d3 ON(d1.department_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN job_types d4 ON(d1.job_type_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.expect_location_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN job_categories d6 ON(d1.job_categories_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN job_followed_jobs d8 ON(d1.id = d8.job_id AND d8.status=0)";
        $sql = $sql . " LEFT OUTER JOIN (SELECT d9.job_id, COUNT(d10.candidate_id) AS total_candidate FROM job_stages d9 LEFT OUTER JOIN job_candidates d10 ON(d9.id = d10.job_stage_id) GROUP BY d9.job_id) d11 ON(d1.id = d11.job_id)";
        $sql = $sql . " LEFT OUTER JOIN (SELECT rel_id, SUM(value) AS total_commission FROM job_commissions GROUP BY rel_id) d12 ON(d1.id = d12.rel_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($followed == "1") {
            $followed_id = $this->find_job_followed_id();
            $sql = $sql . " AND d8.status=0 AND d8.followed_id='" . $followed_id . "'";
        }

        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.title", "d2.company_name"], $search) . ")";
        }

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($location_id != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $location_id) . ")";
        }

        if ($in_companies != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.companies_id", $in_companies) . ")";
        }

        if ($handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $handling_by) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($start_salary != "" || $end_salary != "") {
            $sql = $sql . " AND d1.salary>='" . $start_salary . "' AND d1.salary<='" . $end_salary . "'";
        }

        if ($in_job_categories != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_categories_id", $in_job_categories) . ")";
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
            $arr_companies = array();
            $arr_departments = array();
            $arr_job_types = array();
            $arr_location = array();
            $arr_job_categories = array();
            $arr_skills = array();
            $arr_user_followed = array();
            $arr_progress_stage = array();

            $arr['id'] = $result[$i][0];
            $arr['title'] = $result[$i][1];
            $arr['companies_id'] = $result[$i][2];
            $arr['department_id'] = $result[$i][3];
            $arr['job_type_id'] = $result[$i][4];
            $arr['recruitment_period'] = $result[$i][5];
            $arr['experience_in_years'] = $result[$i][6];
            $arr['salary'] = $result[$i][7];
            $arr['expect_location_id'] = $result[$i][8];
            $arr['job_categories_id'] = $result[$i][9];
            $arr['description'] = $result[$i][16];
            $arr['followed_id'] = $result[$i][17];
            $arr['deadline'] = $result[$i][18];
            $arr['total_candidate'] = intval($result[$i][19]);
            $arr['total_commission'] = intval($result[$i][20]);

            $arr_companies['id'] = $result[$i][2];
            $arr_companies['name'] = $result[$i][10];
            $arr['company'] = $arr_companies;

            $arr_departments['id'] = $result[$i][3];
            $arr_departments['name'] = $result[$i][11];
            $arr['department'] = $arr_departments;

            $arr_job_types['id'] = $result[$i][4];
            $arr_job_types['name'] = $result[$i][12];
            $arr['job_type'] = $arr_job_types;

            $arr_location['id'] = $result[$i][8];
            $arr_location['name'] = $result[$i][13];
            $arr['location'] = $arr_location;

            $arr_job_categories['id'] = $result[$i][9];
            $arr_job_categories['name'] = $result[$i][14];
            $arr['job_category'] = $arr_job_categories;

            $arr['logo_id'] = $result[$i][15];

            $sql = "SELECT d1.id, d3.name, d1.description FROM job_skills d1";
            $sql = $sql . " LEFT OUTER JOIN jobs d2 ON(d1.job_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN skills d3 ON(d1.skill_id = d3.id)";
            $sql = $sql . " WHERE d1.job_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_skills = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_skills); $j++) {
                $arr_skill = array();
                $arr_skill['id'] = $result_skills[$j][0];
                $arr_skill['name'] = $result_skills[$j][1];
                $arr_skill['description'] = $result_skills[$j][2];
                $arr_skills[] = $arr_skill;
            }

            $arr['skills'] = $arr_skills;

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM job_followed_jobs d1";
            $sql = $sql . " LEFT OUTER JOIN job_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.job_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_followed = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_followed); $j++) {
                $arr_followed = array();
                $arr_followed['id'] = $result_followed[$j][0];
                $arr_followed['user_id'] = $result_followed[$j][3];
                $arr_followed['user_name'] = $result_followed[$j][1];
                $arr_followed['avatar_id'] = $result_followed[$j][2];
                $arr_user_followed[] = $arr_followed;
            }

            $arr['user_followed'] = $arr_user_followed;

            $sql = "SELECT d1.id, d1.name, d2.total_candidate FROM job_stages d1";
            $sql = $sql . " LEFT OUTER JOIN (SELECT job_stage_id, COUNT(id) AS total_candidate FROM job_candidates WHERE status=0 GROUP BY job_stage_id) d2 ON(d1.id = d2.job_stage_id)";
            $sql = $sql . " WHERE d1.job_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";

            $this->msg->add("query", $sql);
            $result_progress_stage = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_progress_stage); $j++) {
                $arr_progress = array();
                $arr_progress['id'] = $result_progress_stage[$j][0];
                $arr_progress['name'] = $result_progress_stage[$j][1];
                $arr_progress['total'] = intval($result_progress_stage[$j][2]);
                $arr_progress_stage[] = $arr_progress;
            }

            $arr['total_candidate_progress'] = $arr_progress_stage;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['jobs' => $data],
            'message' => "Lấy danh sách job thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job(Request $request, Response $response)
    {

        $data = $request->request->all();
        $title = $data['title'];
        $companies_id = $data['companies_id'];
        $department_id = $data['department_id'];
        $job_type_id = $data['job_type_id'];
        $recruitment_period = $data['recruitment_period'];
        $experience_in_years = $data['experience_in_years'];
        $salary = $data['salary'];
        $expect_location_id = $data['expect_location_id'];
        $job_categories_id = $data['job_categories_id'];
        $description = $data['description'];
        $deadline = $data['deadline'];


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
            'title' => 'required|max:256',
            'companies_id' => 'required',
            'department_id' => 'required',
            'job_type_id' => 'required',
            'recruitment_period' => 'required|date:Y-m-d',
            'experience_in_years' => 'required|integer',
            'salary' => 'integer',
            'expect_location_id' => 'required',
            'job_categories_id' => 'required',
            //'description' => 'max:1000',
            'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
            'deadline' => 'required|date:Y-m-d',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("jobs");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("title", str_replace("'", "''", $title));
            $builder->add("companies_id", str_replace("'", "''", $companies_id));
            $builder->add("department_id", str_replace("'", "''", $department_id));
            $builder->add("job_type_id", str_replace("'", "''", $job_type_id));
            $builder->add("recruitment_period", str_replace("'", "''", $recruitment_period));
            $builder->add("experience_in_years", str_replace("'", "''", $experience_in_years));
            $builder->add("salary", str_replace("'", "''", $salary));
            $builder->add("expect_location_id", str_replace("'", "''", $expect_location_id));
            $builder->add("job_categories_id", str_replace("'", "''", $job_categories_id));
            $builder->add("description", str_replace("'", "''", $description));
            $builder->add("deadline", str_replace("'", "''", $deadline));

            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result =  $this->appSession->getTier()->exec($this->msg);

            $logo_id = $this->create_document_job($request, $id);

            if ($result == '1') {

                $arr_defaul_stage = [
                    ["name" => "Applied", "parent_stage_id" => "", "description" => "Storing all candidate just applied job", "sequence" => "1", "is_edit" => "1"],
                    ["name" => "Phone Screen", "parent_stage_id" => "", "description" => "Applied candidates matched with job requirements", "sequence" => "2", "is_edit" => "1"],
                    ["name" => "Interviewed", "parent_stage_id" => "", "description" => "Candidates completed interviewed & waiting for feedback", "sequence" => "3", "is_edit" => "1"],
                    ["name" => "Hired", "parent_stage_id" => "", "description" => "Candidates signed contract & are hired", "sequence" => "4", "is_edit" => "1"],
                    ["name" => "Rejected", "parent_stage_id" => "", "description" => "Cuase some reason, this candidates is failed on recruitment", "sequence" => "5", "is_edit" => "1"],
                ];

                for ($j = 0; $j < count($arr_defaul_stage); $j++) {

                    $id_stage = $this->appSession->getTool()->getId();

                    $builder = $this->appSession->getTier()->createBuilder("job_stages");
                    $builder->add("id", $id_stage);
                    $builder->add("create_uid", $this->session_user_id);
                    $builder->add("write_uid", $this->session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $this->session_company_id);
                    $builder->add("name", str_replace("'", "''", $arr_defaul_stage[$j]['name']));
                    $builder->add("parent_stage_id", str_replace("'", "''", $arr_defaul_stage[$j]['parent_stage_id']));
                    $builder->add("description", str_replace("'", "''", $arr_defaul_stage[$j]['description']));
                    $builder->add("sequence", str_replace("'", "''", $arr_defaul_stage[$j]['sequence']));
                    $builder->add("is_edit", str_replace("'", "''", $arr_defaul_stage[$j]['is_edit']));
                    $builder->add("job_id", str_replace("'", "''", $id));

                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $this->appSession->getTier()->exec($this->msg);
                }

                $data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $this->session_company_id . "&message=" . $this->appSession->getTool()->urlEncode("type=database.hq.updated&name=jobs");
                $ws = new \WebService($this->appSession);
                $ws->sendMessage($data);
                $status = new \ServiceJob($this->appSession);
                $status->doCreate($id, "jobs", "",  $this->session_user_id, $this->session_company_id);

                $data = $this->get_job_by_id($id, $response);
                $message = json_decode($data->getContent());
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo job thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $title = $data['title'];
        $companies_id = $data['companies_id'];
        $department_id = $data['department_id'];
        $job_type_id = $data['job_type_id'];
        $recruitment_period = $data['recruitment_period'];
        $experience_in_years = $data['experience_in_years'];
        $salary = $data['salary'];
        $expect_location_id = $data['expect_location_id'];
        $job_categories_id = $data['job_categories_id'];
        $description = $data['description'];
        $deadline = $data['deadline'];


        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg, png.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'title' => 'max:256',
            'recruitment_period' => 'date:Y-m-d',
            'experience_in_years' => 'numeric',
            'salary' => 'numeric',
            // 'expect_location_id' => 'required',
            // 'job_categories_id' => 'required',
            // 'description' => 'max:1000',
            'logo' => 'uploaded_file|max:1M|mimes:jpeg,png',
            'deadline' => 'date:Y-m-d',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("jobs");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("title", str_replace("'", "''", $title));
                $builder->update("companies_id", str_replace("'", "''", $companies_id));
                $builder->update("department_id", str_replace("'", "''", $department_id));
                $builder->update("job_type_id", str_replace("'", "''", $job_type_id));
                $builder->update("recruitment_period", str_replace("'", "''", $recruitment_period));
                $builder->update("experience_in_years", str_replace("'", "''", $experience_in_years));
                $builder->update("salary", str_replace("'", "''", $salary));
                $builder->update("expect_location_id", str_replace("'", "''", $expect_location_id));
                $builder->update("job_categories_id", str_replace("'", "''", $job_categories_id));
                $builder->update("description", str_replace("'", "''", $description));
                $builder->update("deadline", str_replace("'", "''", $deadline));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                $logo_id = $this->update_document_job($request, $id);
                if ($result == '1') {

                    $data = $this->get_job_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.title, d1.companies_id, d1.department_id, d1.job_type_id, d1.recruitment_period, d1.experience_in_years";
        $sql = $sql . ", d1.salary, d1.expect_location_id, d1.job_categories_id";
        $sql = $sql . ", d2.company_name AS company_name, d3.name AS department_name, d4.name AS job_type_name";
        $sql = $sql . ", d5.name AS location_name, d6.name AS job_category_name, d7.id AS logo_id, d1.description";
        $sql = $sql . ", d1.deadline, d8.id AS create_by_id, d8.name AS create_by_name, d1.create_date, d1.write_date";
        $sql = $sql . " FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN departments d3 ON(d1.department_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN job_types d4 ON(d1.job_type_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.expect_location_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN job_categories d6 ON(d1.job_categories_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN res_user d8 ON(d1.create_uid = d8.id)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();
            $arr_companies = array();
            $arr_departments = array();
            $arr_job_types = array();
            $arr_location = array();
            $arr_job_categories = array();
            $arr_skills = array();
            $arr_created_by = array();

            $arr['id'] = $row->getString("id");
            $arr['title'] = $row->getString("title");
            $arr['companies_id'] = $row->getString("companies_id");
            $arr['department_id'] = $row->getString("department_id");
            $arr['job_type_id'] = $row->getString("job_type_id");
            $arr['recruitment_period'] = $row->getString("recruitment_period");
            $arr['experience_in_years'] = $row->getString("experience_in_years");
            $arr['salary'] = $row->getString("salary");
            $arr['expect_location_id'] = $row->getString("expect_location_id");
            $arr['job_categories_id'] = $row->getString("job_categories_id");
            $arr['description'] = $row->getString("description");
            $arr['deadline'] = $row->getString("deadline");
            $arr['create_date'] = $row->getString("create_date");
            $arr['update_date'] = $row->getString("write_date");
            $arr['created_by_id']  = $row->getString("create_by_id");


            $arr_companies['id'] = $row->getString("companies_id");
            $arr_companies['name'] = $row->getString("company_name");
            $arr['company'] = $arr_companies;

            $arr_departments['id'] = $row->getString("department_id");
            $arr_departments['name'] = $row->getString("department_name");
            $arr['department'] = $arr_departments;

            $arr_job_types['id'] = $row->getString("job_type_id");
            $arr_job_types['name'] = $row->getString("job_type_name");
            $arr['job_type'] = $arr_job_types;

            $arr_location['id'] = $row->getString("expect_location_id");
            $arr_location['name'] = $row->getString("location_name");
            $arr['location'] = $arr_location;

            $arr_job_categories['id'] = $row->getString("job_categories_id");
            $arr_job_categories['name'] = $row->getString("job_category_name");
            $arr['job_category'] = $arr_job_categories;


            $sql = "SELECT d1.id, d1.name, d1.user_name, d2.id AS logo_id FROM res_user d1";
            $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.id='" . $arr['created_by_id']. "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";

            $this->msg->add("query", $sql);
            $result_create_by =  $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            if ($numrows > 0) {
                $row = $result_create_by->getRow(0);

                $arr_created_by['created_by_id'] = $row->getString("id");
                $arr_created_by['created_by_name'] = $row->getString("name");
                $arr_created_by['created_by_user_name'] = $row->getString("user_name");
                $arr_created_by['created_by_logo'] = $row->getString("logo_id");

                $arr['created_by'] = $arr_created_by;
            }

            $sql = "SELECT d1.id, d3.name, d1.description FROM job_skills d1";
            $sql = $sql . " LEFT OUTER JOIN jobs d2 ON(d1.job_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN skills d3 ON(d1.skill_id = d3.id)";
            $sql = $sql . " WHERE d1.job_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.create_date ASC";
            $this->msg->add("query", $sql);
            $result_skills = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_skills); $j++) {
                $arr_skill = array();
                $arr_skill['id'] = $result_skills[$j][0];
                $arr_skill['name'] = $result_skills[$j][1];
                $arr_skill['description'] = $result_skills[$j][2];
                $arr_skills[] = $arr_skill;
            }

            $arr['skills'] = $arr_skills;


            $arr['logo_id'] = $row->getString("logo_id");

            $message = [
                'status' => true,
                'data' => ['job' => $arr],
                'message' => "Lấy job by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job($id, Response $response)
    {
        $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Jobs không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("jobs");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_job(Request $request, $rel_id)
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
        $builder->add("document_type_rel", 'logo');
        $sql = $this->appSession->getTier()->getInsert($builder);
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        return $file_id;
    }

    public function update_document_job(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='logo'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $message = [
                'status' => true,
                'message' => "Logo không tồn tại."
            ];
        } else {

            $file = $request->files->get('logo');
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
                $builder->update("document_type_rel", 'logo');
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

                return $file_id;
            }
        }
    }
    // END JOB

    // START SETTINGS JOB GROUP
    public function get_job_group(Request $request, Response $response)
    {
        $data = $request->query->all();

        $id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.name, d1.job_id FROM job_group d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "' AND d1.job_id='" . $id . "'";
        $sql = $sql . " ORDER BY d1.write_date ASC";
        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $arr['job_id'] = $result[$i][2];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_groups' => $data],
            'message' => "Lấy danh sách job group thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_group(Request $request, Response $response)
    {

        $data = $request->request->all();
        $rel_id = $data['rel_id'];
        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'name' => 'required|max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("job_group");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("name", $name);
            $builder->add("job_id", $rel_id);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo job group thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo job group note thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_group($id, Request $request, Response $response)
    {
        $data = $request->request->all();
        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'name' => 'max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_group d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job group không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("job_group");
                $builder->update("id", $id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job group thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job group thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_group_by_id($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM job_group d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job group không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM job_group d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['job_types' => $data],
                'message' => "Lấy job group by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_group($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM job_group d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job group không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("job_group");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job group thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS JOB GROUP

    // START SETTINGS JOB HIRING TEAM
    public function get_job_hiring_team(Request $request, Response $response)
    {
        $data = $request->query->all();

        $id = $data['job_id'];

        $sql = "SELECT d1.id, d2.name AS job_role_name";
        $sql = $sql . ", d3.name AS user_name, d7.id AS avatar_id, d5.id AS department_id, d5.name AS department_name";
        $sql = $sql . ", d6.id AS user_group_id, d6.name AS group_name";
        $sql = $sql . " FROM job_hiring_teams d1";
        $sql = $sql . " LEFT OUTER JOIN job_group d2 ON(d1.job_group_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.user_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_company d4 ON(d3.id = d4.user_id)";
        $sql = $sql . " LEFT OUTER JOIN res_company d5 ON(d5.id = d4.company_id)";
        $sql = $sql . " LEFT OUTER JOIN res_user_group d6 ON(d6.id = d4.group_id) ";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d3.id = d7.rel_id AND d7.document_type_rel='avatar')";
        $sql = $sql . " WHERE d1.job_id='" . $id . "' AND d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['job_role_name'] = $result[$i][1];
            $arr['user_name'] = $result[$i][2];
            $arr['avatar_id'] = $result[$i][3];
            $arr['department_id'] = $result[$i][4];
            $arr['department_name'] = $result[$i][5];
            $arr['group_id'] = $result[$i][6];
            $arr['group_name'] = $result[$i][7];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_hiring_teams' => $data],
            'message' => "Lấy danh sách job hiring teams thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_hiring_team(Request $request, Response $response)
    {
        $data = $request->request->all();

        $job_id = $data['job_id'];
        $job_group_id = $data['job_group_id'];
        $user_id = $data['user_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'job_id' => 'required',
            'job_group_id' => 'required',
            'user_id' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $job_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("job_hiring_teams");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("job_id", str_replace("'", "''", $job_id));
                $builder->add("job_group_id", str_replace("'", "''", $job_group_id));
                $builder->add("user_id", str_replace("'", "''", $user_id));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job hiring team thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job hiring team thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_hiring_team($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $job_group_id = $data['job_group_id'];
        $user_id = $data['user_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'job_group_id' => 'required',
            'user_id' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_hiring_teams d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job hiring team không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("job_hiring_teams");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_uid", $this->session_user_id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("company_id", $this->session_company_id);
                $builder->update("job_group_id", str_replace("'", "''", $job_group_id));
                $builder->update("user_id", str_replace("'", "''", $user_id));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job hiring team thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job hiring team thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_hiring_team_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id FROM job_hiring_teams d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job inhiring không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d2.name AS job_role_name";
            $sql = $sql . ", d3.name AS user_name, d7.id AS avatar_id, d5.id AS department_id, d5.name AS department_name";
            $sql = $sql . ", d6.id AS user_group_id, d6.name AS group_name";
            $sql = $sql . " FROM job_hiring_teams d1";
            $sql = $sql . " LEFT OUTER JOIN job_group d2 ON(d1.job_group_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user_company d4 ON(d3.id = d4.user_id)";
            $sql = $sql . " LEFT OUTER JOIN res_company d5 ON(d5.id = d4.company_id)";
            $sql = $sql . " LEFT OUTER JOIN res_user_group d6 ON(d6.id = d4.group_id) ";
            $sql = $sql . " LEFT OUTER JOIN document d7 ON(d3.id = d7.rel_id AND d7.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
            $this->msg->add("query", $sql);

            $result =  $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            $data = array();
            if ($numrows > 0) {
                $row = $result->getRow(0);
                $data['id'] =   $row->getString("id");
                $data['job_role_name'] = $row->getString("job_role_name");
                $data['user_name'] =   $row->getString("user_name");
                $data['avatar_id'] = $row->getString("avatar_id");
                $data['department_id'] =   $row->getString("department_id");
                $data['department_name'] = $row->getString("department_name");
                $data['group_id'] =   $row->getString("user_group_id");
                $data['group_name'] = $row->getString("group_name");
            }

            $message = [
                'status' => true,
                'data' => ['job_inhiring_teams' => $data],
                'message' => "Lấy job inhiring team by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_hiring_team($id, Response $response)
    {
        $sql = "SELECT d1.id FROM job_hiring_teams d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job hiring team không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("job_hiring_teams");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job hiring team thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS JOB HIRING TEAM

    // START JOB SKILLS

    public function get_job_skills(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.job_id, d1.skill_id, d1.description, d2.name AS skill_name FROM job_skills d1";
        $sql = $sql . " LEFT OUTER JOIN skills d2 ON(d1.skill_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.job_id='" . $rel_id . "'";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['job_id'] = $result[$i][1];
            $arr['skill_id'] = $result[$i][2];
            $arr['description'] = $result[$i][3];
            $arr['skill_name'] = $result[$i][4];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_skills' => $data],
            'message' => "Lấy danh sách job skill thành công."
        ];

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function create_job_skills(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $skill_id = $data['skill_id'];
        $description = $data['description'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'skill_id' => 'required',
            'description' => 'max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("job_skills");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("job_id", str_replace("'", "''", $rel_id));
                $builder->add("skill_id", str_replace("'", "''", $skill_id));
                $builder->add("description", str_replace("'", "''", $description));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job skills thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job skills thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_skills($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $skill_id = $data['skill_id'];
        $description = $data['description'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            //'skill_id' => 'required',
            'description' => 'max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_skills d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job skill không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("job_skills");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("skill_id", str_replace("'", "''", $skill_id));
                $builder->update("description", str_replace("'", "''", $description));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job skills thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job skills thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_skills_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.job_id, d1.skill_id, d1.description, d2.name AS skill_name FROM job_skills d1";
        $sql = $sql . " LEFT OUTER JOIN skills d2 ON(d1.skill_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['job_id'] = $row->getString("job_id");
            $arr['skill_id'] = $row->getString("skill_id");
            $arr['description'] = $row->getString("description");
            $arr['skill_name'] = $row->getString("skill_name");

            $message = [
                'status' => true,
                'data' => ['job_skill' => $arr],
                'message' => "Lấy danh sách job skill by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job skill không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_skills($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM job_skills d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job skill không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("job_skills");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job skill thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START JOB NOTES

    public function get_job_notes(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.id AS user_id, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.notesable_id='" . $rel_id . "'";
        $sql = $sql . " AND d1.notesable_type='job'";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['notesable_type'] = $result[$i][1];
            $arr['notesable_id'] = $result[$i][2];
            $arr['contents'] = $result[$i][3];
            $arr['user_id'] = $result[$i][4];
            $arr['user_name'] = $result[$i][5];
            $arr['name'] = $result[$i][6];
            $arr['create_date'] = $result[$i][7];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['jobs_note' => $data],
            'message' => "Lấy danh sách job note thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_notes(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $content = $data['contents'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'contents' => 'required|max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("notes");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("notesable_type", 'job');
                $builder->add("notesable_id", str_replace("'", "''", $rel_id));
                $builder->add("contents", str_replace("'", "''", $content));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job note thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job note thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_notes($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $content = $data['contents'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'contents' => 'max:256',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM notes d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job note không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("notes");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("contents", str_replace("'", "''", $content));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job note thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job note thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_notes_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " AND d1.notesable_type='job'";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['notesable_type'] = $row->getString("notesable_type");
            $arr['notesable_id'] = $row->getString("notesable_id");
            $arr['contents'] = $row->getString("contents");
            $arr['user_name'] = $row->getString("user_name");
            $arr['name'] = $row->getString("name");
            $arr['create_date'] = $row->getString("create_date");

            $message = [
                'status' => true,
                'data' => ['job_note' => $arr],
                'message' => "Lấy danh sách job note by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job note không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_notes($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM notes d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job note không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("notes");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job note thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START JOB STAGE

    public function get_job_stages(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.name, d1.parent_stage_id, d1.description, d1.sequence, d1.is_edit, d1.job_id FROM job_stages d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.job_id='" . $rel_id . "'";
        $sql = $sql . " ORDER BY d1.sequence ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $arr['parent_stage_id'] = $result[$i][2];
            $arr['description'] = $result[$i][3];
            $arr['sequence'] = $result[$i][4];
            $arr['is_edit'] = $result[$i][5];
            $arr['job_id'] = $result[$i][6];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['jobs_stages' => $data],
            'message' => "Lấy danh sách job states thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_stages(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $name = $data['name'];
        $parent_stage_id = $data['parent_stage_id'];
        $description = $data['description'];
        // $sequence = $data['sequence'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
            'numeric' => ':attribute không phải là số hợp lệ.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'name' => 'required|max:256',
            'parent_stage_id' => 'required',
            'description' => 'max:256',
            // 'sequence' => 'required|numeric',

        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {

                $sql = "SELECT MAX(sequence) AS counts FROM job_stages d1 WHERE d1.status=0 AND d1.job_id='" . $rel_id . "'";
                $this->msg->add("query", $sql);
                $counts = $this->appSession->getTier()->getValue($this->msg);

                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("job_stages");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("name", str_replace("'", "''", $name));
                $builder->add("parent_stage_id", str_replace("'", "''", $parent_stage_id));
                $builder->add("description", str_replace("'", "''", $description));
                $builder->add("sequence", str_replace("'", "''", $counts + 1));
                $builder->add("is_edit", str_replace("'", "''", 0));
                $builder->add("job_id", str_replace("'", "''", $rel_id));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job stage thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job stage thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_stages($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];
        $parent_stage_id = $data['parent_stage_id'];
        $description = $data['description'];
        $sequence = $data['sequence'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
            'numeric' => ':attribute không phải là số hợp lệ.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'name' => 'max:256',
            //'parent_stage_id' => 'required',
            'description' => 'max:256',
            'sequence' => 'numeric',

        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_stages d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job stage không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("job_stages");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("name", str_replace("'", "''", $name));
                $builder->update("parent_stage_id", str_replace("'", "''", $parent_stage_id));
                $builder->update("description", str_replace("'", "''", $description));
                $builder->update("sequence", str_replace("'", "''", $sequence));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job stage thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job stage thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_stages_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.name, d1.parent_stage_id, d1.description, d1.sequence, d1.is_edit, d1.job_id FROM job_stages d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " ORDER BY d1.sequence ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['name'] = $row->getString("name");
            $arr['parent_stage_id'] = $row->getString("parent_stage_id");
            $arr['description'] = $row->getString("description");
            $arr['sequence'] = $row->getString("sequence");
            $arr['is_edit'] = $row->getString("is_edit");
            $arr['job_id'] = $row->getString("job_id");

            $message = [
                'status' => true,
                'data' => ['job_note' => $arr],
                'message' => "Lấy danh sách job stage by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job stage không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_stages($id, Response $response)
    {
        $sql = "SELECT d1.id FROM job_stages d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job note không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("job_stages");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job stage thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START JOB CANDIDATE STAGE

    public function get_job_candidate_stages(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.name, d1.parent_stage_id, d1.description, d1.sequence, d1.is_edit, d1.job_id FROM job_stages d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.job_id='" . $rel_id . "'";
        $sql = $sql . " ORDER BY d1.sequence ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr_candidates = array();

            $sql = "SELECT d1.id, d1.create_date, d3.first_name, d3.last_name, d3.phone, d3.email, d4.id AS logo_id, d3.id AS candidate_id FROM job_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN jobs d2 ON(d1.job_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN candidates d3 ON(d1.candidate_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='logo')";
            $sql = $sql . " WHERE  d1.status=0";
            $sql = $sql . " AND d1.job_stage_id='" . $result[$i][0] . "'";
            $sql = $sql . " ORDER BY d1.create_date ASC";
            $this->msg->add("query", $sql);
            $result_job_candidates = $this->appSession->getTier()->getArray($this->msg);

            $arr['job_stage_id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $arr['parent_stage_id'] = $result[$i][2];
            $arr['description'] = $result[$i][3];
            $arr['sequence'] = $result[$i][4];
            $arr['is_edit'] = $result[$i][5];
            $arr['job_id'] = $result[$i][6];

            for ($j = 0; $j < count($result_job_candidates); $j++) {
                $arr_candidate = array();
                $arr_candidate['job_candidate_id'] = $result_job_candidates[$j][0];
                $arr_candidate['create_date'] = $result_job_candidates[$j][1];
                $arr_candidate['first_name'] = $result_job_candidates[$j][2];
                $arr_candidate['last_name'] = $result_job_candidates[$j][3];
                $arr_candidate['phone'] = $result_job_candidates[$j][4];
                $arr_candidate['email'] = $result_job_candidates[$j][5];
                $arr_candidate['logo_id'] = $result_job_candidates[$j][6];
                $arr_candidate['candidate_id'] = $result_job_candidates[$j][7];

                $sql = "SELECT d1.id, d3.user_name, d4.id AS avatar_id FROM candidate_followed_candidates d1";
                $sql = $sql . " LEFT OUTER JOIN candidate_followed d2 ON(d1.followed_id = d2.id)";
                $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
                $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
                $sql = $sql . " WHERE d1.candidate_id='" . $arr_candidate['candidate_id']  . "' AND d1.status=0";
                $sql = $sql . " ORDER BY d1.write_date ASC";
                $this->msg->add("query", $sql);
                $result_followed = $this->appSession->getTier()->getArray($this->msg);

                for ($j = 0; $j < count($result_followed); $j++) {
                    $arr_followed = array();
                    $arr_followed['id'] = $result_followed[$j][0];
                    $arr_followed['user_name'] = $result_followed[$j][1];
                    $arr_followed['avatar_id'] = $result_followed[$j][2];
                    $arr_user_followed[] = $arr_followed;
                }

                $arr_candidate['user_followed'] = $arr_user_followed;

                $arr_candidates[] = $arr_candidate;
            }
            $arr['candidates'] = $arr_candidates;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_candidate_stages' => $data],
            'message' => "Lấy danh sách job states thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_candidate_stages(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $candidate_id = $data['candidate_id'];
        $job_stage_id = $data['job_stage_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'candidate_id' => 'required',
            'job_stage_id' => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("job_candidates");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("candidate_id", str_replace("'", "''", $candidate_id));
                $builder->add("job_stage_id", str_replace("'", "''", $job_stage_id));
                $builder->add("job_id", str_replace("'", "''", $rel_id));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job candidate thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job candidate thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_job_candidate_stages($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $job_stage_id = $data['job_stage_id'];
        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'job_stage_id' => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_candidates d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Job candidate không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("job_candidates");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("job_stage_id", str_replace("'", "''", $job_stage_id));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job candidate stage thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job candidate stage thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_candidate_stages_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.name, d1.parent_stage_id, d1.description, d1.sequence, d1.is_edit, d1.job_id FROM job_stages d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " ORDER BY d1.sequence ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr_candidates = array();

            $sql = "SELECT d1.id, d1.create_date, d3.first_name, d3.last_name, d3.phone, d3.email, d4.id AS logo_id, d3.id AS candidate_id FROM job_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN jobs d2 ON(d1.job_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN candidates d3 ON(d1.candidate_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='logo')";
            $sql = $sql . " WHERE  d1.status=0";
            $sql = $sql . " AND d1.job_stage_id='" . $row->getString("id") . "'";
            $sql = $sql . " ORDER BY d1.create_date ASC";
            $this->msg->add("query", $sql);
            $result_job_candidates = $this->appSession->getTier()->getArray($this->msg);

            $arr['job_stage_id'] = $row->getString("id");
            $arr['name'] = $row->getString("name");
            $arr['parent_stage_id'] = $row->getString("parent_stage_id");
            $arr['description'] = $row->getString("description");
            $arr['sequence'] = $row->getString("sequence");
            $arr['is_edit'] = $row->getString("is_edit");
            $arr['job_id'] = $row->getString("job_id");

            for ($j = 0; $j < count($result_job_candidates); $j++) {
                $arr_candidate = array();
                $arr_candidate['job_candidate_id'] = $result_job_candidates[$j][0];
                $arr_candidate['create_date'] = $result_job_candidates[$j][1];
                $arr_candidate['first_name'] = $result_job_candidates[$j][2];
                $arr_candidate['last_name'] = $result_job_candidates[$j][3];
                $arr_candidate['phone'] = $result_job_candidates[$j][4];
                $arr_candidate['email'] = $result_job_candidates[$j][5];
                $arr_candidate['logo_id'] = $result_job_candidates[$j][6];
                $arr_candidate['candidate_id'] = $result_job_candidates[$j][7];
                $arr_candidates[] = $arr_candidate;
            }
            $arr['candidates'] = $arr_candidates;

            $message = [
                'status' => true,
                'data' => ['job_candidate_stage' => $arr],
                'message' => "Lấy danh sách job candidate stage by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job stage không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_job_candidate_stages($id, Response $response)
    {

        $sql = "SELECT d1.id FROM job_stages d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job note không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("job_stages");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job stage thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // START JOB COMMISSIONS

    public function get_job_commissions(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.vendor_id, d1.vendor_type_id, d1.commission_type_id, d1.commission_kpi_type_id,";
        $sql = $sql . " d2.name AS vendor_name, d3.name AS vendor_name_type, d4.name AS commission_type_name, d5.name AS commission_kpi_name,";
        $sql = $sql . " d1.value, d1.kpi";
        $sql = $sql . " FROM job_commissions d1";
        $sql = $sql . " LEFT OUTER JOIN vendors d2 ON(d1.vendor_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_types d3 ON(d1.vendor_type_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_commission_types d4 ON(d1.commission_type_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_commission_kpis d5 ON(d1.commission_kpi_type_id = d5.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.rel_id='" . $rel_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['vendor_id'] = $result[$i][1];
            $arr['vendor_type_id'] = $result[$i][2];
            $arr['commission_type_id'] = $result[$i][3];
            $arr['commission_kpi_type_id'] = $result[$i][4];
            $arr['vendor_name'] = $result[$i][5];
            $arr['vendor_name_type'] = $result[$i][6];
            $arr['commission_type_name'] = $result[$i][7];
            $arr['commission_kpi_name'] = $result[$i][8];
            $arr['value'] = $result[$i][9];
            $arr['kpi'] = $result[$i][10];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_commissions' => $data],
            'message' => "Lấy danh sách job commission thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_job_commissions(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $vendor_id = $data['vendor_id'];
        $vendor_type_id = $data['vendor_type_id'];
        $commission_type_id = $data['commission_type_id'];
        $value = $data['value'];
        $commission_kpi_type_id = $data['commission_kpi_type_id'];
        $kpi = $data['kpi'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_type_id' => 'required',
            'value' => 'required|max:256',
            'commission_kpi_type_id' => 'required',
            'kpi' => 'required|max:1000'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("job_commissions");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("vendor_id", str_replace("'", "''", $vendor_id));
                $builder->add("vendor_type_id", str_replace("'", "''", $vendor_type_id));
                $builder->add("commission_type_id", str_replace("'", "''", $commission_type_id));
                $builder->add("value", str_replace("'", "''", $value));
                $builder->add("commission_kpi_type_id", str_replace("'", "''", $commission_kpi_type_id));
                $builder->add("rel_id", str_replace("'", "''", $rel_id));
                $builder->add("kpi", str_replace("'", "''", $kpi));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job commission thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job commission thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }
    public function update_job_commissions($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $vendor_id = $data['vendor_id'];
        $vendor_type_id = $data['vendor_type_id'];
        $commission_type_id = $data['commission_type_id'];
        $value = $data['value'];
        $commission_kpi_type_id = $data['commission_kpi_type_id'];
        $kpi = $data['kpi'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_type_id' => 'required',
            'value' => 'required|max:256',
            'commission_kpi_type_id' => 'required',
            'kpi' => 'required|max:1000'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM job_commissions d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Job commission của bạn không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("job_commissions");
                $builder->update("id", $id);
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("vendor_id", str_replace("'", "''", $vendor_id));
                $builder->update("vendor_type_id", str_replace("'", "''", $vendor_type_id));
                $builder->update("commission_type_id", str_replace("'", "''", $commission_type_id));
                $builder->update("value", str_replace("'", "''", $value));
                $builder->update("commission_kpi_type_id", str_replace("'", "''", $commission_kpi_type_id));
                $builder->update("rel_id", str_replace("'", "''", $rel_id));
                $builder->update("kpi", str_replace("'", "''", $kpi));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job commission thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job commission thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }
    public function get_job_commissions_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id, d1.vendor_id, d1.vendor_type_id, d1.commission_type_id, d1.commission_kpi_type_id,";
        $sql = $sql . " d2.name AS vendor_name, d3.name AS vendor_name_type, d4.name AS commission_type_name, d5.name AS commission_kpi_name,";
        $sql = $sql . " d1.value, d1.kpi";
        $sql = $sql . " FROM job_commissions d1";
        $sql = $sql . " LEFT OUTER JOIN vendors d2 ON(d1.vendor_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_types d3 ON(d1.vendor_type_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_commission_types d4 ON(d1.commission_type_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN vendor_commission_kpis d5 ON(d1.commission_kpi_type_id = d5.id)";
        $sql = $sql . " WHERE d1.id='" . $id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {
            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['vendor_id'] = $row->getString("vendor_id");
            $arr['vendor_type_id'] = $row->getString("vendor_type_id");
            $arr['commission_type_id'] = $row->getString("commission_type_id");
            $arr['commission_kpi_type_id'] = $row->getString("commission_kpi_type_id");
            $arr['vendor_name'] = $row->getString("vendor_name");
            $arr['vendor_name_type'] = $row->getString("vendor_name_type");
            $arr['commission_type_name'] = $row->getString("commission_type_name");
            $arr['commission_kpi_name'] = $row->getString("commission_kpi_name");
            $arr['value'] = $row->getString("value");
            $arr['kpi'] = $row->getString("kpi");

            $message = [
                'status' => true,
                'data' => ['job_commission' => $arr],
                'message' => "Lấy danh sách job commission by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job commission không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }
    public function delete_job_commissions($id, Response $response)
    {

        $sql = "SELECT d1.id FROM job_commissions d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Job commission không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("job_commissions");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job commissions thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START JOB FOLLOWED

    public function add_job_followed(Request $request, Response $response)
    {
        $data = $request->request->all();
        $job_id = $data['job_id'];

        $followed_id = $this->find_job_followed_id();

        $sql = "SELECT d1.id FROM job_followed_jobs d1 WHERE d1.status=0 AND d1.followed_id='" . $followed_id . "' AND d1.job_id='" . $job_id . "'";
        $this->msg->add("query", $sql);
        $followed_job_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_job_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("job_followed_jobs");
            $followed_job_id = $this->appSession->getTool()->getId();
            $builder->add("id", $followed_job_id);
            $builder->add("followed_id", $followed_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("job_id", $job_id);
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $sql = $this->appSession->getTier()->getInsert($builder);

            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);
        }

        $message = [
            'status' => true,
            'message' => "Tạo job followed thành công."
        ];
        return $this->appSession->getTier()->response($message, $response);
    }
    public function find_job_followed_id()
    {
        $user_id = $this->session_user_id;

        $sql = "SELECT d1.id FROM job_followed d1 WHERE d1.status = 0 AND d1.user_id='" . $user_id . "'";

        $this->msg->add("query", $sql);
        $followed_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("job_followed");
            $followed_id = $this->appSession->getTool()->getId();
            $builder->add("id", $followed_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("user_id", $user_id);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);
        }

        return $followed_id;
    }
    public function remove_job_followed($id, Request $request, Response $response)
    {
        $followed_id = $this->find_job_followed_id();

        $sql = "SELECT d1.id FROM job_followed_jobs d1 WHERE d1.status = 0 AND (d1.job_id='" . $id . "' AND d1.followed_id='" . $followed_id . "')";
        $this->msg->add("query", $sql);

        $followed_job_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_job_id == "") {
            $message = [
                'status' => true,
                'message' => "Job followed không tồn tại."
            ];
        } else {
            $sql = "UPDATE job_followed_jobs SET status='1', write_date=NOW() WHERE id='" . $followed_job_id . "'";
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa job followed thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START JOB SOFT COLUMNS

    public function job_soft_column(Response $response)
    {
        $soft_column = [
            "title" => "title",
        ];

        $message = [
            'status' => true,
            'data' => ['job_column_soft' => $soft_column],
            'message' => "Lấy danh sách column soft thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // START JOB SOFT BY

    public function job_soft_by(Response $response)
    {
        $soft_by = [
            "ASC" => "ASC",
            "DESC" => "DESC",
        ];

        $message = [
            'status' => true,
            'data' => ['job_soft_by' => $soft_by],
            'message' => "Lấy danh sách soft by thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
}
