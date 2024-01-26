<?php

namespace AppLight\Controllers;

use App\Http\Controllers\Controller;
use Rakit\Validation\Validator;
use AppLight\Controllers\DocumentController as Document;

class CommentsController extends Controller
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


    // START COMMENTS
    public function get_comments(Request $request, Response $response)
    {
        $param = $request->query->all();

        $rel_id = $param['rel_id'];

        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";
        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'] ?? 20;

        $sql = "SELECT d1.id, d1.rel_id, d1.contents, d1.parent_id, d1.create_uid, d2.name, d2.email";
        $sql = $sql . " FROM comments d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.create_uid = d2.id)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0 AND d1.rel_id='" . $rel_id . "'";

        $soft = "{$soft_column}" . " " . "{$soft_by}";

        $arrPaging = $this->appSession->getTier()->paging($sql, $p, $ps, "d1." . $soft);

        $arrResult = $this->appSession->getTier()->getArrayPaging($this->msg, $this->appSession, $arrPaging, $p, $ps);

        $this->msg->add("query", $arrResult->sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['rel_id'] = $result[$i][1];
            $arr['contents'] = $result[$i][2];
            $arr['parent_id'] = $result[$i][3];
            $arr['create_uid'] = $result[$i][4];
            $arr['name'] = $result[$i][5];
            $arr['email'] = $result[$i][6];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $arrResult->total,
            'per_page' => $arrResult->per_page,
            'current_page' => $arrResult->current_page,
            'from' => $arrResult->from,
            'to' => $arrResult->to,
            'data' => ['comments' => $data],
            'message' => "Lấy danh sách comments thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_comments(Request $request, Response $response)
    {
        $data = $request->request->all();

        $rel_id = $data['rel_id'];
        $contents = $data['contents'];
        $parent_id = $data['parent_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'contents' => 'required|max:10000',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $builder = $this->appSession->getTier()->createBuilder("comments");
            $id = $this->appSession->getTool()->getId();
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("rel_id", str_replace("'", "''", $rel_id));
            $builder->add("contents", str_replace("'", "''", $contents));
            $builder->add("parent_id", str_replace("'", "''", $parent_id));

            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result =  $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $data = $this->get_comments_by_id($id, $response);
                $message = json_decode($data->getContent());
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo commtent thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_comments($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $contents = $data['contents'];
        $parent_id = $data['parent_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'rel_id' => 'required',
            'contents' => 'max:10000',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {

            $builder = $this->appSession->getTier()->getBuilder("comments");
            $builder->update("id", str_replace("'", "''", $id));
            $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->update("contents", str_replace("'", "''", $contents));
            $builder->update("parent_id", str_replace("'", "''", $parent_id));

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            if ($result == '1') {
                $data = $this->get_comments_by_id($id, $response);
                $message = json_decode($data->getContent());
            } else {
                $message = [
                    'status' => false,
                    'message' => "Cập nhật comment thất bại."
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_comments_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.rel_id, d1.contents, d1.parent_id, d1.create_uid, d2.name, d2.email";
        $sql = $sql . " FROM comments d1";
        $sql = $sql . " LEFT OUTER JOIN res_user d2 ON(d1.create_uid = d2.id)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['rel_id'] = $row->getString("rel_id");
            $arr['contents'] = $row->getString("contents");
            $arr['parent_id'] = $row->getString("parent_id");
            $arr['create_uid'] = $row->getString("create_uid");
            $arr['name'] = $row->getString("name");
            $arr['email'] = $row->getString("email");

            $message = [
                'status' => true,
                'data' => ['comment' => $arr],
                'message' => 'Lấy comment by id thành công.'
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Comment không tồn tại."
            ];
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_comments($id, Response $response)
    {

        $sql = "SELECT d1.id FROM comments d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Comments không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("comments");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa comment thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }
}
