<?php
declare(strict_types=1);

namespace Zod;

if (!class_exists('Parser')) {
    interface IParser {
        public function __construct(string $name, array $args = [
            'accept_state' => [],
            'priority' => 10,
            'is_init_state' => null,
            'parser_arguments' => null,
            'default_argument' => [],
            'parser_callback' => null
        ]);
        public function __get(string $name);
        public function clone(?Parser $parser = null): Parser;
        public function parse(mixed $value, array $args);
        public function set_accept_state(array $accept_state): Parser;
        public function set_is_init_state(bool $is_init_state): Parser;
        public function set_arguments(array $argument): Parser;
        public function set_priority_of_parser(int $priority): Parser;
        public function calc_order_of_parsing(CaretakerParsers &$parserRules, array $parents = []): ?int;
    }


    class Parser {
        private ?string $_name = null;
        private array $_accept_state = [];
        private int $_priority = 10; // calculate the priority of a parser on concurrency with the other parser with the same key, using the field priority 
        private int $_order_of_parsing = 0; // Calculate the order of execution of a parser on concurrency with the other parsers
        private ?bool $_is_init_state = null;
        private callable|null $_parser_arguments = null;
        private array $_default_argument = []; 
        private array $_argument = [];
        private callable|null $_parser_callback = null;

        
        public function __construct(string $name, array $args = [
            'accept_state' => [],
            'priority' => 10,
            'is_init_state' => null,
            'parser_arguments' => null,
            'default_argument' => [],
            'parser_callback' => null
        ]) {
            $this->_name = $name;

            $this->set_accept_state($args['accept_state']);
            $this->set_priority_of_parser($args['priority']);
            $this->set_is_init_state($args['is_init_state']);
            $this->set_structure_arguments($args['default_argument'], $args['parser_arguments']);
            $this->set_parser_callback($args['parser_callback']);
        }
        // constructor of copy of the parser
        public function __get($name) {
            switch ($name) {
                case 'name':
                    return $this->_name;
                case 'accept_state':
                    return $this->_accept_state;
                case 'priority':
                    return $this->_priority;
                case 'order_of_parsing':
                    return $this->_order_of_parsing;
                case 'is_init_state':
                    return $this->_is_init_state;
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
         * @param Parser|null $parser The parser object to clone. If null, the current parser object is returned.
         * @return Parser The cloned parser object.
         */
        public function clone(?Parser $parser = null): Parser {
            if (is_null($parser)) {
                return $this;
            }
            $this->set_accept_state($parser->_accept_state);
            
            $this->_priority = $parser->_priority;
            $this->set_priority_of_parser($parser->_priority);
            $this->_order_of_parsing = $parser->_order_of_parsing;
            $this->_is_init_state = $parser->_is_init_state;
            $this->set_structure_arguments($parser->_default_argument, $parser->_parser_arguments);

            $this->_parser_callback = $parser->_parser_callback;
            return $this;
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
            $before_parser = $args['before_parser'];
            $default = $args['default'];

            // Get the path of the owner Zod instance
            $path = $zod_owner->get_path();

            // Call the parser callback function
            $response = call_user_func($this->_parser_callback, [
                'value' => $value,
                'default' => $default, // default value of the parser
                'argument' => $this->_argument,
                'default_argument' => $this->_default_argument,
                'before_valid_parser' => $before_parser,
                'owner' => $zod_owner
            ]);

            // Handle the response based on its type
            if (is_string($response)) {
                // If the response is a string, set an error and return invalid result
                $zod_owner = $zod_owner->set_error(
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
                return [
                    'is_valid' => true,
                ];
            }

            // If the response is not a valid type, throw an error
            throw new ZodError('The parser_callback field must return a string or a boolean', 'parser_callback');
        }
        public function set_accept_state(array $accept_state): Parser {
            // if $this->accept is not null, return an error
            if (isset($this->_accept_state)) {
                throw new ZodError('The accept field is already set', 'accept');
            }
            $this->_accept_state = $accept_state;
            return $this;
        }
        public function set_is_init_state(bool $is_init_state): Parser {
            // if $this->_is_init_state is not nll, return an error
            if (isset($this->_is_init_state)) {
                throw new ZodError('The is_init_state field is already set', 'is_init_state');
            }
            // if $is_init_state is not a boolean, return an error
            if (!is_bool($is_init_state)) {
                throw new ZodError('The is_init_state field must be a boolean', 'is_init_state');
            }
            $this->_is_init_state = $is_init_state;
            return $this;
        }
        // -------------- ARGUMENTS OF THE PARSER -------------- //
        /**
         * Parses an argument using a callback function.
         *
         * @param array $argument The argument to be parsed.
         * @param callable $parser_argument The callback function used to parse the argument.
         * @return array The parsed argument.
         * @throws ZodError If the argument is not an array or if the parser_argument is not a callback function.
         * @throws ZodError If the parsed argument is an instance of ZodError.
         */
        private function _parse_argument(array $argument, callable $parser_argument): array {
            if (!is_array($argument)) {
                throw new ZodError('The argument field must be an array', 'argument');
            }
            if (!is_callable($parser_argument)) {
                throw new ZodError('The parser_argument field must be a callback function', 'parser_argument');
            }

            $zod_parser_argument = $parser_argument();
            $valid_argument = $zod_parser_argument->parse($argument);

            if ($valid_argument instanceof ZodError) {
                throw $valid_argument;
            }
            return $valid_argument;
        }
        /**
         * Sets the structure arguments for the parser.
         *
         * @param array $default The default field values.
         * @param callable $parser_arguments The callback function for parsing the arguments.
         * @return Parser The updated Parser instance.
         * @throws ZodError If the default field is not an array or if the parser_arguments field is not a callback function.
         */
        private function set_structure_arguments(array $default = [], callable $parser_arguments): Parser {
            if (!is_array($default)) {
                throw new ZodError('The default field must be an array', 'default');
            }
            if (!is_callable($parser_arguments)) {
                throw new ZodError('The parser_arguments field must be a callback function', 'parser_arguments');
            }


            $this->_default_argument = $this->_parse_argument($default, $parser_arguments);
            $this->_parser_arguments = $parser_arguments;
            return $this;
        }
        /**
         * Sets the arguments for the parser.
         *
         * @param array $argument The array of arguments to set.
         * @return Parser Returns the current instance of the Parser.
         * @throws ZodError Throws a ZodError if the argument field is not an array or if the parser_arguments field is not set.
         */
        public function set_arguments(array $argument): Parser {
            if (!is_array($argument)) {
                throw new ZodError('The argument field must be an array', 'argument');
            }
            if (!isset($this->_parser_arguments)) {
                throw new ZodError('The parser_arguments field must be set', 'parser_arguments');
            }
            
            $argument_merge_by_default = array_merge($this->_default_argument, $argument);
            $this->_argument = $this->_parse_argument($argument_merge_by_default, $this->_parser_arguments);
            return $this;
        }
        // -------------- ARGUMENTS OF THE PARSER -------------- //
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
        public function set_priority_of_parser(int $priority): Parser {
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
            return null;
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