<?php

class Anonym
{
    public $submissionId;
    public $fileLink;
    public $userId;
    public $F_file;
    public $S_file;
    public $utility;
    public $naiveAttack;
    public $status;
    public $isPublished;
    public $name;
    public function __construct(
        $submissionId = false,
        $fileLink = false,
        $userId = false,
        $F_file = false,
        $S_file = false,
        $utility = false,
		$naiveAttack = false,
        $status = false,
        $isPublished = false,
        $name = false
    ) {
        if ($fileLink  === false) return;
        $this->submissionId = $submissionId;
        $this->fileLink = $fileLink;
        $this->userId = $userId;
        $this->F_file = $F_file;
        $this->S_file = $S_file;
        $this->utility = $utility;
		$this->naiveAttack = $naiveAttack;
        $this->status = $status;
        $this->isPublished =$isPublished;
        $this->name =$name;
    } //end construct

    public function getFromId($id)
    {
        global $con;

        $req = $con->query("SELECT * FROM anonymisation where SubmissionId='$id' limit 1");
        if($req){
            $result = $req->fetch();
            if(isset($result['SubmissionId'])){
                return new Anonym($result['SubmissionId'],$result['fileLink'],$result['UserId'],$result['F_file'], $result['S_file'] ,$result['utility'],$result['naiveAttack'],$result['status'],$result['IsPublished'],$result['name']);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getAllSubmissions()
  {
      global $con;
      $list = array();

      $req = $con->query("SELECT * FROM anonymisation");
      foreach ($req as $result)
          $list[] = new Anonym($result['SubmissionId'],$result['fileLink'],$result['UserId'],$result['F_file'], $result['S_file'] ,$result['utility'],$result['naiveAttack'],$result['status'],$result['IsPublished'],$result['name']);
      return $list;
  }

    public function publish(){
          global $con;

          $req = $con->query("UPDATE anonymisation SET IsPublished = 1 WHERE SubmissionId = '$this->submissionId';");
          if($req){
              return true;
          } else {
              return false;
          }
        }

    public function unpublish(){
      global $con;

      $req = $con->query("UPDATE anonymisation SET IsPublished = 0 WHERE SubmissionId = '$this->submissionId';");
      if($req){
          return true;
      } else {
        return false;
        }
      }

    public function rename($newName){
      global $con;

      $req = $con->query("UPDATE anonymisation SET name = '$newName' WHERE SubmissionId = '$this->submissionId';");
      if($req){
          return true;
      } else {
          return false;
        }
      }

    public function delete(){
        global $con;
        $req = $con->query("DELETE FROM `anonymisation` WHERE `SubmissionId` = '$this->submissionId'");
        if($req){
            unlink("uploads/".$this->F_file);
            unlink("files/".$this->S_file);
            unlink("files/".$this->S_file.".zip");
            unlink("uploads/".$this->fileLink);
            unlink("uploads/".$this->fileLink.".zip");
            return true;
        } else {
            return false;
        }
    }

    public function getAggregation()
	{
            global $con;
            $reqs = $con->query("SELECT * FROM aggregation");
            foreach ($reqs as $req){
              return $req['name'];
            }
            return false;
	}

    public function getAttacks()
    {
        global $con;

        $list = array();
        $req = $con->query("SELECT AttackId,IdAnonymisation,IdAttacker,IdDefencer,score FROM attack where IdAnonymisation='$this->submissionId'");

        foreach ($req as $result)
            $list[] = new Attack($result['AttackId'],$result['IdAnonymisation'],$result['IdAttacker'],$result['IdDefencer'],$result['score']);
        return $list;
    }

	public function setSubmission($fileLink,$team ,$F_file, $S_file,$status,$fileName)
	{
		global $con;

        $req = $con->query("INSERT INTO anonymisation (fileLink,UserId ,F_file, S_file,status,name) VALUES('$fileLink','$team' ,'$F_file', '$S_file','$status','$fileName')");
        if($req){
            return true;
        } else {
            return false;
        }
	}
}
