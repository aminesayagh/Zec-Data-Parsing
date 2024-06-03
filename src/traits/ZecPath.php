<?php
declare(strict_types=1);

namespace Zec\Traits;

use \Zec\Zec;
use function \Zec\Utils\is_zec;

if(!class_exists('PileItem')) {
    class PileItem {
        const VERSION = '1.0.0';
        const TYPE_KEY_FLAG = 'flag';
        const TYPE_KEY_PARSER = 'parser';
        const TYPE_KEY = 'key';
        public string $key;
        public string $value;
        public function setKey(string $key): PileItem {
            if (!is_string($key)) {
                throw new \Exception('Key must be a string');
            }
            if ($key !== self::TYPE_KEY_FLAG && $key !== self::TYPE_KEY_PARSER && $key !== self::TYPE_KEY) {
                throw new \Exception('Key must be either ' . self::TYPE_KEY_FLAG . ' or ' . self::TYPE_KEY_PARSER);
            }
            $this->key = $key;
            return $this;
        }
        public function setValue(string $value): PileItem {
            if (!is_string($value)) {
                throw new \Exception('Value must be a string');
            }
            $this->value = $value;
            return $this;
        }
        public function getValue(): string {
            if (!isset($this->value)) {
                throw new \Exception('Value is not set');
            }
            return $this->value;
        }
        public function getKey(): string {
            if (!isset($this->key)) {
                throw new \Exception('Key is not set');
            }
            return $this->key;
        }
        public function compare(PileItem $pileItem): bool {
            return $this->key === $pileItem->key && $this->value === $pileItem->value;
        }
        public function isFlag(): bool {
            return $this->key === self::TYPE_KEY_FLAG;
        }
        public function isParser(): bool {
            return $this->key === self::TYPE_KEY_PARSER;
        }
        public function isKey(): bool {
            return $this->key === self::TYPE_KEY;
        }
        public function print(): string {
            switch($this->key) {
                case self::TYPE_KEY_FLAG:
                    return '[' . $this->value . ']';
                case self::TYPE_KEY_PARSER:
                    return '.' . $this->value;
                default:
                    return '->' . $this->value;
            }
        }
    }
}




if(!trait_exists('ZecPath')) {
    trait ZecPath {
        private array $pile = [];
        public function setKeyFlag(string $value): Zec {
            if(!is_string($value)) {
                throw new \Exception('Flag value must be a string');
            }
            $this->pile[] = (new PileItem())->setKey(PileItem::TYPE_KEY_FLAG)->setValue($value);
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
            $this->pile[] = (new PileItem())->setKey(PileItem::TYPE_KEY_PARSER)->setValue($value);
            return $this;
        }
        public function getPile(): array {
            return $this->pile;
        }
        public function isParent(array $zecParent, array|null $zecChild = null): bool {
            $pileParent = $zecParent;
            $pile = $zecChild ? $zecChild : $this->pile;
            if (count($pileParent) >= count($pile)) {
                return false;
            }
            foreach($pileParent as $i => $p) {
                if(!isset($pile[$i])) {
                    return false;
                }
                if(!$p->compare($pile[$i])) {
                    return false;
                }
            }
            return true;
        }
        public function setPile(array $pile): Zec {
            $this->resetPile();
            $this->pile = $pile;
            return $this;
        }
        public function getLastFlag() {
            $pile = $this->pile;
            $last_flag = null;
            foreach($pile as $p) {
                if($p->isFlag()) {
                    $last_flag = $p->getValue();
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
                $pile_string .= $p->print();
            }
            return $pile_string;
        }
        public function cleanLastFlag(null|string $flag = null) {
            $pile = $this->pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p->isFlag() || (!is_null($flag) && $p->getValue() == $flag)) {
                    break;
                }
            }
            $this->pile = array_reverse($pile);
            return $this;
        }
        public function clearLastParser() {
            $pile = $this->pile;
            $pile = array_reverse($pile);
            foreach($pile as $key => $p) {
                unset($pile[$key]);
                if($p->isParser()) {
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
        public function getPileLength(): int {
            return count($this->pile);
        }
    }
} 