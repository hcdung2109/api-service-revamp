<?php

namespace Digisource\Settings\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Settings\Contracts\SettingGlobalServiceFactory;
use Digisource\Settings\Entities\Skill;
use Digisource\Settings\Services\V1\SettingGlobalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsGlobalController extends Controller
{
    private SettingGlobalService $settingGlobalService;

    public function __construct(SettingGlobalServiceFactory $settingGlobalServiceFactory)
    {
        $this->settingGlobalService = $settingGlobalServiceFactory;
    }

// START SETTINGS SKILL

    public function createSkills(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:256',
                Rule::unique('skills')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ]
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $name = $request->get('name');
        $this->data = $this->settingGlobalService->createSkills($name);
        return $this->getResponse();
    }

    public function getSkills(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'p' => 'numeric',
            'ps' => 'numeric'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->settingGlobalService->getSkills($request->all());
        return $this->getResponse();
    }

    public function updateSkills(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:256'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $name = $request->get('name');
        $this->data = $this->settingGlobalService->updateSkills($id, $name);
        return $this->getResponse();
    }

    public function getSkillsById($id)
    {
        $this->data = $this->settingGlobalService->getSkillById($id);
        return $this->getResponse();
    }

    public function deleteSkills($id)
    {
        $res = $this->settingGlobalService->deleteSkills($id);
        $this->data = $res;
        return $this->getResponse();
    }
    // END SETTINGS SKILL

}
