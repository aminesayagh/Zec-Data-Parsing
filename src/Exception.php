<?php
declare(strict_types=1);

namespace Zec;

if (!class_exists('ZecError')) {
    /**
     * Represents an error that occurred during the execution of the Zec library.
     */
    class ZecError extends \Exception
    {
        public const VERSION = '1.0.0'; 
        use Traits\ZecPath, Traits\ZecErrorFrom;
        private $_key = null;
        private $_key_name = 'key'; 
        private array $_children = [];
        private string $_message = '';
        static $KEY_TYPE_DEFAULT = 'key';
        public function __construct(array|string $body, mixed ...$args)
        {
            if (is_array($body)) {
                if (!isset($body['message']) && !isset($body['pile'])) {
                    throw new \Exception('Invalid message format');
                }
                $this->_message = $body['message'];
                $this->set_pile($body['pile'] ?? []);
                $this->pile_merge($body['pile'] ?? [], []);
            } else if (is_string($body)) {
                $this->_message = $body;
            } else {
                throw new \Exception('Invalid message format');
            }
            parent::__construct($this->_message,...$args); 
        }
        public function set_pile(array $pile): void {
            $this->_pile = $pile;
        }
        public function set_children(array $errors): void {
            foreach ($errors as $error) {
                $this->_children[] = $error;
            }
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