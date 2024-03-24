<?php

if (!class_exists('Parser')) {
    class Parser
    {
        private string $key;
        private array $accept;
        private bool $initState;
        private array $arguments = [];
        private int $priority;
        private Zod $argumentsParser;
        private $parser;

        /**
         * Constructs a Parser object with the specified key.
         *
         * @param string $key The key used to initialize the parser.
         * @throws Exception If the parser key is not found.
         */
        public function __construct(string $key)
        {
            $parserRules = bundler()->getConfig($key);
            $this->key = $key;
            $this->accept = $parserRules['accept'];
            $this->initState = $parserRules['initState'];
            $this->priority = $parserRules['priority'];
            $this->parser = $parserRules['parser'];
            $this->argumentsParser = $parserRules['argumentsParser']();
        }

        /**
         * Checks if the state is acceptable.
         *
         * @param mixed $state The state to check.
         * @return bool True if the state is acceptable, false otherwise.
         */
        public function acceptState($state): bool
        {
            return in_array($state, $this->accept);
        }

        /**
         * Gets the initial state of the parser.
         *
         * @return bool The initial state of the parser.
         */
        public function initState(): bool
        {
            return $this->initState;
        }

        /**
         * Sets the arguments for the parser.
         *
         * @param array $arguments The arguments to set.
         */
        public function setArguments(array $arguments): void
        {
            $this->argumentsParser->parseOrThrow($arguments);
            $this->arguments = $arguments;
        }

        /**
         * Gets the arguments of the parser.
         *
         * @return array The arguments of the parser.
         */
        public function getArguments(): array
        {
            return $this->arguments;
        }

        /**
         * Parses the given value using the parser.
         *
         * @param mixed $value The value to parse.
         * @return mixed The result of the parser.
         */
        public function parse($value)
        {
            return ($this->parser)($value, $this->arguments);
        }

        /**
         * Gets the priority of the parser.
         *
         * @return int The priority of the parser.
         */
        public function getPriority(): int
        {
            return $this->priority;
        }

        /**
         * Gets the key of the parser.
         *
         * @return string The key of the parser.
         */
        public function getKey(): string
        {
            return $this->key;
        }

        /**
         * Checks if a given key corresponds to a parser.
         *
         * @param string $key The key to check.
         * @return bool True if the key corresponds to a parser, false otherwise.
         */
    }
}