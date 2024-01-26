<?php

namespace AppLight\Controllers;

require_once(ABSPATH . 'app/services/WebService.php');
require_once(ABSPATH . 'app/services/Status.php');

use App\Http\Controllers\Controller;

class NotificationController extends Controller
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

    // START NOTIFICATIONS
    public function get_notification_detail($id, Request $request, Response $response)
    {

        $sql = "SELECT d1.id, d1.name, d1.description, d1.type, d1.notification_id, d1.create_date, d1.status FROM res_notification d1 WHERE d1.id='" . $id . "' AND d1.status=0 AND d1.seen=0 AND d1.status!=1";
        $sql = $sql . " ORDER BY d1.create_date ASC";
        $this->msg->add("query", $sql);
        $dt = $this->appSession->getTier()->getArray($this->msg);

        $sql = "UPDATE res_notification SET status=2 WHERE id='" . $id . "'";
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        $data = array();
        for ($i = 0; $i < count($dt); $i++) {
            $arr = array();
            $arr['id'] = $dt[$i][0];
            $arr['name'] = $dt[$i][1];
            $arr['description'] = $dt[$i][2];
            $arr['type'] = $dt[$i][3];
            $arr['notification_id'] = $dt[$i][4];
            $arr['create_date'] = $dt[$i][5];
            $arr['status'] = $dt[$i][6];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['notifications' => $data],
            'message' => "Lấy danh sách notifications thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    // START NOTIFICATIONS DETAIL
    public function get_notification_list(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];
        $fdate = $param['start_date'];
        $tdate = $param['end_date'];

        $sql = "SELECT d1.id, d1.name, d1.description, d1.type, d1.notification_id, d1.create_date FROM res_notification d1 WHERE (d1.status =0 OR d1.status=2)";
        $sql = $sql . " AND d1.company_id='" . $this->session_company_id . "'";

        if ($fdate != "") {
            $sql = $sql . " AND  d1.create_date>='" . $fdate . "'";
        }
        if ($tdate != "") {
            $sql = $sql . " AND  d1.create_date<='" . $tdate . "'";
        }
        if ($tdate != "") {
            $sql = $sql . " AND  d1.create_date<='" . $tdate . "'";
        }
        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.name", "d1.description"], $search) . ")";
        }
        $sql = $sql . " ORDER BY d1.create_date DESC";
        $sql = $sql . " LIMIT 50";
        $this->msg->add("query", $sql);
        $dt = $this->appSession->getTier()->getArray($this->msg);

        $data = array();
        for ($i = 0; $i < count($dt); $i++) {
            $arr = array();
            $arr['id'] = $dt[$i][0];
            $arr['name'] = $dt[$i][1];
            $arr['description'] = $dt[$i][2];
            $arr['type'] = $dt[$i][3];
            $arr['notification_id'] = $dt[$i][4];
            $arr['create_date'] = $dt[$i][5];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['notifications' => $data],
            'message' => "Lấy danh sách notifications thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    // START NOTIFICATIONS SEEN
    public function post_notification_seen($rel_id, Request $request, Response $response)
    {
        $sql = "SELECT d1.id FROM res_notification d1 WHERE d1.status = 0 AND d1.id='" . $rel_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Notification không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("res_notification");
            $builder->add("id", $rel_id);
            $builder->add("seen", 1);
            $builder->add("write_date", $this->appSession->getTier()->getDateString());
            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Seen notification thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    //     public function post_notification_test(Request $request, Response $response)
    //     {
    //         $data = $request->request->all();

    //         $sale_id = $data['sale_id'];


    //         $data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $this->session_company_id . "&message=" . $this->appSession->getTool()->urlEncode("type=database.hq.updated&name=candidates");
    //         $ws = new \WebService($this->appSession);
    //         $ws->sendMessage($data);
    //         $status = new \Status($this->appSession);

    //         $status->doStatus($sale_id, "candidates", "",  $this->session_company_id, $this->session_user_id, $this->session_company_id);

    //         $message = [
    //             'status' => true,
    //             'message' => "Send notification thành công."
    //         ];

    //         return $this->appSession->getTier()->response($message, $response);
    //     }
}
