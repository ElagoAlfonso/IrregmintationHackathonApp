<?php
class Evaluation {
    public $id;
    public $faculty_id;
    public $rater_role;
    public $score1;
    public $score2;
    public $score3;
    public $avg_score;

    public function __construct($id, $faculty_id, $rater_role, $score1, $score2, $score3, $avg_score){
        $this->id = $id;
        $this->faculty_id = $faculty_id;
        $this->rater_role = $rater_role;
        $this->score1 = $score1;
        $this->score2 = $score2;
        $this->score3 = $score3;
        $this->avg_score = $avg_score;
    }
}
?>