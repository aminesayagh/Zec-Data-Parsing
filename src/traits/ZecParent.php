<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;

if(!trait_exists('ZecParent')) {
    trait ZecParent {
        private ?Zec $parent = null;

        public function resetParent(): void {
            $this->parent = null;
        }
        private function cloneParent(?Zec $parent): Zec {
            if($parent == null) {
                return $this;
            }
            $this->parent = $parent;
            $this->resetPath();
            $this->setPath($parent->getPath());
            $this->configsExtend($parent);

            return $this;
        }
        private function sendErrorsToParent(): void {
            if($this->parent == null) {
                return;
            }
            $this->parent->errorsExtend($this);
        }
    }
}