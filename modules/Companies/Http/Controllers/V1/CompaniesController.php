<?php

namespace Digisource\Companies\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Digisource\Companies\Contracts\CompaniesServiceFactory;
use Digisource\Companies\Services\V1\CompaniesService;
use Digisource\Settings\Contracts\SettingsCompanyServiceFactory;
use Digisource\Settings\Services\V1\SettingsCompanyService;
use Illuminate\Validation\ValidationException;

class CompaniesController extends Controller
{
    private CompaniesService $companiesService;
    private SettingsCompanyService $settingsCompanyService;

    public function __construct(
        CompaniesServiceFactory $companiesServiceFactory,
        SettingsCompanyServiceFactory $settingsCompanyServiceFactory
    ) {
        $this->companiesService = $companiesServiceFactory;
        $this->settingsCompanyService = $settingsCompanyServiceFactory;
    }

    public function get_company_commissions(Request $request)
    {
        $companiesId = $request->get('companies_id');

        $result = DB::table('companies_commissions as d1')
            ->select(
                'd1.id',
                'd1.vendor_id',
                'd1.vendor_type_id',
                'd1.commission_value',
                'd1.companies_id',
                'd2.name AS vendor_name',
                'd3.name AS vendor_type_name'
            )
            ->leftJoin('vendors as d2', 'd1.vendor_id', '=', 'd2.id')
            ->leftJoin('vendor_types as d3', 'd1.vendor_type_id', '=', 'd3.id')
            ->where('d1.company_id', auth()->user()->company_id)
            ->where('d1.companies_id', $companiesId)
            ->where('d1.status', 0)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = [];

        foreach ($result as $item) {
            $arr = [
                'id' => $item->id,
                'vendor_id' => $item->vendor_id,
                'vendor_type_id' => $item->vendor_type_id,
                'commission_value' => $item->commission_value,
                'companies_id' => $item->companies_id,
                'vendors' => [
                    'id' => $item->vendor_id,
                    'name' => $item->vendor_name,
                ],
                'vendor_types' => [
                    'id' => $item->vendor_type_id,
                    'name' => $item->vendor_type_name,
                ],
            ];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['companies_commissions' => $data],
            'message' => "Lấy danh sách companies commissions thành công.",
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_company_commissions(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $id = uniqid();

        $vendorId = $param['vendor_id'];
        $vendorTypeId = $param['vendor_type_id'];
        $commissionValue = $param['commission_value'];
        $companiesId = $param['companies_id'];

        $validator = Validator::make($param, [
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_value' => 'required|numeric',
            'companies_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
            'numeric' => ':attribute không phải là số.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $result = DB::table('companies_commissions')
                ->insert([
                    'id' => $id,
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'vendor_id' => $vendorId,
                    'vendor_type_id' => $vendorTypeId,
                    'commission_value' => $commissionValue,
                    'companies_id' => $companiesId,
                ]);

            if ($result) {
                $this->setMessage("Tạo company commissions thành công");
            } else {
                $this->setMessage("Tạo company commissions thất bại");
            }
        }

        return $this->getResponse();
    }

    public function update_company_commissions(Request $request, $id)
    {
        $user = auth()->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_value' => 'numeric',
        ], [
            'required' => ':attribute không được để trống.',
            'numeric' => ':attribute không phải là số.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
            $this->addData($message);

            return $this->getResponse();
        }

        $vendor_id = $data['vendor_id'];
        $vendor_type_id = $data['vendor_type_id'];
        $commission_value = $data['commission_value'];

        $seen_id = DB::table('companies_commissions')
            ->where('id', $id)
            ->where('status', 0)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Companies commission không tồn tại."
            ];
            $this->addData($message);

            return $this->getResponse();
        }

        DB::table('companies_commissions')
            ->where('id', $id)
            ->update([
                'write_date' => date('Y-m-d H:i:s'),
                'vendor_id' => $vendor_id,
                'vendor_type_id' => $vendor_type_id,
                'commission_value' => $commission_value,
            ]);

        $message = [
            'status' => true,
            'message' => "Cập nhật company commission thành công"
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_company_commissions_by_id(Request $request, $id)
    {
        $result = DB::table('companies_commissions as d1')
            ->select(
                'd1.id',
                'd1.vendor_id',
                'd1.vendor_type_id',
                'd1.commission_value',
                'd1.companies_id',
                'd2.name as vendor_name',
                'd3.name as vendor_type_name'
            )
            ->leftJoin('vendors as d2', 'd1.vendor_id', '=', 'd2.id')
            ->leftJoin('vendor_types as d3', 'd1.vendor_type_id', '=', 'd3.id')
            ->where('d1.id', $id)
            ->where('d1.status', 0)
            ->orderBy('d1.write_date', 'ASC')
            ->first();

        if ($result) {
            $arr = [
                'id' => $result->id,
                'vendor_id' => $result->vendor_id,
                'vendor_type_id' => $result->vendor_type_id,
                'commission_value' => $result->commission_value,
                'companies_id' => $result->companies_id,
                'vendors' => [
                    'id' => $result->vendor_id,
                    'name' => $result->vendor_name,
                ],
                'vendor_types' => [
                    'id' => $result->vendor_type_id,
                    'name' => $result->vendor_type_name,
                ],
            ];

            $message = [
                'status' => true,
                'data' => ['company' => $arr],
                'message' => "Lấy danh sách company commission by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Company commission không tồn tại."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_company_commissions(Request $request, $id)
    {
        $seenId = DB::table('companies_commissions')
            ->select('id')
            ->where('status', 0)
            ->where('id', $id)
            ->first();

        if (!$seenId) {
            return [
                'status' => true,
                'message' => 'Companies commissions không tồn tại.'
            ];
        }

        DB::table('companies_commissions')
            ->where('id', $id)
            ->update(['status' => 1]);

        $message = [
            'status' => true,
            'message' => 'Xóa companies commissions thành công.'
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_company_notes(Request $request)
    {
        $data = $request->all();
        $companiesId = $data['companies_id'];

        $result = DB::table('notes as d1')
            ->select(
                'd1.id',
                'd1.notesable_type',
                'd1.notesable_id',
                'd1.contents',
                'd3.id AS user_id',
                'd3.user_name',
                'd3.name',
                'd1.create_date'
            )
            ->leftJoin('candidates as d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', auth()->user()->company_id)
            ->where('d1.status', 0)
            ->where('d1.notesable_id', $companiesId)
            ->where('d1.notesable_type', 'company')
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = $result->toArray();

        $message = [
            'status' => true,
            'data' => ['jobs_note' => $data],
            'message' => "Lấy danh sách job note thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_company_notes(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $relId = $param['companies_id'];
        $content = $param['contents'];

        $validator = Validator::make($request->all(), [
            'companies_id' => 'required',
            'contents' => 'required|max:256',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $seenId = DB::table('companies')
                ->where('status', 0)
                ->where('id', $relId)
                ->value('id');

            if (empty($seenId)) {
                $message = [
                    'status' => false,
                    'message' => "Company của bạn không tồn tại."
                ];
            } else {
                $id = DB::table('notes')->insertGetId([
                    'id' => uniqid(),
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'notesable_type' => 'company',
                    'notesable_id' => $relId,
                    'contents' => $content,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo company note thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo company note thất bại"
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_company_notes(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $content = $data['contents'];

        $validator = Validator::make($request->all(), [
            'contents' => 'max:256',
        ], [
            'max' => ':attribute không được quá :max .',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $seenId = DB::table('notes')
                ->where('status', 0)
                ->where('id', $id)
                ->value('id');

            if (empty($seenId)) {
                $message = [
                    'status' => true,
                    'message' => "Company note không tồn tại."
                ];
            } else {
                DB::table('notes')
                    ->where('id', $id)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'contents' => $content,
                    ]);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật company note thành công."
                ];
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_company_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('notes as d1')
            ->select('d1.id', 'd1.notesable_type', 'd1.notesable_id', 'd1.contents', 'd3.user_name', 'd3.name', 'd1.create_date')
            ->leftJoin('candidates as d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.id', $id)
            ->where('d1.notesable_type', 'company')
            ->orderBy('d1.create_date', 'ASC')
            ->first();

        if ($result) {
            $arr = [
                'id' => $result->id,
                'notesable_type' => $result->notesable_type,
                'notesable_id' => $result->notesable_id,
                'contents' => $result->contents,
                'user_name' => $result->user_name,
                'name' => $result->name,
                'create_date' => $result->create_date,
            ];

            $message = [
                'status' => true,
                'data' => ['company_note' => $arr],
                'message' => "Lấy danh sách company note by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Company note không tồn tại."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_company_notes(Request $request, $id)
    {
        $user = auth()->user();

        $seenId = DB::table('notes')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seenId) {
            $message = [
                'status' => true,
                'message' => "Company note không tồn tại."
            ];
        } else {
            DB::table('notes')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa company note thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    // START COMPANY
    public function get_company(Request $request, Response $response)
    {
        $param = $request->query->all();
        $search = $param['search'];
        $start_date = $param['start_date'];
        $end_date = $param['end_date'];
        $location_id = $param['location_id'];
        $handling_by = $param['handling_by'];
        $in_sources = $param['in_sources'];
        $followed = $param['followed'] ?? "0";
        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];

        $sql = "SELECT d1.id, d1.industry_id, d1.company_name, d1.source_id, d1.representative, d1.phone, d1.email";
        $sql = $sql . ", d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary";
        $sql = $sql . ", d2.name AS industry_name, d3.name AS source_name, d4.name AS city_name";
        $sql = $sql . ", d5.name AS district_name, d6.name AS ward_name, d7.id AS logo_id";
        $sql = $sql . ", d8.followed_id";
        $sql = $sql . " FROM companies d1";
        $sql = $sql . " LEFT OUTER JOIN industries d2 ON(d1.industry_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d3 ON(d1.source_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d4 ON(d1.city_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.district_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.ward_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN company_followed_companies d8 ON(d1.id = d8.companies_id AND d8.status=0)";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.status=0";

        if ($followed == "1") {
            $followed_id = $this->find_companies_followed_id();
            $sql = $sql . " AND d8.status=0 AND d8.followed_id='" . $followed_id . "'";
        }

        if ($search != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildSearch(["d1.company_name", "d1.phone", "d1.email"], $search) . ")";
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

        if ($in_sources != "") {
            $sql = $sql . " AND (" . $this->appSession->getTier()->buildFliter("d1.source_id", $in_sources) . ")";
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
            $arr_industry = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_user_followed = array();

            $arr['id'] = $result[$i][0];
            $arr['industry_id'] = $result[$i][1];
            $arr['company_name'] = $result[$i][2];
            $arr['source_id'] = $result[$i][3];
            $arr['representative'] = $result[$i][4];
            $arr['phone'] = $result[$i][5];
            $arr['email'] = $result[$i][6];
            $arr['city_id'] = $result[$i][7];
            $arr['district_id'] = $result[$i][8];
            $arr['ward_id'] = $result[$i][9];
            $arr['address'] = $result[$i][10];
            $arr['summary'] = $result[$i][11];

            $arr_industry['id'] = $result[$i][1];
            $arr_industry['name'] = $result[$i][12];
            $arr['industries'] = $arr_industry;

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

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM company_followed_companies d1";
            $sql = $sql . " LEFT OUTER JOIN company_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.companies_id='" . $arr['id'] . "' AND d1.status=0";
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
            'data' => ['companies' => $data],
            'message' => "Lấy danh sách companies thành công."
        ];


        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_company(Request $request, Response $response)
    {
        $data = $request->request->all();
        $id = $this->appSession->getTool()->getId();

        $industry_id = $data['industry_id'];
        $company_name = $data['company_name'];
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
            'numeric' => ':attribute không phải là số điện thoại hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg, png.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'industry_id' => 'required',
            'company_name' => 'required|max:256',
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
            $sql = "SELECT d1.email, d1.phone FROM companies d1 WHERE d1.status=0 AND (d1.email='" . $email . "' OR d1.phone='" . $phone . "')";

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
                $builder = $this->appSession->getTier()->createBuilder("companies");
                $builder->add("id", $id);
                $builder->add("create_uid", $this->session_user_id);
                $builder->add("write_uid", $this->session_user_id);
                $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->add("status", 0);
                $builder->add("company_id", $this->session_company_id);
                $builder->add("industry_id", str_replace("'", "''", $industry_id));
                $builder->add("company_name", str_replace("'", "''", $company_name));
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
                $result =  $this->appSession->getTier()->exec($this->msg);
                $logo_id = $this->create_document_company($request, $id);

                if ($result == '1') {
                    $data = $this->get_company_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo company thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_company($id, Request $request, Response $response)
    {
        $data = $request->request->all();
        $file = $request->files->all();

        $industry_id = $data['industry_id'];
        $company_name = $data['company_name'];
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
            'numeric' => ':attribute không phải là số điện thoại hợp lệ.',
            'mimes' => ':attribute phải là hình ảnh jpeg, png.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            // 'industry_id' => 'required',
            'company_name' => 'max:256',
            // 'source_id' => 'required',
            'representative' => 'max:256',
            'phone' => 'numeric',
            'email' => 'email',
            // 'city_id' => 'required',
            // 'district_id' => 'required',
            // 'ward_id' => 'required',
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
            $sql = "SELECT d1.id FROM companies d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Companies không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("companies");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("industry_id", str_replace("'", "''", $industry_id));
                $builder->update("company_name", str_replace("'", "''", $company_name));
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
                $logo_id = $this->update_document_company($request, $id);
                if ($result == '1') {

                    $data = $this->get_company_by_id($id, $response);
                    $message = json_decode($data->getContent());
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật company thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_company_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.industry_id, d1.company_name, d1.source_id, d1.representative, d1.phone, d1.email";
        $sql = $sql . ", d1.city_id, d1.district_id, d1.ward_id, d1.address, d1.summary";
        $sql = $sql . ", d2.name AS industry_name, d3.name AS source_name, d4.name AS city_name";
        $sql = $sql . ", d5.name AS district_name, d6.name AS ward_name, d7.id AS logo_id";
        $sql = $sql . ", d8.followed_id";
        $sql = $sql . " FROM companies d1";
        $sql = $sql . " LEFT OUTER JOIN industries d2 ON(d1.industry_id = d2.id)";
        $sql = $sql . " LEFT OUTER JOIN sources d3 ON(d1.source_id = d3.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d4 ON(d1.city_id = d4.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d5 ON(d1.district_id = d5.id)";
        $sql = $sql . " LEFT OUTER JOIN res_address d6 ON(d1.ward_id = d6.id)";
        $sql = $sql . " LEFT OUTER JOIN document d7 ON(d1.id = d7.rel_id AND d7.document_type_rel='logo')";
        $sql = $sql . " LEFT OUTER JOIN company_followed_companies d8 ON(d1.id = d8.companies_id AND d8.status=0)";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.create_date ASC";

        $this->msg->add("query", $sql);


        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);


            $arr = array();
            $arr_industry = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_user_followed = array();

            $arr['id'] = $row->getString("id");
            $arr['industry_id'] = $row->getString("industry_id");
            $arr['company_name'] = $row->getString("company_name");
            $arr['source_id'] = $row->getString("source_id");
            $arr['representative'] = $row->getString("representative");
            $arr['phone'] = $row->getString("phone");
            $arr['email'] = $row->getString("email");
            $arr['city_id'] = $row->getString("city_id");
            $arr['district_id'] = $row->getString("district_id");
            $arr['ward_id'] = $row->getString("ward_id");
            $arr['address'] = $row->getString("address");
            $arr['summary'] = $row->getString("summary");

            $arr_industry['id'] = $row->getString("industry_id");
            $arr_industry['name'] = $row->getString("industry_name");
            $arr['industries'] = $arr_industry;

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

            $sql = "SELECT d1.id, d3.user_name, d1.id AS avatar_id, d3.id AS user_id FROM company_followed_companies d1";
            $sql = $sql . " LEFT OUTER JOIN company_followed d2 ON(d1.followed_id = d2.id)";
            $sql = $sql . " LEFT OUTER JOIN res_user d3 ON(d2.user_id = d3.id)";
            $sql = $sql . " LEFT OUTER JOIN document d4 ON(d3.id = d4.rel_id AND d4.document_type_rel='avatar')";
            $sql = $sql . " WHERE d1.companies_id='" . $arr['id'] . "' AND d1.status=0";
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
                'data' => ['company' => $arr],
                'message' => "Lấy company by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Cpmpany không tồn tại."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function delete_company($id, Response $response)
    {

        $sql = "SELECT d1.id FROM companies d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Companies không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("companies");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa conmpany thành công."
            ];
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_document_company(Request $request, $rel_id)
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

    public function update_document_company(Request $request, $rel_id)
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
    // END COMPANY

    // START INVOICES
    public function get_invoices(Request $request, Response $response)
    {
        $query = $request->query->all();
        $companies_id = $query['companies_id'];

        $sql = "SELECT d1.id, d1.invoice_no, d1.invoice_date, d1.amount, d1.description, d1.companies_id, d2.id AS invoice_id";
        $sql = $sql . " FROM invoices d1";
        $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='invoice')";
        $sql = $sql . " WHERE d1.company_id='" . $this->session_company_id . "' AND d1.companies_id='" . $companies_id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result = $this->appSession->getTier()->getArray($this->msg);
        $data = array();
        for ($i = 0; $i < count($result); $i++) {
            $arr = array();

            $arr['id'] = $result[$i][0];
            $arr['invoice_no'] = $result[$i][1];
            $arr['invoice_date'] = $result[$i][2];
            $arr['amount'] = $result[$i][3];
            $arr['description'] = $result[$i][4];
            $arr['companies_id'] = $result[$i][5];
            $arr['invoice_id'] = $result[$i][6];
            $arr['link_download_invoice'] = URL . "document/?id=" . $result[$i][6] . "";
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['company_invoices' => $data],
            'message' => "Lấy danh sách company invoices thành công."
        ];

        return $this->appSession->getTier()->response($message, $response);
    }

    public function create_invoices(Request $request, Response $response)
    {
        $data = $request->request->all();
        $file = $request->files->all();
        $id = $this->appSession->getTool()->getId();

        $invoice_no = $data['invoice_no'];
        $invoice_date = $data['invoice_date'];
        $amount = $data['amount'];
        $description = $data['description'];
        $companies_id = $data['companies_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số.',
            'mimes' => ':attribute phải là định dạng PDF.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            'invoice_no' => 'required',
            'invoice_date' => 'required|date:Y-m-d',
            'amount' => 'required|numeric',
            'description' => 'max:256',
            'companies_id' => 'required',
            'file_invoice' => 'required|uploaded_file|max:2M|mimes:pdf',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $builder = $this->appSession->getTier()->createBuilder("invoices");
            $builder->add("id", $id);
            $builder->add("create_uid", $this->session_user_id);
            $builder->add("write_uid", $this->session_user_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("status", 0);
            $builder->add("company_id", $this->session_company_id);
            $builder->add("invoice_no", str_replace("'", "''", $invoice_no));
            $builder->add("invoice_date", str_replace("'", "''", $invoice_date));
            $builder->add("amount", str_replace("'", "''", $amount));
            $builder->add("description", str_replace("'", "''", $description));
            $builder->add("companies_id", str_replace("'", "''", $companies_id));

            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $invoice_id = $this->create_document_invoices($request, $id);
            if ($result == '1') {
                $message = [
                    'status' => true,
                    'message' => "Tạo invoice company thành công"
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo invoice company thất bại"
                ];
            }
        }

        return $this->appSession->getTier()->response($message, $response);
    }

    public function update_invoices($id, Request $request, Response $response)
    {
        $data = $request->request->all();

        $invoice_no = $data['invoice_no'];
        $invoice_date = $data['invoice_date'];
        $amount = $data['amount'];
        $description = $data['description'];
        //$companies_id = $data['companies_id'];

        $validator = new Validator;
        $validator->setMessages([
            'required' => ':attribute không được để trống.',
            'min' => ':attribute tối thiểu :min ký tự.',
            'max' => ':attribute không được quá :max .',
            'email' => ':attribute không phải là email hợp lệ.',
            'numeric' => ':attribute không phải là số.',
            'mimes' => ':attribute phải là định dạng PDF.',
            'date' => ':attribute không đúng định dạng Y-m-d .'
        ]);

        $validation = $validator->make($_POST + $_FILES, [
            //'invoice_no' => 'required',
            'invoice_date' => 'date:Y-m-d',
            'amount' => 'numeric',
            'description' => 'max:256',
            'file_invoice' => 'uploaded_file|max:2M|mimes:pdf',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $message = [
                'status' => false,
                'message' => $validation->errors->firstOfAll(':message', true)
            ];
        } else {
            $sql = "SELECT d1.id FROM invoices d1 WHERE d1.status=0 AND d1.id='" . $id . "'";
            $this->msg->add("query", $sql);
            $seen_id = $this->appSession->getTier()->getValue($this->msg);

            if ($seen_id == "") {
                $message = [
                    'status' => true,
                    'message' => "Invoice companies không tồn tại."
                ];
            } else {
                $builder = $this->appSession->getTier()->getBuilder("invoices");
                $builder->update("id", str_replace("'", "''", $id));
                $builder->update("write_date", $this->appSession->getTier()->getDateString(), 'f');
                $builder->update("invoice_no", str_replace("'", "''", $invoice_no));
                $builder->update("invoice_date", str_replace("'", "''", $invoice_date));
                $builder->update("amount", str_replace("'", "''", $amount));
                $builder->update("description", str_replace("'", "''", $description));
                // $builder->update("companies_id", str_replace("'", "''", $companies_id));

                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $result = $this->appSession->getTier()->exec($this->msg);
                $invoice_id = $this->update_document_invoices($request, $id);

                if ($result == '1') {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật invoice company thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật invoice company thất bại."
                    ];
                }
            }
        }
        return $this->appSession->getTier()->response($message, $response);
    }

    public function get_invoices_by_id($id, Response $response)
    {
        $sql = "SELECT d1.id, d1.invoice_no, d1.invoice_date, d1.amount, d1.description, d1.companies_id, d2.id AS invoice_id";
        $sql = $sql . " FROM invoices d1";
        $sql = $sql . " LEFT OUTER JOIN document d2 ON(d1.id = d2.rel_id AND d2.document_type_rel='invoice')";
        $sql = $sql . " WHERE d1.id='" . $id . "' AND d1.status=0";
        $sql = $sql . " ORDER BY d1.write_date ASC";

        $this->msg->add("query", $sql);

        $result =  $this->appSession->getTier()->getTable($this->msg);
        $numrows = $result->getRowCount();

        if ($numrows > 0) {

            $row = $result->getRow(0);

            $arr = array();

            $arr['id'] = $row->getString("id");
            $arr['invoice_no'] = $row->getString("invoice_no");
            $arr['invoice_date'] = $row->getString("invoice_date");
            $arr['amount'] = $row->getString("amount");
            $arr['description'] = $row->getString("description");
            $arr['companies_id'] = $row->getString("companies_id");
            $arr['invoice_id'] = $row->getString("invoice_id");
            $arr['link_download_invoice'] = URL . "document/?id=" . $row->getString("invoice_id") . "";
            $data[] = $arr;

            $message = [
                'status' => true,
                'data' => ['invoice' => $arr],
                'message' => "Lấy danh sách company invoice by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Invoice không tồn tại."
            ];
        }

        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function delete_invoices($id, Response $response)
    {
        $message = [];

        $sql = "SELECT d1.id FROM invoices d1 WHERE d1.status = 0 AND d1.id='" . $id . "'";
        $this->msg->add("query", $sql);
        $seen_id = $this->appSession->getTier()->getValue($this->msg);

        if ($seen_id == "") {
            $message = [
                'status' => true,
                'message' => "Invoices không tồn tại."
            ];
        } else {
            $builder = $this->appSession->getTier()->getBuilder("invoices");
            $builder->add("id", $id);
            $builder->add("status", 1);

            $sql = $this->appSession->getTier()->getUpdate($builder);
            $this->msg->add("query", $sql);
            $result = $this->appSession->getTier()->exec($this->msg);

            $message = [
                'status' => true,
                'message' => "Xóa invoice company thành công."
            ];
        }
        $response->setContent(json_encode($message));
        $response->setStatusCode(Response::HTTP_OK);

        // sets a HTTP response header
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function create_document_invoices(Request $request, $rel_id)
    {
        $file = $request->files->get('file_invoice');
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
        $builder->add("document_type_rel", 'invoice');
        $sql = $this->appSession->getTier()->getInsert($builder);
        $this->msg->add("query", $sql);
        $this->appSession->getTier()->exec($this->msg);

        return $file_id;
    }

    public function update_document_invoices(Request $request, $rel_id)
    {

        $sql = "SELECT d1.id FROM document d1 WHERE d1.status=0 AND d1.rel_id='" . $rel_id . "' AND d1.document_type_rel='invoice'";
        $this->msg->add("query", $sql);
        $file_id = $this->appSession->getTier()->getValue($this->msg);

        if ($file_id == "") {
            $message = [
                'status' => true,
                'message' => "Invoice không tồn tại."
            ];
        } else {

            $file = $request->files->get('file_invoice');
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
                $builder->update("document_type_rel", 'invoice');
                $sql = $this->appSession->getTier()->getUpdate($builder);
                $this->msg->add("query", $sql);
                $this->appSession->getTier()->exec($this->msg);

                return $file_id;
            }
        }
    }
    // END INVOICES
}
