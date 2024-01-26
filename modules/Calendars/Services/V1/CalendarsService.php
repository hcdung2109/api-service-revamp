<?php

namespace Digisource\Calendars\Services\V1;


use Digisource\Calendars\Contracts\CalendarsServiceFactory;
use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;
use Digisource\Candidates\Repositories\V1\InterviewsRepository;
use Digisource\Common\Entities\Interviews;
use Digisource\Core\Constant\Status;
use Digisource\Jobs\Contracts\JobsRepositoryFactory;
use Digisource\Jobs\Repositories\V1\JobsRepository;
use Illuminate\Support\Facades\DB;

class CalendarsService implements CalendarsServiceFactory
{
    public InterviewsRepository $interviewsRepository;
    protected JobsRepository $jobsRepository;

    public function __construct(
        InterviewsRepositoryFactory $interviewsRepositoryFactory,
        JobsRepositoryFactory $jobsRepositoryFactory
    ) {
        $this->interviewsRepository = $interviewsRepositoryFactory;
        $this->jobsRepository = $jobsRepositoryFactory;
    }

    public function getCalendar()
    {
        $user = auth()->user();
        $result = $this->interviewsRepository->query()->where('interviews.company_id', $user->company_id)
            ->where('interviews.status', Status::UN_PUBLISHED)
            ->leftJoin('candidates', 'interviews.candidate_id', '=', 'candidates.id')
            ->leftJoin('jobs', 'interviews.job_id', '=', 'jobs.id')
            ->select(['interviews.interview_date'])
            ->groupBy('interviews.interview_date')->get();

        $data = [];
        foreach ($result as $item) {
            $arr = array();
            $arr_interviews = array();
            $arr['interview_date'] = $item->interview_date;

            $date = date('Y-m-d', strtotime($item->interview_date));

            $result_interviews = $this->interviewsRepository->query()->where('interviews.company_id', $user->company_id)
                ->where('interviews.status', Status::UN_PUBLISHED)
                ->where('interviews.interview_date', '>=', $date . ' 00:00:00')
                ->where('interviews.interview_date', '<=', $date . ' 23:59:59')
                ->leftJoin('candidates', 'interviews.candidate_id', '=', 'candidates.id')
                ->leftJoin('jobs', 'interviews.job_id', '=', 'jobs.id')
                ->select([
                    'interviews.id',
                    'interviews.candidate_id',
                    'interviews.job_id',
                    'interviews.interview_date',
                    'interviews.location',
                    'interviews.description',
                    'interviews.interview_from',
                    'interviews.interview_to',
                    'interviews.title',
                    'interviews.attendees',
                    'candidates.first_name',
                    'candidates.last_name',
                    'jobs.title as job_title'
                ])
                ->get();

            foreach ($result_interviews as $result_interview) {
                $arr_data = array();
                $arr_data['id'] = $result_interview->id;
                $arr_data['candidate_id'] = $result_interview->candidate_id;
                $arr_data['candidate_first_name'] = $result_interview->first_name;
                $arr_data['candidate_last_name'] = $result_interview->last_name;
                $arr_data['job_id'] = $result_interview->job_id;
                $arr_data['job_title'] = $result_interview->job_title;
                $arr_data['interview_date'] = $result_interview->interview_date;
                $arr_data['location'] = $result_interview->location;
                $arr_data['description'] = $result_interview->description;
                $arr_data['interview_from'] = $result_interview->interview_from;
                $arr_data['interview_to'] = $result_interview->interview_to;
                $arr_data['title'] = $result_interview->title;
                $arr_data['attendees'] = $result_interview->attendees;
                $arr_interviews[] = $arr_data;
            }

            $arr['in_interviews'] = $arr_interviews;
            $data[] = $arr;
        }

        return ['interviews' => $data];
    }
}
