<?php
declare(strict_types=1);

namespace Zec\Traits;

if (!trait_exists('ZecValue')) {
    trait ZecValue
    {
        private $value = null;

        public function setValue($value)
        {
            $this->value = $value;
            return $this;
        }

        public function getValue()
        {
            return $this->value;
        }
    }
}