<?php
# funciones de utilidad general

#variable de peiciones al modulo
function formateaPeticion(){
    global $constantes_peticion;
    $uri   = $_SERVER['REQUEST_URI'];
    foreach ($constantes_peticion as $valor) {
        $peticion = str_replace($uri,'',$valor);
        return $peticion;
    }
}
//function para redirigir a un modulo segun parametros
function redireccionar($url=array()){
    $_mod = !empty($url["modulo"])?base64_encode($url["modulo"]):null;
    $_met = !empty($url["met"])?base64_encode($url["met"]):null;
    $_arg = !empty($url["arg"])?base64_encode(implode(',', $url["arg"])):null;

    if(!empty($url))
        header('Location: '.VAR_APP.$_mod.VAR_MET.$_met.VAR_ARG.$_arg);
}
//funcion para harcodear formulario index o principal
function formateaIndex($_Objmodelo=null, $_Objvista=null, $formulario=0, $_dataFormulario=null, $_formulario=null, $version='1.0.0'){
    $session = Session::get('cod')?true:false;

    if(!$session)
        $_formulario='login';

    $_Objmodelo->get_datos($_dataFormulario, 'head_formulario_config="'.$_formulario.'"', 'sys_formulario_config');
    $_Objvista->_vistaDatos = isset($_Objmodelo->_data)?$_Objmodelo->_data[0]:'';

    if($_formulario=='login')
        return;

    $_Objmodelo->get_config_usuario();
    $_Objvista->_asignacion->infoUsuario = $_Objmodelo->_data;
    $_Objvista->_asignacion->infoSession = $_SESSION;
    $_Objmodelo->get_datos('fbDevuelveArchivos('.$formulario.',1) as _CSS');
    $_Objvista->_cssDe  = $_Objmodelo->_data[0]["_CSS"];
    $_Objmodelo->get_datos('fbDevuelveArchivos('.$formulario.',2) as _JS');

    $aDataJs = str_replace('.js">' , '.js?'.$version.'">', $_Objmodelo->_data[0]["_JS"]);
    $_Objvista->_jsDe   = $aDataJs;

}
#funcion para devolver partiendo desde el final de la cadena
function devuelveString($cadena=null,$busqueda=null,$case=0){
    $tamano   = strlen($cadena);
    switch ($case){
        case 1:
            $posicion = strpos($cadena, $busqueda);
            if($posicion==''){$posicion=$tamano;}
            $cadena   = substr($cadena,0,($posicion));
            break;
        case 2:
            $posicion = strpos($cadena, $busqueda);
            $cadena   = substr($cadena,($posicion + 1),($tamano - $posicion));
            break;
    }
    return $cadena;
}

#funcion para verificar la session
function validaSession(){
    if(!Session::get('usuario'))
        redireccionar(array('modulo'=>'sistema','met'=>'login'));

}

#funcion para cerrar la session
function cerrarSession(){
    session_unset();
    session_destroy();
}

#funcion para imprimir mensaje de alert a con variables
function alert_data($data){
    print "<script>alert('" .$data. "')</script>";
}

#funcion para recuperar datos segun accion de uri
function formateaMetodo($metodo=null, $template=null) {

    $metodo = empty($metodo)?'post':$metodo;
    $count=0;$t;
    $user_data = array();
    switch ($metodo){
        case 'login':
            if($_POST) {
                if(array_key_exists('username', $_POST)) {
                    $user_data['username'] = $_POST['username'];
                }
                if(array_key_exists('password', $_POST)) {
                    $user_data['password'] = $_POST['password'];
                }
            }
            break;
        case 'nuevoRegistro':
        case 'post':
            if($_POST) {
                foreach($_POST as $key=>$val){
                    if(strpos($key,'password_') !== false){$val = md5($val);}
                    $user_data[$key] = $val;

                }
                if(isset($_FILES) And !empty($_FILES)){
                    $pos        = strpos($user_data['no_esq_tabla'], '_');
                    $nomTabla   = substr($user_data['no_esq_tabla'],$pos+1,strlen($user_data['no_esq_tabla']));
                    foreach ($_FILES as $k=>$v):
                        $t=$count>0 ? $count : '';
                        if(isset($_FILES['img'.$t.'_' . $nomTabla]['name']) Or isset($_FILES['no_img'.$t.'_' . $nomTabla]['name'])):
                            $user_data["no_nombre_img".$t] = isset($_FILES['img'.$t.'_' . $nomTabla]['name']) ? $_FILES['img'.$t.'_' . $nomTabla]['name'] : $_FILES['no_img'.$t.'_' . $nomTabla]['name'];
                            $user_data["no_tamano_img".$t] = isset($_FILES['img'.$t.'_' . $nomTabla]['size']) ? $_FILES['img'.$t.'_' . $nomTabla]['size'] : $_FILES['no_img'.$t.'_' . $nomTabla]['size'];
                            $user_data["no_tmp_img".$t]    = isset($_FILES['img'.$t.'_' . $nomTabla]['tmp_name']) ? $_FILES['img'.$t.'_' . $nomTabla]['tmp_name'] : $_FILES['no_img'.$t.'_' . $nomTabla]['tmp_name'];
                        else:
                            for($i=0;$i<count($_FILES[$k]["name"]);$i++):
                                $user_data["no_imagen_base"]["nombre"][]=$_FILES[$k]["name"][$i];
                                $user_data["no_imagen_base"]["size"][]=$_FILES[$k]["size"][$i];
                                $user_data["no_imagen_base"]["tmp"][]=$_FILES[$k]["tmp_name"][$i];
                            endfor;
                        endif;
                        $count++;
                    endforeach;


                }
            }
            if($_GET){
                foreach($_GET as $key=>$val):
                    if(strpos($key,'password_') !== false)
                        $val = md5($val);

                    if(!empty($val))
                        $user_data[$key] = $val;
                endforeach;
            }
            break;

    }
    return $user_data;
}

function getImagesByTemplate($template=null){
    $arrayImg   = array();

    if(isset($_FILES[$template]) && !empty($_FILES[$template])){
        $arrayImg['name'] = is_array($_FILES[$template]['name'])?$_FILES[$template]['name']:array($_FILES[$template]['name']);
        $arrayImg['temp'] = is_array($_FILES[$template]['tmp_name'])?$_FILES[$template]['tmp_name']:array($_FILES[$template]['tmp_name']);
    }

    return $arrayImg;
}

#funcion para subir imagenes al servidor y validar su formato
function uploadImg($data_img=array(),$path, &$tmpName, &$colName,&$count){
    $valid_formats = array("JPEG", "JPG", "PNG", "JPEG", "BMP","WBMP","TXT","DOC","DOCX","XLS","PDF","SQL");
    if(!empty( $data_img["no_nombre_img".$count])){
        list($nomImg, $extImg) = explode(".", $data_img["no_nombre_img".$count]);

        $pos = strpos($data_img['no_esq_tabla'], '_');
        $nomTabla = substr($data_img['no_esq_tabla'],$pos+1,strlen($data_img['no_esq_tabla']));
        if(isset($data_img["nom_" . $nomTabla])){
            $tmpName = str_replace(" ","_",$data_img["nom_" . $nomTabla] . $count . date("Y-m-d") . '-' . time() . "." . $extImg);
        }else{
            $tmpName = str_replace(" ","_",'adjunto'. $count . date("Y-m-d") . '-' . time() . "." . $extImg);
        }
        $colName = "img".$count."_";
        move_uploaded_file($data_img["no_tmp_img".$count], $path . $tmpName);
        $err_img = "El archivo se asocio correctamente. ";

        return $err_img;
    }
}

#funcion para subir archivos al servidor no obligatorios
function uploadImg_1($data_img=array(),$path, &$tmpName="",$nombre="",$ciclo=0){
    if(!empty($data_img)){
        $valid_formats = array("JPEG", "JPG", "PNG", "JPEG", "BIP","TXT","DOC","DOCX","XLS","PDF","SQL");
        list($nomImg, $extImg) = explode(".", $data_img["nombre"][$ciclo]);
        if (in_array(strtoupper($extImg), $valid_formats)) {
            $tmpName = str_replace(' ', '', trim($nombre)) . $ciclo . date("Y-m-d") . '-' . time() . "." . $extImg;
            move_uploaded_file($data_img["tmp"][$ciclo], $path . $tmpName);
        }
    }
}

#funcion para subir archivos al servidor y retornar arry con el path
function uploadImges($data_img=array(),$path, $pathUser=null){

    $dataReturn = array();

    if($pathUser) {
        if (!is_dir($path.$pathUser)) {
            mkdir($path.$pathUser, 0777, true);
        }

        $path = $path.$pathUser;
    }

    foreach ($data_img['temp'] as $item=>$tmpimage) {

        list($nomImg, $extImg) = !is_array($tmpimage)?explode(".", $data_img["name"][$item]):explode(".", $data_img["name"][$item][0]);

        $valid_formats = array("JPEG", "JPG", "PNG", "JPEG", "BIP","TXT","DOC","DOCX","XLS","PDF","SQL","CSV","BMP");
        if (in_array(strtoupper($extImg), $valid_formats)) {
            $tmpName = $nomImg.'-'.date("Y-m-d").'-'.time().".".$extImg;
            if(!is_array($tmpimage))
                $result = move_uploaded_file($tmpimage, $path . $tmpName);
            else
                $result = move_uploaded_file($tmpimage[0], $path . $tmpName);
        }

        if(!is_array($tmpimage))
            $dataReturn[] = $tmpName;
        else
            $dataReturn[$item] = $tmpName;
    }

    return $dataReturn;

}

#funcion para imprimir mensajes de error de acceso a los modulos
function getMenssage($tipo, $titulo, $descripcion){
    $mensage = '<div class="alert alert-'.$tipo.'">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>'.$titulo.'</strong> '.$descripcion.'.
            </div>';
    return $mensage;
}

#cargamos las variables necesarias para la vista
function setVariables($controller,$Objvista,$metodo,$form,$tipoView,$vista = Null,$condicion = Null,$camposCombo=array(),$camposChek=array(),$ciclo=1,$camposComboEsp=array(),$codSubMenu=null,$met="N",$tablaAux=null,$selecionAux=null){
    $controller->get_config_usuario(Session::get('cod')) ;
    $Objvista->_empresa          = $controller->_empresa;
    $Objvista->_notificacion     = array('des_notificacion'=>$controller->_notificacion[0]['des_notificacion']);
    $Objvista->_tarea            = array('des_tarea'=>$controller->_tarea[0]['des_tarea']);
    /*$var = "";
    for($t=0;$t<count($controller->_mensajes);$t++){$var .= $controller->_mensajes[$t]['des_mensajes'];}
    $Objvista->_mensajes         = array ('des_mensajes'=>$var);
    $Objvista->_numMensajes      = $controller->_numMensajes;*/
    $var = "";
    for($t=0;$t<count($controller->_menu);$t++){$var .= $controller->_menu[$t]['menu'];}
    $Objvista->_menu             = array ('menu'=>$var);
    $Objvista->_menuHeader       = $controller->_menuHeader;
    $Objvista->_menuShorcut      = $controller->_menuShorcut;
    #traemos el formulario de usuario.
    switch($tipoView){
        case 1:
            $controller->get_form($metodo,Session::get('cod'),$form,0,$ciclo,$codSubMenu,$met);
            break;
        case 2:
            $controller->get_table($metodo,Session::get('cod'),$form,$vista,$condicion,$met,$tablaAux,$selecionAux);
            break;
        case 3:
            $controller->get_form($metodo,Session::get('cod'),$form,1,$ciclo,$codSubMenu,$met);
            break;
    }
    #traemos los archivos relacionados al formulario
    $controller->get_datos('fbDevuelveArchivos('.$form.',1) as ARCHIVOSCSS');
    $Objvista->_archivos_css = $controller->_data;
    $controller->get_datos('fbDevuelveArchivos('.$form.',2) as ARCHIVOSSCRIPT');
    $Objvista->_archivos_js  = $controller->_data;
    #armamos la tabla con los datos de las propiedades del sitema
    $var = "";
    $_rowFinal  = '';
    $_rowFinal1 = '';
    $_rowFinal2 = '';
    $_colFinal  = '';
    $_tabla     = '';
    $_clase     = "";
    //var_dump($controller->_cTabla);exit;
    for($t=0;$t<count($controller->_cTabla);$t++){
        isset($controller->_cTabla[$t]["clase_estado"]) ? $_clase=$controller->_cTabla[$t]["clase_estado"] : $_clase="";
        foreach($controller->_cTabla[$t] as $p=>$v){
            $_row = '<td style="font-size:8pt;" id="'.$p.'">' . $v . '</td>';
            $_rowFinal .=  $_row;
        }
        $_rowFinal1 .= '<tr class="'. $_clase .'">' . $_rowFinal . '</tr>';
        $_rowFinal = '';
    }

    for($t=0;$t<count($controller->_tabla);$t++){
        $_tabla = $controller->_tabla[$t]['tabla'] . $_rowFinal1;
    }
    $_formulario="";
    //var_dump($camposCombo);exit;
    if(!empty($camposCombo)){
        foreach ($camposCombo as $k=>$va){
            if(is_array($va)){
                foreach($va as $k1=>$va1){
                    if(is_array($va1)){
                        foreach($va1 as $k2=>$va2){
                            if(is_array($va2)){
                                foreach($va2 as $k3=>$va3){
                                    $campo3=key($va2);
                                    $_formulario = str_replace('<option data-selected="'.$campo3.'" value="'.$va3.'"', '<option data-selected="'.$campo3.'" selected value="'.$va3.'"', $controller->_formulario[0]["formulario"]);
                                    $controller->_formulario[0]["formulario"] = $_formulario;
                                    next($va2);
                                }
                            }else{
                                $campo2=key($va1);
                                $_formulario = str_replace('<option data-selected="'.$campo2.'" value="'.$va2.'"', '<option data-selected="'.$campo2.'" selected value="'.$va2.'"', $controller->_formulario[0]["formulario"]);
                                $controller->_formulario[0]["formulario"] = $_formulario;
                                next($va1);
                            }
                        }
                    }else{
                        $campo1=key($va);
                        $_formulario = str_replace('<option data-selected="'.$campo1.'" value="'.$va1.'"', '<option data-selected="'.$campo1.'"  selected value="'.$va1.'"', $controller->_formulario[0]["formulario"]);
                        $controller->_formulario[0]["formulario"] = $_formulario;
                        next($va);
                    }
                }
            }else{
                $campo=key($camposCombo);
                $_formulario = str_replace('<option data-selected="'.$campo.'" value="'.$va.'"', '<option data-selected="'.$campo.'" selected  value="'.$va.'"', $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formulario;
                next($camposCombo);
            }
        }
    }
    //var_dump($camposChek);exit();
    if(!empty($camposChek)){
        foreach ($camposChek as $k=>$va){
            $campo = '"'.$va.'"';
            $_formulario = str_replace("name=$campo", "name=$campo checked=checked", $controller->_formulario[0]["formulario"]);
            $controller->_formulario[0]["formulario"] = $_formulario;
        }
    }
    if(!empty($camposComboEsp)){
        $u=0;
        foreach($camposComboEsp as $k =>$v){
            foreach($v as $k1=>$v1){
                $_formulario = str_replace('<option data-array='.$u.' data-selected="'.$k1.'" value="'.$v1.'"', '<option data-array='.$u.' data-selected="'.$k.'" selected value="'.$v1.'"', $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formulario;
                $_formularioTxt = str_replace('name="no_'.$k1.'[]" data-array='.$u.' value="{'.$k1.'}"', 'name="no_'.$k1.'[]" data-array='.$u.' value="'.$v1.'"', $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formularioTxt;
                $_formularioTxtAr = str_replace('name="no_'.$k1.'[]" data-array='.$u.'>{'.$k1.'}', 'name="no_'.$k1.'[]" data-array='.$u.'>'.$v1, $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formularioTxtAr;
                $selected = $v1==1 ? ' checked="checked"' : '';
                $_formularioCheck = str_replace('name="no_'.$k1.'[]" data-array='.$u.' value="1"', 'name="no_'.$k1.'[]" data-array='.$u.' value="1" ' . $selected, $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formularioCheck;
                $_formularioDiv = str_replace('" data-ref="'.$k1.'" data="'.$v1.'" id="btoAccion"', ' active1" data-ref="'.$k1.'"  data="'.$v1.'" id="btoAccion"', $controller->_formulario[0]["formulario"]);
                $controller->_formulario[0]["formulario"] = $_formularioDiv;
            }$u=$u+1;
        }
    }
    $Objvista->_tabla                = array('tabla'=>$_tabla);
    $Objvista->_formulario           = $controller->_formulario;
    $Objvista->_formulario_ayuda     = $controller->_formulario_ayuda;
    $Objvista->_formulario_modal     = $controller->_formulario_modal;
    $Objvista->_boton                = $controller->_boton;
}

//funcion para devolver la configuracion del formulario segun parametros
function fbRetornaConfigForm(){
    return "title_formulario_config as TITLE,fbBase64_encode(nom_form_formulario_config) as NOM_FORM,
                    fbBase64_encode(controller_formulario_config) as FORM_CONTROLLER,view_form_formulario_config as VIEW_FORM,
                    fbBase64_encode(metodo_formulario_config) as FORM_MET,fbBase64_encode(arg_formulario_config) as FORM_ARG,
                    fbBase64_encode(CONCAT(num_form_formulario_config,',',met_new_formulario_config)) as CONFIG_FORM_NEW,
                    fbBase64_encode(CONCAT(num_form_formulario_config,',',met_edti_formulario_config)) as CONFIG_FORM_EDIT,
                    fbBase64_encode(CONCAT(tipview_formulario_config,',',num_form_ant_formulario_config,',',view_form_ant_formulario_config)) as CONFIG_FORM_BACK,
                    FBBASE64_ENCODE(CONCAT(num_form_formulario_config,',',met_table_formulario_config)) AS CONFIG_TABLE,
                    fbBase64_encode(form_ant_formulario_config) as NOM_FORM_ANT, num_form_ant_formulario_config as NUM_FORM_ANT,
                    view_form_ant_formulario_config as VIEW_FORM_ANT,form_formulario_config as FORM,
                    met_edti_formulario_config as MET_EDIT,met_new_formulario_config as MET_NEW,
                    target_formulario_config as TARGET";
}
//Funcion para formatear el array post y obtener los campos para la transaccion en el base de datos
function fbFormateaPost($user_data=array(),&$tblCol=null,&$tblVal=null){
    foreach ($user_data as $col => $dat):
        if (strpos(substr($col, 0, 3), 'no_') === false And strpos(substr($col, 0, 4), 'noo_') === false) :
            $tblCol = $tblCol . $col . ',';
            $tblVal = $tblVal . "'" . $dat . "'" . ',';
        endif;
    endforeach;
}
//Funcion para formatear el array post y obtener los campos para la transaccion en el base de datos
function fbFormateaNoPost($user_data=array(),&$tblCol=null,&$tblVal=null){
    foreach ($user_data as $col => $dat):
        if (substr($col, 0, 4)== 'noo_') :
            $tblCol = $tblCol . str_replace('noo_', '', $col). ',';
            $tblVal = $tblVal . "'" . $dat . "'" . ',';
        endif;
    endforeach;
}
//Funcion para enviar emails segun el case
function  sendEmail($modulo,$template="",$data=array(),$view=null,$dataIn=array(),$ruta=null,$aux=null,$adj="",&$_return=null){

    if($ruta==null){
        require_once ROOT . 'libs/PHPMailer-master/PHPMailerAutoload.php';
        require_once ROOT . 'libs/PHPMailer-master/class.smtp.php';
    }else{
        require_once $ruta . 'libs/PHPMailer-master/PHPMailerAutoload.php';
        require_once $ruta . 'libs/PHPMailer-master/class.smtp.php';
    }
    $mail = new PHPMailer(true);
    $index = isset($dataIn[1]) and is_array($dataIn[1]) ? 1 : 0;
    $mail->Username   = $index==1 ? $dataIn[$index][0]["email_envio_config"]    : $dataIn[$index]["email_envio_config"];
    $mail->Password   = $index==1 ? $dataIn[$index][0]["pass_envio_config"]     : $dataIn[$index]["pass_envio_config"];
    $mail->From       = $index==1 ? $dataIn[$index][0]["email_envio_config"]    : $dataIn[$index]["email_envio_config"];
    $mail->FromName   = $index==1 ? $dataIn[$index][0]["from_config"]           : $dataIn[$index]["from_config"];
    $mail->Host       = $index==1 ? $dataIn[$index][0]["host_envio_config"]     : $dataIn[$index]["host_envio_config"];
    $mail->Port       = $index==1 ? $dataIn[$index][0]["port_envio_config"]     : $dataIn[$index]["port_envio_config"];
    $mail->SMTPSecure = $index==1 ? $dataIn[$index][0]["tipo_sec_envio_config"] : $dataIn[$index]["tipo_sec_envio_config"];
    $mail->Subject    = $index==1 ? str_replace('{NRO_TIQUET}',$aux,$dataIn[$index][0]["asunto_config"]) : str_replace('{NRO_TIQUET}',$aux,$dataIn[$index]["asunto_config"]);
    switch ($template){
        //email para notificar asignacion de tiquet
        case 1:
            $html = $view->get_template($modulo,'forms/view_email',"");
            $html = $view->render_dinamic_data($modulo, $html, $data);
            try {
                $mail->IsSMTP();
                $mail->Mailer = 'smtp';
                $mail->SMTPAuth = true;
                $mail->Body = $html;
                $mail->IsHTML(true);
                $mail->AddAddress($data["to"]);
                $mail->Send();
            } catch (phpmailerException $e) {
                echo $e->errorMessage();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
        //email para notificar la recepcion del tiquet
        case 2:
            $html = $view->get_template($modulo,'forms/view_email',1);
            $html = $view->render_dinamic_data($modulo, $html, $data);
            try {
                $mail->IsSMTP();
                $mail->Mailer = 'smtp';
                $mail->SMTPAuth = true;
                $mail->Body = $html;
                $mail->IsHTML(true);
                $mail->AddAddress($data["to"]);
                $mail->Send();
            } catch (phpmailerException $e) {
                echo $e->errorMessage();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            break;
        case 3:
            try {
                $mail->IsSMTP();
                $mail->SMTPAuth = true;
                $mail->IsHTML(true);

                if(is_array($data["to"]))
                    foreach($data["to"] as $key=>$email)
                        $mail->AddAddress($email);
                else
                    $mail->AddAddress($data["to"]);

                $mail->AddAttachment($adj);
                $mail->Body      = 'Notificacion automatica desde MiEndodoncia.com';
                $mail->Send();
                return 1;
            } catch (phpmailerException $e) {
                return 0;
            } catch (Exception $e) {
                return 0;
            }
            break;
    }
}

function recuperaEmailHelpDesk($dataIn=array(),&$data=array()){
    $hostname = $dataIn[0]["host_recepcion_config"];
    $username = $dataIn[0]["email_recepcion_config"];
    $password = $dataIn[0]["pass_recepcion_config"];
    date_default_timezone_set("America/Bogota");
    $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to host: ' . imap_last_error());
    $emails = imap_search($inbox,'SUBJECT '. $dataIn[0]["cad_asunto_config"] );
    if($emails):
        $output = '';$i=0;
        rsort($emails);
        foreach($emails as $email_number):
            $dataInsert=array();$sql='';$insert=false;
            $overview = imap_fetch_overview($inbox,$email_number,0);
            $overview[0]->seen ? $insert=false : $insert=true;
            $dataInsert["email_servicio"]= formatearString($overview[0]->from, '<', '>');
            $dataInsert["nom_servicio"]  = trim(devuelveString($overview[0]->from, '<', 1));
            $dataInsert["cod_empresa"]   = $dataIn[0]["cod_empresa"];
            $dataInsert["cod_estado"]    = 'SAA';
            $dataInsert['fec_servicio']  = date("Y.m.d H:i:s");
            $dataInsert['des_servicio']  = message($inbox, $email_number);
            $dataInsert["cod_prioridad"] = "3";
            if($insert){
                $sql.= "INSERT INTO hd_servicio(cod_servicio,des_servicio,fec_servicio,cod_estado,email_servicio,nom_servicio,cod_empresa,cod_prioridad)";
                $sql.= "                 VALUES({cod_servicio},'".$dataInsert["des_servicio"]."','".$dataInsert["fec_servicio"]."','".$dataInsert["cod_estado"]."',";
                $sql.= "                        '".$dataInsert["email_servicio"]."','".$dataInsert["nom_servicio"]."','".$dataInsert["cod_empresa"]."',";
                $sql.= "                        '".$dataInsert["cod_prioridad"]."')";
            }
            $data[$i]["query"]    =$sql;
            $data[$i]["from"]=$dataInsert["email_servicio"];
            $data[$i]["nombre"]=$dataInsert["nom_servicio"];
            $i++;

        endforeach;
    endif;
    imap_close($inbox);
}
//Funcion para decodificar los mensajes
function decode_qprint($str){
    $str = preg_replace("/\=([A-F][A-F0-9])/","%$1",$str);
    $str = urldecode($str);
    $str = utf8_encode($str);
    return $str;
}
//funcion para leer el mensaje
function message($connection,$number){
    $info = imap_fetchstructure($connection, $number, 0);
    if($info -> encoding == 3){
        $message = base64_decode(imap_fetchbody($connection, $number, 1));
    }elseif($info -> encoding == 4){
        $message = imap_qprint(imap_fetchbody($connection, $number, 1));
    }else{
        $message = imap_fetchbody($connection, $number, 1);
    }
    //$message = imap_fetchbody($this -> connection, $number, 2);
    return decode_qprint($message);
}
//function para obtener cadenas dentro de caracteres o llaves ejemplo: <zeta>, obtendra la palabra zeta
//enviando los contenedores que en este caso con < > y la cadena completa.
function formatearString($cadena='',$busqueda='',$busqueda_1=''){
    $tamano     = strlen($cadena);
    $posicion   = strpos($cadena, $busqueda)+1;
    $posicion_1 = strpos($cadena, $busqueda_1);
    return substr($cadena,$posicion,($posicion_1-$posicion));
}

function RandomString($length=500,$uc=true,$n=true,$sc=true){
    $an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ.-";
    $su = strlen($an) - 1;
    return  substr($an, rand(0, $su), 1) .
    substr($an, rand(0, $su), 1) .
    substr($an, rand(0, $su), 1) .
    substr($an, rand(0, $su), 1) .
    substr($an, rand(0, $su), 1) .
    substr($an, rand(0, $su), 1);
}

//Funcion para imprimir archivos pdf segun parametros
function PrintPdf($data=array(),$dataParametros=array()){
    require_once ROOT . 'libs/class.fpdf.php';
    require_once ROOT . 'libs/class.fpdf.php';
    $pdf = new PDF('L','mm','A3');
    $pdf->AliasNbPages();
    $pdf->AddPage('L','A5');
    $pdf->Cell(30, 20,'');
    $pdf->SetFont('Arial','',8);
    //$pdf->Cell(130,6,'INFORME ACADEMICO Y DISCIPLINARIO '.$data["lectivo"].' '.$data["periodo"],0,0,'C',false);
    $pdf->Ln(10);
    $pdf->Output();
}

function armaTextAnidado($data=array(),$case=0){
    $count=0;
    $cadReturn="";
    if(!empty($data)):
        for($i=0;$i<count($data);$i++):
            $cadArray="";
            foreach($data[$i] as $key=>$val):
                $cadArray .= $case==0 ? $key.' : '.$val.'    ' : $val.',';
            endforeach;
            $cadReturn .=  $case==0 ? $cadArray."\n\n" : $cadArray;
        endfor;
    endif;
    return $cadReturn;
}

// Funcion para contar el numero de concurrencias de un string dentro de un texto
function contarCoincidencias($data=array(),$clave=""){
    $cadena="";
    foreach($data as $k=>$v):$cadena.=$k; endforeach;
    return substr_count($cadena, $clave);
}
// Funcion para convertir objetos en notacion json a array php para procesarolos posteriormente
function objeto_a_array($data) {
    if (is_object($data)) {
        $data = get_object_vars($data);
    }

    if (is_array($data)) {
        return array_map(__FUNCTION__, $data);
    }
    else {
        return $data;
    }
}
//funcion para convertir imagenes de tipo bmp en jpg
function bmp2gd($src, $dest = false){
    if(!($src_f = fopen($src, "rb"))){
        return false;
    }
    if(!($dest_f = fopen($dest, "wb"))){
        return false;
    }
    $header = unpack("vtype/Vsize/v2reserved/Voffset", fread( $src_f, 14));
    $info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant",
        fread($src_f, 40));
    extract($info);
    extract($header);
    if($type != 0x4D42){
        return false;
    }

    $palette_size = $offset - 54;
    $ncolor = $palette_size / 4;
    $gd_header = "";

    $gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
    $gd_header .= pack("n2", $width, $height);
    $gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
    if($palette_size) {
        $gd_header .= pack("n", $ncolor);
    }

    $gd_header .= "\xFF\xFF\xFF\xFF";

    fwrite($dest_f, $gd_header);

    if($palette_size){
        $palette = fread($src_f, $palette_size);
        $gd_palette = "";
        $j = 0;
        while($j < $palette_size){
            $b = $palette{$j++};
            $g = $palette{$j++};
            $r = $palette{$j++};
            $a = $palette{$j++};
            $gd_palette .= "$r$g$b$a";
        }
        $gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
        fwrite($dest_f, $gd_palette);
    }
    $scan_line_size = (($bits * $width) + 7) >> 3;
    $scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;

    for($i = 0, $l = $height - 1; $i < $height; $i++, $l--){
        fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
        $scan_line = fread($src_f, $scan_line_size);
        if($bits == 24){
            $gd_scan_line = "";
            $j = 0;
            while($j < $scan_line_size){
                $b = $scan_line{$j++};
                $g = $scan_line{$j++};
                $r = $scan_line{$j++};
                $gd_scan_line .= "\x00$r$g$b";
            }
        }elseif($bits == 8){
            $gd_scan_line = $scan_line;
        }elseif($bits == 4){
            $gd_scan_line = "";
            $j = 0;
            while($j < $scan_line_size){
                $byte = ord($scan_line{$j++});
                $p1 = chr($byte >> 4);
                $p2 = chr($byte & 0x0F);
                $gd_scan_line .= "$p1$p2";
            }
            $gd_scan_line = substr($gd_scan_line, 0, $width);
        }elseif($bits == 1){
            $gd_scan_line = "";
            $j = 0;
            while($j < $scan_line_size){
                $byte = ord($scan_line{$j++});
                $p1 = chr((int) (($byte & 0x80) != 0));
                $p2 = chr((int) (($byte & 0x40) != 0));
                $p3 = chr((int) (($byte & 0x20) != 0));
                $p4 = chr((int) (($byte & 0x10) != 0));
                $p5 = chr((int) (($byte & 0x08) != 0));
                $p6 = chr((int) (($byte & 0x04) != 0));
                $p7 = chr((int) (($byte & 0x02) != 0));
                $p8 = chr((int) (($byte & 0x01) != 0));
                $gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
            }
            $gd_scan_line = substr($gd_scan_line, 0, $width);
        }
        fwrite($dest_f, $gd_scan_line);
    }
    fclose($src_f);
    fclose($dest_f);
    return true;
}

function ImageCreateFromBmp($filename){
    $tmp_name = tempnam("/tmp", "GD");
    if(bmp2gd($filename, $tmp_name)){
        $img = imagecreatefromgd($tmp_name);
        unlink($tmp_name);
        return $img;
    }
    return false;
}

function ImagenProporcion($ruta_imagen=null,$nombre_img=null,$rutaDestino=null){
    $miniatura_ancho_maximo = 300;
    $miniatura_alto_maximo  = 450;
    $validacion             = 3;
    $transparente           = null;
    $imageMarco            = null;

    $info_imagen  = getimagesize($ruta_imagen);
    $imagen_ancho = $info_imagen[0];
    $imagen_alto  = $info_imagen[1];
    $imagen_tipo  = $info_imagen['mime'];

    $proporcion_imagen = $imagen_ancho / $imagen_alto;
    $proporcion_miniatura = $miniatura_ancho_maximo / $miniatura_alto_maximo;

    $validacion = ($imagen_ancho < $imagen_alto) ? $validacion=1 : $validacion;
    $validacion = ($imagen_ancho > $imagen_alto) ? $validacion=2 : $validacion;
    $validacion = ($imagen_ancho == $imagen_alto) ? $validacion=3 : $validacion;

    switch ($validacion) {
        case 1:
            $miniatura_ancho = $miniatura_ancho_maximo+70;
            $miniatura_alto  = $miniatura_alto_maximo;
            break;
        case 2:
            $miniatura_ancho = ( ($miniatura_ancho_maximo * $proporcion_imagen)+50 ) > 450 ? 450 : ( $miniatura_ancho_maximo * $proporcion_imagen ) +50;
            $miniatura_alto  = ($miniatura_alto_maximo / $proporcion_imagen)+80;
            break;
        case 3:
            $miniatura_ancho = $miniatura_ancho_maximo;
            $miniatura_alto  = $miniatura_alto_maximo;
            break;
    }

    switch ( $imagen_tipo ){
        case "image/jpg":
        case "image/jpeg":
            $imagen = imagecreatefromjpeg( $ruta_imagen );
            break;
        case "image/png":
            $imagen = imagecreatefrompng( $ruta_imagen );
            break;
        case "image/gif":
            $imagen = imagecreatefromgif( $ruta_imagen );
            break;
    }

    $lienzo = imagecreatetruecolor( $miniatura_ancho, $miniatura_alto );
    $fondo_lienzo = imagecolorallocate($lienzo, 255, 255, 255);
    imagefilledrectangle($lienzo, 0, 0, $miniatura_ancho, $miniatura_alto, $fondo_lienzo);
    imagecopyresampled($lienzo, $imagen, 0, 0, 0, 0, $miniatura_ancho, $miniatura_alto, $imagen_ancho, $imagen_alto);

    $imageMarco = imageCreateTrueColor(  450, 450 );
    $fondo = imagecolorallocate($imageMarco, 255, 255, 255);
    imagefilledrectangle($imageMarco, 0, 0, 450, 450, $fondo);
    imageCopyResampled( $imageMarco, $lienzo, 0, 0, 0, 0, $miniatura_ancho, $miniatura_alto, 450, 450 );

    $lienzo = $imageMarco;

    imagejpeg($lienzo, $rutaDestino.$nombre_img, 80);

    return $nombre_img;
}

function escapeJsonString($value) {
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c", ' ');
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b", '-');
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}

function armaTreeview($aData){
    $treeView = array();
    foreach($aData as $key=>$tree){
        $tree['cod_perfil_padre'] = $tree['cod_perfil_padre']==0?'#':$tree['cod_perfil_padre'];
        $treeView[] = array('id'=>$tree['cod_perfil'],'parent'=>$tree['cod_perfil_padre'],'text'=>$tree['nom_perfil'],'state'=>array('opened'=>true));
    }

    return $treeView;
}

function generaHistoriaClinicaPDF( $data=array(), $type=0, $ruta='modules/endodoncia/adjuntos/', $libs="" ) {

    $result = false;
    $logoEmpresa = is_file("modules/sistema/adjuntos/{$data['empresa']['img_empresa']}")?"modules/sistema/adjuntos/{$data['empresa']['img_empresa']}":"../../sistema/adjuntos/{$data['empresa']['img_empresa']}";
    ob_start();
    $pdf                 = new PDF('P','mm','Letter');
    $pdf->logo_header    = $logoEmpresa;
    $pdf->titulo         = $data["empresa"]["nom_empresa"];
    $pdf->linea_1        = "Nit: {$data["empresa"]["nit_empresa"]}";
    $pdf->linea_2        = "Telefonos: - {$data["empresa"]["tel_empresa"]}";
    $pdf->linea_3        = "Web: {$data["empresa"]["web_empresa"]}";
    $pdf->linea_4        = "Sugerenias / PQR: {$data["empresa"]["email_empresa"]}";
    $pdf->linea_1_footer = $data["empresa"]["dir_empresa"];

    $pdf->AddPage('P','Letter');
    $pdf->SetAutoPageBreak(true,20);
    //Datos paciente
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'INFORMACION DEL PACIENTE',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'NUMERO DE CEDULA: ','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["ced_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'NOMBRE: ','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["nombre"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'EDAD: ','T,L',0,'L');
    $pdf->Cell(20,5,utf8_decode($data["paciente"]["edad"]),'T,R',0,'L');
    $pdf->Cell(30,5,'GENERO: ','T,L',0,'L');
    $pdf->Cell(20,5,utf8_decode($data["paciente"]["nom_genero"]),'T,R',0,'L');
    $pdf->Cell(40,5,'FECHA: ','T,L',0,'L');
    $pdf->Cell(34,5,utf8_decode($data["paciente"]["fec_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'DIRECCION: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["dir_paciente"]),'T,R',0,'L');
    $pdf->Cell(40,5,'TELEFONO: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["tel_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'PROFESION: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["profesion_paciente"]),'T,R',0,'L');
    $pdf->Cell(40,5,'CELULAR: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["cel_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'EMBARAZADA: ','T,L,B',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["embarazada"]),'T,R,B',1,'L');
    // Motivo de Consulta
    $pdf->Ln(5);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'RETRATAMIENTO: ','T,L,B',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["retratamiento"]),'T,R,B',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'MOTIVO DE CONSULTA','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["historia_clinica"]["motivo_historia_clinica"]),'L,R,B','J',false);
    // Anamnesis
    $pdf->Ln(5);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'ANAMNESIS',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(70,5,'ANTECEDENTES MEDICOS FAMILIARES:','T,L',0,'L');
    $pdf->Cell(114,5,utf8_decode($data["antecedentes_medicos_familiares"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(70,5,'ANTECEDENTES MEDICOS PERSONALES:','T,L',0,'L');
    $pdf->Cell(114,5,utf8_decode($data["antecedentes_medicos_personales"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(70,5,'ANTECEDENTES MEDICOS ODONTOLOGICOS:','T,L',0,'L');
    $pdf->Cell(114,5,utf8_decode($data["antecedentes_medicos_odontologicos"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(70,5,'ALERGIAS:','T,L',0,'L');
    $pdf->Cell(114,5,utf8_decode($data["alergias"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'MEDICAMENTOS:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,5,utf8_decode($data["medicamentos"]),'R,L,B','J',false);
    //examen endodontico
    $pdf->Ln(5);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'ANALISIS ENDODONTICO',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'DIENTE :','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["diente"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'QUE ?','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["que_historia_clinica"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'COMO ?','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["como_historia_clinica"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'CUANDO ?','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["cuando_historia_clinica"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'DONDE ?','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["donde_historia_clinica"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'PORQUE ?','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["porque_historia_clinica"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'TEJIDOS BLANDOS:','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["tejidos_blandos"].' - '.$data["historia_clinica"]["otro_tejidos_blandos"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'TEJIDOS DENTAL:','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["tejidos_dental"].' - '.$data["historia_clinica"]["otro_tejidos_dentales"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'TEJIDOS PERIODONTAL:','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["tejidos_periodontal"].' - '.$data["historia_clinica"]["otro_tejidos_periodontales"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'TEJIDOS PERIRRADUCULAR:','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["tejidos_perirradicular"].' - '.$data["historia_clinica"]["otro_tejidos_perirradiculares"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'TEJIDOS PULPAR:','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["tejidos_pulpar"].' - '.$data["historia_clinica"]["otro_tejidos_pulpares"]),'T,R',1,'L',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'ANALISIS RADIOGRAFICO:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["historia_clinica"]["des_anarad_historia_clinica"]),'R,L,B','J',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'DIAGNOSTICO:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["diagnostico"]),'R,L,B','J',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'ANALISIS DE SENSIBILIDAD: ','T,L,B',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["sensibilidad"]),'T,R,B',1,'L',false);
    //segunda hoja
    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'INFORMACION CONDUCTOS',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $lap  = contarCoincidencias($data["conductometria"] ,'lap');
    $slap=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='lap'){if($val==''){$slap++;}}}$slap = $lap - $slap;
    $clap = 0;
    $pdf->Cell(34,5,'CANAL',0,$slap==0 ? 1 : 0,'L');
    //informacion para lima apical principal
    foreach ($data["conductometria"] as $key => $value):
        if(devuelveString($key,'_',1)=='lap'){
            if($value!= ''){
                $clap++;
                $marco = $slap==$clap ? 1 : 0;
                $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
            }
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $lap  = contarCoincidencias($data["conductometria"] ,'lap');
    $slap=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='lap'){if($val==''){$slap++;}}}$slap = $lap - $slap;
    $clap=0;
    $pdf->Cell(34,5,'LAP',0,$slap==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'lap':
                if($value!= ''){
                    $clap++;
                    $marco = $slap==$clap ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }else{$lap = $lap-1;}
                break;
        }
    endforeach;
    //informacion para longitud
    $pdf->Ln(0);$pdf->Cell(5,5);
    $longitud  = contarCoincidencias($data["conductometria"] ,'longitud');
    $slongitud=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='longitud'){if($val==''){$slongitud++;}}}$slongitud = $longitud - $slongitud;
    $clongitud = 0;
    $pdf->Cell(34,5,'CANAL',0,$slongitud==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'longitud':
                if($value!= ''){
                    $clongitud++;
                    $marco = $slongitud==$clongitud ? 1 : 0;
                    $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                    $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
                }
                break;
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $longitud  = contarCoincidencias($data["conductometria"] ,'longitud');
    $slongitud=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='longitud'){if($val==''){$slongitud++;}}}$slongitud = $longitud - $slongitud;
    $clongitud = 0;
    $pdf->Cell(34,5,'LONGITUD',0,$slongitud==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'longitud':
                if($value!= ''){
                    $clongitud++;
                    $marco = $slongitud==$clongitud ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }
                break;
        }
    endforeach;
    //informacion para conometria
    $pdf->Ln(0);$pdf->Cell(5,5);
    $conometria  = contarCoincidencias($data["conductometria"] ,'conometria');
    $sconometria=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='conometria'){if($val==''){$sconometria++;}}}$sconometria = $conometria - $sconometria;
    $cconometria = 0;
    $pdf->Cell(34,5,'CANAL',0,$sconometria==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'conometria':
                if($value!= ''){
                    $cconometria++;
                    $marco = $sconometria==$cconometria ? 1 : 0;
                    $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                    $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
                }
                break;
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $conometria  = contarCoincidencias($data["conductometria"] ,'conometria');
    $sconometria=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='conometria'){if($val==''){$sconometria++;}}}$sconometria = $conometria - $sconometria;
    $cconometria = 0;
    $pdf->Cell(34,5,'CONOMETRIA',0,$sconometria==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'conometria':
                if($value!= ''){
                    $cconometria++;
                    $marco = $sconometria==$cconometria ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }
                break;
        }
    endforeach;
    if($data["historia_clinica"]["ind_desobturacion"]==1){
        $pdf->Ln(5);$pdf->Cell(5,5);
        $pdf->Cell(184,5,'DESOBTURACION',0,1,'L');
        //informacion para lima apical principal
        if($data["conductometria"]["canal_desobturacion"]>0 Or $data["conductometria"]["canal_desobturacion"]!=null):
            $pdf->Ln(0);$pdf->Cell(5,5);
            $pdf->Cell(34,5,'CANAL',0,0,'L');
            $pdf->Cell(34,5,$data["conductometria"]["canal_desobturacion"],1,1,'C');
        endif;
        if($data["conductometria"]["long_desobturacion"]>0 Or $data["conductometria"]["long_desobturacion"]!=null):
            $pdf->Ln(0);$pdf->Cell(5,5);
            $pdf->Cell(34,5,'LONGITUD',0,0,'L');
            $pdf->Cell(34,5,$data["conductometria"]["long_desobturacion"],1,1,'C');
        endif;
    }
    $pdf->AddPage('P','Letter');
    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'RADIOGRAFIAS E IMAGENES',0,1,'L');
    $mx=$pdf->GetX()+5;
    $my=$pdf->GetY();
    $c=0;
    if(!empty($data["imagenes"])):
        foreach ($data["imagenes"] as $key => $value):
            $imgFinal = ImagenProporcion($ruta .$value['img_registro_imagenes'],$value["img_registro_imagenes"],$ruta.'img_historia/');
            $pdf->Cell(5,5);
            $pdf->Cell(70, 40, $pdf->Image($ruta.'img_historia/'.$imgFinal,$mx,$my,70), 0,0, 'C');
            $mx = $mx+85;
            $c++;
            if($c%2==0){$my = $my+78;$mx=15.00125;}
            unlink($ruta.'img_historia/'.$imgFinal);
        endforeach;
    endif;
    $pdf->AddPage('P','Letter');
    $pdf->Ln(10);

    if(!empty($data["evoluciones"])):
        foreach ($data["evoluciones"] as $key):
            $pdf->MultiCell(184,5,utf8_decode($key["evolucion"]),0,'J');
        endforeach;
    endif;

    switch ($type) {
        case 0:
            $pdf->Output($data["paciente"]["nombre"] . '.pdf', 'D');
            $result = true;
            break;
        case 1:
            $adj = $ruta.'historias/historia-' . $data["paciente"]["nombre"] . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);
            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la historia clinica del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome",
                "to"            => $data["odontologos"]
            );

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);

            unlink($adj);

            if($result)
                $result = true;

            break;
        case 2:
            $adj = $ruta.'historias/' . $data["paciente"]["nombre"] . '-' . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);
            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la historia clinica del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome",
                "to"            => $data["paciente"]["email_paciente"]
            );

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);

            unlink($adj);

            if($result)
                $result = true;

            break;

        case 3:
            $adj = $ruta.'historias/historia-' . $data["paciente"]["nombre"] . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);

            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la historia clinica del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome",
                "to"            => $data["paciente"]["email_odontologo"]);

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);
            $email_array['to'] = $data["paciente"]["email_paciente"];
            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);
            unlink($adj);

            if($result)
                $result = true;

            break;
    }

    ob_end_flush();

    return $result;
}

function generaRemisionPDF( $data=array(), $type=0, $ruta='modules/endodoncia/adjuntos/', $libs="" ) {

    $result         = false;
    $logoEmpresa    = is_file("modules/sistema/adjuntos/{$data['empresa']['img_empresa']}")?"modules/sistema/adjuntos/{$data['empresa']['img_empresa']}":"../../sistema/adjuntos/{$data['empresa']['img_empresa']}";
    ob_start();

    $pdf                 = new PDF('P','mm','Letter');
    $pdf->logo_header    = $logoEmpresa;
    $pdf->titulo         = $data["empresa"]["nom_empresa"];
    $pdf->linea_1        = 'Nit: ' . $data["empresa"]["nit_empresa"];
    $pdf->linea_2        = 'Telefonos: - ' . $data["empresa"]["tel_empresa"];
    $pdf->linea_3        = 'Web: ' . $data["empresa"]["web_empresa"];
    $pdf->linea_4        = 'Sugerenias / PQR: ' . $data["empresa"]["email_empresa"];
    $pdf->linea_1_footer = $data["empresa"]["dir_empresa"];
    $pdf->AddPage('P','Letter');
    //Datos paciente
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'INFORMACION DEL PACIENTE',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'NUMERO DE CEDULA: ','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["ced_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'NOMBRE: ','T,L',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["nombre"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'EDAD: ','T,L',0,'L');
    $pdf->Cell(20,5,utf8_decode($data["paciente"]["edad"]),'T,R',0,'L');
    $pdf->Cell(30,5,'GENERO: ','T,L',0,'L');
    $pdf->Cell(20,5,utf8_decode($data["paciente"]["nom_genero"]),'T,R',0,'L');
    $pdf->Cell(40,5,'FECHA: ','T,L',0,'L');
    $pdf->Cell(34,5,utf8_decode($data["paciente"]["fec_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'DIRECCION: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["dir_paciente"]),'T,R',0,'L');
    $pdf->Cell(40,5,'TELEFONO: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["tel_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'PROFESION: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["profesion_paciente"]),'T,R',0,'L');
    $pdf->Cell(40,5,'CELULAR: ','T,L',0,'L');
    $pdf->Cell(52,5,utf8_decode($data["paciente"]["cel_paciente"]),'T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'EMBARAZADA: ','T,L,B',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["paciente"]["embarazada"]),'T,R,B',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(40,5,'DIENTE :','T,L,B',0,'L');
    $pdf->Cell(144,5,utf8_decode($data["historia_clinica"]["diente"]),'T,R,B',1,'L',false);

    //examen endodontico
    $pdf->Ln(5);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'ANALISIS RADIOGRAFICO:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["historia_clinica"]["des_anarad_historia_clinica"]),'R,L,B','J',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'DIAGNOSTICO:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["diagnostico"]),'R,L,B','J',false);
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(184,5,'OBSERVACIONES:','T,L,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->MultiCell(184,10,utf8_decode($data["historia_clinica"]["des_remision_historia_clinica"]),'R,L,B','J',false);
    //segunda hoja
    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'INFORMACION CONDUCTOS',0,1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $lap  = contarCoincidencias($data["conductometria"] ,'lap');
    $slap=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='lap'){if($val==''){$slap++;}}}$slap = $lap - $slap;
    $clap = 0;
    $pdf->Cell(34,5,'CANAL',0,$slap==0 ? 1 : 0,'L');
    //informacion para lima apical principal
    foreach ($data["conductometria"] as $key => $value):
        if(devuelveString($key,'_',1)=='lap'){
            if($value!= ''){
                $clap++;
                $marco = $slap==$clap ? 1 : 0;
                $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
            }
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $lap  = contarCoincidencias($data["conductometria"] ,'lap');
    $slap=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='lap'){if($val==''){$slap++;}}}$slap = $lap - $slap;
    $clap=0;
    $pdf->Cell(34,5,'LAP',0,$slap==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'lap':
                if($value!= ''){
                    $clap++;
                    $marco = $slap==$clap ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }else{$lap = $lap-1;}
                break;
        }
    endforeach;
    //informacion para longitud
    $pdf->Ln(0);$pdf->Cell(5,5);
    $longitud  = contarCoincidencias($data["conductometria"] ,'longitud');
    $slongitud=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='longitud'){if($val==''){$slongitud++;}}}$slongitud = $longitud - $slongitud;
    $clongitud = 0;
    $pdf->Cell(34,5,'CANAL',0,$slongitud==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'longitud':
                if($value!= ''){
                    $clongitud++;
                    $marco = $slongitud==$clongitud ? 1 : 0;
                    $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                    $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
                }
                break;
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $longitud  = contarCoincidencias($data["conductometria"] ,'longitud');
    $slongitud=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='longitud'){if($val==''){$slongitud++;}}}$slongitud = $longitud - $slongitud;
    $clongitud = 0;
    $pdf->Cell(34,5,'LONGITUD',0,$slongitud==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'longitud':
                if($value!= ''){
                    $clongitud++;
                    $marco = $slongitud==$clongitud ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }
                break;
        }
    endforeach;
    //informacion para conometria
    $pdf->Ln(0);$pdf->Cell(5,5);
    $conometria  = contarCoincidencias($data["conductometria"] ,'conometria');
    $sconometria=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='conometria'){if($val==''){$sconometria++;}}}$sconometria = $conometria - $sconometria;
    $cconometria = 0;
    $pdf->Cell(34,5,'CANAL',0,$sconometria==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'conometria':
                if($value!= ''){
                    $cconometria++;
                    $marco = $sconometria==$cconometria ? 1 : 0;
                    $celda = devuelveString($key,'_',2)!='uni_conducto' ? devuelveString($key,'_',2) : 'uni c';
                    $pdf->Cell(10,5,strtoupper($celda),1,$marco,'C');
                }
                break;
        }
    endforeach;
    $pdf->Ln(0);$pdf->Cell(5,5);
    $conometria  = contarCoincidencias($data["conductometria"] ,'conometria');
    $sconometria=0;
    foreach ($data["conductometria"] as $k => $val) { if(devuelveString($k,'_',1)=='conometria'){if($val==''){$sconometria++;}}}$sconometria = $conometria - $sconometria;
    $cconometria = 0;
    $pdf->Cell(34,5,'CONOMETRIA',0,$sconometria==0 ? 1 : 0,'L');
    foreach ($data["conductometria"] as $key => $value):
        switch (devuelveString($key,'_',1)) {
            case 'conometria':
                if($value!= ''){
                    $cconometria++;
                    $marco = $sconometria==$cconometria ? 1 : 0;
                    $pdf->Cell(10,5,$value,1,$marco,'C');
                }
                break;
        }
    endforeach;
    if($data["historia_clinica"]["ind_desobturacion"]==1){
        $pdf->Ln(5);$pdf->Cell(5,5);
        $pdf->Cell(184,5,'DESOBTURACION',0,1,'L');
        //informacion para lima apical principal
        if($data["conductometria"]["canal_desobturacion"]>0 Or $data["conductometria"]["canal_desobturacion"]!=null):
            $pdf->Ln(0);$pdf->Cell(5,5);
            $pdf->Cell(34,5,'CANAL',0,0,'L');
            $pdf->Cell(34,5,$data["conductometria"]["canal_desobturacion"],1,1,'C');
        endif;
        if($data["conductometria"]["long_desobturacion"]>0 Or $data["conductometria"]["long_desobturacion"]!=null):
            $pdf->Ln(0);$pdf->Cell(5,5);
            $pdf->Cell(34,5,'LONGITUD',0,0,'L');
            $pdf->Cell(34,5,$data["conductometria"]["long_desobturacion"],1,1,'C');
        endif;
    }
    $pdf->AddPage('P','Letter');
    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->Cell(184,5,'RADIOGRAFIAS E IMAGENES',0,1,'L');
    $mx=$pdf->GetX()+5;
    $my=$pdf->GetY();
    $c=0;

    if(!empty($data["imagenes"])):
        foreach ($data["imagenes"] as $key => $value):
            $imgFinal = ImagenProporcion($ruta .$value['img_registro_imagenes'],$value["img_registro_imagenes"],$ruta.'img_historia/');
            $pdf->Cell(5,5);
            $pdf->Cell(70, 40, $pdf->Image($ruta.'img_historia/'.$imgFinal,$mx,$my,70), 0,0, 'C');
            $mx = $mx+85;
            $c++;
            if($c%2==0){$my = $my+78;$mx=15.00125;}
            unlink($ruta.'img_historia/'.$imgFinal);
        endforeach;
    endif;

    switch ($type) {
        case 0:
            $pdf->Output($data["paciente"]["nombre"] . '.pdf', 'D');
            $result = true;
            break;
        case 1:
            $adj = $ruta.'remisiones/remision-' . $data["paciente"]["nombre"] . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);
            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la remision del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome.",
                "to"            => $data["odontologos"]
            );

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);

            unlink($adj);

            if($result)
                $result = true;

            break;
        case 2:
            $adj = $ruta.'remisiones/remision-' . $data["paciente"]["nombre"] . '-' . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);
            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la remision del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome",
                "to"            => $data["paciente"]["email_paciente"]
            );

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);

            unlink($adj);

            if($result)
                $result = true;

            break;

        case 3:
            $adj = $ruta.'remisiones/remision-' . $data["paciente"]["nombre"] . date('Y-m-d') . '-' . time() . '.pdf';
            $pdf->Output($adj);

            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene la remision del paciente {$data['paciente']['nombre']} generada automaticamente desde miendodoncia.com, para visualizar correctamente se recomienda abrir el documento PFD con Google Chrome",
                "to"            => $data["paciente"]["email_odontologo"]);

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);
            $email_array['to'] = $data["paciente"]["email_paciente"];
            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);
            unlink($adj);

            if($result)
                $result = true;

            break;
    }

    ob_end_flush();

    return $result;
}

function generaConsentimientoPDF( $data=array(), $ruta='modules/endodoncia/adjuntos/', $libs="" ) {

    $result         = false;
    $logoEmpresa    = is_file("modules/sistema/adjuntos/{$data['empresa']['img_empresa']}")?"modules/sistema/adjuntos/{$data['empresa']['img_empresa']}":"../../sistema/adjuntos/{$data['empresa']['img_empresa']}";
    ob_start();

    $pdf                 = new PDF('P','mm','Letter');
    $pdf->logo_header    = $logoEmpresa;
    $pdf->titulo         = $data["empresa"]["nom_empresa"];
    $pdf->linea_1        = 'Nit: ' . $data["empresa"]["nit_empresa"];
    $pdf->linea_2        = 'Telefonos: - ' . $data["empresa"]["tel_empresa"];
    $pdf->linea_3        = 'Web: ' . $data["empresa"]["web_empresa"];
    $pdf->linea_4        = 'Sugerenias / PQR: ' . $data["empresa"]["email_empresa"];
    $pdf->linea_1_footer = $data["empresa"]["dir_empresa"];
    $pdf->AddPage('P','Letter');

    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->SetFont('Arial','',8);
    $pdf->Cell(184,5,'CONSENTIMIENTO INFORMADO',0,1,'C');
    $pdf->Ln(10);
    $pdf->Cell(5,5);
    $pdf->MultiCell(184,5,$data['texto'],0,'J');
    $pdf->Ln(30);
    $pdf->Cell(5,5);
    $pdf->Cell(60, 23, $pdf->Image('modules/endodoncia/adjuntos/'.$data['text_img'],15,198,40), 0, 0, 'C');
    //
    $pdf->Output($data["consentimiento"]["nom_paciente"].'.pdf','D');

    ob_end_flush();

    return $result;
}

function generaComprobanteIngresoPDF($data=array(), $type=0, $ruta='modules/endodoncia/adjuntos/', $libs="") {
    $result         = false;
    $logoEmpresa    = is_file("modules/sistema/adjuntos/{$data['empresa']['img_empresa']}")?"modules/sistema/adjuntos/{$data['empresa']['img_empresa']}":"../../sistema/adjuntos/{$data['empresa']['img_empresa']}";
    ob_start();

    $pdf                 = new PDF('P','mm','Letter');
    $pdf->logo_header    = $logoEmpresa;
    $pdf->titulo         = $data["empresa"]["nom_empresa"];
    $pdf->linea_1        = 'Nit: ' . $data["empresa"]["nit_empresa"];
    $pdf->linea_2        = 'Telefonos: - ' . $data["empresa"]["tel_empresa"];
    $pdf->linea_3        = 'Web: ' . $data["empresa"]["web_empresa"];
    $pdf->linea_4        = 'Sugerenias / PQR: ' . $data["empresa"]["email_empresa"];
    $pdf->linea_1_footer = $data["empresa"]["dir_empresa"];

    $pdf->AddPage('L','A5');
    //Datos paciente
    $pdf->Cell(5,5);
    $pdf->Cell(70,5,'COMPROBANTE DE ABONO: ',1,0,'L');
    $pdf->Cell(45,5,$data["informacion_comprobante"]["num_sig_comp_ingreso"],1,1,'L');
    $pdf->Ln(10);$pdf->Cell(5,5);
    $pdf->Cell(50,5,'FECHA Y HORA DE REGISTRO: ','T,L',0,'L');
    $pdf->Cell(134,5,utf8_decode($data["informacion_comprobante"]["fec_pago"]),'L,T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(50,5,'PACIENTE: ','T,L',0,'L');
    $pdf->Cell(134,5,utf8_decode($data["informacion_comprobante"]["nom_paciente"]),'L,T,R',1,'L');
    $pdf->Ln(0);$pdf->Cell(5,5);
    $pdf->Cell(50,5,'DIRECCION: ','T,L,B',0,'L');
    $pdf->Cell(60,5,utf8_decode($data["informacion_comprobante"]["dir_paciente"]),'L,T,R,B',0,'L');
    $pdf->Cell(24,5,'CIUDAD: ','T,B',0,'L');
    $pdf->Cell(50,5,utf8_decode($data["informacion_comprobante"]["nom_ciudad"]),'T,R,B',1,'J');
    $pdf->Ln(10);$pdf->Cell(5,5);
    $pdf->Cell(50,5,'POR CONCEPTO DE: ','T,L,B',0,'L');
    $pdf->MultiCell(134,5,utf8_decode($data["informacion_comprobante"]["tratamiento"]),'L,T,R,B','J');
    $pdf->Cell(5,5);
    $pdf->Cell(50,5,'POR VALOR DE: ','T,L,B',0,'L');
    $pdf->Cell(134,5,round($data["informacion_comprobante"]["imp_pago"],2),'L,T,R,B',1,'L');
    /*$pdf->Cell(5,5);
    $pdf->Cell(50,5,'NUEVO SALDO: ','T,L,B',0,'L');
    $pdf->Cell(134,5,round($data["informacion_comprobante"]["imp_adeu_historia_clinica"],2),'T,R,B',1,'L');*/
    $pdf->Cell(5,5);
    $pdf->Cell(50,5,'OBSERVACIONES: ','T,L,B',0,'L');
    $pdf->Cell(134,5,utf8_decode($data["informacion_comprobante"]["obs_pago"]),'L,T,R,B',1,'L');
    $pdf->Ln(15);$pdf->Cell(5,5);
    $pdf->Cell(100,5,'RECIBI CONFORME: ','T',0,'L');

    switch ($type) {
        case 0:
            $pdf->Output($data["informacion_comprobante"]["num_sig_comp_ingreso"] . '.pdf', 'D');
            $result = true;
            break;
        case 2:
            $adj = $ruta."comprobantes/ingreso_{$data["informacion_comprobante"]["num_sig_comp_ingreso"]}".'-'.date('Y-m-d').'-'.time().'.pdf';
            $pdf->Output($adj);
            $email_array = array(
                "Saludo"        => "Cordial Saludo: ",
                "Introduccion"  => $data[1][0]["asunto_config"],
                "Descripcion"   => "Este correo contiene el comprobante del paciente {$data["informacion_comprobante"]["nom_paciente"]} generado automaticamente desde miendodoncia.com",
                "to"            => $data["informacion_comprobante"]["email_paciente"]
            );

            $result = sendEmail("sistema", 3, $email_array, '', $data, $libs, '', $adj, $r);

            unlink($adj);

            if($result)
                $result = true;

            break;
    }

    ob_end_flush();

    return $result;

}