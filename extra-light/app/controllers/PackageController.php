<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;

class PackageController extends Controller
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

    // START VENDOR
    public function get_packages(Request $request, Response $response)
    {

        $sql = "SELECT d1.id, d1.name, d1.description, d1.monthly_price, d1.annual_price, d1.is_trial, d1.trial_duration, d1.price";
        $sql = $sql . " FROM packages d1";
        $sql = $sql . " WHERE d1.company_id='ROOT' AND d1.status=0";

        $this->msg->add("query", $sql);
        $result = $this->appSession->getTier()->getArray($this->msg);

        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();


            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $arr['description'] = $result[$i][2];
            $arr['monthly_price'] = $result[$i][3];
            $arr['annual_price'] = $result[$i][4];
            $arr['is_trial'] = $result[$i][5];
            $arr['trial_duration'] = $result[$i][6];
            $arr['price'] = $result[$i][7];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['packages' => $data],
            'message' => "Lấy danh sách vendors thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }
}
