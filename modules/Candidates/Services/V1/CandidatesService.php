<?php

namespace Digisource\Candidates\Services\V1;


use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Contracts\CandidateServiceFactory;
use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;
use Digisource\Candidates\Entities\CandidateFollowedCandidates;
use Digisource\Candidates\Entities\Candidates;
use Digisource\Candidates\Entities\CandidateSkills;
use Digisource\Candidates\Entities\JobCandidates;
use Digisource\Candidates\Repositories\V1\CandidateFollowedRepository;
use Digisource\Candidates\Repositories\V1\CandidatesRepository;
use Digisource\Candidates\Repositories\V1\InterviewsRepository;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;

class CandidatesService implements CandidateServiceFactory
{
    use TraitCandidatesService;

    public CandidatesRepository $candidateRepository;
    public InterviewsRepository $interviewsRepository;
    private CandidateFollowedRepository $candidateFollowedRepository;

    public function __construct(
        CandidateRepositoryFactory $candidateRepository,
        CandidateFollowedRepository $candidateFollowedRepository,
        InterviewsRepositoryFactory $interviewsRepositoryFactory
    ) {
        $this->candidateFollowedRepository = $candidateFollowedRepository;
        $this->candidateRepository = $candidateRepository;
        $this->interviewsRepository = $interviewsRepositoryFactory;
    }


    /**
     * @param $query
     * @param $filterBy
     * @param $sortBy
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function list($query, $filterBy, $sortBy, $page = 1, $pageSize = 10)
    {
        $where = [];
        $sortMap = [
            'id' => 'id',
            'name' => 'last_name',
            'status' => 'status',
        ];

        $sorts = explode('.', $sortBy);
        $field = empty($sorts[0]) ? 'last_name' : $sorts[0];
        $direct = $sorts[1] ?? 'asc';
        $field = Arr::get($sortMap, $field, 'last_name');

        $query = $this->candidateRepository->where($where);

        if (isset($q) && $q) {
            $query->where(
                function ($query) use ($q) {
                    $query->orWhere('phone', 'like', '%' . $q . '%');
                    $query->orWhere('email', 'like', '%' . $q . '%');
                }
            );
        }

        $fieldSort = trim($field);

        $query->select('*');
        $query->orderBy(DB::raw("$fieldSort IS NULL, $fieldSort"), $direct);

        return $query->paginate($pageSize, ['*'], 'page', $page);
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function getDetail($id): mixed
    {
        $candidate = $this->candidateRepository->where('id', $id)->findFirst();
        if (!empty($candidate)) {
            return $candidate;
        }
        return [];
    }

    public function createCandidateInterview($params)
    {
        $candidate_id = $params['candidate_id'];
        $job_id = $params['job_id'];
        $interview_from = $params['interview_from'];
        $interview_to = $params['interview_to'];
        $location = $params['location'];
        $description = $params['description'];
        $title = $params['title'];
        $attendees = $params['attendees'];
        $interview_date = date('Y-m-d H:i:s', strtotime($params['interview_date']));

        $user = auth()->user();

        $id = uniqid();
        $interview = $this->interviewsRepository->create([
                'id' => $id,
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                
                
                'status' => Status::INACTIVE,
                'company_id' => $user->company_id,
                'candidate_id' => $candidate_id,
                'job_id' => $job_id,
                'interview_date' => $interview_date,
                'interview_from' => $interview_from,
                'interview_to' => $interview_to,
                'location' => $location,
                'description' => $description,
                'title' => $title,
                'attendees' => $attendees
            ]
        );
        if ($interview) {
            return [
                'message' => "Tạo candidate interview thành công"
            ];
        } else {
            return new Exception(__("Tạo candidate interview thất bại."), 505);
        }
    }


    // START CANDIDATE
    public function getListCandidate($params)
    {
        $search = Arr::get($params, 'search');
        $start_date = Arr::get($params, 'start_date');
        $end_date = Arr::get($params, 'end_date');
        $location_id = Arr::get($params, 'location_id');
        $in_jobs = Arr::get($params, 'in_jobs');
        $handling_by = Arr::get($params, 'handling_by');
        $followed = Arr::get($params, 'followed') ?? "0";

        $sort_by = Arr::get($params, 'soft_by') ?? "DESC";
        $sort_column = Arr::get($params, 'soft_column') ?? "create_date";
        $page = Arr::get($params, 'p') ?? 0;
        $pageLimit = Arr::get($params, 'ps') ?? 25;
        $user = auth()->user();

        $queryCandidates = Candidates::query()
            ->select(
                'candidates.*',
                'expect_location.name AS expect_location_name',
                'national_country.name AS national_name',
                'sources.name AS source_name',
                'city_address.name AS city_name',
                'district_address.name AS district_name',
                'ward_address.name AS ward_name',
                'candidate_level.name AS candidate_level_name',
                'logo_document.id AS logo_id',
                'portfolio_document.id AS portfolio_id',
                DB::raw('CASE WHEN followed_candidate.candidate_id IS NULL THEN 0 ELSE 1 END AS is_followed')
            )
            ->leftJoin('res_address AS expect_location', 'candidates.expect_location_id', '=', 'expect_location.id')
            ->leftJoin('res_country AS national_country', 'candidates.national_id', '=', 'national_country.id')
            ->leftJoin('sources', 'candidates.source_id', '=', 'sources.id')
            ->where('sources.status', 0)
            ->leftJoin('res_address AS city_address', 'candidates.city_id', '=', 'city_address.id')
            ->leftJoin('res_address AS district_address', 'candidates.district_id', '=', 'district_address.id')
            ->leftJoin('res_address AS ward_address', 'candidates.ward_id', '=', 'ward_address.id')
            ->leftJoin('candidate_level', 'candidates.candidate_level_id', '=', 'candidate_level.id')
            ->where('candidate_level.status', 0)
            ->leftJoin('document AS logo_document', function ($join) {
                $join->on('candidates.id', '=', 'logo_document.rel_id')
                    ->where('logo_document.document_type_rel', '=', 'logo');
            })
            ->leftJoin('document AS portfolio_document', function ($join) {
                $join->on('candidates.id', '=', 'portfolio_document.rel_id')
                    ->where('portfolio_document.document_type_rel', '=', 'portfolio');
            })
            ->leftJoin(
                'candidate_followed_candidates AS followed_candidate',
                'candidates.id',
                '=',
                'followed_candidate.candidate_id'
            )
            ->where('candidates.company_id', $user->company_id)
            ->where('candidates.status', 0);

        if ($followed == "1") {
            $followed_id = $this->findCandidateFollowedId($user);
            $queryCandidates->where('candidate_followed_candidates.status', 0);
            $queryCandidates->where('candidate_followed_candidates.followed_id', $followed_id);
        }

        if ($search) {
            $queryCandidates->where(function ($query) use ($search) {
                $query->where('candidates.first_name', 'like', '%' . $search . '%')
                    ->orWhere('candidates.last_name', 'like', '%' . $search . '%')
                    ->orWhere('candidates.phone', 'like', '%' . $search . '%')
                    ->orWhere('candidates.email', 'like', '%' . $search . '%')
                    ->orWhere('candidates.job_title', 'like', '%' . $search . '%')
                    ->orWhere(
                        DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name)"),
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        if ($start_date && $end_date) {
            $queryCandidates->where(function ($query) use ($start_date, $end_date) {
                $query->where('candidates.create_date', '>=', $start_date)
                    ->where('candidates.create_date', '<=', $end_date);
            });
        }

        if ($location_id) {
            $queryCandidates->whereIn('candidates.expect_location_id', explode(',', $location_id));
        }

        if ($in_jobs != "") {
            $queryCandidates->whereIn('job_candidates.job_id', explode(',', $in_jobs));
        }

        if ($handling_by != "") {
            $queryCandidates->whereIn('candidates.create_uid', explode(',', $handling_by));
        }

        $queryCandidates->orderBy($sort_column, $sort_by);
        $result = $queryCandidates->paginate($pageLimit, ['*'], 'page', $page);

        $data = array();

        foreach ($result as $item) {
            $arr = array();
            $arr_exepect_location = array();
            $arr_national = array();
            $arr_source = array();
            $arr_city = array();
            $arr_district = array();
            $arr_ward = array();
            $arr_candidate_level = array();

            $arr['id'] = $item->id;
            $arr['expect_location_id'] = $item->expect_location_id;
            $arr['first_name'] = $item->first_name;
            $arr['last_name'] = $item->last_name;
            $arr['headline'] = $item->headline;
            $arr['phone'] = $item->phone;
            $arr['email'] = $item->email;
            $arr['birthday'] = $item->birthday;
            $arr['national_id'] = $item->national_id;
            $arr['source_id'] = $item->source_id;
            $arr['city_id'] = $item->city_id;
            $arr['district_id'] = $item->district_id;
            $arr['ward_id'] = $item->ward_id;
            $arr['address'] = $item->address;
            $arr['summary'] = $item->summary;
            $arr['job_title'] = $item->job_title;
            $arr['qualification'] = $item->qualification;
            $arr['current_salary'] = $item->current_salary;
            $arr['expected_salary'] = $item->expected_salary;
            $arr['experience_years'] = $item->experience_years;
            $arr['candidate_level_id'] = $item->candidate_level_id;
            $arr['create_date'] = $item->create_date;

            $arr['personal_link'] = $item->personal_link;
            $arr['additional_projects'] = $item->additional_projects;
            $arr['achievements'] = $item->achievements;
            $arr['award'] = $item->award;
            $arr['relevant_certificates'] = $item->relevant_certificates;
            $arr['ca_references'] = $item->ca_references;
            $arr['portfolio_id'] = $item->portfolio_id;
            $arr['create_uid'] = $item->create_uid;
            $arr['followed_id'] = $item->followed_id;
            $arr['is_followed'] = $item->is_followed;

            $arr_exepect_location['id'] = $item->expect_location_id;
            $arr_exepect_location['name'] = $item->exepect_location_name;
            $arr['exepect_location'] = $arr_exepect_location;

            $arr_national['id'] = $item->national_id;
            $arr_national['name'] = $item->national_name;
            $arr['national'] = $arr_national;

            $arr_source['id'] = $item->source_id;
            $arr_source['name'] = $item->source_name;
            $arr['source'] = $arr_source;

            $arr_city['id'] = $item->city_id;
            $arr_city['name'] = $item->city_name;
            $arr['city'] = $arr_city;

            $arr_district['id'] = $item->district_id;
            $arr_district['name'] = $item->district_name;
            $arr['district'] = $arr_district;

            $arr_ward['id'] = $item->ward_id;
            $arr_ward['name'] = $item->ward_name;
            $arr['ward'] = $arr_ward;

            $arr_candidate_level['id'] = $item->candidate_level_id;
            $arr_candidate_level['name'] = $item->candidate_level_name;
            $arr['candidate_level'] = $arr_candidate_level;

            $arr['logo_id'] = $item->logo_id;

            $result_skills = CandidateSkills::query()
                ->select('candidate_skills.id', 'skills.name', 'candidate_skills.description')
                ->leftJoin('candidates', 'candidate_skills.candidate_id', '=', 'candidates.id')
                ->where('candidates.status', 0)
                ->leftJoin('skills', 'candidate_skills.skill_id', '=', 'skills.id')
                ->where('skills.status', 0)
                ->where('candidate_skills.candidate_id', $arr['id'])
                ->where('candidate_skills.status', 0)
                ->orderBy('candidate_skills.create_date', 'asc')
                ->get();

            $arr_skills = $result_skills->map->only(
                'id',
                'name',
                'description'
            )->all();

            $arr['skills'] = $arr_skills;

            $jobCandidates = JobCandidates::query()
                ->select(
                    'job_candidates.job_id',
                    'job_candidates.candidate_id',
                    'job_candidates.job_stage_id',
                    'jobs.title AS job_name',
                    'job_stages.name AS stage_name',
                    'document.id AS logo_id'
                )
                ->leftJoin('candidates', 'job_candidates.candidate_id', '=', 'candidates.id')
                ->where('candidates.status', 0)
                ->leftJoin('jobs', 'job_candidates.job_id', '=', 'jobs.id')
                ->where('jobs.status', 0)
                ->leftJoin('job_stages', 'job_candidates.job_stage_id', '=', 'job_stages.id')
                ->where('job_stages.status', 0)
                ->leftJoin('document', 'jobs.id', '=', 'document.rel_id')
                ->where('job_candidates.company_id', $user->company_id)
                ->where('job_candidates.status', 0)
                ->where('job_candidates.candidate_id', $arr['id'])
                ->orderBy('job_candidates.create_date', 'asc')
                ->get();


            $arr_jobs = $jobCandidates->map->only(
                'job_id',
                'candidate_id',
                'job_stage_id',
                'job_name',
                'stage_name',
                'logo_id'
            )->all();


            $arr['in_jobs'] = $arr_jobs;

            $arr_user_followed = CandidateFollowedCandidates::query()
                ->select(
                    'candidate_followed_candidates.id',
                    'res_user.user_name',
                    'document.id AS avatar_id',
                    'res_user.id AS user_id'
                )
                ->leftJoin(
                    'candidate_followed',
                    'candidate_followed_candidates.followed_id',
                    '=',
                    'candidate_followed.id'
                )
                ->leftJoin('res_user', 'candidate_followed.user_id', '=', 'res_user.id')
                ->leftJoin('document', 'res_user.id', '=', 'document.rel_id')
                ->where('document.document_type_rel', 'avatar')
                ->where('candidate_followed_candidates.candidate_id', $arr['id'])
                ->where('candidate_followed_candidates.status', 0)
                ->orderBy('candidate_followed_candidates.write_date', 'asc')
                ->get()
                ->map->only('id', 'user_id', 'user_name', 'avatar_id')
                ->all();
            $arr['user_followed'] = $arr_user_followed;

            $data[] = $arr;
        }

        $current = $result->currentPage();
        return [
            "items" => $data,
            "total" => $result->total(),
            "total_page" => $result->lastPage(),
            "first_page" => 1,
            "current_page" => $current,
            "next_page" => $result->hasMorePages() ? $current + 1 : null,
            "prev_page" => $current > 1 ? $current - 1 : null,
            "per_page" => $result->perPage(),
            "from" => $result->firstItem(),
            "to" => $result->lastItem(),
        ];
    }

    public function findCandidateFollowedId($user)
    {
        $candidateFollowed = $this->candidateFollowedRepository
            ->where('status', 0)
            ->where('create_uid', $user->id)
            ->select('id')
            ->findFirst();
        $candidateFollowedId = $candidateFollowed->id ?? '';
        if ($candidateFollowedId == '') {
            $candidateFollowedId = uniqid();
            $candidateFollowed = $this->candidateFollowedRepository->create([
                "id" => $candidateFollowedId,
                "create_uid" => $user->id,
                "write_uid" => $user->id,
                "create_date" => now(),
                "write_date" => now(),
                "status" => 0,
                "company_id" => $user->company_id,
                "user_id" => $user->id
            ]);
        }

        return $candidateFollowedId;
    }

    public function createCandidate(Request $request)
    {
        $fileLogo = $request->file('logo');
        $filePortfolio = $request->file('portfolio');

        $user = auth()->user();
        $data = $request->all();
        $candidateId = uniqid();

        $data['id'] = $candidateId;
        $data['create_uid'] = $user->id;
        $data['write_uid'] = $user->id;
        $data['create_date'] = now();
        $data['write_date'] = now();
        $data['status'] = Status::ACTIVE;
        $data['company_id'] = $user->company_id;
        Arr::forget($data, ['logo', 'portfolio']);

        $candidate = $this->candidateRepository->create($data);

        if ($fileLogo) {
            $logoFileId = Utils::storageFile($fileLogo, Constant::FILE_LOGO, $candidateId, $user->company_id);

            if ($logoFileId instanceof Exception) {
                return [
                    'status' => false,
                    'message' => "Lỗi upload logo"
                ];
            }
        }
        if ($filePortfolio) {
            $portfolioFileId = Utils::storageFile(
                $filePortfolio,
                Constant::FILE_PORTFOLIO,
                $candidateId,
                $user->company_id,
            );

            if ($portfolioFileId instanceof Exception) {
                return [
                    'status' => false,
                    'message' => "Lỗi upload portfolio."
                ];
            }
        }

        if ($candidate) {
            return $candidate;
        } else {
            return new Exception(__("Tạo candidate thất bại."), 109);
        }
    }

    public function updateCandidate(Request $request, $candidateId)
    {
        $fileLogo = $request->file('logo');
        $filePortfolio = $request->file('portfolio');

        $user = auth()->user();
        $data = $request->all();

        $data['write_uid'] = $user->id;
        $data['write_date'] = now();
        Arr::forget($data, ['logo', 'portfolio']);
        $candidate = $this->candidateRepository->update($candidateId, $data);

        if ($fileLogo) {
            $logoFileId = Utils::updateFile($fileLogo, Constant::FILE_LOGO, $candidateId, $user->company_id);

            if ($logoFileId instanceof Exception) {
                return [
                    'status' => false,
                    'message' => __("Lỗi update logo")
                ];
            }
        }

        if ($filePortfolio) {
            $portfolioFileId = Utils::updateFile(
                $filePortfolio,
                Constant::FILE_PORTFOLIO,
                $candidateId,
                $user->company_id,
            );

            if ($portfolioFileId instanceof Exception) {
                return [
                    'status' => false,
                    'message' => __("Lỗi update portfolio.")
                ];
            }
        }

        if ($candidate) {
            return $candidate;
        } else {
            return new Exception(__("Tạo candidate thất bại."), 109);
        }
    }

    public function deleteCandidate($id)
    {
        $seen_id = $this->candidateRepository
            ->select('id')
            ->where('status', Status::ACTIVE)
            ->where('id', $id)
            ->first();

        if (!$seen_id) {
            $message = [
                'message' => __("Candidate không tồn tại.")
            ];
        } else {
            $candidate = $this->candidateRepository->find($id);
            $candidate->status = Status::INACTIVE;
            $candidate->save();

            $message = [
                'message' => __("Xóa candidate thành công.")
            ];
        }
        return $message;
    }

}
