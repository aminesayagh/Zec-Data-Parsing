<?php
declare(strict_types=1);

namespace Zec\Traits;

if (!trait_exists('ZecValue')) {
    trait ZecValue
    {
        private $value = null;

        public function setValue(mixed $value)
        {
            $this->value = $value;
            return $this;
        }

        public function getValue(): mixed
        {
            return $this->value;
        }
    }
}