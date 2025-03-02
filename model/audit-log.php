<?php
require_once('database.php');

class AuditLog extends Database {

    private $audit_log_id;
    private $time_stamp;
    private $entity;
    private $member_id;
    private $action;
    private $entry;

    private $table_name;

    /**
     * Constructor.
     * 
     * @param $entity       name of business object alterned - required.
     * @param $member_id    id of user who performed the action - required.
     * @param $action       Login, Logout, Update, Insert, or Delete - required.
     * @param $entry        description of action, successful, unsuccessful, and reason - required.
     * @param $time_stamp   created when log method is called - defaults to null.
     * @param $audit_log_id defaults to null.
     */
    
    public function __construct(
        $entity,
        $member_id,
        $action,
        $entry,
        $time_stamp=null,
        $audit_log_id=null
    ) {
        parent::__construct();
        $this->table_name = "auditLogs";

        $this->setAuditLogId($audit_log_id);
        $this->setTimeStamp($time_stamp);
        $this->setEntity($entity);
        $this->setMemberId($member_id);
        $this->setAction($action);
        $this->setEntry($entry);
    }
    public function __destruct() {
        //echo "Destroying AuditLog object";
    }

    public function setAuditLogId($audit_log_id=null) {
        if ($audit_log_id) {
            $this->audit_log_id = $audit_log_id;
        }
    }
    public function setTimeStamp($time_stamp=null) {
        if ($time_stamp) {
            $this->time_stamp = $time_stamp;
        }
    }
    public function setEntity($entity=null) {
        if ($entity) {
            $this->entity = $entity;
        }
    }
    public function setMemberId($member_id=null) {
        if ($member_id) {
            $this->member_id = $member_id;
        }
    }
    public function setAction($action=null) {
        if ($action) {
            $this->action = $action;
        }
    }
    public function setEntry($entry=null) {
        if ($entry) {
            $this->entry = $entry;
        }
    }

    public function getAuditLogId() {
        return $this->audit_log_id;
    }
    public function getTimeStamp() {
        return $this->time_stamp;
    }
    public function getEntity() {
        return $this->entity;
    }
    public function getMemberId() {
        return $this->member_id;
    }
    public function getAction() {
        return $this->action;
    }
    public function getEntry() {
        return $this->entry;
    }

    /**
     * Method log
     * 
     * Saves the log entry to the database. Cannot override or update existing logs
     */

    public function log() {
        $ret_code = 9;

        if (!$this->time_stamp) {
            $this->setTimeStamp(date("c"));
        }

        if ($this->audit_log_id) {
            //echo "Error: log entry already exists";
        } else {
            if ($this->member_id == null) {
                $member_id = 'null';
            } else {
                $member_id = $this->member_id;
            }

            $sql = <<<EOD
            INSERT INTO $this->table_name
            (timeStamp, entity, memberId, action, entry)
            VALUES (
                '$this->time_stamp',
                '$this->entity',
                 $member_id,
                '$this->action',
                '$this->entry'
            )
            EOD;

            //echo "<br>".$sql."<br>";
    
            if ($this->run($sql)) {
                // Once INSERT is done, retrieve the new audit_log_id and store
                $this->audit_log_id = $this->getConn()->insert_id;
                $this->commit();
                $ret_code = 0;
            } else {
                $ret_code = 2;
            }
        }
        return $ret_code;
    }

}

?>

