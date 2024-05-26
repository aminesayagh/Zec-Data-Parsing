<?php
declare(strict_types=1);

namespace Zec;


if (!trait_exists('ParserPriority')) {
    trait ParserPriority {
        private int $_priority;
        static int $MAX_PRIORITY = 20;
        public static int $DEFAULT_PRIORITY = 10;
        static int $MIN_PRIORITY = 0;
        private function is_valid_priority(int $priority): bool {
            return is_int($priority) && $priority >= self::$MIN_PRIORITY && $priority <= self::$MAX_PRIORITY;
        }
        public function is_prioritized(int $priority_compare): bool {
            if ($this->is_valid_priority($priority_compare) === false) {
                throw new \Exception('Priority must be between 0 and 20');
            }
            if ($this->_priority < $priority_compare) {
                return true;
            }
            return false;
        }
        public function get_priority(): int {
            return $this->_priority;
        }
        public function set_priority_of_parser(int $priority): Parser {
            if ($this->is_valid_priority($priority) === false) {
                throw new \Exception('Priority must be between 0 and 20');
            }
            $this->_priority = $priority;
            return $this;
        }
    }
}