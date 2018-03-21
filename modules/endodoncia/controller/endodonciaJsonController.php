<?php
require_once '../../../app/constants.php';
require_once "../../../core/db_abstract_model.php";
require_once '../../../model/appmodel.php';
require_once '../model/endodonciaJsonModel.php';
require_once '../model/endodonciaModel.php';
require_once '../../../app/principalFunction.php';
require_once '../../../app/session.php';
require_once LIBS . EXTERNAL_LIBS . 'class.fpdf.historiaclinica.php';


class endodonciaJsonController{
    public $_endodonciaJsonModel = null;
    public $_dataMetodo         = array();
    public $_jsonReturn         = array();

    public function __construct( $_data = array() ){
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        header('Access-Control-Allow-Origin: *');
        date_default_timezone_set('GMT');
        $this->_endodonciaJsonModel = new ModeloJsonEndodoncia();
        $this->_dataMetodo          = $_data;
        Session::init();
    }

    public function Odontologos(){
        $result             = array('data'=>'');
        $insertOdontologo   = $this->_endodonciaJsonModel->Odontologos( $this->_dataMetodo );

        if($insertOdontologo)
            $result = $this->traerOdontologos( $insertOdontologo );
        else
            $result = json_encode($result);

        return $result;

    }

    public function Pacientes() {
        $result             = array('data'=>'');
        $insertPaciente     = $this->_endodonciaJsonModel->Pacientes( $this->_dataMetodo['paciente'] );

        if($insertPaciente>0)
            $result = $this->traerPacientes( $insertPaciente );
        else
            $result = json_encode($result);

        return $result;
    }

    public function traerOdontologos( $codUsuario=null ){
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if($codUsuario)
            $data['cod_usuario'] = $codUsuario;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerOdontologos( $data );
    }

    public function eliminaOdontologo() {
        return $this->_endodonciaJsonModel->eliminaOdontologo($this->_dataMetodo);
    }

    public function cambiarEstadoOdontologo() {
        $this->_endodonciaJsonModel->cambiarEstadoOdontologo($this->_dataMetodo);
        return $this->traerOdontologos( $this->_dataMetodo['cod_usuario'] );
    }

    public function eliminaPaciente() {
        return $this->_endodonciaJsonModel->eliminaPaciente($this->_dataMetodo);
    }

    public function cambiarEstadoPaciente() {
        $this->_endodonciaJsonModel->cambiarEstadoPaciente($this->_dataMetodo);
        return $this->traerPacientes( $this->_dataMetodo['cod_paciente'] );
    }

    public function traerHistoriaClinica( $codHistoriaClinica=null ) {
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if( isset($this->_dataMetodo['search']) && !empty($this->_dataMetodo['search']) && !is_array($this->_dataMetodo['search']))
            $data['search'] = $this->_dataMetodo['search'];

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        if($codHistoriaClinica)
            $data['cod_historia_clinica'] = $codHistoriaClinica;

        return $this->_endodonciaJsonModel->traerHistoriaClinica( $data );
    }

    public function traerPacientes( $codPaciente=null ) {
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if($codPaciente)
            $data['cod_paciente'] = $codPaciente;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerPacientes( $data );
    }

    public function consultaDienteHistoriaDatos() {
        return $this->_endodonciaJsonModel->consultaDienteHistoriaDatos( $this->_dataMetodo );
    }

    public function consultaMedicamentos() {
        return $this->_endodonciaJsonModel->consultaMedicamentos($this->_dataMetodo);
    }

    public function traerSubDiagnosticos() {
        return $this->_endodonciaJsonModel->traerSubDiagnosticos($this->_dataMetodo['coddiagnosticos']);
    }

    public function consultaCiudad() {
        return $this->_endodonciaJsonModel->consultaCiudad($this->_dataMetodo);
    }

    public function PrecacargarCsvMedicamentos() {
        require_once '../../../libs/CsvIterator/CsvIterator.class.php';

        $lista          = getImagesByTemplate('cargarcsvmedicamentos');
        $archivo        = uploadImges($lista, ENDO_DIR_ADJ, Session::get('nom').'/medicamentos/');
        $path           = ENDO_DIR_ADJ.Session::get('nom').'/medicamentos/';
        $csvIterator    = new CsvIterator($path.$archivo[0], false, ',');
        $data           = array();

        while ($csvIterator->next()) {

            $aLine = $csvIterator->current();

            $dataResult['cod_unico_config_medicamentos']    = $aLine[0];
            $dataResult['nom_config_medicamentos']          = $aLine[1];
            $dataResult['des_config_medicamentos']          = $aLine[2];
            $dataResult['forma_farma_config_medicamentos']  = $aLine[3];

            $data[] = $dataResult;
        }

        if(count($data)>0)
            $data = array_slice($data, 1);

        return json_encode($data);
    }

    public function Medicamentos() {
        $result             = array();
        $insertMedicamento  = $this->_endodonciaJsonModel->Medicamentos( $this->_dataMetodo['medicamento'] );

        if( $insertMedicamento )
            $result = $this->traerMedicamentos( $insertMedicamento );

        return $result;
    }

    public function traerMedicamentos( $codMedicamento=null ){
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if($codMedicamento)
            $data['cod_config_medicamentos'] = $codMedicamento;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerMedicamentos( $data );
    }

    public function MedicamentosCSv() {

        $result = false;

        $dataMedicamentos = json_decode($this->_dataMetodo['data'], true);
        $dataMedicamentos = array_filter($dataMedicamentos, function($item){ return is_array($item) && array_key_exists('cod_unico_config_medicamentos', $item); });

        foreach($dataMedicamentos as $key=>$aDataMedicamentos)
            $result = $this->_endodonciaJsonModel->Medicamentos($aDataMedicamentos);

        return json_encode(array('status',$result));
    }

    public function HistoriaClinica() {
        $result                 = array();
        $dataHistoriaClinica    = $this->_dataMetodo['historiaclinica'];
        $registrofotografico    = getImagesByTemplate('historiaclinica');

        if(!empty($registrofotografico))
            $imagenesHistoria = uploadImges($registrofotografico, ENDO_DIR_ADJ);

        if(!empty($imagenesHistoria))
            $dataHistoriaClinica['imagenes'] = $imagenesHistoria;

        $insertHistoriaClinica = $this->_endodonciaJsonModel->HistoriaClinica( $dataHistoriaClinica );

        return json_encode($insertHistoriaClinica);
    }

    public function Evoluciones() {
        $result                 = array();
        $dataHistoriaClinica    = $this->_dataMetodo['historiaclinica'];
        $registrofotografico    = getImagesByTemplate('historiaclinica');

        if(!empty($registrofotografico))
            $imagenesHistoria = uploadImges($registrofotografico, ENDO_DIR_ADJ);

        if(!empty($imagenesHistoria))
            $dataHistoriaClinica['imagenes'] = $imagenesHistoria;

        $insertHistoriaClinica = $this->_endodonciaJsonModel->Evoluciones( $dataHistoriaClinica );

        return json_encode(array('historiaclinica'=>$insertHistoriaClinica));
    }

    public function GenerarHistoriaClinicaPDF() {
        $result = $this->_endodonciaJsonModel->GenerarHistoriaClinicaPDF($this->_dataMetodo);
        return json_encode(array('status'=>$result));
    }

    public function cambiarEstadoHistoriaClinica() {
        $this->_endodonciaJsonModel->cambiarEstadoHistoriaClinica($this->_dataMetodo);
        return $this->traerHistoriaClinica( $this->_dataMetodo['cod_historia_clinica'] );
    }

    public function infoRemisiones() {
        return $this->_endodonciaJsonModel->infoRemisiones($this->_dataMetodo);
    }

    public function GenerarRemisionPDF() {
        $result = $this->_endodonciaJsonModel->GenerarRemisionPDF($this->_dataMetodo);
        return json_encode(array('status'=>$result));
    }

    public function GenerarIngresoPDF() {
        $result = $this->_endodonciaJsonModel->GenerarIngresoPDF($this->_dataMetodo);
        return json_encode(array('status'=>$result));
    }

    public function infoEvoluciones() {
        return $this->_endodonciaJsonModel->infoEvoluciones($this->_dataMetodo);
    }

    public function getNumComprobantes() {
        $numeracion = $this->_endodonciaJsonModel->getNumComprobantes();
        return json_encode(array('numeracion'=>$numeracion));
    }

    public function getInfoIngresoByCod() {
        $infoTratamiento = $this->_endodonciaJsonModel->getInfoIngreso($this->_dataMetodo);
        return json_encode(array('infoTratamiento'=>$infoTratamiento));
    }

    public function getinfoIngreso() {
        $numeracion     = $this->_endodonciaJsonModel->getNumComprobantes();
        $infoTratamiento= $this->_endodonciaJsonModel->getInfoIngreso($this->_dataMetodo);

        return json_encode(array('numeracion'=>$numeracion,'infoTratamiento'=>$infoTratamiento));
    }

    public function Ingresos() {
        $nuevoIngreso = $this->_endodonciaJsonModel->Ingresos($this->_dataMetodo['ingresos']);
        return json_encode(array('status'=>$nuevoIngreso));
    }

    public function Gastos() {
        $nuevoIngreso = $this->_endodonciaJsonModel->Gastos($this->_dataMetodo['gastos']);
        return json_encode(array('status'=>$nuevoIngreso));
    }

    public function traerConsentimientos( $codconsentimiento=null ){
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if( $codconsentimiento )
            $data['cod_paciente_consentimiento'] = $codconsentimiento;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerConsentimientos( $data );
    }

    public function traerTratamientosPacientes() {
        $tratamientos = $this->_endodonciaJsonModel->traerTratamientosPacientes($this->_dataMetodo);
        return json_encode(array('tratamientos'=>$tratamientos));
    }

    public function ConsentimientoInfo() {
        $result             = array();
        $dataConsentimiento = $this->_dataMetodo['consentimiento'];
        $huella             = getImagesByTemplate('huellas');

        if(!empty($huella))
            $imagenesHuellas = uploadImges($huella, ENDO_DIR_ADJ);

        if(!empty($imagenesHuellas))
            $dataConsentimiento['imagenes'] = $imagenesHuellas;

        $insertConsentimiento = $this->_endodonciaJsonModel->ConsentimientoInfo( $dataConsentimiento );

        if($insertConsentimiento>0)
            $result = $this->traerConsentimientos( $insertConsentimiento );
        else
            $result = json_encode($result);

        return $result;
    }

    public function AgendaMedica() {

        $result = json_encode(array());
        $insertaEvento = $this->_endodonciaJsonModel->AgendaMedica($this->_dataMetodo['agendamedica']);

        if($insertaEvento)
            return $this->traerAgendaMedica($insertaEvento);

        return $result;

    }

    public function traerAgendaMedica($codagenda=null) {

        $aData  = array();

        if($codagenda)
            $aData['codAgenda'] = $codagenda;

        if(isset($this->_dataMetodo['indConfirmado']))
            $aData['indConfirmado'] = 1;

        if(isset($this->_dataMetodo['fec_ini']) && isset($this->_dataMetodo['fec_hasta'])) {
            $aData['fec_ini']   = $this->_dataMetodo['fec_ini'];
            $aData['fec_hasta'] = $this->_dataMetodo['fec_hasta'];
        }

        if(isset($this->_dataMetodo['dsshboard'])) {
            $aData['fec_ini']   = date('Y-m-d');;
            $aData['fec_hasta'] = date('Y-m-d');;
        }

        return $this->_endodonciaJsonModel->traerAgendaMedica($aData);
    }

    public function eliminarAgenda() {
        return $this->_endodonciaJsonModel->eliminarAgenda($this->_dataMetodo['cod_agenda']);
    }

    public function traerCartera($codPaciente=null) {
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if( $codPaciente )
            $data['cod_paciente'] = $codPaciente;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerCartera( $data );
    }

    public function traerControles($codPaciente=null) {
        $data           = array();
        $data['inicio'] = !isset($this->_dataMetodo['start'])?:$this->_dataMetodo['start'];
        $data['filas']  = !isset($this->_dataMetodo['length'])?:$this->_dataMetodo['length'];
        $data['search'] = !isset($this->_dataMetodo['search']['value'])?:$this->_dataMetodo['search']['value'];

        if( $codPaciente )
            $data['cod_paciente'] = $codPaciente;

        if(isset($this->_dataMetodo['order']))
            $data['order']  = array('column'=>$this->_dataMetodo['columns'][$this->_dataMetodo['order'][0]['column']],'order'=>$this->_dataMetodo['order'][0]['dir']);

        return $this->_endodonciaJsonModel->traerControles( $data );
    }
}

$_aData     = formateaMetodo();
$_aMetodo   = $_aData['funcion'];
$_aClass    = new endodonciaJsonController( $_aData );

print $_aClass->$_aMetodo();