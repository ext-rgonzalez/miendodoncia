<?php

function autoloadCore($class){ 
    if(file_exists(APP_PATH . $class . '.php')){
        include_once APP_PATH . $class . '.php';
    }
}

spl_autoload_register("autoloadCore");

?>
