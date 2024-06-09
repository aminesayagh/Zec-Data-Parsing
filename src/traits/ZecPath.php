<?php
declare(strict_types=1);

namespace Zec\Traits;

use \Zec\Zec;
use \Zec\ZecError;

if(!trait_exists('ZecPath')) {
    trait ZecPath {
        private array $path = [];
        public function pathMerge(array $path1, array $path2): array {
            $path = [];
            foreach($path1 as $p) {
                $path[] = $p;
            }
            foreach($path2 as $p) {
                $path[] = $p;
            }
            return $path;
        }
        public function setPath(array $path): Zec|ZecError {
            $this->resetPath();
            $this->path = $path;
            return $this;
        }
        public function getPath(): array {
            return $this->path;
        }
        public function addItem(string|int $pathItem): Zec|ZecError {
            if(!is_string($pathItem) && !is_int($pathItem)) {
                throw new \Exception('Path item must be a string or an integer');
            }
            $this->path[] = $pathItem;
            return $this;
        }
        public function popItem(): string|int|null {
            
            return array_pop($this->path) ?? null;
        }
        public function resetPath(): Zec|ZecError {
            $this->path = [];
            return $this;
        }
        public function getPathLength(): int {
            return count($this->path);
        }
        public function hasPath(): bool {
            return count($this->path) > 0;
        }
    }
}
