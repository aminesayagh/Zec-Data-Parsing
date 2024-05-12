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
        private mixed $_value = null;
        private mixed $_default = null;
        private bool $has_valid_parser_state = false;
        private bool $parser_sorted = false;
        private bool $has_valid_default = false;
        public function __construct(array $parsers = [], array $_errors = new ZodErrors()) {
            parent::__construct($parsers);
            $this->_errors = $_errors;
        }
        public function __clone() {
            $this->_errors = clone $this->_errors;
            $this->parsers = array_filter($this->parsers, function($parser) {
                if (is_null($parser)) {
                    return false;
                }
                if ($parser->key == 'required') {
                    return false;
                }
                return $parser->clone();
            });
        }
        public function __call(string $name, array $arguments = []) {
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                $parser = bundler()->get_parser($name);

                $parser->set_arguments($arguments);
                $this->add_parser($parser);
            }

        }
        public function parse(mixed $value, mixed $default = null): Zod {
            $this->_errors->reset();

            $this->_value = $value;
            $this->set_default($default);

            return $this;
        } 
        public function set_default(mixed $default): Zod {
            if (!is_null($default)) {
                return $this;
            }
            if ($this->has_valid_default) {
                return $this;
            }
            if ($this->length() == 0) {
                return $this;
            }
            $parser_of_default = clone $this;
            $parser_of_default->parse_or_throw($default);

            $this->_default = $default;
            $this->has_valid_default = true;
            return $this;
        }
    }
}