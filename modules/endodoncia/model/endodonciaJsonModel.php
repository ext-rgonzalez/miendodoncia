<?php

class ModeloJsonEndodoncia{

    private $_modeloEndodoncia  = null;

    public function __construct(){
        header('Access-Control-Allow-Origin: *');
        $this->_modeloEndodoncia= new ModeloEndodoncia();
        $this->_modeloGeneral   = new appModel();
    }

    public function Odontologos( $aData=array() ) {

        if ( !empty( $aData['nom_usuario'] ) ) {

            $aDataOdontologos = array(
                'no_esq_tabla'          => 'sys_usuario',
                'nom_usuario'           => $aData['nom_usuario'],
                'ape_usuario'           => $aData['ape_usuario'],
                'dir_usuario'           => $aData['dir_usuario'],
                'tel_usuario'           => $aData['tel_usuario'],
                'cel_usuario'           => $aData['cel_usuario'],
                'email_usuario'         => $aData['email_usuario'],
                'cod_usuario_registra'  => Session::get('cod'),
                'fec_usuario'           => date('Y-m-d H:i:s'),
                'cod_estado'            => 'AAA',
                'cod_perfil'            => $aData['cod_perfil'],
                'ind_medico'            => 1
            );

            if ( isset( $aData['cod_usuario'] ) && $aData['cod_usuario'] != null ) {
                $aDataOdontologos['cod_usuario'] = $aData['cod_usuario'];
                $this->_modeloEndodoncia->editRegistro( $aDataOdontologos );

                return $aData['cod_usuario'];
            } else {
                $this->_modeloGeneral->get_dato('count(1)', "nom_usuario='{$aData['nom_usuario']}' and ape_usuario='{$aData['ape_usuario']}'", 'sys_usuario');

                if ($this->_modeloGeneral->_data)
                    return 0;

                $secuencia = $this->_modeloEndodoncia->setSigSecuencia('sys_usuario');
                $aDataOdontologos['cod_usuario'] = $secuencia;
                $this->_modeloEndodoncia->setRegistro( $aDataOdontologos );

                return $secuencia;
            }
        }

        return false;
    }

    public function Pacientes( $aData=array() ) {
        if ( !empty( $aData['nom1_paciente'] ) ) {

            $aDataPacientes = array(
                'no_esq_tabla'              => 'endodoncia_paciente',
                'ced_paciente'              => $aData['ced_paciente'],
                'nom1_paciente'             => $aData['nom1_paciente'],
                'ape1_paciente'             => $aData['ape1_paciente'],
                'fec_nacimiento_paciente'   => $aData['fec_nacimiento_paciente'],
                'dir_paciente'              => $aData['dir_paciente'],
                'tel_paciente'              => $aData['tel_paciente'],
                'cel_paciente'              => $aData['cel_paciente'],
                'email_paciente'            => $aData['email_paciente'],
                'cod_ciudad'                => $aData['cod_ciudad'],
                'cod_genero'                => $aData['cod_genero'],
                'profesion_paciente'        => $aData['profesion_paciente'],
                'ind_embarazada'            => isset($aData['ind_embarazada'])?1:0,
                'fec_paciente'              => date('Y-m-d H:i:s'),
                'cod_estado'                => 'AAA',
                'cod_usuario'               => Session::get('cod'),
            );

            if ( isset( $aData['cod_paciente'] ) && $aData['cod_paciente'] != null ) {
                $aDataPacientes['cod_paciente']     = $aData['cod_paciente'];
                $aDataPacientes['fec_mod_paciente'] = date('Y-m-d H:i:s');

                $this->_modeloEndodoncia->editRegistro( $aDataPacientes );

                return $aData['cod_paciente'];
            } else {
                $this->_modeloGeneral->get_dato('count(1)', "ced_paciente='{$aData['ced_paciente']}'", 'endodoncia_paciente');

                if ($this->_modeloGeneral->_data)
                    return 0;

                $secuencia                      = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_paciente');
                $aDataPacientes['cod_paciente'] = $secuencia;
                $this->_modeloEndodoncia->setRegistro( $aDataPacientes );

                return $secuencia;
            }
        }

        return false;
    }

    public function Medicamentos( $aData=array() ) {

        if ( !empty( $aData['cod_unico_config_medicamentos'] ) ) {

            $aDataMedicamentos = array(
                'no_esq_tabla'                      => 'endodoncia_config_medicamentos',
                'cod_unico_config_medicamentos'     => $aData['cod_unico_config_medicamentos'],
                'nom_config_medicamentos'           => $aData['nom_config_medicamentos'],
                'des_config_medicamentos'           => $aData['des_config_medicamentos'],
                'forma_farma_config_medicamentos'   => $aData['forma_farma_config_medicamentos'],
                'fec_config_medicamentos'           => date('Y-m-d H:i:s'),
                'cod_usuario'                       => Session::get('cod'),
                'cod_estado'                        => 'AAA'
            );

            if ( isset( $aData['cod_config_medicamentos'] ) && $aData['cod_config_medicamentos'] != null ) {
                $aDataMedicamentos['cod_config_medicamentos'] = $aData['cod_config_medicamentos'];
                $this->_modeloEndodoncia->editRegistro( $aDataMedicamentos );

                return $aData['cod_config_medicamentos'];
            } else {
                $this->_modeloGeneral->get_dato('count(1)', "cod_unico_config_medicamentos='{$aData['cod_unico_config_medicamentos']}'", 'endodoncia_config_medicamentos');

                if ($this->_modeloGeneral->_data)
                    return 0;

                $secuencia = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_config_medicamentos');
                $aDataMedicamentos['cod_config_medicamentos'] = $secuencia;
                $this->_modeloEndodoncia->setRegistro( $aDataMedicamentos );

                return $secuencia;
            }
        }

        return false;
    }

    public function traerOdontologos( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = "where nom_usuario like '%{$data['search']}%' Or ape_usuario like '%{$data['search']}%' Or ced_usuario like '%{$data['search']}%'";

        if( isset($data['cod_usuario'] ) && !empty( $data['cod_usuario'] )) {
            $search = "where cod_usuario={$data['cod_usuario']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $this->_modeloGeneral->get_datos("t1.*,t2.nom_perfil,'action' as action","","sys_usuario as t1 left join sys_perfil as t2 on(t1.cod_perfil=t2.cod_perfil) {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","","sys_usuario as t1 left join sys_perfil as t2 on(t1.cod_perfil=t2.cod_perfil) {$search}");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);
    }

    public function traerMedicamentos( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = "where cod_unico_config_medicamentos like '%{$data['search']}%' Or nom_config_medicamentos like '%{$data['search']}%' Or forma_farma_config_medicamentos like '%{$data['search']}%'";

        if( isset($data['cod_config_medicamentos'] ) && !empty( $data['cod_config_medicamentos'] )) {
            $search = "where cod_config_medicamentos={$data['cod_config_medicamentos']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $this->_modeloGeneral->get_datos("*, 'action' as action","","endodoncia_config_medicamentos {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","","endodoncia_config_medicamentos {$search}");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);
    }

    public function cambiarEstadoOdontologo($aData=array()) {
        return $this->_modeloGeneral->set_simple_query("update sys_usuario set cod_estado='{$aData['estado']}' where cod_usuario={$aData['cod_usuario']}");
    }

    public function eliminaOdontologo($aData=array()) {
        $result = $this->_modeloGeneral->set_simple_query("delete from sys_usuario where cod_usuario={$aData['cod_usuario']}");
        return json_encode(array('status'=>1));
    }

    public function eliminaHistoriaClinica() {
        $result = $this->_modeloGeneral->set_simple_query("delete from sys_usuario where cod_usuario={$aData['cod_usuario']}");
        return json_encode(array('status'=>1));
    }

    public function cambiarEstadoPaciente($aData=array()) {
        return $this->_modeloGeneral->set_simple_query("update endodoncia_paciente set cod_estado='{$aData['estado']}' where cod_paciente={$aData['cod_paciente']}");
    }

    public function eliminaPaciente($aData=array()) {
        $result = $this->_modeloGeneral->set_simple_query("delete from endodoncia_paciente where cod_paciente={$aData['cod_paciente']}");
        return json_encode(array('status'=>1));
    }

    public function traerHistoriaClinica( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = "where nom_paciente like '%{$data['search']}%' Or ced_paciente like '%{$data['search']}%' Or diente_tratado like '%{$data['search']}%'";

        if( isset($data['cod_historia_clinica'] ) && !empty( $data['cod_historia_clinica'] )) {
            $search = "where cod_historia_clinica={$data['cod_historia_clinica']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $this->_modeloGeneral->get_datos("t1.*,'action' as action","","endodoncia_view_historia_clinica as t1 {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","","endodoncia_view_historia_clinica {$search}");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);
    }

    public function traerPacientes( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = "where nom_paciente like '%{$data['search']}%' Or ced_paciente like '%{$data['search']}%'";

        if( isset($data['cod_paciente'] ) && !empty( $data['cod_paciente'] )) {
            $search = "where cod_paciente={$data['cod_paciente']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $this->_modeloGeneral->get_datos("t1.*,'action' as action","","endodoncia_view_paciente as t1 {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","","endodoncia_view_paciente {$search}");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);
    }

    public function consultaDienteHistoriaDatos( $aData=null ) {

        $aData['cod_historia_clinica']  = 0;
        $aData['case']                  = 0;

        $result = $this->_modeloEndodoncia->consultaDienteHistoriaDatos( $aData ) ;

        return json_encode( $result );
    }

    public function consultaMedicamentos($aData=null) {

        $result = array();

        $this->_modeloGeneral->get_datos("cod_config_medicamentos as codigo,concat(nom_config_medicamentos,forma_farma_config_medicamentos) result","cod_unico_config_medicamentos like '%{$aData['term']}%' Or nom_config_medicamentos like '%{$aData['term']}%'","endodoncia_config_medicamentos");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config)
           return json_encode($config);

        return $result;
    }


    public function consultaCiudad($aData=null) {
        $result = array();

        $this->_modeloGeneral->get_datos("cod_ciudad as codigo,nom_ciudad as result","cod_ciudad like '%{$aData['term']}%' Or nom_ciudad like '%{$aData['term']}%'","sys_ciudad");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config)
            return json_encode($config);

        return $result;
    }

    public function traerSubDiagnosticos($codDiagnosticos=null) {
        $result = array();

        $this->_modeloGeneral->get_datos("cod_config_diagnosticos,des_config_diagnosticos","nom_config_diagnosticos='{$codDiagnosticos}' ","endodoncia_config_diagnosticos");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config)
            return json_encode($config);

        return $result;
    }

    public function HistoriaClinica( $aData=array() ) {

        $historiclinicaprocesada    = false;
        $cedPaciente                = 0;

        if ( !empty( $aData['paciente']['cod_paciente'] ) && !empty( $aData['config_dental']['cod_config_dental'] ) ) {

            $aDataHistoriaClinica = array(
                'no_esq_tabla'                  => 'endodoncia_historia_clinica',
                'cod_paciente'                  => $aData['paciente']['cod_paciente'],
                'cod_config_dental'             => $aData['config_dental']['cod_config_dental'],
                'motivo_historia_clinica'       => $aData['historiaclinica']['motivo_historia_clinica'],
                'que_historia_clinica'          => $aData['historiaclinica']['que_historia_clinica'],
                'como_historia_clinica'         => $aData['historiaclinica']['como_historia_clinica'],
                'cuando_historia_clinica'       => $aData['historiaclinica']['cuando_historia_clinica'],
                'donde_historia_clinica'        => $aData['historiaclinica']['como_historia_clinica'],
                'porque_historia_clinica'       => $aData['historiaclinica']['porque_historia_clinica'],
                'cod_analisis_sensibilidad'     => $aData['historiaclinica']['cod_analisis_sensibilidad'],
                'des_anarad_historia_clinica'   => $aData['historiaclinica']['des_remision_historia_clinica'],
                'ind_desobturacion'             => isset($aData['historiaclinica']['ind_desobturacion'])?1:0,
                'ind_retratamiento'             => isset($aData['historiaclinica']['ind_retratamiento'])?1:0,
                'ind_temporales'                => isset($aData['historiaclinica']['ind_temporales'])?1:0,
                'cod_usuario'                   => Session::get('cod'),
                'cod_estado'                    => 'AAA',
                'cod_config_control'            => $aData['historiaclinica']['cod_config_control'],
                'imp_total_historia_clinica'    => $aData['historiaclinica']['imp_total_historia_clinica'],
                'imp_adeu_historia_clinica'     => $aData['historiaclinica']['imp_total_historia_clinica'],
                'otro_tejidos_blandos'          => isset($aData['tej_blandos']['otro_cod_tej_bla'])?$aData['tej_blandos']['otro_cod_tej_bla']:'',
                'otro_tejidos_dentales'         => isset($aData['tej_dentales']['otro_cod_tej_den'])?$aData['tej_dentales']['otro_cod_tej_den']:'',
                'otro_tejidos_periodontales'    => isset($aData['tej_periodontales']['otro_cod_tej_per'])?$aData['tej_periodontales']['otro_cod_tej_per']:'',
                'otro_tejidos_perirradiculares' => isset($aData['tej_perirradiculares']['otro_cod_tej_peri'])?$aData['tej_perirradiculares']['otro_cod_tej_peri']:'',
                'otro_tejidos_pulpares'         => isset($aData['tej_pulpares']['otro_cod_tej_pul'])?$aData['tej_pulpares']['otro_cod_tej_pul']:'',
                'des_remision_historia_clinica' => $aData['historiaclinica']['des_remision_historia_clinica'],
                'fec_prox_pago'                 => $aData['historiaclinica']['fec_prox_pago'],
                'cod_empresa'                   => $this->_modeloEndodoncia->_empresa
            );

            if ( isset( $aData['historiaclinica']['cod_historia_clinica'] ) && $aData['historiaclinica']['cod_historia_clinica'] != null ) {
                $aDataHistoriaClinica['cod_historia_clinica']       = $aData['historiaclinica']['cod_historia_clinica'];
                $aDataHistoriaClinica['fec_mod_historia_clinica']   = date('Y-m-d H:i:s');
                $this->_modeloEndodoncia->editRegistro( $aDataHistoriaClinica );

                $historiclinicaprocesada = $aData['historiaclinica']['cod_historia_clinica'];
            } else {

                $secuencia = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_historia_clinica');
                $aDataHistoriaClinica['cod_historia_clinica']       = $secuencia;
                $aDataHistoriaClinica['fec_historia_clinica']   = date('Y-m-d H:i:s');

                $this->_modeloEndodoncia->setRegistro( $aDataHistoriaClinica );

                $historiclinicaprocesada = $secuencia;
            }

            if($historiclinicaprocesada) {
                if(isset($aData['alergias']['cod_ale']))
                    $this->HistoriaClinicaAlergias($historiclinicaprocesada, $aData['alergias']['cod_ale']);

                if(isset($aData['ana_radiografico']['cod_anarad']))
                    $this->HistoriaClinicaAnalisisRadiografico($historiclinicaprocesada, $aData['ana_radiografico']['cod_anarad']);
                //antecedentes
                if(isset($aData['ant_familiares']['cod_ant_fam']))
                    $this->HistoriaClinicaAntecedentes($historiclinicaprocesada, $aData['ant_familiares']['cod_ant_fam']);

                if(isset($aData['ant_odontologicos']['cod_ant_odo']))
                    $this->HistoriaClinicaAntecedentes($historiclinicaprocesada, $aData['ant_odontologicos']['cod_ant_odo'], 'odontologicos');

                if(isset($aData['ant_personales']['cod_ant_per']))
                    $this->HistoriaClinicaAntecedentes($historiclinicaprocesada, $aData['ant_personales']['cod_ant_per'], 'personales');

                if(isset($aData['diagnostico']['cod_subdiagnostico']))
                    $this->HistoriaClinicaDiagnostico($historiclinicaprocesada, array($aData['diagnostico']['cod_subdiagnostico']));

                if(isset($aData['medicamentos']['cod_med']))
                    $this->HistoriaClinicaMedicamentos($historiclinicaprocesada, $aData['medicamentos']['cod_med']);

                //tejidos
                if(isset($aData['tej_blandos']['cod_tej_bla']))
                    $this->HistoriaClinicaTejidos($historiclinicaprocesada, $aData['tej_blandos']['cod_tej_bla']);

                if(isset($aData['tej_dentales']['cod_tej_den']))
                    $this->HistoriaClinicaTejidos($historiclinicaprocesada, $aData['tej_dentales']['cod_tej_den'], 'dental');

                if(isset($aData['tej_periodontales']['cod_tej_per']))
                    $this->HistoriaClinicaTejidos($historiclinicaprocesada, $aData['tej_periodontales']['cod_tej_per'], 'periodontal');

                if(isset($aData['tej_perirradiculares']['cod_tej_peri']))
                    $this->HistoriaClinicaTejidos($historiclinicaprocesada, $aData['tej_perirradiculares']['cod_tej_peri'], 'perirradicular');

                if(isset($aData['tej_pulpares']['cod_tej_pul']))
                    $this->HistoriaClinicaTejidos($historiclinicaprocesada, $aData['tej_pulpares']['cod_tej_pul'], 'pulpar');

                //imagenes
                if(isset($aData['imagenes']) && !empty($aData['imagenes']))
                    $this->HistoriaClinicaImgenes($aData['paciente']['cod_paciente'], $historiclinicaprocesada, $aData['imagenes']);

                //Evolucion
                if(isset($aData['evolucion']['nuevaEvolucion']))
                    $this->HistoriaClinicaEvoluciones($aData['paciente']['cod_paciente'], $historiclinicaprocesada, $aData['evolucion']['nuevaEvolucion'], $aData['historiaclinica']['hora_entrada']);

                //conductos
                $conductos = array_merge($aData['conductos'], $aData['desobturacion']);
                $this->HistoriaClinicaConductos($historiclinicaprocesada, $conductos);

                $this->_modeloGeneral->get_dato('ced_paciente',"cod_paciente={$aData['paciente']['cod_paciente']}","endodoncia_paciente");
                $cedPaciente = $this->_modeloGeneral->_data;
            }

            return array('cod_historia_clinica'=>$historiclinicaprocesada,'ced_paciente'=>$cedPaciente);
        }

        return false;
    }

    public function Evoluciones( $aData=array() ) {

        if($aData['historiaclinica']['cod_historia_clinica']) {

            $historiclinicaprocesada = $aData['historiaclinica']['cod_historia_clinica'];

            //imagenes
            if(isset($aData['imagenes']) && !empty($aData['imagenes']))
                $this->HistoriaClinicaImgenes($aData['paciente']['cod_paciente'], $historiclinicaprocesada, $aData['imagenes'], true);

            //Evolucion
            if(isset($aData['evolucion']['nuevaEvolucion']))
                $this->HistoriaClinicaEvoluciones($aData['paciente']['cod_paciente'], $historiclinicaprocesada, $aData['evolucion']['nuevaEvolucion'], date('H:i:s'));

            //conductos
            $conductos = array_merge($aData['conductos'], $aData['desobturacion']);
            $this->HistoriaClinicaConductos($historiclinicaprocesada, $conductos);

            return $historiclinicaprocesada;
        }

        return false;
    }

    public function HistoriaClinicaAlergias($codHistoriaClinica, $aData=array()) {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_historia_clinica_alergias');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_alergias where cod_historia_clinica={$codHistoriaClinica}");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'          => 'endodoncia_historia_clinica_alergias',
                'cod_config_alergias'   => $aDetalle,
                'cod_historia_clinica'  => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }
    }

    public function HistoriaClinicaAnalisisRadiografico($codHistoriaClinica, $aData=array()) {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_historia_clinica_analisis_radiografico');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_analisis_radiografico where cod_historia_clinica={$codHistoriaClinica}");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'                      => 'endodoncia_historia_clinica_analisis_radiografico',
                'cod_config_analisis_radiografico'  => $aDetalle,
                'cod_historia_clinica'              => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }
    }

    public function HistoriaClinicaAntecedentes($codHistoriaClinica, $aData=array(), $tipoAntecedentes='familiares') {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica} and cod_config_antecedentes_{$tipoAntecedentes} is not null", 'endodoncia_historia_clinica_antecedentes');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_analisis_radiografico where cod_historia_clinica={$codHistoriaClinica} and cod_config_antecedentes_{$tipoAntecedentes}>0");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'                                  => 'endodoncia_historia_clinica_antecedentes',
                "cod_config_antecedentes_{$tipoAntecedentes}"   => $aDetalle,
                'cod_historia_clinica'                          => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }

    }

    public function HistoriaClinicaDiagnostico($codHistoriaClinica, $aData=array()) {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_historia_clinica_diagnostico');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_diagnostico where cod_historia_clinica={$codHistoriaClinica}");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'             => 'endodoncia_historia_clinica_diagnostico',
                'cod_config_diagnosticos'  => $aDetalle,
                'cod_historia_clinica'     => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }
    }

    public function HistoriaClinicaConductos($codHistoriaClinica, $aData=array()) {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_historia_clinica_informacion_conductos');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_informacion_conductos where cod_historia_clinica={$codHistoriaClinica}");

        $aDataInsert = array(
            'no_esq_tabla'             => 'endodoncia_historia_clinica_informacion_conductos',
            'cod_historia_clinica'     => $codHistoriaClinica
        );

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert[str_replace('noo_','',$key)] = $aDetalle;
        }

        $this->_modeloEndodoncia->setRegistro( $aDataInsert );

    }

    public function HistoriaClinicaMedicamentos($codHistoriaClinica, $aData=array()) {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_historia_clinica_medicamentos');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_medicamentos where cod_historia_clinica={$codHistoriaClinica}");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'             => 'endodoncia_historia_clinica_medicamentos',
                'cod_config_medicamentos'  => $aDetalle,
                'cod_historia_clinica'     => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }
    }

    public function HistoriaClinicaTejidos($codHistoriaClinica, $aData=array(), $tipoTejidos='blandos') {

        $this->_modeloGeneral->get_dato('count(1)', "cod_historia_clinica={$codHistoriaClinica} and cod_config_tejidos_{$tipoTejidos} is not null", 'endodoncia_historia_clinica_tejidos');

        if ($this->_modeloGeneral->_data)
            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_historia_clinica_tejidos where cod_historia_clinica={$codHistoriaClinica} and cod_config_tejidos_{$tipoTejidos}>0");

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'                          => 'endodoncia_historia_clinica_tejidos',
                "cod_config_tejidos_{$tipoTejidos}"     => $aDetalle,
                'cod_historia_clinica'                  => $codHistoriaClinica
            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }

    }

    public function HistoriaClinicaImgenes($codPaciente, $codHistoriaClinica, $aData=array(), $evolucion=false) {

        $this->_modeloGeneral->get_datos('img_registro_imagenes', "cod_historia_clinica={$codHistoriaClinica}", 'endodoncia_registro_imagenes');
        $imagenesActuales = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if (count($imagenesActuales)>0 && !$evolucion) {

            $delete = $this->_modeloGeneral->set_simple_query("delete from endodoncia_registro_imagenes where cod_historia_clinica={$codHistoriaClinica}");

            foreach($imagenesActuales as $key=>$aDetailArchivos) {
                unlink(ENDO_DIR_ADJ.$aDetailArchivos);
            }
        }

        foreach($aData as $key=>$aDetalle) {
            $aDataInsert = array(
                'no_esq_tabla'          => 'endodoncia_registro_imagenes',
                'cod_historia_clinica'  => $codHistoriaClinica,
                'cod_config_tipo_imagen'=> 1,
                'nom_registro_imagenes' => 'Registro Radiografico',
                'img_registro_imagenes' => $aDetalle,
                'fec_registro_imagenes' => date('Y-m-d H:i:s'),
                'cod_usuario'           => Session::get('cod'),
                'cod_estado'            => 'AAA'

            );

            $this->_modeloEndodoncia->setRegistro( $aDataInsert );
        }
    }

    public function HistoriaClinicaEvoluciones($codPaciente, $codHistoriaClinica, $aData=null, $horaEntrada=null) {

        $aDataInsert = array(
            'no_esq_tabla'                      => 'endodoncia_paciente_evolucion',
            'cod_paciente'                      => $codPaciente,
            'des_paciente_evolucion'            => $aData,
            'cod_historia_clinica'              => $codHistoriaClinica,
            'hora_entrada_paciente_evolucion'   => $horaEntrada,
            'fec_paciente_evolucion'            => date('Y-m-d H:i:s'),
            'cod_usuario'                       => Session::get('cod'),
            'cod_empresa'                       => $this->_modeloEndodoncia->_empresa
        );

        $this->_modeloEndodoncia->setRegistro( $aDataInsert );

    }

    public function GenerarHistoriaClinicaPDF($aData=array()) {
        $result = false;
        $data   = $this->_modeloEndodoncia->HistoriClinicaPDFData( $aData );

        if(isset($aData['cod_odontologo']))
            $data['odontologos'] = explode(',',$aData['cod_odontologo']);

        $result = generaHistoriaClinicaPDF($data, $aData['type'], ENDO_DIR_ADJ, LIBS);
        return $result;
    }

    public function cambiarEstadoHistoriaClinica($aData=array()) {
        return $this->_modeloGeneral->set_simple_query("update endodoncia_historia_clinica set cod_estado='{$aData['estado']}' where cod_historia_clinica={$aData['cod_historia_clinica']}");
    }

    public function infoRemisiones($aData=array()){

        $lista = array();

        $this->_modeloGeneral->get_datos("cod_registro_imagenes,img_registro_imagenes","cod_historia_clinica={$aData['cod_historia_clinica']}","endodoncia_registro_imagenes");
        if(isset($this->_modeloGeneral->_data))
            $lista = $this->_modeloGeneral->_data;

        $panel = $this->_modeloEndodoncia->traerImagenesHistoriaClinica($aData);

        return json_encode(array('lista'=>$lista, 'panel'=>$panel));

    }

    public function GenerarRemisionPDF($aData=array()) {
        $result                 = false;
        $imagenesSeleccionadas  = explode('|', $aData['cod_imagenes']);
        $data                   = $this->_modeloEndodoncia->HistoriClinicaPDFData( $aData, $imagenesSeleccionadas);

        if(isset($aData['cod_odontologo']))
            $data['odontologos'] = explode(',',$aData['cod_odontologo']);

        $result = generaRemisionPDF($data, $aData['type'], ENDO_DIR_ADJ, LIBS);
        return $result;
    }

    public function GenerarIngresoPDF($aData=array()) {
        $result                 = false;
        $data                   = $this->_modeloEndodoncia->ComprobanteIngresoPDFData( $aData );

        $result = generaComprobanteIngresoPDF($data, $aData['type'], ENDO_DIR_ADJ, LIBS);
        return $result;
    }

    public function infoEvoluciones($aData=array()){

        $evoluciones    = $this->_modeloEndodoncia->traerEvoluciones($aData);
        $diagnostico    = $this->_modeloEndodoncia->traerPanelDiagnostico($aData);
        $desopturacion  = $this->_modeloEndodoncia->traerDesobturacion($aData);

        return json_encode(array('evoluciones'=>$evoluciones, 'diagnostico'=>$diagnostico, 'desobturacion'=>$desopturacion));

    }

    public function getNumComprobantes($cod_empresa=null){
        return $this->_modeloEndodoncia->getNumComprobantes($cod_empresa);
    }

    public function getInfoIngreso($aData=array()) {
        return $this->_modeloEndodoncia->getDatosIngresoHistoriaClinica($aData);
    }

    public function Ingresos($aData=null) {

        if(isset($aData['ingresos']['detalle']) && count($aData['ingresos']['detalle'])>0) {

            $aDataIngreso = array(
                'no_esq_tabla'              => 'endodoncia_pago',
                'cod_paciente'              => $aData['paciente']['cod_paciente'],
                'num_sig_comp_ingreso'      => $aData['ingresos']['num_sig_comp_ingreso'],
                'cod_met_pago'              => 1,
                'obs_pago'                  => $aData['ingresos']['obs_pago'],
                'not_pago'                  => $aData['ingresos']['not_pago'],
                'ind_ingreso'               => 1,
                'cod_empresa'               => $this->_modeloEndodoncia->_empresa,
                'cod_usuario'               => Session::get('cod')
            );



            $secuencia = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_pago');

            $aDataIngreso['cod_pago']   = $secuencia;
            $aDataIngreso['fec_pago']   = date('Y-m-d H:i:s');

            $this->_modeloEndodoncia->setRegistro( $aDataIngreso );

            $pagoprocesado      = $secuencia;
            $historiaClinica    = isset($aData['historiaclinica']['cod_historia_clinica'])?$aData['historiaclinica']['cod_historia_clinica']:null;

            if($pagoprocesado) {
                $this->pagoDetalle($aData['ingresos']['detalle'], $pagoprocesado, $historiaClinica);
                return $pagoprocesado;
            }
        }

        return false;
    }

    public function Gastos($aData=null) {

        if(isset($aData['gastos']['detalle']) && count($aData['gastos']['detalle'])>0) {

            $aDataIngreso = array(
                'no_esq_tabla'              => 'endodoncia_pago',
                'num_sig_comp_egreso'       => $aData['gastos']['num_sig_comp_ingreso'],
                'cod_met_pago'              => 1,
                'obs_pago'                  => $aData['gastos']['obs_pago'],
                'not_pago'                  => $aData['gastos']['not_pago'],
                'ind_egreso'                => 1,
                'cod_empresa'               => $this->_modeloEndodoncia->_empresa,
                'cod_usuario'               => Session::get('cod')
            );



            $secuencia = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_pago');

            $aDataIngreso['cod_pago']   = $secuencia;
            $aDataIngreso['fec_pago']   = date('Y-m-d H:i:s');

            $this->_modeloEndodoncia->setRegistro( $aDataIngreso );

            $pagoprocesado      = $secuencia;

            if($pagoprocesado) {
                $this->pagoDetalle($aData['gastos']['detalle'], $pagoprocesado);
                return $pagoprocesado;
            }
        }

        return false;
    }

    public function pagoDetalle($aData=array(), $referencia=null, $cod_historia_clinica=null) {

        if($referencia && count($aData)>0) {

            foreach($aData['recibido'] as $key=>$valor) {
                $aDataDetalleIngreso = array(
                    'no_esq_tabla'          => 'endodoncia_pago_detalle',
                    'cod_pago'              => $referencia,
                    'cod_historia_clinica'  => isset($aData['cod_historia_clinica'][$key])?$aData['cod_historia_clinica'][$key]:$cod_historia_clinica,
                    'con_pago_detalle'      => $aData['detalle'][$key],
                    'imp_pago_detalle'      => $valor,
                );

                $this->_modeloEndodoncia->setRegistro( $aDataDetalleIngreso );

                if(isset($aData['fec_prox_pago']) && !empty($aData['fec_prox_pago'][$key])) {
                    $codHistoria = isset($aData['cod_historia_clinica'][$key])?$aData['cod_historia_clinica'][$key]:$cod_historia_clinica;
                    $this->_modeloGeneral->set_simple_query("update endodoncia_historia_clinica set fec_prox_pago='{$aData['fec_prox_pago'][$key]}' where cod_historia_clinica={$codHistoria}");
                }
            }

            return true;
        }

        return false;
    }

    public function traerConsentimientos( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = "where nom_paciente like '%{$data['search']}%' Or num_config_dental like '%{$data['search']}%' Or nom_config_consentimiento like '%{$data['search']}%'";

        if( isset($data['cod_paciente_consentimiento'] ) && !empty( $data['cod_paciente_consentimiento'] )) {
            $search = "where cod_paciente_consentimiento={$data['cod_paciente_consentimiento']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $this->_modeloGeneral->get_datos("*,'action' as action","","endodoncia_view_paciente_consentimiento {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","","endodoncia_view_paciente_consentimiento {$search}");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);
    }

    public function traerTratamientosPacientes($aData) {
        return $this->_modeloEndodoncia->tratamientosPaciente($aData);
    }

    public function ConsentimientoInfo( $aData=array() ) {

        if($aData['cod_paciente'] && $aData['cod_historia_clinica'] && $aData['cod_config_consentimiento'] && is_array($aData['imagenes'])) {

            $aDataConsentimiento = array(
                'no_esq_tabla'                  => 'endodoncia_paciente_consentimiento',
                'cod_paciente'                  => $aData['cod_paciente'],
                'cod_historia_clinica'          => $aData['cod_historia_clinica'],
                'cod_config_consentimiento'     => $aData['cod_config_consentimiento'],
                'img_paciente_consentimiento'   => $aData['imagenes'][0],
                'cod_usuario'                   => Session::get('cod'),
                'cod_empresa'                   => $this->_modeloEndodoncia->_empresa,
                'cod_estado'                    => 'AAA'
            );

            $secuencia = $this->_modeloEndodoncia->setSigSecuencia('endodoncia_paciente_consentimiento');

            $aDataConsentimiento['cod_paciente_consentimiento'] = $secuencia;
            $aDataConsentimiento['fec_paciente_consentimiento'] = date('Y-m-d H:i:s');

            $this->_modeloEndodoncia->setRegistro( $aDataConsentimiento );

            $consentimientoprocesado = $secuencia;

            if($consentimientoprocesado)
                return $consentimientoprocesado;

        }

        return false;
    }

    public function AgendaMedica( $aData=array() ) {

        if($aData['start_evento_agenda'] && $aData['end_evento_agenda']) {

            $aDataAgendaMedica = array(
                'no_esq_tabla'                  => 'sys_agenda',
                'start_evento_agenda'           => $aData['start_evento_agenda'],
                'end_evento_agenda'             => $aData['end_evento_agenda'],
                'cod_usuario'                   => Session::get('cod'),
                'cod_empresa'                   => $this->_modeloEndodoncia->_empresa,
                'ind_confirmado'                => isset($aData['ind_confirmado'])?1:0
            );

            if(isset($aData['nom_evento_agenda']))
                $aDataAgendaMedica['nom_evento_agenda'] = $aData['nom_evento_agenda'];

            if(isset($aData['des_evento_agenda']))
                $aDataAgendaMedica['des_evento_agenda'] = $aData['des_evento_agenda'];

            $aDataAgendaMedica['color_agenda']  = $aDataAgendaMedica['ind_confirmado']?'alert-info':'alert-warning';

            if ( isset( $aData['cod_agenda'] ) && $aData['cod_agenda'] != null ) {
                $aDataAgendaMedica['cod_agenda']    = $aData['cod_agenda'];
                $aDataAgendaMedica['fec_mod_agenda']= date('Y-m-d H:i:s');

                $this->_modeloEndodoncia->editRegistro( $aDataAgendaMedica );

                return $aData['cod_agenda'];
            } else {
                $secuencia = $this->_modeloEndodoncia->setSigSecuencia('sys_agenda');
                $aDataAgendaMedica['cod_agenda']    = $secuencia;
                $aDataAgendaMedica['fec_agenda']    = date('Y-m-d H:i:s');

                $this->_modeloEndodoncia->setRegistro( $aDataAgendaMedica );

                return $secuencia;
            }

        }

        return false;
    }

    public function traerAgendaMedica($aData=null) {
        $soloConfirmados= "";
        $rangoFechas    = "";
        $withagenda     = isset($aData['codAgenda'])?"cod_agenda={$aData['codAgenda']}" : "date(start_evento_agenda) between date_sub(now(), interval 2 month) and date_add(now(), interval 2 month)";

        if (isset($aData['indConfirmado'])) {
            $soloConfirmados = "ind_confirmado=1 and date(start_evento_agenda) between date_sub(now(), interval 2 month) and date_add(now(), interval 2 month)";
            $withagenda = "";
        }

        if (isset($aData['fec_ini']) && isset($aData['fec_hasta'])) {
            $rangoFechas = "date(start_evento_agenda) between date('{$aData['fec_ini']}') and date('{$aData['fec_hasta']}')";
            $withagenda = "";
        }

        $this->_modeloGeneral->get_datos("*","{$withagenda} {$soloConfirmados} {$rangoFechas}","sys_agenda");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config)
            $config = array_map(function($item){
                return array(
                    'id'                => $item['cod_agenda'],
                    'title'             => $item['nom_evento_agenda'].':'.$item['des_evento_agenda'],
			        'start'             => $item['start_evento_agenda'],
                    'end'               => $item['end_evento_agenda'],
                    'ind_confirmado'    => $item['ind_confirmado'],
                    'cod_agenda'        => $item['cod_agenda'],
                    'allDay'            => false,
                    'backgroundColor'   => $item['ind_confirmado']?'#27a9e3':'#646464',
                    'className'         => $item['color_agenda']);

            }, $config);


        return json_encode($config);
    }

    public function eliminarAgenda($cod_agenda=null) {
        if($cod_agenda) {
            $this->_modeloGeneral->set_simple_query("delete from sys_agenda where cod_agenda={$cod_agenda}");
            return json_encode(array('status'=>1));

        }
    }

    public function traerCartera( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = " and fec_prox_pago like '%{$data['search']}%' Or nom1_paciente like '%{$data['search']}%' and ape1_paciente like '%{$data['search']}%'";

        if( isset($data['cod_paciente'] ) && !empty( $data['cod_paciente'] )) {
            $search = " and cod_paciente={$data['cod_paciente']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $auxWhere = !isset($data['search']) || !isset($data['cod_paciente'])?"and t1.fec_prox_pago between date_sub(date(now()), interval 1 week) and date_add(date(now()), interval 1 week)":'';

        $select = "t1.fec_prox_pago, t1.cod_paciente,t5.nom_config_dental,t1.cod_historia_clinica, max(date(t4.fec_pago)) as fec_ult_pago,concat(t2.ape1_paciente,' ',t2.nom1_paciente) as nom_paciente, format(t1.imp_total_historia_clinica,0) as imp_total_historia_clinica,format(t1.imp_adeu_historia_clinica,0) as imp_adeu_historia_clinica,format(t1.imp_canc_historia_clinica,0) as imp_canc_historia_clinica, if(t1.fec_prox_pago<date(now()),1,0) as vencido";
        $from   = "endodoncia_historia_clinica as t1 left join endodoncia_pago_detalle as t3 on(t1.cod_historia_clinica=t3.cod_historia_clinica) left join endodoncia_pago as t4 on(t3.cod_pago=t4.cod_pago),endodoncia_paciente as t2, endodoncia_config_dental as t5";
        $where  = "where t1.cod_paciente=t2.cod_paciente and t1.cod_config_dental=t5.cod_config_dental and t1.imp_adeu_historia_clinica>0 {$auxWhere}";
        $groupBy= "group by t1.cod_historia_clinica";

        $this->_modeloGeneral->get_datos("{$select}","","{$from} {$where} {$search} {$groupBy} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","t1.imp_adeu_historia_clinica > 0 {$auxWhere}","endodoncia_historia_clinica as t1");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);

    }

    public function traerControles( $data=array() ) {
        $search     = '';
        $limit      = "limit {$data['inicio']}, {$data['filas']}";
        $order      = '';
        $result     = array();
        $configCount= 0;

        if(isset($data['search']) && !empty($data['search']))
            $search = " and nom1_paciente like '%{$data['search']}%' and ape1_paciente like '%{$data['search']}%'";

        if( isset($data['cod_paciente'] ) && !empty( $data['cod_paciente'] )) {
            $search = " and cod_paciente={$data['cod_paciente']}";
            $limit  = '';
        }

        if(isset($data['order']))
            $order = " order by {$data['order']['column']['data']} {$data['order']['order']}";

        $auxWhere = !isset($data['search']) || !isset($data['cod_paciente'])?"and date(date_add(t1.fec_historia_clinica, interval t2.mes_config_control month)) between date_sub(date(now()), interval 1 week) and date_add(date(now()), interval 1 week)":'';

        $select = "t1.fec_historia_clinica, t3.cod_paciente, t5.nom_config_dental, t1.cod_historia_clinica, CONCAT(t3.ape1_paciente, ' ', t3.nom1_paciente) AS nom_paciente, date(date_add(t1.fec_historia_clinica, interval t2.mes_config_control month)) as fec_control, if(date(date_add(t1.fec_historia_clinica, interval t2.mes_config_control month)) < DATE(NOW()), 1, 0) AS vencido";
        $from   = "endodoncia_historia_clinica as t1,endodoncia_config_control as t2, endodoncia_paciente AS t3,endodoncia_config_dental as t5";
        $where  = "where t1.cod_paciente = t3.cod_paciente and t1.cod_config_control=t2.cod_config_control and t1.cod_config_dental=t5.cod_config_dental {$auxWhere}";

        $this->_modeloGeneral->get_datos("{$select}","","{$from} {$where} {$search} {$order} {$limit}");
        $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        if($config) {
            $this->_modeloGeneral->get_dato("count(1)","t1.cod_config_control=t2.cod_config_control {$auxWhere}","endodoncia_historia_clinica as t1,endodoncia_config_control as t2");
            $configCount = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:0;
        }

        $result = array(
            'recordsTotal'      => $configCount,
            'recordsFiltered'   => $configCount,
            'data'              => $config
        );

        return json_encode($result);

    }
}

