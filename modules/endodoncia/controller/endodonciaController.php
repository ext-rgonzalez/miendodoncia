<?php
require_once ROOT . DEFAULT_CORE;
require_once ROOT . DEFAUL_FUNCTION;
require_once ROOT . DEFAUL_MODEL;
require_once ROOT . 'modules/endodoncia/model/endodonciaModel.php';
require_once ROOT . EXTERNAL_LIBS . 'class.fpdf.historiaclinica.php';

class endodonciaController {
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
    public function __construct( $_formulario, $_formularioId,$metodo ){
        $this->_formulario          = $_formulario;
        $this->_formularioId        = $_formularioId;
        $this->_modelo              = new ModeloEndodoncia();
        $this->_modeloGeneral       = new appModel();
        $this->_Objvista            = new view();
        $this->_Objvista->_version  = $this->_fileversion;
        $this->_Objvista->_diretorio= DIR_SISTEMA;
        $this->_Objvista->_dirForm  = DIR_ENDODONCIA.DIR_FORM;
        $this->_Objvista->_dirModulo= DIR_ENDODONCIA;
        $this->_Objvista->_service  = SERVICE_ENDODONCIA;
        $this->_Objvista->_metodo   = $metodo;
        $this->_Objvista->_template = 'index.html';
        $this->_configForm          = array();
        $this->_dataForm            = fbRetornaConfigForm();


        formateaIndex($this->_modeloGeneral, $this->_Objvista, $this->_formularioId, $this->_dataForm, devuelveString($this->_formulario,'*',1),$this->_fileversion);
    }

    public function index(){
        print "Metodo no existe";
    }

    public function Odontologos() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion de Odontologos';
        $this->_Objvista->_subtitulo= 'Creacion, Edicion y Eliminacion de Odontologos';
        $this->_Objvista->_vista    = 'view_odontologos.html';

        $this->_modeloGeneral->get_datos('*','cod_perfil>0 order by cod_perfil_padre asc','sys_perfil');
        $this->_Objvista->_asignacion->perfilUsuarios = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_Objvista->_renderVista();
    }

    public function HistoriaClinica() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion de Historias Clinicas';
        $this->_Objvista->_subtitulo= 'Creacion, Edicion y Eliminacion de Historias Clinicas';
        $this->_Objvista->_vista    = 'view_historiaclinica.html';

        $this->_modeloGeneral->get_datos('*','ind_medico=1 and email_usuario is not null','sys_usuario');
        $this->_Objvista->_asignacion->odontologos = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_Objvista->_asignacion->search = isset($_POST['search']) && !empty($_POST['search'])?$_POST['search']:'';
        $this->_Objvista->_asignacion->accion = json_encode(array());

        if(isset($this->_argumentos[3])) {
            if($this->_argumentos[4]=='cerrarEnviar')
                $this->_modeloGeneral->set_simple_query("update endodoncia_historia_clinica set cod_estado='HCT' where cod_historia_clinica={$this->_argumentos[3]}");

            if($this->_argumentos[4]=='ingreso')
                $this->_Objvista->_asignacion->search = $this->_argumentos[5];

            $this->_Objvista->_asignacion->accion = json_encode(array('codHistoria'=>$this->_argumentos[3],'accion'=>$this->_argumentos[4]));
        }

        $this->_Objvista->_renderVista();
    }

    public function CrearHistoriaClinica() {

        $this->_Objvista->_titulo               = 'Nueva Historia Clinica';
        $this->_Objvista->_subtitulo            = 'Creacionde Historias Clinicas';
        $this->_Objvista->_vista                = 'view_nuevahistoriaclinica.html';
        $this->_Objvista->_asignacion->detalle  = 0;

        $this->_modeloGeneral->get_datos('*','','endodoncia_config_control');
        $this->_Objvista->_asignacion->config_control = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_antecedentes=1','endodoncia_config_antecedentes');
        $this->_Objvista->_asignacion->config_antecedentes = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_antecedentes=2','endodoncia_config_antecedentes');
        $this->_Objvista->_asignacion->config_antecedentes_odontologicos = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_antecedentes=3','endodoncia_config_antecedentes');
        $this->_Objvista->_asignacion->config_antecedentes_personales = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','','endodoncia_config_alergias');
        $this->_Objvista->_asignacion->config_alergias = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','tip_respuestas=1','sys_respuestas');
        $this->_Objvista->_asignacion->prueba_sensibilidad = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_tejidos=1','endodoncia_config_tejidos');
        $this->_Objvista->_asignacion->config_tejidos_blandos = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_tejidos=2','endodoncia_config_tejidos');
        $this->_Objvista->_asignacion->config_tejidos_dentales = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_tejidos=3','endodoncia_config_tejidos');
        $this->_Objvista->_asignacion->config_tejidos_periodontales = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_tejidos=4','endodoncia_config_tejidos');
        $this->_Objvista->_asignacion->config_tejidos_perirradiculares = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*','cod_config_tipo_tejidos=5','endodoncia_config_tejidos');
        $this->_Objvista->_asignacion->config_tejidos_pulpares = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('nom_config_diagnosticos',"","endodoncia_config_diagnosticos where cod_estado='AAA' group by nom_config_diagnosticos");
        $this->_Objvista->_asignacion->config_diagnosticos = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*',"cod_estado='AAA'","endodoncia_config_analisis_radiografico");
        $this->_Objvista->_asignacion->config_analisis = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*',"","sys_genero");
        $this->_Objvista->_asignacion->config_genero = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_Objvista->_asignacion->hora_entrada = date('H:i',time());

        $this->_Objvista->_asignacion->odontogramaAdultos   = DIR_ENDODONCIA.DIR_FORM.'view_odontogramaAdultos.html';
        $this->_Objvista->_asignacion->odontogramaNinos     = DIR_ENDODONCIA.DIR_FORM.'view_odontogramaNinos.html';

        if(isset($this->_argumentos[4])) {
            $cod_historia_clinica   = $this->_argumentos[3];
            $cod_paciente           = $this->_argumentos[4];

            $this->_modeloGeneral->get_datos('t1.*,t2.cod_imagen_dental,t2.nom_config_dental,t3.*',"t1.cod_historia_clinica={$cod_historia_clinica} and t1.cod_config_dental=t2.cod_config_dental and t1.cod_paciente=t3.cod_paciente",'endodoncia_historia_clinica as t1, endodoncia_config_dental as t2, endodoncia_view_paciente as t3');
            $this->_Objvista->_asignacion->historia_clinica_edit = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data[0]:array();

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica}",'endodoncia_historia_clinica_alergias');
            $alergias  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $alergias  = array_map(function($aAlergias){ return $aAlergias['cod_config_alergias'];}, $alergias);
            $this->_Objvista->_asignacion->historia_alergias_edit  = $alergias;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica}",'endodoncia_historia_clinica_analisis_radiografico');
            $radiografico  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $radiografico  = array_map(function($aRadiografio){ return $aRadiografio['cod_config_analisis_radiografico'];}, $radiografico);
            $this->_Objvista->_asignacion->historia_analisis_radiografico_edit = $radiografico;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_antecedentes_familiares>0",'endodoncia_historia_clinica_antecedentes');
            $antenedentesfamiliares  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $antenedentesfamiliares  = array_map(function($aAntecedentes){ return $aAntecedentes['cod_config_antecedentes_familiares'];}, $antenedentesfamiliares);
            $this->_Objvista->_asignacion->historia_antecedentes_familiares_edit = $antenedentesfamiliares;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_antecedentes_odontologicos>0",'endodoncia_historia_clinica_antecedentes');
            $antenedentesodontologicos  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $antenedentesodontologicos  = array_map(function($aAntecedentes){ return $aAntecedentes['cod_config_antecedentes_odontologicos'];}, $antenedentesodontologicos);
            $this->_Objvista->_asignacion->historia_antecedentes_odontologicos_edit = $antenedentesodontologicos;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_antecedentes_personales>0",'endodoncia_historia_clinica_antecedentes');
            $antenedentespersonales  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $antenedentespersonales  = array_map(function($aAntecedentes){ return $aAntecedentes['cod_config_antecedentes_personales'];}, $antenedentespersonales);
            $this->_Objvista->_asignacion->historia_antecedentes_personales_edit = $antenedentespersonales;

            $this->_modeloGeneral->get_datos('t1.*,t2.nom_config_diagnosticos, t2.des_config_diagnosticos',"t1.cod_historia_clinica={$cod_historia_clinica} and t1.cod_config_diagnosticos=t2.cod_config_diagnosticos",'endodoncia_historia_clinica_diagnostico as t1, endodoncia_config_diagnosticos as t2');
            $this->_Objvista->_asignacion->historia_diagnotico_edit = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data[0]:array();

            $this->_modeloGeneral->get_datos('t1.*,t2.nom_config_medicamentos',"t1.cod_historia_clinica={$cod_historia_clinica} and t1.cod_config_medicamentos=t2.cod_config_medicamentos",'endodoncia_historia_clinica_medicamentos as t1, endodoncia_config_medicamentos as t2');
            $this->_Objvista->_asignacion->historia_medicamentos_edit = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_tejidos_blandos>0",'endodoncia_historia_clinica_tejidos');
            $tejidosblandos = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $tejidosblandos = array_map(function($aTejidos){ return $aTejidos['cod_config_tejidos_blandos'];}, $tejidosblandos);
            $this->_Objvista->_asignacion->historia_tejidos_blando_edit = $tejidosblandos;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_tejidos_dental>0",'endodoncia_historia_clinica_tejidos');
            $tejidosdental  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $tejidosdental  = array_map(function($aTejidos){ return $aTejidos['cod_config_tejidos_dental'];}, $tejidosdental);
            $this->_Objvista->_asignacion->historia_tejidos_dental_edit = $tejidosdental;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_tejidos_periodontal>0",'endodoncia_historia_clinica_tejidos');
            $tejidosperiodontal = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $tejidosperiodontal = array_map(function($aTejidos){ return $aTejidos['cod_config_tejidos_periodontal'];}, $tejidosperiodontal);
            $this->_Objvista->_asignacion->historia_tejidos_pariodontal_edit = $tejidosperiodontal;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_tejidos_perirradicular>0",'endodoncia_historia_clinica_tejidos');
            $tejidosperirradicular  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $tejidosperirradicular  = array_map(function($aTejidos){ return $aTejidos['cod_config_tejidos_perirradicular'];}, $tejidosperirradicular);
            $this->_Objvista->_asignacion->historia_tejidos_perirradicular_edit = $tejidosperirradicular;

            $this->_modeloGeneral->get_datos('*',"cod_historia_clinica={$cod_historia_clinica} and cod_config_tejidos_pulpar>0",'endodoncia_historia_clinica_tejidos');
            $tejidospulpar  = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();
            $tejidospulpar  = array_map(function($aTejidos){ return $aTejidos['cod_config_tejidos_pulpar'];}, $tejidospulpar);
            $this->_Objvista->_asignacion->historia_tejidos_pulpar_edit = $tejidospulpar;

            $data = array(
                'cod_historia_clinica'  => $cod_historia_clinica,
                'cod_paciente'          => $cod_paciente,
                'cod_config_dental'     => $this->_Objvista->_asignacion->historia_clinica_edit['cod_imagen_dental'],
                'case'                  => 1
            );

            $this->_Objvista->_asignacion->evoluciones  = $this->_modelo->traerEvoluciones($data);
            $this->_Objvista->_asignacion->imagenes     = $this->_modelo->traerImagenesHistoriaClinica($data);
            $this->_Objvista->_asignacion->diagnostico  = $this->_modelo->traerPanelDiagnostico($data);
            $this->_Objvista->_asignacion->desobturacion= $this->_modelo->traerDesobturacion($data);

            $this->_Objvista->_asignacion->edit = 1;

            if(isset($this->_argumentos['5']))
                $this->_Objvista->_asignacion->detalle = 1;
        }

        $this->_Objvista->_renderVista();
    }

    public function GenerarHistoriaClinicaPDF() {

        $data       = array();
        $argumentos = array();

        $argumentos['cod_historiaclinica']  = $this->_argumentos[0];
        $argumentos['type']                 = isset($this->_argumentos[1])?$this->_argumentos[1]:0;
        $data                               = $this->_modelo->HistoriClinicaPDFData( $argumentos );

        generaHistoriaClinicaPDF( $data, $argumentos['type'] );
        die();
    }

    public function GenerarRemisionPDF() {
        $data       = array();
        $argumentos = array();

        $argumentos['cod_historiaclinica']  = $this->_argumentos[0];
        $argumentos['type']                 = isset($this->_argumentos[1])?$this->_argumentos[1]:0;
        $imagenesSeleccionadas              = explode('|', $this->_argumentos[2]);
        $data                               = $this->_modelo->HistoriClinicaPDFData( $argumentos, $imagenesSeleccionadas );

        generaRemisionPDF( $data, $argumentos['type'] );
        die();
    }

    public function ConsentimientoInfo() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion de Consentimientos informados';
        $this->_Objvista->_subtitulo= 'Creacion, Edicion y Eliminacion de Consentimientos Informados';
        $this->_Objvista->_vista    = 'view_consentimientoinfo.html';

        $this->_modeloGeneral->get_datos('*',"cod_estado='AAA'",'endodoncia_config_consentimiento');
        $this->_Objvista->_asignacion->tipoconsentimiento = isset($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        $this->_modeloGeneral->get_datos('*',"cod_estado='AAA'", "endodoncia_config");
        $this->_Objvista->_asignacion->configEndodoncia = $this->_modeloGeneral->_data[0];

        $this->_Objvista->_renderVista();
    }

    public function GenerarConsentimientoPDF() {
        $argumentos = array();

        $argumentos['cod_consentimiento']   = $this->_argumentos[0];
        $data                               = $this->_modelo->ConsentimientoPDFData( $argumentos );

        $ext    = explode('.', $data["consentimiento"]["img_paciente_consentimiento"]);
        $ext    = $ext[1];

        if($ext=='bmp' Or $ext =='BMP'){
            $text_img   = explode('.',$data["consentimiento"]["img_paciente_consentimiento"]);
            $text_img   = $text_img[0].'.jpg';
            $img        = ImageCreateFromBmp("modules/endodoncia/adjuntos/{$data["consentimiento"]["img_paciente_consentimiento"]}");
            imagejpeg($img, "modules/endodoncia/adjuntos/{$text_img}");
            $this->_modeloGeneral->set_simple_query("update endodoncia_paciente_consentimiento set img_paciente_consentimiento='{$text_img}' where cod_paciente_consentimiento={$this->_argumentos[0]}");
            unlink('modules/endodoncia/adjuntos/'.$data["consentimiento"]["img_paciente_consentimiento"]);
        }else
            $text_img = $data["consentimiento"]["img_paciente_consentimiento"];

        $data['text_img']   = $text_img;
        $data['texto']      = utf8_decode(  str_replace('{nom_paciente}', $data["consentimiento"]["nom_paciente"],
                                            str_replace('{ced_paciente}', $data["consentimiento"]["ced_paciente"],
                                            str_replace('{nom_config_diagnostico}', $data["diagnostico"] ,
                                            str_replace('{nom_ciudad}',$data["consentimiento"]["nom_ciudad"],$data["consentimiento"]["des_consentimiento"])))));

        generaConsentimientoPDF( $data );
        die();
    }

    public function Ingresos() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion de Ingresos';
        $this->_Objvista->_subtitulo= 'Creacion de Ingresos';
        $this->_Objvista->_vista    = 'view_ingreso.html';

        $this->_Objvista->_renderVista();
    }

    public function GenerarComprobanteIngresoPDF() {
        $data       = array();
        $argumentos = array();

        $argumentos['cod_pago'] = $this->_argumentos[0];
        $argumentos['type']     = isset($this->_argumentos[1])?$this->_argumentos[1]:0;
        $data                   = $this->_modelo->ComprobanteIngresoPDFData( $argumentos );

        generaComprobanteIngresoPDF( $data, $argumentos['type'] );
        die();
    }

    public function Gastos() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion de Gastos';
        $this->_Objvista->_subtitulo= 'Creacion de Gastos';
        $this->_Objvista->_vista    = 'view_gasto.html';

        $this->_Objvista->_renderVista();
    }

    public function AgendaMedica() {

        validaSession();

        $this->_Objvista->_titulo   = 'Administracion Agenda Medica';
        $this->_Objvista->_subtitulo= 'Creacion, edicion y eliminacino de agendas medicas';
        $this->_Objvista->_vista    = 'view_agendamedica.html';

        $this->_Objvista->_renderVista();
    }

    public function ExportArchivo() {

        $tipoArchivo = $this->_argumentos[0];

        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"{$tipoArchivo}.csv\"");

        switch( $tipoArchivo ) {
            case('formatoMedicamentos'):

                $column_array= array(
                    array('Codigo Unico','Nombre Medicamento', 'Description(opcional)', 'Forma(optional)'),
                    array('Ejemplo0001223','EjemploAcetaminofen', 'EjemploDescripcion', 'EjemploForma')
                );
                $outputBuffer = fopen("php://output", 'w');

                foreach($column_array as $val)
                    fputcsv($outputBuffer, $val);

                fclose($outputBuffer);
            break;
        }

        die();
    }
}

?>