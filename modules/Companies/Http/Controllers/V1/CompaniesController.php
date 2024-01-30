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

    public function getCompanyIndustries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->getCompanyIndustries($request->all());
        return $this->getResponse();
    }


    public function createCompanyIndustries(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->createCompanyIndustries($request->all(), $id);
        return $this->getResponse();
    }

    public function updateCompanyIndustries(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->updateCompanyIndustries($request->all(), $id);
        return $this->getResponse();
    }

    public function getCompanyIndustriesById($id)
    {
        $this->data = $this->settingsCompanyService->getCompanyIndustriesById($id);
        return $this->getResponse();
    }

    public function deleteCompanyIndustries($id)
    {
        $this->data = $this->settingsCompanyService->deleteCompanyIndustries($id);
        return $this->getResponse();
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
                $message = [
                    'status' => true,
                    'message' => "Tạo company commissions thành công",
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo company commissions thất bại",
                ];
            }
        }

        $this->addData($message);

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
}
