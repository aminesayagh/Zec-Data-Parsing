<?php
declare(strict_types=1);

namespace Zec\Traits;

use \Zec\Zec;
use function \Zec\Utils\is_zec;


if(!trait_exists('ZecPath')) {
    trait ZecPath {
        private static string $TYPE_KEY_FLAG = 'flag';
        private static string $TYPE_KEY_PARSER = 'parser';
        private static string $TYPE_KEY = 'key';
        private array $pile = [];
        public function setKeyFlag(string $value): Zec {
            if(!is_string($value)) {
                throw new \Exception('Flag value must be a string');
            }
            $this->pile[] = [
                'key' => self::$TYPE_KEY_FLAG,
                'value' => $value
            ];
            return $this;
        }
        static function pileMerge(array $pile1, array $pile2): array {
            $pile = [];
            foreach($pile1 as $p) {
                $pile[] = $p;
            }
            foreach($pile2 as $p) {
                $pile[] = $p;
            }
            return $pile;
        }
        
        public function setKeyParser(string $value): Zec {
            if(!is_string($value)) {
                throw new \Exception('Parser value must be a string');
            }
            $this->pile[] = [
                'key' => self::$TYPE_KEY_PARSER,
                'value' => $value
            ];
            return $this;
        }
        public function getPile(): array {
            return $this->pile;
        }
        public function setPile(array $pile): Zec {
            $this->resetPile();
            $this->pile = $pile;
            return $this;
        }
        public function get_last_flag() {
            $pile = $this->pile;
            $last_flag = null;
            foreach($pile as $p) {
                if($p['key'] === self::$TYPE_KEY_FLAG) {
                    $last_flag = $p['value'];
                }
            }
            return $last_flag;
        }
        public function pileExtend(Zec $z) {
            if(!is_zec($z)) {
                throw new \Exception('Zec instance is required');
            }
            $this->pile = $this::pileMerge($z->getPile(), $this->pile);
            return $this;
        }
        public function getPileString(): string {
            $pile = $this->pile;
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
        public function cleanLastFlag(null|string $flag = null) {
            $pile = $this->pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p['key'] == $this::$TYPE_KEY_FLAG || (!is_null($flag) && $p['value'] == $flag)) {
                    break;
                }
            }
            $this->pile = array_reverse($pile);
            return $this;
        }
        public function clear_last_parser() {
            $pile = $this->pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p['key'] == $this::$TYPE_KEY_PARSER) {
                    break;
                }
            }
            $this->pile = array_reverse($pile);
            return $this;
        }
        public function resetPile() {
            $this->pile = [];
            return $this;
        }
        public function get_pile_length(): int {
            return count($this->pile);
        }
        
    }
}