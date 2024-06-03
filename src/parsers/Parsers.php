<?php
declare(strict_types=1);

namespace Zec;
use Zec\CaretakerParsers;
use Zec\Parser;
use Zec\CONST\LIFECYCLE_PARSER as LC_P;

require_once ZEC_PATH . '/src/CaretakerParsers.php';

if(!class_exists('Parsers')) {
    abstract class Parsers extends CaretakerParsers {
        private bool $is_sorted = false;
        public function __construct(array $parsers = []) {
            parent::__construct($parsers);
        }
        public function addParser(Parser $parser): ?Parser { 
            if ($this->hasParserKey($parser->name)) {
                return null;
            }
            $new_parser = parent::addParser($parser);
            if (is_null($new_parser)) {
                return null;
            }
            $this->is_sorted = false;
            $new_parser->setLifecycleState(LC_P::ASSIGN);
            return $new_parser;
        }
        public function getParser(string $name): ?Parser {
            $parser = parent::getParser($name);
            $parser->setLifecycleState(LC_P::ASSIGN);
            return $parser;
        }
        public function sort_parsers() : void {
            usort($this->parsers, function($a, $b) {
                return $a->order_of_parsing <=> $b->order_of_parsing;
            });
        
            $this->is_sorted = true;
        }
        public function listParsers(): array {
            if (!$this->is_sorted) {
                $this->sort_parsers();
            }
            return $this->parsers;
        }
    }
}