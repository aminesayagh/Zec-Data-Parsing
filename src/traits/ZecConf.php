<?php
declare(strict_types=1);

namespace Zec\Traits;
use Zec\Zec;

use Zec\CONST\CONFIG_KEY as CK;
use Zec\CONST\CONFIG_ROLE as CR;

if(!class_exists('ZecConf')) {
    class ZecConf {
        public const VERSION = '1.0.0';
        private string $key;
        private mixed $value;
        private string $type;
        public function __construct(string $key, mixed $init_value, string $type) {
            $this->key = $key;
            $this->value = $init_value;
            $this->type = $type;
        }
        public function getKey() {
            return $this->key;
        }
        public function clone () {
            return new ZecConf($this->key, $this->value, $this->type);
        }
        public function getType() {
            return $this->type;
        }
        public function setValue($value) {
            if ($this->type === 'readonly') {
                throw new \Exception('Cannot set value of readonly config');
            }
            $this->value = $value;
        }
        public function getValue() {
            return $this->value;
        }
    }
}

if(!trait_exists('ZecConfigs')) {
    trait ZecConfigs {
        private array $configs = [];
        private static array $default_configs = [
            CK::TRUST_ARGUMENTS => [false, CR::READWRITE],
            CK::STRICT => [false, CR::READONLY],
        ];
        private function initConfig(?array $args = null) {
            foreach(self::$default_configs as $config_key => $config_value) {
                $value = isset($args[$config_key]) ? $args[$config_key] : $config_value[0];
                $role = $config_value[1];

                $this->configs[$config_key] = new ZecConf($config_key, $value, $role);
            }
        }

        public function getConfigs() {
            return $this->configs;
        }
        
        public function configsExtend(Zec $zec) {
            $zec_config = $zec->getConfigs();
            foreach($zec_config as $key => $value) {
                if (!($value instanceof ZecConf)) {
                    throw new \Exception('Invalid config value');
                }
                $this->configs[$key] = new ZecConf($key, $value->getValue(), $value->getType());
            }
        }
        public function getConfig(string $key) {
            if (!isset($this->configs[$key])) {
                throw new \Exception("Config $key not found");
            }
            $config = $this->configs[$key];
            if (is_null($config)) {
                throw new \Exception("Config $key not found");
            }
            return $config->getValue();
        }
    }
}