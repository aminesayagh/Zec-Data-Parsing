<?php 

define('ZOD_PATH', __DIR__);

spl_autoload_register(function ($class) {
    $file = ZOD_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});


require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';
require_once ZOD_PATH . '/config/bundler.php';
require_once ZOD_PATH . '/src/Zod.php';



echo 'work';