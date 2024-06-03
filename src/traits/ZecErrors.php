<?php
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;
use Zec\ZecError;
use Exception;

if(!trait_exists('ZecErrors')) {
    trait ZecErrors {
        private array $errors = [];
        private function setError(ZecError $error): void {
            foreach($this->errors as $e) {
                if($e->message === $error->message) {
                    return;
                }
            }
            $this->errors[] = $error;
        }
        private function getErrors(): array {
            return $this->errors;
        }
        private function throwErrors(): void {
            if(!$this->hasErrors()) {
                throw new Exception('No errors found');
            }
            throw ZecError::fromErrors($this);
        }
        public function getMapErrors(): array {
            $map = [];
            $errors = $this->getErrors();
            function edit_case(array $keys, array &$map, mixed $error): void {
                $key = array_shift($keys);
                if(count($keys) === 0) {
                    if(!isset($map[$key])) {
                        $map[$key] = [];
                        $map[$key][] = $error;
                    }
                    return;
                }
                if(!isset($map[$key])) {
                    $map[$key] = [];
                }
                edit_case($keys, $map[$key], $error);
            }
            foreach($errors as $error) {
                $pile = $error->getPile();
                $keys = [];
                foreach ($pile as $p) {
                     
                    $keys[] = $p['value'];
                }
                edit_case($keys, $map, $error);
            }
            return $map;
        }
        private function clearErrors(): void {
            $this->errors = [];
        }
        private function hasErrors(): bool {
            return count($this->errors) > 0;
        }
        private function errorsExtend(Zec $zec): void {
            $errors = $zec->getErrors();
            foreach($errors as $error) {
                $this->setError($error);
            }
        }
    }
}