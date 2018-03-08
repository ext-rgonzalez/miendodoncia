<?php
#rutas librerias
const DIR_SISTEMA           = 'modules/sistema/html/';
const DIR_NUCLEO            = 'modules/nucleo/html/';
const DIR_GANAFACIL         = 'modules/ganafacil/html/';
const DIR_ENDODONCIA        = 'modules/endodoncia/html/';
const DIR_WEB               = 'modules/web/html/';
const DIR_CMS               = 'modules/cms/html/';
const DIR_FORM              = 'forms/';
const DIR_JS_TEMPLATE       = 'js_template/';
const DIR_CSS_TEMPLATE      = 'css_template/';
#rutas services
const SERVICE_SISTEMA       = 'modules/sistema/controller/sistemaJsonController.php';
const SERVICE_NUCLEO        = 'modules/nucleo/controller/nucleoJsonController.php';
const SERVICE_GANAFACIL     = 'modules/ganafacil/controller/ganafacilJsonController.php';
const SERVICE_ENDODONCIA    = 'modules/endodoncia/controller/endodonciaJsonController.php';
const SERVICE_cms           = 'modules/cms/controller/cmsJsonController.php';
const SERVICE_WEB           = 'modules/web/controller/webJsonController.php';
#rutas constantes
const DEFAULT_CONTROLLER    = 'sistema';
const WEB_CONTROLLER        = 'web';
const DEFAULT_METODO        = 'index';
const FA_CONTROLLER         = 'facturacion';
const CON_CONTROLLER        = 'contabilidad';
const VAR_APP               = '?app=';
const VAR_MET               = '&met=';
const VAR_ARG               = '&arg=';
#core mysql
const DEFAULT_CORE          = 'core/db_abstract_model.php';
#funciones generales
const DEFAUL_FUNCTION       = 'app/principalFunction.php';
#modelo general
const DEFAUL_MODEL          = 'model/appmodel.php';
#rutas de directorios raiz
const MODULES_PATH          = 'modules';
const VIEW_PACH             = 'view';
#constante de carpeta adjuntos de sistema
const SYS_DIR_ADJ           = '../adjuntos/';
const GF_DIR_ADJ            = SYS_DIR_ADJ;
const ENDO_DIR_ADJ          = SYS_DIR_ADJ;
const WEB_DIR_ADJ           = SYS_DIR_ADJ;
const CMS_DIR_ADJ           = SYS_DIR_ADJ;

const LIBS                  = '../../../';
const LIBS_MODEL            = '../';
const EXTERNAL_LIBS         = 'libs/';
