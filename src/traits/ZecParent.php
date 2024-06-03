<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;

if(!trait_exists('ZecParent')) {
    trait ZecParent {
        private ?Zec $parent = null;
        private function cloneParent(?Zec $parent): Zec {
            if(is_null($parent)) {
                return $this;
            }
            $this->parent = $parent;
            $this->pileExtend($parent);
            $this->configsExtend($parent);

            return $this;
        }
        private function sendErrorsToParent(): void {
            if(is_null($this->parent)) {
                return;
            }
            $this->parent->errorsExtend($this);
        }
    }
}