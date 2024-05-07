<?php


namespace Zod;
use Zod\FIELD\KEY as FK;

require_once ZOD_PATH . '/src/config/default.php';


if (!class_exists('ZodConfig')) {
    class Config {
        private ?string $_key = null;
        private ?array $_accept = null;
        private int $_priority = 10;
        private int $_priority_of_execution = 0;
        private ?bool $_is_init_state = null;
        private $_parser_arguments = null;
        private array $_default_argument = [];
        private $_parser = null;
        public function __construct($key) {
            $this->_key = $key;
        }
        public function set_accept(array $accept): Config {
            $this->_accept = $accept;
            return $this;
        }
        public function set_init_state(bool $is_init_state): Config {
            $this->_is_init_state = $is_init_state;
            return $this;
        }
        public function set_parser_arguments($parser_arguments): Config {
            $this->_parser_arguments = $parser_arguments;
            return $this;
        }
        public function set_default_argument(array $default_argument): Config {
            $this->_default_argument = $default_argument;
            return $this;
        }
        public function set_parser($parser): Config {
            $this->_parser = $parser;
            return $this;
        }
        public function set_priority_of_parser(int $priority): Config {
            $this->_priority = $priority;
            return $this;
        }
        public function cal_priority(array &$parserRules, array $parents = []): int|null {
            if (!isset($this->_accept)) {
                return 0;
            }

            $priority_of_execution = 0;

            foreach ($this->_accept as $a) {
                // Check if the parser has a priority
                if (isset($this->_priority_of_execution)) {
                    $priority_of_execution = max($priority_of_execution, $this->_priority_of_execution);
                } else {
                    if (in_array($a, $parents)) {
                        continue; // avoid infinite loop in case of circular dependency
                    }
                    $parents[] = $this->_key;
                    $priority_of_execution = max($priority_of_execution, $parserRules[$a]->cal_priority($parserRules, $parents) + 1);
                }
            }
            $this->_priority_of_execution = $priority_of_execution + 1;
            return $this->_priority_of_execution;
        }
    }
}

if (!class_exists('Bundler')) {
    class Bundler {
        private static ?Bundler $_instance = null;
        private array $config;
        private function __construct()
        {
            $this->assignPriority();
        }
        private function assignPriority() {
            foreach ($this->config as $key => $value) {
                $value->cal_priority($this->config);
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
            self::$config[$key] = self::assign_parser_config(
                (new Config($key))
                    ->set_accept($value[FK\ACCEPT])
                    ->set_init_state($value[FK\IS_INIT_STATE])
                    ->set_parser_arguments($value[FK\PARSER_ARGUMENTS])
                    ->set_default_argument($value[FK\DEFAULT_ARGUMENT])
                    ->set_parser($value[FK\PARSER])
                    ->set_priority_of_parser($priority),
                self::$config[$key]
            );
        }
        static function getConfig(string $key): Config {
            return self::$config[$key];
        }
        static function getInstance(): Bundler {
            if (!isset(self::$_instance)) {
                self::$_instance = new Bundler();
            }
            return self::$_instance;
        }
    }
}



