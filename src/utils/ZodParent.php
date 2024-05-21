<?php 
declare (strict_types = 1);

namespace Zod;

if(!trait_exists('ZodParent')) {
    trait ZodParent {
        private ?Zod $_parent;
        private function _clone_parent(Zod $parent): void {
            $this->pile_extend($parent);
            $this->configs_extend($parent);

            return $this;
        }
    }
}