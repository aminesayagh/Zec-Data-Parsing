<?php
declare(strict_types=1);


namespace Zec\Bundler;
use Zec\CONST\FIELD as FK;
use Zec\CaretakerParsers;
use Zec\Parser;


if (!class_exists('Bundler')) {
    /**
     * Class Bundler
     * 
     * This class extends the CaretakerParsers class and represents a bundler.
     * It provides functionality for bundling and managing default parsers.
     */
    class Bundler extends CaretakerParsers {
        public const VERSION = '1.0.0';
        private static ?Bundler $instance = null;
        private bool $parser_ordered = false;
        const ARRAY_CONFIG_KEYS = [
            FK::PRIORITIZE,
            FK::PARSER_ARGUMENTS,
            FK::DEFAULT_ARGUMENT,
            FK::PARSER_CALLBACK
        ];

        private $DEFAULT_PARSER_CONFIG = [
            FK::PRIORITIZE => [],
            FK::PARSER_ARGUMENTS => null,
            FK::DEFAULT_ARGUMENT => [
                'message' => 'Error on the {{name}} field'
            ],
            FK::PARSER_CALLBACK => null,
        ];
        /**
         * Class Bundler
         * 
         * This class represents a bundler for the Zec library.
         * It extends the parent class and provides additional functionality.
         */
        private function __construct()
        {
            // class parent
            parent::__construct();
        }
        private function isParserConfig(array $value) : bool {
            return count(array_intersect_key($value, array_flip(self::ARRAY_CONFIG_KEYS))) === count(self::ARRAY_CONFIG_KEYS);
        }
        private function getCompleteParserConfig(string $name, array $value, array $default = null): array {
            $parser_config = array_merge(
                $default ?? $this->DEFAULT_PARSER_CONFIG
            , $value);
            if(!$this->isParserConfig($parser_config)) {
                throw new \Exception("The parser configuration for the parser with the key $name is not valid.");
            }
            
            return $parser_config;
        }
        /**
         * Assigns a parser configuration to the bundler.
         *
         * @param string $key The key of the parser configuration.
         * @param array $value The value of the parser configuration.
         * @param int $priority The priority of the parser configuration (default is 10).
         * @return $this The current instance of the Bundler.
         */
        public function assignParserConfig(string $name, array $value, int $priority = 10) {
            
            $is_init_state = in_array(FK::IS_INIT_STATE, $value);
            $before_config = parent::getParser($name);
            $parser_config = null;
            if(is_null($before_config)) {
                $parser_config = $this->getCompleteParserConfig($name, $value);
            } else {
                $parser_config = $this->getCompleteParserConfig($name, $value, $before_config->getConfig());
            }
            $value_parser = array_merge(
                $parser_config,
                [
                'priority' => $priority,
                ]
            );
            $new_parser = $this->addParser(new Parser($name, $value_parser));
            if(!$is_init_state) {
                $new_parser->incrementOrder();
            }
            return $this;
        }
        static function getInstance(): Bundler {
            if (!isset(self::$instance)) {
                self::$instance = new Bundler();
            }
            return self::$instance;
        }
        static function generateSortParser(string $name, array &$cache): int {
            $parser = self::getInstance()->getParser($name, ['order' => false]);
            if ($parser->order_of_parsing == 0) {
                return 0;
            }
            if (isset($cache[$parser->name])) {
                return $cache[$parser->name];
            }
            $parser_name = $parser->name;
            $parser_prioritize = $parser->prioritize;
            $order = $parser->getOrderParsing();
            
            foreach ($parser_prioritize as $prioritize) {
                $order_parent = self::generateSortParser($prioritize, $cache);
                if ($order_parent > $order) {
                    $order += $order_parent;
                } else if ($order_parent == $order) {
                    $order += 1;
                }
            }
            $cache[$parser_name] = $order;
            return $order;
        }
        private function generateSortParsers () {
            $cache = [];
            // log_msg(count($this->parsers));
            foreach ($this->parsers as $parser) {
                self::generateSortParser($parser->name, $cache);
            }

            foreach ($cache as $key => $value) {
                $parsers = $this->getParsers($key);
                foreach ($parsers as $parser) {
                    $parser->setOrderParsing($value);
                }
            }
            $this->parser_ordered = true;
        }
        public function getParser(string $key, array $arg = []): ?Parser {
            $had_to_be_ordered = array_key_exists('order', $arg) ? $arg['order'] : true;
            if (!$this->parser_ordered && $had_to_be_ordered) {
                $this->generateSortParsers();
            }
            

            $parser = parent::getParser($key);
            if (is_null($parser)) {
                return null;
            }
            return $parser->clone();
        }
        public function addParser(Parser $parser): ?Parser {
            $this->parser_ordered = false;
            $this->removeParser($parser->name, $parser->priority);
            return parent::addParser($parser);
        }
        private function removeParser(string $name, int $priority): void {
            $this->parsers = array_filter($this->parsers, function($parser) use ($name, $priority) {
                return $parser->name !== $name || $parser->priority !== $priority;
            });
        }
    }
}

if(!function_exists('bundler')) {
    /**
     * Returns the instance of the Bundler class.
     *
     * @return Bundler The instance of the Bundler class.
     */
    function bundler(): Bundler {
        return Bundler::getInstance();
    }
}    



