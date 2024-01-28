<?php

namespace Digisource\Vendors\Services\V1;

use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Settings\Entities\Address;
use Digisource\Settings\Entities\Country;
use Digisource\Vendors\Entities\Vendor;
use Illuminate\Support\Facades\Auth;
use Digisource\Vendors\Contracts\VendorServiceFactory;
use Digisource\Vendors\Repositories\V1\VendorRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;

class VendorService implements VendorServiceFactory
{
    public VendorRepository $vendorRepository;

    public function __construct(
        VendorRepository $vendorRepository
    ) {
        $this->vendorRepository = $vendorRepository;
    }

    /**
     * @param $query
     * @param $filterBy
     * @param $sortBy
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getVendor($param, $page = 1, $pageSize = 10)
    {
        $user = auth()->user();

        $query = DB::table('vendors as d1')
            ->select(
                'd1.id',
                'd1.vendor_type_id',
                'd1.name',
                'd1.source_id',
                'd1.representative',
                'd1.phone',
                'd1.email',
                'd1.city_id',
                'd1.district_id',
                'd1.ward_id',
                'd1.address',
                'd1.summary',
                'd2.id as vendor_type_id',
                'd2.name as vendor_type_name',
                'd3.id as source_id',
                'd3.name as source_name',
                'd4.id as city_id',
                'd4.name as city_name',
                'd5.id as district_id',
                'd5.name as district_name',
                'd6.id as ward_id',
                'd6.name as ward_name',
                'd7.id as logo_id',
                'd8.followed_id'
            )
            ->leftJoin('vendor_types as d2', 'd1.vendor_type_id', '=', 'd2.id')
            ->leftJoin('sources as d3', 'd1.source_id', '=', 'd3.id')
            ->leftJoin('res_address as d4', 'd1.city_id', '=', 'd4.id')
            ->leftJoin('res_address as d5', 'd1.district_id', '=', 'd5.id')
            ->leftJoin('res_address as d6', 'd1.ward_id', '=', 'd6.id')
            ->leftJoin('document as d7', function ($join) {
                $join->on('d1.id', '=', 'd7.rel_id')->where('d7.document_type_rel', '=', 'logo');
            })
            ->leftJoin('vendor_followed_vendors as d8', function ($join) use ($user) {
                $join->on('d1.id', '=', 'd8.vendor_id')
                    ->where('d8.status', '=', 0)
                    ->where('d8.company_id', '=', $user->company_id);
            })
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0);

        if (!empty($param['followed']) && $param['followed'] == '1') {
            $followed_id = $this->find_vendor_followed_id();
            $query->where('d8.status', 0)->where('d8.followed_id', $followed_id);
        }

        if (!empty($param['search'])) {
            $query->where(function ($q) use ($param) {
                $q->where('d1.name', 'like', '%' . $param['search'] . '%')
                    ->orWhere('d1.phone', 'like', '%' . $param['search'] . '%')
                    ->orWhere('d1.email', 'like', '%' . $param['search'] . '%');
            });
        }

        if (!empty($param['start_date']) || !empty($param['end_date'])) {
            $query->whereBetween('d1.create_date', [$param['start_date'], $param['end_date']]);
        }

        if (!empty($param['location_id'])) {
            $query->where('d1.city_id', $param['location_id']);
        }

        if (!empty($param['handling_by'])) {
            $query->where('d1.create_uid', $param['handling_by']);
        }

        if (!empty($param['in_vendor_types'])) {
            $query->where('d1.vendor_type_id', $param['in_vendor_types']);
        }

        $result = $query->paginate($pageSize, ['*'], 'page', $page);

        foreach ($result as &$vendor) {
            $vendor_id = $vendor->id;
            $followedVendors = DB::table('vendor_followed_vendors as d1')
                ->select('d1.id', 'd3.user_name', 'd1.id as avatar_id', 'd3.id as user_id')
                ->leftJoin('vendor_followed as d2', 'd1.followed_id', '=', 'd2.id')
                ->leftJoin('res_user as d3', 'd2.user_id', '=', 'd3.id')
                ->leftJoin('document as d4', function ($join) {
                    $join->on('d3.id', '=', 'd4.rel_id')
                        ->where('d4.document_type_rel', '=', 'avatar');
                })
                ->where('d1.vendor_id', '=', $vendor_id)
                ->where('d1.status', '=', 0)
                ->orderBy('d1.write_date', 'ASC')
                ->get();

            $vendor->user_followed = $followedVendors;
            $vendor->vendor_type = [
                'id' => $vendor->vendor_type_id,
                'name' => $vendor->vendor_type_name,
            ];
            unset($vendor->vendor_type_id);
            unset($vendor->vendor_type_name);

            $vendor->ward = [
                'id' => $vendor->ward_id,
                'name' => $vendor->ward_name,
            ];
            unset($vendor->ward_id);
            unset($vendor->ward_name);

            // District
            $vendor->district = [
                'id' => $vendor->district_id,
                'name' => $vendor->district_name,
            ];
            unset($vendor->district_id);
            unset($vendor->district_name);

            // City
            $vendor->city = [
                'id' => $vendor->city_id,
                'name' => $vendor->city_name,
            ];
            unset($vendor->city_id);
            unset($vendor->city_name);

            // Source
            $vendor->source = [
                'id' => $vendor->source_id,
                'name' => $vendor->source_name,
            ];
            unset($vendor->source_id);
            unset($vendor->source_name);
        }

        return $result;
    }

    public function getVendorById($uuid)
    {
        $user = auth()->user();

        $query = DB::table('vendors as d1')
            ->select(
                'd1.id',
                'd1.vendor_type_id',
                'd1.name',
                'd1.source_id',
                'd1.representative',
                'd1.phone',
                'd1.email',
                'd1.city_id',
                'd1.district_id',
                'd1.ward_id',
                'd1.address',
                'd1.summary',
                'd2.id as vendor_type_id',
                'd2.name as vendor_type_name',
                'd3.id as source_id',
                'd3.name as source_name',
                'd4.id as city_id',
                'd4.name as city_name',
                'd5.id as district_id',
                'd5.name as district_name',
                'd6.id as ward_id',
                'd6.name as ward_name',
                'd7.id as logo_id',
                'd8.followed_id'
            )
            ->leftJoin('vendor_types as d2', 'd1.vendor_type_id', '=', 'd2.id')
            ->leftJoin('sources as d3', 'd1.source_id', '=', 'd3.id')
            ->leftJoin('res_address as d4', 'd1.city_id', '=', 'd4.id')
            ->leftJoin('res_address as d5', 'd1.district_id', '=', 'd5.id')
            ->leftJoin('res_address as d6', 'd1.ward_id', '=', 'd6.id')
            ->leftJoin('document as d7', function ($join) {
                $join->on('d1.id', '=', 'd7.rel_id')->where('d7.document_type_rel', '=', 'logo');
            })
            ->leftJoin('vendor_followed_vendors as d8', function ($join) use ($user) {
                $join->on('d1.id', '=', 'd8.vendor_id')
                    ->where('d8.status', '=', 0)
                    ->where('d8.company_id', '=', $user->company_id);
            })
            ->where('d1.id', $uuid);
        $vendor = $query->first();

        $vendor_id = $uuid;
        $followedVendors = DB::table('vendor_followed_vendors as d1')
            ->select('d1.id', 'd3.user_name', 'd1.id as avatar_id', 'd3.id as user_id')
            ->leftJoin('vendor_followed as d2', 'd1.followed_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd2.user_id', '=', 'd3.id')
            ->leftJoin('document as d4', function ($join) {
                $join->on('d3.id', '=', 'd4.rel_id')
                    ->where('d4.document_type_rel', '=', 'avatar');
            })
            ->where('d1.vendor_id', '=', $vendor_id)
            ->where('d1.status', '=', 0)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $vendor->user_followed = $followedVendors;

        $vendor->vendor_type = [
            'id' => $vendor->vendor_type_id,
            'name' => $vendor->vendor_type_name,
        ];
        unset($vendor->vendor_type_id);
        unset($vendor->vendor_type_name);

        $vendor->ward = [
            'id' => $vendor->ward_id,
            'name' => $vendor->ward_name,
        ];
        unset($vendor->ward_id);
        unset($vendor->ward_name);

        // District
        $vendor->district = [
            'id' => $vendor->district_id,
            'name' => $vendor->district_name,
        ];
        unset($vendor->district_id);
        unset($vendor->district_name);

        // City
        $vendor->city = [
            'id' => $vendor->city_id,
            'name' => $vendor->city_name,
        ];
        unset($vendor->city_id);
        unset($vendor->city_name);

        // Source
        $vendor->source = [
            'id' => $vendor->source_id,
            'name' => $vendor->source_name,
        ];
        unset($vendor->source_id);
        unset($vendor->source_name);

        return $vendor;
    }

    public function find_vendor_followed_id()
    {
        $user = auth()->user();

        $followedVendorIds = DB::table('vendor_followed as d1')
            ->select('d1.id')
            ->where('d1.status', '=', 0)
            ->where('d1.user_id', '=', $user->id)
            ->where('d1.company_id', '=', $user->company_id)
            ->get()
            ->pluck('id');

        return $followedVendorIds;
    }

    public function createVendor($request)
    {
        $user = auth()->user();
        $id = uniqid();
        $params = $request->all();

        $Vendor = Vendor::create(
            [
                'id' => $id,
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                'create_date' => date('Y-m-d H:i:s'),
                'write_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'company_id' => $user->company_id,
                'name' => $params['name'],
                'vendor_type_id' => $params['vendor_type_id'],
                'source_id' => $params['source_id'],
                'representative' => $params['representative'],
                'phone' => $params['phone'],
                'email' => $params['email'],
                'city_id' => $params['city_id'],
                'district_id' => $params['district_id'],
                'ward_id' => $params['ward_id'],
                'address' => $params['address'],
                'summary' => $params['summary'],
            ]
        );

        if ($request->hasFile('logo')) {
            $logo_id = Utils::updateFile($request->logo, Constant::FILE_LOGO, $user->id, $user->company_id);
        }

        if ($Vendor) {
            return [
                'message' => "Tạo thành công"
            ];
        } else {
            return new Exception(__("Tạo thất bại."), 505);
        }
    }

    public function updateVendor($params, $id)
    {
        $user = auth()->user();

        $vendor = Vendor::find($id);
        $vendor->update(
            [
                'write_uid' => $user->id,
                'write_date' => date('Y-m-d H:i:s'),
                'name' => $params['name'] ?? $vendor->name,
                'vendor_type_id' => $params['vendor_type_id'] ?? $vendor->vendor_type_id,
                'source_id' => $params['source_id'] ?? $vendor->source_id,
                'representative' => $params['representative'] ?? $vendor->representative,
                'phone' => $params['phone'] ?? $vendor->phone,
                'email' => $params['email'] ?? $vendor->email,
                'city_id' => $params['city_id'] ?? $vendor->city_id,
                'district_id' => $params['district_id'] ?? $vendor->district_id,
                'ward_id' => $params['ward_id'] ?? $vendor->ward_id,
                'address' => $params['address'] ?? $vendor->address,
                'summary' => $params['summary'] ?? $vendor->summary,
            ]
        );

        if ($vendor) {
            return [
                'status' => true,
                'message' => "Cập nhật thành công."
            ];
        } else {
            return [
                'status' => false,
                'message' => "Không tìn thấy Vendor"
            ];
        }

        // return new Exception(__("Cập nhật thất bại."), 505);
    }

    public function deleteVendor($id)
    {
        $seen_id = Vendor::query()->select('id')
            ->where('status', 0)
            ->where('id', $id)->first();

        if (!$seen_id) {
            $message = [
                'message' => __("Vendor không tồn tại.")
            ];
        } else {
            $vendor = Vendor::find($id);
            $vendor->status = 1;
            $vendor->save();

            $message = [
                'status' => true,
                'message' => __("Xóa thành công.")
            ];
        }

        return $message;
    }

    public function get_vendor_notes($request)
    {
        $param = $request->all();
        $user = auth()->user();

        $rel_id = $param['rel_id'];

        $query = DB::table('notes AS d1')
            ->select('d1.id', 'd1.notesable_type', 'd1.notesable_id', 'd1.contents', 'd3.id AS user_id', 'd3.user_name', 'd3.name', 'd1.create_date')
            ->leftJoin('vendors AS d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user AS d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.notesable_id', $rel_id)
            ->where('d1.notesable_type', 'vendor')
            ->orderBy('d1.create_date', 'ASC');

        $results = $query->get();

        $data = $results->map(function ($result) {
            return [
                'id' => $result->id,
                'notesable_type' => $result->notesable_type,
                'notesable_id' => $result->notesable_id,
                'contents' => $result->contents,
                'user_id' => $result->user_id,
                'user_name' => $result->user_name,
                'name' => $result->name,
                'create_date' => $result->create_date,
            ];
        })->toArray();

        return $data;
    }
}
