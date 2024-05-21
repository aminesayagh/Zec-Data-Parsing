<?php
declare(strict_types=1);

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
            parent::__construct($message . ' ' . $key, $code, $previous);
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
        public function get_message() {
            return $this->message . ' ' . $this->key;
        }
    }
}