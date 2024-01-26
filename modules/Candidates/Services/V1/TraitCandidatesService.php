<?php

namespace Digisource\Candidates\Services\V1;


use Digisource\Candidates\Contracts\CandidateRepositoryFactory;
use Digisource\Candidates\Contracts\CandidateServiceFactory;
use Digisource\Candidates\Contracts\InterviewsRepositoryFactory;
use Digisource\Candidates\Entities\CandidateFollowed;
use Digisource\Candidates\Entities\Candidates;
use Digisource\Candidates\Entities\ResNotification;
use Digisource\Candidates\Entities\ResStatus;
use Digisource\Candidates\Entities\ResStatusLine;
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
use Rakit\Validation\Validator;

trait TraitCandidatesService
{


    public function parseContent(Collection $dt, string $s)
    {
        if ($dt->isNotEmpty()) {
            foreach ($dt->first() as $key => $value) {
                $s = str_replace("{{$key}}", $value, $s);
            }
        }

        return $s;
    }


    public function doCreate($candidate_id, $table_id, $status_id, $session_user_id, $session_company_id)
    {
        // Tìm trạng thái phù hợp
        $status = ResStatus::query()
            ->where('status', 0)
            ->where('table_id', $table_id)
            ->when($status_id, fn ($query) => $query->where('id', $status_id))
            ->when(!$status_id, fn ($query) => $query->where('sequence', 1))
            ->first();

        if ($status) {
            // Thực thi truy vấn bổ sung nếu có
            $status_description = $status->description;
            if ($status->query) {
                $query = str_replace("{id}", $candidate_id, $status->query);
                $data = DB::select($query);
                if ($data->count()) {
                    $status_description = $this->parseContent($data, $status_description);
                }
            }

            // Tạo dòng trạng thái
            $line_id = ResStatusLine::create([
                'id' =>Str::uuid(),
                'create_uid' => $session_company_id,
                'write_uid' => $session_company_id,
                
                
                'name' => $status->name,
                'status_id' => $status->id,
                'description' => $status_description,
                'rel_id' => $candidate_id,
                'status' => 0,
                'company_id' => $session_company_id,
            ]);

            // Gửi thông báo WebSocket
            $this->sendWebSocketMessage('res_status_line');

            // Tạo thông báo nếu có
            foreach ($status->notifications as $notification) {
                $this->createNotification($notification, $candidate_id);
            }

            return $line_id;
        }

        return '';
    }

    private function createNotification($notification, $rel_id, $dt = null)
    {
        $rel_id = $this->parseContent($dt, $rel_id);
        $name = $this->parseContent($dt, $notification->name);
        $description = $this->parseContent($dt, $notification->description);

//        $nid = ResNotification::create([
//            'id' =>Str::uuid(),
//            'create_uid' => $session_user_id,
//            'write_uid' => $session_user_id,
//            
//            
//            'name' => $name,
//            'rel_id' => $rel_id,
//            'notification_id' => $candidate_id,
//            'description' => $description,
//            'type' => $notification->type,
//            'seen' => 0,
//            'status' => 0,
//            'company_id' => $session_company_id,
//        ]);

        $this->sendWebSocketMessage('res_notification');
    }

    private function sendWebSocketMessage($name)
    {
//        $data = [
//            'action' => '28d9aaeb-9535-4939-9eb6-c88f103cd256',
//            'id' => $session_company_id,
//            'message' => urlencode("type=database.hq.changed&name=$name"),
//        ];
//
//        WebService::sendMessage($data);
    }

}
