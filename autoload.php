<?php


function carregaClasses($classe) {
    
    $classe = dirname(__DIR__) .'/'. str_replace('\\', '/', $classe) . '.php';
    
    if (file_exists($classe)) 
    {
        require_once $classe;
    }
    
}
spl_autoload_register('carregaClasses');