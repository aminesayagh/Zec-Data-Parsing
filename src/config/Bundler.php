<?php


namespace Zod;
use Zod\FIELD as FK;

require_once ZOD_PATH . '/src/config/init.php';
require_once ZOD_PATH . '/src/CaretakerParsers.php';


if (!class_exists('Bundler')) {
    /**
     * Class Bundler
     * 
     * This class extends the CaretakerParsers class and represents a bundler.
     * It provides functionality for bundling and managing default parsers.
     */
    class Bundler extends CaretakerParsers {
        private static ?Bundler $_instance = null;
        private bool $_parser_ordered = false;
        const ARRAY_CONFIG_KEYS = [
            FK::PRIORITIZE,
            FK::PARSER_ARGUMENTS,
            FK::DEFAULT_ARGUMENT,
            FK::PARSER_CALLBACK
        ];
        const DEFAULT_PARSER_CONFIG = [
            FK::PRIORITIZE => [],
            FK::PARSER_ARGUMENTS => function () {
                return z()->options([
                    'message' => z()->required()->string()
                ]);
            },
            FK::DEFAULT_ARGUMENT => [
                'message' => 'Error on the {{name}} field'
            ],
            FK::PARSER_CALLBACK => function (array $par): string|bool {
                return $par['argument']['message'];
            }
        ];
        /**
         * Class Bundler
         * 
         * This class represents a bundler for the Zod library.
         * It extends the parent class and provides additional functionality.
         */
        private function __construct()
        {
            // class parent
            parent::__construct();
        }
        private function is_parser_config(array $value) : bool {
            return count(array_intersect_key($value, array_flip(self::ARRAY_CONFIG_KEYS))) === count(self::ARRAY_CONFIG_KEYS);
        }
        private function get_complete_parser_config(string $name, array $value, array $default = self::DEFAULT_PARSER_CONFIG): array {
            $parser_config = array_merge($default, $value);
            if(!$this->is_parser_config($parser_config)) {
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
        public function assign_parser_config(string $name, array $value, int $priority = 10) {
            
            $is_init_state = in_array(FK::IS_INIT_STATE, $value);
            $before_config = parent::get_parser($name);
            $parser_config = null;
            if(is_null($before_config)) {
                $parser_config = $this->get_complete_parser_config($name, $value);
            } else {
                $parser_config = $this->get_complete_parser_config($name, $value, $before_config->get_config());
            }
            $value_parser = array_merge(
                $parser_config,
                [
                'priority' => $priority,
                ]
            );
            $new_parser = $this->add_parser(new Parser($name, $value_parser));
            if(!$is_init_state) {
                $new_parser->increment_order();
            }

            return $this;
        }
        /**
         * Returns an instance of the Bundler class.
         *
         * @return Bundler The instance of the Bundler class.
         */
        static function get_instance(): Bundler {
            if (!isset(self::$_instance)) {
                self::$_instance = new Bundler();
            }
            return self::$_instance;
        }
        static function sort_parser(string $name, array &$cache): int {
            $parser = self::get_instance()->get_parser($name);
            if ($parser->order_of_parsing == 0) {
                return 0;
            }
            $parser_name = $parser->name;
            $parser_prioritize = $parser->prioritize;
            $order = $parser->get_order_parsing();
            foreach ($parser_prioritize as $prioritize) {
                $order_parent = self::sort_parser($prioritize, $cache);
                if ($order_parent > $order) {
                    $order += $order_parent;
                } else if ($order_parent == $order) {
                    $order += 1;
                }
            }
            $cache[$parser_name] = $order;
            return $order;
        }
        
        public function get_parser(string $key): ?Parser {
            // sort the parsers

                
            return parent::get_parser($key);
        }
        public function add_parser(Parser $parser): ?Parser {
            $this->_parser_ordered = false;
            $this->remove_parser($parser->name, $parser->priority);
            return parent::add_parser($parser);
        }
        private function remove_parser(string $name, int $priority): void {
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
        return Bundler::get_instance();
    }
}    



