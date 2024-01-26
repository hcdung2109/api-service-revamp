<?php

namespace Digisource\Settings\Services\V1;

use Digisource\Settings\Contracts\CandidateLevelRepositoryFactory;
use Digisource\Settings\Contracts\SettingsCandidateServiceFactory;
use Digisource\Settings\Contracts\SourcesRepositoryFactory;
use Digisource\Settings\Repositories\V1\CandidateLevelRepository;
use Digisource\Settings\Repositories\V1\SourcesRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SettingsCandidateService implements SettingsCandidateServiceFactory
{
    private SourcesRepository $sourcesRepository;
    private CandidateLevelRepository $candidateLevelRepository;

    public function __construct(
        SourcesRepositoryFactory $sourcesRepositoryFactory,
        CandidateLevelRepositoryFactory $candidateLevelRepositoryFactory
    ) {
        $this->sourcesRepository = $sourcesRepositoryFactory;
        $this->candidateLevelRepository = $candidateLevelRepositoryFactory;
    }


    // START SETTINGS CANDIDATE SOURCE

    public function getCandidateSources(array $request)
    {
        $p = Arr::get($request, 'p', 0);
        $ps = Arr::get($request, 'ps', null);

        $query = $this->sourcesRepository->where('status', 0)
            ->where('company_id', auth()->user()->company_id);

        if ($ps != null) {
            $result = $query->paginate($ps, ['*'], 'p', $p);
            $current = $result->currentPage();
            return [
                "items" => $result->items(),
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
        } else {
            $skills = $query->get();
            return $skills->toArray();
        }
    }

    public function createCandidateSources($name)
    {
        $user = auth()->user();
        $data = [
            'id' =>Str::uuid(),
            "create_uid" => $user->id,
            "write_uid" => $user->id,
            "create_date" => now(),
            "write_date" => now(),
            "status" => 0,
            "company_id" => $user->company_id,
            "name" => $name
        ];
        $skill = $this->sourcesRepository->create($data);
        return $skill;
    }

    public function updateCandidateSources($id, $name)
    {
        $user = auth()->user();
        $source = $this->sourcesRepository->where('status', 0)->where('id', $id)->first();

        if ($source) {
            // Nguồn tồn tại, thực hiện update
            $data = [
                "write_uid" => $user->id,
                "write_date" => now(),
                "name" => $name
            ];

            return $this->sourcesRepository
                ->where('company_id', $user->company_id) // Lấy company_id từ user
                ->update($id, $data);
        } else {
            // Nguồn không tồn tại, xử lý lỗi
            return new \Exception(__('Source not found'), 404);
        }
    }

    public function getCandidateSourcesById($id)
    {
        $source = $this->sourcesRepository->where('status', 0)->where('id', $id)->first();
        if (!$source) {
            return [
                'message' => "Candidate sources không tồn tại."
            ];
        }

        return [
            'id' => $source->id,
            'name' => $source->name,
        ];
    }


    public function deleteCandidateSources($id)
    {
        $source = $this->sourcesRepository->where('status', 0)->where('id', $id)->first();

        if (!$source) {
            return [
                'message' => "Candidate source không tồn tại."
            ];
        }

        $this->sourcesRepository->delete($id);

        return [
            'message' => "Xóa candidate source thành công."
        ];
    }

    // END SETTINGS CANDIDATE SOURCE




    // START SETTINGS CANDIDATE LEVELS
    public function getCandidateLevels(array $request)
    {
        $p = Arr::get($request, 'p', 0);
        $ps = Arr::get($request, 'ps', null);

        $query = $this->candidateLevelRepository->where('status', 0)
            ->where('company_id', auth()->user()->company_id);

        if ($ps != null) {
            $result = $query->paginate($ps, ['*'], 'p', $p);
            $current = $result->currentPage();
            return [
                "items" => $result->items(),
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
        } else {
            $skills = $query->get();
            return $skills->toArray();
        }
    }

    public function createCandidateLevels($name)
    {
        $user = auth()->user();
        $data = [
            'id' =>Str::uuid(),
            "create_uid" => $user->id,
            "write_uid" => $user->id,
            "create_date" => now(),
            "write_date" => now(),
            "status" => 0,
            "company_id" => $user->company_id,
            "name" => $name
        ];
        $candidateLevel = $this->candidateLevelRepository->create($data);
        return $candidateLevel;
    }

    public function updateCandidateLevels($id, $name)
    {
        $user = auth()->user();
        $source = $this->candidateLevelRepository->where('status', 0)->where('id', $id)->first();

        if ($source) {
            // Nguồn tồn tại, thực hiện update
            $data = [
                "write_uid" => $user->id,
                "write_date" => now(),
                "name" => $name
            ];

            return $this->candidateLevelRepository
                ->where('company_id', $user->company_id) // Lấy company_id từ user
                ->update($id, $data);
        } else {
            // Nguồn không tồn tại, xử lý lỗi
            return new \Exception(__('Candidate level not found'), 404);
        }
    }

    public function getCandidateLevelsById($id)
    {
        $source = $this->candidateLevelRepository->where('status', 0)->where('id', $id)->first();
        if (!$source) {
            return [
                'message' => "Candidate level không tồn tại."
            ];
        }

        return [
            'id' => $source->id,
            'name' => $source->name,
        ];
    }

    public function deleteCandidateLevels($id)
    {
        $source = $this->candidateLevelRepository->where('status', 0)->where('id', $id)->first();
        if (!$source) {
            return [
                'message' => "Candidate level không tồn tại."
            ];
        }
        $this->candidateLevelRepository->delete($id);
        return [
            'message' => "Xóa candidate level thành công."
        ];
    }
    // END SETTINGS CANDIDATE LEVELS
}
