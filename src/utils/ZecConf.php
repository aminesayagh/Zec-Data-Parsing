<?php
declare(strict_types=1);

namespace Zec;

use Zec\CONFIG_KEY as CK;
use Zec\CONFIG_ROLE as CR;

if(!class_exists('ZecConf')) {
    class ZecConf {
        private string $_key;
        private mixed $_value;
        private string $_type;
        public function __construct(string $key, mixed $init_value, string $type) {
            $this->_key = $key;
            $this->_value = $init_value;
            $this->_type = $type;
        }
        public function get_key() {
            return $this->_key;
        }
        public function clone () {
            return new ZecConf($this->_key, $this->_value, $this->_type);
        }
        public function get_value() {
            return $this->_value;
        }
        public function get_type() {
            return $this->_type;
        }
        public function set_value($value) {
            if ($this->_type === 'readonly') {
                throw new \Exception('Cannot set value of readonly config');
            }
            $this->_value = $value;
        }
    }
}

if(!trait_exists('ZecConfigs')) {
    trait ZecConfigs {
        private array $_config = [];
        private static array $_default_configs = [
            CK::TRUST_ARGUMENTS => [false, CR::READWRITE],
            CK::STRICT => [false, CR::READONLY],
        ];
        private function init_config(?array $args = null) {
            foreach(self::$_default_configs as $config_key => $config_value) {
                // check the value from the args else check it from the default configs
                $value = isset($args[$config_key]) ? $args[$config_key] : $config_value[0];
                $role = $config_value[1];

                $this->_config[$config_key] = new ZecConf($config_key, $value, $role);
            }
        }

        public function get_configs() {
            return $this->_config;
        }
        
        public function configs_extend(Zec $zec) {
            $zec_config = $zec->get_configs();
            foreach($zec_config as $key => $value) {
                $this->_config[$key] = new ZecConf($key, $value->get_value(), $value->get_type());
            }
        }
        public function get_config(string $key) {
            return $this->_config[$key]->get_value();
        }
    }
}