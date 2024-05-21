<?php
declare(strict_types=1);

namespace Zod;

if(!class_exists('ZodConf')) {
    class ZodConf {
        private string $_key;
        private mixed $_value;
        private string $_type;
        // constructor
        public function __construct(string $key, mixed $init_value, string $type) {
            $this->_key = $key;
            $this->_value = $init_value;
            $this->_type = $type;
        }
        public function get_key() {
            return $this->_key;
        }
        public function clone () {
            return new ZodConf($this->_key, $this->_value, $this->_type);
        }
        public function get_value() {
            return $this->_value;
        }
        public function set_value($value) {
            if ($this->_type === 'readonly') {
                throw new \Exception('Cannot set value of readonly config');
            }
            $this->_value = $value;
        }
    }
}

if(!trait_exists('ZodConfigs')) {
    trait ZodConfigs {
        private array $_config = [];
        // if zod has parent and parent has configs, extend zod's configs with parent's configs
        private function init_config() {
            $this->_config = [
                'trust_arguments' => new ZodConf('trust_arguments', false, 'readonly'),
                'strict' => new ZodConf('strict', false, 'readwrite'),
            ];
        }

        public function get_configs() {
            return $this->_config;
        }
        
        public function configs_extend(Zod $zod) {
            $zod_config = $zod->get_configs();
            foreach($zod_config as $key => $value) {
                $this->_config[$key]->set_value($value);
            }
        }
        public function get_config(string $key) {
            return $this->_config[$key]->get_value();
        }
        public function set_config(string $key, $value) {
            $this->_config[$key]->set_value($value);
        }
        
    }
}