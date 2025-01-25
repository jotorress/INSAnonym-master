<?php
class AttackController
{
    public function __construct()
    {
    }

    public function submission()
    {
        global $security;
        if($security->uploadVerify() && $security->idRequestVerify('anonymisationID')){
        if($_SESSION['user']->getNumberOfAttackSubmission($_POST['anonymisationID'])[0] < 10){
          if(Uploader::attack()){
              echo('<div class="bs-component">
            <div class="alert alert-dismissible alert-success">
              <button type="button" class="close" data-dismiss="alert">×</button>L\'attaque a bien été enregistré</div>
          </div>');
          } else {
            echo('<div class="bs-component">
          <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>Impossible de traiter le fichier d\'attaque</div>
        </div>');
          }
        }else{
          echo('<div class="bs-component">
        <div class="alert alert-dismissible alert-danger">
          <button type="button" class="close" data-dismiss="alert">×</button>Nombre d\'attaques maximales atteintes!</div>
      </div>');
        }
        }

        // Submission start number
        $users = new User();
        $users=$users->getAllUsers();
        require_once('views/attack/submission.php');
    }
}
