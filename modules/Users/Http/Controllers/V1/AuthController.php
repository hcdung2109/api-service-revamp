<?php

namespace Digisource\Users\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Core\Constant\Status;
use Digisource\Users\Contracts\UsersServiceFactory;
use Digisource\Users\Entities\User;
use Digisource\Users\Services\V1\UsersService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JWTAuth;


class AuthController extends Controller
{

    public UsersService $usersService;

    public function __construct(UsersServiceFactory $usersServiceFactory)
    {
        $this->usersService = $usersServiceFactory;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        if (!$token = auth()->attempt($validator->validated())) {
            $this->setException(new Exception("Unauthenticated", 401));
            $this->setStatusCode(401);
            $this->setMessage("Unauthenticated");
            return $this->getResponse();
        }

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        $user = auth()->user();

        $sql = "SELECT d1.id, d1.password, d1.user_name, d1.name, d1.company_id, d1.date_format, d1.thousands_sep, d1.time_format, d1.decimal_point,
                d1.avatar, d1.lang_id,
                d2.group_id AS user_group_id, d4.name AS user_group_name, d5.parent_id AS parent_company_id
                FROM res_user d1
                LEFT OUTER JOIN res_user_company d2 ON(d1.id = d2.user_id AND d2.status =0)
                LEFT OUTER JOIN res_user_group d4 ON(d2.group_id = d4.id)
                LEFT OUTER JOIN res_company d5 ON(d2.company_id = d5.id)
                WHERE d1.id = ? AND d1.status =0 AND d2.status= 0";

        $results = DB::select($sql, [$user->id]);
        $row = $results[0];

        $_user = [
            "date_format" => $row->date_format,
            "thousands_sep" => $row->thousands_sep,
            "decimal_point" => $row->decimal_point,
            "avatar" => $row->avatar,
            "user_id" => $user->id,
            "name" => $user->name,
            "user_name" =>  $user->user_name,
            "company_id" => $user->company_id,
            "user_group_id" => $row->user_group_id,
            "user_group_name" => $row->user_group_name,
            "parent_company_id" => $row->parent_company_id,
            "lang_id" => $row->lang_id,
        ];

        $data = [
            'user' => $_user,
            'token' => $token,
            //'access_token' => $token,
            //'token_type' => 'bearer',
            //'expires_in' => auth('api')->factory()->getTTL() * 60 //mention the guard name inside the auth fn
        ];

        $this->addData($data);
        return $this->getResponse();
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string||regex:/^[a-zA-Z0-9]+$/|between:2,50|unique:res_user',
            'email' => 'required|string|email|max:100|unique:res_user',
            'password' => 'required|string|min:6',
            'type' => 'required|' . Rule::in(['freelancer', 'headhunting_agency', 'hr_internal_team']),
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $params = $request->all();
        $messRes = $this->usersService->createUser($params);
//        $token = JWTAuth::fromUser($user);
        $this->data = [
            'message' => $messRes
        ];
        return $this->getResponse();
    }


    public function active(Request $request, $key)
    {
        $mess = $this->usersService->active($key);
        $this->data = [
            'message' => $mess
        ];

        return '<html lang="en"><head><meta charset="UTF-8"><title>' . $mess . '</title></head><body><h1>' . $mess . '</h1></body></html>';
    }

    public function getaccount(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $this->addData($user);
            return $this->getResponse();
        }

        $this->setException(new Exception(__("Vui lòng đăng nhập"), 401));
        return $this->getResponse();
    }

    public function logout()
    {
        auth()->logout();
        $this->data = ['message' => __('Đặng xuất thành công.')];
        return $this->getResponse();
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * @throws Exception
     */
    public function sendOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }
        $params = $request->all();
        $data = $this->usersService->sendOTP($params);
        $this->data = $data;
        return $this->getResponse();
    }

    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|',
            'code' => 'required|numeric|between:1000,9999'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $params = $request->all();
        $data = $this->usersService->verifyOTP($params);
        $this->data = $data;
        return $this->getResponse();
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verify_id' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            $this->validateFails(new ValidationException($validator));
            return $this->getResponse();
        }

        $params = $request->all();
        $data = $this->usersService->changePassword($params);
        $this->data = $data;
        return $this->getResponse();
    }
}
