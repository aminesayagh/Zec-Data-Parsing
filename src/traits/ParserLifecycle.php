<?php 
declare(strict_types=1);

namespace Zec\Traits;
use Zec\CONST\LIFECYCLE_PARSER as LC_P;
use Zec\Parser as Parser;

if(!trait_exists('ParserLifecycle')) {
    trait ParserLifecycle {
        private string $lifecycle_state = LC_P::CREATE;
        private static $LIFECYCLE_STATE_MAP = [
            LC_P::CREATE => [LC_P::BUILD],
            LC_P::BUILD => [LC_P::ASSIGN],
            LC_P::ASSIGN => [LC_P::PARSE],
            LC_P::PARSE => [LC_P::VALIDATE, LC_P::ASSIGN],
            LC_P::VALIDATE => [LC_P::FINALIZE, LC_P::PARSE],
            LC_P::FINALIZE => [LC_P::FINALIZE, LC_P::PARSE], // ex: I use the same finalized parser on each to parse next case on the array
        ];
        public function getLifecycleState(): string {
            return $this->lifecycle_state;
        }
        public function setLifecycleState(string $state): Parser {
            if (!in_array($state, self::$LIFECYCLE_STATE_MAP[$this->lifecycle_state]) && $state !== $this->lifecycle_state) {
                throw new \Exception('Invalid lifecycle state transition from ' . $this->lifecycle_state . ' to ' . $state);
            }
            $this->lifecycle_state = $state;
            return $this;
        }
        public function nextLifecycleState(): Parser {
            $this->lifecycle_state = self::$LIFECYCLE_STATE_MAP[$this->lifecycle_state][0];
            return $this;
        }
    }
}