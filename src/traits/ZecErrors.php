<?php
declare(strict_types=1);

namespace Zec\Traits;

use Zec\Zec;
use Zec\ZecError;
use Exception;

if (!class_exists('ZecErrorNode')) {
    class ZecErrorNode
    {
        private string $key;
        private ZecError $error;
        private null|string $parent;
        public function __construct(string $key, ZecError $error, string|null $parent = null)
        {
            $this->key = $key;
            $this->error = $error;
            $this->parent = $parent;
        }
        public function getKey(): string
        {
            return $this->key;
        }
        public function getError(): ZecError
        {
            return $this->error;
        }
        public function getParent(): string
        {
            return $this->parent;
        }
        public function setParent(string $parent): void
        {
            // if the current parent is a string, and contains the new parent as a substring then return
            if (is_string($this->parent) && strpos($this->parent, $parent) !== false) {
                return;
            }
            $this->parent = $parent;
        }
        public function generateMessage(): array
        {
            $message = $this->error->generateMessage();
            if (!is_null($this->parent)) {
                $message['parent'] = $this->parent;
            }

            return $message;
        }
    }
}

if (!trait_exists('ZecErrors')) {
    trait ZecErrors
    {
        private array $errors = [];
        private function setError(ZecError $error): void
        {
            foreach ($this->errors as $e) {
                if ($e->message === $error->message) {
                    return;
                }
            }
            $this->errors[] = $error;
        }
        private function getErrors(): array
        {
            return $this->errors;
        }
        private function throwErrors(): void
        {
            if (!$this->hasErrors()) {
                throw new Exception('No errors found');
            }
            throw ZecError::fromErrors($this);
        }
        public function getMapErrors(): array
        {
            $map = [];
            foreach ($this->errors as $error) {
                if (!($error instanceof ZecError)) {
                    throw new Exception('Error must be an instance of ZecError');
                }

                $map[] = new ZecErrorNode($error->key, $error);
            }
            foreach ($map as &$item) {
                $error = $item->getError();
                foreach ($map as $parent) {
                    $error_parent = $parent->getError();
                    if ($error->isParent($error_parent->getPile())) {
                        $item->setParent($parent->getKey());
                    }
                }
            }
            return $map;
        }
        private function clearErrors(): void
        {
            $this->errors = [];
        }
        private function hasErrors(): bool
        {
            return count($this->errors) > 0;
        }
        private function errorsExtend(Zec $zec): void
        {
            $errors = $zec->getErrors();
            foreach ($errors as $error) {
                $this->setError($error);
            }
            $this->sendErrorsToParent();
        }
    }
}