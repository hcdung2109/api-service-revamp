<?php

namespace Digisource\Notifications\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificationsController extends Controller
{
    public function get_notification_list(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();
        $search = $param['search'] ?? null;
        $fdate = $param['start_date'] ?? null;
        $tdate = $param['end_date'] ?? null;

        $query = DB::table('res_notification as d1')
            ->select('d1.id', 'd1.name', 'd1.description', 'd1.type', 'd1.notification_id', 'd1.create_date')
            ->where(function ($query) {
                $query->where('d1.status', 0)
                    ->orWhere('d1.status', 2);
            })
            ->where('d1.company_id', auth()->user()->company_id);

        if ($fdate) {
            $query->where('d1.create_date', '>=', $fdate);
        }
        if ($tdate) {
            $query->where('d1.create_date', '<=', $tdate);
        }
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('d1.name', 'like', '%' . $search . '%')
                    ->orWhere('d1.description', 'like', '%' . $search . '%');
            });
        }

        $notifications = $query->orderByDesc('d1.create_date')
            ->limit(50)
            ->get();

        $data = $notifications->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'notification_id' => $item->notification_id,
                'create_date' => $item->create_date,
            ];
        });

        $message = [
            'status' => true,
            'data' => ['notifications' => $data],
            'message' => "Lấy danh sách notifications thành công.",
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_notification_detail(Request $request, $id)
    {
        $user = auth()->user();

        $dt = DB::table('res_notification as d1')
            ->select('d1.id', 'd1.name', 'd1.description', 'd1.type', 'd1.notification_id', 'd1.create_date', 'd1.status')
            ->where('d1.id', $id)
            ->where('d1.status', 0)
            ->where('d1.seen', 0)
            ->where('d1.status', '!=', 1)
            ->orderBy('d1.create_date', 'ASC')
            ->get();

        $data = $dt->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type,
                'notification_id' => $item->notification_id,
                'create_date' => $item->create_date,
                'status' => $item->status,
            ];
        });

        DB::table('res_notification')
            ->where('id', $id)
            ->update(['status' => 2]);

        $message = [
            'status' => true,
            'data' => ['notifications' => $data->toArray()],
            'message' => "Lấy danh sách notifications thành công.",
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function post_notification_seen(Request $request, $id)
    {
        $user = auth()->user();

        $seen_id = DB::table('res_notification')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Notification không tồn tại.",
            ];
        } else {
            DB::table('res_notification')
                ->where('id', $id)
                ->update([
                    'seen' => 1,
                    'write_date' => date('Y-m-d H:i:s'),
                ]);

            $message = [
                'status' => true,
                'message' => "Seen notification thành công.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }
}
