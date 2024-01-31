<?php

namespace Digisource\Jobs\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobsController extends Controller
{
    public function get_job_group(Request $request)
    {
        $user = auth()->user();

        $param = $request->all();

        $id = $param['rel_id'];
        $result = DB::table('job_group')
            ->select('id', 'name', 'job_id')
            ->where('status', 0)
            ->where('company_id', $user->company_id)
            ->where('job_id', $id)
            ->orderBy('write_date', 'ASC')
            ->get();

        $data = $result->toArray();

        $message = [
            'status' => true,
            'data' => ['job_groups' => $data],
            'message' => "Lấy danh sách job group thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_group(Request $request)
    {
        $user = auth()->user();
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'job_id' => 'required',
            'job_group_id' => 'required',
            'user_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $job_id = $requestData['job_id'];
            $job_group_id = $requestData['job_group_id'];
            $user_id = $requestData['user_id'];

            $seen_id = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $job_id)
                ->value('id');

            if (!$seen_id) {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = DB::table('job_hiring_teams')->insertGetId([
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'job_id' => $job_id,
                    'job_group_id' => $job_group_id,
                    'user_id' => $user_id,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job hiring team thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job hiring team thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_group(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'max:256',
        ], [
            'max' => ':attribute không được quá :max.',
        ]);

        $validator->validate();

        $existingJobGroup = DB::table('job_group')->where('status', 0)->where('id', $id)->first();

        if (!$existingJobGroup) {
            $message = [
                'status' => true,
                'message' => "Job group không tồn tại."
            ];
        } else {
            DB::table('job_group')
                ->where('id', $id)
                ->update([
                    'write_date' => date('Y-m-d H:i:s'),
                    'name' => $data['name'],
                ]);

            $message = [
                'status' => true,
                'message' => "Cập nhật job group thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_group_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $existingJobGroup = DB::table('job_group')->where('status', 0)->where('id', $id)->first();

        if (!$existingJobGroup) {
            $message = [
                'status' => true,
                'message' => "Job group không tồn tại."
            ];
        } else {
            $jobGroupData = DB::table('job_group')->select('id', 'name')->where('id', $id)->first();

            $data = [];
            if ($jobGroupData) {
                $data['id'] = $jobGroupData->id;
                $data['name'] = $jobGroupData->name;
            }

            $message = [
                'status' => true,
                'data' => ['job_types' => $data],
                'message' => "Lấy job group by id thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_group(Request $request, $id)
    {
        $user = auth()->user();

        $existingJobGroup = DB::table('job_group')->where('status', 0)->where('id', $id)->first();

        if (!$existingJobGroup) {
            $message = [
                'status' => true,
                'message' => "Job group không tồn tại."
            ];
        } else {
            DB::table('job_group')->where('id', $id)->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job group thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_skills(Request $request)
    {
        $user = auth()->user();
        $param = $request->all();
        $relId = $param['rel_id'];

        $jobSkills = DB::table('job_skills as d1')
            ->select('d1.id', 'd1.job_id', 'd1.skill_id', 'd1.description', 'skills.name AS skill_name')
            ->leftJoin('skills', 'd1.skill_id', '=', 'skills.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.job_id', $relId)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = [];

        foreach ($jobSkills as $jobSkill) {
            $arr = [
                'id' => $jobSkill->id,
                'job_id' => $jobSkill->job_id,
                'skill_id' => $jobSkill->skill_id,
                'description' => $jobSkill->description,
                'skill_name' => $jobSkill->skill_name,
            ];

            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_skills' => $data],
            'message' => "Lấy danh sách job skill thành công.",
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_skills(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($data, [
            'rel_id' => 'required',
            'skill_id' => 'required',
            'description' => 'max:256',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            // Check if job exists
            $relId = $data['rel_id'];
            $skillId = $data['skill_id'];
            $description = $data['description'];
            $jobExists = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $relId)
                ->exists();

            if (!$jobExists) {
                $message = [
                    'status' => false,
                    'message' => "Job không tồn tại.",
                ];
            } else {
                // Insert job skills
                $id = DB::table('job_skills')->insertGetId([
                    'id' => uniqid(),
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'job_id' => $relId,
                    'skill_id' => $skillId,
                    'description' => $description,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job skills thành công.",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job skills thất bại.",
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_skills(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($data, [
            // 'skill_id' => 'required',
            'description' => 'max:256',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            // Check if job skill exists
            $skillId = $data['skill_id'];
            $description = $data['description'];
            $jobSkillExists = DB::table('job_skills')
                ->where('status', 0)
                ->where('id', $id)
                ->exists();

            if (!$jobSkillExists) {
                $message = [
                    'status' => false,
                    'message' => "Job skill không tồn tại.",
                ];
            } else {
                // Update job skill
                $updatedRows = DB::table('job_skills')
                    ->where('id', $id)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'skill_id' => $skillId,
                        'description' => $description,
                    ]);

                if ($updatedRows > 0) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job skills thành công.",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job skills thất bại.",
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_skills_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('job_skills as d1')
            ->leftJoin('skills as d2', 'd1.skill_id', '=', 'd2.id')
            ->select('d1.id', 'd1.job_id', 'd1.skill_id', 'd1.description', 'd2.name AS skill_name')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.id', $id)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $numrows = $result->count();

        if ($numrows > 0) {
            $row = $result->first();

            $arr = [
                'id' => $row->id,
                'job_id' => $row->job_id,
                'skill_id' => $row->skill_id,
                'description' => $row->description,
                'skill_name' => $row->skill_name,
            ];

            $message = [
                'status' => true,
                'data' => ['job_skill' => $arr],
                'message' => "Lấy danh sách job skill by id thành công.",
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job skill không tồn tại.",
            ];
        }
        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_skills(Request $request, $id)
    {
        $user = auth()->user();

        $jobSkill = DB::table('job_skills')
            ->where('id', $id)
            ->where('status', 0)
            ->first();

        if (!$jobSkill) {
            $message = [
                'status' => true,
                'message' => "Job skill không tồn tại."
            ];
        } else {
            DB::table('job_skills')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job skill thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_job_notes(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $relId = $data['rel_id'];

        $results = DB::table('notes as d1')
            ->select('d1.id', 'd1.notesable_type', 'd1.notesable_id', 'd1.contents', 'd3.id AS user_id', 'd3.user_name', 'd3.name', 'd1.create_date')
            ->leftJoin('candidates as d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.notesable_id', $relId)
            ->where('d1.notesable_type', 'job')
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = $results->map(function ($result) {
            return [
                'id' => $result->id,
                'notesable_type' => $result->notesable_type,
                'notesable_id' => $result->notesable_id,
                'contents' => $result->contents,
                'user_id' => $result->user_id,
                'user_name' => $result->user_name,
                'name' => $result->name,
                'create_date' => $result->create_date,
            ];
        });

        $message = [
            'status' => true,
            'data' => ['jobs_note' => $data->toArray()],
            'message' => "Lấy danh sách job note thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_notes(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'contents' => 'required|max:256',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $relId = $data['rel_id'];
            $content = $data['contents'];
            $seenId = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $relId)
                ->value('id');

            if (!$seenId) {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = uniqid();
                $now = date('Y-m-d H:i:s');

                $result = DB::table('notes')->insert([
                    'id' => $id,
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => $now,
                    'write_date' => $now,
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'notesable_type' => 'job',
                    'notesable_id' => $relId,
                    'contents' => $content,
                ]);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job note thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job note thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_notes(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'contents' => 'max:256',
        ], [
            'max' => ':attribute không được quá :max.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        } else {
            $content = $data['contents'];

            $seenId = DB::table('notes')
                ->where('status', 0)
                ->where('id', $id)
                ->value('id');

            if (!$seenId) {
                $message = [
                    'status' => true,
                    'message' => "Job note không tồn tại."
                ];
            } else {
                $now = date('Y-m-d H:i:s');

                $result = DB::table('notes')
                    ->where('id', $id)
                    ->update([
                        'write_date' => $now,
                        'contents' => $content,
                    ]);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job note thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job note thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_notes_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('notes as d1')
            ->select('d1.id', 'd1.notesable_type', 'd1.notesable_id', 'd1.contents', 'd3.user_name', 'd3.name', 'd1.create_date')
            ->leftJoin('candidates as d2', 'd1.notesable_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.create_uid', '=', 'd3.id')
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->where('d1.id', $id)
            ->where('d1.notesable_type', 'job')
            ->orderBy('d1.create_date', 'ASC')
            ->first();

        if ($result) {
            $message = [
                'status' => true,
                'data' => [
                    'job_note' => [
                        'id' => $result->id,
                        'notesable_type' => $result->notesable_type,
                        'notesable_id' => $result->notesable_id,
                        'contents' => $result->contents,
                        'user_name' => $result->user_name,
                        'name' => $result->name,
                        'create_date' => $result->create_date,
                    ]
                ],
                'message' => "Lấy danh sách job note by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job note không tồn tại."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_notes(Request $request, $id)
    {
        $seenId = DB::table('notes')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seenId) {
            $message = [
                'status' => true,
                'message' => "Job note không tồn tại."
            ];
        } else {
            DB::table('notes')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job note thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_job_stages(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $relId = $data['rel_id'];

        $results = DB::table('job_stages')
            ->select('id', 'name', 'parent_stage_id', 'description', 'sequence', 'is_edit', 'job_id')
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('job_id', $relId)
            ->orderBy('sequence', 'ASC')
            ->get();

        $data = $results->map(function ($result) {
            return [
                'id' => $result->id,
                'name' => $result->name,
                'parent_stage_id' => $result->parent_stage_id,
                'description' => $result->description,
                'sequence' => $result->sequence,
                'is_edit' => $result->is_edit,
                'job_id' => $result->job_id,
            ];
        });

        $message = [
            'status' => true,
            'data' => ['jobs_stages' => $data->toArray()],
            'message' => "Lấy danh sách job states thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_stages(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'name' => 'required|max:256',
            'parent_stage_id' => 'required',
            'description' => 'max:256',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        } else {
            $relId = $data['rel_id'];
            $name = $data['name'];
            $parentStageId = $data['parent_stage_id'];
            $description = $data['description'];
            $seenId = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $relId)
                ->value('id');

            if (!$seenId) {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $counts = DB::table('job_stages')
                    ->where('status', 0)
                    ->where('job_id', $relId)
                    ->max('sequence');

                $id = uniqid();
                $now = date('Y-m-d H:i:s');

                $result = DB::table('job_stages')->insert([
                    'id' => $id,
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => $now,
                    'write_date' => $now,
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'name' => $name,
                    'parent_stage_id' => $parentStageId,
                    'description' => $description,
                    'sequence' => $counts + 1,
                    'is_edit' => 0,
                    'job_id' => $relId,
                ]);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job stage thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job stage thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_stages(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'max:256',
            'description' => 'max:256',
            'sequence' => 'numeric',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first()
            ];
        } else {
            $name = $data['name'];
            $parentStageId = $data['parent_stage_id'];
            $description = $data['description'];
            $sequence = $data['sequence'] ?? 1;
            $seenId = DB::table('job_stages')
                ->where('status', 0)
                ->where('id', $id)
                ->value('id');

            if (!$seenId) {
                $message = [
                    'status' => true,
                    'message' => "Job stage không tồn tại."
                ];
            } else {
                $now = date('Y-m-d H:i:s');

                $result = DB::table('job_stages')
                    ->where('id', $id)
                    ->update([
                        'write_date' => $now,
                        'name' => $name,
                        'parent_stage_id' => $parentStageId,
                        'description' => $description,
                        'sequence' => $sequence,
                    ]);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job stage thành công."
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job stage thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_stages_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('job_stages')
            ->select('id', 'name', 'parent_stage_id', 'description', 'sequence', 'is_edit', 'job_id')
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('id', $id)
            ->orderBy('sequence', 'ASC')
            ->get();

        $numrows = count($result);

        if ($numrows > 0) {
            $row = $result[0];

            $arr = [
                'id' => $row->id,
                'name' => $row->name,
                'parent_stage_id' => $row->parent_stage_id,
                'description' => $row->description,
                'sequence' => $row->sequence,
                'is_edit' => $row->is_edit,
                'job_id' => $row->job_id,
            ];

            $message = [
                'status' => true,
                'data' => ['job_note' => $arr],
                'message' => "Lấy danh sách job stage by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job stage không tồn tại."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_stages(Request $request, $id)
    {
        $user = auth()->user();

        $seenId = DB::table('job_stages')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (empty($seenId)) {
            $message = [
                'status' => true,
                'message' => "Job stage không tồn tại."
            ];
        } else {
            $result = DB::table('job_stages')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job stage thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_job_candidate_stages(Request $request)
    {
        $data = $request->all();
        $user = auth()->user();
        $relId = $data['rel_id'];


        $jobStages = DB::table('job_stages')
            ->select('id', 'name', 'parent_stage_id', 'description', 'sequence', 'is_edit', 'job_id')
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('job_id', $relId)
            ->orderBy('sequence', 'ASC')
            ->get();

        $resultData = [];

        foreach ($jobStages as $jobStage) {
            $arrCandidates = [];

            $jobCandidates = DB::table('job_candidates')
                ->select('id', 'create_date', 'candidate_id')
                ->where('status', 0)
                ->where('job_stage_id', $jobStage->id)
                ->orderBy('create_date', 'ASC')
                ->get();

            $arr = [
                'job_stage_id' => $jobStage->id,
                'name' => $jobStage->name,
                'parent_stage_id' => $jobStage->parent_stage_id,
                'description' => $jobStage->description,
                'sequence' => $jobStage->sequence,
                'is_edit' => $jobStage->is_edit,
                'job_id' => $jobStage->job_id,
                'candidates' => [],
            ];

            foreach ($jobCandidates as $jobCandidate) {
                $arrCandidate = [
                    'job_candidate_id' => $jobCandidate->id,
                    'create_date' => $jobCandidate->create_date,
                    'candidate_id' => $jobCandidate->candidate_id,
                    'user_followed' => [],
                ];

                $userFollowed = DB::table('candidate_followed_candidates')
                    ->select('id', 'user_name', 'avatar_id')
                    ->join('candidate_followed', 'candidate_followed_candidates.followed_id', '=', 'candidate_followed.id')
                    ->join('res_user', 'candidate_followed.user_id', '=', 'res_user.id')
                    ->leftJoin('document', function ($join) {
                        $join->on('res_user.id', '=', 'document.rel_id')
                            ->where('document.document_type_rel', '=', 'avatar');
                    })
                    ->where('candidate_followed_candidates.candidate_id', $arrCandidate['candidate_id'])
                    ->where('candidate_followed_candidates.status', 0)
                    ->orderBy('candidate_followed_candidates.write_date', 'ASC')
                    ->get();

                foreach ($userFollowed as $followed) {
                    $arrUserFollowed = [
                        'id' => $followed->id,
                        'user_name' => $followed->user_name,
                        'avatar_id' => $followed->avatar_id,
                    ];

                    $arrCandidate['user_followed'][] = $arrUserFollowed;
                }

                $arrCandidates[] = $arrCandidate;
            }

            $arr['candidates'] = $arrCandidates;
            $resultData[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_candidate_stages' => $resultData],
            'message' => "Lấy danh sách job states thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_candidate_stages(Request $request)
    {
        $user = auth()->user();

        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'rel_id' => 'required',
            'candidate_id' => 'required',
            'job_stage_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $relId = $requestData['rel_id'];
            $candidateId = $requestData['candidate_id'];
            $jobStageId = $requestData['job_stage_id'];
            $jobExists = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $relId)
                ->exists();

            if (!$jobExists) {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại.",
                ];
            } else {
                $id = DB::table('job_candidates')->insertGetId([
                    'id' => uniqid(),
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'candidate_id' => $candidateId,
                    'job_stage_id' => $jobStageId,
                    'job_id' => $relId,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job candidate thành công",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job candidate thất bại.",
                    ];
                }
            }
        }
        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_candidate_stages(Request $request, $id)
    {
        $user = auth()->user();

        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'job_stage_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $jobStageId = $requestData['job_stage_id'];
            $jobCandidateExists = DB::table('job_candidates')
                ->where('status', 0)
                ->where('id', $id)
                ->exists();

            if (!$jobCandidateExists) {
                $message = [
                    'status' => true,
                    'message' => "Job candidate không tồn tại.",
                ];
            } else {
                $updatedRows = DB::table('job_candidates')
                    ->where('id', $id)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'job_stage_id' => $jobStageId,
                    ]);

                if ($updatedRows > 0) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job candidate stage thành công.",
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job candidate stage thất bại.",
                    ];
                }
            }
        }
        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_candidate_stages_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $jobStage = DB::table('job_stages')
            ->select('id', 'name', 'parent_stage_id', 'description', 'sequence', 'is_edit', 'job_id')
            ->where('company_id', $user->company_id)
            ->where('status', 0)
            ->where('id', $id)
            ->orderBy('sequence', 'ASC')
            ->first();

        if ($jobStage) {
            $jobCandidates = DB::table('job_candidates')
                ->select(
                    'job_candidates.id',
                    'job_candidates.create_date',
                    'candidates.first_name',
                    'candidates.last_name',
                    'candidates.phone',
                    'candidates.email',
                    'document.id AS logo_id',
                    'candidates.id AS candidate_id'
                )
                ->leftJoin('jobs', 'job_candidates.job_id', '=', 'jobs.id')
                ->leftJoin('candidates', 'job_candidates.candidate_id', '=', 'candidates.id')
                ->leftJoin('document', function ($join) {
                    $join->on('candidates.id', '=', 'document.rel_id')
                        ->where('document.document_type_rel', '=', 'logo');
                })
                ->where('job_candidates.status', 0)
                ->where('job_candidates.job_stage_id', $jobStage->id)
                ->orderBy('job_candidates.create_date', 'ASC')
                ->get();

            $arr = [
                'job_stage_id' => $jobStage->id,
                'name' => $jobStage->name,
                'parent_stage_id' => $jobStage->parent_stage_id,
                'description' => $jobStage->description,
                'sequence' => $jobStage->sequence,
                'is_edit' => $jobStage->is_edit,
                'job_id' => $jobStage->job_id,
                'candidates' => $jobCandidates->toArray(),
            ];

            $message = [
                'status' => true,
                'data' => ['job_candidate_stage' => $arr],
                'message' => "Lấy danh sách job candidate stage by id thành công.",
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job stage không tồn tại.",
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_candidate_stages(Request $request, $id)
    {
        $user = auth()->user();

        $jobStage = DB::table('job_stages')
            ->where('status', 0)
            ->where('id', $id)
            ->first();

        if (!$jobStage) {
            $message = [
                'status' => true,
                'message' => "Job stage không tồn tại."
            ];
        } else {
            DB::table('job_stages')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job stage thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_job_commissions(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $relId = $data['rel_id'];

        $result = DB::table('job_commissions AS d1')
            ->leftJoin('vendors AS d2', 'd1.vendor_id', '=', 'd2.id')
            ->leftJoin('vendor_types AS d3', 'd1.vendor_type_id', '=', 'd3.id')
            ->leftJoin('vendor_commission_types AS d4', 'd1.commission_type_id', '=', 'd4.id')
            ->leftJoin('vendor_commission_kpis AS d5', 'd1.commission_kpi_type_id', '=', 'd5.id')
            ->select(
                'd1.id',
                'd1.vendor_id',
                'd1.vendor_type_id',
                'd1.commission_type_id',
                'd1.commission_kpi_type_id',
                'd2.name AS vendor_name',
                'd3.name AS vendor_name_type',
                'd4.name AS commission_type_name',
                'd5.name AS commission_kpi_name',
                'd1.value',
                'd1.kpi'
            )
            ->where('d1.company_id', $user->company_id)
            ->where('d1.rel_id', $relId)
            ->where('d1.status', 0)
            ->orderBy('d1.write_date', 'ASC')
            ->get();

        $data = $result->toArray();

        $message = [
            'status' => true,
            'data' => ['job_commissions' => $data],
            'message' => "Lấy danh sách job commission thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_commissions(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'rel_id' => 'required',
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_type_id' => 'required',
            'value' => 'required|max:256',
            'commission_kpi_type_id' => 'required',
            'kpi' => 'required|max:1000',
        ]);

        $message = [];

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $jobExists = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $request->rel_id)
                ->exists();

            if (!$jobExists) {
                $message = [
                    'status' => false,
                    'message' => 'Job của bạn không tồn tại.',
                ];
            } else {
                $id = DB::table('job_commissions')->insertGetId([
                    'id' => uniqid(),
                    'create_uid' => auth()->id(),
                    'write_uid' => auth()->id(),
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => session('company_id'),
                    'vendor_id' => $request->vendor_id,
                    'vendor_type_id' => $request->vendor_type_id,
                    'commission_type_id' => $request->commission_type_id,
                    'value' => $request->value,
                    'commission_kpi_type_id' => $request->commission_kpi_type_id,
                    'rel_id' => $request->rel_id,
                    'kpi' => $request->kpi,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => 'Tạo job commission thành công',
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => 'Tạo job commission thất bại.',
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_commissions(Request $request, $id)
    {
        $user = auth()->user();

        $data = $request->all();

        $validator = Validator::make($data, [
            'rel_id' => 'required',
            'vendor_id' => 'required',
            'vendor_type_id' => 'required',
            'commission_type_id' => 'required',
            'value' => 'required|max:256',
            'commission_kpi_type_id' => 'required',
            'kpi' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            // Check if the job commission exists
            $commissionExists = DB::table('job_commissions')
                ->where('status', 0)
                ->where('id', $id)
                ->exists();

            if (!$commissionExists) {
                $message = [
                    'status' => false,
                    'message' => "Job commission của bạn không tồn tại."
                ];
            } else {
                $result = DB::table('job_commissions')
                    ->where('id', $id)
                    ->update([
                        'write_date' => date('Y-m-d H:i:s'),
                        'vendor_id' => $data['vendor_id'],
                        'vendor_type_id' => $data['vendor_type_id'],
                        'commission_type_id' => $data['commission_type_id'],
                        'value' => $data['value'],
                        'commission_kpi_type_id' => $data['commission_kpi_type_id'],
                        'rel_id' => $data['rel_id'],
                        'kpi' => $data['kpi'],
                    ]);

                if ($result) {
                    $message = [
                        'status' => true,
                        'message' => "Cập nhật job commission thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Cập nhật job commission thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function get_job_commissions_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $result = DB::table('job_commissions AS d1')
            ->select(
                'd1.id',
                'd1.vendor_id',
                'd1.vendor_type_id',
                'd1.commission_type_id',
                'd1.commission_kpi_type_id',
                'd2.name AS vendor_name',
                'd3.name AS vendor_name_type',
                'd4.name AS commission_type_name',
                'd5.name AS commission_kpi_name',
                'd1.value',
                'd1.kpi'
            )
            ->leftJoin('vendors AS d2', 'd1.vendor_id', '=', 'd2.id')
            ->leftJoin('vendor_types AS d3', 'd1.vendor_type_id', '=', 'd3.id')
            ->leftJoin('vendor_commission_types AS d4', 'd1.commission_type_id', '=', 'd4.id')
            ->leftJoin('vendor_commission_kpis AS d5', 'd1.commission_kpi_type_id', '=', 'd5.id')
            ->where('d1.id', $id)
            ->where('d1.status', 0)
            ->orderBy('d1.write_date', 'ASC')
            ->first();

        if ($result) {
            $arr = [
                'id' => $result->id,
                'vendor_id' => $result->vendor_id,
                'vendor_type_id' => $result->vendor_type_id,
                'commission_type_id' => $result->commission_type_id,
                'commission_kpi_type_id' => $result->commission_kpi_type_id,
                'vendor_name' => $result->vendor_name,
                'vendor_name_type' => $result->vendor_name_type,
                'commission_type_name' => $result->commission_type_name,
                'commission_kpi_name' => $result->commission_kpi_name,
                'value' => $result->value,
                'kpi' => $result->kpi,
            ];

            $message = [
                'status' => true,
                'data' => ['job_commission' => $arr],
                'message' => "Lấy danh sách job commission by id thành công."
            ];
        } else {
            $message = [
                'status' => false,
                'message' => "Job commission không tồn tại."
            ];
        }
        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_commissions(Request $request, $id)
    {
        $user = auth()->user();

        $seen_id = DB::table('job_commissions')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Job commission không tồn tại."
            ];
        } else {
            DB::table('job_commissions')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job commissions thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    //
    public function get_job_hiring_team(Request $request)
    {
        $user = auth()->user();
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'job_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
            $this->addData($message);

            return $this->getResponse();
        }

        $id = $requestData['job_id'];

        $result = DB::table('job_hiring_teams as d1')
            ->leftJoin('job_group as d2', 'd1.job_group_id', '=', 'd2.id')
            ->leftJoin('res_user as d3', 'd1.user_id', '=', 'd3.id')
            ->leftJoin('res_user_company as d4', 'd3.id', '=', 'd4.user_id')
            ->leftJoin('res_company as d5', 'd5.id', '=', 'd4.company_id')
            ->leftJoin('res_user_group as d6', 'd6.id', '=', 'd4.group_id')
            ->leftJoin('document as d7', function ($join) {
                $join->on('d3.id', '=', 'd7.rel_id')->where('d7.document_type_rel', '=', 'avatar');
            })
            ->select(
                'd1.id',
                'd2.name AS job_role_name',
                'd3.name AS user_name',
                'd7.id AS avatar_id',
                'd5.id AS department_id',
                'd5.name AS department_name',
                'd6.id AS user_group_id',
                'd6.name AS group_name'
            )
            ->where('d1.job_id', $id)
            ->where('d1.company_id', $user->company_id)
            ->where('d1.status', 0)
            ->get();

        $data = [];
        foreach ($result as $item) {
            $arr = [
                'id' => $item->id,
                'job_role_name' => $item->job_role_name,
                'user_name' => $item->user_name,
                'avatar_id' => $item->avatar_id,
                'department_id' => $item->department_id,
                'department_name' => $item->department_name,
                'group_id' => $item->user_group_id,
                'group_name' => $item->group_name,
            ];
            $data[] = $arr;
        }

        $message = [
            'status' => true,
            'data' => ['job_hiring_teams' => $data],
            'message' => "Lấy danh sách job hiring teams thành công."
        ];

        $this->addData($message);

        return $this->getResponse();
    }

    public function create_job_hiring_team(Request $request)
    {
        $user = auth()->user();
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'job_id' => 'required',
            'job_group_id' => 'required',
            'user_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $job_id = $requestData['job_id'];
            $job_group_id = $requestData['job_group_id'];
            $user_id = $requestData['user_id'];

            $seen_id = DB::table('jobs')
                ->where('status', 0)
                ->where('id', $job_id)
                ->value('id');

            if (!$seen_id) {
                $message = [
                    'status' => false,
                    'message' => "Job của bạn không tồn tại."
                ];
            } else {
                $id = DB::table('job_hiring_teams')->insertGetId([
                    'id' => uniqid(),
                    'create_uid' => $user->id,
                    'write_uid' => $user->id,
                    'create_date' => date('Y-m-d H:i:s'),
                    'write_date' => date('Y-m-d H:i:s'),
                    'status' => 0,
                    'company_id' => $user->company_id,
                    'job_id' => $job_id,
                    'job_group_id' => $job_group_id,
                    'user_id' => $user_id,
                ]);

                if ($id) {
                    $message = [
                        'status' => true,
                        'message' => "Tạo job hiring team thành công"
                    ];
                } else {
                    $message = [
                        'status' => false,
                        'message' => "Tạo job hiring team thất bại."
                    ];
                }
            }
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function update_job_hiring_team(Request $request, $id)
    {
        $user = auth()->user();
        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'job_group_id' => 'required',
            'user_id' => 'required',
        ], [
            'required' => ':attribute không được để trống.',
            'max' => ':attribute không được quá :max .',
        ]);

        $validator->validate();

        if ($validator->fails()) {
            $message = [
                'status' => false,
                'message' => $validator->errors()->first(),
            ];
        } else {
            $job_group_id = $requestData['job_group_id'];
            $user_id = $requestData['user_id'];

            $seen_id = DB::table('job_hiring_teams')
                ->where('status', 0)
                ->where('id', $id)
                ->value('id');

            if (!$seen_id) {
                $message = [
                    'status' => true,
                    'message' => "Job hiring team không tồn tại."
                ];
            } else {
                DB::table('job_hiring_teams')
                    ->where('id', $id)
                    ->update([
                        'write_uid' => $user->id,
                        'write_date' => date('Y-m-d H:i:s'),
                        'company_id' => $user->company_id,
                        'job_group_id' => $job_group_id,
                        'user_id' => $user_id,
                    ]);

                $message = [
                    'status' => true,
                    'message' => "Cập nhật job hiring team thành công"
                ];
            }
        }

        $this->addData($message);
        return $this->getResponse();
    }

    public function get_job_hiring_team_by_id(Request $request, $id)
    {
        $user = auth()->user();

        $seen_id = DB::table('job_hiring_teams')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Job inhiring không tồn tại."
            ];
        } else {
            $result = DB::table('job_hiring_teams as d1')
                ->select('d1.id', 'd2.name AS job_role_name', 'd3.name AS user_name', 'd7.id AS avatar_id', 'd5.id AS department_id', 'd5.name AS department_name', 'd6.id AS user_group_id', 'd6.name AS group_name')
                ->leftJoin('job_group as d2', 'd1.job_group_id', '=', 'd2.id')
                ->leftJoin('res_user as d3', 'd1.user_id', '=', 'd3.id')
                ->leftJoin('res_user_company as d4', 'd3.id', '=', 'd4.user_id')
                ->leftJoin('res_company as d5', 'd5.id', '=', 'd4.company_id')
                ->leftJoin('res_user_group as d6', 'd6.id', '=', 'd4.group_id')
                ->leftJoin('document as d7', function ($join) {
                    $join->on('d3.id', '=', 'd7.rel_id')->where('d7.document_type_rel', '=', 'avatar');
                })
                ->where('d1.id', $id)
                ->where('d1.status', 0)
                ->first();

            $data = [
                'id' => $result->id,
                'job_role_name' => $result->job_role_name,
                'user_name' => $result->user_name,
                'avatar_id' => $result->avatar_id,
                'department_id' => $result->department_id,
                'department_name' => $result->department_name,
                'group_id' => $result->user_group_id,
                'group_name' => $result->group_name,
            ];

            $message = [
                'status' => true,
                'data' => ['job_inhiring_teams' => $data],
                'message' => "Lấy job inhiring team by id thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }

    public function delete_job_hiring_team(Request $request, $id)
    {
        $user = auth()->user();

        $seen_id = DB::table('job_hiring_teams')
            ->where('status', 0)
            ->where('id', $id)
            ->value('id');

        if (!$seen_id) {
            $message = [
                'status' => true,
                'message' => "Job hiring team không tồn tại."
            ];
        } else {
            DB::table('job_hiring_teams')
                ->where('id', $id)
                ->update(['status' => 1]);

            $message = [
                'status' => true,
                'message' => "Xóa job hiring team thành công."
            ];
        }

        $this->addData($message);

        return $this->getResponse();
    }
}
