<?php
declare(strict_types=1);

namespace Zod;

if (!trait_exists('ZodValue')) {
    trait ZodValue
    {
        private $_value = null;

        public function set_value($value)
        {
            $this->_value = $value;
            return $this;
        }

        public function get_value()
        {
            return $this->_value;
        }
    }
}