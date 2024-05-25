<?php 

use Zod\Zod;

if (!defined('ZOD_PATH')) {
    define('ZOD_PATH', __DIR__);
}

function log_msg(mixed $msg) {
    if (!defined('STDIN')) { // if running from the command line
        return;
    }
    echo 'LOG: ';
    if (is_string($msg) || is_numeric($msg)) {
        echo $msg . PHP_EOL;
    } else {
        print_r($msg);
    }
    // print the stack trace
    $trace = debug_backtrace();
    // foreach ($trace as $t) {
    //     echo 'File: ' . $t['file'] . ' Line: ' . $t['line'] . PHP_EOL;
    // }
    echo PHP_EOL;
    echo PHP_EOL;
    return;
}


require_once ZOD_PATH . '/src/const/KEY.php';

require_once ZOD_PATH . '/src/utils/ParserArgument.php';

require_once ZOD_PATH . '/src/utils/ParserLifecycle.php';

require_once ZOD_PATH . '/src/utils/ParserOrder.php';

require_once ZOD_PATH . '/src/utils/ParserPriority.php';

require_once ZOD_PATH . '/src/utils/ZodPath.php';

require_once ZOD_PATH . '/src/utils/ZodErrors.php';

require_once ZOD_PATH . '/src/utils/ZodConf.php';

require_once ZOD_PATH . '/src/utils/ZodUtils.php';

require_once ZOD_PATH . '/src/utils/ZodDefault.php';

require_once ZOD_PATH . '/src/utils/ZodValue.php';

require_once ZOD_PATH . '/src/utils/ZodParent.php';

require_once ZOD_PATH . '/src/CaretakerParsers.php';

require_once ZOD_PATH . '/src/parsers/Parser.php';

require_once ZOD_PATH . '/src/config/Bundler.php';

require_once ZOD_PATH . '/src/parsers/Parsers.php';

require_once ZOD_PATH . '/src/Zod.php';

require_once ZOD_PATH . '/src/config/init.php';

