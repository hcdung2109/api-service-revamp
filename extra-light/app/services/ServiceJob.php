<?php

class ServiceJob
{
    public $appSession;
    public $msg;

    public function __construct()
    {
        global $appSession;
        $this->appSession = $appSession;
        $this->msg = $this->appSession->getTier()->createMessage();
    }

    public function parseContent($dt, $s)
    {
        if ($dt->getRowCount() > 0) {
            for ($i = 0; $i < count($dt->getColumns()); $i++) {
                $s = $this->appSession->getTool()->replace($s, "{" . $dt->getColumns()[$i]->getName() . "}", $dt->getStringAt(0, $i));
            }
        }

        return $s;
    }

    public function doCreate($candidate_id, $table_id, $status_id, $session_user_id, $session_company_id)
    {
        $line_id = "";
        $sql = "SELECT d1.id, d1.name, d1.query, d1.description FROM res_status d1 WHERE d1.status = 0 AND d1.table_id='" . $table_id . "'";
        if ($status_id == "") {
            $sql = $sql . " AND (d1.sequence=2)";
        } else {
            $sql = $sql . " AND d1.id='" . $status_id . "'";
        }
        $this->msg->add("query", $sql);
        $arr = $this->appSession->getTier()->getArray($this->msg);
        if (count($arr) > 0) {
            $status_id = $arr[0][0];
            $status_name = $arr[0][1];
            $query = $arr[0][2];
            $status_description = $arr[0][3];
            $dt = NULL;
            if ($query != "") {
                $sql = $this->appSession->getTool()->replace($query, "{id}", $candidate_id);
                $this->msg->add("query", $sql);
                $dt = $this->appSession->getTier()->getTable($this->msg);
                if ($dt->getRowCount() > 0) {
                    $status_description = $this->parseContent($dt, $status_description);
                }
            }
            $builder = $this->appSession->getTier()->createBuilder("res_status_line");
            $line_id = $this->appSession->getTool()->getId();
            $builder->add("id", $line_id);
            $builder->add("create_uid", $session_company_id);
            $builder->add("write_uid", $session_company_id);
            $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
            $builder->add("name", $status_name);
            $builder->add("status_id", $status_id);
            $builder->add("description", $status_description);
            $builder->add("rel_id", $candidate_id);
            $builder->add("status", 0);
            $builder->add("company_id", $session_company_id);
            $sql = $this->appSession->getTier()->getInsert($builder);
            $this->msg->add("query", $sql);
            $this->appSession->getTier()->exec($this->msg);

            $data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $session_company_id . "&message=" . $this->appSession->getTool()->urlEncode("type=database.hq.changed&name=res_status_line");
            $ws = new \WebService($this->appSession);
            $ws->sendMessage($data);

            $sql = "SELECT d1.rel_id, d1.name, d1.type, d1.description FROM res_status_notification d1 WHERE d1.status_id='" . $status_id . "' AND d1.status=0";

            $this->msg->add("query", $sql);
            $notifications = $this->appSession->getTier()->getArray($this->msg);
            for ($i = 0; $i < count($notifications); $i++) {
                $rel_id = $notifications[$i][0];
                $name = $notifications[$i][1];
                $type = $notifications[$i][2];
                $description = $notifications[$i][3];
                if ($dt != NULL) {
                    $rel_id = $this->parseContent($dt, $rel_id);
                    $name = $this->parseContent($dt, $name);
                    $description = $this->parseContent($dt, $description);
                    $nid = $this->appSession->getTool()->getId();
                    $builder->setName("res_notification");
                    $builder->clear();
                    $builder->add("id", $nid);
                    $builder->add("create_uid", $session_user_id);
                    $builder->add("write_uid",  $session_user_id);
                    $builder->add("create_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("write_date", $this->appSession->getTier()->getDateString(), 'f');
                    $builder->add("name", $name);
                    $builder->add("rel_id", $rel_id);
                    $builder->add("notification_id", $candidate_id);
                    $builder->add("description", $description);
                    $builder->add("type", $type);
                    $builder->add("seen", 0);
                    $builder->add("status", 0);
                    $builder->add("company_id",  $session_company_id);
                    $sql = $this->appSession->getTier()->getInsert($builder);
                    $this->msg->add("query", $sql);
                    $this->appSession->getTier()->exec($this->msg);
                    $data = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $session_company_id . "&message=" . $this->appSession->getTool()->urlEncode("type=database.hq.changed&name=res_notification");
                    $ws->sendMessage($data);

                    $sql = "SELECT d1.id, d1.name, d1.description, d1.type, d1.notification_id, d1.create_date, d1.status FROM res_notification d1 WHERE d1.id='" . $nid . "' AND d1.status=0 AND d1.seen=0 AND d1.status!=1";
                    $sql = $sql . " ORDER BY d1.create_date ASC";
                    $this->msg->add("query", $sql);
                    $dt = $this->appSession->getTier()->getArray($this->msg);
            
                    $data = array();
                    for ($i = 0; $i < count($dt); $i++) {
                        $arr = array();
                        $arr['id'] = $dt[$i][0];
                        $arr['name'] = $dt[$i][1];
                        $arr['description'] = $dt[$i][2];
                        $arr['type'] = $dt[$i][3];
                        $arr['notification_id'] = $dt[$i][4];
                        $arr['create_date'] = $dt[$i][5];
                        $arr['status'] = $dt[$i][6];
                        $data[] = $arr;
                    }
                    $datac = "action=28d9aaeb-9535-4939-9eb6-c88f103cd256&id=" . $session_company_id . "&message=".$this->appSession->getTool()->urlEncode(json_encode($data));
                    $ws->sendMessage($datac);
                }
            }
        }
        return $line_id;
    }
}
