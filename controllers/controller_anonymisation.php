<?php


class AnonymisationController
{
    public $errorMessage;
    public $errorMessages;
    public function __construct()
    {
      $this->errorMessage ="";
      $this->errorMessages = ["le fichier original est invalide","le fichier déposé est invalide\n (mauvais nombre de colonnes)",
              "le fichier déposé est invalide\n (mauvais format)","le fichier déposé est invalide\n (mauvais nombre de lignes)",
              "un utilisateur posséde plusieurs identifiants par mois","un identifiant est manquant",
              "erreur dans le calcul d’utilité (Vérifiez votre fichier déposé)"];
    }
    public function checkNumberofSubPublished($submissions){
          $numberSubPublished =0;
          foreach($submissions as $submission){
            if($submission->isPublished == 1){
              $numberSubPublished +=1;
            }
          }
          if($numberSubPublished>=3){
            return false;
          }else{
            return true;
          }
    }
    public function publishSub($submissions){
      global $security;
          $anonym = new Anonym();
          if($this->checkNumberofSubPublished($submissions)){
            $anonym = $anonym->getFromId($_POST['subToPublish']);
            if($anonym != false){
              if (!$security->idCheck($anonym->userId)){
                return false;
              }

              if(strcmp ( $anonym->status ,"processing" )==0){
                $this->errorMessage ="Publication en cours de traitement";
                return false;
              }else{

                if($anonym->utility < 0){
                  $this->errorMessage = $this->errorMessages[-$anonym->utility - 1];
                  return false;
                }else{
                    return $anonym->publish();
                }

              }
            }else{
              $this->errorMessage ="fichier inexistant";
              return false;
            }
          }else{
            $this->errorMessage ="trois soumissions faites";
            return false;
          }

    }
    public function unpublishSub(){
      global $security;
          $anonym = new Anonym();

          $anonym = $anonym->getFromId($_POST['subToUnpublish']);
          if($anonym != false){
            if (!$security->idCheck($anonym->userId)){
              return false;
            }
            return $anonym->unpublish();
          }else{
            return false;
          }
    }
    public function deleteSub(){
      global $security;
        $anonym = new Anonym();
        $anonym = $anonym->getFromId($_POST['subToDelete']);
        if (!$security->idCheck($anonym->userId)){
          return false;
        }
        if($anonym != false){
            if(strcmp ( $anonym->status ,"processing" )==0){
                $this->errorMessage = "Publication en cours de traitement";
                return false;
            }else{
                return $anonym->delete();
            }

        } else {
            return false;
        }
    }

    public function renameSub(){
      global $security;
        $anonym = new Anonym();
        $anonym->submissionId = $_POST['subToRename'];
        return $anonym->rename($_POST['newName']);
    }

    public function upload(){
        global $security;
        //Si la demande est bien formatée
        if($security->uploadVerify() && count($_SESSION['user']->getSubmissions())<=20){
            Uploader::anonym();
            echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Upload réalisée avec succès</div>
            </div>');
        }
    }

    public function submission()
    {
        global $security;
        $submissions = $_SESSION['user']->getSubmissions();

        //S'il y a une demande de changement de nom de soumission
        if(isset($_POST['newName'])){
        if($security->nameRequestVerify('newName')){
            if($this->renameSub()){
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Nom de la soumission changé avec succès</div>
            </div>');
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors du changement de nom</div>
            </div>');
            }
	 }
         else{
             echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Caractères spéciaux non autorisés</div>
            </div>');
         }}

        //S'il y a une demande de suppression
        if($security->idRequestVerify('subToDelete')){
            if($this->deleteSub()){
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-success">
                <button type="button" class="close" data-dismiss="alert">×</button>Soumission supprimée avec succès</div>
            </div>');
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Impossible de supprimer la soumission: '.$this->errorMessage.'</div>
            </div>');
            }
	   }
     if($security->idRequestVerify('subToPublish')){
      if($this->publishSub($submissions)){
          echo('<div class="bs-component">
        <div class="alert alert-dismissible alert-success">
          <button type="button" class="close" data-dismiss="alert">×</button>Soumission publiée avec succès</div>
      </div>');
      } else {
          echo('<div class="bs-component">
        <div class="alert alert-dismissible alert-danger">
          <button type="button" class="close" data-dismiss="alert">×</button>Impossible de publier la soumission: '.$this->errorMessage.'</div>
      </div>');
      }

    }
    if($security->idRequestVerify('subToUnpublish')){
     if($this->unpublishSub()){
         echo('<div class="bs-component">
       <div class="alert alert-dismissible alert-success">
         <button type="button" class="close" data-dismiss="alert">×</button>Soumission retirée avec succès</div>
     </div>');
     } else {
         echo('<div class="bs-component">
       <div class="alert alert-dismissible alert-danger">
         <button type="button" class="close" data-dismiss="alert">×</button>Impossible de retirer la soumission</div>
  </div>');
     }

   }
     $submissions = $_SESSION['user']->getSubmissions();
        require_once('views/anonymisation/submission.php');
    }

}
