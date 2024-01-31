<?php

namespace Digisource\Comments\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Comments\Entities\Comment;
use Digisource\Vendors\Entities\VendorCommissionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CommentsController extends Controller
{
    // START COMMENTS
    public function get_comments(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $p =  $param['p'] ?? 0;
        $ps =  $param['ps'];
        $rel_id = $param['rel_id'];

        $soft_by = $param['soft_by'] ?? "ASC";
        $soft_column = $param['soft_column'] ?? "create_date";

        $result = DB::table('comments')
            ->leftjoin('res_user','comments.create_uid','=','res_user.id')
            ->select("comments.id", "comments.rel_id", "comments.contents", "comments.parent_id","comments.create_uid","res_user.name","res_user.email")
            ->where("comments.rel_id","=", $rel_id)
            ->where("comments.company_id","=", $user->company_id)
            ->where("comments.status","=", 0)
            ->orderBy($soft_column, $soft_by)
            ->paginate($ps, ['*'], 'page', $p);

        $this->addData($result);
        $this->setMessage("Lấy danh sách users thành công.");

        return $this->getResponse();
    }

    public function create_comments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'contents' => 'required|max:10000',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $data = $request->all();
        $user = auth()->user();
        $rel_id = $data['rel_id'];
        $contents = $data['contents'];
        $parent_id = $data['parent_id'];

        $new = new Comment();
        $new->id = uniqid();
        $new->create_uid = $user->id;
        $new->write_uid = $user->id;
        $new->create_date = date('Y-m-d H:i:s');
        $new->write_date = date('Y-m-d H:i:s');
        $new->contents = $contents;
        $new->company_id = $user->company_id;
        $new->parent_id = $parent_id;
        $new->rel_id = $rel_id;
        $new->status = 0;
        $new->save();

        $this->setMessage("Tạo thành công");
        return $this->getResponse();
    }

    public function update_comments(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'contents' => 'max:10000',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }


        $user = auth()->user();
        $data = $request->all();
        $contents = $data['contents'];
        $parent_id = $data['parent_id'];

        $item = Comment::where('status', 0)->where('company_id', $user->company_id)->find($id);

        if ($item == null) {
            $this->setMessage("Vendor commisstion type không tồn tại.");
        } else {
            $item->contents = $contents;
            $item->parent_id = $parent_id;
            $item->save();
        }

        return $this->getResponse();
    }

    public function get_comments_by_id(Request $request, $id)
    {
        $result = DB::table('comments')
            ->leftjoin('res_user','comments.create_uid','=','res_user.id')
            ->select("comments.id", "comments.rel_id", "comments.contents", "comments.parent_id","comments.create_uid","res_user.name","res_user.email")
            ->where("comments.id","=", $id)
            ->where("comments.status","=", 0)
            ->orderBy("create_date")
            ->get();

        $this->addData(['comment' => $result]);
        return $this->getResponse();
    }

    public function delete_comments(Request  $request,$id)
    {
        $com = Comment::findOrFail($id);
        $com->status = 1;
        $com->save();

        $this->setMessage("Xóa thành công");
        return $this->getResponse();
    }
}
