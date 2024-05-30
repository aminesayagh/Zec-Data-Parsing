<?php
declare(strict_types=1);

namespace Zec;

// STRUCT OF AN ERROR MESSAGE
// {
//     "message": "Error message",
//     "key": "[flag].parser"
//     "children": [
//         {
//             "message": "Error message",
//             "key": "[flag].parser"
//         }
//     ]
// }

if (!class_exists('ZecError')) {
    /**
     * Represents an error that occurred during the execution of the Zec library.
     */
    class ZecError extends \Exception
    {
        use ZecPath, ZecErrorFrom;
        private $_key = null;
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
                $this->_message = $message;
                parent::__construct($message,...$args);
            } else {
                throw new \Exception('Invalid message format');
            }
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
            $key = $this->get_key();
            $response = [];
            $response['message'] = $this->_message;
            if (count($this->_children) > 0) {
                $response['children'] = [];
                foreach ($this->_children as $child) {
                    $response['children'][] = $child->get_message();
                }
            }
            if ($key !== '') {
                $response['key'] = $key;   
            }
            return $response;
        }
        public function get_key(): string {
            $this->_key = $this->_key ?? $this->get_pile_string();
            return $this->_key;
        }
    }
}