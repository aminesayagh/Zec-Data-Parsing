<?php

namespace Zod;

if (!class_exists('ZodError')) {
    /**
     * Represents an error that occurred during the execution of the Zod library.
     */
    class ZodError extends \Exception {
        /**
         * The key associated with the error, if applicable.
         *
         * @var string|null
         */
        private ?string $key;

        /**
         * Constructs a new ZodError instance.
         *
         * @param string $message The error message.
         * @param string|null $key The key associated with the error, if applicable.
         * @param array $options Additional options for the error.
         */
        public function __construct(string $message, ?string $key = null, array $options = []) {
            $code = $options['code'] ?? 0;
            $previous = $options['previous'] ?? null;
            $this->key = $key;
            parent::__construct($message, $code, $previous);
        }
    
        /**
         * Magic method to handle getting inaccessible properties.
         *
         * @param string $name The name of the property being accessed.
         * @return mixed The value of the property being accessed.
         */
        public function __get(string $name) {
            if($name === 'key') {
                return $this->key;
            }
            throw new \Exception("Property $name not found");
        }
    }
}

if (!interface_exists('IZodErrors')) {
    interface IZodErrors {
        public function __construct(array $errors);
        public function setError(ZodError $error);
    }
}

if(!class_exists('ZodErrors')) {
    /**
     * Class ZodErrors
     * 
     * Represents a collection of ZodError objects.
     */
    class ZodErrors {
        /**
         * @var array An array to store the ZodError objects.
         */
        private array $errors = [];

        /**
         * ZodErrors constructor.
         * 
         * @param array $errors An optional array of ZodError objects to initialize the collection.
         */
        public function __construct(array $errors = []) {
            foreach ($errors as $error) {
                $this->_set_error($error);
            }
            $this->errors = $errors;
        }

        /**
         * Sets a ZodError object in the collection.
         * 
         * @param ZodError $error The ZodError object to set.
         * @throws \Exception If the provided error is not an instance of ZodError.
         */
        private function _set_error(ZodError $error): void {
            if (!(is_a($error, 'ZodError'))) {
                throw new \Exception('Error must be an instance of ZodError');
            }
            $this->errors[] = $error;
        }

        /**
         * Returns all the ZodError objects in the collection.
         * 
         * @return array An array of ZodError objects.
         */
        public function getErrors(): array {
            return $this->errors;
        }

        /**
         * Checks if the collection has any errors.
         * 
         * @return bool True if the collection has errors, false otherwise.
         */
        public function hasErrors(): bool {
            return count($this->errors) > 0;
        }

        /**
         * Returns the ZodError object with the specified key.
         * 
         * @param string $key The key of the ZodError object to retrieve.
         * @return ZodError|null The ZodError object with the specified key, or null if not found.
         */
        public function getError(string $key): ?ZodError {
            foreach ($this->errors as $error) {
                if ($error->key === $key) {
                    return $error;
                }
            }
            return null;
        }

        /**
         * Resets the collection by removing all the ZodError objects.
         */
        public function reset(): void {
            $this->errors = [];
        }

        /**
         * Destructor method that cleans up the collection by un-setting all the ZodError objects and resetting the collection.
         */
        public function __destruct() {
            foreach ($this->errors as $error) {
                unset($error);
            }
            $this->reset();
        }

        /**
         * Magic method to get the value of a property.
         * 
         * @param string $name The name of the property to get.
         * @return mixed The value of the property.
         * @throws \Exception If the specified property is not found.
         */
        public function __get(string $name) {
            switch ($name) {
                case 'errors': 
                    return $this->errors;
                case 'length':
                    return count($this->errors);
                case 'isEmpty':
                    return count($this->errors) === 0;
                case 'first':
                    return $this->errors[0] ?? null;
                case 'last':
                    return $this->errors[count($this->errors) - 1] ?? null;
                case 'keys':
                    return array_map(function($error) {
                        return $error->key;
                    }, $this->errors);
                default:
                    throw new \Exception("Property $name not found");
            }
        }

        /**
         * Magic method to set the value of a property.
         * 
         * @param string $name The name of the property to set.
         * @param mixed $value The value to set.
         * @throws \Exception If the specified property is not found.
         */
        public function __set(string $name, $value) {
            throw new \Exception("Property $name not found");
        }
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