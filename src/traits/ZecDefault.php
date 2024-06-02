<?php
declare (strict_types = 1);

namespace Zec\Traits;

if(!trait_exists('ZecDefault')) {
    trait ZecDefault {
        private $_default = null;
        public function set_default($value) {
            $this->_default = $value;
            return $this;
        }

        public function get_default() {
            return $this->_default;
        }
    }
}