<?php
declare(strict_types=1);

namespace Zod;
use Zod\FIELD as FK;

if (!trait_exists('ParserArgument')) {
    trait ParserArgument {
        private array $_arguments = [];
        private callable|null $_parser_arguments = null;
        private mixed $_handler_argument = null;
        private bool $_is_valid_argument = false;
        private array $_default_argument = [];
        public function init_argument(string $main_key, array $default_argument, callable $_parser_arguments) {
            $this->_main_key = $main_key;
            $this->set_default_argument($default_argument);
            $this->set_parser_arguments($_parser_arguments);
        }
        private function set_default_argument(array $default_argument): void {
            if (!is_array($default_argument)) {
                throw new ZodError('Default argument must be an array');
            }
            $this->_default_argument = $default_argument;
        }
        private function set_parser_arguments(callable $_parser_arguments): void {
            if (!is_callable($_parser_arguments)) {
                throw new ZodError('Parser argument must be a callable');
            }
            $this->_parser_arguments = $_parser_arguments;
        }
        public function get_argument(): array {
            $merged_argument = array_merge($this->_default_argument, $this->_arguments);
            return $merged_argument;
        }
        public function valid_argument(Zod $parent = null) {
            $merged_argument = $this->merge_argument();
            if (!$this->_is_valid_argument) {
                
            }
            return $merged_argument;
        }
    }
}