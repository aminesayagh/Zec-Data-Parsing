<?php
declare(strict_types=1);

namespace Zod;

use Zod\Parser;



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
        public function get_parser(string $name): ?Parser {
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
                if (is_null($selected_parser) || !$selected_parser->is_priority($value)) {
                    $selected_parser = $value;
                }
            }

            if (is_null($selected_parser)) {
                return null;
            }

            $selected_parser_cloned_and_initialized = clone $selected_parser;
            $selected_parser_cloned_and_initialized->initialize();

            return $selected_parser_cloned_and_initialized;
        }
        public function get_parsers(string $name): array {
            $parsers = $this->parsers;

            $selected_parsers = array_filter($parsers, function (Parser $value) use ($name) {
                return $value->name === $name;
            });

            if (count($selected_parsers) === 0) {
                throw new ZodError('The parser with the key ' . $name . ' is not found', 'key');
            }

            return $selected_parsers;
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
                return $value->name;
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