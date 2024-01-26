<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Digisource\Settings\Contracts\SettingsCompanyServiceFactory;
use Digisource\Settings\Services\V1\SettingsCompanyService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsCompanyController extends Controller
{
    private SettingsCompanyService $settingsCompanyService;
    function __construct(SettingsCompanyServiceFactory $settingsCompanyServiceFactory)
    {
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

    public function createCompanyIndustries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->createCompanyIndustries($request->all());
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


    // Source
    public function getCompanySources(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->getCompanySources($request->all());
        return $this->getResponse();
    }

    public function createCompanySources(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->createCompanySources($request->all());
        return $this->getResponse();
    }

    public function updateCompanySources(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:256'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCompanyService->updateCompanySources($request->all(), $id);
        return $this->getResponse();
    }

    public function getCompanySourcesById($id)
    {
        $this->data = $this->settingsCompanyService->getCompanySourcesById($id);
        return $this->getResponse();
    }

    public function deleteCompanySources($id)
    {
        $this->data = $this->settingsCompanyService->deleteCompanySources($id);
        return $this->getResponse();
    }
}
