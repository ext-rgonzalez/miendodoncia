<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__)) . DS);
define('APP_PATH', ROOT . 'app' . DS);
define('APP_LIBS', ROOT . 'libs' . DS . 'smarty-master' . DS . 'libs' . DS);
define('APP_VIEW', ROOT . 'view' . DS);
ini_set('session.cookie_secure', 0);
require_once APP_PATH . 'constants.php';
require_once APP_LIBS . 'Smarty.class.php';
require_once APP_PATH . 'appcontroller.php';
require_once APP_PATH . 'session.php';
require_once APP_PATH . 'bootstrap.php';
//require_once APP_PATH . 'autoload.php';
require_once APP_VIEW . 'view.php';
#inicializamos la funcion principal de App
Session::init();
Bootstrap::run(new appController);
?>