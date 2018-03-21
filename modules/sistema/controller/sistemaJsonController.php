<?php
require_once "../../../core/db_abstract_model.php";
require_once '../../../model/appmodel.php';
require_once '../model/sistemaJsonModel.php';
require_once '../model/sistemaModel.php';
require_once '../../../app/principalFunction.php';
require_once '../../../app/session.php';
require_once '../../../app/constants.php';
class sistemaJsonController{
    public $_sistemaJsonModel   = null;
    public $_dataMetodo         = array();
    public $_jsonReturn         = array();

    public function __construct( $_data = array() ){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Access-Control-Allow-Origin: *');
        $this->_sistemaJsonModel    = new ModeloJsonSistema();
        $this->_dataMetodo          = $_data;
        Session::init();
    }

    public function infoGraficosDashboard(){
        return $this->_sistemaJsonModel->infoGraficosDashboard($this->_dataMetodo);
    }

    public function traerNotificacionesSistema(){
        return $this->_sistemaJsonModel->traerNotificacionesSistema();
    }

}

$_aData     = formateaMetodo();
$_aMetodo   = $_aData['funcion'];
$_aClass    = new sistemaJsonController( $_aData );

print $_aClass->$_aMetodo();