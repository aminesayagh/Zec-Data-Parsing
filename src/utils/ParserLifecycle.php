<?php 
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;
use Zod\LIFECYCLE_PARSER as LC_P;

if(!trait_exists('ParserLifecycle')) {
    trait ParserLifecycle {
        private string $_lifecycle_state = LC_P::CREATE;
        const LIFECYCLE_STATE_MAP = [
            LC_P::CREATE => [LC_P::BUILD],
            LC_P::BUILD => [LC_P::ASSIGN],
            LC_P::ASSIGN => [LC_P::PARSE],
            LC_P::PARSE => [LC_P::VALIDATE, LC_P::ASSIGN],
            LC_P::FINALIZE => [LC_P::FINALIZE]
        ];
        public function get_lifecycle_state(): string {
            return $this->_lifecycle_state;
        }
        public function set_lifecycle_state(string $state): Parser {
            if (!array_key_exists($state, self::LIFECYCLE_STATE_MAP[$this->_lifecycle_state])) { // 
                throw new ZodError('Invalid lifecycle state');
            }
            $this->_lifecycle_state = $state;
            return $this;
        }
        public function next_lifecycle_state(): Parser {
            $this->_lifecycle_state = self::LIFECYCLE_STATE_MAP[$this->_lifecycle_state];
            return $this;
        }
    }
}