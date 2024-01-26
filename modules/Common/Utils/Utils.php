<?php

namespace Digisource\Common\Utils;

use Digisource\Common\Entities\Documents;
use Digisource\Core\Constant\Constant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Utils
{
    public static function buildFilter($columnName, $idList)
    {
        $filterClauses = [];
        foreach (explode(',', $idList) as $id) {
            if ($id) {
                $filterClauses[] = $columnName . " = '" . $id . "'";
            }
        }

        return implode(' OR ', $filterClauses);
    }


    /**
     * @param UploadedFile $file
     * @param $type
     * @param $ownerId
     * @return string
     * @throws \Exception
     */
    public static function storageFile(UploadedFile $file, $type, $rel_id, $companyId, $isDefault = null): string
    {
        $file_id = uniqid();
        if ($file) {
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::limit($fileName, 190, true);
            $fileName = $rel_id . '-' . $fileName;

            $path = 'uploads/' . Carbon::now()->format('Y/m/d/');
            Storage::makeDirectory($path); // Tạo thư mục nếu chưa tồn tại
            $file->storeAs($path, $fileName);

            $document = new Documents([
                "id" => $file_id,
                "create_uid" => $rel_id,
                "write_uid" => $rel_id,
                "create_date" => now(),
                "write_date" => now(),
                "status" => 0,
                "company_id" => $companyId,
                "name" => $fileName,
                "type" => 'file',
                "rel_id" => $rel_id,
                "path" => $path,
                "ext" => $fileExtension,
                "document_type_rel" => $type,
                "is_default" => $isDefault
            ]);

            $res = $document->save();
            if ($res) {
                return $document->id;
            }
        }
        return new \Exception('Failed to upload file');
    }

    public static function updateFile(UploadedFile $file, $type, $rel_id, $companyId, $isDefault = null): string
    {
        $fileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();

        $path = 'uploads/' . Carbon::now()->format('Y/m/d/');
        Storage::makeDirectory($path); // Tạo thư mục nếu chưa tồn tại

        $fileName = Str::limit($fileName, 190, true);
        $fileName = $rel_id . '-' . $fileName;

        $file->storeAs($path, $fileName);

        $document = Documents::query()
            ->where('status', 0)
            ->where('rel_id', $rel_id)
            ->where('document_type_rel', $type)
            ->select('*')->first();  // Lấy giá trị cột "id" đầu tiên
        if ($document) {
            Storage::delete($document->path . '/' . $document->name);

            $res = $document->update(
                [
                    "write_uid" => $rel_id,
                    "write_date" => now(),
                    "company_id" => $companyId,
                    "name" => $fileName,
                    "type" => 'file',
                    "rel_id" => $rel_id,
                    "path" => $path,
                    "ext" => $fileExtension,
                    "document_type_rel" => $type,
                    "is_default" => $isDefault
                ]
            );
            if ($res) {
                return $document->id;
            }
        } else {
            $document = new Documents([
                "id" => uniqid(),
                "create_uid" => $rel_id,
                "write_uid" => $rel_id,
                "create_date" => now(),
                "write_date" => now(),
                "status" => 0,
                "company_id" => $companyId,
                "name" => $fileName,
                "type" => 'file',
                "rel_id" => $rel_id,
                "path" => $path,
                "ext" => $fileExtension,
                "document_type_rel" => $type,
                "is_default" => $isDefault
            ]);
            $res = $document->save();
            if ($res) {
                return $document->id;
            }
        }
        return new \Exception('Failed to upload file');
    }


}
