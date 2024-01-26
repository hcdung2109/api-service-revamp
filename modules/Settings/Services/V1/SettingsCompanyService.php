<?php

namespace Digisource\Settings\Services\V1;

use Digisource\Settings\Contracts\SettingsCompanyServiceFactory;
use Illuminate\Http\Request;
use Digisource\Settings\Entities\Industry;
use Digisource\Settings\Entities\Sources;
use Illuminate\Support\Arr;

class SettingsCompanyService implements SettingsCompanyServiceFactory
{
    public function __construct()
    {
    }

    // START SETTINGS COMPANY INDUSTRIES
    public function getCompanyIndustries(array $request)
    {
        $page = Arr::get($request, 'p', 1);  // Mặc định page = 1
        $perPage = Arr::get($request, 'ps');
        $user = auth()->user();
        $industries = Industry::where('status', 0)
            ->where('company_id', $user->company_id)
            ->orderBy('name', 'asc');

        if ($perPage) {
            $industries = $industries->paginate($perPage, ['*'], 'p', $page);
        } else {
            $industries = $industries->get();
        }

        if ($perPage) {
            $data = [];
            foreach ($industries->items() as $industry) {
                $data[] = [
                    'id' => $industry->id,
                    'name' => $industry->name
                ];
            }
            $current = $industries->currentPage();
            return [
                "items" => $data,
                "total" => $industries->total(),
                "total_page" => $industries->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $industries->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $industries->perPage(),
                "from" => $industries->firstItem(),
                "to" => $industries->lastItem(),
            ];
        } else {
            return $industries->map(function ($industry) {
                return [
                    'id' => $industry->id,
                    'name' => $industry->name
                ];
            })->toArray();;
        }
    }

    public function createCompanyIndustries(array $request)
    {
        $user = auth()->user();
        $industry = Industry::create([
            'create_uid' => $user->id,
            'write_uid' =>  $user->id,
            'status' => 0,
            'company_id' =>  $user->company_id,
            'name' => Arr::get($request, 'name'),
        ]);

        return array_merge([
            'status' => true,
            'message' => 'Tạo company industries thành công.'
        ], $industry->toArray());
    }
    public function updateCompanyIndustries(array $request, $id)
    {
        $name = $request['name'];
        $industry = Industry::where('status', 0)->where('id', $id)->first();

        if ($industry == null) {
            return new \Exception(__('Company industries không tồn tại.'), 404);
        } else {
            $industry->name = $name;
            $industry->save();

            $message = [
                'status' => true,
                'message' => "Cập nhật company industries thành công."
            ];
        }


        return array_merge($message, $industry->toArray());
    }


    public function getCompanyIndustriesById($id)
    {
        $industry = Industry::where('status', 0)->where('id', $id)->first();

        if ($industry == null) {
            return new \Exception(__('Company industries không tồn tại.'), 404);
        } else {
            $data = [
                'id' => $industry->id,
                'name' => $industry->name,
            ];

            $message = [
                'status' => true,
                'data' => ['company_industrie' => $data],
                'message' => "Lấy company industries by id thành công."
            ];
        }

        return $message;
    }

    public function deleteCompanyIndustries($id)
    {
        $industry = Industry::where('status', 0)->where('id', $id)->first();

        if ($industry == null) {
            return new \Exception(__('Company industries không tồn tại.'), 404);
        } else {
            $industry->status = 1;
            $industry->save();

            $message = [
                'status' => true,
                'message' => "Xóa company industries thành công."
            ];
        }

        return $message;
    }


    // END SETTINGS COMPANY INDUSTRIES

    // // START SETTINGS COMPANY SOURCE

    public function getCompanySources(array $request)
    {
        $page = Arr::get($request, 'p', 1);  // Mặc định page = 1
        $perPage = Arr::get($request, 'ps');
        $user = auth()->user();
        $sources = Sources::where('status', 0)
            ->where('company_id', $user->company_id)
            ->orderBy('name', 'asc');

        if ($perPage) {
            $sources = $sources->paginate($perPage, ['*'], 'p', $page);
        } else {
            $sources = $sources->get();
        }

        if ($perPage) {
            $data = [];
            foreach ($sources->items() as $source) {
                $data[] = [
                    'id' => $source->id,
                    'name' => $source->name
                ];
            }
            $current = $sources->currentPage();
            return [
                "items" => $data,
                "total" => $sources->total(),
                "total_page" => $sources->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $sources->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $sources->perPage(),
                "from" => $sources->firstItem(),
                "to" => $sources->lastItem(),
            ];
        } else {
            return $sources->map(function ($source) {
                return [
                    'id' => $source->id,
                    'name' => $source->name
                ];
            })->toArray();;
        }
    }

    public function createCompanySources(array $request)
    {
        $user = auth()->user();
        $industry = Sources::create([
            'create_uid' => $user->id,
            'write_uid' =>  $user->id,
            'status' => 0,
            'company_id' =>  $user->company_id,
            'name' => Arr::get($request, 'name'),
        ]);

        return array_merge([
            'status' => true,
            'data' => ['company_sources' => $industry],
            'message' => 'Tạo company sources thành công.'
        ], $industry->toArray());
    }

    public function updateCompanySources(array $request, $id)
    {
        $companySource = Sources::where('status', 0)->where('id', $id)->first();

        if (!$companySource) {
            return new \Exception(__('Company sources không tồn tại.'), 404);
        } else {
            $companySource->update([
                'name' => Arr::get($request, 'name'),
            ]);

            $message = [
                'status' => true,
                'data' => ['company_source' => $companySource],
                'message' => "Cập nhật company source thành công."
            ];
        }

        return $message;
    }

    public function getCompanySourcesById($id)
    {
        $source = Sources::where('status', 0)->where('id', $id)->first();

        if ($source == null) {
            return new \Exception(__('Company sources không tồn tại.'), 404);
        } else {
            $data = [
                'id' => $source->id,
                'name' => $source->name,
            ];

            $message = [
                'status' => true,
                'data' => ['company_source' => $data],
                'message' => "Lấy company sources by id thành công."
            ];
        }

        return $message;
    }

    public function deleteCompanySources($id)
    {
        $sources = Sources::where('status', 0)->where('id', $id)->first();

        if ($sources == null) {
            return new \Exception(__('Company sources không tồn tại.'), 404);
        } else {
            $sources->status = 1;
            $sources->save();

            $message = [
                'status' => true,
                'message' => "Xóa company sources thành công."
            ];
        }

        return $message;
    }

    // END SETTINGS COMPANY SOURCE

}
