<?php
declare (strict_types = 1);

namespace Zec;

if(!trait_exists('ZecUtils')) {
    trait ZecUtils {
        public function is_valid() {
            if (count($this->_errors) == 0) {
                return true;
            }
            return false;
        }
    }
}