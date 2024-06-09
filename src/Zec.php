<?php
declare(strict_types=1);

namespace Zec;

use BadMethodCallException;
// use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use Zec\CONST\{
    PARSERS_KEY as PK
};
use Zec\Traits\{
    ZecErrors,
    ZecPath,
    ZecConfigs,
    ZecUtils,
    ZecDefault,
    ZecValue,
    ZecParent
};
use function Zec\Utils\is_zec;
use function Zec\bundler\bundler as bundler;

require_once ZEC_PATH . '/src/parsers/Parser.php';
require_once ZEC_PATH . '/src/parsers/Parsers.php';
require_once ZEC_PATH . '/src/Exception.php';


if(!class_exists('Zed')) {
    class Zec extends Parsers {
        use ZecErrors, ZecPath, ZecConfigs, ZecUtils, ZecDefault, ZecValue, ZecParent;
        public const VERSION = '1.0.0'; 
        public function __construct(Zec|array|null $args = null) {
            parent::__construct();
            $parent = is_zec($args) ? $args : null;
            if(!is_null($parent)) {
                $this->cloneParent($parent);
            }

            $this->initConfig($args);
        }
        // override
        public function __clone(): void {
            $this->parsers = array_filter($this->parsers, function($parser) {
                if (is_null($parser)) {
                    return false;
                }
                if ($parser->name == PK::REQUIRED) {
                    return false;
                }
                return $parser->clone();
            });
        }
        public function __call(string $name, ?array $arguments): mixed{
            // check if the method exists
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            // check if the parser exists
            if (bundler()->hasParserKey($name)) {
                // get the parser
                $parser = bundler()->getParser($name);
                
                // set the arguments if they exist
                if(is_array($arguments) && count($arguments) > 0) {
                    $arguments = $arguments[0];
                    $parser->setArgument($arguments);
                }
                
                // add the parser to the Zec instance
                $this->addParser($parser);
                return $this;
            }
            // throw an error if the method or parser is not found
            throw new BadMethodCallException("Method $name not found");
        }
        public function parse(mixed $value): Zec {
            // Init
            $this->clearErrors();
            $this->setValue($value);
            
            foreach ($this->listParsers() as $parser) {
                if (!is_a($parser, Parser::class)) {
                    continue;
                } 
                $parser->default($this->default);
                $parser->setOwner($this);
                $response = $parser->parse($this->value);
                if($response['close']) {
                    break;
                }
            }

            $this->sendErrorsToParent();
            return $this;
        }
        public function parseOrThrow(mixed $value): mixed {
            $this->parse($value);
            if (!$this->isValid()) {
                $this->throwErrors();
            }
            return $this->getValue();
        }
    }
}
