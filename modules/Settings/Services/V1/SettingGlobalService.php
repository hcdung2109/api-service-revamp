<?php

namespace Digisource\Settings\Services\V1;


use Digisource\Settings\Contracts\SettingGlobalServiceFactory;
use Digisource\Settings\Contracts\SkillsRepositoryFactory;
use Digisource\Settings\Entities\Skill;
use Digisource\Settings\Repositories\V1\SkillsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SettingGlobalService implements SettingGlobalServiceFactory
{
    private SkillsRepository $skillsRepository;

    public function __construct(SkillsRepositoryFactory $skillsRepositoryFactory)
    {
        $this->skillsRepository = $skillsRepositoryFactory;
    }

    public function getSkills(array $request)
    {
        $p = Arr::get($request, 'p', 0);
        $ps = Arr::get($request, 'ps', null);

        $skills = $this->skillsRepository
            ->where('status', 0)
            ->where('company_id', auth()->user()->company_id) // Lấy company_id từ user
            ->orderBy('name');

        if ($ps != null) {
            $result = $skills->paginate($ps, ['*'], 'p', $p);
            $current = $result->currentPage();
            return [
                "items" => $result->items(),
                "total" => $result->total(),
                "total_page" => $result->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $result->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $result->perPage(),
                "from" => $result->firstItem(),
                "to" => $result->lastItem(),
            ];
        } else {
            $skills = $skills->get();
            return $skills->toArray();
        }
    }

    public function createSkills($name)
    {
        $user = auth()->user();
        $data = [
            'id' => Str::uuid(),
            "create_uid" => $user->id,
            "write_uid" => $user->id,
            "create_date" => now(),
            "write_date" => now(),
            "status" => 0,
            "company_id" => $user->company_id,
            "name" => $name
        ];
        $skill = $this->skillsRepository->create($data);
        return $skill;
    }

    public function updateSkills($id, $name)
    {
        $user = auth()->user();
        $data = [
            "write_uid" => $user->id,
            "write_date" => now(),
            "name" => $name
        ];

        $skill = $this->skillsRepository
            ->where('company_id', $user->company_id) // Lấy company_id từ user
            ->update($id, $data);

        return $skill;
    }

    public function getSkillById($id)
    {
        $skill = $this->skillsRepository->where('company_id', auth()->user()->company_id)->find($id);
        if ($skill) {
            return [
                'skill' => $skill->toArray(),
            ];
        } else {
            return [
                'message' => __('Skill not found')
            ];
        }
    }

    public function deleteSkills($id)
    {
        $res = $this->skillsRepository->where('company_id', auth()->user()->company_id)->delete($id);
        if ($res) {
            return ['message' => __('Skill deleted')];
        } else {
            return ['message' => __('Skill not found')];
        }
    }
    // END SETTINGS SKILL

    // // START SETTINGS COUNTRY
    // public function get_countrys(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function create_countrys(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function update_countrys($id, Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_countrys_by_id($id)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function delete_countrys($id)
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
    //     return $this->appSession->getTier()->response($message);
    // }
    // // END SETTINGS COUNTRY

    // // START SETTINGS CITIS
    // public function get_citis(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function create_citis(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function update_citis($id, Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_citis_by_id($id)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function delete_citis($id)
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
    //     return $this->appSession->getTier()->response($message);
    // }
    // // END SETTINGS CITIS

    // // START SETTINGS DISTRICTS
    // public function get_districts(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function create_districts(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function update_districts($id, Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_districts_by_id($id)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function delete_districts($id)
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
    //     return $this->appSession->getTier()->response($message);
    // }
    // // END SETTINGS DISTRICTS

    // // START SETTINGS WARDS
    // public function get_wards(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function create_wards(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function update_wards($id, Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_wards_by_id($id)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function delete_wards($id)
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
    //     return $this->appSession->getTier()->response($message);
    // }
    // // END SETTINGS WARDS

    // // START SETTINGS FLITER

    // public function get_filter_by_city(Request $request)
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

    //     return $this->appSession->getTier()->response($message);

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_filter_by_district(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // public function get_filter_by_ward(Request $request)
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

    //     return $this->appSession->getTier()->response($message);
    // }

    // // END SETTINGS FLITER
}
