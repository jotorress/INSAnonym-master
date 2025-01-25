<?php

class Attack
{
    public $AttackId;
    public $anonymisation;
    public $teamAttack;
	  public $teamDefence;
    public $score;

    public function __construct(
        $AttackId = false,
        $anonymisation = false,
        $teamAttack = false,
        $teamDefence = false,
		    $score = false
    ) {
        if ($teamAttack === false) return;

        $this->AttackId = $AttackId;
        $this->anonymisation = $anonymisation;
        $this->teamAttack = $teamAttack;
        $this->teamDefence = $teamDefence;
		    $this->score = $score;
    } //end construct

	public function add($anonymisation,$teamAttack,$teamDefence,$score,$file)
	{
		global $con;
        $req = $con->query("INSERT INTO attack (IdAnonymisation,IdAttacker,IdDefencer,score,file) VALUES('$anonymisation->submissionId','$teamAttack->id','$teamDefence->id','$score','$file')");
        if($req){
            return true;
        } else {
            return false;
        }
	}


}
