<?php
// Require User class for session
require_once('models/user.php');
require_once('models/admin.php');
require_once('models/page.php');

//Start SESSION and check SESSION variables
session_start();
$user_connected = isset($_SESSION['user']);
$admin_connected = isset($_SESSION['admin']);

// Load the security class
require_once('models/security.php');
$security = new Security();

// Database connection
require_once('database/db.php');
$con = DBConnect::getInstance();

//Page ckecker
$page_check = new Page();
