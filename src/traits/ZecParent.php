<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;

if(!trait_exists('ZecParent')) {
    trait ZecParent {
        private ?Zec $_parent = null;
        private function _clone_parent(?Zec $parent): Zec {
            if(is_null($parent)) {
                return $this;
            }
            $this->_parent = $parent;
            $this->pile_extend($parent);
            $this->configs_extend($parent);

            return $this;
        }
        private function _send_errors_to_parent(): void {
            if(is_null($this->_parent)) {
                return;
            }
            $this->_parent->errors_extend($this);
        }
    }
}