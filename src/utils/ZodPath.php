<?php
declare(strict_types=1);

namespace Zod;

if(!trait_exists('ZodPath')) {
    trait ZodPath{
        private static array $_TYPE_KEY = [
            'FLAG' => 'flag',
            'PARSER' => 'parser'
        ];
        private array $_pile = [];
        public function set_key_flag(string $value): Zod {
            $this->_pile[] = [
                'key' => $this::$_TYPE_KEY['FLAG'],
                'value' => $value
            ];
            return $this;
        }
        public function set_key_parser(string $value): Zod {
            $this->_pile[] = [
                'key' => $this::$_TYPE_KEY['PARSER'],
                'value' => $value
            ];
            return $this;
        }
        public function get_pile() {
            return $this->_pile;
        }
        // get last element on the pile of type flag
        public function get_last_flag() {
            $pile = $this->_pile;
            $last_flag = null;
            foreach($pile as $p) {
                if($p['key'] === $this::$_TYPE_KEY['FLAG']) {
                    $last_flag = $p['value'];
                }
            }
            return $last_flag;
        }
        public function pile_extend(Zod $z) {
            $extend_pile = $z->get_pile();
            $this->_pile = array_merge($extend_pile, $this->_pile);
            return $this;
        }
        public function get_pile_string() {
            $pile = $this->_pile;
            $pile_string = '';
            foreach($pile as $p) {
                if($p['key'] === $this::$_TYPE_KEY['FLAG']) {
                    $pile_string .= '/' . $p['value'];
                }
                if($p['key'] === $this::$_TYPE_KEY['PARSER']) {
                    $pile_string .= '.' . $p['value'];
                }
            }
            return $pile_string;
        }
        public function log_pile() {
            echo 'PILE: ' . $this->get_pile_string() . PHP_EOL;
        }
        public function clean_last_flag() {
            $pile = $this->_pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                if($p['key'] === $this::$_TYPE_KEY['FLAG']) {
                    unset($pile[$key]);
                    break;
                }
            }
            $this->_pile = array_reverse($pile);
            return $this;
        }
    }
}