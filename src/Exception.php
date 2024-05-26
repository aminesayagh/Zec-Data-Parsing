<?php
declare(strict_types=1);

namespace Zec;

if (!class_exists('ZecError')) {
    /**
     * Represents an error that occurred during the execution of the Zec library.
     */
    class ZecError extends \Exception
    {
        private $_key = 'unknown';
        private $_key_name = 'key'; 
        private array|string $_message = '';
        static $KEY_TYPE_DEFAULT = 'key';
        /**
         * The key associated with the error, if applicable.
         *
         * @var string|null
         */
        public function __construct(array $message, mixed ...$args)
        {
            if (is_array($message) && (isset($message['key']) && isset($message['message']))) {
                $this->_key = $message['key'];
                $this->_message = $message['message'];
                parent::__construct(json_encode($this->_message),...$args);
            } else {
                throw new \Exception('Invalid message format');
            }
        }
        static function generate_message(string|array $message, string $key = 'unknown', string $key_type = 'key'): array
        {
            return [
                $key_type => $key,
                'message' => $message,
            ];
        }
        public function log() {

            $messageArray = $this->get_message();
            $formattedMessage = json_encode($messageArray, JSON_PRETTY_PRINT);
            echo $formattedMessage . PHP_EOL;
        }
        public function __get(string $name)
        {
            if ($name === 'key') {
                return $this->_key;
            } else if ($name === 'message') {
                return $this->message;
            }
            throw new \Exception("Property $name not found");
        }
        public function get_message(): array
        {
            return [
                $this->_key_name => $this->_key,
                'message' => $this->_message,
            ];
        }
    }
}