<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->session_user_id = session("session_user_id");
        $this->session_company_id = session("session_company_id");
    }

    // START CALENDAR
    public function get_calendar(Request $request)
    {
        $sql = "SELECT d1.interview_date";
        $sql = $sql . " FROM interviews d1";
        $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0 GROUP BY d1.interview_date";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr_interviews = array();
            $arr['interview_date'] = $result[$i][0];

            $date = date('Y-m-d', strtotime($arr['interview_date']));

            $sql = "SELECT d1.id, d1.candidate_id, d1.job_id, d1.interview_date, d1.location, d1.description, d1.interview_from, d1.interview_to, d1.title, d1.attendees";
            $sql = $sql . " ,d2.first_name , d2.last_name, d3.title";
            $sql = $sql . " FROM interviews d1";
            $sql = $sql . " LEFT OUTER JOIN candidates d2 ON(d1.candidate_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN jobs d3 ON(d1.job_id = d3.id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
            $sql = $sql . " AND d1.interview_date>='" . $date . " 00:00:00' AND d1.interview_date<='" . $date . " 23:59:59'";
            $this->msg->add("query", $sql);

            $result_interviews = $this->appSession->getTier()->getArray($this->msg);
            for ($k = 0; $k < count($result_interviews); $k++) {
                $arr_data = array();
                $arr_data['id'] = $result_interviews[$k][0];
                $arr_data['candidate_id'] = $result_interviews[$k][1];
                $arr_data['candidate_first_name'] = $result_interviews[$k][10];
                $arr_data['candidate_last_name'] = $result_interviews[$k][11];
                $arr_data['job_id'] = $result_interviews[$k][2];
                $arr_data['job_title'] = $result_interviews[$k][12];
                $arr_data['interview_date'] = $result_interviews[$k][3];
                $arr_data['location'] = $result_interviews[$k][4];
                $arr_data['description'] = $result_interviews[$k][5];
                $arr_data['interview_from'] = $result_interviews[$k][6];
                $arr_data['interview_to'] = $result_interviews[$k][7];
                $arr_data['title'] = $result_interviews[$k][8];
                $arr_data['attendees'] = $result_interviews[$k][9];
                $arr_interviews[] = $arr_data;
            }
            $arr['in_interviews'] = $arr_interviews;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['interviews' => $data],
            'message' => "Lấy danh sách interviews thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
}
