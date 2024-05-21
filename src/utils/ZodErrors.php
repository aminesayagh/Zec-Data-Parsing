<?php
declare (strict_types = 1);

namespace Zod;

if(!trait_exists('ZodErrors')) {
    trait ZodErrors {
        private array $_errors = [];
        private function set_error(ZodError $error): void {
            $this->_errors[] = $error;
        }
        private function get_errors(): array {
            return $this->_errors;
        }
        private function clear_errors(): void {
            $this->_errors = [];
        }  
        private function message_errors() {
            $errors = $this->get_errors();
            $message = '';
            foreach($errors as $error) {
                $message .= $error->get_message() . PHP_EOL;
            }
            return $message;
        }
        private function has_errors(): bool {
            return count($this->_errors) > 0;
        }
    }
}