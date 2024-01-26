<?php

namespace Digisource\Candidates\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Candidates\Contracts\CandidateCommonServiceFactory;
use Digisource\Candidates\Services\V1\CandidateCommonService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CandidateCommonController extends Controller
{
    private CandidateCommonService $candidateCommonService;

    public function __construct(
        CandidateCommonServiceFactory $candidateCommonService,
    ) {
        $this->candidateCommonService = $candidateCommonService;
    }

    /**
     * Retrieves the skills of a candidate.
     *
     * @param Request $request The HTTP request object.
     * @return Response The HTTP response object.
     */
    public function getCandidateSkills(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $data = $this->candidateCommonService->getCandidateSkills($request->all());
        $this->addData($data);
        return $this->getResponse();
    }

    /**
     * Show the form for creating a new resource.
     * @return JsonResponse
     */
    public function createCandidateSkills(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'description' => 'string',
            'skill_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->createCandidateSkills($request->all());
        return $this->getResponse();
    }

    public function updateCandidateSkills(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'description' => 'string',
            'skill_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->candidateCommonService->updateCandidateSkills($request->all(), $id);
        return $this->getResponse();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function getCandidateSkillsById(Request $request, $id)
    {
        $this->data = $this->candidateCommonService->getCandidateSkillsById($id);
        return $this->getResponse();
    }


    public function deleteCandidateSkills($id)
    {
        $this->data = $this->candidateCommonService->deleteCandidateSkills($id);
        return $this->getResponse();
    }

    // END SETTINGS SKILL

    public function getCandidateNotes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $data = $this->candidateCommonService->getCandidateNotes($request->all());
        $this->addData($data);
        return $this->getResponse();
    }

    /**
     * Show the form for creating a new resource.
     * @return JsonResponse
     */
    public function createCandidateNotes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'description' => 'string',
            'skill_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->createCandidateNotes($request->all());
        return $this->getResponse();
    }

    public function updateCandidateNotes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'contents' => 'string',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $this->data = $this->candidateCommonService->updateCandidateNotes($request->all(), $id);
        return $this->getResponse();
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function getCandidateNotesById(Request $request, $id)
    {
        $this->data = $this->candidateCommonService->getCandidateNotesById($id);
        return $this->getResponse();
    }


    public function deleteCandidateNotes($id)
    {
        $this->data = $this->candidateCommonService->deleteCandidateNotes($id);
        return $this->getResponse();
    }

    // END SETTINGS SKILL


    public function getCandidateResume(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $data = $this->candidateCommonService->getCandidateResume($request->all());
        $this->addData($data);
        return $this->getResponse();
    }

    /**
     * Show the form for creating a new resource.
     * @return JsonResponse
     */
    public function createCandidateResume(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'resume' => 'file|max:5120|mimes:pdf'
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->createCandidateResume($request);
        return $this->getResponse();
    }


    public function deleteCandidateResume($id)
    {
        $this->data = $this->candidateCommonService->deleteCandidateResume($id);
        return $this->getResponse();
    }


    // START CANDIDATE SOFT BY
    public function candidateSortColumn()
    {
        $this->data = $this->candidateCommonService->candidateSortColumn();
        return $this->getResponse();
    }

    public function candidateSortBy()
    {
        $this->data = $this->candidateCommonService->candidateSortBy();
        return $this->getResponse();
    }

    public function getCandidateApplyToJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->getCandidateApplyToJob($request->all());
        return $this->getResponse();
    }

    public function addCandidateApplyToJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'job_id' => 'required',
            'resume_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->addCandidateApplyToJob($request->all());
        return $this->getResponse();
    }


    public function getStageByJob(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->getStageByJob($request->all());
        return $this->getResponse();
    }

    // START CANDIDATE SOFT BY
    public function addCandidateFollowed(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $this->data = $this->candidateCommonService->addCandidateFollowed($request->all());
        return $this->getResponse();
    }


    public function removeCandidateFollowed($id)
    {
        $this->data = $this->candidateCommonService->deleteCandidateResume($id);
        return $this->getResponse();
    }

}
