<?php
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;

if (!class_exists('Parser')) {
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

    class ArgumentParser {
        private array $_argument = [];
        private mixed $_parser_arguments = null;
        private bool $_is_valid_argument = false;
        private array $_default_argument = [];
        public function __construct(array $default_argument, callable $parser_arguments) {
            if (!is_array($default_argument)) {
                throw new ZodError('The default_argument field must be an array', 'default_argument');
            }
            if (!is_callable($parser_arguments)) {
                throw new ZodError('The parser_arguments field must be a callback function', 'parser_arguments');
            }

            $this->_default_argument = $default_argument;
            $this->_parser_arguments = $parser_arguments;
        }
        public function get_config(): array {
            return [
                'default_argument' => $this->_default_argument,
                'parser_arguments' => $this->_parser_arguments
            ];
        }
        public function get_argument(): array {
            if (!$this->_parser_arguments) {
                throw new ZodError('The parser_arguments field must be set', 'parser_arguments');
            }
            $merged_argument = array_merge($this->_default_argument, $this->_argument);
            if (is_bool($this->_is_valid_argument) && !$this->_is_valid_argument) {
                $argument_zod_validator = call_user_func($this->_parser_arguments);
                $argument = $argument_zod_validator->parse_or_throw($merged_argument);
                $this->_is_valid_argument = true;
            } else {
                $argument = $merged_argument;
            }
            return $argument;
        }
        public function set_argument(array $argument): ArgumentParser {
            if (!is_array($argument)) {
                throw new ZodError('The argument field must be an array', 'argument');
            }
            $this->_argument = $argument;
            $this->_is_valid_argument = false;
            return $this;
        }
    }
    class Parser {
        private ?string $_name = null;
        private array $_prioritize = [];
        private int $_priority = 10; // calculate the priority of a parser on concurrency with the other parser with the same key, using the field priority 
        private int $_order_of_parsing = 0; // Calculate the order of execution of a parser on concurrency with the other parsers
        private bool $_is_validate_parser = false;
        private callable|null $_parser_callback = null;
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
            $this->set_priority_of_parser($args['priority']);

            // assign argument
            if (is_null($args['argument'])) {
                $this->_argument_parser = new ArgumentParser($args[FK::DEFAULT_ARGUMENT], $args[FK::PARSER_ARGUMENTS]);
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
        private function identify_parser_key(string $name_of_parser, ZodPath $path_to_parser = null) {
            if (is_null($path_to_parser)) {
                return $name_of_parser;
            }
            return $path_to_parser->get_path_string() . '/' . $name_of_parser;
        }
        public function initialize(): void {
            if ($this->_is_init) {
                throw new ZodError('The parser is already initialized', 'parser');
            }
            $this->_is_init = true;
        }
        public function get_argument(): array {
            return $this->_argument_parser->get_argument();
        }
        public function set_argument(array $argument): Parser {
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
            $path = $zod_owner->get_path_string();

            // Call the parser callback function
            $response = call_user_func($this->_parser_callback, [
                'value' => $value,
                'default' => $default, // default value of the parser
                'argument' => $this->get_argument(),
                'owner' => $zod_owner
            ]);

            // Handle the response based on its type
            if (is_string($response)) {
                // If the response is a string, set an error and return invalid result
                $zod_owner->set_error(
                    new ZodError($response, $this->identify_parser_key($this->_name, $path))
                );
                return [
                    'is_valid' => false,
                ];
            } else if (is_array($response)) {
                // If the response is an array, set errors and return invalid result
                foreach ($response as $value) {
                    $error = new ZodError($value, $this->identify_parser_key($this->_name, $path));
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
        // TODO: Continue here //
        // TODO: Calculate the order of parsing of a parser
        // TODO: Look for possible conflicts between parsers
        public function calc_order_of_parsing(CaretakerParsers &$parserRules, array $parents = []): ?int {
            return 1;
            // if (!isset($this->_accept)) {
            //     return 0;
            // }

            // $priority_of_execution = 0;

            // foreach ($this->_accept as $a) {
            //     // Check if the parser has a priority
            //     if (isset($this->_order_of_parsing)) {
            //         $priority_of_execution = max($priority_of_execution, $this->_order_of_parsing);
            //     } else {
            //         if (in_array($a, $parents)) {
            //             continue; // avoid infinite loop in case of circular dependency
            //         }
            //         $parents[] = $this->_name;
            //         $parser = $parserRules->get_parser($a);
            //         $priority_of_execution = max($priority_of_execution, $parserRules->cal_priority($parserRules, $parents) + 1);
            //     }
            // }
        
            // $this->set_priority_of_parser($priority_of_execution + 1);
            // return $this->_order_of_parsing;
        }
    }
}