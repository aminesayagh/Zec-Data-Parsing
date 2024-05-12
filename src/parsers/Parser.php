<?php

namespace Zod;

if (!class_exists('Parser')) {
    class Parser {
        private ?string $_key = null;
        private array $_accept_state = [];
        private int $_priority = 10; // calculate the priority of a parser on concurrency with the other parser with the same key, using the field priority 
        private int $_order_of_parsing = 0; // Calculate the order of execution of a parser on concurrency with the other parsers
        private ?bool $_is_init_state = null;
        private callable|null $_parser_arguments = null;
        private array $_default_argument = []; 
        private array $_argument = [];
        private callable|null $_parser_callback = null;
        
        public function __construct(string $key, array $args = [
            'accept_state' => [],
            'priority' => 10,
            'is_init_state' => null,
            'parser_arguments' => null,
            'default_argument' => [],
            'parser_callback' => null
        ]) {
            $this->_key = $key;

            $this->set_accept_state($args['accept_state']);
            $this->set_priority_of_parser($args['priority']);
            $this->set_is_init_state($args['is_init_state']);
            $this->set_structure_arguments($args['default_argument'], $args['parser_arguments']);
            $this->set_parser_callback($args['parser_callback']);
        }
        // constructor of copy of the parser
        public function __get($name) {
            switch ($name) {
                case 'key':
                    return $this->_key;
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
        private function set_parser_callback(callable $parser): Parser {
            // if $this->_parser is not a callback function, return an error
            if (!is_callable($parser)) {
                throw new ZodError('The parser field must be a callback function', 'parser');
            }
            $this->_parser_callback = $parser;
            return $this;
        }
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
            //         $parents[] = $this->_key;
            //         $parser = $parserRules->get_parser($a);
            //         $priority_of_execution = max($priority_of_execution, $parserRules->cal_priority($parserRules, $parents) + 1);
            //     }
            // }
        
            // $this->set_priority_of_parser($priority_of_execution + 1);
            // return $this->_order_of_parsing;
        }
    }
}