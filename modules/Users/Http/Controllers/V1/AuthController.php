<?php

namespace Digisource\Users\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Users\Contracts\UsersServiceFactory;
use Digisource\Users\Services\V1\UsersService;
use Exception;
use Illuminate\Http\Request;
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
        $data = [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 //mention the guard name inside the auth fn
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
