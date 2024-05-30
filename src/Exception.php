<?php
declare(strict_types=1);

namespace Zec;

if (!class_exists('ZecError')) {
    /**
     * Represents an error that occurred during the execution of the Zec library.
     */
    class ZecError extends \Exception
    {
        use ZecPath;
        private $_key = 'unknown';
        private $_key_name = 'key'; 
        private array $_children = [];
        private array|string $_message = '';
        static $KEY_TYPE_DEFAULT = 'key';
        /**
         * The key associated with the error, if applicable.
         *
         * @var string|null
         */
        public function __construct(array|string $message, mixed ...$args)
        {
            if (is_array($message)) {
                if (!isset($message['message']) && !isset($message['pile'])) {
                    throw new \Exception('Invalid message format');
                }
                $this->_message = $message['message'];
                $this->set_pile($message['pile'] ?? []);
                $this->pile_merge($message['pile'] ?? [], []);
                parent::__construct(json_encode($this->_message),...$args); 
            } else if (is_string($message)) {
                $this->_key = 'unknown';
                $this->_message = $message;
                parent::__construct($message,...$args);
            } else {
                throw new \Exception('Invalid message format');
            }
        }
        static function from_exception(\Exception $e): ZecError {
            return new ZecError([
                'message' => $e->getMessage(),
                'pile' => [
                    [
                        'key' => 'exception',
                        'value' => $e
                    ]
                ]
            ], $e->getCode(), $e->getPrevious());
        }
        static function from_message(string $message): ZecError {
            return new ZecError($message);
        }
        static function from_message_pile(string $message, array $pile): ZecError {
            return new ZecError([
                'message' => $message,
                'pile' => $pile
            ]);
        }
        static function from_message_key(string $message, string $key): ZecError {
            return new ZecError([
                'message' => $message,
                'pile' => [
                    [
                        'key' => 'key',
                        'value' => $key
                    ]
                ]
            ]);
        }
        static function from_errors(array $errors): ZecError {
            $error = ZecError::from_message('Multiple errors occurred');
            $error->set_children($errors);
            return $error;
        }
        public function set_pile(array $pile): void {
            $this->_pile = $pile;
        }
        public function set_children(array $errors): void {
            $this->_children = $errors;
        }
        public function log() {
            $messageArray = $this->get_message();
            $formattedMessage = json_encode($messageArray, JSON_PRETTY_PRINT);
            echo $formattedMessage . PHP_EOL;
        }
        public function __get(string $name)
        {
            if ($name === 'key') {
                return $this->get_key();
            } else if ($name === 'message') {
                return $this->message;
            }
            throw new \Exception("Property $name not found");
        }
        public function get_message(): array
        {
            return [
                $this->_key_name => $this->get_key(),
                'message' => $this->_message,
            ];
        }
        public function get_key(): string {
            $this->_key = $this->_key ?? $this->get_pile_string();
            return $this->_key;
        }
    }
}