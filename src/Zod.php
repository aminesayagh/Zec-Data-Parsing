<?php

namespace Zod;
use ArrayObject;

require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';

if(!interface_exists('IZod')) {
    interface IZod {

    }
}

if(!class_exists('Zod')) {
    class Zod extends Parsers implements IZod {
        /**
         * Holds the array of errors encountered during validation.
         *
         * @var ZodErrors
         */
        private ZodErrors $_errors = new ZodErrors();
        private string $type = 'field';
        private bool $has_valid_parser_state = false;
        private bool $parser_sorted = false;
        private bool $has_valid_default = false;
        public function __construct(array $parsers = [], array $_errors = new ZodErrors()) {
            parent::__construct($parsers);
            $this->_errors = $_errors;

            
        }
        private function assign_parser(string $key): Zod {
            $parser = bundler()->get_parser($key);

            if ($this->has_parser($parser)) {
                return $this;
            }

            $this->add_parser($parser);
            return $this;
        }
        public function __call(string $name, $arguments) {
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                $parser = bundler()->get_parser($name);
                $parser->set_parser_arguments($arguments);
            }

        }
        public function parse(mixed $value, mixed $default = null): Zod {
            $this->_errors->reset();

            return $this;
        } 
    }
}