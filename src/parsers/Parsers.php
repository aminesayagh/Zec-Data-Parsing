<?php

use Zod\Parser;

if(!class_exists('Parsers')) {
    class Parsers {
        private array $_parsers = [];   
        private bool $_is_sorted = false;
        public function add_parser(Parser $parser): ?Parser {
            if($this->has_parser($parser)) {
                return null;
            } 

            $this->_parsers[] = $parser;
            $this->_is_sorted = false;
            return $parser;
        }
        public function has_parser(Parser $parser) {
            return in_array($parser, $this->_parsers);
        }
        public function sort_parsers() : void {
            usort($this->_parsers, function (Parser $a, Parser $b): int {
                return $a->get_priority_of_parser() - $b->get_priority_of_parser();
            });
            $this->_is_sorted = true;
        }
        public function get_parsers(): array {
            if (!$this->_is_sorted) {
                $this->sort_parsers();
            }
            return $this->_parsers;
        }
    }
}