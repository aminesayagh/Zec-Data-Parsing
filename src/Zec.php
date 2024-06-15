<?php
declare(strict_types=1);

namespace Zec;

use BadMethodCallException;
use Zec\CONST\PARSERS_KEY as PK;

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


if (!class_exists('Zec')) {
    /**
     * Class Zec
     * @package Zec
     * 
     * The main class for Zec library that handles parsing and validation.
     * @method Zec string(array|string|number|null $arguments = null)
     * @method Zec number(array|string|number|null $arguments = null)
     * @method Zec bool(array|string|number|null $arguments = null)
     * @method Zec date(array|string|number|null $arguments = null)
     * @method Zec email(array|string|number|null $arguments = null)
     * @method Zec url(array|string|number|null $arguments = null)
     * @method Zec options(array|string|number|null $arguments = null)
     * @method Zec each(array|string|number|Zec|null $arguments = null)
     * @method Zec optional(array|string|number|null $arguments = null)
     * @method Zec nullable(array|string|number|null $arguments = null)
     * @method Zec min(array|string|number|null $arguments = null)
     * @method Zec max(array|string|number|null $arguments = null)
     * @method Zec length(array|string|number|null $arguments = null)
     */
    class Zec extends Parsers
    {
        use ZecErrors, ZecPath, ZecConfigs, ZecUtils, ZecDefault, ZecValue, ZecParent;
        public const VERSION = '1.0.0';

        /**
         * Zec constructor.
         * Initializes the Zec instance with optional arguments.
         *
         * @param Zec|array|null $args Optional arguments for initialization.
         */
        public function __construct(Zec|array|null $args = null)
        {
            parent::__construct();
            $parent = is_zec($args) ? $args : null;
            if ($parent !== null) {
                $this->cloneParent($parent);
            }

            $this->initConfig($args);
        }
        /**
         * Clone method to handle cloning of parsers.
         */
        public function __clone(): void
        {
            $this->parsers = array_filter($this->parsers, function ($parser) {
                if ($parser == null) {
                    return false;
                }
                if ($parser->name == PK::REQUIRED) {
                    return false;
                }
                return $parser->clone();
            });
        }
        /**
         * Magic call method to handle dynamic method calls.
         * 
         * @param string $name The method name being called.
         * @param array|null $arguments The arguments passed to the method.
         * @return Zec
         * @throws BadMethodCallException If the method or parser is not found.
         */
        public function __call(string $name, ?array $arguments): mixed
        {
            // check if the method exists
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            // check if the parser exists
            if (bundler()->hasParserKey($name)) {
                // get the parser
                $parser = bundler()->getParser($name);

                // set the arguments if they exist
                if (is_array($arguments) && count($arguments) > 0) {
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
        /**
         * Parse the provided value with the defined parsers.
         * 
         * @param mixed $value The value to be parsed.
         * @return Zec
         */
        public function parse(mixed $value): Zec
        {
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
                if ($response['close']) {
                    break;
                }
            }

            $this->sendErrorsToParent();
            return $this;
        }
        /**
         * Parse the provided value and throw an exception if invalid.
         * 
         * @param mixed $value The value to be parsed and validated.
         * @return mixed The parsed value if valid.
         * @throws ZecError If the value is invalid.
         */
        public function parseOrThrow(mixed $value): mixed
        {
            $this->parse($value);
            if (!$this->isValid()) {
                $this->throwErrors();
            }
            return $this->getValue();
        }
    }
}
