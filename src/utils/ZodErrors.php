<?php
declare (strict_types = 1);

namespace Zod;

if(!trait_exists('ZodErrors')) {
    trait ZodErrors {
        private array $_errors = [];
        private function set_error(ZodError $error): void {
            // check if error is already in the list
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
        private function errors_extend(Zod $zod): void {
            $errors = $zod->get_errors();
            foreach($errors as $error) {
                $this->set_error($error);
            }
        }
    }
}