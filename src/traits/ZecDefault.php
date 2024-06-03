<?php
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;

if(!trait_exists('ZecDefault')) {
    trait ZecDefault {
        private mixed $default = null;
        public function setDefault(mixed $value): Zec {
            if(is_null($value)) {
                return $this;
            }
            $parser_of_default = clone $this;
            $parser_of_default->parseOrThrow($value);

            $this->default = $value;
            return $this;
        }

        public function getDefault() {
            return $this->default;
        }
    }
}