<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class VendorController extends Controller
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
    public function get_vendor(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];
        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $location_id = $param['location_id'];
        $handling_by = $param['handling_by'];
        $in_vendor_types = $param['in_vendor_types'];
        $followed = $param['followed'] ?? "0";
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.vendor_type_id, d1.name, d1.source_id, d1.representative, d1.phone, d1.email";
        $sql = $sql . ", d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary";
        $sql = $sql . ", d2.name AS vendor_type_name, d3.name AS source_name, d4.name AS city_name";
        $sql = $sql . ", d5.name AS district_name, d6.name AS ward_name, d7.id AS logo_id";
        $sql = $sql . ", d8.followed_id";
        $sql = $sql . " FROM vendors d1";
        $sql = $sql . " LEFT OUTER JOIN vendor_types d2 ON(d1.vendor_type_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d3 ON(d1.source_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d4 ON(d1.city_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.district_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.ward_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN vendor_followed_vendors d8 ON(d1.id = d8.vendor_id AND d8.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($followed == "1") {
            $followed_id = $this->find_vendor_followed_id();
            $sql = $sql . " AND d8.status=0 AND d8.followed_id='" . $followed_id . "'";
        }

        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.name", "d1.phone", "d1.email"], $search) . ")";
        }

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($location_id != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.city_id", $location_id) . ")";
        }

        if ($handling_by != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.create_uid", $handling_by) . ")";
        }

        if ($in_vendor_types != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.vendor_type_id", $in_vendor_types) . ")";
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
            $arr_vendor_type = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_user_followed = array();

            $arr['id'] = $result[$i][0];
            $arr['vendor_type_id'] = $result[$i][1];
            $arr['name'] = $result[$i][2];
            $arr['source_id'] = $result[$i][3];
            $arr['representative'] = $result[$i][4];
            $arr['phone'] = $result[$i][5];
            $arr['email'] = $result[$i][6];
            $arr['city_id'] = $result[$i][7];
            $arr['district_id'] = $result[$i][8];
            $arr['ward_id'] = $result[$i][9];
            $arr['address'] = $result[$i][10];
            $arr['summary'] = $result[$i][11];

            $arr_vendor_type['id'] = $result[$i][1];
            $arr_vendor_type['name'] = $result[$i][12];
            $arr['vendor_type'] = $arr_vendor_type;

            $arr_source['id'] = $result[$i][3];
            $arr_source['name'] = $result[$i][13];
            $arr['source'] = $arr_source;

            $arr_city['id'] = $result[$i][7];
            $arr_city['name'] = $result[$i][14];
            $arr['city'] = $arr_city;

            $arr_district['id'] = $result[$i][8];
            $arr_district['name'] = $result[$i][15];
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $result[$i][9];
            $arr_ward['name'] = $result[$i][16];
            $arr['ward'] = $arr_ward;

            $arr['logo_id'] = $result[$i][17];
            $arr['followed_id'] = $result[$i][18];

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM vendor_followed_vendors d1";
            $sql = $sql . " LEFT OUTER JOIN vendor_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.vendor_id='" . $arr['id'] . "' AND d1.status=0";
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
            'data' => ['vendors' => $data],
            'message' => "Lấy danh sách vendors thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_vendor(Request $request, Response $response)
    {
        $data = $request->request->all();
        $file = $request->files->all();
        $id = $this->appSession->getTool()->getId();

        $vendor_type_id = $data['vendor_type_id'];
        $name = $data['name'];
        $source_id = $data['source_id'];
        $representative = $data['representative'];
        $phone = $data['phone'];
        $email = $data['email'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $summary = $data['summary'];

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
            'vendor_type_id' => 'required',
            'name' => 'required|max:1000',
            'source_id' => 'required',
            'representative' => 'required|max:256',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required|max:256',
            //'summary' => 'max:1000',
            'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.email, d1.phone FROM vendors d1 WHERE d1.status=0 AND (d1.email='" . $email . "' OR d1.phone='" . $phone . "') AND d1.company_id='" . $this->session_company_id . "'";

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
                $builder = $this->appSession->getTier()->createBuilder("vendors");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("vendor_type_id", str_replace("'", "''", $vendor_type_id));
                $builder->add("name", str_replace("'", "''", $name));
                $builder->add("source_id", str_replace("'", "''", $source_id));
                $builder->add("representative", str_replace("'", "''", $representative));
                $builder->add("phone", str_replace("'", "''", $phone));
                $builder->add("email", str_replace("'", "''", $email));
                $builder->add("city_id", str_replace("'", "''", $city_id));
                $builder->add("district_id", str_replace("'", "''", $district_id));
                $builder->add("ward_id", str_replace("'", "''", $ward_id));
                $builder->add("address", str_replace("'", "''", $address));
                $builder->add("summary", str_replace("'", "''", $summary));
                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $logo_id = $this->create_document_vendor($request, $id);

                if ($result == '1') {
                    $data = $this->get_vendor_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo vendor thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_vendor($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $vendor_type_id = $data['vendor_type_id'];
        $name = $data['name'];
        $source_id = $data['source_id'];
        $representative = $data['representative'];
        $phone = $data['phone'];
        $email = $data['email'];
        $city_id = $data['city_id'];
        $district_id = $data['district_id'];
        $ward_id = $data['ward_id'];
        $address = $data['address'];
        $summary = $data['summary'];

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
            //'vendor_type_id' => 'required',
            'name' => 'max:1000',
            //'source_id' => 'required',
            'representative' => 'max:256',
            'phone' => 'numeric',
            'email' => 'email',
            //'city_id' => 'required',
            //'district_id' => 'required',
            //'ward_id' => 'required',
            'address' => 'max:256',
            //'summary' => 'max:1000',
            'logo' => 'uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Vendors không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("vendors");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("vendor_type_id", str_replace("'", "''", $vendor_type_id));
                $builder->update("name", str_replace("'", "''", $name));
                $builder->update("source_id", str_replace("'", "''", $source_id));
                $builder->update("representative", str_replace("'", "''", $representative));
                $builder->update("phone", str_replace("'", "''", $phone));
                $builder->update("email", str_replace("'", "''", $email));
                $builder->update("city_id", str_replace("'", "''", $city_id));
                $builder->update("district_id", str_replace("'", "''", $district_id));
                $builder->update("ward_id", str_replace("'", "''", $ward_id));
                $builder->update("address", str_replace("'", "''", $address));
                $builder->update("summary", str_replace("'", "''", $summary));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                $logo_id = $this->update_document($request, $id);
                if ($result == '1') {
                    $data = $this->get_vendor_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật vendor thất bại."
                    ];
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_vendor_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.vendor_type_id, d1.name, d1.source_id, d1.representative, d1.phone, d1.email";
        $sql = $sql . ", d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary";
        $sql = $sql . ", d2.name AS vendor_type_name, d3.name AS source_name, d4.name AS city_name";
        $sql = $sql . ", d5.name AS district_name, d6.name AS ward_name, d7.id AS logo_id";
        $sql = $sql . ", d8.followed_id";
        $sql = $sql . " FROM vendors d1";
        $sql = $sql . " LEFT OUTER JOIN vendor_types d2 ON(d1.vendor_type_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d3 ON(d1.source_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d4 ON(d1.city_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.district_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.ward_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN vendor_followed_vendors d8 ON(d1.id = d8.vendor_id AND d8.status=0)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();
            $arr_vendor_type = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_user_followed = array();

            $arr['id'] = $row->getString("id");
            $arr['vendor_type_id'] = $row->getString("vendor_type_id");
            $arr['name'] = $row->getString("name");
            $arr['source_id'] = $row->getString("source_id");
            $arr['representative'] = $row->getString("representative");
            $arr['phone'] = $row->getString("phone");
            $arr['email'] = $row->getString("email");
            $arr['city_id'] = $row->getString("city_id");
            $arr['district_id'] = $row->getString("district_id");
            $arr['ward_id'] = $row->getString("ward_id");
            $arr['address'] = $row->getString("address");
            $arr['summary'] = $row->getString("summary");

            $arr_vendor_type['id'] = $row->getString("vendor_type_id");
            $arr_vendor_type['name'] = $row->getString("vendor_type_name");
            $arr['vendor_types'] = $arr_vendor_type;

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

            $arr['logo_id'] = $row->getString("logo_id");

            $arr['followed_id'] = $row->getString("followed_id");

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM vendor_followed_vendors d1";
            $sql = $sql . " LEFT OUTER JOIN vendor_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.vendor_id='" . $arr['id'] . "' AND d1.status=0";
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

            $message = [
                'status' => true,
                'data' => ['vendor' => $arr],
                'message' => "Lấy danh sách vendor by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Vendor không tồn tại."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_vendor($id, Response $response)
    {
        $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("vendors");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa vendor thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_vendor(Request $request, $rel_id)
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
    // END VENDOR

    // START CANDIDATE NOTES

    public function get_vendor_notes(Request $request, Response $response)
    {
        $data = $request->query->all();

        $rel_id = $data['rel_id'];

        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.id AS user_id, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN vendors d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.notesable_id='" . $rel_id . "'";
        $sql = $sql . " AND d1.notesable_type='vendor'";
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
            'data' => ['vendor_notes' => $data],
            'message' => "Lấy danh sách vendor note thành công."
        ];

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function create_vendor_notes(Request $request, Response $response)
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
            $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status=0 AND d1.id='" . $rel_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Vendor của bạn không tồn tại."
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
                $builder->add("notesable_type", 'vendor');
                $builder->add("notesable_id", str_replace("'", "''", $rel_id));
                $builder->add("contents", str_replace("'", "''", $content));

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Tạo vendor note thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo vendor note thất bại"
                    ];
                }
            }
        }

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function update_vendor_notes($id, Request $request, Response $response)
    {
        $data = $request->request->all();

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
            $sql = "SELECT d1.id FROM notes d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Note không tồn tại."
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
                        'message' => "Cập nhật vendor note thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật vendor note thất bại"
                    ];
                }
            }
        }

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function get_vendor_notes_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.notesable_type, d1.notesable_id, d1.contents, d3.user_name, d3.name, d1.create_date FROM notes d1";
        $sql = $sql . " LEFT OUTER JOIN vendors d2 ON(d1.notesable_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d1.create_uid = d3.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";
        $sql = $sql . " AND d1.notesable_type='vendor'";
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
                'data' => ['vendor_note' => $arr],
                'message' => "Lấy danh sách vendor note by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Vendor không tồn tại."
            ];
        }

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function delete_vendor_notes($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM notes d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor note không tồn tại."
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
                'message' => "Xóa vendor note thành công."
            ];
        }
        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    // START VENDOR FOLLOWED

    public function add_vendor_followed(Request $request, Response $response)
    {
        $data = $request->request->all();
        $vendor_id = $data['vendor_id'];

        $followed_id = $this->find_vendor_followed_id();

        $sql = "SELECT d1.id FROM vendor_followed_vendors d1 WHERE d1.status=0 AND d1.followed_id='" . $followed_id . "' AND d1.vendor_id='" . $vendor_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $followed_company_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_company_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("vendor_followed_vendors");
            $followed_company_id = $this->appSession->getTool()->getId();
            $builder->add("id", $followed_company_id);
            $builder->add("followed_id", $followed_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("vendor_id", $vendor_id);
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $sql = $this->appSession->getTier()->getInsert($builder);

            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);
        }

        $message = [
            'status' => true,
            'message' => "Tạo vendor followed thành công."
        ];
        return $this->appSession->getTier()->response($message, $response);
    }
    public function find_vendor_followed_id()
    {
        $user_id = $this->session_user_id;

        $sql = "SELECT d1.id FROM vendor_followed d1 WHERE d1.status = 0 AND d1.user_id='" . $user_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $followed_id = $this->appSession->getTier()->getValue($this->msg);

        if ($followed_id == "") {
            $builder = $this->appSession->getTier()->createBuilder("vendor_followed");
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
    public function remove_vendor_followed($id, Request $request, Response $response)
    {
        $followed_id = $this->find_vendor_followed_id();

        $sql = "SELECT d1.id FROM vendor_followed_vendors d1 WHERE d1.status = 0 AND (d1.vendor_id='" . $id . "' AND d1.followed_id='" . $followed_id . "' AND d1.company_id='" . $this->session_company_id . "')";
        $this->msg->add("query", $sql);

        $followed_vendor_id = $this->appSession->getTier()->getValue($this->msg);
        if ($followed_vendor_id == "") {
            $message = [
                'status' => true,
                'message' => "Vendor followed không tồn tại."
            ];
        } else {
            $sql = "UPDATE vendor_followed_vendors SET status='1', write_date=NOW() WHERE id='" . $followed_vendor_id . "'";
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa vendor followed thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // START VENDOR SOFT COLUMNS

    public function vendor_soft_column(Response $response)
    {
        $soft_column = [
            "name" => "Vendor name",
            "phone" => "Phone",
            "email" => "Email",
        ];

        $message = [
            'status' => true,
            'data' => ['vendor_column_soft' => $soft_column],
            'message' => "Lấy danh sách column soft thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }
    // START JOB SOFT BY

    public function vendor_soft_by(Response $response)
    {
        $soft_by = [
            "ASC" => "ASC",
            "DESC" => "DESC",
        ];

        $message = [
            'status' => true,
            'data' => ['vendor_soft_by' => $soft_by],
            'message' => "Lấy danh sách soft by thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_job_opening(Request $request, Response $response)
    {
        $data = $request->query->all();
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 20;
        $vendor_id = $data['vendor_id'];

        $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status=0 AND d1.id='" . $vendor_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => false,
                'message' => "Vendor của bạn không tồn tại."
            ];
        } else {

            $sql = "SELECT d1.id, d1.title, d11.total_candidate, d12.id AS vendor_id";
            $sql = $sql . " FROM jobs d1";
            $sql = $sql . " LEFT OUTER JOIN job_commissions d2 ON(d1.id = d2.rel_id)";
            $sql = $sql . " LEFT OUTER JOIN departments d3 ON(d1.department_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN job_types d4 ON(d1.job_type_id = d4.id)";
            $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.expect_location_id = d5.id)";
            $sql = $sql . " LEFT OUTER JOIN job_categories d6 ON(d1.job_categories_id = d6.id)";
            $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
            $sql = $sql . " LEFT OUTER JOIN vendor_followed_vendors d8 ON(d2.vendor_id = d8.vendor_id)";
            $sql = $sql . " LEFT OUTER JOIN (SELECT d9.job_id, COUNT(d10.candidate_id) AS total_candidate FROM job_stages d9 LEFT OUTER JOIN job_candidates d10 ON(d9.id = d10.job_stage_id) GROUP BY d9.job_id) d11 ON(d1.id = d11.job_id)";
            $sql = $sql . " LEFT OUTER JOIN vendors d12 ON(d2.vendor_id = d12.id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0 AND d12.id='" . $vendor_id . "'";

            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date DESC");

            $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);
            $this->msg->add("query", $arrResult->sql);

            $result = $this->appSession->getTier()->getArray($this->msg);

            $data = array();
            for ($i = 0; $i < count($result); $i++) {
                $arr = array();
                $arr_user_followed = array();

                $arr['id'] = $result[$i][0];
                $arr['title'] = $result[$i][1];
                $arr['total_candidate'] = intval($result[$i][2]);
                $arr['vendor_id'] = $result[$i][3];

                $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM vendor_followed_vendors d1";
                $sql = $sql . " LEFT OUTER JOIN vendor_followed d2 ON(d1.followed_id = d2.id)";
                $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
                $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
                $sql = $sql . " WHERE d1.vendor_id='" . $arr['vendor_id'] . "' AND d1.status=0";
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
                'data' => ['jobs' => $data],
                'message' => "Lấy danh sách job opening thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_vendor_commission(Request $request, Response $response)
    {
        $data = $request->query->all();
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 20;
        $vendor_id = $data['vendor_id'];

        $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status=0 AND d1.id='" . $vendor_id . "' AND d1.company_id='" . $this->session_company_id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => false,
                'message' => "Vendor của bạn không tồn tại."
            ];
        } else {

            $sql = "SELECT d1.id, d3.company_name, d2.title AS job_title, d1.value, d6.amount, d5.name AS kpi_name";
            $sql = $sql . " FROM job_commissions d1";
            $sql = $sql . " LEFT OUTER JOIN jobs d2 ON(d2.id = d1.rel_id)";
            $sql = $sql . " LEFT OUTER JOIN companies d3 ON(d2.companies_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN vendor_commission_types d4 ON(d1.commission_type_id = d4.id)";
            $sql = $sql . " LEFT OUTER JOIN vendor_commission_kpis d5 ON(d1.commission_kpi_type_id = d5.id)";
            $sql = $sql . " LEFT OUTER JOIN invoices d6 ON(d3.id = d6.companies_id)";
            $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0 AND d1.vendor_id='" . $vendor_id . "'";

            $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1.create_date DESC");

            $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);
            $this->msg->add("query", $arrResult->sql);

            $result = $this->appSession->getTier()->getArray($this->msg);

            $data = array();
            for ($i = 0; $i < count($result); $i++) {
                $arr = array();
                $arr_user_followed = array();

                $arr['id'] = $result[$i][0];
                $arr['company_name'] = $result[$i][1];
                $arr['job_title'] = $result[$i][2];
                $arr['value'] = intval($result[$i][3]);
                $arr['amount'] = intval($result[$i][4]);
                $arr['kpi_name'] = $result[$i][5];
                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'total' => $arrResult->total,
                'per_page' => $arrResult->per_page,
                'current_page' => $arrResult->current_page,
                'from' => $arrResult->from,
                'to' => $arrResult->to,
                'data' => ['vendor_commissions' => $data],
                'message' => "Lấy danh sách vendor commissions thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // ADD MAIN ACCOUNT

    public function get_vendor_main_account(Request $request, Response $response)
    {
        $data = $request->query->all();

        $vendor_id = $data['vendor_id'];

        $sql = "SELECT d1.id, d2.name, d2.email, d2.phone, d2.position, d3.id AS logo_id FROM vendors_account d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.user_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN document d3 ON(d2.id = d3.rel_id AND d3.document_type_rel='logo')";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "'";
        $sql = $sql . " AND d1.status=0";
        $sql = $sql . " AND d1.vendor_id='" . $vendor_id . "'";
        $sql = $sql . " ORDER BY d1.write_date ASC";
        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();
            $arr['id'] = $result[$i][0];
            $arr['name'] = $result[$i][1];
            $arr['email'] = $result[$i][2];
            $arr['phone'] = $result[$i][3];
            $arr['position'] = $result[$i][4];
            $arr['logo_id'] = $result[$i][5];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['vendors_account' => $data],
            'message' => "Lấy danh sách vendors account thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_vendor_main_account(Request $request, Response $response)
    {
        $data = $request->request->all();
        $query = $request->query->all();
        $vendor_id = $query['vendor_id'];

        $full_name = $data['full_name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $position = $data['position'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'full_name' => 'required',
            'email' => 'required|max:256',
            'phone' => 'required',
            'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM vendors d1 WHERE d1.status=0 AND d1.id='" . $vendor_id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => false,
                    'message' => "Vendor của bạn không tồn tại."
                ];
            } else {

                $sql = "SELECT id, phone, email, user_name FROM res_user WHERE (email='" . str_replace("'", "''", $email) . "'";

                if ($phone != "") {
                    $sql = $sql . " OR phone = '" . str_replace("'", "''", $phone) . "'";
                }
                $sql = $sql . ")";
                $this->msg->add("query", $sql);

                $result = $this->appSession->getTier()->getTable($this->msg);
                $numrows = $result->getRowCount();

                if ($numrows > 0) {
                    $row = $result->getRow(0);
                    if ($row->getString("email") != "" && $row->getString("email") == $email) {
                        $message = [
                            'status' => false,
                            'message' => "Email của bạn đã tồn tại trên hệ thống."
                        ];
                    } else if ($row->getString("phone") != "" && $row->getString("phone") == $phone) {
                        $message = [
                            'status' => false,
                            'message' => "Số điện thoại của bạn đã tồn tại trên hệ thống."
                        ];
                    }
                } else {
                    $sql = "SELECT d1.id FROM res_company d1 WHERE d1.status=0 AND d1.parent_id='" . $this->session_company_id . "'";
                    $sql = $sql . " AND d1.type='vendor'";
                    $this->msg->add("query", $sql);
                    $res_company_id = $this->appSession->getTier()->getValue($this->msg);

                    $sql = "SELECT d1.id FROM res_user_group d1 WHERE d1.status=0 AND d1.company_id='" . $this->session_company_id . "'";
                    $sql = $sql . " AND d1.type='vendor'";

                    $this->msg->add("query", $sql);
                    $res_user_group_id = $this->appSession->getTier()->getValue($this->msg);

                    $user_id = $this->appSession->getTool()->getId();
                    $company_id = $this->appSession->getTool()->getId();

                    $password = $this->appSession->getTool()->randomPassword();

                    $r = $this->appSession->getTool()->send_mail(SUPPORT_EMAIL, "INHIRING", $email, "Mật khẩu INHIRING.IO", "Mật khẩu INHIRING.IO", "Mật khẩu của bạn là: " . $password);

                    $password = sha1($password);

                    $s = $this->appSession->getTool()->toHash("sha256", "[" . $user_id . "]" . $password);
                    $len = $this->appSession->getTool()->lenght($password);
                    for ($i = 0; $i < $len; $i++) {
                        $s = $s . chr($i + 48);
                    }

                    $password = $this->appSession->getTool()->toHash("md5", $s);

                    $builder = $this->appSession->getTier()->createBuilder("res_user");
                    $builder->add("id", $user_id);
                    $builder->add("create_uid", $this->session_user_id);
                    $builder->add("write_uid", $this->session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("name", str_replace("'", "''", $full_name));
                    $builder->add("email", str_replace("'", "''", $email));
                    $builder->add("phone", str_replace("'", "''", $phone));
                    $builder->add("position", str_replace("'", "''", $position));
                    $builder->add("password", str_replace("'", "''", $password));

                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $result = $this->appSession->getTier()->exec($this->msg);

                    $builder = $this->appSession->getTier()->createBuilder("res_company");
                    $builder->add("id", $company_id);
                    $builder->add("create_uid", $this->session_user_id);
                    $builder->add("write_uid", $this->session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("parent_id", $res_company_id);
                    $builder->add("name",  str_replace("'", "''", $full_name));
                    $builder->add("commercial_name",  str_replace("'", "''", $full_name));
                    $builder->add("phone",  str_replace("'", "''", $phone));
                    $builder->add("email",  str_replace("'", "''", $email));

                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $result = $this->appSession->getTier()->exec($this->msg);

                    $res_user_company_id = $this->appSession->getTool()->getId();
                    $builder = $this->appSession->getTier()->createBuilder("res_user_company");
                    $builder->add("id", $res_user_company_id);
                    $builder->add("create_uid", $this->session_user_id);
                    $builder->add("write_uid", $this->session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $company_id);
                    $builder->add("user_id", $user_id);
                    $builder->add("group_id", $res_user_group_id);

                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $result = $this->appSession->getTier()->exec($this->msg);

                    $company_account_id = $this->appSession->getTool()->getId();
                    $builder = $this->appSession->getTier()->createBuilder("vendors_account");
                    $builder->add("id", $company_account_id);
                    $builder->add("create_uid", $this->session_user_id);
                    $builder->add("write_uid", $this->session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("status", 0);
                    $builder->add("company_id", $this->session_company_id);
                    $builder->add("vendor_id", $vendor_id);
                    $builder->add("user_id", $user_id);
                    $builder->add("is_main_account", 1);

                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);

                    $result = $this->appSession->getTier()->exec($this->msg);

                    $logo_id = $this->create_image_profile($request, $user_id, $company_id);

                    if ($result == '1') {

                        $arr_module_id = [
                            ["module_id" => "5c74c32c-a1d1-4272-9cfe-0bb4cae5b949", "module_name" => "Dashboard"],
                            ["module_id" => "cc3e072c-8490-49e7-a6f5-926da175031c", "module_name" => "Candidate View"],
                            ["module_id" => "2bf52543-f096-4e1e-f173-6698e2ca1c1e", "module_name" => "Job View"],
                            ["module_id" => "9aae23ae-287e-4221-d34a-2cab3a39a82d", "module_name" => "Company View"],
                            ["module_id" => "aeb2cf62-a9e6-408b-850d-1c03b0147512", "module_name" => "Vendor View"],
                            ["module_id" => "d6daeadc-f7b7-408d-e2f5-7e3b9417582a", "module_name" => "Report View"],
                            ["module_id" => "72fc461c-8003-4204-d08b-42cd18770212", "module_name" => "Subscription"],
                            ["module_id" => "7ae2e3dd-0606-4bb0-e543-d6b2bd21d199", "module_name" => "Settings"],
                        ];

                        for ($j = 0; $j < count($arr_module_id); $j++) {

                            $module_id = $this->appSession->getTool()->getId();

                            $builder = $this->appSession->getTier()->createBuilder("ir_module_rel");
                            $builder->add("id", $module_id);
                            $builder->add("create_uid",  $this->session_user_id);
                            $builder->add("write_uid",  $this->session_user_id);
                            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                            $builder->add("status", 0);
                            $builder->add("company_id", $company_id);
                            $builder->add("module_id", str_replace("'", "''", $arr_module_id[$j]['module_id']));
                            $builder->add("rel_id", str_replace("'", "''", $company_id));

                            $sql = $this->appSession->getTier()->getInsert($builder);
                            $this->msg->add("query", $sql);
                            $this->appSession->getTier()->exec($this->msg);
                        }
                        $message = [
                            'status' => true,
                            'message' => "Tạo vendor main account thành công"
                        ];
                    } else {
                        $message = [
                            'status' => false,
                            'message' => "Tạo vendor main account thất bại"
                        ];
                    }
                }
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_image_profile(Request $request, $rel_id, $company_id)
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
        $builder->add("company_id", $company_id);
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
}
