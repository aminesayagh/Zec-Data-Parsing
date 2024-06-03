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
        private $key = null;
        private $key_name = 'key'; 
        private array $children = [];
        static $KEY_TYPE_DEFAULT = 'key';
        public function __construct(array|string $body, mixed ...$args)
        {
            if (is_array($body)) {
                if (!isset($body['message']) && !isset($body['pile'])) {
                    throw new \Exception('Invalid message format');
                }
                $this->message = $body['message'];
                $this->setPile($body['pile'] ?? []);
                $this->pileMerge($body['pile'] ?? [], []);
            } else if (is_string($body)) {
                $this->message = $body;
            } else {
                throw new \Exception('Invalid message format');
            }
            parent::__construct($this->message, ...$args); 
        }
        public function setPile(array $pile): void {
            $this->pile = $pile;
        }
        public function set_children(array $errors): void {
            foreach ($errors as $error) {
                $this->children[] = $error;
            }
        }
        public function log() {
            $messageArray = $this->generateMessage();
            $formattedMessage = json_encode($messageArray, JSON_PRETTY_PRINT);
            echo $formattedMessage . PHP_EOL;
        }
        public function __get(string $name)
        {
            if ($name === 'key') {
                return $this->getKey();
            } else if ($name === 'message') {
                return $this->getMessage();
            }
            throw new \Exception("Property $name not found");
        }
        private function generateMessage(): array {
            $key = $this->getKey();
            $response = [];
            $response['message'] = $this->message;
            if (count($this->children) > 0) {
                $response['children'] = [];
                foreach ($this->children as $child) {
                    $response['children'][] = $child->generateMessage();
                }
            }
            if ($key !== '') {
                $response['key'] = $key;   
            }
            return $response;
        }
        public function getKey(): string {
            $this->key = $this->key ?? $this->getPileString();
            return $this->key;
        }
    }
}