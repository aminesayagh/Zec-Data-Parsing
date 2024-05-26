<?php 

use Zec\Zec;

if (!defined('ZEC_PATH')) {
    define('ZEC_PATH', __DIR__);
}

require_once ZEC_PATH . '/src/const/KEY.php';

require_once ZEC_PATH . '/src/utils/ParserArgument.php';

require_once ZEC_PATH . '/src/utils/ParserLifecycle.php';

require_once ZEC_PATH . '/src/utils/ParserOrder.php';

require_once ZEC_PATH . '/src/utils/ParserPriority.php';

require_once ZEC_PATH . '/src/utils/ZecPath.php';

require_once ZEC_PATH . '/src/utils/ZecErrors.php';

require_once ZEC_PATH . '/src/utils/ZecConf.php';

require_once ZEC_PATH . '/src/utils/ZecUtils.php';

require_once ZEC_PATH . '/src/utils/ZecDefault.php';

require_once ZEC_PATH . '/src/utils/ZecValue.php';

require_once ZEC_PATH . '/src/utils/ZecParent.php';

require_once ZEC_PATH . '/src/CaretakerParsers.php';

require_once ZEC_PATH . '/src/parsers/Parser.php';

require_once ZEC_PATH . '/src/config/Bundler.php';

require_once ZEC_PATH . '/src/parsers/Parsers.php';

require_once ZEC_PATH . '/src/Zec.php';

require_once ZEC_PATH . '/src/config/init.php';

