<?php
declare(strict_types=1);

namespace Zod;

use BadMethodCallException;
use Zod\PARSERS_KEY as PK; // PK: Parser Key

require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';


if(!class_exists('Zod')) {
    class Zod extends Parsers {
        use ZodErrors, ZodPath, ZodConfigs, ZodUtils, ZodDefault, ZodValue, ZodParent;        
        public function __construct(Zod|array|null $args = null) {
            parent::__construct();
            // if type of args is Zod then clone it as a parent
            $parent = is_zod($args) ? $args : null;
            if(!is_null($parent)) {
                $this->_clone_parent($parent);
            }

            $this->init_config($args);
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
        /**
         * Dynamically handles method calls for the Zod class.
         *
         * @param string $name The name of the method being called.
         * @param mixed $arguments The arguments passed to the method.
         * @return mixed The result of the method call.
         * @throws ZodError If the method or parser is not found.
         */
        public function __call(string $name, ?array $arguments): mixed{

            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                $parser = bundler()->get_parser($name);
                
                if(is_array($arguments) && count($arguments) > 0) {
                    $arguments = $arguments[0];
                    $parser->set_argument($arguments);
                }
                
                $this->add_parser($parser);
                return $this;
            }

            throw new BadMethodCallException("Method $name not found");
        }
        static function proxy_set_arg(mixed $default, ?Zod $parent = null): array {
            return [
                'default' => $default,
                'parent' => $parent
            ];
        }
        public static function proxy_get_arg(?array $args): array {
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
        
        public function parse(mixed $value, array $args): Zod {

            // clean errors
            $this->clear_errors();

            // set the value
            $this->set_value($value);

            if (is_null($args)) {
                $args = [];
            }
            $args = self::proxy_get_arg($args);

            $this->set_default($args['default']);
            $this->_clone_parent($args['parent']);

            $has_required = $this->has_parser_key(PK::REQUIRED);
            if (!$has_required && is_null($value)) {
                return $this;
            }
            
            foreach ($this->list_parsers() as $parser) {
                // identify $parser as a instance of Parser
                if (!is_a($parser, Parser::class)) {
                    continue;
                } 
                $this->set_key_parser($parser->name);
                $parser->parse($this->_value, Parser::proxy_set_arg($this->_default, $this));
            }

            $this->_send_errors_to_parent();

            return $this;
        }
        /**
         * Sets the default value for the Zod instance.
         *
         * @param mixed $default The default value to be set.
         * @return Zod The updated Zod instance.
         */
        public function set_default(mixed $default): Zod {
            if(is_null($default)) {
                return $this;
            }
            $parser_of_default = clone $this;
            $parser_of_default->parse_or_throw($default);

            $this->_default = $default;
            return $this;
        }
        /**
         * Parses the given value and throws an error if it is not valid.
         *
         * @param mixed $value The value to parse.
         * @param mixed $default The default value to use if the parsed value is invalid.
         * @return Zod The parsed value.
         * @throws mixed The error(s) if the parsed value is invalid.
         */
        public function parse_or_throw(mixed $value, mixed $default = null, Zod $parent = null): mixed {
            $this->parse($value, Zod::proxy_set_arg($default, null));
            if (!$this->is_valid()) {
                throw new ZodError($this->message_errors());
            }
            return $this->get_value();
        }
    }
}

if (!function_exists('zod')) {
    /**
     * Returns an instance of the Zod class.
     *
     * @param array $parsers The array of parsers to assign to the Zod instance.
     * @param ZodErrors $errors The array of errors to assign to the Zod instance.
     * @return Zod The instance of the Zod class.
     */
    function zod(Zod|array|null $args = null): Zod {
        return new Zod($args);
    }
}

if (!function_exists('is_zod')) {
    /**
     * Checks if the given value is an instance of the Zod class.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is an instance of Zod, false otherwise.
     */
    function is_zod($value): bool {
        return is_a($value, \Zod\Zod::class);
    }
}


if (!function_exists('z')) {
    
    function z(Zod|array|null $args = null): Zod {
        return zod($args);
    }
}