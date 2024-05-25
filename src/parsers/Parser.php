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
        
        public function __construct(string $name, array $args = [
            FK::PRIORITIZE => [],
            FK::PRIORITY => 10,
            FK::PARSER_ARGUMENTS => null,
            FK::DEFAULT_ARGUMENT => [],
            FK::PARSER_CALLBACK => null
        ]) {
            $this->_name = $name;

            $this->set_prioritize($args[FK::PRIORITIZE]);
            $this->set_priority_of_parser($args[FK::PRIORITY]);
            $this->init_argument(
                $args[FK::DEFAULT_ARGUMENT],
                $args[FK::PARSER_ARGUMENTS]
            );

            // assign parser callback
            $this->set_parser_callback($args[FK::PARSER_CALLBACK]);
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
            return new Parser($this->_name, [
                FK::PRIORITIZE => $this->_prioritize,
                FK::PRIORITY => $this->_priority,
                FK::ARGUMENT => $this->get_argument(),
                FK::PARSER_CALLBACK => $this->_parser_callback
            ]);
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


            // Retrieve arguments
            $default = $args['default'];
            $zod_owner = $args['owner'];

            // Get the path of the owner Zod instance
            
            $path = is_null(
                $zod_owner
            ) ? null : $zod_owner->get_path_string();
            


            // $has_to_parse_argument = !$zod_owner->get_config(CK::TRUST_ARGUMENTS); // TODO: send this information to 
            $argument = $this->get_argument($zod_owner);

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
                $this->set_lifecycle_state(LC::VALIDATE);
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