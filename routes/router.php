<?php
function routing($controller, $action)
{
    require_once('controllers/controller_' . $controller . '.php');
    switch ($controller) {
        case 'page':
            require_once('models/anonym.php');
            require_once('models/attack.php');
            $myController = new PagesController();
            break;
		case 'attack':
			   require_once('models/uploader.php');
			   require_once('models/attack.php');
			   require_once('models/anonym.php');
            $myController = new AttackController();
            break;
        case 'anonymisation':
			require_once('models/uploader.php');
			require_once('models/anonym.php');
            $myController = new AnonymisationController();
            break;
        case 'user':
            require_once('models/invitekey.php');
            $myController = new userController();
            break;
        case 'admin':
			require_once('models/uploader.php');
            require_once('models/invitekey.php');
            require_once('models/page.php');
            require_once('models/anonym.php');
            $myController = new adminController();
            break;
    }
    $myController->{$action}();
}

// Controlleur pour les invités
$controllers = array(
    'page' => ['home', 'error', 'classement','about','example'],
    'user' => ['register', 'login', 'loggout']
);

// Controleur pour les utilisateurs
$controllers_connected = array(
    'page' => ['soumission','start'],
    'user' => ['profile']
);
// Add activated pages
$attack_activated = $page_check->isActivated('attack');
$submission_activated = $page_check->isActivated('anonymisation');
if ($attack_activated) $controllers_connected['attack'] = ['submission'];
if ($submission_activated) $controllers_connected['anonymisation'] = ['submission', 'upload'];

// Controleur pour les administrateurs
$controllers_administrator = array(
    'admin' => ['board', 'upload', 'uploadScript']
);

// URL Rewriting
$params = explode('/', $_GET['r']);

// Splite URL in a controller and a action
if($security->urlVerify($params)){
    $controller= $params[0];
    $action=$params[1];
} else {
   $controller='page';
   $action='home';
}

// Routing to page
// Administrator connected
if ($admin_connected && isset($controllers_administrator[$controller]) && in_array($action, $controllers_administrator[$controller])){
    require_once('views/layout-top.php');
    routing($controller, $action);
    require_once('views/layout-bottom.php');
// User connected
} else if ($user_connected && isset($controllers_connected[$controller]) && in_array($action, $controllers_connected[$controller])){
    require_once('views/layout-top.php');
    routing($controller, $action);
    require_once('views/layout-bottom.php');
// Database and Filemanager
} else if ($admin_connected && $controller == "admin"){
    if ($action=="database"){
        // Special controller for database access
        require_once('views/admin/phpliteadmin.php');
    } else if ($action=="filemanager"){
        // Special controller for utility file manager
        require_once('views/admin/filemanager.php');
    }
// Default
} else if (isset($controllers[$controller]) && in_array($action, $controllers[$controller])) {
    require_once('views/layout-top.php');
    routing($controller, $action);
    require_once('views/layout-bottom.php');
// Error page
} else {
    http_response_code(404);
    require_once('views/layout-top.php');
    routing('page', 'error');
    require_once('views/layout-bottom.php');
 }
