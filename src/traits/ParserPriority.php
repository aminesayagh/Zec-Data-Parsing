<?php
declare(strict_types=1);

namespace Zec\Traits;
use Zec\Parser;

if (!trait_exists('ParserPriority')) {
    trait ParserPriority {
        static int $MAX_PRIORITY = 20;
        public static int $DEFAULT_PRIORITY = 10;
        static int $MIN_PRIORITY = 0;
        private int $priority;
        private function isValidPriority(int $priority): bool {
            return is_int($priority) && $priority >= self::$MIN_PRIORITY && $priority <= self::$MAX_PRIORITY;
        }
        public function isPrioritized(int $priority_compare): bool {
            if ($this->isValidPriority($priority_compare) === false) {
                throw new \Exception('Priority must be between 0 and 20');
            }
            if ($this->priority < $priority_compare) {
                return true;
            }
            return false;
        }
        public function getPriority(): int {
            return $this->priority;
        }
        public function setPriorityOfParser(int $priority): Parser {
            if ($this->isValidPriority($priority) === false) {
                throw new \Exception('Priority must be between 0 and 20');
            }
            $this->priority = $priority;
            return $this;
        }
    }
}