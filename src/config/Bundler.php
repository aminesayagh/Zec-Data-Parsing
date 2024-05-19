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
        public function assign_parser_config(string $name, array $value, int $priority = 10) {
            // check if their is a parser with the same key
            // if yes check if the priority is higher
            // if yes register the new parser with the new priority and use the old one to get the default $value parser
            // if no register the new parser with the new priority
            // if their is no parser with the same key register the new parser with the new priority
            // if the priority is the same register update the parser with the new value
            // the value is an array of parameters

            $before_config = $this::get_parser($name); // get the paarser with the same key and the biggest priority
            if(is_null($before_config)) {
                // create parser
                // set paser to the cartacker
                $value_parser = array_merge(
                    $value,
                    ['priority' => $priority]
                );
                $parser = $this->add_parser(new Parser($name, $value_parser));
                
            } else {

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



