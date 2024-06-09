<?php
declare(strict_types=1);

namespace Zec;
use Zec\Traits\{
    ZecPath,
    ZecErrorFrom
};
use Zec\Meta;

if (!class_exists('ZecError')) {
    class ZecError extends \Exception {
        public const VERSION = '1.0.0'; 
        use ZecPath, ZecErrorFrom, Meta;
        private ?string $validation;
        private bool $is_to_log = true;
        private ?array $errors;
        public function __construct(string $message, mixed ...$args) {
            $this->message = $message;
            parent::__construct($this->message, ...$args);
        }
        public function __get(string $name) {
            if (array_key_exists($name, $this->meta)) {
                return $this->meta[$name];
            }
            return match ($name) {
                'message' => $this->message,
                'validation' => $this->validation,
                'errors' => $this->errors,
                'path' => $this->path,
                default => throw new \Exception("Property $name not found"),
            };
        }
        public function path(array $path): void {
            $this->path = $path;
        }
        public function info(): array|null {
            // if errors is set, return errors
            // check if meta has a key (parser_accept_log) if yes, and the key value is false return empty array
            // else continue
            $info = [];
            if (isset($this->meta['parser_accept_log']) && !$this->meta['parser_accept_log']) {
                return null;
            }
            if (isset($this->errors) && !is_null($this->errors)) {
                foreach ($this->errors as $error) {
                    $infoValue = $error->info();
                    if (!is_null($infoValue)) {
                        $info[] = $infoValue;
                    }
                }
                return $info;
            }
            $info = [];
            foreach ($this->meta as $key => $value) {
                $info[$key] = $value;
            }
            $info['message'] = $this->message;
            if (isset($this->validation) && !is_null($this->validation)) {
                $info['validation'] = $this->validation;
            }
            if ($this->hasPath()) {
                $info['path'] = $this->getPath();
            }
            return $info;
        }
        public function log() {
            $info = $this->info();
            echo json_encode($info, JSON_PRETTY_PRINT) . PHP_EOL;
        }
    }
}

