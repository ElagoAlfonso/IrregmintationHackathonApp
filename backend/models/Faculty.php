<?php
class Faculty {
    public $id;
    public $name;
    public $department;

    public function __construct($id, $name, $department){
        $this->id = $id;
        $this->name = $name;
        $this->department = $department;
    }
}
?>