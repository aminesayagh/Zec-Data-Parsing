<?php

namespace Zod;
use Zod\CaretakerParsers;
use Zod\Parser;
require_once ZOD_PATH . '/src/CaretakerParsers.php';

if(!class_exists('Parsers')) {
    class Parsers extends CaretakerParsers {
        private bool $_is_sorted = false;
        public function add_parser(Parser $parser): ?Parser {
            if($this->has_parser($parser)) {
                return null;
            }

            $this->parsers[] = $parser;
            $this->_is_sorted = false;
            return $parser;
        }
        public function sort_parsers() : void {
            usort($this->parsers, function (Parser $a, Parser $b): int {
                return $a->get_priority_of_parser() - $b->get_priority_of_parser();
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