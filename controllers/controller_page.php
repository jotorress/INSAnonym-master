<?php
function cmp($a, $b)
    {
        return $a[1]<$b[1];
    }
function index_of_element($array, $element){
    foreach($array as $key=>$value){
      if ($value[0]->id == $element->id){
        return $key+1;
      }
    }
}

class PagesController
{
    public function __construct()
    {
    }

    public function home()
    {

        require_once('views/page/home.php');
    }
    public function start()
	{
        
		require_once('views/page/start.php');
	}
	public function classement()
	{
        global $security;
        
        $users = new User();
        $users_all = $users->getAllUsers();
        
        if($security->listOfidRequestVerify('team')){
            global $con;
            
            $list_id = $_REQUEST['team'];
            $ids = '(0,'.implode(",", $list_id).')';
            
            $con->query("CREATE TEMP VIEW 'anonymisation_view' AS select * from anonymisation where UserId IN $ids");
            $con->query("CREATE TEMP VIEW 'user_view' AS select * from user where id IN $ids");
            $con->query("CREATE TEMP VIEW 'attack_view' AS select * from attack where IdAttacker IN $ids and IdDefencer IN $ids");
            $con->query("CREATE TEMP VIEW 'attack_naive_view' AS select * from (select AttackId,IdAnonymisation,IdAttacker,IdDefencer,score from attack union select 0,SubmissionId,0,UserId,naiveAttack from anonymisation) where IdAttacker IN $ids");
        }
        
		$users = $users->getAllUsers();
        $rank_general = array();
        $rank_by_defense = array();
        $rank_by_attack = array();
            
        foreach ($users as $user){
            $rank_by_defense[] = array($user, $user->getDefenseScore());
            $rank_by_attack[] = array($user, $user->getAttackScore());
        }
        usort($rank_by_defense, "cmp");
        usort($rank_by_attack, "cmp");
        
        foreach ($users as $user){
            $rank_general[] = array($user, index_of_element($rank_by_defense, $user), index_of_element($rank_by_attack, $user));
        }
        
        //Numéro de début du rang
        $rank_by_defense_start = 1;
        $rank_by_attack_start = 1;
		require_once('views/page/classement.php');
	}
    public function about()
	{
        
		require_once('views/page/about.php');
	}

    public function example()
    {

        require_once('views/page/example.php');
    }

    public function error()
    {
        require_once('views/page/error.php');
    }
}
