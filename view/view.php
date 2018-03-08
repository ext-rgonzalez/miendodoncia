<?php
class view {
    public $_smarty         = null;
    public $_dirForm        = null;
    public $_dirModulo      = null;
    public $_asignacion     = null;
    public $_diretorio      = '';
    public $_titulo         = '';
    public $_subtitulo      = '';
    public $_template       = '';
    public $_vista          = '';
    public $_vistaDatos     = '';
    public $_service        = '';
    public $_metodo         = '';
    public $_js             = null;
    public $_css            = null;
    public $_includeJs      = array();
    public $_version        = '';
//Autor:       Ricardo Gonzalez Ablir 8-14
//descripcion: constructor de clase
    public function __construct(){
        $this->_smarty     = new Smarty();
        $this->_asignacion = new stdClass();
        $this->_js         = new stdClass();
        $this->_css        = new stdClass();
        $this->_vista      = '';
        $this->_vistaDatos = array();
        $this->_cssDe      = '';
        $this->_jsDe       = '';
        $this->_service    = '';
    }
//Autor:       Ricardo Gonzalez Abril 8-14
//descripcion: Metodo publico para asinar las variables a smarty
    private function _asignarVariables(){
        foreach($this->_asignacion as $asignacion=>$valor){
            $this->_smarty->assign($asignacion, $valor);
        }
    }
//Autor:       Ricardo Gonzalez Abril 8-14
//descripcion: Metodo private para imprimir la vista
    public function _renderVista(){
        $this->_smarty->clearAllCache(1);
        $this->_smarty->debugging   = false;
        $this->_smarty->caching     = false;
        $this->_smarty->assign('_titulo'        ,$this->_titulo);
        $this->_smarty->assign('_subtitulo'     ,$this->_subtitulo);
        $this->_smarty->assign('_jsDe'          ,$this->_jsDe);
        $this->_smarty->assign('_cssDe'         ,$this->_cssDe);
        $this->_smarty->assign('_vista'         ,empty($this->_vista)?'':$this->_dirForm.$this->_vista);
        $this->_smarty->assign('_vistaDatos'    ,$this->_vistaDatos);
        $this->_smarty->assign('_service'       ,$this->_service);
        $this->_smarty->assign('_metodo'        ,$this->_metodo);
        $this->_smarty->assign('_jsTemplate'    ,$this->_devuelveFileIncludeValido($this->_dirModulo.DIR_JS_TEMPLATE,$this->_vista));
        $this->_smarty->assign('_cssTemplate'    ,$this->_devuelveFileIncludeValido($this->_dirModulo.DIR_CSS_TEMPLATE,$this->_vista, 'css'));
        $this->_smarty->assign('_includeJs'     ,$this->_devuelveJsIncluidos($this->_includeJs));
        $this->_asignarVariables();
        $this->_smarty->display($this->_diretorio . $this->_template);
    }
//Autor:       Ricardo Gonzalez Abril 8-14
//descripcion: Metodo publico para devolver el js por template
    private function _devuelveFileIncludeValido($dirJs=null, $vista=null, $fileType="js"){
        $jsPorDefecto = $dirJs.str_replace('view',$fileType,str_replace('html',$fileType,$vista));
        if(is_readable($jsPorDefecto))
            return $jsPorDefecto . '?' . $this->_version;

        return null;
    }
//Autor:       Ricardo Gonzalez Abril 8-14
//descripcion: Metodo publico para devolver los js incluidos por template
    private function _devuelveJsIncluidos($js=array()){
        foreach($js as $idJs => $jsFile){
            if(!$this->_devuelveFileIncludeValido(DIR_SISTEMA,$jsFile)==null)
                $js[$idJs] = DIR_SISTEMA.$jsFile;

                unset($js[$idJs]);
        }

        return $js;
    }

    public function renderTemplate(){
        $this->_asignarVariables();
        return $this->_smarty->fetch( $this->_template );
    }
//Autor:       Ricardo Gonzalez Ablir 8-14
//descripcion: destructor de clase
    public function __destruct(){
        unset($this);
    }
}
?>
