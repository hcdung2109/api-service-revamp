<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;

class CareerSiteController extends Controller
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

    // START CAREER SITE

    public function get_career_site(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];

        $companies_id = $param['companies_id'];

        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $in_companies = $param['in_companies'];
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.title, d1.contents, d1.publish, d1.fonts, d1.main_color, d1.text_color, d1.header_layout";
        $sql = $sql . " ,d1.button_text, d1.button_text_color, d1.logo_id, d1.banner_id";
        $sql = $sql . " FROM career_sites d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.title"], $search) . ")";
        }

        if ($start_date != "" || $end_date  != "") {
            $sql = $sql . " AND d1.create_date>='" . $start_date . "' AND d1.create_date<='" . $end_date . "'";
        }

        if ($in_companies != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.companies_id", $in_companies) . ")";
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
            $arr_socials = array();

            $arr['id'] = $result[$i][0];
            $arr['title'] = $result[$i][1];
            $arr['contents'] = $result[$i][2];
            $arr['publish'] = $result[$i][3];
            $arr['fonts'] = $result[$i][4];
            $arr['main_color'] = $result[$i][5];
            $arr['text_color'] = $result[$i][6];
            $arr['header_layout'] = $result[$i][7];
            $arr['button_text'] = $result[$i][8];
            $arr['button_text_color'] = $result[$i][9];
            $arr['logo_id'] = $result[$i][10];
            $arr['banner_id'] = $result[$i][11];

            $sql = "SELECT d1.id, d1.active, d1.url, d1.icons FROM career_site_socials d1";
            $sql = $sql . " WHERE d1.career_site_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_socials = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_socials); $j++) {
                $arr_socials[$result_socials[$j][3] ] = ['id'=>$result_socials[$j][0],'active'=>$result_socials[$j][1],'url' => $result_socials[$j][2]];
            }

            $arr['socials'] = $arr_socials;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['career_sites' => $data],
            'message' => "Lấy danh sách career sites thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_career_site(Request $request, Response $response)
    {
        $data = $request->request->all();
        $companies_id = $data['companies_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'companies_id' => 'required',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("career_sites");
            $career_site_id = $this->appSession->getTool()->getId();
            $builder->add("id", $career_site_id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("companies_id", str_replace("'", "''", $companies_id));
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result =  $this->appSession->getTier()->exec($this->msg);


            $arr_defaul_socials = [
                ["name" => "facebook",  "active" => "0",  "url" => ""],
                ["name" => "instagram",  "active" => "0",  "url" => ""],
                ["name" => "twitter",  "active" => "0",  "url" => ""],
                ["name" => "linkedin",  "active" => "0",  "url" => ""],
                ["name" => "website",  "active" => "0",  "url" => ""],
            ];

            for ($j = 0; $j < count($arr_defaul_socials); $j++) {

                $builder = $this->appSession->getTier()->createBuilder("career_site_socials");
                $id = $this->appSession->getTool()->getId();
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("career_site_id", str_replace("'", "''", $career_site_id));
                $builder->add("active", $arr_defaul_socials[$j]['active']);
                $builder->add("url", $arr_defaul_socials[$j]['url']);
                $builder->add("icons", $arr_defaul_socials[$j]['name']);

                $sql = $this->appSession->getTier()->getInsert($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

            }

            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo career site thành công."
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo career site thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_career_site($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $publish = $data['publish'] ?? 0;
        $fonts = $data['fonts'];
        $main_color = $data['main_color'];
        $text_color = $data['text_color'];
        $header_layout = $data['header_layout'];
        $button_text = $data['button_text'];
        $button_text_color = $data['button_text_color'];
        $title = $data['title'];
        $contents = $data['contents'];
        $social_id = $data['social_id'];
        $active = $data['active'];
        $url = $data['url'];

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
            'fonts' => 'required',
            'main_color' => 'required',
            'text_color' => 'required',
            'header_layout' => 'required',
            'button_text' => 'required',
            'button_text_color' => 'required',
            'logo' => 'uploaded_file|max:1M|mimes:jpeg,png',
            'banner' => 'uploaded_file|max:1M|mimes:jpeg,png',
            'title' => 'required|max:1000',
            'contents' => 'required|max:10000',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM career_sites d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Career site không tồn tại."
                ];
            } else {
                $logo_id = $this->update_document_career_site_logo($request, $id);
                $banner_id = $this->update_document_career_site_banner($request, $id);

                $builder = $this->appSession->getTier()->getBuilder("career_sites");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("publish", str_replace("'", "''", $publish));
                $builder->update("fonts", str_replace("'", "''", $fonts));
                $builder->update("main_color", str_replace("'", "''", $main_color));
                $builder->update("text_color", str_replace("'", "''", $text_color));
                $builder->update("header_layout", str_replace("'", "''", $header_layout));
                $builder->update("button_text", str_replace("'", "''", $button_text));
                $builder->update("button_text_color", str_replace("'", "''", $button_text_color));
                $builder->update("title", str_replace("'", "''", $title));
                $builder->update("contents", str_replace("'", "''", $contents));
                $builder->update("logo_id", str_replace("'", "''", $logo_id));
                $builder->update("banner_id", str_replace("'", "''", $banner_id));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                $builder = $this->appSession->getTier()->getBuilder("career_site_socials");
                $builder->update("id", str_replace("'", "''", $social_id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("active", str_replace("'", "''", $active));
                $builder->update("url", str_replace("'", "''", $url));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật career site thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật career site  thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_career_site_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.title, d1.contents, d1.publish, d1.fonts, d1.main_color, d1.text_color, d1.header_layout";
        $sql = $sql . " ,d1.button_text, d1.button_text_color, d1.logo_id, d1.banner_id";
        $sql = $sql . " FROM career_sites d1";
        $sql = $sql . " LEFT OUTER JOIN companies d2 ON(d1.companies_id = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
        $sql = $sql . " AND d1.id='" . $id . "'";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);
            $arr = array();
            $arr_socials = array();

            $arr['id'] = $row->getString("id");
            $arr['title'] = $row->getString("title");
            $arr['contents'] = $row->getString("contents");
            $arr['publish'] = $row->getString("publish");
            $arr['fonts'] = $row->getString("fonts");
            $arr['main_color'] = $row->getString("main_color");
            $arr['text_color'] = $row->getString("text_color");
            $arr['header_layout'] = $row->getString("header_layout");
            $arr['button_text'] = $row->getString("button_text");
            $arr['button_text_color'] = $row->getString("button_text_color");
            $arr['logo_id'] = $row->getString("logo_id");
            $arr['banner_id'] = $row->getString("banner_id");

            $sql = "SELECT d1.id, d1.active, d1.url, d1.icons FROM career_site_socials d1";
            $sql = $sql . " WHERE d1.career_site_id='" . $arr['id'] . "' AND d1.status=0";
            $sql = $sql . " ORDER BY d1.write_date ASC";
            $this->msg->add("query", $sql);
            $result_socials = $this->appSession->getTier()->getArray($this->msg);

            for ($j = 0; $j < count($result_socials); $j++) {
                $arr_socials[$result_socials[$j][3] ] = ['id'=>$result_socials[$j][0],'active'=>$result_socials[$j][1],'url' => $result_socials[$j][2]];
            }

            $arr['socials'] = $arr_socials;

            $data[] = $arr;

            $message = [
                'status' => true,
                'data' => ['career_site' => $arr],
                'message' => "Lấy career site by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Career site không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_career_site($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM career_sites d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Career sites không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("career_sites");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa career sites thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_career_site_logo(Request $request, $rel_id)
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

    public function update_document_career_site_logo(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='logo'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $file_id = $this->create_document_career_site_logo($request, $rel_id);
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

    public function create_document_career_site_banner(Request $request, $rel_id)
    {
        $file = $request->files->get('banner');
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
        $builder->add("document_type_rel", 'banner');
        $sql = $this->appSession->getTier()->getInsert($builder);
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        return $file_id;
    }

    public function update_document_career_site_banner(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='banner'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $file_id = $this->create_document_career_site_banner($request, $rel_id);
        } else {

            $file = $request->files->get('banner');
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
                $builder->update("document_type_rel", 'banner');
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

                return $file_id;
            }
        }
    }

    // // START SOCIAL

    // public function get_social(Request $request, Response $response)
    // {
    //     $param = $request->query->all();

    //     $career_site_id = $param['career_site_id'];

    //     $sql = "SELECT d1.id, d1.career_site_id, d1.active, d1.url, d1.icons";
    //     $sql = $sql . " FROM career_site_socials d1";
    //     $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
    //     $sql = $sql . " AND d1.career_site_id='" . $career_site_id . "'";

    //     $this->msg->add("query", $sql);
    //     $result = $this->appSession->getTier()->getArray($this->msg);
    //     $data = array();
    //     for ($i = 0; $i < count($result); $i++) {
    //         $arr = array();

    //         $arr['id'] = $result[$i][0];
    //         $arr['career_site_id'] = $result[$i][1];
    //         $arr['active'] = $result[$i][2];
    //         $arr['url'] = $result[$i][3];
    //         $arr['icons'] = $result[$i][4];

    //         $data[] = $arr;
    //     }

    //     $message = [
    //         'status' => true,
    //         'data' => ['career_site_socials' => $data],
    //         'message' => "Lấy danh sách career site socials thành công."
    //     ];

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function create_social(Request $request, Response $response)
    // {
    //     $data = $request->request->all();
    //     $career_site_id = $data['career_site_id'];
    //     $active = $data['active'];
    //     $url = $data['url'];
    //     $icons = $data['icons'];


    //     $validator = new Validator;
    //     $validator->setMessages([
    //         'required' => ':attribute không được để trống.',
    //         'max' => ':attribute không được quá :max .',
    //     ]);

    //     $validation = $validator->make($_POST + $_FILES, [
    //         'career_site_id' => 'required',
    //         'active' => 'required',
    //         'url' => 'required|max:1000',
    //         'icons' => 'required|max:1000',
    //     ]);

    //     $validation->validate();

    //     if ($validation->fails()) {
    //         $message = [
    //             'status' => false,
    //             'message' => $validation->errors->firstOfAll(':message', true)
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->createBuilder("career_site_socials");
    //         $id = $this->appSession->getTool()->getId();
    //         $builder->add("id", $id);
    //         $builder->add("create_uid", $this->session_user_id);
    //         $builder->add("write_uid", $this->session_user_id);
    //         $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
    //         $builder->add("status", 0);
    //         $builder->add("company_id", $this->session_company_id);
    //         $builder->add("career_site_id", str_replace("'", "''", $career_site_id));
    //         $builder->add("active", str_replace("'", "''", $active));
    //         $builder->add("url", str_replace("'", "''", $url));
    //         $builder->add("icons", str_replace("'", "''", $icons));


    //         $sql = $this->appSession->getTier()->getInsert($builder);
    //         $this->msg->add("query", $sql);
    //         $result =  $this->appSession->getTier()->exec($this->msg);

    //         if ($result == '1') {
    //             $message = [
    //                 'status' => true,
    //                 'message' => "Tạo career site social thành công."
    //             ];
    //         } else {
    //             $message = [
    //                 'status' => false,
    //                 'message' => "Tạo career social thất bại."
    //             ];
    //         }
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    public function update_social($id, Request $request, Response $response)
    {
        $data = $request->request->all();
        $active = $data['active'];
        $url = $data['url'];
        // $icons = $data['icons'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',

        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'active' => 'required',
            'url' => 'max:1000',
            'icons' => 'max:1000',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM career_site_socials d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Career site social không tồn tại."
                ];
            } else {

                $builder = $this->appSession->getTier()->getBuilder("career_site_socials");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("active", str_replace("'", "''", $active));
                $builder->update("url", str_replace("'", "''", $url));
                // $builder->update("icons", str_replace("'", "''", $icons));
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật career site social thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật career site social thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    // public function get_social_by_id($id, Response $response)
    // {
    //     $sql = "SELECT d1.id, d1.career_site_id, d1.active, d1.url, d1.icons";
    //     $sql = $sql . " FROM career_site_socials d1";
    //     $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";
    //     $sql = $sql . " AND d1.id='" . $id . "'";

    //     $this->msg->add("query", $sql);

    //     $result =  $this->appSession->getTier()->getTable($this->msg);
    //     $numrows = $result->getRowCount();

    //     if ($numrows > 0) {
    //         $row = $result->getRow(0);
    //         $arr = array();

    //         $arr['id'] = $row->getString("id");
    //         $arr['career_site_id'] = $row->getString("career_site_id");
    //         $arr['active'] = $row->getString("active");
    //         $arr['url'] = $row->getString("url");
    //         $arr['icons'] = $row->getString("icons");

    //         $message = [
    //             'status' => true,
    //             'data' => ['career_site_social' => $arr],
    //             'message' => "Lấy career site social by id thành công."
    //         ];
    //     } else {
    //         $message = [
    //             'status' => false,
    //             'message' => "Career site social không tồn tại."
    //         ];
    //     }

    //     return $this->appSession->getTier()->response($message, $response);
    // }

    // public function delete_social($id, Response $response)
    // {
    //     $message = [];

    //     $sql = "SELECT d1.id FROM career_site_socials d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
    //     $this->msg->add("query", $sql);
    //     $seen_id = $this->appSession->getTier()->getValue($this->msg);

    //     if ($seen_id == "") {
    //         $message = [
    //             'status' => true,
    //             'message' => "Career sites social không tồn tại."
    //         ];
    //     } else {
    //         $builder = $this->appSession->getTier()->getBuilder("career_site_socials");
    //         $builder->add("id", $id);
    //         $builder->add("status", 1);

    //         $sql = $this->appSession->getTier()->getUpdate($builder);
    //         $this->msg->add("query", $sql);
    //         $result = $this->appSession->getTier()->exec($this->msg);

    //         $message = [
    //             'status' => true,
    //             'message' => "Xóa career site social thành công."
    //         ];
    //     }
    //     return $this->appSession->getTier()->response($message, $response);
    // }
}
