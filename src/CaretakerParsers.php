<?php

namespace Zod;

use ArrayObject;
use Zod\FIELD\KEY as FK;
use Zod\Parser;

require_once ZOD_PATH . '/src/config/init.php';


if (!class_exists('CaretakerParsers')) {
    /**
     * Class CaretakerParsers
     * 
     * This class represents a caretaker for parsers.
     * It provides a way to manage and access an array of parsers.
     */
    class CaretakerParsers
    {
        /**
         * @var Parser[] $parsers An array of objects instantiated from the class Parser.
         */
        protected array $parsers = [];
        public function __construct(array $parsers = [])
        {
            $this->parsers = $parsers;
        }
        /**
         * Retrieves the parser with the specified key from the given array of parsers, with the highest priority.
         *
         * @param string $key The key of the parser to retrieve.
         * @param array|null $parsers The array of parsers to search in. If null, the default parsers will be used.
         * @return Parser The parser with the specified key.
         * @throws ZodError If the parser with the specified key is not found.
         */
        static function get_parser(string $key, ?array $parsers = null): ?Parser {
            if (is_null($parsers)) {
                $parsers = self::$parsers;
            }

            $selected_parsers = array_filter($parsers, function ($value) use ($key) {
                return $value->get_key() === $key;
            });

            $selected_parser = null;

            if (count($selected_parsers) === 0) {
                throw new ZodError('The parser with the key ' . $key . ' is not found', 'key');
            }

            // get the parser with the highest priority
            foreach ($selected_parsers as $value) {
                if ($value->name === $key && (is_null($selected_parser) || $value->_priority > $selected_parser->_priority)) {
                    $selected_parser = $value;
                }
            }

            if (is_null($selected_parser)) {
                return null;
            }

            return $selected_parser;
        }
        /**
         * Checks if a parser is present in the list of parsers.
         *
         * @param Parser $parser The parser to check.
         * @return bool Returns true if the parser is found, false otherwise.
         */
        public function has_parser(Parser $parser): bool
        {
            return in_array($parser, $this->parsers);
        }
        /**
         * Checks if a parser key exists in the caretaker parsers.
         *
         * @param string $key The parser key to check.
         * @return bool Returns true if the parser key exists, false otherwise.
         */
        public function has_parser_key(string $key): bool
        {
            return in_array($key, array_map(function ($value) {
                return $value->get_key();
            }, $this->parsers));
        }
        /**
         * Adds a parser to the list of parsers.
         *
         * @param Parser $parser The parser to add.
         * @return Parser|null The added parser, or null if the parser already exists.
         */
        public function add_parser(Parser $parser): ?Parser
        {
            if ($this->has_parser($parser)) {
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