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
        private array $zod_types = [];
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
            // check if their is a parser with the same key
            // if yes check if the priority is higher
            // if yes register the new parser with the new priority and use the old one to get the default $value parser
            // if no register the new parser with the new priority
            // if their is no parser with the same key register the new parser with the new priority
            // if the priority is the same register update the parser with the new value
            // the value is an array of parameters

            $before_config = $this::get_parser($name); // get the parser with the same key and the biggest priority
            if(is_null($before_config)) {
                $parser_config = $this->get_complete_parser_config($name, $value);
                $value_parser = array_merge(
                    $parser_config,
                    [
                        'priority' => $priority,
                    ]
                );
                $parser = $this->add_parser(new Parser($name, $value_parser));
                if(is_null($parser)) {
                    throw new \Exception("The parser configuration for the parser with the key $name is not valid.");
                }
            } else {
                $parser_config = $this->get_complete_parser_config($name, $value, $before_config->get_config());
                $value_parser = array_merge(
                    $parser_config,
                    [
                        'priority' => $priority,
                    ]
                );
                $parser = $this->add_parser(new Parser($name, $value_parser));
                if(is_null($parser)) {
                    throw new \Exception("The parser configuration for the parser with the key $name is not valid.");
                }
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



