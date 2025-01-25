<?php

class Security
{

    public function __construct() {
    }

    public function requestExist($request)
    {
        if(isset($_REQUEST[$request]) && !empty($_REQUEST[$request])){
            return true;
        } else {
            return false;
        }
    }

    public function nameRequestVerify($request)
    {
        if($this->requestExist($request)){
            return preg_match('/^[a-zA-Z0-9]{1,}$/', $_REQUEST[$request]);
        } else {
            return false;
        }
    }

    public function idRequestVerify($request)
    {
        if($this->requestExist($request)){
            return preg_match('/^[0-9]{1,}$/', $_REQUEST[$request]);
        } else {
            return false;
        }
    }

    public function listOfidRequestVerify($request)
    {
        if($this->requestExist($request)){
            foreach($_REQUEST[$request] as $id){
                if(empty($id) || !preg_match('/^[0-9]{1,}$/', $id)){
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    public function passwordRequestVerify($request)
    {
        return $this->requestExist($request);
        /* //Déprécié
        if($this->requestExist($request)){
            return preg_match('/^[0-9a-zA-Z_!$@#^&]{1,}$/', $_REQUEST[$request]);
        } else {
            return "false";
        }*/
    }
    public function passwordIsStrong($request)
    {
      if($this->requestExist($request)){
          // Given password
          $password = $_REQUEST[$request];

          // Validate password strength
          $uppercase = preg_match('@[A-Z]@', $password);
          $lowercase = preg_match('@[a-z]@', $password);
          $number    = preg_match('@[0-9]@', $password);
          $specialChars = preg_match('@[^\w]@', $password);
          if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password)<8){
            return false;
          } else {
            return true;
          }
      } else{
          return false;
      }
    }

    public function booleanRequestVerify($request)
    {
        if($this->requestExist($request)){
            return $_REQUEST[$request]=="true"||$_REQUEST[$request]=="false";
        } else {
            return false;
        }
    }

    public function urlVerify($params)
    {
        if(isset($params[0]) && isset($params[1]) && !empty($params[0]) && !empty($params[1])){
            return preg_match('/^[a-zA-Z]{1,20}$/', $params[0]) && preg_match('/^[a-zA-Z]{1,20}$/', $params[1]);
        } else {
            return false;
        }
    }

    public function uploadVerify()
    {
        if(isset($_FILES) && !empty($_FILES) && isset($_FILES["file"]) && !$_FILES["file"]["error"]){
            if(isset($_REQUEST["name"])){
                return preg_match('/^[\/\w\-. ]+$/', $_REQUEST["name"]);
            } else {
                return preg_match('/^[\/\w\-. ]+$/', $_FILES["file"]["name"]);
            }
        } else {
            return false;
        }
    }

    public function idGenerator()
    {
        return hash('sha256', uniqid(rand(), TRUE));
    }

    public function idCheck($id)
    {
      return $id == $_SESSION['user']->id;
    }

}
