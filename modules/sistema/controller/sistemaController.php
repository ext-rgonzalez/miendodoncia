<?php

require_once ROOT . DEFAULT_CORE;
require_once ROOT . DEFAUL_FUNCTION;
require_once ROOT . DEFAUL_MODEL;
require_once ROOT . 'modules/sistema/model/sistemaModel.php';

class sistemaController{
    public $_Objvista       = null;
    public $_modelo         = null;
    public $_modeloGeneral  = null;
    public $_configForm     = null;
    public $_dataForm       = null;
    public $_dataPost       = null;
    public $_input          = null;
    public $_argumentos     = null;
    public $_formulario     = 'index';
    public $_formularioId   = 0;
    public $_fileversion    = '1.0.0';

//Autor:       David G -  Abr 8-2016
//descripcion: constructor de clase
    public function __construct( $_formulario, $_formularioId, $metodo ){
        $this->_formulario          = $_formulario;
        $this->_formularioId        = $_formularioId;
        $this->_metodo              = $metodo;
        $this->_modelo              = new ModeloSistema();
        $this->_modeloGeneral       = new appModel();
        $this->_Objvista            = new view();
        $this->_Objvista->_version  = $this->_fileversion;
        $this->_Objvista->_diretorio= DIR_SISTEMA;
        $this->_Objvista->_dirForm  = DIR_SISTEMA.DIR_FORM;
        $this->_Objvista->_dirModulo= DIR_SISTEMA;
        $this->_Objvista->_service  = SERVICE_SISTEMA;
        $this->_Objvista->_metodo   = $metodo;
        $this->_Objvista->_template = 'index.html';
        $this->_configForm          = array();
        $this->_dataForm            = fbRetornaConfigForm();

        formateaIndex($this->_modeloGeneral, $this->_Objvista, $this->_formularioId, $this->_dataForm, devuelveString($this->_formulario,'*',1));
    }

    public function index(){

        validaSession();

        $this->_Objvista->_titulo   = 'Dashboard';
        $this->_Objvista->_subtitulo= 'Panel de estadisticas e inicio';
        $this->_Objvista->_vista    = 'view_dashboard.html';
        $this->_Objvista->_renderVista();
    }

    public function login(){
        $this->_input = formateaMetodo($this->_metodo);

        if($this->_modelo->get_login($this->_input))
            redireccionar(array('modulo'=>'sistema','met'=>'index'));
        else{
            $data = array('ERR' => 3,
                'MSJ' => 'No ha iniciado Sesi&oacute;n');
            $this->_Objvista->_titulo    = 'inicio de sesion';
            $this->_Objvista->_template  = 'validate.html';
            $this->_Objvista->_asignacion->mensaje = $data;
            $this->_Objvista->_renderVista();
        }
    }

    public function cerrar(){

        foreach($_SESSION as $session=>$valor) 
            Session::destroy($session);

        if(!Session::get('usuario'))
            redireccionar(array('modulo'=>'sistema','met'=>'login'));
    }
}
?>