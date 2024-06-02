<?php 
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('ZEC_PATH')) {
    define('ZEC_PATH', __DIR__);
}


require_once ZEC_PATH . '/src/const/KEY.php';

require_once ZEC_PATH . '/src/traits/ParserArgument.php';
require_once ZEC_PATH . '/src/traits/ParserLifecycle.php';
require_once ZEC_PATH . '/src/traits/ParserOrder.php';
require_once ZEC_PATH . '/src/traits/ParserPriority.php';
require_once ZEC_PATH . '/src/traits/ZecPath.php';
require_once ZEC_PATH . '/src/traits/ZecParent.php';
require_once ZEC_PATH . '/src/traits/ZecConf.php';
require_once ZEC_PATH . '/src/traits/ZecDefault.php';
require_once ZEC_PATH . '/src/traits/ZecUtils.php';
require_once ZEC_PATH . '/src/traits/ZecValue.php';
require_once ZEC_PATH . '/src/traits/ZecErrorFrom.php';
require_once ZEC_PATH . '/src/traits/ZecErrors.php';

require_once ZEC_PATH . '/src/parsers/Parser.php';
require_once ZEC_PATH . '/src/parsers/Parsers.php';

require_once ZEC_PATH . '/src/config/Bundler.php';
require_once ZEC_PATH . '/src/config/init.php'; 

require_once ZEC_PATH . '/src/Zec.php';
require_once ZEC_PATH . '/src/Exception.php';

require_once ZEC_PATH . '/src/utils/zec.php';
require_once ZEC_PATH . '/src/parsers/Parser.php';
require_once ZEC_PATH . '/src/parsers/Parsers.php';