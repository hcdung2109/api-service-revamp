<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class ReportController extends Controller
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

    public function get_report_job(Request $request, Response $response)
    {
        $param = $request->query->all();

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_locations = $param['in_locations'];
        $in_job_types = $param['in_job_types'];
        $in_handling_by = $param['in_handling_by'];


        // JOBS TOTAL
        $sql = "SELECT COUNT(d1.id) AS total , total_commission FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN (SELECT rel_id, SUM(value) AS total_commission FROM job_commissions GROUP BY rel_id) d2 ON(d1.id = d2.rel_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id, total_commission";


        $this->msg->add("query", $sql);
        $result_in_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job = 0;
        $total_job_commission = 0;

        for ($j = 0; $j < count($result_in_jobs); $j++) {
            $total_job += $result_in_jobs[$j][0];
            $total_job_commission += $result_in_jobs[$j][1];
        }

        // JOBS TOTAL ACTIVE

        $sql = "SELECT COUNT(d1.id) AS total , total_commission FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN (SELECT rel_id, SUM(value) AS total_commission FROM job_commissions GROUP BY rel_id) d2 ON(d1.id = d2.rel_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id, total_commission";

        $this->msg->add("query", $sql);
        $result_in_active_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_active = 0;
        $total_job_commission_active = 0;

        for ($j = 0; $j < count($result_in_active_jobs); $j++) {
            $total_job_active += $result_in_active_jobs[$j][0];
            $total_job_commission_active += $result_in_active_jobs[$j][1];
        }

        // JOBS TOTAL INACTIVE

        $sql = "SELECT COUNT(d1.id) AS total , total_commission FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN (SELECT rel_id, SUM(value) AS total_commission FROM job_commissions GROUP BY rel_id) d2 ON(d1.id = d2.rel_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=1";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id, total_commission";

        $this->msg->add("query", $sql);
        $result_in_active_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_in_active = 0;
        $total_job_commission_in_active = 0;

        for ($j = 0; $j < count($result_in_active_jobs); $j++) {
            $total_job_in_active += $result_in_active_jobs[$j][0];
            $total_job_commission_in_active += $result_in_active_jobs[$j][1];
        }

        // JOBS TOTAL JOB WIN

        $sql = "SELECT COUNT(d1.id) AS total , total_commission FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN (SELECT rel_id, SUM(value) AS total_commission FROM job_commissions GROUP BY rel_id) d2 ON(d1.id = d2.rel_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=2";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id, total_commission";

        $this->msg->add("query", $sql);
        $result_win_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_win = 0;
        $total_job_commission_win = 0;

        for ($j = 0; $j < count($result_win_jobs); $j++) {
            $total_job_win += $result_win_jobs[$j][0];
            $total_job_commission_win += $result_win_jobs[$j][1];
        }

        // JOBS TOTAL MOUNT

        $sql = "SELECT to_char(d1.create_date,'MON') as year_month , COUNT(d1.status) as jobs_completed, SUM(d2.amount) as revenue FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN invoices d2 ON(d1.companies_id = d2.companies_id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_job_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY year_month";
        $sql = $sql . " ORDER BY MIN(d1.create_date)";

        $this->msg->add("query", $sql);
        $result_month_jobs =  $this->appSession->getTier()->getArray($this->msg);

        //
        $arr = array();
        $arr_total = array();
        $arr_total_active = array();
        $arr_total_in_active = array();
        $arr_total_win_job = array();
        $arr_month_job = array();

        $arr_total['total'] = $total_job;
        $arr_total['total_commission'] = $total_job_commission;
        $arr['total'] = $arr_total;

        $arr_total_active['total'] = $total_job_active;
        $arr_total_active['total_commission'] = $total_job_commission_active;
        $arr['total_active'] = $arr_total_active;

        $arr_total_in_active['total'] = $total_job_in_active;
        $arr_total_in_active['total_commission'] = $total_job_commission_in_active;
        $arr['total_in_active'] = $arr_total_in_active;

        $arr_total_win_job['total'] = $total_job_win;
        $arr_total_win_job['total_commission'] = $total_job_commission_win;
        $arr['total_win_job'] = $arr_total_win_job;


        for ($j = 0; $j < count($result_month_jobs); $j++) {
            $arr_month = array();
            $arr_month['month'] = $result_month_jobs[$j][0];
            $arr_month['jobs_complete'] = $result_month_jobs[$j][1];
            $arr_month['revenue'] = $result_month_jobs[$j][2] ?? "0";

            $arr_month_job[] = $arr_month;
        }

        $arr['chart'] = $arr_month_job;

        $message = [
            'status' => true,
            'data' => ['total' => $arr],
            'message' => "Lấy total thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_report_application_candidates(Request $request, Response $response)
    {
        $param = $request->query->all();

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_locations = $param['in_locations'];
        $in_handling_by = $param['in_handling_by'];


        // CANDIDATES TOTAL
        $sql = "SELECT COUNT(d1.id) AS total FROM candidates d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id";


        $this->msg->add("query", $sql);
        $result_in_candidates =  $this->appSession->getTier()->getArray($this->msg);

        $total_candidates = 0;

        for ($j = 0; $j < count($result_in_candidates); $j++) {
            $total_candidates += $result_in_candidates[$j][0];
        }

        // APPLICATION TOTAL
        $sql = "SELECT COUNT(d1.id) AS total FROM job_stages d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_applications =  $this->appSession->getTier()->getArray($this->msg);

        $total_applications = 0;

        for ($j = 0; $j < count($result_in_applications); $j++) {
            $total_applications += $result_in_applications[$j][0];
        }
         // APPLICATION NAME TOTAL

         $sql = "SELECT d1.name, COUNT(d1.id) AS total FROM job_stages d1";
         $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
         $sql = $sql . " GROUP BY d1.name";
         $this->msg->add("query", $sql);
         $result_in_applications_name =  $this->appSession->getTier()->getArray($this->msg);

         $arr_total_stage_by_name = array();
         for ($j = 0; $j < count($result_in_applications_name); $j++) {
             $arr = array();
             $arr[$result_in_applications_name[$j][0]] = $result_in_applications_name[$j][1];

             $arr_total_stage_by_name[] = $arr;

         }

          // JOBS TOTAL MOUNT

          $sql = "SELECT to_char(d1.create_date,'MON') as year_month , COUNT(d1.status) as jobs_completed, SUM(d2.amount) as revenue FROM jobs d1";
          $sql = $sql . " LEFT OUTER JOIN invoices d2 ON(d1.companies_id = d2.companies_id)";
          $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

          if ($start_date != "" || $end_date  != "") {
              $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
          }

          if ($in_locations != "") {
              $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
          }

          if ($in_handling_by != "") {
              $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
          }

          $sql = $sql . " GROUP BY year_month";
          $sql = $sql . " ORDER BY MIN(d1.create_date)";

          $this->msg->add("query", $sql);
          $result_month_jobs =  $this->appSession->getTier()->getArray($this->msg);

        //
        $arr = array();
        $arr_total = array();
        $arr_total_applications = array();
        $arr_total_in_active = array();
        $arr_total_win_job = array();
        $arr_month_job = array();

        $arr_total['total'] = $total_candidates;
        $arr['total'] = $arr_total;

        $arr_total_applications['total'] = $total_applications;
        $arr_total_applications['total_by_name_stages'] = $arr_total_stage_by_name;
        $arr['total_applications'] = $arr_total_applications;

        for ($j = 0; $j < count($result_month_jobs); $j++) {
            $arr_month = array();
            $arr_month['month'] = $result_month_jobs[$j][0];
            $arr_month['jobs_complete'] = $result_month_jobs[$j][1];
            $arr_month['revenue'] = $result_month_jobs[$j][2] ?? "0";

            $arr_month_job[] = $arr_month;
        }

        $arr['chart'] = $arr_month_job;

        $message = [
            'status' => true,
            'data' => ['total' => $arr],
            'message' => "Lấy total thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);


    }

    public function get_report_source(Request $request, Response $response)
    {
        $param = $request->query->all();

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_locations = $param['in_locations'];
        $in_handling_by = $param['in_handling_by'];

        $sql = "SELECT d1.id, d1.name FROM sources d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_sources =  $this->appSession->getTier()->getArray($this->msg);


        $data = array();
        $data_arr = array();
        $data_arr_chart= array();
        for ($i = 0; $i < count($result_in_sources); $i++) {
            $arr = array();
            $arr_applications = array();

            $arr['id'] = $result_in_sources[$i][0];
            $arr['name'] = $result_in_sources[$i][1];

            $sql = "SELECT d1.name, SUM(d4.total_candidate) FROM job_stages d1";
            $sql = $sql . " LEFT OUTER JOIN (SELECT d2.job_stage_id, d3.source_id, COUNT(d2.id) AS total_candidate FROM job_candidates d2 LEFT OUTER JOIN candidates d3 ON(d3.id = d2.candidate_id) WHERE d2.status=0 AND d3.source_id='".$arr['id']."' GROUP BY d2.job_stage_id, d3.source_id) d4 ON(d1.id = d4.job_stage_id)";
            $sql = $sql . " WHERE (d1.is_edit='1' AND d1.status=0 AND d1.company_id='" . $this->session_company_id . "')";

            // if ($start_date != "" || $end_date  != "") {
            //     $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
            // }

            // if ($in_locations != "") {
            //     $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
            // }

            // if ($in_job_types != "") {
            //     $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.job_type_id", $in_job_types) . ")";
            // }

            // if ($in_handling_by != "") {
            //     $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $handling_by) . ")";
            // }

            $sql = $sql . " GROUP BY d1.name";
            $this->msg->add("query", $sql);
            $result_applications =  $this->appSession->getTier()->getArray($this->msg);
            for ($j= 0; $j < count($result_applications); $j++) {
                $arr_application = array();
                $arr_application['name'] = $result_applications[$j][0];
                $arr_application['total'] = $result_applications[$j][1] ?? "0";
                $arr_applications[] = $arr_application;
            }

            $arr['application_stage'] = $arr_applications;

            $data_arr[] = $arr;
        }

        $sql = "SELECT d1.name, SUM(d4.total_candidate) FROM job_stages d1";
        $sql = $sql . " LEFT OUTER JOIN (SELECT d2.job_stage_id, COUNT(d2.id) AS total_candidate FROM job_candidates d2 GROUP BY d2.job_stage_id) d4 ON(d1.id = d4.job_stage_id)";
        $sql = $sql . " WHERE (d1.is_edit='1' AND d1.status=0 AND d1.company_id='" . $this->session_company_id . "')";
        $sql = $sql . " GROUP BY d1.name";

        $this->msg->add("query", $sql);
        $result_applications =  $this->appSession->getTier()->getArray($this->msg);

        for ($j= 0; $j < count($result_applications); $j++) {
            $arr_application = array();
            $arr_application['name'] = $result_applications[$j][0];
            $arr_application['total'] = $result_applications[$j][1] ?? "0";
            $data_arr_chart[] = $arr_application;
        }
        $data['total_sources'] = $data_arr;
        $data['total_chart'] = $data_arr_chart;
        $message = [
            'status' => true,
            'data' => ['total' => $data],
            'message' => "Lấy total thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_report_company(Request $request, Response $response)
    {
        $param = $request->query->all();

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_locations = $param['in_locations'];
        $in_handling_by = $param['in_handling_by'];


        // COMPANIES TOTAL
        $sql = "SELECT COUNT(d1.id) AS total FROM companies d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id";


        $this->msg->add("query", $sql);
        $result_in_companies =  $this->appSession->getTier()->getArray($this->msg);

        $total_companies = 0;

        for ($j = 0; $j < count($result_in_companies); $j++) {
            $total_companies += $result_in_companies[$j][0];
        }

        // COMPANIES TOTAL 1 MOUTH

        $sql = "SELECT COUNT(1) AS counter FROM companies d1 WHERE d1.create_date >= date_trunc('month', CURRENT_DATE)";
        $sql = $sql . " AND d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $this->msg->add("query", $sql);
        $result_in_count_mouth =  $this->appSession->getTier()->getArray($this->msg);

        $total_count_mount = 0;

        for ($j = 0; $j < count($result_in_count_mouth); $j++) {
            $total_count_mount += $result_in_count_mouth[$j][0];

        }

        // JOBS TOTAL COMPANY

        $sql = "SELECT COUNT(d1.id) AS total FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $this->msg->add("query", $sql);
        $result_in_total_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_in_companies = 0;

        for ($j = 0; $j < count($result_in_total_jobs); $j++) {
            $total_job_in_companies += $result_in_total_jobs[$j][0];
        }

        // COMPANY COMMISSION

        $sql = "SELECT COUNT(d1.id) AS total FROM invoices d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_total_invoices =  $this->appSession->getTier()->getArray($this->msg);

        $total_invoices = 0;

        for ($j = 0; $j < count($result_in_total_invoices); $j++) {
            $total_invoices += $result_in_total_invoices[$j][0];
        }


        // COMPANY COMMISSION

        $sql = "SELECT COUNT(d1.id) AS total FROM companies_commissions d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_total_commisions =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_in_commission = 0;

        for ($j = 0; $j < count($result_in_total_commisions); $j++) {
            $total_job_in_commission += $result_in_total_commisions[$j][0];
        }

         // JOBS TOTAL MOUNT

         $sql = "SELECT to_char(d1.create_date,'MON') as year_month , COUNT(d1.status) as jobs_completed, SUM(d2.amount) as revenue FROM jobs d1";
         $sql = $sql . " LEFT OUTER JOIN invoices d2 ON(d1.companies_id = d2.companies_id)";
         $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

         if ($start_date != "" || $end_date  != "") {
             $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
         }

         if ($in_locations != "") {
             $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
         }

         if ($in_handling_by != "") {
             $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
         }

         $sql = $sql . " GROUP BY year_month";
         $sql = $sql . " ORDER BY MIN(d1.create_date)";

         $this->msg->add("query", $sql);
         $result_month_jobs =  $this->appSession->getTier()->getArray($this->msg);


        //
        $arr = array();
        $arr_total = array();
        $arr_total_count_mouth = array();
        $arr_total_job_company = array();
        $arr_total_revenue = array();
        $arr_total_commisions = array();
        $arr_month_job = array();

        $arr_total['total'] = $total_companies;
        $arr_total['total_mouth'] = $total_count_mount;
        $arr['total'] = $arr_total;

        $arr_total_job_company['total'] = $total_job_in_companies;
        $arr['total_job'] = $arr_total_job_company;

        $arr_total_revenue['total'] = $total_invoices;
        $arr['total_revenue'] = $arr_total_revenue;

        $arr_total_commisions['total'] = $total_job_in_commission;
        $arr['total_commisions'] = $arr_total_commisions;


        for ($j = 0; $j < count($result_month_jobs); $j++) {
            $arr_month = array();
            $arr_month['month'] = $result_month_jobs[$j][0];
            $arr_month['jobs_complete'] = $result_month_jobs[$j][1];
            $arr_month['revenue'] = $result_month_jobs[$j][2] ?? "0";

            $arr_month_job[] = $arr_month;
        }

        $arr['chart'] = $arr_month_job;

        $message = [
            'status' => true,
            'data' => ['total' => $arr],
            'message' => "Lấy total thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_report_vendor(Request $request, Response $response)
    {
        $param = $request->query->all();

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_locations = $param['in_locations'];
        $in_handling_by = $param['in_handling_by'];

        // VENDOR TOTAL
        $sql = "SELECT COUNT(d1.id) AS total FROM vendors d1";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $sql = $sql . " GROUP BY d1.id";


        $this->msg->add("query", $sql);
        $result_in_vendors =  $this->appSession->getTier()->getArray($this->msg);

        $total_vendors = 0;

        for ($j = 0; $j < count($result_in_vendors); $j++) {
            $total_vendors += $result_in_vendors[$j][0];
        }

        // VENDOR TOTAL 1 MOUTH

        $sql = "SELECT COUNT(1) AS counter FROM vendors d1 WHERE d1.create_date >= date_trunc('month', CURRENT_DATE)";
        $sql = $sql . " AND d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $this->msg->add("query", $sql);
        $result_in_count_mouth =  $this->appSession->getTier()->getArray($this->msg);

        $total_count_mount = 0;

        for ($j = 0; $j < count($result_in_count_mouth); $j++) {
            $total_count_mount += $result_in_count_mouth[$j][0];

        }

        // APPLICATION TOTAL VENDOR

        $sql = "SELECT COUNT(d1.id) AS total FROM jobs d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_locations != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $in_locations) . ")";
        }

        if ($in_handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
        }

        $this->msg->add("query", $sql);
        $result_in_total_jobs =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_in_companies = 0;

        for ($j = 0; $j < count($result_in_total_jobs); $j++) {
            $total_job_in_companies += $result_in_total_jobs[$j][0];
        }

        // COMPANY COMMISSION

        $sql = "SELECT COUNT(d1.id) AS total FROM invoices d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_total_invoices =  $this->appSession->getTier()->getArray($this->msg);

        $total_invoices = 0;

        for ($j = 0; $j < count($result_in_total_invoices); $j++) {
            $total_invoices += $result_in_total_invoices[$j][0];
        }


        // COMPANY COMMISSION

        $sql = "SELECT COUNT(d1.id) AS total FROM companies_commissions d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result_in_total_commisions =  $this->appSession->getTier()->getArray($this->msg);

        $total_job_in_commission = 0;

        for ($j = 0; $j < count($result_in_total_commisions); $j++) {
            $total_job_in_commission += $result_in_total_commisions[$j][0];
        }

         // JOBS TOTAL MOUNT

         $sql = "SELECT to_char(d1.create_date,'MON') as year_month , COUNT(d1.status) as jobs_completed, SUM(d2.amount) as revenue FROM jobs d1";
         $sql = $sql . " LEFT OUTER JOIN invoices d2 ON(d1.companies_id = d2.companies_id)";
         $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

         if ($start_date != "" || $end_date  != "") {
             $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
         }

         if ($in_locations != "") {
             $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.expect_location_id", $in_locations) . ")";
         }

         if ($in_handling_by != "") {
             $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $in_handling_by) . ")";
         }

         $sql = $sql . " GROUP BY year_month";
         $sql = $sql . " ORDER BY MIN(d1.create_date)";

         $this->msg->add("query", $sql);
         $result_month_jobs =  $this->appSession->getTier()->getArray($this->msg);


        //
        $arr = array();
        $arr_total = array();
        $arr_total_count_mouth = array();
        $arr_total_job_company = array();
        $arr_total_revenue = array();
        $arr_total_commisions = array();
        $arr_month_job = array();

        $arr_total['total'] = $total_vendors;
        $arr_total['total_mouth'] = $total_count_mount;
        $arr['total'] = $arr_total;

        $arr_total_job_company['total'] = $total_job_in_companies;
        $arr['total_job'] = $arr_total_job_company;

        $arr_total_revenue['total'] = $total_invoices;
        $arr['total_revenue'] = $arr_total_revenue;

        $arr_total_commisions['total'] = $total_job_in_commission;
        $arr['total_commisions'] = $arr_total_commisions;


        for ($j = 0; $j < count($result_month_jobs); $j++) {
            $arr_month = array();
            $arr_month['month'] = $result_month_jobs[$j][0];
            $arr_month['jobs_complete'] = $result_month_jobs[$j][1];
            $arr_month['revenue'] = $result_month_jobs[$j][2] ?? "0";

            $arr_month_job[] = $arr_month;
        }

        $arr['chart'] = $arr_month_job;

        $message = [
            'status' => true,
            'data' => ['total' => $arr],
            'message' => "Lấy total thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

}
