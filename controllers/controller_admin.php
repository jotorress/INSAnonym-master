<?php
class AdminController
{
    public function __construct()
    {
    }

    public function upload(){
        global $security;
        //Si la demande est bien formatée
        if($security->uploadVerify()){
            Admin::uploadInitialFile();
        }
    }

    public function uploadScript(){
        global $security;
        //Si la demande est bien formatée
        if($security->uploadVerify()){
            Admin::uploadScriptFile();
        }
    }

    public function board()
    {
        global $security;
        global $submission_activated;
        global $attack_activated;

	// Activation/Desactivation de la phase de soumission
	   if($security->booleanRequestVerify('submission_phase')){
            $page = new Page();
            if($_REQUEST['submission_phase']=='true')
                $page->activatePage("anonymisation");
            else
                $page->deactivatePage("anonymisation");
            header("Refresh:0"); // On rafraichit l'entete du site
        }

	// Activation/Desactivation de la phase d'attaque
        if($security->booleanRequestVerify('attack_phase')){
            $page = new Page();
            if($_REQUEST['attack_phase']=='true')
                $page->activatePage("attack");
            else
                $page->deactivatePage("attack");
            header("Refresh:0"); // On rafraichit l'entete du site
        }

	// Expultion d'une equipe
        if($security->idRequestVerify('kickID')){
            $user = new User();
            $user = $user->getUserByID($_REQUEST['kickID']);
            $result = $_SESSION['admin']->kickUser($user);

            if($result){
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Disqualification réalisée avec succès</div>
            </div>');
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la requête SQL</div>
            </div>');
            }
	   }

	// Modification du nom d'une equipe
        if($security->idRequestVerify('id') && $security->nameRequestVerify('newName')){
            $user = new User();
            $user = $user->getUserByID($_REQUEST['id']);

		    $result = $_SESSION['admin']->changeUserName($user, $_REQUEST['newName']);

            if($result){
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Modification réalisée avec succès</div>
            </div>');
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la requête SQL</div>
            </div>');
            }
	   }



	// Modification du mot de passe d'une equipe
      if(isset($_POST['newPasswd'])){
          if($security->passwordRequestVerify('newPasswd')){
            if (!$security->passwordIsStrong('newPasswd')){
              echo('<div class="bs-component">
                <div class="alert alert-dismissible alert-danger">
                  <button type="button" class="close" data-dismiss="alert">×</button>Mot de passe faible: minimum un chiffre, un caractère spécial, une miniscule et une majuscule requis, longueur de 8 caractères minimum</div>
              </div>');
            } else{
              $user = new User();
              $user = $user->getUserByID($_REQUEST['id']);
              if($user->changeUserPasswd($_POST['newPasswd'])){
                echo('<div class="bs-component">
                <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Mot de passe modfié!</div>
                </div>');
              }
            }
          }else{
            echo('<div class="bs-component">
            <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>Impossible de changer le mot de passe</div>
            </div>');
          }

        }




	// Generation d'une clef d'invitation
        if($security->nameRequestVerify('deletekey')){
            $key = new InviteKey($_REQUEST['deletekey']);
            if($key->isValid()){
                if($key->delete()){
                    echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>La clé a bien été supprimé</div>
            </div>');
                } else {
                    echo('<div class="bs-component">
                  <div class="alert alert-dismissible alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la requête SQL</div>
                </div>');
                }
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Impossible de supprimer la clé</div>
            </div>');
            }
	   }

        if($security->nameRequestVerify('newkey')){
            $key = new InviteKey($_REQUEST['newkey']);
            if($key->add()){
                    echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>La clé a bien été ajouté</div>
            </div>');
            } else {
                echo('<div class="bs-component">
                  <div class="alert alert-dismissible alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la requête SQL</div>
                </div>');
            }
	   }

	$anonym = new Anonym();
	// Suppression d'une soumission
        if($security->idRequestVerify('subToDelete')){
	    $anonymToDelete = $anonym->getFromId($_POST['subToDelete']);
            if($anonymToDelete->delete()){
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Soumission supprimé avec succès</div>
            </div>');
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Impossible de supprimer la soumission</div>
            </div>');
            }
	   }

	// Selection des metrics
	if(isset($_REQUEST['metricNames']) && isset($_REQUEST['aggregName'])){
		$_SESSION['admin']->setMetrics($_REQUEST['metricNames']);
        $_SESSION['admin']->setAggregation($_REQUEST['aggregName']);
	}

    // Mise à jour des paramètres de la métrique
	if(isset($_REQUEST['metricParametersUpdate']) && isset($_REQUEST['metricName'])){
		$_SESSION['admin']->setMetric($_REQUEST['metricName'], $_REQUEST['metricParametersUpdate']);
	}

	// Recuperation des metrics
	$metrics = $_SESSION['admin']->getAllMetrics();
    $activeMetrics = $_SESSION['admin']->getSelectedMetrics();
    $mectricsParameters = [];
    foreach($activeMetrics as $metric){
        $mectricsParameters[$metric] = $_SESSION['admin']->getMetricParameters($metric);
    }
    $selectedAggreg = $anonym->getAggregation();

	// Recuperation des soumissions
	$submissions = $anonym->getAllSubmissions();
	$userSub = new User();

        //Display the board page with updated users
        $users = new User();
        $users=$users->getAllUsers();

        $keys = new InviteKey(0);
        $keys = $keys->getAll();
        $securekey = substr($security->idGenerator(),0,6);
        require_once('views/admin/board.php');
	}

}
