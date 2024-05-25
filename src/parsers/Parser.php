<?php
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;
use Zod\CONFIG_KEY as CK;
use Zod\LIFECYCLE_PARSER as LC;

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
                    throw new ZodError("Property $name not found", $name);
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
        /**
         * Identifies the parser key based on the name of the parser and the path to the parser.
         *
         * @param string $name_of_parser The name of the parser.
         * @param ZodPath|null $path_to_parser The path to the parser (optional).
         * @return string The identified parser key.
         */
        private function identify_parser_key(string $name_of_parser, string $path_to_parser = null): string {
            if (is_null($path_to_parser)) {
                return $name_of_parser;
            }
            return $path_to_parser . '/' . $name_of_parser;
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

            // if the owner is not an instance of Zod, set it to null
            $owner = $owner instanceof Zod ? $owner : null;

            if (is_null($owner)) {
                throw new ZodError('The owner field must be an instance of Zod', 'owner');
            }
            return [
                'default' => $default,
                'owner' => $owner
            ];
        }
        /**
         * Parses the given value using the provided parser callback function.
         *
         * @param mixed $value The value to be parsed.
         * @param array $args An array of arguments passed to the parser.
         * @param Zod $zod_owner The owner Zod instance.
         * @return array An array containing the validation result.
         * @throws ZodError If the parser_callback field is not a callback function or if it returns an invalid value.
         */
        public function parse(mixed $value, array $args): array {
            if (!is_callable($this->_parser_callback)) {
                throw new ZodError('The parser_callback field must be a callback function', 'parser_callback');
            }
            $this->set_lifecycle_state(LC::PARSE);

            $args = $this->_proxy_parser_argument($args);
            
            $default = $args['default'];
            $zod_owner = $args['owner'];

            // Get the path of the owner Zod instance
            
            $path = is_null(
                $zod_owner
            ) ? null : $zod_owner->get_pile_string();
            
            $argument = $this->get_argument($zod_owner); // get the argument of the parser, and check the config of the zod_owner

            // Call the parser callback function
            $response = call_user_func($this->_parser_callback, [
                'value' => $value,
                'default' => $default, // default value of the parser
                'argument' => $argument,
                'owner' => $zod_owner
            ]);

            // Handle the response based on its type
            if (is_string($response)) {
                $path_of_error = $this->identify_parser_key($this->_name, $path);
                $zod_owner->set_error(
                    new ZodError($response, $path_of_error)
                );
                return [
                    'is_valid' => false,
                ];
            } else if (is_array($response)) {

                // If the response is an array, set errors and return invalid result
                foreach ($response as $value) {
                    $error = new ZodError($value, $this->identify_parser_key($this->_name, $path) . '/' . $value);
                    $zod_owner->set_error($error);
                }
                return [
                    'is_valid' => false,
                ];
            } else if (is_bool($response) && $response == true) {
                $this->set_lifecycle_state(LC::VALIDATE)->set_lifecycle_state(LC::FINALIZE);
                return [
                    'is_valid' => true,
                ];
            } else if (is_bool($response) && $response == false) {
                return [
                    'is_valid' => true,
                    'close' => true
                ];
            }

            // If the response is not a valid type, throw an error
            throw new ZodError('The parser_callback field must return a string or a boolean', 'parser_callback');
        }
        private function set_prioritize(array $prioritize): Parser {
            if (!isset($prioritize)) {
                throw new ZodError('The accept field is already set', 'accept');
            }
            if (!is_array($prioritize)) {
                throw new ZodError('The accept field must be an array', 'accept');
            }

            $this->_prioritize = $prioritize;
            return $this;
        }
        /**
         * Sets the parser callback function for the Parser.
         *
         * @param callable $parser The callback function to set as the parser.
         * @return Parser Returns the current instance of the Parser.
         * @throws ZodError If the provided $parser is not a valid callback function.
         */
        private function set_parser_callback(callable $parser): Parser {
            // if $this->_parser is not a callback function, return an error
            if (!is_callable($parser)) {
                throw new ZodError('The parser field must be a callback function', 'parser');
            }
            $this->_parser_callback = $parser;
            return $this;
        }
    }
}