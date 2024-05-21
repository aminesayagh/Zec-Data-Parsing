<?php
declare (strict_types = 1);

namespace Zod;

if(!trait_exists('ZodDefault')) {
    trait ZodDefault {
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