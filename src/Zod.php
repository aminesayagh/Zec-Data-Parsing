<?php
declare(strict_types=1);

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

if(!class_exists('ZodPath')) {
    class ZodPath {
        private array $_path = [];
        public function __construct(ZodPath $path = null) {
            if (!is_null($path)) {
                $this->_path = $path->get_path();
            }
        }
        public function get_path(): array {
            return $this->_path;
        }
        public function push(string $key): void {
            $this->_path[] = $key;
        }
        public function pop(): void {
            array_pop($this->_path);
        }
        public function get_path_string(): string {
            return implode('.', $this->_path);
        }
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
        private mixed $_value = null;
        private mixed $_default = null;
        private ZodPath $_path;
        private Zod|null $_parent = null;
        public function __construct(array $parsers = [], array $_errors = new ZodErrors()) {
            parent::__construct($parsers);
            $this->_errors = $_errors;
            $this->_path = new ZodPath();
        }
        /**
         * Creates a clone of the Zod object.
         *
         * This method is used to create a deep copy of the Zod object, including its internal state.
         * It clones the `_errors` property and filters the `parsers` array, removing any null values
         * and parsers with the key 'required'. It also clones each parser in the filtered array.
         *
         * @return void
         */
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
        /**
         * Dynamically handles method calls for the Zod class.
         *
         * @param string $name The name of the method being called.
         * @param mixed $arguments The arguments passed to the method.
         * @return mixed The result of the method call.
         * @throws ZodError If the method or parser is not found.
         */
        public function __call(string $name, mixed $arguments = []) {
            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
            if (bundler()->has_parser_key($name)) {
                $parser = bundler()->get_parser($name);

                if (is_array($arguments)) {
                    $parser->set_argument($arguments);
                } else {
                    $parser->set_argument([
                        $name => $arguments
                    ]);
                }
                
                $this->add_parser($parser);
                return $this;
            }
            throw new ZodError("Method $name not found");
        }
        /**
         * Parses the given value using the specified parsers.
         *
         * @param mixed $value The value to be parsed.
         * @param mixed $default The default value to be used if the parsed value is null.
         * @param Zod|null $parent The parent Zod instance, if any.
         * @return Zod The current Zod instance.
         */
        public function parse(mixed $value, mixed $default = null, Zod|null $parent = null): Zod {
            $this->_errors->reset();

            $this->_value = $value;
            $this->set_default($default);
            $before_parsers = [];

            if (!is_null($parent)) {
                $this->_path->push($parent->get_path_string());
            }

            $has_required = $this->has_parser_key('required');
            if (!$has_required && is_null($value)) {
                return $this;
            } 

            if (!is_null($parent)) {
                $this->_parent = $parent;
            }

            foreach ($this->get_parsers() as $parser) {
                $response = $parser->parse($this->_value, [
                    'default' => $this->_default,
                ], $this);

                if ($response->is_valid) {
                    $before_parsers[] = $parser->name;
                }
            }

            return $this;
        }
        /**
         * Sets the default value for the Zod instance.
         *
         * @param mixed $default The default value to be set.
         * @return Zod The updated Zod instance.
         */
        public function set_default(mixed $default): Zod {
            if(is_null($default)) {
                return $this;
            }
            $parser_of_default = clone $this;
            $parser_of_default->parse_or_throw($default);

            $this->_default = $default;
            return $this;
        }
        public function is_valid(): bool {
            return $this->_errors->is_empty();
        }
        public function get_errors(): ZodErrors {
            return $this->_errors;
        }
        /**
         * Sets an error for the current Zod instance and its parent, if any.
         *
         * @param ZodError $error The error to be set.
         * @return Zod The current Zod instance.
         */
        public function set_error(ZodError $error): Zod {
            // set the same error to the parent
            if (!is_null($this->_parent)) {
                $this->_parent->set_error($error);
            }

            $this->_errors->set_error($error);
            return $this;
        }
        /**
         * Parses the given value and throws an error if it is not valid.
         *
         * @param mixed $value The value to parse.
         * @param mixed $default The default value to use if the parsed value is invalid.
         * @return Zod The parsed value.
         * @throws mixed The error(s) if the parsed value is invalid.
         */
        public function parse_or_throw(mixed $value, mixed $default = null): Zod {
            $this->parse($value, $default);
            if (!$this->is_valid()) {
                throw $this->_errors;
            }
            return $this->get_value();
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