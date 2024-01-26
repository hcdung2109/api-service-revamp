<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class SettingsGlobalController extends Controller
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

    // START SETTINGS SKILL
    public function get_skills(Request $request, Response $response)
    {
        $param = $request->query->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.name FROM skills d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

        if ($ps != "") {
            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

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
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['skills' => $data],
            'message' => "Lấy danh sách skills thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_skills(Request $request, Response $response)
    {
        $data = $request->request->all();

        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'name' => $name,
        ], [
            'name' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("skills");
            $vendor_id = $this->appSession->getTool()->getId();
            $builder->add("id", $vendor_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("name", $name);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Tạo skills thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_skills($id, Request $request, Response $response)
    {

        $data = $request->request->all();
        $name = $data['name'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá 256 ký tự.',
        ]);

        $validation = $validator->make([
            'name' => $name,
        ], [
            'name' => 'required|max:256'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM skills d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Skills không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->createBuilder("skills");
                $builder->add("id", $id);
                $builder->add("name", $name);

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật skills thành công."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_skills_by_id($id, Response $response)
    {

        $sql = "SELECT d1.id FROM skills d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Skill không tồn tại."
            ];
        } else {
            $sql = "SELECT d1.id, d1.name FROM skills d1 WHERE d1.id='" . $id . "'";
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
                'data' => ['skill' => $data],
                'message' => "Lấy skill by id thành công."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_skills($id, Response $response)
    {

        $sql = "SELECT d1.id FROM skills d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Skill không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("skills");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa skill thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
    // END SETTINGS SKILL

    // // START SETTINGS COUNTRY
    // public function get_countrys(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'];

    //     $sql = "SELECT d1.id, d1.name FROM countrys d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         //$arr['code'] = $result[$i][2];
    //         //$arr['phone_code'] = $result[$i][3];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['countrys' => $data],
    //         'message' => "Lấy danh sách country thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function create_countrys(Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     //$code = $data['code'];
    //     $name = $data['name'];
    //     //$phone_code = $data['phone_code'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         //'code' => $code,
    //         'name' => $name,
    //         //'phone_code' => $phone_code,
    //     ], [
    //         //'code' => 'required|max:256',
    //         'name' => 'required|max:256',
    //         //'phone_code' => 'required|max:256'
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("countrys");
    //         $vendor_id = $this->appSession->getTool()->getId();
    //         $builder->add("id", $vendor_id);
    //         $builder->add("create_uid", $this->session_user_id);
    //         $builder->add("write_uid", $this->session_user_id);
    //         $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("status", 0);
    //         $builder->add("company_id", $this->session_company_id);
    //         //$builder->add("code", str_replace("'", "''", $code));
    //         $builder->add("name", str_replace("'", "''", $name));
    //         // $builder->add(
    //         //     "phone_code",
    //         //     str_replace("'", "''", $phone_code)
    //         // );
    //         $sql = $this->appSession->getTier()->getInsert($builder);
    //         $this->msg->add("query", $sql);
    //         $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Tạo countrys thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function update_countrys($id, Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     // $code = $data['code'];
    //     $name = $data['name'];
    //     //$phone_code = $data['phone_code'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         //'code' => $code,
    //         'name' => $name,
    //         //'phone_code' => $phone_code,
    //     ], [
    //         //'code' => 'max:256',
    //         'name' => 'max:256',
    //         //'phone_code' => 'max:256'
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id FROM countrys d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //         $this->msg->add("query", $sql);
    //         $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //         if ($seen_id == "") {
    //             $message = [
    //                 'status' => true,
    //                 'message' => "Countrys không tồn tại."
    //             ];
    //         } else {
    //             $builder = $this->appSession->getTier()->createBuilder("countrys");
    //             $builder->update("id", $id);
    //             //$builder->update("code", str_replace("'", "''", $code));
    //             $builder->update("name", str_replace("'", "''", $name));
    //             //$builder->update("phone_code", str_replace("'", "''", $phone_code));

    //             $sql = $this->appSession->getTier()->getUpdate($builder);
    //             $this->msg->add("query", $sql);
    //             $result = $this->appSession->getTier()->exec($this->msg);

    //             $message = [
    //                 'status' => true,
    //                 'message' => "Cập nhật countrys thành công."
    //             ];
    //         }
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_countrys_by_id($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM countrys d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Country không tồn tại."
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id, d1.name FROM countrys d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //         $this->msg->add("query", $sql);
    //         $result =  $this->appSession->getTier()->getTable($this->msg);
    //         $numrows = $result->getRowCount();

    //         $data = array();
    //         if ($numrows > 0) {
    //             $row = $result->getRow(0);
    //             $data['id'] =   $row->getString("id");
    //             //$data['code'] = $row->getString("code");
    //             $data['name'] = $row->getString("name");
    //             //$data['phone_code'] = $row->getString("phone_code");
    //         }

    //         $message = [
    //             'status' => true,
    //             'data' => ['country' => $data],
    //             'message' => "Lấy country by id thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function delete_countrys($id, Response $response)
    // {

    //     $sql = "SELECT d1.id FROM countrys d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Country không tồn tại."
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("countrys");
    //         $builder->update("id", $id);
    //         $builder->update("status", 1);

    //         $sql = $this->appSession->getTier()->getUpdate($builder);
    //         $this->msg->add("query", $sql);
    //         $result = $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Xóa country thành công."
    //         ];
    //     }
    //     return $this->appSession->getTier()->response($message, $response);
    // }
    // // END SETTINGS COUNTRY

    // // START SETTINGS CITIS
    // public function get_citis(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id ";
    //     $sql = $sql . " FROM citis d1";
    //     $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['country_id'] = $result[$i][3];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['citys' => $data],
    //         'message' => "Lấy danh sách citys thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function create_citis(Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         'country_id' => 'required'
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("citis");
    //         $vendor_id = $this->appSession->getTool()->getId();
    //         $builder->add("id", $vendor_id);
    //         $builder->add("create_uid", $this->session_user_id);
    //         $builder->add("write_uid", $this->session_user_id);
    //         $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("status", 0);
    //         $builder->add("company_id", $this->session_company_id);
    //         $builder->add("name", str_replace("'", "''", $name));
    //         $builder->add("country_id", str_replace("'", "''", $country_id));

    //         $sql = $this->appSession->getTier()->getInsert($builder);
    //         $this->msg->add("query", $sql);
    //         $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Tạo city thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function update_citis($id, Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         'country_id' => 'required'
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id FROM citis d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //         $this->msg->add("query", $sql);
    //         $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //         if ($seen_id == "") {
    //             $message = [
    //                 'status' => true,
    //                 'message' => "Citis không tồn tại."
    //             ];
    //         } else {
    //             $builder = $this->appSession->getTier()->createBuilder("citis");
    //             $builder->update("id", $id);
    //             $builder->update("name", str_replace("'", "''", $name));
    //             $builder->update("country_id", str_replace("'", "''", $country_id));

    //             $sql = $this->appSession->getTier()->getUpdate($builder);
    //             $this->msg->add("query", $sql);
    //             $result = $this->appSession->getTier()->exec($this->msg);

    //             $message = [
    //                 'status' => true,
    //                 'message' => "Cập nhật city thành công."
    //             ];
    //         }
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_citis_by_id($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM citis d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Citis không tồn tại."
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id ";
    //         $sql = $sql . " FROM citis d1";
    //         $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //         $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //         $this->msg->add("query", $sql);
    //         $result =  $this->appSession->getTier()->getTable($this->msg);
    //         $numrows = $result->getRowCount();

    //         $data = array();
    //         if ($numrows > 0) {
    //             $row = $result->getRow(0);
    //             $data['id'] =   $row->getString("id");
    //             $data['name'] = $row->getString("name");
    //             $data['country_name'] = $row->getString("country_name");
    //             $data['country_id'] = $row->getString("country_id");
    //         }

    //         $message = [
    //             'status' => true,
    //             'data' => ['city' => $data],
    //             'message' => "Lấy city by id thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function delete_citis($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM citis d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "City không tồn tại."
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("citis");
    //         $builder->update("id", $id);
    //         $builder->update("status", 1);

    //         $sql = $this->appSession->getTier()->getUpdate($builder);
    //         $this->msg->add("query", $sql);
    //         $result = $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Xóa city thành công."
    //         ];
    //     }
    //     return $this->appSession->getTier()->response($message, $response);
    // }
    // // END SETTINGS CITIS

    // // START SETTINGS DISTRICTS
    // public function get_districts(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id,";
    //     $sql = $sql . " d3.id AS city_id, d3.name AS city_name";
    //     $sql = $sql . " FROM districts d1";
    //     $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //     $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_id'] = $result[$i][3];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['city_id'] = $result[$i][4];
    //         $arr['city_name'] = $result[$i][5];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['districts' => $data],
    //         'message' => "Lấy danh sách districts thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function create_districts(Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];
    //     $city_id = $data['city_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //         'city_id' => $city_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         'country_id' => 'required',
    //         'city_id' => 'required',
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("districts");
    //         $vendor_id = $this->appSession->getTool()->getId();
    //         $builder->add("id", $vendor_id);
    //         $builder->add("create_uid", $this->session_user_id);
    //         $builder->add("write_uid", $this->session_user_id);
    //         $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("status", 0);
    //         $builder->add("company_id", $this->session_company_id);
    //         $builder->add("name", str_replace("'", "''", $name));
    //         $builder->add("country_id", str_replace("'", "''", $country_id));
    //         $builder->add("city_id", str_replace("'", "''", $city_id));

    //         $sql = $this->appSession->getTier()->getInsert($builder);
    //         $this->msg->add("query", $sql);
    //         $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Tạo district thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function update_districts($id, Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];
    //     $city_id = $data['city_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //         'city_id' => $city_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         //'country_id' => 'required',
    //         //'city_id' => 'required',
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id FROM districts d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //         $this->msg->add("query", $sql);
    //         $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //         if ($seen_id == "") {
    //             $message = [
    //                 'status' => true,
    //                 'message' => "District không tồn tại."
    //             ];
    //         } else {
    //             $builder = $this->appSession->getTier()->createBuilder("districts");
    //             $builder->update("id", $id);
    //             $builder->update("name", str_replace("'", "''", $name));
    //             $builder->update("country_id", str_replace("'", "''", $country_id));
    //             $builder->update("city_id", str_replace("'", "''", $city_id));

    //             $sql = $this->appSession->getTier()->getUpdate($builder);
    //             $this->msg->add("query", $sql);
    //             $result = $this->appSession->getTier()->exec($this->msg);

    //             $message = [
    //                 'status' => true,
    //                 'message' => "Cập nhật district thành công."
    //             ];
    //         }
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_districts_by_id($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM districts d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "District không tồn tại."
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id,";
    //         $sql = $sql . " d3.id AS city_id, d3.name AS city_name";
    //         $sql = $sql . " FROM districts d1";
    //         $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //         $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //         $sql = $sql . " WHERE d1.status=0 AND d1.id='" . $id . "'";

    //         $this->msg->add("query", $sql);
    //         $result =  $this->appSession->getTier()->getTable($this->msg);
    //         $numrows = $result->getRowCount();

    //         $data = array();
    //         if ($numrows > 0) {
    //             $row = $result->getRow(0);
    //             $data['id'] =   $row->getString("id");
    //             $data['name'] = $row->getString("name");
    //             $data['country_id'] = $row->getString("country_id");
    //             $data['country_name'] = $row->getString("country_name");
    //             $data['city_id'] = $row->getString("city_id");
    //             $data['city_name'] = $row->getString("city_name");
    //         }

    //         $message = [
    //             'status' => true,
    //             'data' => ['district' => $data],
    //             'message' => "Lấy district by id thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function delete_districts($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM districts d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "District không tồn tại."
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("districts");
    //         $builder->update("id", $id);
    //         $builder->update("status", 1);

    //         $sql = $this->appSession->getTier()->getUpdate($builder);
    //         $this->msg->add("query", $sql);
    //         $result = $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Xóa city thành công."
    //         ];
    //     }
    //     return $this->appSession->getTier()->response($message, $response);
    // }
    // // END SETTINGS DISTRICTS

    // // START SETTINGS WARDS
    // public function get_wards(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id,";
    //     $sql = $sql . " d3.id AS city_id, d3.name AS city_name, d4.id AS district_id, d4.name AS district_name";
    //     $sql = $sql . " FROM wards d1";
    //     $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //     $sql = $sql . " LEFT OUTER JOIN districts d4 ON(d1.district_id = d4.id)";
    //     $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_id'] = $result[$i][3];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['city_id'] = $result[$i][4];
    //         $arr['city_name'] = $result[$i][5];
    //         $arr['district_id'] = $result[$i][6];
    //         $arr['district_name'] = $result[$i][7];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['wards' => $data],
    //         'message' => "Lấy danh sách wards thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function create_wards(Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];
    //     $city_id = $data['city_id'];
    //     $district_id = $data['district_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //         'city_id' => $city_id,
    //         'district_id' => $district_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         'country_id' => 'required',
    //         'city_id' => 'required',
    //         'district_id' => 'required',
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("wards");
    //         $vendor_id = $this->appSession->getTool()->getId();
    //         $builder->add("id", $vendor_id);
    //         $builder->add("create_uid", $this->session_user_id);
    //         $builder->add("write_uid", $this->session_user_id);
    //         $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("status", 0);
    //         $builder->add("company_id", $this->session_company_id);
    //         $builder->add("name", str_replace("'", "''", $name));
    //         $builder->add("country_id", str_replace("'", "''", $country_id));
    //         $builder->add("city_id", str_replace("'", "''", $city_id));
    //         $builder->add("district_id", str_replace("'", "''", $district_id));

    //         $sql = $this->appSession->getTier()->getInsert($builder);
    //         $this->msg->add("query", $sql);
    //         $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Tạo ward thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function update_wards($id, Request $request, Response $response)
    // {
    //     $data = $request->request->all();

    //     $name = $data['name'];
    //     $country_id = $data['country_id'];
    //     $city_id = $data['city_id'];
    //     $district_id = $data['district_id'];

    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá 256 ký tự.',
    //     ]);

    //     $validation = $validator->make([
    //         'name' => $name,
    //         'country_id' => $country_id,
    //         'city_id' => $city_id,
    //         'district_id' => $district_id,
    //     ], [
    //         'name' => 'required|max:256',
    //         //'country_id' => 'required',
    //         //'city_id' => 'required',
    //         //'district_id' => 'required',
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id FROM wards d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //         $this->msg->add("query", $sql);
    //         $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //         if ($seen_id == "") {
    //             $message = [
    //                 'status' => true,
    //                 'message' => "Wards không tồn tại."
    //             ];
    //         } else {
    //             $builder = $this->appSession->getTier()->createBuilder("wards");
    //             $builder->update("id", $id);
    //             $builder->update("name", str_replace("'", "''", $name));
    //             $builder->update("country_id", str_replace("'", "''", $country_id));
    //             $builder->update("city_id", str_replace("'", "''", $city_id));
    //             $builder->update("district_id", str_replace("'", "''", $district_id));

    //             $sql = $this->appSession->getTier()->getUpdate($builder);
    //             $this->msg->add("query", $sql);
    //             $result = $this->appSession->getTier()->exec($this->msg);

    //             $message = [
    //                 'status' => true,
    //                 'message' => "Cập nhật ward thành công."
    //             ];
    //         }
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_wards_by_id($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM wards d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Wards không tồn tại."
    //         ];
    //     } else {
    //         $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.code AS country_id,";
    //         $sql = $sql . " d3.id AS city_id, d3.name AS city_name, d4.id AS district_id, d4.name AS district_name";
    //         $sql = $sql . " FROM wards d1";
    //         $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //         $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //         $sql = $sql . " LEFT OUTER JOIN districts d4 ON(d1.district_id = d4.id)";
    //         $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";

    //         $this->msg->add("query", $sql);
    //         $result =  $this->appSession->getTier()->getTable($this->msg);
    //         $numrows = $result->getRowCount();

    //         $data = array();
    //         if ($numrows > 0) {
    //             $row = $result->getRow(0);
    //             $data['id'] =   $row->getString("id");
    //             $data['name'] = $row->getString("name");
    //             $data['country_id'] = $row->getString("country_id");
    //             $data['country_name'] = $row->getString("country_name");
    //             $data['city_id'] = $row->getString("city_id");
    //             $data['city_name'] = $row->getString("city_name");
    //             $data['district_id'] = $row->getString("district_id");
    //             $data['district_name'] = $row->getString("district_name");
    //         }

    //         $message = [
    //             'status' => true,
    //             'data' => ['ward' => $data],
    //             'message' => "Lấy ward by id thành công."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function delete_wards($id, Response $response)
    // {
    //     $sql = "SELECT d1.id FROM wards d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Ward không tồn tại."
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("wards");
    //         $builder->update("id", $id);
    //         $builder->update("status", 1);

    //         $sql = $this->appSession->getTier()->getUpdate($builder);
    //         $this->msg->add("query", $sql);
    //         $result = $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Xóa ward thành công."
    //         ];
    //     }
    //     return $this->appSession->getTier()->response($message, $response);
    // }
    // // END SETTINGS WARDS

    // // START SETTINGS FLITER

    // public function get_filter_by_city(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $country_id =  $param['country_id'];

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id ";
    //     $sql = $sql . " FROM res_address d1";
    //     $sql = $sql . " LEFT OUTER JOIN res_country d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " WHERE d1.status=0";
    //     $sql = $sql . " AND d1.country_id='" . $country_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['country_id'] = $result[$i][3];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['citys' => $data],
    //         'message' => "Lấy danh sách fliter by citys thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_filter_by_district(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $city_id =  $param['city_id'];

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id,";
    //     $sql = $sql . " d3.id AS city_id, d3.name AS city_name";
    //     $sql = $sql . " FROM districts d1";
    //     $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //     $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";
    //     $sql = $sql . " AND d1.city_id='" . $city_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_id'] = $result[$i][3];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['city_id'] = $result[$i][4];
    //         $arr['city_name'] = $result[$i][5];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['districts' => $data],
    //         'message' => "Lấy danh sách fliter by districts thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function get_filter_by_ward(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $p =  $param['p'] ?? 0;
    //     $ps =  $param['ps'] ?? 10;

    //     $district_id = $param['district_id'];

    //     $sql = "SELECT d1.id, d1.name, d2.name AS country_name, d2.id AS country_id,";
    //     $sql = $sql . " d3.id AS city_id, d3.name AS city_name, d4.id AS district_id, d4.name AS district_name";
    //     $sql = $sql . " FROM wards d1";
    //     $sql = $sql . " LEFT OUTER JOIN countrys d2 ON(d1.country_id = d2.id)";
    //     $sql = $sql . " LEFT OUTER JOIN citis d3 ON(d1.city_id = d3.id)";
    //     $sql = $sql . " LEFT OUTER JOIN districts d4 ON(d1.district_id = d4.id)";
    //     $sql = $sql . " WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";
    //     $sql = $sql . " AND d1.district_id='" . $district_id . "'";

    //     $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.name ASC");

    //     $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

    //     $this->msg->add("query", $arrResult->sql);

    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();
    //         $arr['id'] = $result[$i][0];
    //         $arr['name'] = $result[$i][1];
    //         $arr['country_id'] = $result[$i][3];
    //         $arr['country_name'] = $result[$i][2];
    //         $arr['city_id'] = $result[$i][4];
    //         $arr['city_name'] = $result[$i][5];
    //         $arr['district_id'] = $result[$i][6];
    //         $arr['district_name'] = $result[$i][7];
    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'total' => $arrResult->total,
    //         'per_page' => $arrResult->per_page,
    //         'current_page' => $arrResult->current_page,
    //         'from' => $arrResult->from,
    //         'to' => $arrResult->to,
    //         'data' => ['wards' => $data],
    //         'message' => "Lấy danh sách fliter by wards thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // // END SETTINGS FLITER
}
