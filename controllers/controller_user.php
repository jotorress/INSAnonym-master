<?php
class UserController
{
    public function __construct()
    {
    }

    public function register()
    {
        global $security;
		if ($security->nameRequestVerify('username') && $security->passwordRequestVerify('password') && $security->nameRequestVerify('team') && $security->nameRequestVerify('key')){
            if (!$security->passwordIsStrong('password')){
              echo('<div class="bs-component">
                <div class="alert alert-dismissible alert-danger">
                  <button type="button" class="close" data-dismiss="alert">×</button>Mot de passe faible: minimum un chiffre, un caractère spécial, une miniscule et une majuscule requis, longueur de 8 caractères minimum</div>
              </div>');
            } else{
              //Vérification de la validité de la clé d'invitation
              $key  = new InviteKey($_REQUEST['key']);
              if($key->isValid()){
                  $key->delete();

                  $user = new User();
                  $result = $user->register($_REQUEST['username'], $_REQUEST['password'], $_REQUEST['team']);

                  if($result){
                      header('location: /user/login?registered');
                      exit();
                  } else {
                      echo('<div class="bs-component">
                    <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la requête SQL</div>
                  </div>');
                  }
              } else {
                  echo('<div class="bs-component">
                    <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">×</button>Clé d\'invitation non valide</div>
                  </div>');
              }
            }
          }
        require_once('views/user/register.php');
    }

    public function login()
    {
        global $security;
        if ($security->nameRequestVerify('username') && $security->passwordRequestVerify('password')){
            $user = new User();
		    $user = $user->login($_REQUEST['username'], $_REQUEST['password']);

            if($user){ // Checks user is valid
                session_unset();
                $_SESSION['user']=$user;
                if ($user->isAdmin()) $_SESSION['admin']=new Admin($user);
                header("Location: /user/profile");
                exit();
            } else {
                echo('<div class="bs-component">
              <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>Erreur lors de la connexion</div>
            </div>');
            }
        }
        if (isset($_REQUEST['registered'])){
            echo('<div class="bs-component">
                  <div class="alert alert-dismissible alert-success">
                    <button type="button" class="close" data-dismiss="alert">×</button>Inscription réalisée avec succès</div>
                </div>');
        }
        require_once('views/user/login.php');
    }

    public function profile()
    {
        global $security;
        global $admin_connected;
        if(isset($_POST['newPasswd']) && isset($_POST['newPasswdConfirm'])){
          if($security->passwordRequestVerify('newPasswd') && $security->passwordRequestVerify('newPasswdConfirm')){
            if(strcmp($_POST['newPasswd'],$_POST['newPasswdConfirm'])==0){
                  if (!$security->passwordIsStrong('newPasswd')){
                    echo('<div class="bs-component">
                      <div class="alert alert-dismissible alert-danger">
                        <button type="button" class="close" data-dismiss="alert">×</button>Mot de passe faible: minimum un chiffre, un caractère spécial, une miniscule et une majuscule requis, longueur de 8 caractères minimum</div>
                    </div>');
                  } else{
                    if($_SESSION['user']->changeUserPasswd($_POST['newPasswd'])){
                      echo('<div class="bs-component">
                      <div class="alert alert-dismissible alert-success">
                      <button type="button" class="close" data-dismiss="alert">×</button>Mot de passe modfié!</div>
                      </div>');
                    }else{
                      echo('<div class="bs-component">
                      <div class="alert alert-dismissible alert-danger">
                      <button type="button" class="close" data-dismiss="alert">×</button>Impossible de changer le mot de passe</div>
                      </div>');
                    }
                  }
                }else{
                  echo('<div class="bs-component">
                  <div class="alert alert-dismissible alert-danger">
                  <button type="button" class="close" data-dismiss="alert">×</button>Impossible de changer le mot de passe</div>
                  </div>');
                }


          }else{
            echo('<div class="bs-component">
            <div class="alert alert-dismissible alert-danger">
            <button type="button" class="close" data-dismiss="alert">×</button>Impossible de changer le mot de passe</div>
            </div>');
          }

        }
        require_once('views/user/profile.php');
    }

    public function loggout()
    {
        session_unset();
        header("Location: /");
        exit();
    }


}
