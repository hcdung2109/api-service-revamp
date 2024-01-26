<?php

namespace AppLight\Controllers;

use AppSession;
use App\Http\Controllers\Controller;

class DocumentController
{

    // START CREATE DOCUMENT
    public static function create_document(AppSession $appSession, $msg, $type, $file, $rel_id)
    {
        $fileName = $file->getClientOriginalName();

        $path = DOC_PATH;
        $dir = "";
        $current_date =  date('Y-m-d');
        if ($current_date != "") {
            $arr_date = explode("-", $current_date);
            if (count($arr_date) > 2) {
                $dir = $arr_date[0] . "/" . +$arr_date[1] . "/" . +$arr_date[2];
            }
        }
        $path = $path . "/" . $dir;
        if (is_dir($path) == false) {
            if (!mkdir($path, 0777, true)) {
                die('Failed to create folders...');
            }
        }

        $file_id = $appSession->getTool()->getId();

        $file_name = $path . "/";

        try {

            $file->move($file_name, $file_id);
            $file = fopen($file_name, "wb");

            fwrite($file, base64_decode($file_name));
            fclose($file);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload file');
        }

        $extension = "";
        $arr = explode(".", $fileName);
        if (count($arr) > 0) {
            $extension = $arr[count($arr) - 1];
            $name = $arr[0];
        }

        $builder = $appSession->getTier()->createBuilder("document");
        $builder->add("id", $file_id);
        $builder->add("create_uid", $appSession->getConfig()->getProperty("session_user_id"));
        $builder->add("write_uid", $appSession->getConfig()->getProperty("session_user_id"));
        $builder->add("create_date", $appSession->getTier()->getDateString(), 'f');
        $builder->add("write_date", $appSession->getTier()->getDateString(), 'f');
        $builder->add("status", 0);
        $builder->add("company_id", $appSession->getConfig()->getProperty("session_company_id"));
        $builder->add("name", str_replace("'", "''", $name));
        $builder->add("type", 'file');
        $builder->add("rel_id", $rel_id);
        $builder->add("path", $dir);
        $builder->add("ext", $extension);
        $builder->add("document_type_rel",  str_replace("'", "''", $type));
        $sql = $appSession->getTier()->getInsert($builder);
        $msg->add("query", $sql);
        $result =  $appSession->getTier()->exec($msg);

        return $file_id;
    }

    // END CREATE DOCUMENT

}
