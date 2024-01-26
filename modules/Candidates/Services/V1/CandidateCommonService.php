<?php

namespace Digisource\Candidates\Services\V1;


use Digisource\Candidates\Contracts\CandidateCommonServiceFactory;
use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Entities\CandidateFollowed;
use Digisource\Candidates\Entities\CandidateFollowedCandidates;
use Digisource\Candidates\Entities\Candidates;
use Digisource\Candidates\Entities\CandidateSkills;
use Digisource\Candidates\Entities\JobCandidate;
use Digisource\Candidates\Entities\JobCandidates;
use Digisource\Candidates\Entities\JobStage;
use Digisource\Candidates\Entities\Notes;
use Digisource\Candidates\Repositories\V1\CandidatesRepository;
use Digisource\Common\Entities\Documents;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Jobs\Entities\Jobs;
use Digisource\Settings\Contracts\SkillsRepositoryFactory;
use Digisource\Settings\Repositories\V1\SkillsRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Rakit\Validation\Validator;

class CandidateCommonService implements CandidateCommonServiceFactory
{
    private SkillsRepository $skillsRepository;
    private CandidatesRepository $candidateRepository;

    public function __construct(
        CandidateRepositoryFactory $candidateRepositoryFactory,
        SkillsRepositoryFactory $skillsRepositoryFactory,
    ) {
        $this->candidateRepository = $candidateRepositoryFactory;
        $this->skillsRepository = $skillsRepositoryFactory;
    }

    public function getCandidateSkills(array $request)
    {
        $relId = Arr::get($request, 'rel_id');
        $candidateSkills = CandidateSkills::query()
            ->select(
                'candidate_skills.id',
                'candidate_skills.candidate_id',
                'candidate_skills.skill_id',
                'candidate_skills.description',
                'skills.name AS skill_name'
            )
            ->leftJoin('skills', 'candidate_skills.skill_id', '=', 'skills.id')
            ->where('candidate_skills.company_id', auth()->user()->company_id)
            ->where('candidate_skills.status', 0)
            ->where('candidate_skills.candidate_id', $relId)
            ->orderBy('candidate_skills.create_date', 'asc')
            ->get();

        $data = $candidateSkills->map(function ($skill) {
            return [
                'id' => $skill->id,
                'candidate_id' => $skill->candidate_id,
                'skill_id' => $skill->skill_id,
                'description' => $skill->description,
                'skill_name' => $skill->skill_name,
            ];
        })->toArray();

        return $data;
    }

    public function createCandidateSkills(array $request)
    {
        $description = Arr::get($request, 'description');
        $skill_id = Arr::get($request, 'skill_id');
        $rel_id = Arr::get($request, 'rel_id');

        $candidate = $this->candidateRepository->where('status', 0)->where('id', $rel_id)->first();

        if (!$candidate) {
            return new \Exception(__("Ứng viên không tồn tại"), 404);
        }

        $user = auth()->user();
        $candidateSkill = CandidateSkills::create([
            'create_uid' => $user->id,
            'write_uid' => $user->id,
            'status' => 0,
            'company_id' => $user->company_id,
            'candidate_id' => $rel_id,
            'skill_id' => $skill_id,
            'description' => $description,
        ]);
        if ($candidateSkill) {
            return $candidateSkill;
        } else {
            return new Exception(__("Tạo candidate skill thất bại."), 109);
        }
    }

    public function updateCandidateSkills(array $request, $id)
    {
        $candidateSkill = CandidateSkills::where('status', 0)->where('id', $id)->first();

        if (!$candidateSkill) {
            return new Exception(__("Candidate skill không tồn tại."), 109);
        }

        $candidateSkill->update([
            'skill_id' => Arr::get($request,'skill_id'),
            'description' => Arr::get($request,'description')
        ]);

        return $candidateSkill;
    }

    public function getCandidateSkillsById($id)
    {
        $candidateSkill = CandidateSkills::where('status', 0)->where('id', $id)->first();

        if (!$candidateSkill) {
            return new Exception(__("Candidate skill không tồn tại."), 109);
        }

        $data = [
            'id' => $candidateSkill->id,
            'candidate_id' => $candidateSkill->candidate_id,
            'skill_id' => $candidateSkill->skill_id,
            'description' => $candidateSkill->description,
        ];

        return $data;
    }

    public function deleteCandidateSkills($id)
    {
        $candidateSkill = CandidateSkills::where('status', 0)->where('id', $id)->first();
        if (!$candidateSkill) {
            return new Exception(__("Candidate skill không tồn tại."), 109);
        }
        $candidateSkill->delete();
        return [
            'message' => "Xóa candidate skill thành công."
        ];
    }

    // START CANDIDATE NOTES
    public function getCandidateNotes(array $request)
    {
        $relId = Arr::get($request,'rel_id');

        $notes = Notes::query()
            ->select(
                'notes.id',
                'notes.notesable_type',
                'notes.notesable_id',
                'notes.contents',
                'res_user.id AS user_id',
                'res_user.user_name',
                'res_user.name',
                'notes.create_date'
            )
            ->leftJoin('candidates', 'notes.notesable_id', '=', 'candidates.id')
            ->leftJoin('res_user', 'notes.create_uid', '=', 'res_user.id')
            ->where('notes.company_id', auth()->user()->id)
            ->where('notes.status', 0)
            ->where('notes.notesable_id', $relId)
            ->where('notes.notesable_type', 'candidate')
            ->orderBy('notes.create_date', 'asc')
            ->get();

        $data = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'notesable_type' => $note->notesable_type,
                'notesable_id' => $note->notesable_id,
                'contents' => $note->contents,
                'user_id' => $note->user_id,
                'user_name' => $note->user_name,
                'name' => $note->name,
                'create_date' => $note->create_date,
            ];
        })->toArray();

        return $data;
    }


    public function createCandidateNotes(array $request)
    {
        $rel_id = $request['rel_id'];
        $content = $request['contents'];

        $candidate = $this->candidateRepository->where('status', 0)->where('id', $rel_id)->first();

        if (!$candidate) {
            return new \Exception(__("Ứng viên không tồn tại"), 404);
        }

        $user = auth()->user();
        $candidateSkill = CandidateSkills::create([
            'create_uid' => $user->id,
            'write_uid' => $user->id,
            'status' => 0,
            'company_id' => $user->company_id,
            'notesable_type' => 'candidate',
            'notesable_id' => $rel_id,
            'contents' => $content,
        ]);
        if ($candidateSkill) {
            return $candidateSkill;
        } else {
            return new Exception(__("Tạo candidate notes thất bại."), 109);
        }
    }

    public function updateCandidateNotes(array $request, $id)
    {
        $note = Notes::where('status', 0)
            ->where('id', $id)
            ->first();

        if (!$note) {
            return new Exception(__("Candidate note không tồn tại."), 404);
        }

        $note->contents = Arr::get($request, 'contents');
        $note->save();

        return $note;
    }

    public function getCandidateNotesById($id)
    {
        $note = Notes::query()
            ->select(
                'notes.id',
                'notes.notesable_type',
                'notes.notesable_id',
                'notes.contents',
                'res_user.user_name',
                'res_user.name',
                'notes.create_date'
            )
            ->leftJoin('candidates', 'notes.notesable_id', '=', 'candidates.id')
            ->leftJoin('res_user', 'notes.create_uid', '=', 'res_user.id')
            ->where('notes.company_id', auth()->user()->id)
            ->where('notes.status', 0)
            ->where('notes.id', $id)
            ->where('notes.notesable_type', 'candidate')
            ->orderBy('notes.create_date', 'asc')
            ->first();


        if (!$note) {
            return new Exception(__("Candidate note không tồn tại."));
        }

        return [
            'id' => $note->id,
            'notesable_type' => $note->notesable_type,
            'notesable_id' => $note->notesable_id,
            'contents' => $note->contents,
            'user_name' => $note->user_name,
            'name' => $note->name,
            'create_date' => $note->create_date,
        ];
    }

    public function deleteCandidateNotes($id)
    {
        $candidateSkill = Notes::where('status', 0)->where('id', $id)->first();
        if (!$candidateSkill) {
            return new Exception(__("Candidate notes không tồn tại."), 109);
        }
        $candidateSkill->delete();
        return [
            'message' => __("Xóa candidate notes thành công.")
        ];
    }


    // START CANDIDATE RESUME

    public function getCandidateResume(array $request)
    {
        $relId = Arr::get($request, 'rel_id');
        $candidate = $this->candidateRepository->where('status', 0)
            ->where('id', $relId)
            ->first();

        if (!$candidate) {
            return new Exception(__("Candidate không tồn tại."), 109);
        }

        $resumes = Documents::where('status', 0)
            ->where('rel_id', $relId)
            ->where('document_type_rel', Constant::FILE_RESUME)
            ->get();

        $data = $resumes->map(function ($resume) {
            return [
                'id' => $resume->id,
                'name' => $resume->name,
                'type' => $resume->type,
                'rel_id' => $resume->rel_id,
                'status' => $resume->status,
                'path' => $resume->path,
                'ext' => $resume->ext,
                'is_default' => $resume->is_default,
                'link_download' => url($resume->path . $resume->name),  // Tạo link download bằng helper url()
            ];
        })->toArray();

        return $data;
    }

    /**
     * @throws Exception
     */
    public function createCandidateResume(Request $request)
    {
        $relId = $request->get('rel_id');
        $isDefault = $request->get('is_default', 0);
        $resume = $request->file('resume');

        $candidate = $this->candidateRepository->where('status', 0)
            ->where('id', $relId)
            ->first();

        if (!$candidate) {
            return new Exception(__("Candidate không tồn tại."), 109);
        }

        $fileId = Utils::storageFile($resume, Constant::FILE_RESUME, $relId, auth()->user()->company_id, $isDefault);

        if ($fileId) {
            return [
                'status' => true,
                'file_id' => $fileId,
                'message' => "Tạo resume thành công."
            ];
        } else {
            return [
                'status' => false,
                'message' => "Lỗi khi tạo resume."
            ];
        }
    }


    public function deleteCandidateResume($rel_id)
    {
        $resume = Documents::where('status', 0)
            ->where('id', $rel_id)
            ->first();

        if (!$resume) {
            return new Exception(__("Resume không tồn tại."), 109);
        }

        $resume->status = 1;  // Đánh dấu xóa bằng cách cập nhật trạng thái
        $resume->save();

        return $resume;
    }


    public function candidateSortColumn()
    {
        $soft_column = [
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "phone" => "Phone",
            "email" => "Email",
            "job_title" => "Job Title",
            "create_date" => "Create Date",
            "write_date" => "Write Date",
        ];

        return $soft_column;
    }

    // START CANDIDATE SOFT BY

    public function candidateSortBy()
    {
        $soft_by = [
            "ASC" => "ASC",
            "DESC" => "DESC",
        ];
        return $soft_by;
    }


    // START CANDIDATE APPLY TO JOB
    public function getCandidateApplyToJob(array $request)
    {
        $relId = Arr::get($request, 'rel_id');

        $user = auth()->user();
        $candidate = $this->candidateRepository->where('status', 0)
            ->where('id', $relId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$candidate) {
            return new Exception(__("Candidate không tồn tại."), 109);
        }

        $jobApplications = JobCandidates::where('job_candidates.company_id', $user->company_id)
            ->where('job_candidates.status', 0)
            ->where('job_candidates.candidate_id', $relId)
            ->join('jobs', 'job_candidates.job_id', '=', 'jobs.id')
            ->join('job_stages', 'job_candidates.job_stage_id', '=', 'job_stages.id')
            ->leftJoin('document', 'jobs.id', '=', 'document.rel_id')
            ->select(
                'job_candidates.job_id',
                'job_candidates.candidate_id',
                'job_candidates.job_stage_id',
                'jobs.title AS job_name',
                'job_stages.name AS stage_name',
                'document.id AS logo_id'
            )
            ->orderBy('job_candidates.create_date', 'asc')
            ->get();

        $data = $jobApplications->map(function ($jobApplication) {
            return [
                'job_id' => $jobApplication->job_id,
                'candidate_id' => $jobApplication->candidate_id,
                'job_stage_id' => $jobApplication->job_stage_id,
                'job_name' => $jobApplication->job_name,
                'stage_name' => $jobApplication->stage_name,
                'logo_id' => $jobApplication->logo_id,
            ];
        })->toArray();

        return $data;
    }

    // START ADD CANDIDATE APPLY TO JOB
    public function addCandidateApplyToJob(array $data)
    {
        $job = Jobs::where('status', 0)
            ->where('id', $data['job_id'])
            ->first();

        if (!$job) {
            return new Exception(__("Job của bạn không tồn tại."), 109);
        }

        $user = auth()->user();
        $jobCandidate = new JobCandidates();
        $jobCandidate->id = uniqid();
        $jobCandidate->create_uid = $user->id;
        $jobCandidate->write_uid = $user->id;
        $jobCandidate->create_date = now();
        $jobCandidate->write_date = now();
        $jobCandidate->status = 0;
        $jobCandidate->company_id = $user->company_id;
        $jobCandidate->candidate_id = $data['rel_id'];
        $jobCandidate->job_stage_id = $data['job_stage_id'];
        $jobCandidate->job_id = $data['job_id'];
        $jobCandidate->resume_id = $data['resume_id'];
        $jobCandidate->your_expect_salary = $data['your_expect_salary'];
        $jobCandidate->save();

        if ($jobCandidate->id) {
            return $jobCandidate;
        } else {
            return new Exception(__("Tạo job candidate thất bại."), 109);
        }
    }

    public function getStageByJob(array $request)
    {
        $jobId = Arr::get($request, 'job_id');

        $user = auth()->user();
        $job = Jobs::where('status', 0)
            ->where('id', $jobId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$job) {
            return new Exception(__("Job của bạn không tồn tại."), 109);
        }

        $jobStages = JobStage::where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('job_id', $jobId)
            ->orderBy('sequence', 'asc')
            ->get();

        $data = $jobStages->map(function ($jobStage) {
            return [
                'id' => $jobStage->id,
                'name' => $jobStage->name,
                'parent_stage_id' => $jobStage->parent_stage_id,
                'description' => $jobStage->description,
                'sequence' => $jobStage->sequence,
                'is_edit' => $jobStage->is_edit,
                'job_id' => $jobStage->job_id,
            ];
        })->toArray();

        return $data;
    }


    public function findCandidateFollowedId()
    {
        $user = auth()->user();

        $followed = CandidateFollowed::where('status', 0)
            ->where('create_uid', $user->id)
            ->first();
    
        if ($followed == null) {
            $followed = new CandidateFollowed();
            $followed->id = uniqid();
            $followed->create_uid =  $user->id;
            $followed->write_uid =  $user->id;
            $followed->create_date = now();
            $followed->write_date = now();
            $followed->status = 0;
            $followed->company_id = $user->company_id;
            $followed->user_id = $user->id;
            $followed->save();
        }
    
        return $followed->id;
    }

    public function addCandidateFollowed(array $request)
    {
        $candidateId = Arr::get($request,'candidate_id');
        $followedId = $this->findCandidateFollowedId(); // Assuming this method is defined elsewhere
        $user = auth()->user();

        $existingFollow = CandidateFollowedCandidates::where('status', 0)
            ->where('followed_id', $followedId)
            ->where('candidate_id', $candidateId)
            ->first();

        if (!$existingFollow) {
            $existingFollow = new CandidateFollowedCandidates();
            $existingFollow->id = uniqid();
            $existingFollow->followed_id = $followedId;
            $existingFollow->create_uid = $user->id;
            $existingFollow->write_uid = $user->id;
            $existingFollow->create_date = now();
            $existingFollow->write_date = now();
            $existingFollow->candidate_id = $candidateId;
            $existingFollow->status = 0;
            $existingFollow->company_id = $user->company_id;
            $existingFollow->save();
        }

        return $existingFollow;
    }
    public function removeCandidateFollowed($id)
    {
        $followed_id = $this->findCandidateFollowedId();
    
        $followed_candidate = CandidateFollowedCandidates::where('status', 0)
            ->where('candidate_id', $id)
            ->where('followed_id', $followed_id)
            ->first();
    
        if (!$followed_candidate) {
            return new Exception(__("Candidate followed không tồn tại."), 109);

        } else {
            $followed_candidate->status = 1;
            $followed_candidate->write_date = now();
            $followed_candidate->save();
    
            $message = [
                'status' => true,
                'message' => "Xóa candidate followed thành công."
            ];
        }
    
        return $message;
    }

}
