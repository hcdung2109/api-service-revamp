<?php

namespace Digisource\Users\Services\V1;


use Digisource\Companies\Contracts\ResCompanyRepositoryFactory;
use Digisource\Companies\Repositories\V1\ResCompanyRepository;
use Digisource\Core\Constant\Constant;
use Digisource\Core\Constant\Status;
use Digisource\Organizations\Contracts\OrganizationsRepositoryFactory;
use Digisource\Organizations\Repositories\V1\OrganizationsRepository;
use Digisource\Users\Contracts\IrModuleRelRepositoryFactory;
use Digisource\Users\Contracts\IrModuleServiceFactory;
use Digisource\Users\Contracts\ResUserCompanyRepositoryFactory;
use Digisource\Users\Contracts\ResUserGroupRepositoryFactory;
use Digisource\Users\Contracts\ResUserVerificationRepositoryFactory;
use Digisource\Users\Contracts\UsersRepositoryFactory;
use Digisource\Users\Contracts\UsersServiceFactory;
use Digisource\Users\Entities\User;
use Digisource\Users\Repositories\V1\IrModuleRelRepository;
use Digisource\Users\Repositories\V1\ResUserCompanyRepository;
use Digisource\Users\Repositories\V1\ResUserGroupRepository;
use Digisource\Users\Repositories\V1\ResUserVerificationRepository;
use Digisource\Users\Repositories\V1\UsersRepository;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UsersService implements UsersServiceFactory
{
    public UsersRepository $usersRepository;
    public IrModuleService $irModuleService;
    public IrModuleRelRepository $irModuleRelRepository;
    private ResUserGroupRepository $resUserGroupRepository;
    private ResCompanyRepository $resCompanyRepository;

    private OrganizationsRepository $organizationsRepository;
    private ResUserCompanyRepository $resUserCompanyRepository;
    private ResUserVerificationRepository $resUserVerificationRepository;

    public function __construct(
        UsersRepositoryFactory $usersRepository,
        IrModuleServiceFactory $moduleServiceFactory,
        IrModuleRelRepositoryFactory $irModuleRelRepository,
        ResUserGroupRepositoryFactory $resUserGroupRepositoryFactory,
        ResCompanyRepositoryFactory $resCompanyRepositoryFactory,
        ResUserCompanyRepositoryFactory $resUserCompanyRepositoryFactory,
        ResUserVerificationRepositoryFactory $resUserVerificationRepositoryFactory,
        OrganizationsRepositoryFactory $organizationsRepositoryFactory
    ) {
        $this->usersRepository = $usersRepository;
        $this->resUserGroupRepository = $resUserGroupRepositoryFactory;
        $this->irModuleService = $moduleServiceFactory;
        $this->irModuleRelRepository = $irModuleRelRepository;
        $this->resCompanyRepository = $resCompanyRepositoryFactory;
        $this->organizationsRepository = $organizationsRepositoryFactory;
        $this->resUserCompanyRepository = $resUserCompanyRepositoryFactory;
        $this->resUserVerificationRepository = $resUserVerificationRepositoryFactory;
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

        $query = $this->usersRepository->where($where);

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
        $user = $this->usersRepository->where('id', $id)->findFirst();
        if (!empty($user)) {
            return $user;
        }
        return [];
    }


    public function createUser(array $params)
    {
        if ($params['type'] == 'hr_internal_team') {
            $groupType = Constant::HRINTERNALTEAM;
        } elseif ($params['type'] == 'headhunting_agency') {
            $groupType = Constant::HEADHUNTAGENCY;
        } else {
            $groupType = Constant::FREELANCER;
        }
        $commercial_name = $params['commercial_name'];
        $name = $params['name'];
        $phone = $params['phone'];
        $email = $params['email'];
        $user_name = $params['user_name'];
        $password = $params['password'];
        $lang_id = 'vi';

        $user_id = uniqid();
        $password = Hash::make($password);
        $company_id = uniqid();

        $now = now();
        // create res user
        $userInsert = [
            'id' => $user_id,
            'company_id' => $company_id,
            'status' => Status::UN_PUBLISHED,
            'name' => $name,
            'user_name' => $user_name,
            'password' => $password,
            'email' => $email,
            'phone' => $phone,
            'thousands_sep' => '',
            'decimal_point' => '',
            'date_format' => 'YYYY-MM-DD',
            'create_date' => $now,
            'write_date' => $now,
            'create_uid' => $user_id,
            'write_uid' => $user_id,
            'lang_id' => $lang_id,
            'actived' => Status::UN_PUBLISHED

        ];
        $user = $this->usersRepository->create($userInsert);
//         create res company root
        $resCompany = [
            "id" => $company_id,
            "company_id" => $company_id,
            "parent_id" => 'ROOT',
            "status" => Status::UN_PUBLISHED,
            "name" => $name,
            "commercial_name" => $commercial_name,
            "phone" => $phone,
            "email" => $email,
            "create_date" => now(),
            "write_date" => now(),
            "create_uid" => $user_id,
            "write_uid" => $user_id,
        ];
        $this->resCompanyRepository->create($resCompany);

        // res user company
        $res_user_company = [
            "id" => $user_id,
            "company_id" => $company_id,
            "status" => Status::UN_PUBLISHED,
            "user_id" => $user_id,
            "group_id" => $groupType,
            "create_date" => now(),
            "write_date" => now(),
            "create_uid" => $user_id,
            "write_uid" => $user_id
        ];
        $this->resUserCompanyRepository->create($res_user_company);

        if ($user) {
            $res = $this->irModuleService->listDefaultIrModule();
            $arr_module_id = $res->pluck('id')->toArray();

            foreach ($arr_module_id as $item) {
                $permission = [
                    "id" => uniqid(),
                    "create_uid" => '1605bf23-e87c-4c3f-fd59-a7867e428653',
                    "write_uid" => '1605bf23-e87c-4c3f-fd59-a7867e428653',
                    "create_date" => now(),
                    "write_date" => now(),
                    "status" => Status::UN_PUBLISHED,
                    "company_id" => $company_id,
                    "module_id" => str_replace("'", "''", $item),
                    "rel_id" => str_replace("'", "''", $company_id)
                ];

                $this->irModuleRelRepository->create($permission);
            }

            $this->create_user_group($company_id, $user_id, $user_id);
            $this->createCompanyVendorCompany($company_id, $user_id, $user_id);
            $this->create_org_chart($company_id, $user_id, $user_id, $user_id, $email, $phone);
            $messRes = $this->sendEmailActive($user);
        }
        return $messRes;
    }

    /**
     * @param User $user
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Translation\Translator|\Illuminate\Foundation\Application|string|null
     */
    private function sendEmailActive(User $user)
    {
        $key = $user->id . '|' . $user->write_date;
        $activationLink = config('app.url') . '/active/' . base64_encode($key);
        // Gửi email
        $res = Mail::send('account-activation', [
            'username' => $user->name,
            'link' => $activationLink
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject(__('Kích hoạt tài khoản'));
        });
        if ($res instanceof SentMessage) {
            return __(
                ":name đã gửi link kích hoạt cho bạn. Vui lòng kiểm tra email :mail",
                ['name' => config('app.name'), 'mail' => $user->email]
            );
        } else {
            return __("Gửi mã OTP kích hoạt không thành công. Liên hệ info@digisource.vn để được hỗ trợ");
        }
    }

    /**
     * @param $company_id
     * @param $create_uid
     * @param $write_uid
     * @return bool|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function create_user_group($company_id, $create_uid, $write_uid)
    {
        $arr_group_id = [
            ["group_name" => "Vendor", "type" => "vendor"],
            ["group_name" => "Company", "type" => "company"],
        ];

        foreach ($arr_group_id as $item) {
            $user_group_id = uniqid();

            $res_user_group = [
                "id" => $user_group_id,
                "create_uid" => $create_uid,
                "write_uid" => $write_uid,
                "create_date" => now(),
                "write_date" => now(),
                "status" => Status::UN_PUBLISHED,
                "company_id" => $company_id,
                "name" => str_replace("'", "''", $item['group_name']),
                "type" => str_replace("'", "''", $item['type'])
            ];
            $this->resUserGroupRepository->create($res_user_group);
        }
    }

    /**
     * @param $company_id
     * @param $create_uid
     * @param $write_uid
     * @return bool|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function createCompanyVendorCompany($company_id, $create_uid, $write_uid)
    {
        $arr_department = [
            ["name" => "Vendor", "type" => "vendor"],
            ["name" => "Company", "type" => "company"],
        ];

        foreach ($arr_department as $item) {
            $user_company_id = uniqid();
            $resCompany = [
                "id" => $user_company_id,
                "company_id" => $user_company_id,
                "create_uid" => $create_uid,
                "write_uid" => $write_uid,
                "create_date" => now(),
                "write_date" => now(),
                "status" => Status::UN_PUBLISHED,
                "parent_id" => $company_id,
                "name" => str_replace("'", "''", $item['name']),
                "commercial_name" => str_replace("'", "''", $item['name']),
                "type" => str_replace("'", "''", $item['type']),
            ];
            $this->resCompanyRepository->create($resCompany);
        }
    }

    /**
     * @param $company_id
     * @param $create_uid
     * @param $write_uid
     * @param $user_id
     * @param $email
     * @param $phone
     * @return bool|\Illuminate\Database\Eloquent\Model|mixed
     */
    public function create_org_chart($company_id, $create_uid, $write_uid, $user_id, $email, $phone)
    {
        $organizations = [
            "id" => uniqid(),
            "create_uid" => $create_uid,
            "write_uid" => $write_uid,
            "create_date" => now(),
            "write_date" => now(),
            "status" => Status::UN_PUBLISHED,
            "company_id" => $company_id,
            "manager_id" => "ROOT",
            "email" => str_replace("'", "''", $email),
            "phone" => str_replace("'", "''", $phone),
            "user_id" => str_replace("'", "''", $user_id)
        ];
        $this->organizationsRepository->create($organizations);
    }

    /**
     * @param String $key
     * @return String
     */
    public function active(string $key)
    {
        [$userId, $time] = explode('|', base64_decode($key));

        $user = $this->usersRepository->where('id', $userId)
            ->where('status', Status::UN_PUBLISHED)
            ->where('write_date', $time)->findFirst(['id']);
        if ($user) {
            $this->usersRepository->update($userId, ['status' => Status::PUBLISHED, 'actived' => Status::ACTIVE]);
            Log::debug("Active account " . $userId . " success");
            return __('Kích hoạt tài khoản thành công.');
        } else {
            $user = $this->usersRepository->where('id', $userId)
                ->where('status', Status::PUBLISHED)->findFirst(['id']);
            if ($user) {
                return __('Tài khoản đã được kích hoạt.');
            } else {
                Log::debug("Active account " . $userId . " un-success");
                return __('Kích hoạt tài khoản không thành công.');
            }
        }
    }

    public function sendOTP($params)
    {
        $email = $params['email'];
        $message = __("Gửi mail kích hoạt không thành công.");

        $user = $this->usersRepository->where('email', $email)->findFirst();
        $user_id = $user->id;

        $sendEmail = false;
        if ($user) {
            $code = rand(1000, 9999);
            $now = now();
            $time = 0;
            $resUserVerification = $this->resUserVerificationRepository
                ->where('create_uid', $user_id)
                ->where('type', Constant::TYPE_FORGOT_PASSWORD)
                ->findFirst();
            if ($resUserVerification) {
                $diff = $now->diffInSeconds($resUserVerification->write_date);
                if ($diff > 300) { // 300 second -> 5 minus
                    $sendEmail = true;
                } else {
                    $time = gmdate('i:s', 300 - $diff);
                    $message = __("Vui lòng chờ :time giây để gửi lại mã kích hoạt", ['time' => $time]);
                }
            } else {
                $resUserVerification = $this->resUserVerificationRepository->create(
                    [
                        'id' =>Str::uuid(),
                        
                        
                        'status' => Status::ACTIVE,
                        'type' => Constant::TYPE_FORGOT_PASSWORD,
                        'create_uid' => $user_id,
                        'code' => $code,
                    ]
                );
                $sendEmail = true;
            }


            if ($sendEmail) {
                // Gửi email
                $res = Mail::send('forgot-password-otp', [
                    'username' => $user->name,
                    'otp' => $code,
                    'otp_expiry' => 10
                ], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Lấy lại mật khẩu của bạn');
                });

                if ($res instanceof SentMessage) {
                    $message = __(
                        "Gửi mã OTP kích hoạt thành công. Vui lòng kiểm tra email :mail",
                        ['mail' => $user->email]
                    );
                } else {
                    return new \Exception(__("Gửi mã OTP kích hoạt không thành công."), 500);
                }
            }
        } else {
            return new \Exception("Email không tồn tại.", 404);
        }

        return ['message' => $message, 'user_id' => $user_id, 'time_wait' => $time];
    }

    public function verifyOTP($params)
    {
        $code = $params['code'];
        $user_id = $params['user_id'];
        $now = now();
        $res = [];
        $resUserVerification = $this->resUserVerificationRepository
            ->where('create_uid', $user_id)
            ->where('status', Status::ACTIVE)
            ->where('code', $code)->findFirst();
        if ($resUserVerification) {
            $diff = $now->diffInMinutes($resUserVerification->write_date);
            if ($diff <= 10) {
                $resUserVerification->write_date = now();
                $resUserVerification->status = Status::INACTIVE;
                $resUserVerification->save();
                $res['message'] = __("Kích hoạt thành công.");
                $res['verify_id'] = $resUserVerification->id;
            } else {
                $this->resUserVerificationRepository->delete($resUserVerification->id);
                return new \Exception(__("Mã OTP hết hạn."), 404);
            }
        } else {
            return new \Exception(__("Kích hoạt không thành công."), 500);
        }

        return $res;
    }

    public function changePassword($params)
    {
        $userVerificationId = $params['verify_id'];
        $password = Hash::make($params['new_password']);
        $message = __("System error");

        $resUserVerification = $this->resUserVerificationRepository
            ->where('id', $userVerificationId)
            ->where('status', Status::INACTIVE)
            ->findFirst();
        if ($resUserVerification) {
            $user = $this->usersRepository->update(
                $resUserVerification->create_uid,
                ["password" => $password, 'write_date' => now()]
            );
            $this->resUserVerificationRepository->delete($resUserVerification->id);
            if ($user) {
                $message = __("Cập nhật mật khẩu thành công.");
            } else {
                return new \Exception(__("Tài khoản không tồn tại."), 400);
            }
        }
        return ['message' => $message];
    }
}
