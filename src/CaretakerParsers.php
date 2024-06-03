<?php
declare(strict_types=1);

namespace Zec;

use Zec\Parser;
use Zec\CONST\LIFECYCLE_PARSER as LC_P;



if (!class_exists('CaretakerParsers')) {
    /**
     * Class CaretakerParsers
     * 
     * This class represents a caretaker for parsers.
     * It provides a way to manage and access an array of parsers.
     */
    abstract class CaretakerParsers
    {
        protected array $parsers = [];
        public function __construct(array $parsers = [])
        {
            $this->parsers = $parsers;
        }
        public function getParser(string $name): ?Parser {
            $parsers = $this->parsers;

            $selected_parsers = array_filter($parsers, function (Parser $value) use ($name) {
                return $value->name === $name;
            });

            $selected_parser = null;

            if (count($selected_parsers) === 0) {
                return null;
            }

            // get the parser with the highest priority
            foreach ($selected_parsers as $value) {
                if (is_null($selected_parser) || !$selected_parser->isPrioritized($value->getPriority())) {
                    $selected_parser = $value;
                }
            }

            if (is_null($selected_parser)) {
                return null;
            }

            $selected_parser_cloned_and_initialized = clone $selected_parser;
            $selected_parser_cloned_and_initialized->setLifecycleState(LC_P::ASSIGN);

            return $selected_parser_cloned_and_initialized;
        }
        public function getParsers(string $name): array {
            $parsers = $this->parsers;

            $selected_parsers = array_filter($parsers, function (Parser $value) use ($name) {
                return $value->name === $name;
            });

            if (count($selected_parsers) === 0) {
                throw new \Exception('The parser with the key ' . $name . ' is not found');
            }

            return $selected_parsers;
        }
        public function hasParser(Parser $parser): bool
        {
            return in_array($parser, $this->parsers);
        }
        /**
         * Checks if a parser key exists in the caretaker parsers.
         *
         * @param string $key The parser key to check.
         * @return bool Returns true if the parser key exists, false otherwise.
         */
        public function hasParserKey(string $key): bool
        {
            return in_array($key, array_map(function ($value) {
                return $value->name;
            }, $this->parsers));
        }
        /**
         * Adds a parser to the list of parsers.
         *
         * @param Parser $parser The parser to add.
         * @return Parser|null The added parser, or null if the parser already exists.
         */
        public function addParser(Parser $parser): ?Parser
        {
            if ($this->hasParser($parser)) {
                return null;
            }
            
            $this->parsers[] = $parser;
            return $parser;
        }
        /**
         * Returns the number of parsers in the caretaker.
         *
         * @return int The number of parsers.
         */
        public function length(): int
        {
            return count($this->parsers);
        }
    }
}