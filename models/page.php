<?php

class Page
{
    public $name;


    public function __construct(
        $name = false
    ) {
        $this->name = $name;
    } //end construct


    public function deactivatePage($pageName)
	{
		global $con;
        
        $req = $con->query("INSERT INTO `deactivated_pages` (page_name) VALUES('$pageName')");
        if($req){
            return true;
        } else {
            return false;
        }
	}

    public function activatePage($pageName)
	{
		global $con;
        
        $req = $con->query("DELETE FROM `deactivated_pages` WHERE `page_name` = '$pageName'");
        if($req){
            return true;
        } else {
            return false;
        }
	}

    public function isActivated($pageName){
		global $con;
        
        $req = $con->query("SELECT page_name FROM deactivated_pages WHERE page_name = '$pageName'");
        foreach ($req as $line) // Si un tuple est présent de le résultat on retourne false
            return false;
        return true;
	}
    
    public function isCurrent($pageName){
		return $_GET['r']==$pageName;
	}
}
