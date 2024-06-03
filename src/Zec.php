<?php
declare(strict_types=1);

namespace Zec;

use BadMethodCallException;
use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use Zec\Traits;
use function Zec\Utils\is_zec;
use function Zec\bundler\bundler as bundler;

require_once ZEC_PATH . '/src/parsers/Parser.php';
require_once ZEC_PATH . '/src/parsers/Parsers.php';
require_once ZEC_PATH . '/src/Exception.php';


if(!class_exists('Zed')) {
    class Zec extends Parsers {
        use Traits\ZecErrors, Traits\ZecPath, Traits\ZecConfigs, Traits\ZecUtils, Traits\ZecDefault, Traits\ZecValue, Traits\ZecParent;
        public const VERSION = '1.0.0'; 
        public function __construct(Zec|array|null $args = null) {
            parent::__construct();
            // if type of args is Zec then clone it as a parent
            $parent = is_zec($args) ? $args : null;
            if(!is_null($parent)) {
                $this->cloneParent($parent);
            }

            $this->initConfig($args);
        }
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
        static function proxySetArg(mixed $default, ?Zec $parent = null): array {
            return [
                'default' => $default,
                'parent' => $parent
            ];
        }
        public static function proxyGetArg(?array $args): array {
            if (is_null($args)) {
                return [
                    'default' => null,
                    'parent' => null
                ];
            }
            return [
                'default' => isset($args['default']) ? $args['default'] : null,
                'parent' => isset($args['parent']) ? $args['parent'] : null
            ];
        }
        
        public function parse(mixed $value, array $args): Zec {
            // clean errors
            $this->clearErrors();

            // set the value
            $this->setValue($value);

            if (is_null($args)) {
                $args = [];
            }
            $args = self::proxyGetArg($args);

            $this->setDefault($args['default']);
            $this->cloneParent($args['parent']);

            $has_required = $this->hasParserKey(PK::REQUIRED);
            if (!$has_required && is_null($value)) {
                return $this;
            }
            
            foreach ($this->listParsers() as $parser) {
                // identify $parser as a instance of Parser
                if (!is_a($parser, Parser::class)) {
                    continue;
                } 
                $this->setKeyParser($parser->name);
                $response = $parser->parse($this->value, Parser::proxySetArg($this->default, $this));
                $this->clear_last_parser();
                if($response['close']) {
                    break;
                }
            }

            $this->sendErrorsToParent();

            return $this;
        }
        public function parseOrThrow(mixed $value, mixed $default = null): mixed {
            $this->parse($value, Zec::proxySetArg($default, null));
            if (!$this->isValid()) {
                $this->throwErrors();
            }
            return $this->getValue();
        }
    }
}
