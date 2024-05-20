<?php
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;

if(!interface_exists('IParser')) {
    interface IParser {
        public function __construct(string $name, array $args = [
            FK::PRIORITIZE => [],
            FK::PRIORITY => 10,
            FK::PARSER_ARGUMENTS => null,
            FK::DEFAULT_ARGUMENT => [],
            FK::PARSER_CALLBACK => null
        ]);
        public function __get(string $name);
        public function clone(?Parser $parser = null): Parser;
        public function parse(mixed $value, array $args);
        public function set_accept_state(array $accept_state): Parser;
        public function set_argument(array $argument): Parser;
        public function set_priority_of_parser(int $priority): Parser;
        public function calc_order_of_parsing(CaretakerParsers &$parserRules, array $parents = []): ?int;
    }
}

if (!class_exists('ArgumentParser')) {
    class ArgumentParser {
        private string $_main_key;
        private array $_argument = [];
        private mixed $_parser_arguments;
        private mixed $_handler_argument = null;
        private bool $_is_valid_argument = false;
        private array $_default_argument = [];
        public function __construct(string $main_key, array $default_argument, callable $parser_arguments) {
            if (!is_array($default_argument)) {
                throw new ZodError('The default_argument field must be an array', 'default_argument');
            }
            if (!is_callable($parser_arguments)) {
                throw new ZodError('The parser_arguments field must be a callback function', 'parser_arguments');
            }

            $this->_main_key = $main_key;
            $this->_default_argument = $default_argument;
            $this->_parser_arguments = $parser_arguments;
        }
        public function get_config(): array {
            return [
                'default_argument' => $this->_default_argument,
                'parser_arguments' => $this->_parser_arguments
            ];
        }
        public function merge_argument() {
            $merged_argument = array_merge($this->_default_argument, $this->_argument);
            return $merged_argument;
        }
        public function valid_argument() {
            $merged_argument = $this->merge_argument();

            if (!$this->_is_valid_argument) {
                $argument_zod_validator = call_user_func($this->_parser_arguments, z()->set_trust_arguments(true));
                log_msg('Argument: ' . json_encode($merged_argument) . ' for ' . $this->_main_key . ' argument parser');
                $argument_zod_validator->parse_or_throw($merged_argument);
                $this->_is_valid_argument = true;
            }
            return $merged_argument;
        }
        public function get_argument(): array {
            if (!$this->_parser_arguments) {
                throw new ZodError('The parser_arguments field must be set', 'parser_arguments');
            }
            return $this->merge_argument();
        }
        public function set_argument(mixed $argument): ArgumentParser {
            $this->_argument = $this->_default_argument_handler($argument);
            $this->_is_valid_argument = false;
            return $this;
        }
        private function _default_argument_handler(mixed $argument): array {
            if (is_null($argument)) return [];
            

            if (is_array($argument)) {
                $is_associative = false;
                
                foreach ($argument as $key => $value) {
                    if (!is_int($key)) {
                        $is_associative = true;
                        break;
                    }
                }
                echo 'Argument Is associative: ' . json_encode($argument) . ' ' . ($is_associative ? 'true' : 'false') . ' ' . $this->_main_key . PHP_EOL;
                
                if (!$is_associative) {
                    return [
                        $this->_main_key => $argument
                    ];
                }

                $is_associative_of_zod = array_reduce($argument, function($carry, $item) {
                    return $carry && $item instanceof Zod;
                }, true);
                // echo $is_associative_of_zod ? 'true' : 'false' . PHP_EOL;
                if ($is_associative_of_zod) {
                    return [
                        $this->_main_key => $argument
                    ];
                }
                return $argument;
            }

            return [
                $this->_main_key => $argument
            ];
        }
    }
}
if (!class_exists('Parser')) {
    class Parser {
        private ?string $_name = null;
        private array $_prioritize = [];
        private int $_priority = 10; // calculate the priority of a parser on concurrency with the other parser with the same key, using the field priority 
        private int $_order_of_parsing = 0; // Calculate the order of execution of a parser on concurrency with the other parsers
        private bool $_is_validate_parser = false;
        private mixed $_parser_callback = null;
        private ArgumentParser $_argument_parser;
        private bool $_is_init = false; // check if the parser is initialized on a zod instance
        
        public function __construct(string $name, array $args = [
            FK::PRIORITIZE => [],
            FK::PRIORITY => 10,
            FK::PARSER_ARGUMENTS => null,
            FK::DEFAULT_ARGUMENT => [],
            FK::PARSER_CALLBACK => null,
            'argument' => null
        ]) {
            $this->_name = $name;

            $this->set_prioritize($args[FK::PRIORITIZE]);
            $this->set_priority_of_parser($args[FK::PRIORITY]);

            if (!array_key_exists('argument', $args) || is_null($args['argument'])) {
                $this->_argument_parser = new ArgumentParser($this->name, $args[FK::DEFAULT_ARGUMENT], $args[FK::PARSER_ARGUMENTS]);
            } else if ($args['argument'] instanceof ArgumentParser) {
                $this->_argument_parser = $args['argument'];
            } else {
                throw new ZodError('The argument field must be an instance of ArgumentParser', 'argument');
            }

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
                'argument' => $this->_argument_parser,
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
        public function initialize(): void {
            if ($this->_is_init) {
                throw new ZodError('The parser is already initialized', 'parser');
            }
            $this->_is_init = true;
        }
        public function get_argument(bool $parse_argument = true): array {
            if ($parse_argument) {
                $this->_argument_parser->valid_argument();
            }
            return $this->_argument_parser->get_argument();
        }
        public function set_argument(mixed $argument): Parser {
            $this->_argument_parser->set_argument($argument);
            return $this;
        }
        public function is_priority(int $priority_compare): bool {
            if ($this->_priority < $priority_compare) {
                return true;
            } else {
                return false;
            }
        }
        public function get_config() {
            return array_merge([
                FK::PRIORITIZE => $this->_prioritize,
                FK::PARSER_CALLBACK => $this->_parser_callback
            ], $this->_argument_parser->get_config());
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
        public function parse(mixed $value, array $args, Zod $zod_owner): array {
            if (!is_callable($this->_parser_callback)) {
                throw new ZodError('The parser_callback field must be a callback function', 'parser_callback');
            }


            // Retrieve arguments
            $default = $args['default'];

            // Get the path of the owner Zod instance
            
            $path = is_null(
                $zod_owner
            ) ? null : $zod_owner->get_path_string();


            $has_to_parse_argument = $zod_owner->trust_arguments;
            $argument = $this->get_argument(!$has_to_parse_argument);

            log_msg('Argument: ' . json_encode($argument) . ' for ' . $this->_name . ' parser');

            // Call the parser callback function
            $response = call_user_func($this->_parser_callback, [
                'value' => $value,
                'default' => $default, // default value of the parser
                'argument' => $argument,
                'owner' => $zod_owner
            ]);

            // Handle the response based on its type
            if (is_string($response)) {
                echo 'Response: ' . $response . PHP_EOL;
                $zod_owner->set_error(
                    new ZodError($response, $this->identify_parser_key($this->_name, $path) . '/' . $this->_name)
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
                // If the response is a boolean and true, return valid result
                $this->_is_validate_parser = true;
                return [
                    'is_valid' => true,
                ];
            }

            // If the response is not a valid type, throw an error
            throw new ZodError('The parser_callback field must return a string or a boolean', 'parser_callback');
        }
        public function is_validate_parser(): bool {
            return $this->_is_validate_parser;
        }
        public function set_order_parsing(int $order): Parser {
            $this->_order_of_parsing = $order;
            return $this;
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
        /**
         * Sets the priority of the parser.
         *
         * @param int $priority The priority value to set.
         * @return Parser The updated Parser instance.
         * @throws ZodError If the priority field is not an integer.
         */
        private function set_priority_of_parser(int $priority): Parser {
            if (!is_int($priority)) {
                throw new ZodError('The priority field must be an integer', 'priority');
            }
            $this->_priority = $priority;
            return $this;
        }
        public function increment_order(): int {
            $this->_order_of_parsing++;
            return $this->_order_of_parsing;
        }
        public function get_order_parsing(): int {
            return $this->_order_of_parsing;
        }
    }
}