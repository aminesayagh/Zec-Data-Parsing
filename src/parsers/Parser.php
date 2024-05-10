<?php

namespace Zod;

if (!class_exists('Parser')) {
    class Parser {
        private ?string $_key = null;
        private ?array $_accept = null;
        private int $_priority = 10; // calcul the priority of a parser on concurence with the other parser with the same key, using the field priority 
        private int $_order_of_parsing = 0; // Calcul the order of executian of a parser on concurence with the other parsers
        private ?bool $_is_init_state = null;
        private $_parser_arguments = null;
        private array $_default_argument = [];
        private $_parser = null;
        public function __construct($key) {
            $this->_key = $key;
        }
        public  function get_key() {
            return $this->_key;
        }
        public function clone(?Parser $parser = null): Parser {
            if (is_null($parser)) {
                return $this;
            }
            $this->set_accept($parser->_accept);
            
            $this->_priority = $parser->_priority;
            $this->_order_of_parsing = $parser->_order_of_parsing;
            $this->_is_init_state = $parser->_is_init_state;
            $this->_parser_arguments = $parser->_parser_arguments;
            $this->_default_argument = $parser->_default_argument;
            $this->_parser = $parser->_parser;
            return $this;
        }
        public function set_accept(array $accept): Parser {
            // if $this->accept is not null, return an error
            if (isset($this->_accept)) {
                throw new ZodError('The accept field is already set', 'accept');
            }
            $this->_accept = $accept;
            return $this;
        }
        public function get_accept() {
            return $this->_accept;
        }
        public function set_is_init_state(bool $is_init_state): Parser {
            // if $this->_is_init_state is not nll, return an error
            if (isset($this->_is_init_state)) {
                throw new ZodError('The is_init_state field is already set', 'is_init_state');
            }
            $this->_is_init_state = $is_init_state;
            return $this;
        }
        public function get_init_state() {
            return $this->_is_init_state;
        }
        public function set_parser_arguments($parser_arguments): Parser {
            // if $this->_parser_arguments is not null, return an error
            if (isset($this->_parser_arguments)) {
                throw new ZodError('The parser_arguments field is already set', 'parser_arguments');
            }
            $this->_parser_arguments = $parser_arguments;
            return $this;
        }
        public function get_parser_arguments() {
            return $this->_parser_arguments;
        }
        // TODO: maybe a way to assign a new argument
        public function set_default_argument(array $default_argument): Parser {
            if (isset($this->_default_argument)) {
                throw new ZodError('The default_argument field is already set', 'default_argument');
            }
            $this->_default_argument = $default_argument;
            return $this;
        }
        public function get_default_argument() {
            return $this->_default_argument;
        }
        public function set_parser($parser): Parser {
            $this->_parser = $parser;
            return $this;
        }
        public function get_parser() {
            return $this->_parser;
        }
        public function set_priority_of_parser(int $priority): Parser {
            $this->_priority = $priority;
            return $this;
        }
        public function get_priority_of_parser() {
            return $this->_priority;
        }
        public function get_order_of_parsing() {
            return $this->_order_of_parsing;
        }
        // TODO: Continue here //
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