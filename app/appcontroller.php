<?php
class appController{
    public $_controlador;
    public $_metodo;
    public $_argumentos;
    public function __construct() { 
        if(isset($_GET)){
            $this->_controlador = base64_decode(filter_input(INPUT_GET, 'app', FILTER_SANITIZE_URL));
            $this->_metodo      = base64_decode(filter_input(INPUT_GET, 'met', FILTER_SANITIZE_URL));
            $this->_argumentos  = explode(',',base64_decode(filter_input(INPUT_GET, 'arg', FILTER_SANITIZE_URL)));
        }

        if(!$this->_controlador)
            if(file_exists( MODULES_PATH . DS . 'web' . DS  . 'controller' . DS . 'webController.php'))
                $this->_controlador = WEB_CONTROLLER;
            else
                $this->_controlador = DEFAULT_CONTROLLER;

        if(!$this->_metodo)
            $this->_metodo = DEFAULT_METODO;

    }
    public function getControlador(){
        return $this->_controlador;
    }
    public function getMetodo(){
        return $this->_metodo;
    }
    public function getArgumentos(){
        return $this->_argumentos;
    }
    function __destruct() {
        unset($this);
    }
}