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
        /**
         * Assigns a parser configuration to the bundler.
         *
         * @param string $key The key of the parser configuration.
         * @param array $value The value of the parser configuration.
         * @param int $priority The priority of the parser configuration (default is 10).
         * @return $this The current instance of the Bundler.
         */
        public function assign_parser_config(string $key, array $value, int $priority = 10) {
            $before_config = $this::get_parser($key);

            if (!is_null($before_config)) {
                $before_config->clone();
                return $this;
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



