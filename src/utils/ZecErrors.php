<?php
declare (strict_types = 1);

namespace Zec;

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
            throw new ZecError::from_errors($this->errors);
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