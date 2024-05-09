<?php


namespace Zod;
use Zod\FIELD\KEY as FK;

require_once ZOD_PATH . '/src/config/init.php';

if (!class_exists('CaretakerParsers')) {
    class CaretakerParsers {
        private function __construct() {

        }
    }
}


if (!class_exists('Bundler')) {
    class Bundler extends CaretakerParsers {
        private static ?Bundler $_instance = null;
        private array $config;
        private function __construct()
        {
            $this->config = [];
        }
        private function assign_path_of_parsing() {
            foreach ($this->config as $value) {
                $value->cal_order_of_parsing($this->config);
            }
        }
        static function assign_parser_config($new_config, $before_config) {
            if (!isset($before_config)) {
                return $new_config;
            }
            if ($new_config->_priority > $before_config->_priority) {
                return $new_config;
            }
            return $before_config;
        }
        static function valid_parser_config(string $key, array $value, int $priority = 10) {
            // TODO: Fist config is the default config, with the init value
            $before_config = Bundler::get_parser($key);
            $new_parser = (new Parser($key))
                    ->clone($before_config)
                    ->set_accept($value[FK\ACCEPT])
                    ->set_init_state($value[FK\IS_INIT_STATE])
                    ->set_parser_arguments($value[FK\PARSER_ARGUMENTS])
                    ->set_default_argument($value[FK\DEFAULT_ARGUMENT])
                    ->set_parser($value[FK\PARSER])
                    ->set_priority_of_parser($priority);

            
            $new_config = Bundler::assign_parser_config($new_parser, $before_config);
            self::$config[] = $new_config;
            return $new_config;
        }
        static function get_parser(string $key): Parser {
            foreach (self::$config as $value) {
                if ($value->_key === $key) {
                    $selected_parser = $value;
                }
            }
            // get the parser with the highest priority
            foreach (self::$config as $value) {
                if ($value->_key === $key && $value->_priority > $selected_parser->_priority) {
                    $selected_parser = $value;
                }
            }
            return $selected_parser;
        }
        static function get_instance(): Bundler {
            if (!isset(self::$_instance)) {
                self::$_instance = new Bundler();
            }
            return self::$_instance;
        }
    }
}

if(!function_exists('bundler')) {
    function bundler(): Bundler {
        return Bundler::get_instance();
    }
}




