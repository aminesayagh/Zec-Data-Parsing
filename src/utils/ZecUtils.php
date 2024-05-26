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