<?php

if(!class_exists('ZodError')) {
    class ZodError extends Exception
    {   
        /**
         * @var string|null The key associated with the error.
         */
        private string|null $key;
        public function __construct(string $message, ?string $key = null ,array $options = []) {
            $code = $options['code'] ?? 0;
            $previous = $options['previous'] ?? null;
            $this->key = $key;
            parent::__construct($message, $code, $previous);
        }
        public function __get(string $name) {
            if ($name === 'key') {
                return $this->key;
            }
            throw new Exception("Property $name not found");
        }
    }
}

if(!class_exists('ZodErrors')) {
    interface IZodErrors {  
        public function __construct(array $errors);
        public function setError(ZodError $error);
        // finish this interface 
        
    };

    class ZodErrors
    {
        private array $errors = [];
        public function __construct(array $errors = []) {
            foreach ($errors as $error) {
                $this->setError($error);
            }
            $this->errors = $errors;
        }
        private function setError(ZodError $error): void {
            if (!($error instanceof ZodError)) {
                throw new Exception("Error must be an instance of ZodError");
            }
            $this->errors[] = $error;
        }
        private function getErrors(): array {
            return $this->errors;
        }
        public function getError(string $key): ?ZodError {
            foreach ($this->errors as $error) {
                if ($error->key === $key) {
                    return $error;
                }
            }
            return null;
        }
        public function reset(): void {
            $this->errors = [];
        }
        public function __destruct() {
            foreach ($this->errors as $error) {
                $error->__destruct();
            }
            $this->reset();
        }
        public function __get(string $name) {
            if ($name === 'errors') {
                return $this->getErrors();
            } elseif ($name === 'length') {
                return count($this->errors);
            } elseif ($name === 'isEmpty') {
                return count($this->errors) === 0;
            } elseif ($name === 'first') {
                return $this->errors[0] ?? null;
            } elseif ($name === 'last') {
                return $this->errors[count($this->errors) - 1] ?? null;
            } else if ($name === 'length') {
                return count($this->errors);
            }
            throw new Exception("Property $name not found");
        }
        public function __set(string $name, $value) {
            if ($name === 'error') {
                $this->setError($value);
            }
            throw new Exception("Property $name not found");
        }
    }
}

if(!function_exists('is_zod_error')) {
    function is_zod_error($value): bool {
        return $value instanceof ZodError;
    }
}

if(!function_exists('is_zod_errors')) {
    function is_zod_errors($value): bool {
        return $value instanceof ZodErrors;
    }
}