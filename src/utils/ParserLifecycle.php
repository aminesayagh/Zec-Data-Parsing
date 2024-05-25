<?php 
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;
use Zod\LIFECYCLE_PARSER as LC_P;

if(!trait_exists('ParserLifecycle')) {
    trait ParserLifecycle {
        private string $_lifecycle_state = LC_P::CREATE;
        private static $LIFECYCLE_STATE_MAP = [
            LC_P::CREATE => [LC_P::BUILD],
            LC_P::BUILD => [LC_P::ASSIGN],
            LC_P::ASSIGN => [LC_P::PARSE],
            LC_P::PARSE => [LC_P::VALIDATE, LC_P::ASSIGN],
            LC_P::VALIDATE => [LC_P::FINALIZE, LC_P::PARSE],
            LC_P::FINALIZE => [LC_P::FINALIZE]
        ];
        public function get_lifecycle_state(): string {
            return $this->_lifecycle_state;
        }
        public function set_lifecycle_state(string $state): Parser {
            if (!in_array($state, self::$LIFECYCLE_STATE_MAP[$this->_lifecycle_state]) && $state !== $this->_lifecycle_state) {
                throw new \Exception('Invalid lifecycle state transition from ' . $this->_lifecycle_state . ' to ' . $state);
            }
            $this->_lifecycle_state = $state;
            return $this;
        }
        public function next_lifecycle_state(): Parser {
            $this->_lifecycle_state = self::$LIFECYCLE_STATE_MAP[$this->_lifecycle_state][0];
            return $this;
        }
    }
}