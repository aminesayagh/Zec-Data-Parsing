<?php
declare (strict_types = 1);

namespace Zec\Traits;

use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use function Zec\Utils\is_zec;

if(!trait_exists('ZecUtils')) {
    trait ZecUtils {
        public function isValid(): bool {
            if ($this->hasErrors()) {
                return false;
            }
            return true;
        }
        static public function mapParse(array $data, callable $callback, array $par = []) {
            $owner = $par['owner'];
            $each = $par['argument'][PK::EACH];
            if (!is_zec($owner)) {
                throw new \Exception('Owner must be an instance of Zec');
            }
            foreach ($data as $index => $value) {
                if (!is_int($index)) {
                    throw new \Exception('Index on map parse must be an integer');
                }
                $each->resetPile();
                $each->cloneParent($owner);
                call_user_func($callback, $index, $each, $value);

                $owner->cleanLastFlag();
            }
        }
        static public function associativeParse(array $data, callable $callback, array $par = []) {
            $parsed_data = [];
            $index = 0;
            foreach($data as $key => $zec) {
                if(!is_zec($zec)) {
                    throw new \Exception('Value on associative parse must be an instance of Zec');
                }
                if ($index > 0) {
                    $par['owner']->cleanLastFlag();
                }
                $par['owner']->setKeyFlag($key);

                $default = null;
                if (is_array($par['default']) && array_key_exists($key, $par['default'])) {
                    $default = $par['default'][$key];
                }

                $value = array_key_exists($key, $par['value']) ? $par['value'][$key] : null;

                $zec->setDefault($default);
                $zec->cloneParent($par['owner']);
                call_user_func($callback, $key, $zec, $value, $index, $default);
                $index++;
            }
            $par['owner']->cleanLastFlag();

            return $parsed_data;
        }
    }
}