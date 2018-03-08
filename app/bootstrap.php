<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
class Bootstrap{
    public static function run(appController $peticion){

        date_default_timezone_set('GMT');

        $controller           = $peticion->getControlador() . 'Controller';
        $modulo               = str_replace('Controller','',$controller);
        $rutaModulo           = MODULES_PATH . DS . $modulo . DS ;
        $rutaControlador      = $rutaModulo . 'controller' . DS . $controller . '.php';
        $Objvista             = new view;
        $Objvista->_diretorio = $peticion->getControlador()==DEFAULT_CONTROLLER?DIR_SISTEMA:DIR_WEB;
        $argumentos           = $peticion->getArgumentos();
        date_default_timezone_set("America/Bogota");
        if(is_readable($rutaControlador)):
            require_once $rutaControlador;
            $metodo = $peticion->getMetodo();
            $appController = new $controller(isset($argumentos[2])?$argumentos[2]:'index',isset($argumentos[1])&&$argumentos[1]!=''?$argumentos[1]:'186',$metodo);
            is_callable(array($appController, $metodo)) ? $metodo = $peticion->getMetodo() : $metodo= 'index';
            $appController->_metodo         = $metodo;
            $appController->_argumentos     = $argumentos;
            $appController->$metodo();
        else:
            $Objvista->_titulo    = 'error::no se han encontrado resultados';
            $Objvista->_template  = 'error.html';
            $Objvista->_renderVista();
        endif;
    }
}
?>