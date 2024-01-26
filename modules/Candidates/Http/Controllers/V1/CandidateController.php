<?php

namespace Digisource\Candidates\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Candidates\Contracts\CandidateServiceFactory;
use Digisource\Candidates\Services\V1\CandidatesService;
use Digisource\Settings\Contracts\SettingGlobalServiceFactory;
use Digisource\Settings\Contracts\SettingLocationServiceFactory;
use Digisource\Settings\Contracts\SettingsCandidateServiceFactory;
use Digisource\Settings\Services\V1\SettingGlobalService;
use Digisource\Settings\Services\V1\SettingLocationService;
use Digisource\Settings\Services\V1\SettingsCandidateService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CandidateController extends Controller
{
    protected CandidatesService $candidateService;
    private SettingLocationService $settingLocationSerivce;
    private SettingGlobalService $settingGlobalService;
    private SettingsCandidateService $settingsCandidateService;

    public function __construct(
        CandidateServiceFactory $candidateService,
        SettingGlobalServiceFactory $settingGlobalServiceFactory,
        SettingLocationServiceFactory $settingLocationServiceFactory,
        SettingsCandidateServiceFactory $settingsCandidateServiceFactory
    ) {
        $this->settingGlobalService = $settingGlobalServiceFactory;
        $this->candidateService = $candidateService;
        $this->settingLocationSerivce = $settingLocationServiceFactory;
        $this->settingsCandidateService = $settingsCandidateServiceFactory;

    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $filter_type = $request->get('type', '');
        $filter_status = $request->get('status', '');
        $filter = [
            'type' => $filter_type,
            'status' => $filter_status

        ];
        $sort = $request->get('sort', 'first_name.asc');
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 10);
        $data = $this->candidateService->list($query, $filter, $sort, $page, $pageSize);
        $this->addData($data);
        return $this->getResponse();
    }


    /**
     * Show the form for creating a new resource.
     * @return JsonResponse
     */
    public function createCandidateInterview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required',
            'job_id' => 'required',
            'interview_date' => 'required',
            'interview_from' => 'required',
            'interview_to' => 'required',
            'location' => 'required',
            'description' => 'required',
            'title' => 'required|max:1000',
            'attendees' => 'required|max:1000',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateService->createCandidateInterview($request->all());
        return $this->getResponse();
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function createCandidate(Request $request): JsonResponse
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'expect_location_id' => 'required',
            'first_name' => 'required|max:256',
            'last_name' => 'required|max:256',
            'headline' => 'required|max:256',
            'phone' => 'required|' . Rule::unique('candidates')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                }),
            'email' => [
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('candidates')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'birthday' => 'required|date:Y-m-d',
            'national_id' => 'required',
            'source_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
            'ward_id' => 'required',
            'address' => 'required|max:256',
            //'summary' => 'max:1000',
            'job_title' => 'required|max:256',
            'qualification' => 'required|integer',
            'current_salary' => 'required|integer',
            'expected_salary' => 'required|integer',
            'experience_years' => 'required|integer',
            'candidate_level_id' => 'required',
            'logo' => 'file|max:3096|mimes:jpeg,png,jpg,svg',
            'portfolio' => 'file|max:5120|mimes:jpeg,png,jpg,pdf'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->candidateService->createCandidate($request);
        return $this->getResponse();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function getCandidateById(Request $request, $id)
    {
        $data = $this->candidateService->getDetail($id);
        $this->addData($data);
        return $this->getResponse();
    }


    public function getCandidate(Request $request)
    {
        $data = $this->candidateService->getListCandidate($request->all());
        $this->addData($data);
        return $this->getResponse();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return $this->getResponse();
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function updateCandidate(Request $request, $id)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'first_name' => 'max:256',
            'last_name' => 'max:256',
            'headline' => 'max:256',
            'phone' => Rule::unique('candidates')->where(function ($query) use ($user) {
                return $query->where('company_id', $user->company_id);
            }),
            'email' => [
                'string',
                'email',
                'max:191',
                Rule::unique('candidates')->where(function ($query) use ($user) {
                    return $query->where('company_id', $user->company_id);
                })
            ],
            'birthday' => 'date:Y-m-d',
            'address' => 'max:256',
            'job_title' => 'max:256',
            'qualification' => 'integer',
            'current_salary' => 'integer',
            'expected_salary' => 'integer',
            'experience_years' => 'integer',
            'logo' => 'file|max:3096|mimes:jpeg,png,jpg,svg',
            'portfolio' => 'file|max:5120|mimes:jpeg,png,jpg,pdf'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $this->candidateService->updateCandidate($request, $id);
        $this->addData($data);
        return $this->getResponse();
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $data = $this->candidateService->deleteCandidate($id);
        $this->addData($data);
        return $this->getResponse();
    }

    /*
     *
     */
    public function getExpectLocation(Request $request)
    {
        $countryId = $request->get('country_id', null);
        $this->data = $this->settingLocationSerivce->getCities($countryId);
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
}
