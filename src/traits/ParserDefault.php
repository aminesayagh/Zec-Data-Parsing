<?php
declare(strict_types=1);

namespace Zec\Traits;

use Zec\CONST\CONFIG_KEY as CK;
use Zec\Parser as Parser;

if (!trait_exists("ParserDefault")) {
    trait ParserDefault {
        private mixed $default = null;
        public function default(mixed $default): void {
            $this->default = $default;
        }
        public function getDefault(): mixed {
            return $this->default;
        }
    }
}