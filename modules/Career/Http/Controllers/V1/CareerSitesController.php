<?php

namespace Digisource\Career\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Digisource\Common\Utils\Utils;
use Digisource\Core\Constant\Constant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CareerSitesController extends Controller
{
    public function get_career_site(Request $request)
    {

        $requestData = $request->all();

        $search = $requestData['search'] ?? null;
        $companiesId = $requestData['companies_id'] ?? null;
        $startDate = $requestData['start_date'] ?? null;
        $endDate = $requestData['end_date'] ?? null;
        $inCompanies = $requestData['in_companies'] ?? null;
        $sortBy = $requestData['sort_by'] ?? "ASC";
        $sortColumn = $requestData['sort_column'] ?? "create_date";
        $p = $requestData['p'] ?? 0;
        $ps = $requestData['ps'] ?? 10;

        $query = DB::table('career_sites AS d1')
            ->select(
                'd1.id',
                'd1.title',
                'd1.contents',
                'd1.publish',
                'd1.fonts',
                'd1.main_color',
                'd1.text_color',
                'd1.header_layout',
                'd1.button_text',
                'd1.button_text_color',
                'd1.logo_id',
                'd1.banner_id'
            )
            ->leftJoin('companies AS d2', 'd1.companies_id', '=', 'd2.id')
            ->where('d1.company_id', '=', auth()->user()->company_id)
            ->where('d1.status', '=', 0);

        if ($search) {
            $query->where(function ($subquery) use ($search) {
                $subquery->where('d1.title', 'like', "%$search%");
            });
        }

        if ($startDate || $endDate) {
            $query->where(function ($subquery) use ($startDate, $endDate) {
                $subquery->where('d1.create_date', '>=', $startDate)
                    ->where('d1.create_date', '<=', $endDate);
            });
        }

        if ($inCompanies) {
            $query->where('d1.companies_id', $inCompanies);
        }

        $query->orderBy("d1.$sortColumn", $sortBy);

        $result = $query->paginate($ps, ['*'], 'page', $p);

        $data = [];
        foreach ($result as $row) {
            $arrSocials = [];

            $arr = [
                'id' => $row->id,
                'title' => $row->title,
                'contents' => $row->contents,
                'publish' => $row->publish,
                'fonts' => $row->fonts,
                'main_color' => $row->main_color,
                'text_color' => $row->text_color,
                'header_layout' => $row->header_layout,
                'button_text' => $row->button_text,
                'button_text_color' => $row->button_text_color,
                'logo_id' => $row->logo_id,
                'banner_id' => $row->banner_id,
            ];

            $resultSocials = DB::table('career_site_socials AS d1')
                ->select('d1.id', 'd1.active', 'd1.url', 'd1.icons')
                ->where('d1.career_site_id', '=', $arr['id'])
                ->where('d1.status', '=', 0)
                ->orderBy('d1.write_date', 'ASC')
                ->get();

            foreach ($resultSocials as $social) {
                $arrSocials[$social->icons] = ['id' => $social->id, 'active' => $social->active, 'url' => $social->url];
            }

            $arr['socials'] = $arrSocials;

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'total' => $result->total(),
            'per_page' => $result->perPage(),
            'current_page' => $result->currentPage(),
            'from' => $result->firstItem(),
            'to' => $result->lastItem(),
            'data' => ['career_sites' => $data],
            'message' => "Lấy danh sách career sites thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_career_site(Request $request)
    {
        $requestData = $request->all();
        $user = auth()->user();

        $validator = Validator::make($requestData, [
            'companies_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $companiesId = $requestData['companies_id'];
            $careerSiteId = DB::table('career_sites')->insertGetId([
                'id' => uniqid(),
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                'create_date' => date('Y-m-d H:i:s'),
                'write_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'company_id' => $user->company_id,
                'companies_id' => $companiesId,
            ]);

            $arrDefaultSocials = [
                ['name' => 'facebook', 'active' => '0', 'url' => ''],
                ['name' => 'instagram', 'active' => '0', 'url' => ''],
                ['name' => 'twitter', 'active' => '0', 'url' => ''],
                ['name' => 'linkedin', 'active' => '0', 'url' => ''],
                ['name' => 'website', 'active' => '0', 'url' => ''],
            ];

            foreach ($arrDefaultSocials as $social) {
                DB::table('career_site_socials')->insert([
                    'id' => uniqid(),
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'career_site_id' => $careerSiteId,
                    'active' => $social['active'],
                    'url' => $social['url'],
                    'icons' => $social['name'],
                ]);
            }

            $message = [
                'status' => true,
                'message' => 'Tạo career site thành công.',
            ];
        }


        $this->addData($message);

        return $this->getResponse();
    }

    public function update_career_site(Request $request, $id)
    {
        $requestData = $request->all();
        $user = auth()->user();

        $validator = Validator::make($requestData, [
            'fonts' => 'required',
            'main_color' => 'required',
            'text_color' => 'required',
            'header_layout' => 'required',
            'button_text' => 'required',
            'button_text_color' => 'required',
            'title' => 'required|max:1000',
            'contents' => 'required|max:10000',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $publish = $requestData['publish'] ?? 0;
            $fonts = $requestData['fonts'];
            $mainColor = $requestData['main_color'];
            $textColor = $requestData['text_color'];
            $headerLayout = $requestData['header_layout'];
            $buttonText = $requestData['button_text'];
            $buttonTextColor = $requestData['button_text_color'];
            $title = $requestData['title'];
            $contents = $requestData['contents'];
            $socialId = $requestData['social_id'];
            $active = $requestData['active'];
            $url = $requestData['url'];

            $careerSiteExists = DB::table('career_sites')
                ->where('status', 0)
                ->where('id', $id)
                ->exists();

            if (!$careerSiteExists) {
                $message = [
                    'status' => true,
                    'message' => "Career site không tồn tại.",
                ];
            } else {
                $logoId = null;
                $bannerId = null;
                if ($request->hasFile('logo')) {
                    $logoId = Utils::updateFile($request->logo, Constant::FILE_LOGO, $user->id, $user->company_id);
                }
                if ($request->hasFile('banner')) {
                    $bannerId = Utils::updateFile($request->banner, Constant::FILE_BANNER, $user->id, $user->company_id);
                }

                $updateCareerSite = DB::table('career_sites')
                    ->where('id', $id)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'publish' => $publish,
                        'fonts' => $fonts,
                        'main_color' => $mainColor,
                        'text_color' => $textColor,
                        'header_layout' => $headerLayout,
                        'button_text' => $buttonText,
                        'button_text_color' => $buttonTextColor,
                        'title' => $title,
                        'contents' => $contents,
                        'logo_id' => $logoId,
                        'banner_id' => $bannerId,
                    ]);

                $updateSocial = DB::table('career_site_socials')
                    ->where('id', $socialId)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'active' => $active,
                        'url' => $url,
                    ]);

                if ($updateCareerSite && $updateSocial) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật career site thành công.",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật career site thất bại.",
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_career_site_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('career_sites as d1')
            ->select(
                'd1.id',
                'd1.title',
                'd1.contents',
                'd1.publish',
                'd1.fonts',
                'd1.main_color',
                'd1.text_color',
                'd1.header_layout',
                'd1.button_text',
                'd1.button_text_color',
                'd1.logo_id',
                'd1.banner_id'
            )
            ->leftJoin('companies as d2', 'd1.companies_id', '=', 'd2.id')
            ->where('d1.company_id', '=', $user->company_id)
            ->where('d1.status', '=', 0)
            ->where('d1.id', '=', $id)
            ->get();

        $numrows = $result->count();

        if ($numrows > 0) {
            $row = $result->first();
            $arr = [];
            $arrSocials = [];

            $arr['id'] = $row->id;
            $arr['title'] = $row->title;
            $arr['contents'] = $row->contents;
            $arr['publish'] = $row->publish;
            $arr['fonts'] = $row->fonts;
            $arr['main_color'] = $row->main_color;
            $arr['text_color'] = $row->text_color;
            $arr['header_layout'] = $row->header_layout;
            $arr['button_text'] = $row->button_text;
            $arr['button_text_color'] = $row->button_text_color;
            $arr['logo_id'] = $row->logo_id;
            $arr['banner_id'] = $row->banner_id;

            $resultSocials = DB::table('career_site_socials as d1')
                ->select('d1.id', 'd1.active', 'd1.url', 'd1.icons')
                ->where('d1.career_site_id', '=', $arr['id'])
                ->where('d1.status', '=', 0)
                ->orderBy('d1.write_date', 'ASC')
                ->get();

            foreach ($resultSocials as $social) {
                $arrSocials[$social->icons] = [
                    'id' => $social->id,
                    'active' => $social->active,
                    'url' => $social->url,
                ];
            }

            $arr['socials'] = $arrSocials;

            $data[] = $arr;

            $message = [
                'status' => true,
                'data' => ['career_site' => $arr],
                'message' => "Lấy career site by id thành công.",
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Career site không tồn tại.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_career_site(Request $request, $id)
    {
        $user = auth()->user();

        $seenId = DB::table('career_sites')
            ->select('id')
            ->where('status', 0)
            ->where('id', $id)
            ->first();

        if (empty($seenId)) {
            $message = [
                'status' => true,
                'message' => "Career sites không tồn tại.",
            ];
        } else {
            DB::table('career_sites')
                ->where('id', $id)
                ->update([
                    'status' => 1,
                ]);

            $message = [
                'status' => true,
                'message' => "Xóa career sites thành công.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_social(Request $request)
    {
        $user = auth()->user();

        $requestData = $request->all();
        $user = auth()->user();

        $validator = Validator::make($requestData, [
            'career_site_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $careerSiteId = $requestData['career_site_id'];

            $result = DB::table('career_site_socials')
                ->select('id', 'career_site_id', 'active', 'url', 'icons')
                ->where('company_id', $user->company_id)
                ->where('status', 0)
                ->where('career_site_id', $careerSiteId)
                ->get();

            $data = [];
            foreach ($result as $row) {
                $arr = [
                    'id' => $row->id,
                    'career_site_id' => $row->career_site_id,
                    'active' => $row->active,
                    'url' => $row->url,
                    'icons' => $row->icons,
                ];

                $data[] = $arr;
            }

            $message = [
                'status' => true,
                'data' => ['career_site_socials' => $data],
                'message' => "Lấy danh sách career site socials thành công.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_social(Request $request)
    {
        $user = auth()->user();
        $requestData = $request->all();

        $user = auth()->user();

        $validator = Validator::make($requestData, [
            'career_site_id' => 'required',
            'active' => 'required',
            'url' => 'required|max:1000',
            'icons' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $careerSiteId = $requestData['career_site_id'];
            $active = $requestData['active'];
            $url = $requestData['url'];
            $icons = $requestData['icons'];

            $id = DB::table('career_site_socials')->insertGetId([
                'id' => uniqid(),
                'create_uid' => $user->id,
                'write_uid' => $user->id,
                'create_date' => date('Y-m-d H:i:s'),
                'write_date' => date('Y-m-d H:i:s'),
                'status' => 0,
                'company_id' => $user->company_id,
                'career_site_id' => $careerSiteId,
                'active' => $active,
                'url' => $url,
                'icons' => $icons,
            ]);

            if ($id) {
                $message = [
                    'status' => true,
                    'message' => "Tạo career site social thành công.",
                ];
            } else {
                $message = [
                    'status' => false,
                    'message' => "Tạo career social thất bại.",
                ];
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_social(Request $request, $id)
    {
        $user = auth()->user();

        $requestData = $request->all();
        $user = auth()->user();

        $validator = Validator::make($requestData, [
            'active' => 'required',
            'url' => 'max:1000',
            'icons' => 'max:1000',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $active = $requestData['active'];
            $url = $requestData['url'];
            // $icons = $requestData['icons'];

            $seenId = DB::table('career_site_socials')
                ->where('status', 0)
                ->where('id', $id)
                ->value('id');

            if (!$seenId) {
                $message = [
                    'status' => true,
                    'message' => "Career site social không tồn tại.",
                ];
            } else {
                $updateData = [
                    'write_date' => date('Y-m-d H:i:s'),
                    'active' => $active,
                    'url' => $url,
                    // 'icons' => $icons,
                ];

                $result = DB::table('career_site_socials')
                    ->where('id', $id)
                    ->update($updateData);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật career site social thành công.",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật career site social thất bại.",
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_social_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('career_site_socials')
            ->select('id', 'career_site_id', 'active', 'url', 'icons')
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('id', $id)
            ->first();

        if ($result) {
            $arr = [
                'id' => $result->id,
                'career_site_id' => $result->career_site_id,
                'active' => $result->active,
                'url' => $result->url,
                'icons' => $result->icons,
            ];

            $message = [
                'status' => true,
                'data' => ['career_site_social' => $arr],
                'message' => "Lấy career site social by id thành công.",
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Career site social không tồn tại.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_social(Request $request, $id)
    {
        $user = auth()->user();

        $seen_id = DB::table('career_site_socials')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Career site social không tồn tại.",
            ];
        } else {
            DB::table('career_site_socials')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa career site social thành công.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }
}
