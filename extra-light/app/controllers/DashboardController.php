<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class DashboardController extends Controller
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

    // START DASHBOARD

    public function get_total(Request $request, Response $response)
    {
        // CANDIDATE
        $sql = "SELECT COUNT(d1.id) AS total FROM candidates d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date > current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_in_7_day_candidate =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_in_7_day_candidate = $result_in_7_day_candidate->getRow(0);


        $sql = "SELECT COUNT(d1.id) AS total FROM candidates d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date < current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_before_7_day_candidate =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_before_7_day_candidate = $result_before_7_day_candidate->getRow(0);

        // JOB

        $sql = "SELECT COUNT(d1.id) AS total FROM jobs d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date > current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_in_7_day_job =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_in_7_day_job = $result_in_7_day_job->getRow(0);


        $sql = "SELECT COUNT(d1.id) AS total FROM jobs d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date < current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_before_7_day_job =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_before_7_day_job = $result_before_7_day_job->getRow(0);


        // COMPANY

        $sql = "SELECT COUNT(d1.id) AS total FROM companies d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date > current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_in_7_day_companies =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_in_7_day_companies = $result_in_7_day_companies->getRow(0);


        $sql = "SELECT COUNT(d1.id) AS total FROM companies d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date < current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_before_7_day_companies =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_before_7_day_companies = $result_before_7_day_companies->getRow(0);

        // VENDORS

        $sql = "SELECT COUNT(d1.id) AS total FROM vendors d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date > current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_in_7_day_vendors =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_in_7_day_vendors = $result_in_7_day_vendors->getRow(0);


        $sql = "SELECT COUNT(d1.id) AS total FROM vendors d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.create_date < current_date - interval '7 days'";

        $this->msg->add("query", $sql);
        $result_before_7_day_vendors =  $this->appSession->getTier()->getTable($this->msg);

        $numrows_before_7_day_vendors = $result_before_7_day_vendors->getRow(0);


        if (
            $numrows_in_7_day_candidate > 0 || $numrows_before_7_day_candidate > 0
            || $numrows_in_7_day_job > 0 || $numrows_before_7_day_job > 0
            || $numrows_in_7_day_companies > 0 || $numrows_before_7_day_companies > 0
            || $numrows_in_7_day_vendors > 0 || $numrows_before_7_day_vendors > 0
        ) {

            $arr = array();


            $arr['total_in_candidate'] = $numrows_in_7_day_candidate->getString("total");
            $arr['total_before_candidate'] = $numrows_before_7_day_candidate->getString("total");
            $arr['total_in_job'] = $numrows_in_7_day_job->getString("total");
            $arr['total_before_job'] = $numrows_before_7_day_job->getString("total");
            $arr['total_in_company'] = $numrows_in_7_day_companies->getString("total");
            $arr['total_before_company'] = $numrows_before_7_day_companies->getString("total");
            $arr['total_in_vendor'] = $numrows_in_7_day_vendors->getString("total");
            $arr['total_before_vendor'] = $numrows_before_7_day_vendors->getString("total");

            $message = [
                'status' => true,
                'data' => ['total' => $arr],
                'message' => "Lấy total thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Lấy total thất bại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_opening(Request $request, Response $response)
    {
        $param = $request->query->all();
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 5;

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

        $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date DESC");

        $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);
        $this->msg->add("query", $arrResult->sql);

        $result = $this->appSession->getTier()->getArray($this->msg);

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

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id FROM job_followed_jobs d1";
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
                $arr_followed['user_name'] = $result_followed[$j][1];
                $arr_followed['avatar_id'] = $result_followed[$j][2];
                $arr_user_followed[] = $arr_followed;
            }

            $arr['user_followed'] = $arr_user_followed;

            $sql = "SELECT d1.id, d1.name, d2.total_candidate FROM job_stages d1  ";
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

    public function get_candidate_in_job(Request $request, Response $response)
    {
        $param = $request->query->all();
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 5;

        $sql = "SELECT d1.job_id, d1.candidate_id, d1.job_stage_id, d3.title AS job_name, d4.name AS stage_name, d5.id AS logo_id";
        $sql = $sql . " ,d6.id, d6.user_name, d6.name, d2.first_name, d2.last_name";
        $sql = $sql . " FROM job_candidates d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN job_stages d4 ON(d1.job_stage_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN document d5 ON(d3.id = d5.rel_id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d6 ON(d6.id = d2.create_uid)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";

        $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date DESC");

        $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);
        $this->msg->add("query", $arrResult->sql);

        $result = $this->appSession->getTier()->getArray($this->msg);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr_user = array();

            $arr['job_id'] = $result[$i][0];
            $arr['candidate_id'] = $result[$i][1];
            $arr['candidate_first_name'] = $result[$i][9];
            $arr['candidate_last_name'] = $result[$i][10];
            $arr['job_stage_id'] = $result[$i][2];
            $arr['job_name'] = $result[$i][3];
            $arr['stage_name'] = $result[$i][4];
            $arr['logo_id'] = $result[$i][5];

            $arr_user['id'] =  $result[$i][6];
            $arr_user['user_name'] =  $result[$i][7];
            $arr_user['name'] =  $result[$i][8];

            $arr['handling_by'] = $arr_user;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['candidate_in_jobs' => $data],
            'message' => "Lấy danh sách candidate in jobs thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }


}
