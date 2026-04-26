<?php
class AuditLog {
    public $id;
    public $user_id;
    public $action;
    public $timestamp;

    public function __construct($id, $user_id, $action, $timestamp){
        $this->id = $id;
        $this->user_id = $user_id;
        $this->action = $action;
        $this->timestamp = $timestamp;
    }
}
?>