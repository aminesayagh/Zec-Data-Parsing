<?php 

namespace Zec;

use \Exception;

// propose me a meta abstract class to manage meta data of classes
if (!class_exists('Meta')) {
    trait Meta {
        private array $meta = [];
        public function __construct(array $meta) {
            $this->meta = $meta;
        }
        public function __get(string $name) {
            if (array_key_exists($name, $this->meta)) {
                return $this->meta[$name];
            }
            return match ($name) {
                'meta' => $this->meta,
                default => throw new Exception("Property $name not found"),
            };
        }
        public function __set(string $name, mixed $value) {
            $this->meta[$name] = $value;
        }
        public function __isset(string $name): bool {
            return isset($this->meta[$name]);
        }
        public function __unset(string $name): void {
            unset($this->meta[$name]);
        }
        public function __toString(): string {
            return json_encode($this->meta, JSON_PRETTY_PRINT);
        }
    }
}