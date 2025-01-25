<?php

class Admin
{
    public $user;


    public function __construct(
        $user = false
    ) {
        if ($user === false) return;

        $this->user = $user;
    } //end construct


	public function changeUserName($user, $newName)
	{
		global $con;
        
        $req = $con->query("UPDATE `user` SET team = '$newName' WHERE `id` = '$user->id'");
        if($req){
            return true;
        } else {
            return false;
        }
	}

	public function kickUser($user)
	{
		global $con;
        
        $req = $con->query("DELETE FROM `user` WHERE `id` = '$user->id'");
        if($req){
            return true;
        } else {
            return false;
        }
	}

        public function getSelectedMetrics()
	{
            $list = array();
            global $con;
            $reqs = $con->query("SELECT * FROM metric");
            foreach ($reqs as $req){
              $list[] = $req['name'];
            }
          return $list;
	}

	public function getAllMetrics()
	{
            $list = array();
            global $con;

            $scriptNames = scandir('scripts/metrics');
            foreach ($scriptNames as $scriptName){
              if($scriptName!="." && $scriptName!=".." && $scriptName!="__pycache__")
                  $list[] = $scriptName;
            }
            return $list;
	}
    
    public function getMetricParameters($metric)
	{
            global $con;
        
            $req = $con->query("SELECT parameters FROM metric where name='$metric' limit 1");
            if($req){
                $result = $req->fetch();
                if(isset($result['parameters'])){
                    return $result['parameters'];
                }
            }
            return '{}';
	}

    public function setMetrics($metrics)
	{
            global $con;
            $name_list = "'".implode("','", $metrics)."'";
                
            $req = $con->query("DELETE FROM metric WHERE name not in ($name_list)");
            foreach($metrics as $name){
	         $req = $con->query("INSERT INTO metric (name) VALUES('$name')");
	    }
            if($req){
                return true;
            } else {
                return false;
            }
	}
    
    public function setMetric($metric, $parameters)
	{
            global $con;
        
            $req = $con->query("UPDATE metric SET parameters='$parameters' WHERE name='$metric'");
            if($req){
                return true;
            } else {
                return false;
            }
	}

	public function setAggregation($aggreg)
	{
            global $con;
            $req = $con->query("DELETE FROM aggregation");
	    $req = $con->query("INSERT INTO aggregation (name) VALUES('$aggreg')");
            if($req){
                return true;
            } else {
                return false;
            }
	}


	public static function uploadInitialFile()
    	{
        //Début du téléversement
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = "files/$fileName";

        // Open temp file
        $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if ($out) {
          // Read binary input stream and append it to temp file
          $in = fopen($_FILES['file']['tmp_name'], "rb");

          if ($in) {
            while ($buff = fread($in, 4096))
              fwrite($out, $buff);
          } else
            die('{"OK": 0, "info": "Failed to open input stream."}');

          fclose($in);
          fclose($out);

          unlink($_FILES['file']['tmp_name']);
        }
		if (!$chunks || $chunk == $chunks - 1) {
          rename("{$filePath}.part", "files/c3465dad3864bb1e373891fdcfbfcca5f974db6a9e0b646584e07c5f554d7df7.zip");
          unlink("files/c3465dad3864bb1e373891fdcfbfcca5f974db6a9e0b646584e07c5f554d7df7");
        }
    	}

        
        public static function uploadScriptFile()
    	{
        //Début du téléversement
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = "scripts/metrics/$fileName";


        // Open temp file
        $out = fopen("{$filePath}", $chunk == 0 ? "wb" : "ab");
        if ($out) {
          // Read binary input stream and append it to temp file
          $in = fopen($_FILES['file']['tmp_name'], "rb");

          if ($in) {
            while ($buff = fread($in, 4096))
              fwrite($out, $buff);
          } else
            die('{"OK": 0, "info": "Failed to open input stream."}');

          fclose($in);
          fclose($out);

          unlink($_FILES['file']['tmp_name']);
        }
    	}

}
