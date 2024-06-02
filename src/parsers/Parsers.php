<?php
declare(strict_types=1);

namespace Zec;
use Zec\CaretakerParsers;
use Zec\Parser;
use Zec\CONST\LIFECYCLE_PARSER as LC_P;

require_once ZEC_PATH . '/src/CaretakerParsers.php';

if(!class_exists('Parsers')) {
    abstract class Parsers extends CaretakerParsers {
        private bool $_is_sorted = false;
        public function __construct(array $parsers = []) {
            parent::__construct($parsers);
        }
        public function add_parser(Parser $parser): ?Parser { 
            if ($this->has_parser_key($parser->name)) {
                return null;
            }
            $new_parser = parent::add_parser($parser);
            if (is_null($new_parser)) {
                return null;
            }
            $this->_is_sorted = false;
            $new_parser->set_lifecycle_state(LC_P::ASSIGN);
            return $new_parser;
        }
        public function get_parser(string $name): ?Parser {
            $parser = parent::get_parser($name);
            $parser->set_lifecycle_state(LC_P::ASSIGN);
            return $parser;
        }
        public function sort_parsers() : void {
            usort($this->parsers, function($a, $b) {
                return $a->order_of_parsing <=> $b->order_of_parsing;
            });
        
            $this->_is_sorted = true;
        }
        public function list_parsers(): array {
            if (!$this->_is_sorted) {
                $this->sort_parsers();
            }
            return $this->parsers;
        }
    }
}