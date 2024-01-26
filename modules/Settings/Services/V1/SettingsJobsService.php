<?php

namespace Digisource\Settings\Services\V1;

use Digisource\Settings\Contracts\SettingsJobsServiceFactory;
use Illuminate\Http\Request;
use Digisource\Jobs\Entities\JobType;
use Digisource\Jobs\Entities\JobCategory;
use Digisource\Jobs\Entities\JobSalaryRange;
use Illuminate\Support\Arr;

class SettingsJobsService implements SettingsJobsServiceFactory
{
    public function __construct()
    {
    }
    // START SETTINGS JOB
    public function getJobTypes(array $request)
    {

        $p = Arr::get($request, 'p', 1);  // Mặc định page = 1
        $ps = Arr::get($request, 'ps');
        $user = auth()->user();
        $query = JobType::where('status', 0)
            ->where('company_id', $user->id)
            ->orderBy('name', 'ASC');

        if ($ps) {
            $jobTypes = $query->paginate($ps, ['*'], 'p', $p);
        } else {
            $jobTypes = $query->get(['id', 'name']);
        }

        if ($ps) {
            $data = [];
            foreach ($jobTypes->items() as $jobType) {
                $data[] = [
                    'id' => $jobType->id,
                    'name' => $jobType->name
                ];
            }
            $current = $jobTypes->currentPage();
            return [
                "items" => $data,
                "total" => $jobTypes->total(),
                "total_page" => $jobTypes->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $jobTypes->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $jobTypes->perPage(),
                "from" => $jobTypes->firstItem(),
                "to" => $jobTypes->lastItem(),
            ];
        } else {
            return $jobTypes->map(function ($industry) {
                return [
                    'id' => $industry->id,
                    'name' => $industry->name
                ];
            })->toArray();;
        }
    }

    public function createJobTypes(array $request)
    {
        $user = auth()->user();

        $jobType = JobType::create([
            'create_uid' => $user->id,
            'write_uid' => $user->id,
            'status' => 0,
            'company_id' => $user->company_id,
            'name' => $request['name'],
        ]);

        $message = [
            'status' => true,
            'message' => "Tạo job type thành công."
        ];


        return $message;
    }

    public function updateJobTypes(array $request, $id)
    {
        $jobType = JobType::where('status', 0)->where('id', $id)->first();

        if (!$jobType) {
            $message = [
                'status' => true,
                'message' => "Job type không tồn tại."
            ];
        } else {
            $jobType->update([
                'name' => Arr::get($request, 'name')
            ]);
            $message = [
                'status' => true,
                'message' => "Cập nhật job type thành công."
            ];
        }
        return $message;
    }

    public function getJobTypesById($id)
    {
        $jobType = JobType::where('status', 0)->where('id', $id)->first();

        if (!$jobType) {
            $message = [
                'status' => true,
                'message' => "Job type không tồn tại."
            ];
        } else {
            $data = [
                'id' => $jobType->id,
                'name' => $jobType->name,
            ];

            $message = [
                'status' => true,
                'data' => ['job_types' => $data],
                'message' => "Lấy job type by id thành công."
            ];
        }

        return $message;
    }

    public function deleteJobTypes($id)
    {
        $jobType = JobType::where('status', 0)->where('id', $id)->first();

        if (!$jobType) {
            $message = [
                'status' => true,
                'message' => "Job types không tồn tại."
            ];
        } else {
            $jobType->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job type thành công."
            ];
        }

        return $message;
    }
    // END SETTINGS JOB

    // START SETTINGS JOB CATEGORY
    public function getJobCategory(array $request)
    {
        $p =  Arr::get($request, 'p', 0);
        $ps =  Arr::get($request, 'ps');
        $user = auth()->user();

        $jobCategories = JobCategory::where('status', 0)
            ->where('company_id', $user->company_id)
            ->orderBy('name', 'ASC');

        if ($ps != "") {
            $jobCategories = $jobCategories->paginate($ps, ['*'], 'p', $p);
            $data = [];
            foreach ($jobCategories->items() as $jobCategorie) {
                $data[] = [
                    'id' => $jobCategorie->id,
                    'name' => $jobCategorie->name
                ];
            }
            $current = $jobCategories->currentPage();
            return [
                "items" => $data,
                "total" => $jobCategories->total(),
                "total_page" => $jobCategories->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $jobCategories->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $jobCategories->perPage(),
                "from" => $jobCategories->firstItem(),
                "to" => $jobCategories->lastItem(),
            ];
        } else {
            $jobCategories = $jobCategories->get(['id', 'name']);
            return $jobCategories->map(function ($jobCategorie) {
                return [
                    'id' => $jobCategorie->id,
                    'name' => $jobCategorie->name
                ];
            })->toArray();;
        }
    }

    public function createJobCategory(array $request)
    {
        $user = auth()->user();

        $jobCategory = JobCategory::create([
            'create_uid' => $user->id,
            'write_uid' => $user->id,
            'status' => 0,
            'company_id' => $user->company_id,
            'name' =>  Arr::get($request, 'name'),
        ]);

        if ($jobCategory) {
            $message = [
                'status' => true,
                'data' => ['job_category' => $jobCategory],
                'message' => "Tạo job categories thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Tạo job categories thất bại."
            ];
        }
        return $message;
    }

    public function updateJobCategory(array $request, $id)
    {
        $jobCategory = JobCategory::where('status', 0)->where('id', $id)->first();

        if (!$jobCategory) {
            $message = [
                'status' => true,
                'message' => "Job categories không tồn tại."
            ];
        } else {
            $jobCategory->update([
                'name' => Arr::get($request, 'name'),
            ]);

            $message = [
                'status' => true,
                'data' => ['job_category' => $jobCategory],
                'message' => "Cập nhật job categories thành công."
            ];
        }

        return $message;
    }
    public function getJobCategoryById($id)
    {
        $jobCategory = JobCategory::find($id);

        if (!$jobCategory) {
            return [
                'status' => true,
                'message' => "Job categories không tồn tại."
            ];
        }

        return [
            'status' => true,
            'data' => ['job_types' => $jobCategory],
            'message' => "Lấy job categories by id thành công."
        ];
    }

    public function deleteJobCategory($id)
    {
        $jobCategory = JobCategory::find($id);

        if (!$jobCategory) {
            return [
                'status' => true,
                'message' => "Job categories không tồn tại."
            ];
        }

        $jobCategory->status = 1;
        $jobCategory->save();

        return [
            'status' => true,
            'message' => "Xóa job categories thành công."
        ];
    }
    // END SETTINGS JOB CATEGORY

    // START SETTINGS SALARY RANGES
    public function getJobSalaryRanges(array $request)
    {
        $p = Arr::get($request, 'p', 0);
        $ps = Arr::get($request, 'ps');
        $user = auth()->user();

        $query = JobSalaryRange::where('status', 0)
            ->where('company_id', $user->company_id)
            ->orderBy('create_date', 'ASC');

        if ($ps) {
            $jobSalaryRanges = $query->paginate($ps, ['*'], 'p', $p);
        } else {
            $jobSalaryRanges = $query->get(['id', 'salary_from', 'salary_to']);
        }

        if ($ps) {
            $data = [];
            foreach ($jobSalaryRanges->items() as $jobSalaryRange) {
                $data[] = [
                    'id' => $jobSalaryRange->id,
                    'salary_from' => $jobSalaryRange->salary_from,
                    'salary_to' => $jobSalaryRange->salary_to,
                ];
            }
            $current = $jobSalaryRanges->currentPage();
            return [
                "items" => $data,
                "total" => $jobSalaryRanges->total(),
                "total_page" => $jobSalaryRanges->lastPage(),
                "first_page" => 1,
                "current_page" => $current,
                "next_page" => $jobSalaryRanges->hasMorePages() ? $current + 1 : null,
                "prev_page" => $current > 1 ? $current - 1 : null,
                "per_page" => $jobSalaryRanges->perPage(),
                "from" => $jobSalaryRanges->firstItem(),
                "to" => $jobSalaryRanges->lastItem(),
            ];
        } else {
            return $jobSalaryRanges->map(function ($jobSalaryRange) {
                return [
                    'id' => $jobSalaryRange->id,
                    'salary_from' => $jobSalaryRange->salary_from,
                    'salary_to' => $jobSalaryRange->salary_to,
                ];
            })->toArray();;
        }
    }

    public function createJobSalaryRanges(array $request)
    {
        $user = auth()->user();
        $jobSalaryRange = JobSalaryRange::create([
            'id' => $user->id,
            'create_uid' => $user->id,
            'write_uid' => $user->id,
            'status' => 0,
            'company_id' => $user->company_id,
            'salary_from' => Arr::get($request, 'salary_from'),
            'salary_to' => Arr::get($request, 'salary_to'),
        ]);

        return [
            'status' => true,
            'data' => ['job_salary_range' => $jobSalaryRange],
            'message' => "Tạo job salary ranges thành công."
        ];
    }

    public function updateJobSalaryRanges(array $request, $id)
    {

        $jobSalaryRange = JobSalaryRange::find($id);

        if (!$jobSalaryRange) {
            return [
                'status' => true,
                'message' => "Job salary ranges không tồn tại."
            ];
        }

        $jobSalaryRange->salary_from = Arr::get($request, 'salary_from');
        $jobSalaryRange->salary_to = Arr::get($request, 'salary_to');
        $jobSalaryRange->save();

        return [
            'status' => true,
            'data' => ['job_salary_range' => $jobSalaryRange],
            'message' => "Cập nhật job salary ranges thành công."
        ];
    }

    public function getJobSalaryRangesById($id)
    {
        $jobSalaryRange = JobSalaryRange::find($id);

        if (!$jobSalaryRange) {
            return [
                'status' => true,
                'message' => "Job salary ranges không tồn tại."
            ];
        }

        return [
            'status' => true,
            'data' => ['job_salary_range' => $jobSalaryRange],
            'message' => "Lấy job salary ranges by id thành công."
        ];
    }

    public function deleteJobSalaryRanges($id)
    {
        $jobSalaryRange = JobSalaryRange::find($id);

        if (!$jobSalaryRange) {
            return [
                'status' => true,
                'message' => "Job salary ranges không tồn tại."
            ];
        }

        $jobSalaryRange->status = 1;
        $jobSalaryRange->save();

        return [
            'status' => true,
            'message' => "Xóa job salary ranges thành công."
        ];
    }
}
