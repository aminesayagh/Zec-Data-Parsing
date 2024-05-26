<?php
declare(strict_types=1);

namespace Zec;

if(!trait_exists('ZecPath')) {
    trait ZecPath{
        private static string $TYPE_KEY_FLAG = 'flag';
        private static string $TYPE_KEY_PARSER = 'parser';
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
        public function get_pile() {
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
            $extend_pile = $z->get_pile();
            $this->_pile = array_merge($extend_pile, $this->_pile);
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
            }
            return $pile_string;
        }
        public function clean_last_flag() {
            $pile = $this->_pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p['key'] == $this::$TYPE_KEY_FLAG) {
                    break;
                }
            }
            $this->_pile = array_reverse($pile);
            return $this;
        }
    }
}