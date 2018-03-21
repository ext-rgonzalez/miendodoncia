<?php

class ModeloJsonSistema{

    private $_modeloSistema = null;

    public function __construct(){
        header('Access-Control-Allow-Origin: *');
        $this->_modeloSistema   = new ModeloSistema();
        $this->_modeloGeneral   = new appModel();
    }

    public function infoGraficosDashboard($aData=null){
        $resultBarchart = array();

        $queryBarChart = "t1.cod_config_dental=t2.cod_config_dental
                      and date_format(fec_historia_clinica, '%Y-%m') = date_format(date_sub(now(), interval 1 month), '%Y-%m')
                 group by t1.cod_config_dental
                 order by count(1) desc
                 limit 7";

        $this->_modeloGeneral->get_datos("t2.cod_config_dental,t2.nom_config_dental, count(1) cantidad","{$queryBarChart}","endodoncia_historia_clinica as t1, endodoncia_config_dental as t2");
        $result = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        foreach($result as $key=>$chartDetails)
            $resultBarchart['barChart'][]       = array('y'=>$chartDetails['nom_config_dental'],'c'=>$chartDetails['cantidad']);

        $querydonutChart = "group by year(fec_historia_clinica),month(fec_historia_clinica)
                            order by year(fec_historia_clinica) desc,month(fec_historia_clinica) desc limit 4";

        $this->_modeloGeneral->get_datos("concat(year(fec_historia_clinica),'-',monthname(fec_historia_clinica)) as label, count(1) as value","","endodoncia_historia_clinica {$querydonutChart}");
        $result_1 = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        foreach($result_1 as $key=>$donutchartDetails)
            $resultBarchart['donutChart'][] = array('label'=>$donutchartDetails['label'],'value'=>$donutchartDetails['value']);

        $querydonutChart_1 = "group by year(start_evento_agenda),month(start_evento_agenda)
                              order by year(start_evento_agenda) desc,month(start_evento_agenda) desc limit 4";

        $this->_modeloGeneral->get_datos("concat(year(start_evento_agenda),'-',monthname(start_evento_agenda)) as label, count(1) as value","","sys_agenda {$querydonutChart_1}");
        $result_2 = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:array();

        foreach($result_2 as $key=>$donutchartDetails_1)
            $resultBarchart['donutChart_1'][] = array('label'=>$donutchartDetails_1['label'],'value'=>$donutchartDetails_1['value']);

        return json_encode($resultBarchart);
    }

    public function traerNotificacionesSistema(){

        $seconds                = 2;
        $loop                   = 2;
        $status                 = true;
        
        $codUser = Session::get('cod');

        while($status || $loop==0) {
            sleep($seconds);

            $this->_modeloGeneral->get_datos("cod_notificacion, des_notificacion, fecha_notificacion, clase_notificacion, ind_enpanel","(cod_usuario_notificacion is null Or cod_usuario_notificacion={$codUser}) and ind_enpanel=0 order by cod_notificacion asc","sys_notificacion");

            $config = !empty($this->_modeloGeneral->_data)?$this->_modeloGeneral->_data:null;

            if($config){
                $codNotificacion 	= array_map(function($item){ return $item['cod_notificacion'];},$config);
                $codNotificacion	= implode(',', $codNotificacion);
                $this->_modeloGeneral->set_simple_query("update sys_notificacion set ind_enpanel=1 where cod_notificacion in({$codNotificacion})");
                $status = false;
            }

            if($loop==0)
                $status = false;

            $loop--;
        }

        return json_encode(array('config'=>$config));
    }

    function __destruct(){
        unset($this);
    }
}

