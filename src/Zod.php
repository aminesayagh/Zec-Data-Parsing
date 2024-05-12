<?php

namespace Zod;

require_once ZOD_PATH . '/src/parsers/Parser.php';
require_once ZOD_PATH . '/src/parsers/Parsers.php';
require_once ZOD_PATH . '/src/Exception.php';

if(!interface_exists('IZod')) {
    interface IZod {
        public function __construct(array $parsers = [], array $_errors = new ZodErrors());
        public function __clone();
        public function __call(string $name, mixed $arguments = []);
        public function parse(mixed $value, mixed $default = null): Zod;
        public function set_default(mixed $default): Zod;
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
        public function __call(string $name, mixed $arguments = []) {
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                $parser = bundler()->get_parser($name);

                if (is_array($arguments)) {
                    $parser->set_arguments($arguments);
                } else {
                    $parser->set_arguments([
                        $name => $arguments
                    ]);
                }
                $this->add_parser($parser);
                return $this;
            }
            throw new ZodError("Method $name not found");
        }
        public function parse(mixed $value, mixed $default = null, array $path = null): Zod {
            $this->_errors->reset();

            $this->_value = $value;
            $this->set_default($default);
            $before_parsers = [];

            $has_required = $this->has_parser_key('required');
            if (!$has_required && is_null($value)) {
                return $this;
            } 

            foreach ($this->get_parsers() as $parser) {
                $response = $parser->parse($this->_value, [
                    'before_parser' => $before_parsers,
                    'path' => $path,
                ]);

                if ($response->is_valid) {
                    $before_parsers[] = $parser;
                } else {
                    $error = $response->error;
                    $this->_errors->set_error($error);
                }
            }

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

if (!function_exists('zod')) {
    /**
     * Returns an instance of the Zod class.
     *
     * @param array $parsers The array of parsers to assign to the Zod instance.
     * @param array $errors The array of errors to assign to the Zod instance.
     * @return Zod The instance of the Zod class.
     */
    function zod(array $parsers = [], array $errors = []): Zod {
        return new Zod($parsers, $errors);
    }
}

if (!function_exists('is_zod')) {
    /**
     * Checks if the given value is an instance of the Zod class.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is an instance of Zod, false otherwise.
     */
    function is_zod($value): bool {
        return is_a($value, 'Zod');
    }
}

if (!function_exists('is_zod_errors')) {
    /**
     * Checks if the given value is an instance of the ZodErrors class.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is an instance of ZodErrors, false otherwise.
     */
    function is_zod_errors($value): bool {
        return is_a($value, 'ZodErrors');
    }
}

if (!function_exists('is_zod_error')) {
    /**
     * Checks if the given value is an instance of the ZodError class.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is an instance of ZodError, false otherwise.
     */
    function is_zod_error($value): bool {
        return is_a($value, 'ZodError');
    }
}

if (!function_exists('z')) {
    /**
     * Returns an instance of the Zod class.
     *
     * @param array $parsers The array of parsers to assign to the Zod instance.
     * @param array $errors The array of errors to assign to the Zod instance.
     * @return Zod The instance of the Zod class.
     */
    function z(array $parsers = [], array $errors = []): Zod {
        return zod($parsers, $errors);
    }
}