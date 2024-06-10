<?php 

namespace Zec;

use \Exception;
use PHPUnit\Metadata\Metadata;

// propose me a meta abstract class to manage meta data of classes
if(!class_exists('MetaValue')) {
    class MetaValue {
        private array $VALID_VISIBILITY = ['public', 'protected', 'private'];
        private mixed $value;
        private bool $readonly = false;
        private string $visibility = 'public';
        public function __construct(mixed $value = null, bool $readonly = false, string $visibility = 'public') {
            $this->setValue($value)->setVisibility($visibility);
            $this->readonly = $readonly;
        }
        public function getValue(): mixed {
            if ($this->visibility === 'private') {
                throw new Exception('Cannot access private property');
            }
            return $this->value;
        }
        public function getAdminValue(): mixed {
            return $this->value;
        }
        public function setValue(mixed $value): self {
            if ($this->readonly) {
                throw new Exception('Cannot set value on readonly property');
            }
            $this->value = $value;
            return $this;
        }
        public function isReadonly(): bool {
            return $this->readonly;
        }
        public function setVisibility(string $visibility): self {
            if (!in_array($visibility, $this->VALID_VISIBILITY)) {
                throw new Exception('Invalid visibility');
            }
            $this->visibility = $visibility;
            return $this;
        }
        public function getVisibility(): string {
            return $this->visibility;
        }
    }
}

if (!trait_exists('Meta')) {
    trait Meta {
        private array $meta = [];
        public function __construct(array $meta = []) {
            foreach ($meta as $key => $value) {
                $this->meta[$key] = new MetaValue($value);
            }
        }
        public function setMeta(string $key, mixed $value, bool $readonly = false, string $visibility = 'public'): self {
            // if value is a MetaValue object, set it directly
            if ($value instanceof MetaValue) {
                $this->meta[$key] = $value;
                return $this;
            }
            // if key exists, set value
            if (array_key_exists($key, $this->meta)) {
                $this->meta[$key]->setValue($value);
                return $this;
            }
            // set new value with key and value as MetaValue object
            $this->meta[$key] = new MetaValue($value, $readonly, $visibility);
            return $this;
        }
        public function getMeta(string $key): mixed {
            if (array_key_exists($key, $this->meta)) {
                return $this->meta[$key]->getValue();
            }
            return null;
        }
        public function getMetadata() {
            $meta = [];
            foreach ($this->meta as $key => $value) {
                if ($value->getVisibility() !== 'public') {
                    continue;
                }
                $meta[$key] = $value->getValue();
            }
            return $meta;
        }
        public function getAdminMeta(string $key): mixed {
            if (array_key_exists($key, $this->meta)) {
                return $this->meta[$key]->getAdminValue();
            }
            return null;
        }
        public function isReadonly(string $key): bool {
            if (array_key_exists($key, $this->meta)) {
                return $this->meta[$key]->isReadonly();
            }
            throw new Exception('Property not found');
        }
        public function getVisibility(string $key): string {
            if (array_key_exists($key, $this->meta)) {
                return $this->meta[$key]->getVisibility();
            }
            throw new Exception('Property not found');
        }
    }
}