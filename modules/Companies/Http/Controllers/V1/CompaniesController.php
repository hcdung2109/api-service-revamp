<?php

namespace Digisource\Companies\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
}
