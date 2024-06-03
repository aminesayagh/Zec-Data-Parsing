<?php 
declare(strict_types=1);

namespace Zec\Traits;

if(!trait_exists('ParserOwner')) {
    trait ParserOwner {
        private mixed $owner = null;
        public function setOwner(mixed $owner): void {
            $this->owner = $owner;
        }
        public function getOwner(): mixed {
            return $this->owner;
        }
    }
}