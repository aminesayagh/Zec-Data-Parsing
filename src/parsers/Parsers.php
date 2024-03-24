<?php

if (!class_exists('Parsers')) {
    /**
     * Class Parsers manages a collection of Parser objects, allowing addition, sorting, and retrieval based on priority and key.
     */
    class Parsers
    {
        /**
         * @var Parser[] Array of parser objects.
         */
        private array $parsers = [];

        /**
         * @var bool Flag indicating whether the parsers are sorted by priority.
         */
        private bool $isSorted = false;

        /**
         * Adds a parser to the collection. If the parser already exists, it updates the existing entry.
         *
         * @param Parser $parser The parser object to add.
         * @return Parser The added or updated parser object.
         */
        public function addParser(Parser $parser): Parser
        {
            foreach ($this->parsers as &$p) {
                if ($p->getKey() === $parser->getKey()) {
                    $p = $parser;
                    $this->isSorted = false;
                    return $p;
                }
            }
            $this->parsers[] = $parser;
            $this->isSorted = false;
            return $parser;
        }

        /**
         * Sorts the parsers in the collection by their priority.
         */
        public function sortParsers(): void
        {
            usort($this->parsers, function (Parser $a, Parser $b): int {
                return $a->getPriority() - $b->getPriority();
            });
            $this->isSorted = true;
        }

        /**
         * Returns an array of parsers, sorted by priority if not already sorted.
         *
         * @return Parser[] Array of parser objects, sorted by priority.
         */
        public function getParsers(): array
        {
            if (!$this->isSorted) {
                $this->sortParsers();
            }
            return $this->parsers;
        }

        /**
         * Retrieves a parser by its key.
         *
         * @param string $key The key of the parser to retrieve.
         * @return Parser|null The parser with the specified key, or null if not found.
         */
        public function getParser(string $key): ?Parser
        {
            foreach ($this->parsers as $p) {
                if ($p->getKey() === $key) {
                    return $p;
                }
            }
            return null;
        }

        /**
         * Magic method to get properties. Currently, only 'parsers' is accessible and returns the sorted parsers array.
         *
         * @param string $name The property name to access.
         * @return array The value of the accessed property.
         * @throws Exception If the property name is not supported.
         */
        public function __get(string $name): array
        {
            if ($name === 'parsers') {
                return $this->getParsers();
            }
            throw new Exception("Property $name not found");
        }

        /**
         * Invokable method to return the sorted parsers array.
         *
         * @return Parser[] The array of sorted parsers.
         */
        public function __invoke(): array
        {
            return $this->getParsers();
        }
    }
}
