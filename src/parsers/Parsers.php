<?php
declare(strict_types=1);

namespace Zod;
use Zod\CaretakerParsers;
use Zod\Parser;
use Zod\LIFECYCLE_PARSER as LC_P;

require_once ZOD_PATH . '/src/CaretakerParsers.php';

if(!class_exists('Parsers')) {
    /**
     * Class Parsers
     * 
     * This class extends the CaretakerParsers class and represents a collection of parsers.
     */
    abstract class Parsers extends CaretakerParsers {
        private bool $_is_sorted = false;

        /**
         * Parsers constructor.
         * 
         * @param array $parsers An array of parsers.
         */
        public function __construct(array $parsers = []) {
            parent::__construct($parsers);
        }
        /**
         * Adds a parser to the parsers array.
         *
         * @param Parser $parser The parser to be added.
         * @return Parser|null The added parser if successful, null otherwise.
         */
        public function add_parser(Parser $parser): ?Parser { 
            // check if the parser is already in the parsers array
            if ($this->has_parser_key($parser->name)) {
                return null;
            }
            // add the parser to the parsers array
            $new_parser = parent::add_parser($parser);
            if (is_null($new_parser)) {
                return null;
            }
            $this->_is_sorted = false;
            $new_parser->set_lifecycle_state(LC_P::ASSIGN);
            return $new_parser;
        }
        public function get_parser(string $name): ?Parser {
            echo "Parsers get_parser called" . PHP_EOL;
            $parser = parent::get_parser($name);
            $parser->set_lifecycle_state(LC_P::ASSIGN);
            return $parser;
        }
        /**
         * Sorts the parsers in the collection by the order of parsing.
         *
         * @return void
         */
        public function sort_parsers() : void {
            usort($this->parsers, function($a, $b) {
                return $a->order_of_parsing <=> $b->order_of_parsing;
            });
        
            $this->_is_sorted = true;
        }
        /**
         * Retrieves the parsers array.
         *
         * If the parsers array is not sorted, it will be sorted before returning.
         *
         * @return array The parsers array.
         */
        public function list_parsers(): array {
            if (!$this->_is_sorted) {
                $this->sort_parsers();
            }
            return $this->parsers;
        }
    }
}