<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;

class SettingsLocationsController extends Controller
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

    // START SETTINGS EXPECT LOCATION
    public function get_citis(Request $request, Response $response)
    {
        $param = $request->query->all();

        $country_id =  $param['country_id'];

        $sql = "SELECT d1.id, d1.name";
        $sql = $sql . " FROM res_address d1";
        $sql = $sql . " LEFT OUTER JOIN res_country d2 ON(d1.country_id = d2.id)";
        $sql = $sql . " WHERE d1.status=0";
        $sql = $sql . " AND d1.type='CITY'";
        if ($country_id != "") {
            $sql = $sql . " AND d1.country_id='" . $country_id . "'";
        }

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['cities' => $data],
            'message' => "Lấy danh sách citis thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS EXPECT LOCATION

    // START SETTINGS NATIONALS
    public function get_countrys(Response $response)
    {
        $sql = "SELECT d1.id, d1.name FROM res_country d1 WHERE d1.status=0 AND d1.active='1'";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['countrys' => $data],
            'message' => "Lấy danh sách countrys thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS NATIONALS

    // START SETTINGS DISTRICTS
    public function get_districts($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.name";
        $sql = $sql . " FROM res_address d1";
        $sql = $sql . " WHERE d1.status =0 AND (d1.parent_id='" . $id . "' AND d1.type='DIST')";
        $sql = $sql . " ORDER BY d1.name ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['districts' => $data],
            'message' => "Lấy danh sách districts thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS DISTRICTS

    // START SETTINGS WARDS
    public function get_wards($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.name";
        $sql = $sql . " FROM res_address d1";
        $sql = $sql . " WHERE d1.status =0 AND (d1.parent_id='" . $id . "' AND d1.type='WARD')";
        $sql = $sql . " ORDER BY d1.name ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['wards' => $data],
            'message' => "Lấy danh sách wards thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS WARDS
}
