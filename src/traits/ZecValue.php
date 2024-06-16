<?php
declare(strict_types=1);

namespace Zec\Traits;

if (!trait_exists('ZecValue')) {
    trait ZecValue
    {
        private $value = null;

        public function value(mixed $value)
        {
            $this->value = $value;
            return $this;
        }

        private function traitGetValue(string $name)
        {
            return match ($name) {
                'value' => $this->value,
                default => null,
            };
        }
        public function getValue(): mixed
        {
            return $this->value;
        }
    }
}