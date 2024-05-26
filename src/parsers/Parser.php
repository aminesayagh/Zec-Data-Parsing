<?php
declare(strict_types=1);

namespace Zec;

use Exception;
use Zec\FIELD as FK;
use Zec\CONFIG_KEY as CK;
use Zec\LIFECYCLE_PARSER as LC;

if (!class_exists('Parser')) {
    class Parser {
        use ParserArgument, ParserOrder, ParserPriority, ParserLifecycle;
        private ?string $_name = null;
        private array $_prioritize = [];
        private mixed $_parser_callback = null;
        
        public function __construct(string $name, array $args = []) {
            $default_args = [
                FK::PRIORITIZE => [],
                FK::PRIORITY => self::$DEFAULT_PRIORITY,
                FK::PARSER_ARGUMENTS => null,
                FK::DEFAULT_ARGUMENT => [],
                FK::PARSER_CALLBACK => null
            ];
            $args = array_merge($default_args, $args);

            $this->_name = $name;

            $this->set_prioritize($args[FK::PRIORITIZE]);
            $this->set_priority_of_parser($args[FK::PRIORITY]);
            $this->init_argument(
                $args[FK::DEFAULT_ARGUMENT],
                $args[FK::PARSER_ARGUMENTS]
            );

            // assign parser callback
            $this->set_parser_callback($args[FK::PARSER_CALLBACK]);

            $this->set_lifecycle_state(LC::BUILD);
        }
        // constructor of copy of the parser
        public function __get($name) {
            switch ($name) {
                case 'name':
                    return $this->_name;
                case 'prioritize':
                    return $this->_prioritize;
                case 'priority':
                    return $this->_priority;
                case 'order_of_parsing':
                    return $this->_order_of_parsing;
                case 'parser_arguments':
                    return $this->_parser_arguments;
                case 'default_argument':
                    return $this->_default_argument;
                case 'parser_callback':
                    return $this->_parser_callback;
                default:
                    throw new Exception("Property $name not found", $name);
            }
        }
        /**
         * Clones the current parser object.
         *
         * @return Parser The cloned parser object.
         */
        public function clone(): Parser { 
            return (new Parser($this->_name, [
                FK::PRIORITIZE => $this->_prioritize,
                FK::PRIORITY => $this->_priority,
                FK::PARSER_ARGUMENTS => $this->_parser_arguments,
                FK::DEFAULT_ARGUMENT => $this->_default_argument,
                FK::PARSER_CALLBACK => $this->_parser_callback, 
            ]))->set_argument($this->get_argument());
        }
        public function get_config() {
            return array_merge([
                FK::PRIORITIZE => $this->_prioritize,
                FK::PARSER_CALLBACK => $this->_parser_callback
            ], $this->_argument_parser->get_argument());
        }
        private function _proxy_parser_argument(array $args) {
            $args = is_null($args) ? [] : $args;
            $default = isset($args['default']) ? $args['default'] : null; // default value of the content to parser
            $owner = isset($args['owner']) ? $args['owner'] : null; // owner of the parser

            // if the owner is not an instance of Zec, set it to null
            $owner = $owner instanceof Zec ? $owner : null;

            if (is_null($owner)) {
                throw new Exception('The owner field must be an instance of Zec');
            }
            return [
                'default' => $default,
                'owner' => $owner
            ];
        }
        static function proxy_get_arg(array $args): array {
            $args = is_null($args) ? [] : $args;
            $default = isset($args['default']) ? $args['default'] : null; // default value of the content to parser
            $owner = isset($args['owner']) ? $args['owner'] : null; // owner of the parser

            // if the owner is not an instance of Zec, set it to null
            $owner = $owner instanceof Zec ? $owner : null;

            if (is_null($owner)) {
                throw new Exception('The owner field must be an instance of Zec');
            }
            return [
                'default' => $default,
                'owner' => $owner
            ];
        }
        static function proxy_set_arg(mixed $default, Zec $owner): array {
            return [
                'default' => $default,
                'owner' => $owner
            ];
        }
        static function proxy_response_zod(bool $is_valid, bool $close = false): array {
            return [
                'is_valid' => $is_valid,
                'close' => $close
            ];
        }
        public function parse(mixed $value, array $args): array {
            if (!is_callable($this->_parser_callback)) {
                throw new Exception('The parser_callback field must be a callback function');
            }
            $this->set_lifecycle_state(LC::PARSE);

            $args = $this->_proxy_parser_argument($args);
            
            $default = $args['default'];
            $zec_owner = $args['owner'];
            
            $argument = $this->get_argument($zec_owner); // get the argument of the parser, and check the config of the zec_owner

            // Call the parser callback function
            $response = call_user_func($this->_parser_callback, [
                'value' => $value,
                'default' => $default, // default value of the parser
                'argument' => $argument,
                'owner' => $zec_owner
            ]);

            $path = is_null(
                $zec_owner
            ) ? null : $zec_owner->get_pile_string();

            if (is_string($response)) {
                $zec_owner->set_error(new ZecError(
                    ZecError::generate_message($response, $path)
                ));
                return self::proxy_response_zod(false);
            } else if (is_array($response)) {
                foreach ($response as $value) {
                    $zec_owner->set_error(new ZecError(
                        ZecError::generate_message($value, $path)
                    ));
                }
                return self::proxy_response_zod(false);
            } else if (is_bool($response) && $response == true) {
                $this->set_lifecycle_state(LC::VALIDATE)->set_lifecycle_state(LC::FINALIZE);
                return self::proxy_response_zod(true);
            } else if (is_bool($response) && $response == false) {
                return self::proxy_response_zod(true, true);
            }

            throw new Exception('The parser_callback field must return a string or a boolean');
        }
        private function set_prioritize(array $prioritize): Parser {
            if (!isset($prioritize)) {
                throw new Exception('The accept field is already set');
            }
            if (!is_array($prioritize)) {
                throw new Exception('The accept field must be an array');
            }

            $this->_prioritize = $prioritize;
            return $this;
        }
        /**
         * Sets the parser callback function for the Parser.
         *
         * @param callable $parser The callback function to set as the parser.
         * @return Parser Returns the current instance of the Parser.
         * @throws ZecError If the provided $parser is not a valid callback function.
         */
        private function set_parser_callback(callable $parser): Parser {
            // if $this->_parser is not a callback function, return an error
            if (!is_callable($parser)) {
                throw new Exception('The parser field must be a callback function');
            }
            $this->_parser_callback = $parser;
            return $this;
        }
    }
}