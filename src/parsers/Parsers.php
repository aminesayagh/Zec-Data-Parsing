<?php

namespace Zod;
use Zod\CaretakerParsers;
use Zod\Parser;
require_once ZOD_PATH . '/src/CaretakerParsers.php';

if(!class_exists('Parsers')) {
    class Parsers extends CaretakerParsers {
        private bool $_is_sorted = false;
        public function __construct(array $parsers = []) {
            parent::__construct($parsers);
        }
        // override the add_parser method to set the _is_sorted field to false when a new parser is added
        public function add_parser(Parser $parser): ?Parser { 
            // check if the parser is already in the parsers array
            if ($this->has_parser_key($parser->key)) {
                return null;
            }
            // add the parser to the parsers array
            $new_parser = parent::add_parser($parser);
            if (is_null($new_parser)) {
                return null;
            }
            $this->_is_sorted = false;
            return $new_parser;
        }
        public function sort_parsers() : void {
            // TODO: Refactoring, sort by order of parsing
            usort($this->parsers, function($a, $b) {
                return $a->get_order_of_parsing() <=> $b->get_order_of_parsing();
            });
        
            $this->_is_sorted = true;
        }
        public function get_parsers(): array {
            if (!$this->_is_sorted) {
                $this->sort_parsers();
            }
            return $this->parsers;
        }
    }
}