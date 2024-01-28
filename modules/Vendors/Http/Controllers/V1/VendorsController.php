<?php

namespace Digisource\Vendors\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Digisource\Vendors\Contracts\VendorServiceFactory;
use Digisource\Vendors\Entities\Vendor;
use Digisource\Vendors\Services\V1\VendorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Digisource\Core\Constant\Status;
use Illuminate\Support\Facades\DB;

class VendorsController extends Controller
{
    public VendorService $vendorService;

    public function __construct(VendorService $vendorServiceFactory)
    {
        $this->vendorService = $vendorServiceFactory;
    }

    public function getVendor(Request $request)
    {
        $page = $request->get('p', 1);
        $pageSize = $request->get('ps', 10);
        $param = $request->all();
        $data = $this->vendorService->getVendor($param, $page, $pageSize);
        $this->addData($data);

        return $this->getResponse();
    }

    public function getVendorById($uuid)
    {
        $data = $this->vendorService->getVendorById($uuid);
        $this->addData($data);

        return $this->getResponse();
    }

    public function createVendor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_type_id' => 'required',
            'name' => 'required|max:1000',
            'source_id' => 'required',
            'representative' => 'required|max:256',
            'phone' => 'required|numeric|unique:vendors',
            'email' => 'required|email|unique:vendors',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required|max:256',
            // 'logo' => 'required|uploaded_file|max:1M|mimes:jpeg,png',
        ], [
            'vendor_type_id.required' => 'Vendor Type ID không được để trống',
            'name.required' => 'Tên không được để trống',
            'name.max' => 'Tên không được quá 1000 ký tự',
            'source_id.required' => 'Source ID không được để trống',
            'representative.required' => 'Đại diện không được để trống',
            'representative.max' => 'Đại diện không được quá 256 ký tự',
            'phone.required' => 'Số điện thoại không được để trống',
            'phone.numeric' => 'Số điện thoại phải là số',
            'phone.unique' => 'Số điện thoại đã tồn tại',
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',
            'city_id.required' => 'City ID không được để trống',
            'district_id.required' => 'District ID không được để trống',
            'ward_id.required' => 'Ward ID không được để trống',
            'address.required' => 'Địa chỉ không được để trống',
            'address.max' => 'Địa chỉ không được quá 256 ký tự',
        ]);


        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->vendorService->createVendor($request);

        return $this->getResponse();
    }

    public function updateVendor(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'max:10',
            'representative' => 'max:256',
            'phone' => 'numeric',
            'email' => 'email',
            'address' => 'max:256',
        ], [
            'name.max' => 'Tên không được quá 1000 ký tự',
            'representative.max' => 'Đại diện không được quá 256 ký tự',
            'phone.numeric' => 'Số điện thoại phải là số',
            'email.email' => 'Email không hợp lệ',
            'address.max' => 'Địa chỉ không được quá 256 ký tự',
        ]);


        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->vendorService->updateVendor($request->all(), $id);
        return $this->getResponse();
    }

    public function deleteVendor($id)
    {
        $data = $this->vendorService->deleteVendor($id);
        $this->addData($data);

        return $this->getResponse();
    }

    public function get_vendor_notes(Request $request)
    {
        $data = $this->vendorService->get_vendor_notes($request);
        $this->addData($data);

        return $this->getResponse();
    }

    public function create_vendor_notes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'contents' => 'required|max:256',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->all()
            ];
        } else {
            $param = $request->all();
            $rel_id = $param['rel_id'];
            $content = $param['contents'];
            $user = auth()->user();
            $vendorExists = DB::table('vendors')
                ->where('status', 0)
                ->where('id', $rel_id)
                ->exists();

            if (!$vendorExists) {
                $message = [
                    'status' => false,
                    'message' => "Vendor của bạn không tồn tại."
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
                    'notesable_type' => 'vendor',
                    'notesable_id' => $rel_id,
                    'contents' => $content,
                ]);

                if ($id) {
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
        $this->addData($message);

        return $this->getResponse();
    }

    public function update_vendor_notes(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'contents' => 'required|max:256',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max.',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->all()
            ];
        } else {
            $data = $request->request->all();
            $content = $data['contents'];
            $id = $uuid;
            $seen_id = DB::table('notes')
                ->where('status', 0)
                ->where('id', $uuid)
                ->value('id');

            if (!$seen_id) {
                $message = [
                    'status' => false,
                    'message' => "Note không tồn tại."
                ];
            } else {
                $now = date('Y-m-d H:i:s');

                DB::table('notes')
                    ->where('id', $id)
                    ->update([
                        'write_date' => $now,
                        'contents' => $content,
                    ]);

                $result = DB::table('notes')->where('id', $id)->update(['write_date' => $now, 'contents' => $content]);

                if ($result) {
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

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_vendor_notes_by_id($uuid)
    {
        $user = auth()->user();

        $notes = DB::table('notes as d1')
            ->select('d1.id', 'd1.notesable_type', 'd1.notesable_id', 'd1.contents', 'd3.user_name', 'd3.name', 'd1.create_date')
            ->leftJoin('vendors as d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.id', $uuid)
            ->where('d1.notesable_type', 'vendor')
            ->orderBy('d1.create_date', 'ASC')
            ->get();

        if ($notes->isNotEmpty()) {
            $row = $notes->first();

            $arr = [
                'id' => $row->id,
                'notesable_type' => $row->notesable_type,
                'notesable_id' => $row->notesable_id,
                'contents' => $row->contents,
                'user_name' => $row->user_name,
                'name' => $row->name,
                'create_date' => $row->create_date,
            ];

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

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_vendor_notes($id)
    {
        $user = auth()->user();

        $seen_id = DB::table('notes')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => false,
                'message' => "Vendor note không tồn tại."
            ];
        } else {
            DB::table('notes')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa vendor note thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function find_vendor_followed_id()
    {
        $user = auth()->user();
        $user_id = $user->id;

        $followed_id = DB::table('vendor_followed')
            ->where('status', 0)
            ->where('user_id', $user_id)
            ->where('company_id', $user->company_id)
            ->value('id');

        if ($followed_id === null) {
            $followed_id = DB::table('vendor_followed')->insertGetId([
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                'create_date' => date('Y-m-d H:i:s'),
                'write_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'company_id' => $user->company_id,
                'user_id' => $user_id,
            ]);
        }

        return $followed_id;
    }

    public function add_vendor_followed(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $vendor_id = $data['vendor_id'];

        $followed_id = $this->find_vendor_followed_id();

        $followed_company_id = DB::table('vendor_followed_vendors')
            ->where('status', 0)
            ->where('followed_id', $followed_id)
            ->where('vendor_id', $vendor_id)
            ->where('company_id', $user->company_id)
            ->value('id');

        if ($followed_company_id === null) {
            $followed_company_id = uniqid();

            DB::table('vendor_followed_vendors')->insert([
                'id' => $followed_company_id,
                'followed_id' => $followed_id,
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                'create_date' => date('Y-m-d H:i:s'),
                'write_date' => date('Y-m-d H:i:s'),
                'vendor_id' => $vendor_id,
                'status' => 0,
                'company_id' => $user->company_id,
            ]);
        }

        $message = [
            'status' => true,
            'message' => "Thêm vendor vào danh sách theo dõi thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function remove_vendor_followed($id)
    {
        $user = auth()->user();

        $followed_id = $this->find_vendor_followed_id();

        $followed_vendor_id = DB::table('vendor_followed_vendors')
            ->where('status', 0)
            ->where('vendor_id', $id)
            ->where('followed_id', $followed_id)
            ->where('company_id', $user->company_id)
            ->value('id');

        if ($followed_vendor_id === null) {
            $message = [
                'status' => false,
                'message' => "Vendor followed không tồn tại."
            ];
        } else {
            DB::table('vendor_followed_vendors')
                ->where('id', $followed_vendor_id)
                ->update([
                    'status' => 1,
                    'write_date' => date('Y-m-d H:i:s'),
                ]);

            $message = [
                'status' => true,
                'message' => "Xóa vendor followed thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function vendor_soft_column()
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

        $this->addData($message);

        return $this->getResponse();
    }

    public function vendor_soft_by()
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

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_opening(Request $request)
    {
        $param = $request->all();
        $p = $param['p'] ?? 0;
        $ps = $param['ps'] ?? 20;
        $vendor_id = $param['vendor_id'];
        $user = auth()->user();

        // Check if the vendor exists
        $vendorExists = DB::table('vendors')
            ->where('id', $vendor_id)
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->exists();

        if (!$vendorExists) {
            $message = [
                'status' => false,
                'message' => "Vendor của bạn không tồn tại."
            ];
        } else {
            // Build the main query
            $jobsQuery = DB::table('jobs as d1')
                ->leftJoin('job_commissions as d2', 'd1.id', '=', 'd2.rel_id')
                ->leftJoin('departments as d3', 'd1.department_id', '=', 'd3.id')
                ->leftJoin('job_types as d4', 'd1.job_type_id', '=', 'd4.id')
                ->leftJoin('res_address as d5', 'd1.expect_location_id', '=', 'd5.id')
                ->leftJoin('job_categories as d6', 'd1.job_categories_id', '=', 'd6.id')
                ->leftJoin('document as d7', function ($join) {
                    $join->on('d1.id', '=', 'd7.rel_id')
                        ->where('d7.document_type_rel', 'logo');
                })
                ->leftJoin('vendor_followed_vendors as d8', 'd2.vendor_id', '=', 'd8.vendor_id')
                ->leftJoin(DB::raw('(SELECT d9.job_id, COUNT(d10.candidate_id) AS total_candidate FROM job_stages d9 LEFT OUTER JOIN job_candidates d10 ON(d9.id = d10.job_stage_id) GROUP BY d9.job_id) d11'), 'd1.id', '=', 'd11.job_id')
                ->leftJoin('vendors as d12', 'd2.vendor_id', '=', 'd12.id')
                ->where('d1.company_id', $user->company_id)
                ->where('d1.status', 0)
                ->where('d12.id', $vendor_id);

            // Get the total count
            $total = $jobsQuery->count();

            // Perform pagination
            $result = $jobsQuery->paginate($ps, ['*'], 'page', $p);

            $data = array();
            foreach ($result as $item) {
                $arr = array();
                $arr_user_followed = array();

                $arr['id'] = $item->id;
                $arr['title'] = $item->title;
                $arr['total_candidate'] = intval($item->total_candidate);
                $arr['vendor_id'] = $item->vendor_id;

                // Fetch user_followed details
                $result_followed = DB::table('vendor_followed_vendors as d1')
                    ->leftJoin('vendor_followed as d2', 'd1.followed_id', '=', 'd2.id')
                    ->leftJoin('res_user as d3', 'd2.user_id', '=', 'd3.id')
                    ->leftJoin('document as d4', function ($join) {
                        $join->on('d3.id', '=', 'd4.rel_id')
                            ->where('d4.document_type_rel', 'avatar');
                    })
                    ->where('d1.vendor_id', $arr['vendor_id'])
                    ->where('d1.status', 0)
                    ->orderBy('d1.write_date', 'ASC')
                    ->get();

                foreach ($result_followed as $followed) {
                    $arr_followed = array();
                    $arr_followed['id'] = $followed->id;
                    $arr_followed['user_id'] = $followed->id;
                    $arr_followed['user_name'] = $followed->user_name;
                    $arr_followed['avatar_id'] = $followed->avatar_id;
                    $arr_user_followed[] = $arr_followed;
                }

                $arr['user_followed'] = $arr_user_followed;
                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'total' => $total,
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'from' => $result->firstItem(),
                'to' => $result->lastItem(),
                'data' => ['jobs' => $data],
                'message' => "Lấy danh sách job opening thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_vendor_commission(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();
        $p = $data['p'] ?? 0;
        $ps = $data['ps'] ?? 20;
        $vendor_id = $data['vendor_id'];

        $seen_id = DB::table('vendors')
            ->where('id', $vendor_id)
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => false,
                'message' => "Vendor của bạn không tồn tại."
            ];
        } else {
            $result = DB::table('job_commissions as d1')
                ->select('d1.id', 'd3.company_name', 'd2.title AS job_title', 'd1.value', 'd6.amount', 'd5.name AS kpi_name')
                ->leftJoin('jobs as d2', 'd2.id', '=', 'd1.rel_id')
                ->leftJoin('companies as d3', 'd2.companies_id', '=', 'd3.id')
                ->leftJoin('vendor_commission_types as d4', 'd1.commission_type_id', '=', 'd4.id')
                ->leftJoin('vendor_commission_kpis as d5', 'd1.commission_kpi_type_id', '=', 'd5.id')
                ->leftJoin('invoices as d6', 'd3.id', '=', 'd6.companies_id')
                ->where('d1.company_id', $user->company_id)
                ->where('d1.status', 0)
                ->where('d1.vendor_id', $vendor_id)
                ->paginate($ps, ['*'], 'page', $p);

            $data = [];
            foreach ($result as $item) {
                $arr = [];
                $arr['id'] = $item->id;
                $arr['company_name'] = $item->company_name;
                $arr['job_title'] = $item->job_title;
                $arr['value'] = intval($item->value);
                $arr['amount'] = intval($item->amount);
                $arr['kpi_name'] = $item->kpi_name;
                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'from' => $result->firstItem(),
                'to' => $result->lastItem(),
                'data' => ['vendor_commissions' => $data],
                'message' => "Lấy danh sách vendor commissions thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_vendor_main_account(Request $request)
    {
        $user = auth()->user();
        $param = $request->all();
        $vendor_id = $param['vendor_id'];

        $result = DB::table('vendors_account as d1')
            ->leftJoin('res_user as d2', 'd1.user_id', '=', 'd2.id')
            ->leftJoin('document as d3', function ($join) {
                $join->on('d2.id', '=', 'd3.rel_id')
                    ->where('d3.document_type_rel', 'logo');
            })
            ->select('d1.id', 'd2.name', 'd2.email', 'd2.phone', 'd2.position', 'd3.id as logo_id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.vendor_id', $vendor_id)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = $result->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone,
                'position' => $item->position,
                'logo_id' => $item->logo_id,
            ];
        })->toArray();

        $message = [
            'status' => true,
            'data' => ['vendors_account' => $data],
            'message' => "Lấy danh sách vendors account thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_vendor_main_account(Request $request)
    {
        $user = auth()->user();
        $param = $request->all();

        $validator = validator($request->all(), [
            'full_name' => 'required',
            'email' => 'required|max:256',
            'phone' => 'required',
            // 'logo' => 'required|file|max:1024|mimes:jpeg,png',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
            'file' => ':attribute phải là một tệp tin.',
            'mimes' => ':attribute phải có định dạng jpeg hoặc png.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        } else {
            $vendor_id = $param['vendor_id'];

            $full_name = $param['full_name'];
            $email = $param['email'];
            $phone = $param['phone'];
            $position = $param['position'];
            $vendorExists = DB::table('vendors')
                ->where('id', $vendor_id)
                ->where('status', 0)
                ->exists();

            if (!$vendorExists) {
                $message = [
                    'status' => false,
                    'message' => "Vendor của bạn không tồn tại."
                ];
            } else {
                $userExists = DB::table('res_user')
                    ->where('email', $email)
                    ->orWhere('phone', $phone)
                    ->exists();

                if ($userExists) {
                    $message = [
                        'status' => false,
                        'message' => $email . ' hoặc ' . $phone . ' đã tồn tại trên hệ thống.'
                    ];
                } else {
                    // Thêm user mới
                    $user_id = DB::table('res_user')->insertGetId([
                        'id' => uniqid(),
                        'create_uid' => $user->id,
                        'write_uid' => $user->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'write_date' => date('Y-m-d H:i:s'),
                        'status' => 0,
                        'company_id' => $user->company_id,
                        'name' => $full_name,
                        'email' => $email,
                        'phone' => $phone,
                        'position' => $position,
                        'password' => bcrypt(12345678),
                    ]);

                    // Thêm thông tin công ty
                    $company_id = DB::table('res_company')->insertGetId([
                        'id' => uniqid(),
                        'create_uid' => $user->id,
                        'write_uid' => $user->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'write_date' => date('Y-m-d H:i:s'),
                        'status' => 0,
                        'company_id' => $user->company_id,
                        'parent_id' => DB::table('res_company')->where('status', 0)->where('type', 'vendor')->value('id'),
                        'name' => $full_name,
                        'commercial_name' => $full_name,
                        'phone' => $phone,
                        'email' => $email,
                    ]);

                    // Thêm user vào nhóm
                    $res_user_group_id = DB::table('res_user_group')->where('status', 0)->where('company_id', $user->company_id)->where('type', 'vendor')->value('id');
                    DB::table('res_user_company')->insert([
                        'id' => uniqid(),
                        'create_uid' => $user->id,
                        'write_uid' => $user->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'write_date' => date('Y-m-d H:i:s'),
                        'status' => 0,
                        'company_id' => $user->company_id,
                        'user_id' => $user_id,
                        'group_id' => $res_user_group_id,
                    ]);

                    // Thêm thông tin tài khoản công ty
                    $company_account_id = DB::table('vendors_account')->insertGetId([
                        'id' => uniqid(),
                        'create_uid' => $user->id,
                        'write_uid' => $user->id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'write_date' => date('Y-m-d H:i:s'),
                        'status' => 0,
                        'company_id' => $user->company_id,
                        'vendor_id' => $vendor_id,
                        'user_id' => $user_id,
                        'is_main_account' => 1,
                    ]);

                    // Thêm logo
                    if ($request->hasFile('logo')) {
                        $logo_id = Utils::updateFile($request->logo, Constant::FILE_LOGO, $user->id, $user->company_id);
                    }

                    if ($company_account_id) {
                        $arrModuleId = [
                            ["module_id" => "5c74c32c-a1d1-4272-9cfe-0bb4cae5b949", "module_name" => "Dashboard"],
                            ["module_id" => "cc3e072c-8490-49e7-a6f5-926da175031c", "module_name" => "Candidate View"],
                            ["module_id" => "2bf52543-f096-4e1e-f173-6698e2ca1c1e", "module_name" => "Job View"],
                            ["module_id" => "9aae23ae-287e-4221-d34a-2cab3a39a82d", "module_name" => "Company View"],
                            ["module_id" => "aeb2cf62-a9e6-408b-850d-1c03b0147512", "module_name" => "Vendor View"],
                            ["module_id" => "d6daeadc-f7b7-408d-e2f5-7e3b9417582a", "module_name" => "Report View"],
                            ["module_id" => "72fc461c-8003-4204-d08b-42cd18770212", "module_name" => "Subscription"],
                            ["module_id" => "7ae2e3dd-0606-4bb0-e543-d6b2bd21d199", "module_name" => "Settings"],
                        ];

                        foreach ($arrModuleId as $module) {
                            $moduleId = DB::table('ir_module_rel')->insertGetId([
                                'id' => uniqid(),
                                'create_uid' => $user->id,
                                'write_uid' => $user->id,
                                'create_date' => date('Y-m-d H:i:s'),
                                'write_date' => date('Y-m-d H:i:s'),
                                'status' => 0,
                                'company_id' => $user->company_id,
                                'module_id' => $module['module_id'],
                                'rel_id' => $company_account_id,
                            ]);
                        }

                        $message = [
                            'status' => true,
                            'message' => "Tạo vendor main account thành công",
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
        $this->addData($message);

        return $this->getResponse();
    }
}
