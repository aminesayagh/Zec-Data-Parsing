<?php
declare (strict_types = 1);

namespace Zec\Traits;

use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use function Zec\Utils\is_zec;

if(!trait_exists('ZecUtils')) {
    trait ZecUtils {
        public function is_valid() {
            if (count($this->_errors) == 0) {
                return true;
            }
            return false;
        }
        static public function map_parse(array $data, callable $callback, array $par = []) {
            $key = null;
            $owner = $par['owner'];
            $each = $par['argument'][PK::EACH];
            if (!is_zec($owner)) {
                throw new \Exception('Owner must be an instance of Zec');
            }
            foreach ($data as $index => $value) {
                if (!is_int($index)) {
                    throw new \Exception('Index on map parse must be an integer');
                }
                $key = $owner->set_key_flag((string)$index);
                $each->reset_pile();
                call_user_func($callback, $index, $each, $value);

                $owner->clean_last_flag();
            }
        }
        static public function associative_parse(array $data, callable $callback, array $par = []) {
            $parsed_data = [];
            $index = 0;
            foreach($data as $key => $zec) {
                if(!is_zec($zec)) {
                    throw new \Exception('Value on associative parse must be an instance of Zec');
                }
                if ($index > 0) {
                    $par['owner']->clean_last_flag();
                }
                $par['owner']->set_key_flag($key);

                $default = null;
                if (is_array($par['default']) && array_key_exists($key, $par['default'])) {
                    $default = $par['default'][$key];
                }

                $value = array_key_exists($key, $par['value']) ? $par['value'][$key] : null;

                call_user_func($callback, $key, $zec, $value, $index, $default);
                $index++;
            }
            $par['owner']->clean_last_flag();

            return $parsed_data;
        }
    }
}