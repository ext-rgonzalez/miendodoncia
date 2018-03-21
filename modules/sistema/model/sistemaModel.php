<?php
class ModeloSistema extends DBAbstractModel {
    private $_modulo=1;
    protected $cod;

//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para inicializar la sesion siempre y cuando el usuario extista,
//             si no existe se imprime nuevamente la vista de sesion y se muestra el mensaje
    public function get_login($data=array()){
        if(array_key_exists('username', $data)){
            $this->query = " SELECT t1.cod_usuario,CONCAT(t1.nom_usuario,' ',t1.ape_usuario) as nom_usuario,t1.email_usuario,t1.tel_usuario,t1.tw_usuario,
                                    t1.fb_usuario,t1.intro_usuario,t1.linkid_usuario,t1.usuario_usuario,
                                    t1.cod_estado,concat('modules/sistema/adjuntos/',t1.img_usuario) as img_usuario,t2.cod_perfil,t2.des_perfil,t1.cod_proveedores
                               FROM sys_usuario as t1, sys_perfil as t2
                              WHERE t1.cod_perfil=t2.cod_perfil
                                AND usuario_usuario  = '" .$data['username']. "'
                                AND password_usuario = '" .md5($data['password']). "'";
            $this->get_results_from_query();
        }
        if(count($this->rows) == 1){
            foreach ($this->rows[0] as $propiedad=>$valor) {
                Session::set(str_replace('_usuario','',$propiedad), $valor);
            }
            if(Session::get('cod_estado') != 'AAA'){
                $this->msj = 'La sesi&oacute;n esta desactivada, consulte al administrador. ';
                $this->err = '1';
                return false;exit();
            }

            if(    Session::get('cod_perfil')=='2'
                && Session::get('cod_perfil')=='6'
                && Session::get('cod_perfil')=='7'
                && Session::get('cod_perfil')=='8'
                && Session::get('cod_perfil')=='9'){
                $this->msj = 'El usuario no tiene permisos para accder, consulte al administrador. ';
                $this->err = '1';
                if(!Session::get('usuario'))
                    redireccionar(array('modulo'=>'sistema','met'=>'cerrar'));

                return false;
            }
            return true;
        }else{
            $this->msj = 'Informaci&oacute;n incorrecta, revise los datos. ';
            $this->err = '0';
            return false;exit();
        }
    }

//Autor:       David G -  Abr 2-2014
//descripcion: Metodo para insertar un registro a una tabla de la base de datos, en insert se arma
//             dinamicamente con los datos recibidos por POST, los datos deben corresponder a los
//             campos de la tabla donde se desea hacer el registro, este metodo tambien, carga la
//             siguiente secuencia de una tabla independientemente si es autoincrementable para no
//             desperdiciar indices, carga tambien automaticamente el usuario que esta realizando
//             la transaccion y los asigna al array del insert, tambien sube imagenes al servidor si asi
//             lo requiere el proceso y asigan valores auxiliares al insert para evitar conflicto de datos,
//             posteriormente hecho el insert en la tabla padre. se pueden manejar transacciones adicionanles
//             para realizar otros cambios, se puede realizar en el swicth luego del insert y se invoca con el nombre
//             de la tabla.
    public function setRegistro($user_data =  array()){
        $this->query = "";
        $tblCol = '';
        $tblVal = ''; $err_img = '';
        $colExt = ''; $colName = '';
        $valExt = ''; $valName = '';
        //traemos la siguiente secuencia de la tabla segun los parametros de entrada
        if(isset($user_data['cod_' . str_replace('sys_','',$user_data['no_esq_tabla'])]) and empty($user_data['cod_' . str_replace('sys_','',$user_data['no_esq_tabla'])])){
            $user_data['cod_' . str_replace('sys_','',$user_data['no_esq_tabla'])] = ModeloSistema::setSigSecuencia($user_data['no_esq_tabla']);
        }
        //Llenamos el usuario de la transaccion si la tabla lo requiere y los asignamos al array de valores para el insert
        if (isset($user_data['cod_usuario']) and empty($user_data['cod_usuario'])){$user_data['cod_usuario'] = Session::get('cod');}
        /*obtengo el nombre de la tabla para futuras validaciones */
        $pos = strpos($user_data['no_esq_tabla'], '_');
        $nomTabla = substr($user_data['no_esq_tabla'],$pos+1,strlen($user_data['no_esq_tabla']));
        foreach($user_data as $col=>$dat){
            if(strpos($col,'no_') === false){
                $tblCol = $tblCol . $col . ',';
                $tblVal = $tblVal . "'" . $dat . "'" . ',';
            }
        }

        //Procesamos los archivos adjuntos que vienen con el post
        $icount = contarCoincidencias($user_data,"tmp_img");
        for($f=0;$f<$icount;$f++):
            $t=$f>0 ? $f : '';
            if (isset($user_data["no_tmp_img".$t]) Or !empty($user_data["no_tmp_img".$t])):
                $err_img = $err_img . uploadImg($user_data, SYS_DIR_ADJ, $valName, $colName,$t);
            endif;
            //completamos las columnas extras para la transaccion
            if (!empty($valName)) {
                $valExt .= ",'" . $valName . "'";
                $colExt .= "," . $colName . "";
                $colExt .= $nomTabla;
            }
        endfor;

        $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "
                                     (". substr($tblCol,0,(strlen($tblCol)-1)) ."" . $colExt .")
                              VALUES (". substr($tblVal,0,(strlen($tblVal)-1)) . "" . $valExt . ")";
        $this->execute_single_query();
        //Swicth para eventos posteriores al insert segund lo requiera cada proceso
        switch ($user_data['no_nom_tabla']){
            //Asignamos la configuracion al usuario
            case 'nuevaUsuario':
                for($i=0;$i<count($user_data['no_cod_menu']);$i++){
                    $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "_menu
                                                 (cod_usuario,cod_menu)
                                          VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_menu'][$i]. "')";
                    $this->execute_single_query();
                }
                for($i=0;$i<count($user_data['no_menu_sub']);$i++){
                    $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "_menu_sub
                                                 (cod_usuario,cod_menu_sub)
                                          VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_menu_sub'][$i]. "')";
                    $this->execute_single_query();
                    $this->query = " call pbAsignaSubMenu(" . $user_data['cod_' . $nomTabla] . "," . $user_data['no_menu_sub'][$i] . ")";
                    $this->execute_single_query();
                }
                for($i=0;$i<count($user_data['no_cod_empresa']);$i++){
                    $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "_empresa
                                                 (cod_usuario,cod_empresa)
                                          VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_empresa'][$i]. "')";
                    $this->execute_single_query();
                }

                $this->query = " INSERT INTO " . $user_data['no_esq_tabla'] . "_perfil
                                             (cod_usuario,cod_perfil)
                                      VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_perfil']. "')";
                $this->execute_single_query();
                $err_img = $err_img . " La configuracion del sistema a sido asinada al usuario: " . $user_data['nom_' . $nomTabla] ;
                break;
            case 'NuevaConfiguracionGeneral':
                if ($user_data['cod_estado'] == 'AAA') {
                    $this->query = " call pbActualizaConfig(2," . $user_data['cod_config'] . ")";
                    $this->execute_single_query();
                    $err_img = $err_img . " La configuracion ha sido definida como predeterminada";
                }
                break;
        }
        $this->msj = "La transaccion se registro correctamente. " . $err_img;
    }

    # Modificar un registro
    public function editRegistro($user_data = array()) {
        $this->query = "";
        $tblCol = '';
        $err_img = '';
        $colExt = '';
        $colName = '';
        $valExt = '';
        $valName = '';

        if (isset($user_data['cod_usuario']) and empty($user_data['cod_usuario'])) {
            $user_data['cod_usuario'] = Session::get('cod');
        }
        /*obtengo el nombre de la tabla para futuras validaciones */
        $pos = strpos($user_data['no_esq_tabla'], '_');
        $nomTabla = substr($user_data['no_esq_tabla'],$pos+1,  strlen($user_data['no_esq_tabla']));
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
                $err_img = $err_img . uploadImg($user_data, SYS_DIR_ADJ, $valName, $colName,$t);
            endif;
            //completamos las columnas extras para la transaccion
            if (!empty($valName)) {
                $colExt .= "," . $colName . "";
                $colExt .= $nomTabla . "='" . $valName . "'";
            }
        endfor;

        //acciones antes de editar el registro
        switch ($user_data['no_nom_tabla']) {
            case 'NuevaPerfilUsuario':
                $this->rows="";$adjunto="";
                if (!$colExt==""):
                    $this->query = "SELECT img_usuario
                                      FROM sys_usuario
                                     WHERE cod_usuario=".$user_data["cod_usuario"];
                    $this->get_results_from_query();
                    if (count($this->rows) >= 1) {
                        unlink(SYS_DIR_ADJ. $this->rows[0]['img_usuario']);
                    }
                endif;
                break;
        }

        $this->query = " UPDATE " . $user_data['no_esq_tabla'] . "
                             SET " . substr($tblCol, 0, (strlen($tblCol) - 1)) . $colExt . "
                           WHERE " . 'cod_'.$nomTabla."=".$user_data['cod_'.$nomTabla]."";

        //print $this->query;exit();
        $this->execute_single_query();
        switch ($user_data['no_nom_tabla']){
            case 'nuevaUsuario':
                $this->query = "DELETE
                                  FROM sys_usuario_menu
                                 WHERE cod_usuario = '" .$user_data['cod_' . $nomTabla] . "'";
                $this->execute_single_query();
                $this->query = "DELETE
                                  FROM sys_usuario_menu_sub
                                 WHERE cod_usuario = '" .$user_data['cod_' . $nomTabla] . "'";
                $this->execute_single_query();
                $this->query = "DELETE
                                  FROM sys_usuario_empresa
                                 WHERE cod_usuario = '" .$user_data['cod_' . $nomTabla] . "'";
                $this->execute_single_query();
                $this->query = "DELETE
                                  FROM sys_usuario_perfil
                                 WHERE cod_usuario = '" .$user_data['cod_' . $nomTabla] . "'";
                $this->execute_single_query();
                for($i=0;$i<count($user_data['no_cod_menu']);$i++){
                    $this->query = " INSERT INTO sys_usuario_menu
                                                 (cod_usuario,cod_menu)
                                          VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_menu'][$i]. "')";
                    $this->execute_single_query();
                }
                for($i=0;$i<count($user_data['no_menu_sub']);$i++){
                    $this->query = " INSERT INTO sys_usuario_menu_sub
                                                 (cod_usuario,cod_menu_sub)
                                          VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_menu_sub'][$i]. "')";
                    $this->execute_single_query();
                    $this->query = " call pbAsignaSubMenu(" . $user_data['cod_' . $nomTabla] . "," . $user_data['no_menu_sub'][$i] . ")";
                    $this->execute_single_query();
                }
                for($i=0;$i<count($user_data['no_cod_empresa']);$i++){
                    $this->query = " INSERT INTO sys_usuario_empresa
                                                     (cod_usuario,cod_empresa)
                                              VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_empresa'][$i]. "')";
                    $this->execute_single_query();
                }
                $this->query = " INSERT INTO sys_usuario_perfil
                                             (cod_usuario,cod_perfil)
                                      VALUES ('" .$user_data['cod_' . $nomTabla] . "','" .$user_data['no_cod_perfil']. "')";
                $this->execute_single_query();

                $err_img = $err_img . " La configuracion del sistema a sido asinada al usuario: " . $user_data['nom_' . $nomTabla] ;
                break;
            case 'NuevaConfiguracionGeneral':
                if ($user_data['cod_estado'] == 'AAA') {
                    $this->query = " call pbActualizaConfig(2," . $user_data['cod_config'] . ")";
                    $this->execute_single_query();
                    $err_img = $err_img . " La configuracion ha sido definida como predeterminada";
                }
                break;
        }
        $this->msj = "La transaccion se edito correctamente. " . $err_img;
    }

    #trae la siguiente secuencia de la tabla
    public function setSigSecuencia($nomTbl){
        $this->rows="";
        $this->query = "SELECT IF(MAX(cod_" .str_replace('sys_','',$nomTbl)." IS NOT NULL),MAX(cod_" .str_replace('sys_','',$nomTbl). " + 1),1) as codSec
                           FROM " .$nomTbl. " ";
        $this->get_results_from_query();
        if(count($this->rows) >= 1){
            return $numSec = strval($this->rows[0]['codSec']);exit();
        }
    }

    # Método destructor del objeto
    function __destruct(){
        unset($this);
    }
}

