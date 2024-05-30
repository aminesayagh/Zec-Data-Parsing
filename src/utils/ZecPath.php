<?php
declare(strict_types=1);

namespace Zec;

if(!trait_exists('ZecPath')) {
    trait ZecPath {
        private static string $TYPE_KEY_FLAG = 'flag';
        private static string $TYPE_KEY_PARSER = 'parser';
        private static string $TYPE_KEY = 'key';
        private array $_pile = [];
        public function set_key_flag(string $value): Zec {
            if(!is_string($value)) {
                throw new \Exception('Flag value must be a string');
            }
            $this->_pile[] = [
                'key' => self::$TYPE_KEY_FLAG,
                'value' => $value
            ];
            return $this;
        }
        static function pile_merge(array $pile1, array $pile2): array {
            $pile = [];
            foreach($pile1 as $p) {
                $pile[] = $p;
            }
            foreach($pile2 as $p) {
                $pile[] = $p;
            }
            return $pile;
        }
        
        public function set_key_parser(string $value): Zec {
            if(!is_string($value)) {
                throw new \Exception('Parser value must be a string');
            }
            $this->_pile[] = [
                'key' => self::$TYPE_KEY_PARSER,
                'value' => $value
            ];
            return $this;
        }
        public function get_pile(): array {
            return $this->_pile;
        }
        public function get_last_flag() {
            $pile = $this->_pile;
            $last_flag = null;
            foreach($pile as $p) {
                if($p['key'] === self::$TYPE_KEY_FLAG) {
                    $last_flag = $p['value'];
                }
            }
            return $last_flag;
        }
        public function pile_extend(Zec $z) {
            if(!is_zec($z)) {
                throw new \Exception('Zec instance is required');
            }
            $this->_pile = $this::pile_merge($z->get_pile(), $this->_pile);
            return $this;
        }
        public function get_pile_string(): string {
            $pile = $this->_pile;
            $pile_string = '';
            foreach($pile as $p) {
                if($p['key'] === self::$TYPE_KEY_FLAG) {
                    $pile_string .= '[' . $p['value'] . ']';
                }
                if($p['key'] === self::$TYPE_KEY_PARSER) {
                    $pile_string .= '.' . $p['value'];
                }
                if($p['key'] === self::$TYPE_KEY) {
                    $pile_string .= '->' . $p['value'];
                }
            }
            return $pile_string;
        }
        public function clean_last_flag(null|string $flag = null) {
            $pile = $this->_pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p['key'] == $this::$TYPE_KEY_FLAG || (!is_null($flag) && $p['value'] == $flag)) {
                    break;
                }
            }
            $this->_pile = array_reverse($pile);
            return $this;
        }
        public function clear_last_parser() {
            $pile = $this->_pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p['key'] == $this::$TYPE_KEY_PARSER) {
                    break;
                }
            }
            $this->_pile = array_reverse($pile);
            return $this;
        }
        public function reset_pile() {
            $this->_pile = [];
            return $this;
        }
        public function get_pile_length(): int {
            return count($this->_pile);
        }
        
    }
}