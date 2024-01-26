<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

require_once(ABSPATH . 'app/services/WebService.php');
require_once(ABSPATH . 'app/services/ServiceCandidate.php');

class CandidateController extends Controller
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


    // START CANDIDATE
    public function get_candidate(Request $request, Response $response)
    {
        $param = $request->query->all();

        $search = $param['search'];
        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $location_id = $param['location_id'];
        $in_jobs = $param['in_jobs'];
        $handling_by = $param['handling_by'];
        $followed = $param['followed'] ?? "0";

        $soft_by = $param['soft_by'] ?? "DESC";
        $soft_column = $param['soft_column'] ?? "create_date";
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.expect_location_id, d1.first_name, d1.last_name, d1.headline, d1.phone, d1.email, d1.birthday";
        $sql = $sql . ", d1.national_id, d1.source_id, d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary, d1.job_title";
        $sql = $sql . ", d1.qualification, d1.current_salary, d1.expected_salary, d1.experience_years, d1.candidate_level_id";
        $sql = $sql . ", d2.name AS expect_location_name, d3.name AS national_name, d4.name AS source_name, d5.name AS city_name";
        $sql = $sql . ", d6.name AS district_name, d7.name AS ward_name, d8.name AS candidate_level_name, d9.id AS logo_id, d1.create_date";
        $sql = $sql . ", d1.personal_link, d1.additional_projects, d1.achievements, d1.award, d1.relevant_certificates, d1.ca_references, d10.id AS portfolio_id, d1.create_uid";
        $sql = $sql . ", d12.followed_id";
        $sql = $sql . ", CASE WHEN d12.candidate_id IS NULL THEN 0 ELSE 1 END AS is_followed";
        $sql = $sql . " FROM candidates d1";
        $sql = $sql . " LEFT OUTER JOIN res_address d2 ON(d1.expect_location_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_country d3 ON(d1.national_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d4 ON(d1.source_id = d4.id AND d4.status=0)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.city_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.district_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d7 ON(d1.ward_id = d7.id)";
        $sql = $sql . " LEFT OUTER JOIN candidate_level d8 ON(d1.candidate_level_id = d8.id AND d8.status=0)";
        $sql = $sql . " LEFT OUTER JOIN document d9 ON(d1.id = d9.rel_id AND d9.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN document d10 ON(d1.id = d10.rel_id AND d10.document_type_rel='portfolio')";
        $sql = $sql . " LEFT OUTER JOIN job_candidates d11 ON(d1.id = d11.candidate_id AND d11.status=0)";
        $sql = $sql . " LEFT OUTER JOIN candidate_followed_candidates d12 ON(d1.id = d12.candidate_id AND d12.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($followed == "1") {
            $followed_id = $this->find_candidate_followed_id();
            $sql = $sql . " AND d12.status=0 AND d12.followed_id='" . $followed_id . "'";
        }

        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.first_name", "d1.last_name", "d1.phone", "d1.email", "d1.job_title", "CONCAT(d1.first_name, ' ', d1.last_name)"], $search) . ")";
        }

        if ($start_date != "" && $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($location_id != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $location_id) . ")";
        }

        if ($in_jobs != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d11.job_id", $in_jobs) . ")";
        }

        if ($handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $handling_by) . ")";
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
            $arr_exepect_location = array();
            $arr_national = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_candidate_level = array();
            $arr_skills = array();
            $arr_jobs = array();
            $arr_user_followed = array();

            $arr['id'] = $result[$i][0];
            $arr['expect_location_id'] = $result[$i][1];
            $arr['first_name'] = $result[$i][2];
            $arr['last_name'] = $result[$i][3];
            $arr['headline'] = $result[$i][4];
            $arr['phone'] = $result[$i][5];
            $arr['email'] = $result[$i][6];
            $arr['birthday'] = $result[$i][7];
            $arr['national_id'] = $result[$i][8];
            $arr['source_id'] = $result[$i][9];
            $arr['city_id'] = $result[$i][10];
            $arr['district_id'] = $result[$i][11];
            $arr['ward_id'] = $result[$i][12];
            $arr['address'] = $result[$i][13];
            $arr['summary'] = $result[$i][14];
            $arr['job_title'] = $result[$i][15];
            $arr['qualification'] = $result[$i][16];
            $arr['current_salary'] = $result[$i][17];
            $arr['expected_salary'] = $result[$i][18];
            $arr['experience_years'] = $result[$i][19];
            $arr['candidate_level_id'] = $result[$i][20];
            $arr['create_date'] = $result[$i][29];

            $arr['personal_link'] = $result[$i][30];
            $arr['additional_projects'] = $result[$i][31];
            $arr['achievements'] = $result[$i][32];
            $arr['award'] = $result[$i][33];
            $arr['relevant_certificates'] = $result[$i][34];
            $arr['ca_references'] = $result[$i][35];
            $arr['portfolio_id'] = $result[$i][36];
            $arr['create_uid'] = $result[$i][37];
            $arr['followed_id'] = $result[$i][38];
            $arr['is_followed'] = $result[$i][39];

            $arr_exepect_location['id'] = $result[$i][1];
            $arr_exepect_location['name'] = $result[$i][21];
            $arr['exepect_location'] = $arr_exepect_location;

            $arr_national['id'] = $result[$i][8];
            $arr_national['name'] = $result[$i][22];
            $arr['national'] = $arr_national;

            $arr_source['id'] = $result[$i][9];
            $arr_source['name'] = $result[$i][23];
            $arr['source'] = $arr_source;

            $arr_city['id'] = $result[$i][10];
            $arr_city['name'] = $result[$i][24];
            $arr['city'] = $arr_city;

            $arr_district['id'] = $result[$i][11];
            $arr_district['name'] = $result[$i][25];
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $result[$i][12];
            $arr_ward['name'] = $result[$i][26];
            $arr['ward'] = $arr_ward;

            $arr_candidate_level['id'] = $result[$i][20];
            $arr_candidate_level['name'] = $result[$i][27];
            $arr['candidate_level'] = $arr_candidate_level;

            $arr['logo_id'] = $result[$i][28];


            $sql = "SELECT d1.id, d3.name, d1.description FROM candidate_skills d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id AND d2.status=0)";
            $sql = $sql . " LEFT OUTER JOIN skills d3 ON(d1.skill_id = d3.id AND d3.status=0)";
            $sql = $sql . " WHERE d1.candidate_id='" . $arr['id'] . "' AND d1.status=0";
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

            $sql = "SELECT d1.job_id, d1.candidate_id, d1.job_stage_id, d3.title AS job_name, d4.name AS stage_name, d5.id AS logo_id FROM job_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id AND d2.status=0)";
            $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id AND d3.status=0)";
            $sql = $sql . " LEFT OUTER JOIN job_stages d4 ON(d1.job_stage_id = d4.id AND d4.status=0)";
            $sql = $sql . " LEFT OUTER JOIN document d5 ON(d3.id = d5.rel_id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
            $sql = $sql . " AND d1.status=0";
            $sql = $sql . " AND d1.candidate_id='" . $arr['id'] . "'";
            $sql = $sql . " ORDER BY d1.create_date ASC";

            $this->msg->add("query", $sql);

            $result_jobs = $this->appSession->getTier()->getArray($this->msg);
            for ($k = 0; $k < count($result_jobs); $k++) {
                $arr_job = array();
                $arr_job['job_id'] = $result_jobs[$k][0];
                $arr_job['candidate_id'] = $result_jobs[$k][1];
                $arr_job['job_stage_id'] = $result_jobs[$k][2];
                $arr_job['job_name'] = $result_jobs[$k][3];
                $arr_job['stage_name'] = $result_jobs[$k][4];
                $arr_job['logo_id'] = $result_jobs[$k][5];
                $arr_jobs[] = $arr_job;
            }
            $arr['in_jobs'] = $arr_jobs;

            $sql = "SELECT d1.id, d3.user_name, d4.id AS avatar_id, d3.id AS user_id FROM candidate_followed_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN candidate_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.candidate_id='" . $arr['id'] . "' AND d1.status=0";
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


            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['candidates' => $data],
            'message' => "Lấy danh sách candidate thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_candidate(Request $request, Response $response)
    {
        $data = $request->request->all();
        $logo = $request->files->get('logo');

        $expect_location_id = $data['expect_location_id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $headline = $data['headline'];
        $phone = $data['phone'];
        $email = $data['email'];
        $birth_day = $data['birth_day'];
        $national_id = $data['national_id'];
        $source_id = $data['source_id'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $summary = $data['summary'];
        $job_title = $data['job_title'];
        $qualification = $data['qualification'];
        $current_salary = $data['current_salary'];
        $expected_salary = $data['expected_salary'];
        $experience_year = $data['experience_year'];
        $candidate_level_id = $data['candidate_level_id'];
        $personal_link = $data['personal_link'];
        $additional_projects = $data['additional_projects'];
        $achievements = $data['achievements'];
        $award = $data['award'];
        $relevant_certificates = $data['relevant_certificates'];
        $ca_references = $data['ca_references'];


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
            'expect_location_id' => 'required',
            'first_name' => 'required|max:256',
            'last_name' => 'required|max:256',
            'headline' => 'required|max:256',
            'phone' => 'required',
            'email' => 'required|email',
            'birth_day' => 'required|date:Y-m-d',
            'national_id' => 'required',
            'source_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required|max:256',
            //'summary' => 'max:1000',
            'job_title' => 'required|max:256',
            'qualification' => 'required|integer',
            'current_salary' => 'required|integer',
            'expected_salary' => 'required|integer',
            'experience_year' => 'required|integer',
            'candidate_level_id' => 'required',
            'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.email, d1.phone FROM candidates d1 WHERE d1.status=0 AND (d1.email='" . $email . "' OR d1.phone='" . $phone . "') AND d1.company_id='" . $this->session_company_id . "'";

            $this->msg->add("query", $sql);

            $result = $this->appSession->getTier()->getTable($this->msg);
            $numrows = $result->getRowCount();

            if ($numrows > 0) {
                $row = $result->getRow(0);
                if ($row->getString("email") != "" && $row->getString("email") == $email) {
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
                $builder = $this->appSession->getTier()->createBuilder("candidates");
                $id = $this->appSession->getTool()->getId();
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("expect_location_id", str_replace("'", "''", $expect_location_id));
                $builder->add("first_name", str_replace("'", "''", $first_name));
                $builder->add("last_name", str_replace("'", "''", $last_name));
                $builder->add("headline", str_replace("'", "''", $headline));
                $builder->add("phone", str_replace("'", "''", $phone));
                $builder->add("email", str_replace("'", "''", $email));
                $builder->add("birthday", str_replace("'", "''", $birth_day));
                $builder->add("national_id", str_replace("'", "''", $national_id));
                $builder->add("source_id", str_replace("'", "''", $source_id));
                $builder->add("city_id", str_replace("'", "''", $city_id));
                $builder->add("district_id", str_replace("'", "''", $district_id));
                $builder->add("ward_id", str_replace("'", "''", $ward_id));
                $builder->add("address", str_replace("'", "''", $address));
                $builder->add("summary", $this->appSession->getTool()->replace($summary, "'", "''"));
                $builder->add("job_title", str_replace("'", "''", $job_title));
                $builder->add("qualification", str_replace("'", "''", $qualification));
                $builder->add("current_salary", str_replace("'", "''", $current_salary));
                $builder->add("expected_salary", str_replace("'", "''", $expected_salary));
                $builder->add("experience_years", str_replace("'", "''", $experience_year));
                $builder->add("candidate_level_id", str_replace("'", "''", $candidate_level_id));
                $builder->add("personal_link", str_replace("'", "''", $personal_link));
                $builder->add("additional_projects", str_replace("'", "''", $additional_projects));
                $builder->add("achievements", str_replace("'", "''", $achievements));
                $builder->add("award", str_replace("'", "''", $award));
                $builder->add("relevant_certificates", str_replace("'", "''", $relevant_certificates));
                $builder->add("ca_references", str_replace("'", "''", $ca_references));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result =  $this->appSession->getTier()->exec($this->msg);
                $logo_id = $this->create_document($request, $id);
                $portfolio_id = $this->create_portfolio($request, $id);
                if ($result == '1') {

                    $data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $this->session_company_id . "&message=" . $this->appSession->getTool()->urlEncode("type=database.hq.updated&name=candidates");
                    $ws = new \WebService($this->appSession);
                    $ws->sendMessage($data);
                    $status = new \ServiceCandidate($this->appSession);
                    $status->doCreate($id, "candidates", "",  $this->session_user_id, $this->session_company_id);
                    //
                    $data = $this->get_candidate_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo candidate thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_candidate($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $expect_location_id = $data['expect_location_id'];
        $first_name = $data['first_name'];
        $last_name = $data['last_name'];
        $headline = $data['headline'];
        $phone = $data['phone'];
        $email = $data['email'];
        $birth_day = $data['birth_day'];
        $national_id = $data['national_id'];
        $source_id = $data['source_id'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $summary = $data['summary'];
        $job_title = $data['job_title'];
        $qualification = $data['qualification'];
        $current_salary = $data['current_salary'];
        $expected_salary = $data['expected_salary'];
        $experience_year = $data['experience_year'];
        $candidate_level_id = $data['candidate_level_id'];

        $personal_link = $data['personal_link'];
        $additional_projects = $data['additional_projects'];
        $achievements = $data['achievements'];
        $award = $data['award'];
        $relevant_certificates = $data['relevant_certificates'];
        $ca_references = $data['ca_references'];

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
            // 'expect_location_id' => 'required',
            'first_name' => 'max:256',
            'last_name' => 'max:256',
            'headline' => 'max:256',
            'phone' => 'numeric',
            'email' => 'email',
            'birth_day' => 'date:Y-m-d',
            // 'national_id' => 'required',
            // 'source_id' => 'required',
            // 'city_id' => 'required',
            // 'district_id' => 'required',
            // 'ward_id' => 'required',
            'address' => 'max:256',
            //'summary' => 'max:1000',
            'job_title' => 'max:256',
            'qualification' => 'numeric',
            'current_salary' => 'numeric',
            'expected_salary' => 'numeric',
            'experience_year' => 'numeric',
            //'candidate_level_id' => 'required',
            // 'logo' => 'uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Candidate không tồn tại."
                ];
            } else {

                $builder = $this->appSession->getTier()->getBuilder("candidates");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("expect_location_id", str_replace("'", "''", $expect_location_id));
                $builder->update("first_name", str_replace("'", "''", $first_name));
                $builder->update("last_name", str_replace("'", "''", $last_name));
                $builder->update("headline", str_replace("'", "''", $headline));
                $builder->update("phone", str_replace("'", "''", $phone));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("birthday", str_replace("'", "''", $birth_day));
                $builder->update("national_id", str_replace("'", "''", $national_id));
                $builder->update("source_id", str_replace("'", "''", $source_id));
                $builder->update("city_id", str_replace("'", "''", $city_id));
                $builder->update("district_id", str_replace("'", "''", $district_id));
                $builder->update("ward_id", str_replace("'", "''", $ward_id));
                $builder->update("address", str_replace("'", "''", $address));
                $builder->update("summary", str_replace("'", "''", $summary));
                $builder->update("job_title", str_replace("'", "''", $job_title));
                $builder->update("qualification", str_replace("'", "''", $qualification));
                $builder->update("current_salary", str_replace("'", "''", $current_salary));
                $builder->update("expected_salary", str_replace("'", "''", $expected_salary));
                $builder->update("experience_years", str_replace("'", "''", $experience_year));
                $builder->update("candidate_level_id", str_replace("'", "''", $candidate_level_id));
                $builder->update("personal_link", str_replace("'", "''", $personal_link));
                $builder->update("additional_projects", str_replace("'", "''", $additional_projects));
                $builder->update("achievements", str_replace("'", "''", $achievements));
                $builder->update("award", str_replace("'", "''", $award));
                $builder->update("relevant_certificates", str_replace("'", "''", $relevant_certificates));
                $builder->update("ca_references", str_replace("'", "''", $ca_references));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $logo_id = $this->update_document($request, $id);
                $portfolio_id = $this->update_portfolio($request, $id);

                if ($result == '1') {
                    $data = $this->get_candidate_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật candidate thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_candidate_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.expect_location_id, d1.first_name, d1.last_name, d1.headline, d1.phone, d1.email, d1.birthday";
        $sql = $sql . ", d1.national_id, d1.source_id, d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary, d1.job_title";
        $sql = $sql . ", d1.qualification, d1.current_salary, d1.expected_salary, d1.experience_years, d1.candidate_level_id";
        $sql = $sql . ", d2.name AS expect_location_name, d3.name AS national_name, d4.name AS source_name, d5.name AS city_name";
        $sql = $sql . ", d6.name AS district_name, d7.name AS ward_name, d8.name AS candidate_level_name, d9.id AS logo_id, d1.create_date";
        $sql = $sql . ", d1.personal_link, d1.additional_projects, d1.achievements, d1.award, d1.relevant_certificates, d1.ca_references, d10.id AS portfolio_id";
        $sql = $sql . ", d11.id AS create_by_id, d11.name AS create_by_name";
        $sql = $sql . ", d12.followed_id";
        $sql = $sql . ", CASE WHEN d12.candidate_id IS NULL THEN 0 ELSE 1 END AS is_followed";
        $sql = $sql . " FROM candidates d1";
        $sql = $sql . " LEFT OUTER JOIN res_address d2 ON(d1.expect_location_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_country d3 ON(d1.national_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d4 ON(d1.source_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.city_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.district_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d7 ON(d1.ward_id = d7.id)";
        $sql = $sql . " LEFT OUTER JOIN candidate_level d8 ON(d1.candidate_level_id = d8.id)";
        $sql = $sql . " LEFT OUTER JOIN document d9 ON(d1.id = d9.rel_id AND d9.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN document d10 ON(d1.id = d10.rel_id AND d10.document_type_rel='portfolio')";
        $sql = $sql . " LEFT OUTER JOIN res_user d11 ON(d1.create_uid = d11.id)";
        $sql = $sql . " LEFT OUTER JOIN candidate_followed_candidates d12 ON(d1.id = d12.candidate_id AND d12.status=0)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();
            $arr_exepect_location = array();
            $arr_national = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_candidate_level = array();
            $arr_skills = array();
            $arr_jobs = array();
            $arr_created_by = array();
            $arr_user_followed = array();

            $arr['id'] = $row->getString("id");
            $arr['expect_location_id'] = $row->getString("expect_location_id");
            $arr['first_name'] = $row->getString("first_name");
            $arr['last_name'] = $row->getString("last_name");
            $arr['headline'] = $row->getString("headline");
            $arr['phone'] = $row->getString("phone");
            $arr['email'] = $row->getString("email");
            $arr['birthday'] = $row->getString("birthday");
            $arr['national_id'] = $row->getString("national_id");
            $arr['source_id'] = $row->getString("source_id");
            $arr['city_id'] = $row->getString("city_id");
            $arr['district_id'] = $row->getString("district_id");
            $arr['ward_id'] = $row->getString("ward_id");
            $arr['address'] = $row->getString("address");
            $arr['summary'] = $row->getString("summary");
            $arr['job_title'] = $row->getString("job_title");
            $arr['qualification'] = $row->getString("qualification");
            $arr['current_salary'] = $row->getString("current_salary");
            $arr['expected_salary'] = $row->getString("expected_salary");
            $arr['experience_years'] = $row->getString("experience_years");
            $arr['candidate_level_id'] = $row->getString("candidate_level_id");
            $arr['create_date'] = $row->getString("create_date");
            $arr['personal_link'] = $row->getString("personal_link");
            $arr['additional_projects'] = $row->getString("additional_projects");
            $arr['achievements'] = $row->getString("achievements");
            $arr['award'] = $row->getString("award");
            $arr['relevant_certificates'] = $row->getString("relevant_certificates");
            $arr['ca_references'] = $row->getString("ca_references");
            $arr['portfolio_id'] = $row->getString("portfolio_id");
            $arr['followed_id'] = $row->getString("followed_id");
            $arr['is_followed'] = $row->getString("is_followed");

            $arr_exepect_location['id'] = $row->getString("expect_location_id");
            $arr_exepect_location['name'] = $row->getString("expect_location_name");
            $arr['exepect_location'] = $arr_exepect_location;

            $arr_national['id'] = $row->getString("national_id");
            $arr_national['name'] = $row->getString("national_name");
            $arr['national'] = $arr_national;

            $arr_source['id'] = $row->getString("source_id");
            $arr_source['name'] = $row->getString("source_name");
            $arr['source'] = $arr_source;

            $arr_city['id'] = $row->getString("city_id");
            $arr_city['name'] = $row->getString("city_name");
            $arr['city'] = $arr_city;

            $arr_district['id'] = $row->getString("district_id");
            $arr_district['name'] = $row->getString("district_name");
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $row->getString("ward_id");
            $arr_ward['name'] = $row->getString("ward_name");
            $arr['ward'] = $arr_ward;

            $arr_candidate_level['id'] = $row->getString("candidate_level_id");
            $arr_candidate_level['name'] = $row->getString("candidate_level_name");
            $arr['candidate_level'] = $arr_candidate_level;

            $arr_created_by['created_by_id'] = $row->getString("create_by_id");
            $arr_created_by['created_by_name'] = $row->getString("create_by_name");
            $arr['created_by'] = $arr_created_by;

            $arr['logo_id'] = $row->getString("logo_id");

            $sql = "SELECT d1.id, d3.name, d1.description FROM candidate_skills d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN skills d3 ON(d1.skill_id = d3.id)";
            $sql = $sql . " WHERE d1.candidate_id='" . $arr['id'] . "' AND d1.status=0";
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

            $sql = "SELECT d1.job_id, d1.candidate_id, d1.job_stage_id, d3.title AS job_name, d4.name AS stage_name, d5.id AS logo_id FROM job_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN job_stages d4 ON(d1.job_stage_id = d4.id)";
            $sql = $sql . " LEFT OUTER JOIN document d5 ON(d3.id = d5.rel_id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
            $sql = $sql . " AND d1.status=0";
            $sql = $sql . " AND d1.candidate_id='" . $arr['id'] . "'";
            $sql = $sql . " ORDER BY d1.create_date ASC";

            $this->msg->add("query", $sql);

            $result_jobs = $this->appSession->getTier()->getArray($this->msg);
            for ($k = 0; $k < count($result_jobs); $k++) {
                $arr_job = array();
                $arr_job['job_id'] = $result_jobs[$k][0];
                $arr_job['candidate_id'] = $result_jobs[$k][1];
                $arr_job['job_stage_id'] = $result_jobs[$k][2];
                $arr_job['job_name'] = $result_jobs[$k][3];
                $arr_job['stage_name'] = $result_jobs[$k][4];
                $arr_job['logo_id'] = $result_jobs[$k][5];
                $arr_jobs[] = $arr_job;
            }

            $arr['in_jobs'] = $arr_jobs;

            $sql = "SELECT d1.id, d3.user_name, d4.id AS avatar_id, d3.id AS user_id FROM candidate_followed_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN candidate_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.candidate_id='" . $arr['id'] . "' AND d1.status=0";
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

            $message = [
                'status' => true,
                'data' => ['candidate' => $arr],
                'message' => 'Lấy candidate by id thành công.'
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Candidate không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_candidate($id, Response $response)
    {

        $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Candidate không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("candidates");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa candidate thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document(Request $request, $rel_id)
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

    public function update_document(Request $request, $rel_id)
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

    public function create_portfolio(Request $request, $rel_id)
    {
        $file = $request->files->get('portfolio');
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
            $builder->add("document_type_rel", 'portfolio');
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            return $file_id;
        }
    }
    public function update_portfolio(Request $request, $rel_id)
    {
        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='portfolio'";


        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $portfolio_id = $this->create_portfolio($request, $rel_id);
        } else {

            $file = $request->files->get('portfolio');

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
                $builder->update("document_type_rel", 'portfolio');
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

                return $file_id;
            }
        }
    }
    // START CANDIDATE RESUME
    public function get_candidate_resume(Request $request, Response $response)
    {
        $data = $request->query->all();
        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status = 0 AND d1.id='" . $rel_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Candidate không tồn tại."
            ];
        } else {

            $sql = "SELECT d1.id, d1.name, d1.type, d1.rel_id, d1.status, d1.path, d1.ext, d1.is_default FROM document d1 WHERE d1.status = 0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='resume'";

            $this->msg->add("query", $sql);

            $result = $this->appSession->getTier()->getArray($this->msg);

            $data = array();
            for ($i = 0; $i < count($result); $i++) {
                $arr = array();

                $arr['id'] = $result[$i][0];
                $arr['name'] = $result[$i][1];
                $arr['type'] = $result[$i][2];
                $arr['rel_id'] = $result[$i][3];
                $arr['status'] = $result[$i][4];
                $arr['path'] = $result[$i][5];
                $arr['ext'] = $result[$i][6];
                $arr['is_default'] = $result[$i][7];

                $arr['link_download'] = URL . "document/?id=" . $result[$i][0] . "";
                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'data' => ['resume_candidates' => $data],
                'message' => "Lấy danh sách resume thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_candidate_resume(Request $request, Response $response)
    {
        $data = $request->query->all();
        $form = $request->request->all();

        $rel_id = $data['rel_id'];
        $is_default = $form['is_default'] ?? 0;

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
            'mimes' => ':attribute phải là PDF.',

        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'resume' => 'required|uploaded_file|max:1M|mimes:pdf',

        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status = 0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Candidate không tồn tại."
                ];
            } else {

                $file_id = $this->create_document_resume($request, $rel_id, $is_default);

                if (!is_null($file_id)) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo resume thành công."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_candidate_resume($rel_id, Request $request, Response $response)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status = 0 AND d1.id='" . $rel_id . "'";

        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Resume không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("document");
            $builder->add("id", $rel_id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa resume thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_resume(Request $request, $rel_id, $is_default)
    {
        $file = $request->files->get('resume');
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
        $builder->add("document_type_rel", 'resume');
        $builder->add("is_default", $is_default);

        $sql = $this->appSession->getTier()->getInsert($builder);
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        return $file_id;
    }

    // START CANDIDATE SKILLS

    public function get_candidate_skills(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.candidate_id, d1.skill_id, d1.description, d2.name AS skill_name FROM candidate_skills d1";
        $sql = $sql . " LEFT OUTER JOIN skills d2 ON(d1.skill_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.candidate_id='" . $rel_id . "'";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['candidate_id'] = $result[$i][1];
            $arr['skill_id'] = $result[$i][2];
            $arr['description'] = $result[$i][3];
            $arr['skill_name'] = $result[$i][4];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['candidates_skill' => $data],
            'message' => "Lấy danh sách candidate skill thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_candidate_skills(Request $request, Response $response)
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
            $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Candidate của bạn không tồn tại."
                ];
            } else {
                $id = $this->appSession->getTool()->getId();
                $builder = $this->appSession->getTier()->createBuilder("candidate_skills");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("candidate_id", str_replace("'", "''", $rel_id));
                $builder->add("skill_id", str_replace("'", "''", $skill_id));
                $builder->add("description", str_replace("'", "''", $description));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo candidate skills thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo candidate skills thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_candidate_skills($id, Request $request, Response $response)
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
            'skill_id' => 'required',
            'description' => 'max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM candidate_skills d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Candidate skill không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("candidate_skills");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("skill_id", str_replace("'", "''", $skill_id));
                $builder->update("description", str_replace("'", "''", $description));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật candidate skills thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật candidate skills thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_candidate_skills_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.candidate_id, d1.skill_id, d1.description FROM candidate_skills d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['candidate_id'] = $row->getString("candidate_id");
            $arr['skill_id'] = $row->getString("skill_id");
            $arr['description'] = $row->getString("description");

            $message = [
                'status' => true,
                'data' => ['candidate_skill' => $arr],
                'message' => "Lấy danh sách candidate skill by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Candidate skill không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_candidate_skills($id, Response $response)
    {

        $sql = "SELECT d1.id FROM candidate_skills d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Candidate skill không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("candidate_skills");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa candidate skill thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START CANDIDATE NOTES

    public function get_candidate_notes(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.id AS user_id, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.notesable_id='" . $rel_id . "'";
        $sql = $sql . " AND d1.notesable_type='candidate'";
        $sql = $sql . " ORDER BY d1.create_date ASC";

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
            'data' => ['candidates_note' => $data],
            'message' => "Lấy danh sách candidate note thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_candidate_notes(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $content = $data['contents'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max ký tự.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'contents' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Candidate của bạn không tồn tại."
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
                $builder->add("notesable_type", 'candidate');
                $builder->add("notesable_id", str_replace("'", "''", $rel_id));
                $builder->add("contents", str_replace("'", "''", $content));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo candidate note thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo candidate note thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_candidate_notes($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $content = $data['contents'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max ký tự.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'contents' => 'required|max:256'
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
                    'message' => "Candidate note không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("notes");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("contents", str_replace("'", "''", $content));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật candidate note thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật candidate note  thất bại"
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_candidate_notes_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " AND d1.notesable_type='candidate'";
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
                'data' => ['candidate_note' => $arr],
                'message' => "Lấy danh sách candidate note by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Candidate skill không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_candidate_notes($id, Response $response)
    {

        $sql = "SELECT d1.id FROM notes d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Candidate note không tồn tại."
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
                'message' => "Xóa candidate note thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START CANDIDATE SOFT COLUMNS

    public function candidate_soft_column(Response $response)
    {
        $soft_column = [
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "phone" => "Phone",
            "email" => "Email",
            "job_title" => "Job Title",
            "create_date" => "Create Date",
            "write_date" => "Write Date",
        ];

        $message = [
            'status' => true,
            'data' => ['candidates_column_soft' => $soft_column],
            'message' => "Lấy danh sách column soft thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // START CANDIDATE SOFT BY

    public function candidate_soft_by(Response $response)
    {
        $soft_by = [
            "ASC" => "ASC",
            "DESC" => "DESC",
        ];

        $message = [
            'status' => true,
            'data' => ['candidates_soft_by' => $soft_by],
            'message' => "Lấy danh sách soft by thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    // START CANDIDATE APPLY TO JOB

    public function get_candidate_apply_to_job(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id FROM candidates d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => false,
                'message' => "Candidate của bạn không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.job_id, d1.candidate_id, d1.job_stage_id, d3.title AS job_name, d4.name AS stage_name, d5.id AS logo_id FROM job_candidates d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN job_stages d4 ON(d1.job_stage_id = d4.id)";
            $sql = $sql . " LEFT OUTER JOIN document d5 ON(d3.id = d5.rel_id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
            $sql = $sql . " AND d1.status=0";
            $sql = $sql . " AND d1.candidate_id='" . $rel_id . "'";
            $sql = $sql . " ORDER BY d1.create_date ASC";

            $this->msg->add("query", $sql);

            $result = $this->appSession->getTier()->getArray($this->msg);
            $data = array();
            for ($i = 0; $i < count($result); $i++) {
                $arr = array();

                $arr['job_id'] = $result[$i][0];
                $arr['candidate_id'] = $result[$i][1];
                $arr['job_stage_id'] = $result[$i][2];
                $arr['job_name'] = $result[$i][3];
                $arr['stage_name'] = $result[$i][4];
                $arr['logo_id'] = $result[$i][5];
                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'data' => ['candidate_apply_to_job' => $data],
                'message' => "Lấy danh sách candidate apply to job thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START ADD CANDIDATE APPLY TO JOB

    public function add_candidate_apply_to_job(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $job_id = $data['job_id'];
        $job_stage_id = $data['job_stage_id'];
        $resume_id = $data['resume_id'];
        $your_expect_salary = $data['your_expect_salary'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'integer' => ':attribute vui lòng nhập một số.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'job_id' => 'required',
            'job_stage_id' => 'required',
            'resume_id' => 'required',
            'your_expect_salary' => 'required|integer'
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
                $builder = $this->appSession->getTier()->createBuilder("job_candidates");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("candidate_id", str_replace("'", "''", $rel_id));
                $builder->add("job_stage_id", str_replace("'", "''", $job_stage_id));
                $builder->add("job_id", str_replace("'", "''", $job_id));
                $builder->add("resume_id", str_replace("'", "''", $resume_id));
                $builder->add("your_expect_salary", str_replace("'", "''", $your_expect_salary));

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

    // START CANDIDATE APPLY TO JOB

    public function get_stage_by_job(Request $request, Response $response)
    {
        $data = $request->query->all();

        $job_id = $data['job_id'];

        $sql = "SELECT d1.id FROM jobs d1 WHERE d1.status=0 AND d1.id='" . $job_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => false,
                'message' => "Job của bạn không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name, d1.parent_stage_id, d1.description, d1.sequence, d1.is_edit, d1.job_id FROM job_stages d1";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
            $sql = $sql . " AND d1.status=0";
            $sql = $sql . " AND d1.job_id='" . $job_id . "'";
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
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START CANDIDATE ADD INTERVIEW

    public function candidate_add_interview(Request $request, Response $response)
    {
        $data = $request->request->all();

        $candidate_id = $data['candidate_id'];
        $job_id = $data['job_id'];
        $interview_date = $data['interview_date'];
        $interview_from = $data['interview_from'];
        $interview_to = $data['interview_to'];
        $location = $data['location'];
        $description = $data['description'];
        $title = $data['title'];
        $attendees = $data['attendees'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'candidate_id' => 'required',
            'job_id' => 'required',
            'interview_date' => 'required|date:Y-m-d',
            'interview_from' => 'required',
            'interview_to' => 'required',
            'location' => 'required',
            'description' => 'required',
            'title' => 'required|max:1000',
            'attendees' => 'required|max:1000',

        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $id = $this->appSession->getTool()->getId();
            $builder = $this->appSession->getTier()->createBuilder("interviews");
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("candidate_id", str_replace("'", "''", $candidate_id));
            $builder->add("job_id", str_replace("'", "''", $job_id));
            $builder->add("interview_date", str_replace("'", "''", $interview_date));
            $builder->add("interview_from", str_replace("'", "''", $interview_from));
            $builder->add("interview_to", str_replace("'", "''", $interview_to));
            $builder->add("location", str_replace("'", "''", $location));
            $builder->add("description", str_replace("'", "''", $description));
            $builder->add("title", str_replace("'", "''", $title));
            $builder->add("attendees", str_replace("'", "''", $attendees));
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);
            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo candidate interview thành công"
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo candidate interview thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    // START CANDIDATE FOLLOWED

    public function add_candidate_followed(Request $request, Response $response)
    {
        $data = $request->request->all();
        $candidate_id = $data['candidate_id'];

        $followed_id = $this->find_candidate_followed_id();

        $sql = "SELECT d1.id FROM candidate_followed_candidates d1 WHERE d1.status=0 AND d1.followed_id='" . $followed_id . "' AND d1.candidate_id='" . $candidate_id . "'";
        $this->msg->add("query", $sql);
        $followed_candidate_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_candidate_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("candidate_followed_candidates");
            $followed_candidate_id = $this->appSession->getTool()->getId();
            $builder->add("id", $followed_candidate_id);
            $builder->add("followed_id", $followed_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("candidate_id", $candidate_id);
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $sql = $this->appSession->getTier()->getInsert($builder);

            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);
        }

        $message = [
            'status' => true,
            'message' => "Tạo candidate followed thành công."
        ];
        return $this->appSession->getTier()->response($message, $response);
    }
    public function find_candidate_followed_id()
    {
        $user_id = $this->session_user_id;

        $sql = "SELECT d1.id FROM candidate_followed d1 WHERE d1.status = 0 AND d1.create_uid='" . $user_id . "'";

        $this->msg->add("query", $sql);
        $followed_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("candidate_followed");
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
    public function remove_candidate_followed($id, Request $request, Response $response)
    {
        $followed_id = $this->find_candidate_followed_id();

        $sql = "SELECT d1.id FROM candidate_followed_candidates d1 WHERE d1.status = 0 AND (d1.candidate_id='" . $id . "' AND d1.followed_id='" . $followed_id . "')";
        $this->msg->add("query", $sql);

        $followed_candidate_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_candidate_id == "") {
            $message = [
                'status' => true,
                'message' => "Candidate followed không tồn tại."
            ];
        } else {
            $sql = "UPDATE candidate_followed_candidates SET status='1', write_date=NOW() WHERE id='" . $followed_candidate_id . "'";
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa candidate followed thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
}
