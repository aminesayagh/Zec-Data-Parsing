<?php
declare(strict_types=1);


namespace Zec\Bundler;
use Zec\CONST\FIELD as FK;
use Zec\CaretakerParsers;
use Zec\Parser;

use Zec\Parsers\ParserBuild;


if (!class_exists('Bundler')) {
    class Bundler extends CaretakerParsers {
        public const VERSION = '1.0.0';
        private static ?Bundler $instance = null;
        private bool $parser_ordered = false;
        private function __construct()
        {
            // class parent
            parent::__construct();
        }
        public function assignParserConfig(array $config) {
            if (!isset($config['signed']) || $config['signed'] !== ParserBuild::class) {
                throw new \Exception('The signed key is required and must be a class ' . ParserBuild::class);
            }
            $name = $config['name'];
            $is_init_state = $config['is_init_state'];
            $before_config = parent::getParser($name);
            $parser_config = array_merge(
                isset($before_config) ? $before_config->getConfig() : [],
                $config
            );
            $new_parser = $this->addParser(new Parser($name, $parser_config));
            if (!$is_init_state) {
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



