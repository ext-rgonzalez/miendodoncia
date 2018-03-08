<?php
class Session{

    public static function init(){
        session_start();
    }

    public static function destroy(){
        session_unset();
        session_destroy();
    }

    public static function set($clave, $valor){
        if(!empty($clave))
            $_SESSION[$clave] = $valor;
    }
    public static function get($clave){
        if(isset($_SESSION[$clave]))
           return $_SESSION[$clave];

        return null;
    }
    public static function getSessionData(){
        print json_encode($_SESSION);
    }

}
?>