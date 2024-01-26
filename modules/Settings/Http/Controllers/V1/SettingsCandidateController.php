<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Settings\Contracts\SettingLocationServiceFactory;
use Digisource\Settings\Contracts\SettingsCandidateServiceFactory;
use Digisource\Settings\Services\V1\SettingLocationService;
use Digisource\Settings\Services\V1\SettingsCandidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsCandidateController extends Controller
{

    private SettingsCandidateService $settingsCandidateService;

    public function __construct(SettingsCandidateServiceFactory $settingsCandidateServiceFactory)
    {
        $this->settingsCandidateService = $settingsCandidateServiceFactory;
    }

    public function createCandidateSources(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:256',
                Rule::unique('sources')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ]
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $name = $request->get('name');
        $this->data = $this->settingsCandidateService->createCandidateSources($name);
        return $this->getResponse();
    }

    public function getCandidateSources(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCandidateService->getCandidateSources($request->all());
        return $this->getResponse();
    }

    public function updateCandidateSources(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:256'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $name = $request->get('name');
        $this->data = $this->settingsCandidateService->updateCandidateSources($id, $name);
        return $this->getResponse();
    }

    public function getCandidateSourcesById($id)
    {
        $this->data = $this->settingsCandidateService->getCandidateSourcesById($id);
        return $this->getResponse();
    }

    public function deleteCandidateSources($id)
    {
        $res = $this->settingsCandidateService->deleteCandidateSources($id);
        $this->data = $res;
        return $this->getResponse();
    }

    public function createCandidateLevels(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:256',
                Rule::unique('sources')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ]
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $name = $request->get('name');
        $this->data = $this->settingsCandidateService->createCandidateLevels($name);
        return $this->getResponse();
    }

    public function getCandidateLevels(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingsCandidateService->getCandidateLevels($request->all());
        return $this->getResponse();
    }

    public function updateCandidateLevels(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:256'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $name = $request->get('name');
        $this->data = $this->settingsCandidateService->updateCandidateLevels($id, $name);
        return $this->getResponse();
    }

    public function getCandidateLevelsById($id)
    {
        $this->data = $this->settingsCandidateService->getCandidateLevelsById($id);
        return $this->getResponse();
    }

    public function deleteCandidateLevels($id)
    {
        $res = $this->settingsCandidateService->deleteCandidateLevels($id);
        $this->data = $res;
        return $this->getResponse();
    }
}
