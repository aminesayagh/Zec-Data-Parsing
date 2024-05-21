<?php
declare (strict_types = 1);

namespace Zod;

if(!trait_exists('ZodUtils')) {
    trait ZodUtils {
        public function is_valid() {
            if (count($this->errors) === 0) {
                return true;
            }
            return false;
        }
    }
}