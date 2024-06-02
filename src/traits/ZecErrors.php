<?php
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\Zec;
use Zec\ZecError;
use Exception;

if(!trait_exists('ZecErrors')) {
    trait ZecErrors {
        private array $_errors = [];
        private function set_error(ZecError $error): void {
            foreach($this->_errors as $e) {
                if($e->get_message() === $error->get_message()) {
                    return;
                }
            }
            $this->_errors[] = $error;
        }
        private function get_errors(): array {
            return $this->_errors;
        }
        private function throw_errors(): void {
            if(!$this->has_errors()) {
                throw new Exception('No errors found');
            }
            throw ZecError::from_errors($this);
        }
        public function get_map_errors(): array {
            $map = [];
            $errors = $this->get_errors();
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
                $pile = $error->get_pile();
                $keys = [];
                foreach ($pile as $i => $p) {
                     
                    $keys[] = $p['value'];
                }
                edit_case($keys, $map, $error);
            }
            return $map;
        }
        private function clear_errors(): void {
            $this->_errors = [];
        }  
        private function message_errors() {
            $errors = $this->get_errors();
            $errors_list = [];
            foreach($errors as $error) {
                // $message .= $error->get_message() . PHP_EOL;
                $errors_list[] = $error->get_message();
            }
            return $errors_list;
        }
        private function has_errors(): bool {
            return count($this->_errors) > 0;
        }
        private function errors_extend(Zec $zec): void {
            $errors = $zec->get_errors();
            foreach($errors as $error) {
                $this->set_error($error);
            }
        }
    }
}