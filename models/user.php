<?php

class User
{
    public $id;
    public $username;
    public $team;

    public function __construct(
        $id = false,
        $username = false,
        $team = false
    ) {
        if ($id === false) return;

        $this->id = $id;
        $this->username = $username;
        $this->team = $team;
    } //end construct

    public function getUserByID($id)
    {
        global $con;

        $req = $con->query("SELECT id,username,team FROM user where id='$id' limit 1");
        if($req){
            $result = $req->fetch();
            if(isset($result['id'])){
                return new User($result['id'],$result['username'],$result['team']);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // The username is unique (as the identifier)
    public function getUserByUsername($username)
    {
        global $con;

        $req = $con->query("SELECT id,username,team FROM user where username='$username' limit 1");
        if($req){
            $result = $req->fetch();
            return new User($result['id'],$result['username'],$result['team']);
        } else {
            return false;
        }
    }
    public function changeUserPasswd( $newpasswd){
        global $con;
        global $security;
            $salt = $this->getSaltByUsername($this->username);
            if($salt){
              $newpassword = hash('sha256', $salt.$newpasswd);
              $req = $con->query("SELECT id FROM user where username='$this->username' and password='$newpassword'");
              if($req->fetchColumn()>0){

                  return false;
              } else {

                $salt = $security->idGenerator();
                $newpassword = hash('sha256', $salt.$newpasswd);
                $req = $con->query("UPDATE user SET salt='$salt', password='$newpassword' WHERE username='$this->username'");
                if($req){
                  return true;
                }else{
                  return false;
                }
              }

            }
            return false;
    }
    // The username is unique (as the identifier)
    public function getSaltByUsername($username)
    {
        global $con;

        $req = $con->query("SELECT salt FROM user where username='$username' limit 1");
        if($req){
            $result = $req->fetch();
            if(isset($result['salt'])){
                return $result['salt'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function login($username, $password)
    {
        global $con;
        $salt = $this->getSaltByUsername($username);

        if($salt){
            $password = hash('sha256', $salt.$password);

            $req = $con->query("SELECT id FROM user where username='$username' and password='$password'");
            if($req->fetchColumn()>0){
                return (new User())->getUserByUsername($username);
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

	public function isAdmin()
	{
		global $con;
        $req = $con->query("SELECT id FROM admin WHERE id = '$this->id'");
        if($req->fetchColumn()>0){
            return true;
        } else {
            return false;
        }
	}

	public function register($username, $password, $team)
	{
		global $con;
        global $security;
        $salt = $security->idGenerator();
		$password = hash('sha256', $salt.$password);

        $req = $con->query("INSERT INTO user (username, password, salt, team) VALUES('$username','$password','$salt','$team')");
        if($req){
            return true;
        } else {
            return false;
        }
	}

	public function getAllUsers(){
		global $con;
        $list = array();

        $usernames = $con->query("SELECT username FROM user_view WHERE id NOT IN admin");

        foreach ($usernames as $username)
            $list[] = $this->getUserByUsername($username['username']);
        return $list;
	}

  public function getSubmissions()
  {
      global $con;
      $list = array();

      $req = $con->query("SELECT * FROM anonymisation where UserId='$this->id'");
      foreach ($req as $result)
          $list[] = new Anonym($result['SubmissionId'],$result['fileLink'],$result['UserId'],$result['F_file'], $result['S_file'] ,$result['utility'],$result['naiveAttack'],$result['status'],$result['IsPublished'],$result['name']);
      return $list;
  }
  public function getPublishedSubmissions()
      {
          $list = array();

          foreach ($this->getSubmissions() as $result){
              if($result->isPublished) $list[] = $result;
            }
          return $list;
      }

    public function setScore($user,$score)
	{
		global $con;

        $req = $con->query("UPDATE `user` SET score = '$score' WHERE `id` = '$user->id'");
        if($req){
            return true;
        } else {
            return false;
        }
	}

    public function getAttackScoreOfSubmission($submission)
	{
		global $con;

        $req = $con->query("SELECT max(score) FROM (select 0.0 as score union SELECT score FROM attack where IdAnonymisation='$submission->submissionId' and IdAttacker='$this->id')");
        if($req){
            $result = $req->fetch();
            if(isset($result['max(score)'])){
                return $result['max(score)'];
            } else {
                return 0.0;
            }
        } else {
            return 0.0;
        }
	}
  public function getAllAttacksOfSubmission($submission)
      {
            $list = array();
            global $con;
            $req = $con->query("SELECT * FROM attack where IdAnonymisation='$submission->submissionId' and IdAttacker='$this->id'");
            foreach ($req as $attack){
              $list[] = new Attack($attack['AttackId'],$attack['IdAnonymisation'],$attack['IdAttacker'], $attack['IdDefencer'], $attack['score']);
            }
          return $list;
      }
    public function getNumberOfAttackSubmission($submissionId){
        global $con;
        $req = $con->query("SELECT COUNT(*) FROM attack where IdAnonymisation='$submissionId' and IdAttacker='$this->id' and score != 'NAN'");
        if($req){
            $result = $req->fetch();
            return $result;
        }else{
          return -1;
        }
    }

    public function getDefenseScore()
	{
        $scores = $this->getDetailedDefenseScore();
        $max_score = [0];
        foreach ($scores as $score)
            $max_score[] = $score['score'];
        return max($max_score);
	}

    public function getDetailedDefenseScore()
	{
        global $con;
        $list = array();

        $submissions = $con->query("SELECT SubmissionId, name, utility,IFNULL(
                                    (
                                        SELECT max(score)
                                        from attack_naive_view
                                        where IdDefencer='$this->id' and IdAnonymisation=SubmissionId)
                                    , 0.0
                                    ) as best_attack,
                                    (
                                        SELECT IdAttacker
                                        from attack_naive_view
                                        where IdDefencer='$this->id' and IdAnonymisation=SubmissionId
                                        order by score
                                        desc
                                    ) as best_attack_team, IFNULL(
                                    (
                                        SELECT (1-max(score))
                                        from attack_naive_view
                                        where IdDefencer='$this->id' and IdAnonymisation=SubmissionId)
                                    , 1.0
                                    ) * utility as score
                                from anonymisation_view where UserId='$this->id' and IsPublished=1");

        foreach ($submissions as $submission)
            $list[] = $submission;
        return $list;
	}

    public function getAttackScore()
	{
        $scores = $this->getDetailedAttackScore();
        $sum_score = [0];
        foreach ($scores as $score)
            $sum_score[] = $score['result'];
        return array_sum($sum_score);
	}

    public function getDetailedAttackScore()
	{
        global $con;
        $list = array();

        $submissions = $con->query("select UserId, SubmissionId, name, min(score) as result from (
                                        select SubmissionId,name,UserId, (
                                            select max(score) FROM (select 0.0 as score union SELECT score FROM attack_view where IdAnonymisation=SubmissionId and IdAttacker='$this->id')
                                            ) as score
                                        from anonymisation_view
                                        where IsPublished=1 and UserId!='$this->id'
                                    ) group by UserId
                                    ");

        foreach ($submissions as $submission)
            $list[] = $submission;
        return $list;
	}
    
    public function getDetailedUserIdAttackScore($userId)
	{
        global $con;
        $list = array();

        $submissions = $con->query("select UserId, SubmissionId, name, score as result from (
                                        select SubmissionId,name,UserId, (
                                            select max(score) FROM (select 0.0 as score union SELECT score FROM attack_view where IdAnonymisation=SubmissionId and IdAttacker='$this->id')
                                            ) as score
                                        from anonymisation_view
                                        where IsPublished=1 and UserId='$userId'
                                    )
                                    ");

        foreach ($submissions as $submission)
            $list[] = $submission;
        return $list;
	}
}
