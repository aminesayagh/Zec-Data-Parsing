<?php


namespace Zod;

require_once ZOD_PATH . '/src/config/default.php';

if (!class_exists('ZodConfig')) {
    class ZodConfig {
        private ?string $_key = null;
        private ?string $_accept = null;
        private ?bool $_is_init_state = null;
        private $_parser_arguments = null;
        private array $_default_argument = [];
        private $_parser = null;
        public function __construct($key) {
            $this->_key = $key;
        }
        public function get_accept(string $accept) {
            $this->_accept = $accept;
            return $this;
        }
        public function get_init_state(bool $is_init_state) {
            $this->_is_init_state = $is_init_state;
            return $this;
        }
        public function get_parser_arguments($parser_arguments) {
            $this->_parser_arguments = $parser_arguments;
            return $this;
        }
        public function get_default_argument(array $default_argument) {
            $this->_default_argument = $default_argument;
            return $this;
        }
        public function get_parser($parser) {
            $this->_parser = $parser;
            return $this;
        }
        public function assign_priority() {

        }
    }
}

if (!class_exists('Bundler')) {
    class Bundler {
        private static ?Bundler $_instance = null;
        private array $config;
        private array $default;
        private function __construct()
        {
            $this->default = ZOD_DEFAULT_MODEL;
            $this->assignPriority();
        }
        private function assignPriority() {
            foreach ()
        }
        static function valid_parser_config(string $key, array $value) {
            return [$key, $value];
        }
    }
}



