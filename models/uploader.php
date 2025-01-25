<?php

class Uploader
{
	public static function execInBackground($input, $shuffle, $footprint) {
		$cmd = "python3 scripts/FandS.py " . $input . " ". $shuffle . " " . $footprint;
		if (substr(php_uname(), 0, 7) == "Windows"){
			pclose(popen("start /B " . $cmd, "r"));
		}
		else {
			exec($cmd . " > /dev/null &");
		}
	}
    public static function anonym()
    {
        //Début du téléversement
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = "uploads/$fileName";


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

        // Vérification si le fichier a bien fini d'être uploadé
        if (!$chunks || $chunk == $chunks - 1) {
          global $security;
          $fileName = $security->idGenerator();
          rename("{$filePath}.part", "uploads/".$fileName.".zip");

          $FFileName = $security->idGenerator();
          $FFile = fopen("uploads/".$FFileName, "w");
          $SFileName = $security->idGenerator();
          $SFile = fopen("files/".$SFileName, "w");

          // Le fichier est uploadé, on crée une nouvelle soumission
          $anonym = new Anonym();
          $anonym->setSubmission($fileName,$_SESSION['user']->id,$FFileName,$SFileName,"processing",$_REQUEST["name"]);

		  self::execInBackground($fileName, $SFileName, $FFileName);
        }
    }

    public static function attack()
    {
        include_once("scripts/attackScore.php");

        global $security;
    		$tmpfile = $security->idGenerator();

        if (move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/".$tmpfile)) {
            $default_score = 10;
            $anonymisation = new Anonym();
            $anonymisation = $anonymisation->getFromId($_REQUEST["anonymisationID"]);
						if($anonymisation){
							$teamAttack = $_SESSION['user'];
							$teamDefence = new User();
							$teamDefence = $teamDefence->getUserByID($anonymisation->userId);
							if($teamDefence->id != $teamAttack->id){
								// On enregistre la nouvelle attack
								$attack = new Attack();
								$result = checkAttackJson("uploads/".$anonymisation->F_file, "uploads/".$tmpfile);
								if($result==-1){
									return false;
								}
								$attack->add($anonymisation,$teamAttack,$teamDefence, $result, $tmpfile);
								return true;
							}
						}
						return false;
        } else {
            return false;
        }
    }
}
