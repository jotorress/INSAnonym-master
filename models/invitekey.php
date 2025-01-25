<?php

class InviteKey
{
    public $key;

    public function __construct($key) {
        $this->key = $key;
    } //end construct
    
    // Check if the key is valid (present in the database)
    public function isValid()
    {
        global $con;
        $req = $con->query("SELECT count(key) FROM invitekey WHERE key = '$this->key'");
        if($req->fetchColumn()>0){
            return true;
        } else {
            
            return false;
        }
    }
    
    public function getAll(){
		global $con;
        $list = array();
        
        $keys = $con->query("SELECT key FROM invitekey");

        foreach ($keys as $key)
            $list[] = $key['key'];
        return $list;
	}
    
    public function delete()
	{
		global $con;
        
        $req = $con->query("DELETE FROM `invitekey` WHERE `key` = '$this->key'");
        if($req){
            return true;
        } else {
            return false;
        }
	}	
    
    public function add()
	{
		global $con;

        $req = $con->query("INSERT INTO invitekey (key) VALUES('$this->key')");
        if($req){
            return true;
        } else {
            return false;
        }
	}
}
