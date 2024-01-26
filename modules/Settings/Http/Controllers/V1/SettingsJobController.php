<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Digisource\Settings\Contracts\SettingsJobServiceFactory;
use Digisource\Settings\Services\V1\SettingsJobService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Digisource\Jobs\Entities\JobType;
use Digisource\Jobs\Entities\JobCategory;
use Digisource\Jobs\Entities\JobSalaryRange;
use Digisource\Settings\Contracts\SettingsJobsServiceFactory;
use Digisource\Settings\Services\V1\SettingsJobsService;

class SettingsJobController extends Controller
{
    private SettingsJobsService $settingsJobService;

    public function __construct(SettingsJobsServiceFactory $settingsJobsServiceFactory)
    {
        $this->settingsJobService = $settingsJobsServiceFactory;
    }

    // START SETTINGS JOB
    public function getJobTypes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->settingsJobService->getJobTypes($request->all());
        return $this->getResponse();
    }

    public function createJobTypes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsJobService->createJobTypes($request->all());
        return $this->getResponse();
    }

    public function updateJobTypes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->settingsJobService->updateJobTypes($request->all(), $id);
        return $this->getResponse();
    }

    public function getJobTypesById($id)
    {
        $this->data = $this->settingsJobService->getJobTypesById($id);
        return $this->getResponse();
    }

    public function deleteJobTypes($id)
    {
        $this->data = $this->settingsJobService->deleteJobTypes($id);
        return $this->getResponse();
    }
    // END SETTINGS JOB

    // START SETTINGS JOB CATEGORY
    public function getJobCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsJobService->getJobCategory($request->all());
        return $this->getResponse();
    }

    public function createJobCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsJobService->createJobCategory($request->all());
        return $this->getResponse();
    }

    public function updateJobCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsJobService->updateJobCategory($request->all(), $id);
        return $this->getResponse();
    }

    public function getJobCategoryById($id)
    {
        $this->data = $this->settingsJobService->getJobCategoryById($id);
        return $this->getResponse();
    }

    public function deleteJobCategory($id)
    {

        $this->data = $this->settingsJobService->deleteJobCategory($id);
        return $this->getResponse();
    }

    // END SETTINGS JOB CATEGORY

    // START SETTINGS SALARY RANGES
    public function getJobSalaryRanges(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->settingsJobService->getJobSalaryRanges($request->all());
        return $this->getResponse();
    }

    public function createJobSalaryRanges(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'salary_from' => 'required|integer',
            'salary_to' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->settingsJobService->createJobSalaryRanges($request->all());
        return $this->getResponse();
    }

    public function updateJobSalaryRanges(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'salary_from' => 'required|integer',
            'salary_to' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsJobService->updateJobSalaryRanges($request->all(), $id);
        return $this->getResponse();
    }

    public function getJobSalaryRangesById($id)
    {
        $this->data = $this->settingsJobService->getJobSalaryRangesById($id);
        return $this->getResponse();
    }

    public function deleteJobSalaryRanges($id)
    {
        $this->data = $this->settingsJobService->deleteJobSalaryRanges($id);
        return $this->getResponse();
    }
    // END SETTINGS SALARY RANGES
}
