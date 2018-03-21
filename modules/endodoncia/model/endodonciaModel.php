<?php

class ModeloEndodoncia extends DBAbstractModel {
    public      $_modulo    = 12;
    public      $_empresa   = 20;
    protected   $cod;
    private     $_data = null;


    public function setRegistro($user_data = array()) {
        $this->query = "";
        $tblCol = '';
        $tblVal = ''; $err_img = '';
        $colExt = ''; $colName = '';
        $valExt = ''; $valName = '';

        if (isset($user_data['cod_' . str_replace('sys_','',str_replace('endodoncia_', '', $user_data['no_esq_tabla']))]) and empty($user_data['cod_' . str_replace('sys_','',str_replace('endodoncia_', '', $user_data['no_esq_tabla']))])) {
            $user_data['cod_' . str_replace('sys_','',str_replace('endodoncia_', '', $user_data['no_esq_tabla']))] = $this->setSigSecuencia($user_data['no_esq_tabla']);
        }
        // se llena el valor para los campos que lo requiran el el array
        if (isset($user_data['cod_usuario']) and empty($user_data['cod_usuario'])){$user_data['cod_usuario'] = Session::get('cod');}
        /* obtengo el nombre de la tabla para futuras validaciones */
        $pos = strpos($user_data['no_esq_tabla'], '_');
        $nomTabla = substr($user_data['no_esq_tabla'], $pos + 1, strlen($user_data['no_esq_tabla']));
        fbFormateaPost($user_data, $tblCol, $tblVal);

        //Procesamos los archivos adjuntos que vienen con el post
        $icount = contarCoincidencias($user_data,"tmp_img");
        for($f=0;$f<$icount;$f++):
            $t=$f>0 ? $f : '';
            if (isset($user_data["no_tmp_img".$t]) Or !empty($user_data["no_tmp_img".$t])):
                $err_img = $err_img . uploadImg($user_data, ENDO_DIR_ADJ, $valName, $colName,$t);
            endif;
            //completamos las columnas extras para la transaccion
            if (!empty($valName)) {
                $valExt .= ",'" . $valName . "'";
                $colExt .= "," . $colName . "";
                $colExt .= $nomTabla;
            }
        endfor;

        $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "
                                     (" . substr($tblCol, 0, (strlen($tblCol) - 1)) . "" . $colExt . ")
                              VALUES (" . substr($tblVal, 0, (strlen($tblVal) - 1)) . "" . $valExt . ")";
        //print $this->query;exit;
        $this->execute_single_query();

        return true;
    }

    # Modificar un registro

    public function editRegistro($user_data = array()) {
        $this->query = "";
        $tblCol = '';
        $tblVal = ''; $err_img = '';
        $colExt = ''; $colName = '';
        $valExt = ''; $valName = '';

        if (isset($user_data['cod_usuario']) and empty($user_data['cod_usuario'])) {
            $user_data['cod_usuario'] = Session::get('cod');
        }
        /* obtengo el nombre de la tabla para futuras validaciones */
        $pos = strpos($user_data['no_esq_tabla'], '_');
        $nomTabla = substr($user_data['no_esq_tabla'], $pos + 1, strlen($user_data['no_esq_tabla']));
        foreach ($user_data as $col => $dat) {
            if ((strpos(substr($col, 0, 3), 'no_') === false) && (strpos($col, 'cod_' . $nomTabla) === false)) {
                $tblCol = $tblCol . $col . "='" . $dat . "'" . ',';
            }
        }

        //Procesamos los archivos adjuntos que vienen con el post
        $icount = contarCoincidencias($user_data,"tmp_img");
        for($f=0;$f<$icount;$f++):
            $t=$f>0 ? $f : '';
            if (isset($user_data["no_tmp_img".$t]) Or !empty($user_data["no_tmp_img".$t])):
                $err_img = $err_img . uploadImg($user_data, ENDO_DIR_ADJ, $valName, $colName,$t);
            endif;
            //completamos las columnas extras para la transaccion
            if (!empty($valName)) {
                $colExt .= "," . $colName . "";
                $colExt .= $nomTabla . "='" . $valName . "'";
            }
        endfor;

        $this->query = " UPDATE " . $user_data['no_esq_tabla'] . "
                            SET " . substr($tblCol, 0, (strlen($tblCol) - 1)) . $colExt . "
                          WHERE " . 'cod_' . $nomTabla . "=" . $user_data['cod_' . $nomTabla] . "";

        //print $this->query;exit();
        $this->execute_single_query();

        return true;
    }

    #trae la siguiente secuencia de la tabla

    public function setSigSecuencia($nomTbl) {
        $this->query = "SELECT IF(MAX(cod_" . str_replace('sys_','',str_replace('endodoncia_', '', $nomTbl)) . " IS NOT NULL),MAX(cod_" . str_replace('sys_','',str_replace('endodoncia_', '', $nomTbl)) . " + 1),1) as codSec
                       FROM " . $nomTbl . " ";
        //print $this->query;exit;
        $this->get_results_from_query();
        if (count($this->rows) >= 1) {
            return $numSec = strval($this->rows[0]['codSec']);
        }
    }

    public function get_datos($clave, $condicion="", $table='dual', $aux = null) {
        #traemos los datos segun la referencia de la entrada de la funcion
        $this->rows = "";
        $this->query = "";
        $this->query = " SELECT $clave FROM $table ";
        !empty($condicion) ? $this->query .= "WHERE $condicion" : "";
        //print $this->query;exit();
        $this->get_results_from_query();
        unset($this->_data);
        if (count($this->rows) >= 1) {
            foreach ($this->rows as $pro => $va) {
                $this->_data[] = $va;
            }
        }
    }
    
    # Método destructor del objeto

    function __destruct() {
        unset($this);
    }

    public function consultaDienteHistoriaDatos( $aData=null ) {
        $_datosResultado = array();

        $_datosResultado['retratamiento']       = self::traerRetratamiento( $aData );
        $_datosResultado['codConfigDental']     = self::codConfigDentalPorCodImagen( $aData );
        $_datosResultado['panelDiagnostico']    = self::traerPanelDiagnostico( $aData );
        $_datosResultado['desobturacion']       = self::traerDesobturacion( $aData );
        $_datosResultado['imagenesHistoria']    = self::traerImagenesHistoriaClinica( $aData );
        $_datosResultado['evoluciones']         = self::traerEvoluciones( $aData );
        $_datosResultado['evolucionesDiente']   = self::traerEvolucionesDiente( $aData );
        $_datosResultado['imagenesDiente']      = self::traerImagenesDiente( $aData );

        return $_datosResultado;

    }

    public function traerRetratamiento($aData=null) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT COUNT(1) as retratamiento
                        FROM endodoncia_historia_clinica
                       WHERE cod_paciente={$aData['cod_paciente']}
                         AND cod_config_dental = (SELECT cod_config_dental
                                                    FROM endodoncia_config_dental
                                                   WHERE cod_imagen_dental={$aData['cod_config_dental']})";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;
    }

    public function codConfigDentalPorCodImagen($aData=null) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT IF(cod_config_dental is null,0,cod_config_dental) as cod_config_dental, nom_config_dental, des_config_dental, ind_temporales
                                   FROM endodoncia_config_dental
		                          WHERE cod_imagen_dental={$aData['cod_config_dental']}";

        $this->get_results_from_query();

        if ($this->rows)
            $result = $this->rows[0];

        return $result;

    }

    public function traerPanelDiagnostico($aData=null) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaTablaDiagnostico({$aData['cod_config_dental']},{$aData['case']},{$aData['cod_historia_clinica']}) as panel FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;

    }

    public function traerDesobturacion($aData=null) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaTablaDesobturacion({$aData['cod_config_dental']},{$aData['case']},{$aData['cod_historia_clinica']}) as desobturacion FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;
    }

    public function traerImagenesHistoriaClinica($aData=null) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaImagenesHistoriaClinica({$aData['cod_historia_clinica']}) as imagenes FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;
    }

    public function traerEvoluciones($aData=null){

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaEvolucionesPaciente({$aData['cod_historia_clinica']},{$aData['cod_paciente']}) as evoluciones FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;
    }

    public function traerEvolucionesDiente($aData) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaEvolucioneDientePaciente({$aData['cod_config_dental']},{$aData['cod_paciente']}) as evoluciones_diente FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;
    }

    public function traerImagenesDiente($aData) {

        $result         = null;
        $this->query    = "";
        $this->rows     = "";

        $this->query="SELECT fbArmaImagenesHistoriaClinicaDental({$aData['cod_config_dental']},{$aData['cod_paciente']}) as imagenes_diente FROM DUAL";

        $this->get_result_from_query();

        if ($this->row)
            $result = $this->row;

        return $result;

    }


    public function HistoriClinicaPDFData($aData=array(), $auxImagen='') {
        $data           = array();
        $argumentos     = array();
        $argumentos[0]  = $aData['cod_historiaclinica'];
        $argumentos[1]  = isset($aData['type'])?$aData['type']:0;

        if($auxImagen){
            $auxImagen = implode(',', $auxImagen);
            $auxImagen = " and t1.cod_registro_imagenes in ({$auxImagen})";
        }

        $this->get_datos("t1.*,CONCAT('Numero: ',t2.num_config_dental,' ',t2.nom_config_dental,' ',t2.des_config_dental) as diente,t3.nom_respuestas as sensibilidad, concat(if(ind_retratamiento=1,'SI','NO')) as retratamiento", "t1.cod_historia_clinica={$argumentos[0]}", " endodoncia_historia_clinica as t1 join endodoncia_config_dental as t2 on(t1.cod_config_dental=t2.cod_config_dental) left join sys_respuestas as t3 on(t1.cod_analisis_sensibilidad=t3.cod_respuestas)");
        $data["historia_clinica"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos("*", "cod_empresa={$this->_empresa}", 'sys_empresa');
        $data["empresa"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos("t3.email_usuario as email_odontologo,t1.*,CONCAT(t1.ape1_paciente,if(t1.ape2_paciente is not null,concat(' ',t1.ape2_paciente),''),' ',t1.nom1_paciente,if(t1.nom2_paciente is not null,concat(' ',t1.nom2_paciente),'')) as nombre,((YEAR(CURDATE()) - YEAR(t1.fec_nacimiento_paciente)) + IF((DATE_FORMAT(CURDATE(), '%m-%d') > DATE_FORMAT(t1.fec_nacimiento_paciente,'%m-%d')),0,-(1))) as edad,t2.nom_genero,CONCAT(IF(t1.ind_embarazada=1,'Si','NO')) AS embarazada","cod_paciente={$data['historia_clinica']['cod_paciente']} and t1.cod_genero=t2.cod_genero"," endodoncia_paciente as t1 left join sys_usuario as t3 on(t1.cod_medico = t3.cod_usuario), sys_genero as t2");
        $data["paciente"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos("t2.nom_config_antecedentes","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_antecedentes_familiares=t2.cod_config_antecedentes"," endodoncia_historia_clinica_antecedentes as t1, endodoncia_config_antecedentes as t2");
        $data["antecedentes_medicos_familiares"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_antecedentes","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_antecedentes_personales=t2.cod_config_antecedentes"," endodoncia_historia_clinica_antecedentes as t1, endodoncia_config_antecedentes as t2");
        $data["antecedentes_medicos_personales"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_antecedentes","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_antecedentes_odontologicos=t2.cod_config_antecedentes"," endodoncia_historia_clinica_antecedentes as t1, endodoncia_config_antecedentes as t2");
        $data["antecedentes_medicos_odontologicos"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_alergias","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_alergias=t2.cod_config_alergias"," endodoncia_historia_clinica_alergias as t1, endodoncia_config_alergias as t2");
        $data["alergias"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_medicamentos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_medicamentos=t2.cod_config_medicamentos"," endodoncia_historia_clinica_medicamentos as t1, endodoncia_config_medicamentos as t2");
        $data["medicamentos"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_tejidos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tejidos_blandos=t2.cod_config_tejidos"," endodoncia_historia_clinica_tejidos as t1, endodoncia_config_tejidos as t2");
        $data["tejidos_blandos"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_tejidos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tejidos_dental=t2.cod_config_tejidos"," endodoncia_historia_clinica_tejidos as t1, endodoncia_config_tejidos as t2");
        $data["tejidos_dental"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_tejidos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tejidos_periodontal=t2.cod_config_tejidos"," endodoncia_historia_clinica_tejidos as t1, endodoncia_config_tejidos as t2");
        $data["tejidos_periodontal"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_tejidos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tejidos_perirradicular=t2.cod_config_tejidos"," endodoncia_historia_clinica_tejidos as t1, endodoncia_config_tejidos as t2");
        $data["tejidos_perirradicular"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("t2.nom_config_tejidos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tejidos_pulpar=t2.cod_config_tejidos"," endodoncia_historia_clinica_tejidos as t1, endodoncia_config_tejidos as t2");
        $data["tejidos_pulpar"] = isset( $this->_data)?substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("concat(t2.nom_config_diagnosticos,' - ',t2.des_config_diagnosticos) as des_config_diagnosticos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_diagnosticos=t2.cod_config_diagnosticos"," endodoncia_historia_clinica_diagnostico as t1, endodoncia_config_diagnosticos as t2");
        $data["diagnostico"] =isset( $this->_data)? substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        $this->get_datos("*","cod_historia_clinica={$argumentos[0]}"," endodoncia_historia_clinica_informacion_conductos ");
        $data["conductometria"] = !empty($this->_data[0]) ? $this->_data[0] : array();

        $this->get_datos("t1.*,t2.*","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_tipo_imagen= t2.cod_config_tipo_imagen {$auxImagen}"," endodoncia_registro_imagenes  as t1, endodoncia_config_tipo_imagen as t2");
        $data["imagenes"] = !empty($this->_data) ? $this->_data : array();

        $this->get_datos("concat('Fecha-Registro: ',date(fec_paciente_evolucion),' hora-entrada: ',Hora_Entrada_paciente_evolucion,' Hora-Salida: ',hora_salida_paciente_evolucion,' Evolucion: ',des_paciente_evolucion) as evolucion,cod_empresa","cod_historia_clinica={$argumentos[0]}", "endodoncia_paciente_evolucion");
        $data["evoluciones"] = !empty($this->_data) ? $this->_data : array();

        $this->get_datos('*', "cod_estado='AAA' AND cod_empresa={$this->_empresa}", ' endodoncia_config');
        !empty($this->_data)?$data[1]=$this->_data:$data[1]=array();

        return $data;
    }

    public function ConsentimientoPDFData($aData=array(), $auxImagen='') {
        $data           = array();
        $argumentos     = array();
        $argumentos[0]  = $aData['cod_consentimiento'];

        if($auxImagen){
            $auxImagen = implode(',', $auxImagen);
            $auxImagen = " and t1.cod_registro_imagenes in ({$auxImagen})";
        }

        $this->get_datos("t1.*,CONCAT(t2.ape1_paciente,if(t2.ape2_paciente is not null,concat(t2.ape2_paciente),''),' ',t2.nom1_paciente,if(t2.nom2_paciente is not null,concat(' ',t2.nom2_paciente),'')) as nom_paciente,t2.ced_paciente as ced_paciente, t3.des_config_consentimiento as des_consentimiento, t4.nom_ciudad as nom_ciudad", "t1.cod_paciente_consentimiento={$argumentos[0]} and t1.cod_paciente=t2.cod_paciente and t1.cod_config_consentimiento=t3.cod_config_consentimiento", " endodoncia_paciente_consentimiento as t1, endodoncia_paciente as t2 left join sys_ciudad as t4 on(t2.cod_ciudad=t4.cod_ciudad), endodoncia_config_consentimiento as t3");
        $data["consentimiento"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos("*", "cod_empresa={$this->_empresa}", 'sys_empresa');
        $data["empresa"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos("t2.des_config_diagnosticos","t1.cod_historia_clinica={$argumentos[0]} and t1.cod_config_diagnosticos=t2.cod_config_diagnosticos"," endodoncia_historia_clinica_diagnostico as t1, endodoncia_config_diagnosticos as t2");
        $data["diagnostico"] =isset( $this->_data)? substr(armaTextAnidado($this->_data,1), 0, (strlen(armaTextAnidado($this->_data,1)) - 1)):'';

        return $data;
    }

    public function ComprobanteIngresoPDFData($aData=array()){
        $data           = array();
        $argumentos     = array();
        $argumentos[0]  = $aData['cod_pago'];
        $argumentos[1]  = isset($aData['type'])?$aData['type']:0;

        $this->get_datos("t1.*,CONCAT(t4.ape1_paciente,if(t4.ape2_paciente is not null,concat(t4.ape2_paciente),''),' ',t4.nom1_paciente,if(t4.nom2_paciente is not null,concat(' ',t4.nom2_paciente),'')) AS nom_paciente,t4.ced_paciente,t4.dir_paciente,t4.email_paciente,group_concat(' ',t2.con_pago_detalle, ' valor: ',t2.imp_pago_detalle) AS tratamiento,t6.nom_ciudad", " t1.cod_pago=" . $argumentos[0]." and t1.cod_pago=t2.cod_pago and t1.cod_paciente = t4.cod_paciente group by t1.cod_pago", "endodoncia_pago as t1, endodoncia_pago_detalle as t2, endodoncia_paciente as t4 left join sys_ciudad as t6 on (t4.cod_ciudad=t6.cod_ciudad)");
        $data["informacion_comprobante"] = isset($this->_data)?$this->_data[0]:array();

        $this->get_datos("*", "cod_empresa={$this->_empresa}", 'sys_empresa');
        $data["empresa"] = isset( $this->_data)?$this->_data[0]:array();

        $this->get_datos('*', "cod_estado='AAA' AND cod_empresa={$this->_empresa}", ' endodoncia_config');
        !empty($this->_data)?$data[1]=$this->_data:$data[1]=array();

        return $data;
    }

    public function getNumComprobantes($cod_empresa=null){

        $cod_empresa    = $cod_empresa?$cod_empresa:$this->_empresa;
        $this->row      = "";
        $this->query    = "";
        $result         = array();

        $this->query = "SELECT CONCAT(pre_sig_comp_ingreso,(num_sig_comp_ingreso)) as numComprobanteIngreso,CONCAT(pre_sig_comp_egreso,(num_sig_comp_egreso)) as numComprobanteEgreso
                          FROM fa_config
                         WHERE cod_estado='AAA'
                           AND cod_empresa={$cod_empresa}";

        $this->get_result_from_query();

        $result = array(
            'numComprobanteIngreso' => $this->row['numComprobanteIngreso'],
            'numComprobanteEgreso'  => $this->row['numComprobanteEgreso']
        );

        return $result;
    }

    public function getDatosIngresoHistoriaClinica($aData=array()){
        $this->rows ="";
        $this->query="";
        $result     = array();
        $aux        = '';

        if(isset($aData['cod_historia_clinica']))
            $aux = " and t1.cod_historia_clinica={$aData['cod_historia_clinica']}";

        $this->query="SELECT t1.cod_historia_clinica, concat(t2.nom_config_dental,' - ', t2.des_config_dental) as value
                        FROM endodoncia_historia_clinica as t1, endodoncia_config_dental as t2
                       WHERE t1.cod_config_dental=t2.cod_config_dental
                         AND t1.cod_paciente={$aData['cod_paciente']} {$aux}";

        if(!isset($aData['cod_historia_clinica']))
            $this->query .= ' and t1.imp_adeu_historia_clinica>0 ';

        $this->get_results_from_query();

        if(count($this->query) >= 1){
            foreach ($this->rows as $pro=>$va) {
                $result['datosHistoriaClinicaCliente']['tratamientos'][$pro]=$va;
            }
        }

        $this->rows ="";
        $this->query="";
        $this->query="SELECT round(sum(t1.imp_adeu_historia_clinica),2) as value
                        FROM endodoncia_historia_clinica as t1
                       WHERE t1.cod_paciente={$aData['cod_paciente']} {$aux}";

        $this->get_results_from_query();

        if(count($this->query) >= 1){
            foreach ($this->rows as $pro=>$va) {
                $result['datosHistoriaClinicaCliente']["deuda"]=$va;
            }
        }

        $this->rows ="";
        $this->query="";
        $this->query="SELECT ROUND(t1.imp_total_historia_clinica,2) AS imp_total_historia_clinica, ROUND(t1.imp_canc_historia_clinica,2) AS imp_canc_historia_clinica, ROUND(t1.imp_adeu_historia_clinica,2) AS imp_adeu_historia_clinica
                        FROM endodoncia_historia_clinica as t1
                       WHERE t1.cod_historia_clinica={$aData['cod_historia_clinica']}";
        $this->get_results_from_query();
        if(count($this->query) >= 1){
            foreach ($this->rows as $pro=>$va) {
                $result['datosComprobanteFac'][$pro] = $va;
            }
        }

        $this->rows  = "";
        $this->query = "";
        $this->query="SELECT fbArmaHistorialPagos({$aData['cod_historia_clinica']}) as Historial FROM DUAL";
        $this->get_results_from_query();
        if(count($this->query) >= 1){
            foreach ($this->rows as $pro=>$va) {
                $result['datosComprobanteFac']["Historial"]=$va;
            }
        }

        return $result;
    }

    public function tratamientosPaciente($aData=array()) {
        $this->row      = "";
        $this->query    = "";
        $result         = array();

        $this->query="SELECT t1.cod_historia_clinica, concat(t2.nom_config_dental,' - ',t2.num_config_dental) as value
                        FROM endodoncia_historia_clinica as t1, endodoncia_config_dental as t2
                       WHERE t1.cod_config_dental = t2.cod_config_dental
                         AND t1.cod_paciente = {$aData['cod_paciente']}";
        $this->get_results_from_query();
        if(count($this->query) >= 1){
            foreach ($this->rows as $pro=>$va) {
                $result[$pro]=$va;
            }
        }

        return $result;
    }
}

?>
