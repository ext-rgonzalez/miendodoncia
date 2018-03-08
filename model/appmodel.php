<?php

class appModel extends DBAbstractModel {

	public $_data = null;
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para cargar los datos informativos generales de cada usuario en el
//             dashboard de la app si el usuario se loguea exitosamente.
	public function get_config_usuario(){

		$session = Session::get('cod')?Session::get('cod'):0;

		$this->get_datos("fbTraeEmpresa({$session}) as result");
		$this->_data["infoEmpresa"] = $this->_data[0]['result'];
		#traemos contrato, empresa, modulos del usuario
		$this->rows  = "";
		$this->query = "";
		$this->query = " SELECT t1.*,t2.*,t3.*,t4.*,t5.*,concat('modules/sistema/adjuntos/',t2.img_empresa) as LogoEmpresa, t2.nom_empresa as NomEmpresa
                           FROM sys_empresa_contrato as t1,sys_empresa          as t2,
                                sys_contrato         as t3,mod_modulo           as t4,
                                sys_ciudad           as t5,sys_usuario_empresa  as t6
                          WHERE t1.cod_empresa     = t2.cod_empresa
                            AND t1.cod_contrato    = t3.cod_contrato
                            AND t1.cod_modulo      = t4.cod_modulo
                            AND t2.cod_ciudad      = t5.cod_ciudad
                            AND t2.cod_empresa     = t6.cod_empresa
                            AND t1.cod_estado      = 'AAA'
                            AND t6.cod_usuario     = '" . Session::get('cod'). "'";
		$this->get_results_from_query();
		if(count($this->rows) >= 1){
			foreach ($this->rows as $pro=>$va) {
				$this->_data["infoGeneral"][$pro] = $va;
			}
		}
		#traemos los menus y submenus
		$this->rows  = "";
		$this->query = "";
		$this->query = " SELECT CONCAT('<li class=\"sidebar-menu-item\"><a class=\"sidebar-menu-button\" href=\"#\">',
                                       '<i class=\"sidebar-menu-icon material-icons md-18\">build</i>',t1.nom_menu,
                                       '</a><ul class=\"sidebar-submenu sm-condensed\">',
                                fbArmaSubMenu(t1.cod_menu,t3.cod_usuario),'</ul></li>') as menu
                           FROM sys_menu as t1, sys_usuario_menu as t3
                          WHERE t3.cod_menu      = t1.cod_menu
                            AND t3.cod_usuario   = '" . Session::get('cod'). "'
                       ORDER BY t1.cod_indice";
		$this->get_results_from_query();
		if(count($this->rows) >= 1){
			foreach ($this->rows as $pro=>$va) {
				if($va[key($va)]!=null)
					$this->_data["infoMenu"][]= $va[key($va)];
			}
		}else{$this->_menu[0] = array('menu'=>"");}
		#traemos los menusHeader o accesos directos de la app
		$this->rows  = "";
		$this->query = "";
		$this->query = " SELECT fbArmaSubMenuHeader(".Session::get('cod').") as menu_header";
		$this->get_results_from_query();
		if(count($this->rows) >= 1){
			foreach ($this->rows as $pro=>$va) {
				$this->_data["infoMenuHeader"] = $va;
			}
		}else{
			$this->_data["infoMenuHeader"][0] = array('menu_header'=>"");
		}

		$codUser = Session::get('cod');

		$numNoPanel	 = array();
		$this->rows  = "";
		$this->query = "";
		$this->query = " select cod_notificacion, des_notificacion, fecha_notificacion, clase_notificacion, ind_enpanel
		                   from sys_notificacion
		                  where cod_usuario_notificacion is null Or cod_usuario_notificacion={$codUser}
		               order by cod_notificacion desc";
		$this->get_results_from_query();

		if(count($this->rows)>0){

			$numNoPanel 		= array_filter($this->rows, function($item){ return(!$item['ind_enpanel']);});
			$codNotificacion 	= array_map(function($item){ return $item['cod_notificacion'];},$numNoPanel);
			$codNotificacion	= implode(',', $codNotificacion);
			$query = "update sys_notificacion set ind_enpanel=1 where cod_notificacion in({$codNotificacion})";
			$this->set_simple_query($query);
		}

		$this->_data["infoNotificaciones"] 		= $this->rows;
		$this->_data["infoNumNotificaciones"] 	= count($numNoPanel);

	}
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo general para cargar los formularios y su contenido segun los parametros,
//             de la peticion, el siguiente metodo arma todo el formulario y sus componentes(inputs)
//             arma las ayudas y los botones pertenecientes a este formulario, el formulario solo puede
//             ser accedido por los usuarios que poseen los permisos para este objetivo, si no los posee
//             se imprimira el mensaje de error, se maneja bajo funcion almacenada en mysql para dejar la
//             la carga al servidor y optimizar el proceso.
	public function get_form($metodo,$usuario,$form,$data=0,$ciclo=1,$codSubM=null,$cMet=""){
		#traemos el formulario
		$this->rows  = "";
		$this->query = "";
		$this->query = " SELECT fbArmaFormulario($form,$usuario,$data,$ciclo,$codSubM,$this->_modulo,'".$cMet."') as formulario; ";
		$this->get_results_from_query();
		if(count($this->rows) >= 1){
			foreach ($this->rows as $pro=>$va) {
				$this->_formulario[] = $va;
				if($this->_formulario[0]['formulario'] == ''){
					$mensjFrm             = getMenssage('danger','Ocurrio un error','No tiene permisos para acceder a esta opcion. ');
					$this->_formulario[0] = array('formulario'=>$mensjFrm);
				}
			}
		}
		#traemos el formulario modal
		$this->rows  = "";
		$this->query = "";
		$this->query = " SELECT fbArmaFormularioModal($form,$usuario) as modal; ";
		//print $this->query;

		$this->get_results_from_query();
		if(count($this->rows) >= 1){
			foreach ($this->rows as $pro=>$va) {
				$this->_formulario_modal[] = $va;
				if($this->_formulario_modal[0]['modal'] == ''){
					$mensjFrm             = '';
					$this->_formulario_modal[0] = array('modal'=>$mensjFrm);
				}
			}
		}
		#traemos la ayuda del form
		$this->rows = "";
		$this->query = "";
		$this->query = " SELECT fbArmaAyudaFormulario($form,$usuario,1) as formulario_ayuda";
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			foreach ($this->rows as $pro => $va) {
				$this->_formulario_ayuda[] = $va;
				if ($this->_formulario_ayuda[0]['formulario_ayuda'] == '') {
					$mensjFrm                   = getMenssage('info', 'Ocurrio un error', 'No hay ayuda registrada para este proceso,consulte al administrador del sistema. ');
					$this->_formulario_ayuda[0] = array('formulario_ayuda' => $mensjFrm);
				}
			}
		}
		#Traemos los botones por formulario y usuario
		if(!$this->_formulario[0]['formulario']==''){$this->get_boton($metodo,$usuario,$form,$cMet);}
	}
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo general para cargar los tablas y su contenido segun los parametros,
//             de la peticion, el siguiente metodo arma una tabla partiendo de una vista
//             almacenada en mysql. inicialmente carga la cabecera cabecera y columnas dependiendo
//             de lo configurado en la vista y posteriormente carga todo el contenido de la misma vista
//             el metodo tiene la posibilidad de enviar condicion si lo requiere para cargar datos
//             especificos de una tabla, al finalizar la carga, trae la ayuda de la vista y los botones.
	public function get_table($metodo, $usuario, $form, $vista, $condicion=Null,$cMet,$tablaAux=null,$selecionAux=null) {
		$this->rows = "";
		$this->query = "";
		$this->query = " SELECT fbArmaCabTabla('$vista') as tabla";
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			foreach ($this->rows as $pro => $va) {
				$this->_tabla[] = $va;
				if ($this->_tabla[0]['tabla'] == '') {
					$mensjFrm = getMenssage('danger', 'Ocurrio un error', 'No tiene permisos para acceder a esta opcion. ');
					$this->_tabla[0] = array('tabla' => $mensjFrm);
				}
			}
		}
		#traemos los campos
		$this->rows = "";
		$this->query = "";
		$this->query = empty($tablaAux) ? " SELECT * from $vista " : "select $selecionAux from $tablaAux";
		if (!empty($condicion)) {
			$this->query .= " where " . $condicion;
		}
		//print $this->query;exit;
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			foreach ($this->rows as $pro => $va) {
				$this->_cTabla[] = $va;
			}
		}
		#traemos la ayuda del form
		$this->rows = "";
		$this->query = "";
		$this->query = " SELECT fbArmaAyudaFormulario($form,$usuario,2) as formulario_ayuda";
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			foreach ($this->rows as $pro => $va) {
				$this->_formulario_ayuda[] = $va;
				if ($this->_formulario_ayuda[0]['formulario_ayuda'] == '') {
					$mensjFrm = getMenssage('info', 'Ocurrio un error', 'No hay ayuda registrada para este proceso,consulte al administrador del sistema. ');
					$this->_formulario_ayuda[0] = array('formulario_ayuda' => $mensjFrm);
				}
			}
		}
		#Traemos los botones por formulario y usuario
		$this->get_boton($metodo,$usuario,$form,$cMet);
	}
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo general para cargar los botones relacionados a una vista, trae los botones especificos
//             por rol de usuario y permisos configurados para este
	public function get_boton($metodo,$usuario,$form,$cMet="N") {
		#traemos los botones para las acciones del formulario
		$this->rows = "";
		$this->query = "";
		$this->query = " SELECT fbArmaBoton($form,$usuario,'".$cMet."') as boton; ";
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			foreach ($this->rows as $pro => $va) {
				$this->_boton[] = $va;
				if ($this->_boton[0]['boton'] == '') {
					#mensaje de error: warning, danger, success, info
					$mensjBto = '';
					$this->_boton[0] = array('boton' => $mensjBto);
				}
			}
		}
	}
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para traer datos de una consulta dependiendo de los parametros de entrada,
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

	//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para traer datos de una consulta dependiendo de los parametros de entrada,
	public function get_dato($clave, $condicion="", $table='dual', $aux = null) {
		#traemos los datos segun la referencia de la entrada de la funcion
		$this->rows = "";
		$this->query = "";
		$this->query = " SELECT $clave FROM $table ";
		!empty($condicion) ? $this->query .= "WHERE $condicion" : "";
		$this->get_result_from_query();
		unset($this->_data);
		$this->_data = $this->row[$clave];
	}
//Autor:       David G -  Abr 2-2014
//descripcion: metodo para ejecutar sentencias simples sin utilizar el modelo
	public function set_simple_query($query) {
		$this->query = "";
		$this->query = $query;
		//print $this->query;exit;
		$this->execute_single_query();
		if(empty($this->err)):
			$this->err = 6;
			$this->msj = "La transaccion se registro correctamente. ";
		endif;

	}
//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para validar registros existentes antes de realizar un insert en la base de datos,
//             retorna true si el registro existe y restringe el insert, el select es dinamico y se arma
//             dependiendo de los campos y las claves, los campos son los propios de la tabla que se desea
//             consultar y las claves los valores de estos campos, se puede hacer la referencia en el controller
//             de este modulo de como funciona.
	public function getRegistro($tabla, $campo = array(), $clave = array()) {
		$this->rows = "";
		$this->query = "";
		$condicion = "";
		for ($x = 0; $x < count($campo); $x++) {
			$v = $campo[$x] . $clave[$x];
			$condicion = $condicion . $v;
		}
		$this->query = " SELECT *
                           FROM " . $tabla . "
                          WHERE " . $condicion . " ";
		//print $this->query;exit();
		$this->get_results_from_query();
		if (count($this->rows) >= 1) {
			return true;
			exit();
		}
		return false;
	}
//Autor:       David G -  Mar 20-2015
//descripcion: Metodo para ejecutar un procedimiento almacenado y obtener las respuesta del mismo
//en las variables cNumError y cDesError.
	public function getRegistroStoreP($procedimiento = "") {
		$this->row = "";
		$this->query = "";
		$this->query = $procedimiento;
		$this->get_results_from_sp();
		$this->err=$this->row["@cNumError"];
		$this->msj=$this->row["@cDesError"];
	}

    # Método destructor del objeto
    function __destruct() {
        unset($this);
    }

	public function getEmpreseByUser($cod_usuario=null){

		if($cod_usuario==null)
			$cod_usuario=Session::get('cod');

		$this->query = "select cod_empresa
						  from sys_usuario_empresa
						 where cod_usuario=".$cod_usuario;

		$this->get_result_from_query();
		return $this->row['cod_empresa'];
	}
}
?>
