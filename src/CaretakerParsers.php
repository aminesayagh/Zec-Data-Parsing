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
        static function get_parser(string $key, ?array $parsers = null): Parser
        {
            if (is_null($parsers)) {
                $parsers = self::$parsers;
            }

            $selected_parsers = new ArrayObject(
                array_filter($parsers, function ($value) use ($key) {
                    return $value->get_key() === $key;
                })
            );
            $selected_parser = null;

            if (count($selected_parsers) === 0) {
                throw new ZodError('The parser with the key ' . $key . ' is not found', 'key');
            }

            // get the parser with the highest priority
            foreach ($selected_parsers as $value) {
                if ($value->get_key() === $key && (is_null($selected_parser) || $value->_priority > $selected_parser->_priority)) {
                    $selected_parser = $value;
                }
            }

            if (is_null($selected_parser)) {
                throw new ZodError('The parser with the key ' . $key . ' is not found', 'key');
            }

            return $selected_parser;
        }
        public function has_parser(Parser $parser): bool
        {
            return in_array($parser, $this->parsers);
        }
        public function add_parser(Parser $parser): ?Parser
        {
            if ($this->has_parser($parser)) {
                return null;
            }

            $this->parsers[] = $parser;
            return $parser;
        }
    }
}