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
            $this->setMeta('parser_accept_log', $this->is_to_log, false, 'private');
            $this->setMeta('parser_version', self::VERSION, true, 'private');
            $this->setMeta('parser', null);
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
            $parser_accept_log = $this->getAdminMeta('parser_accept_log');
            if (isset($parser_accept_log) && !$parser_accept_log) {
                return null;
            }
            if (isset($this->errors) && $this->errors !== null) {
                foreach ($this->errors as $error) {
                    $infoValue = $error->info();
                    if ($infoValue !== null) {
                        $info[] = $infoValue;
                    }
                }
                return $info;
            }
            $info = [];
            foreach ($this->getMetadata() as $key => $value) {
                $info[$key] = $value;
            }
            $info['message'] = $this->message;
            if (isset($this->validation) && $this->validation !== null) {
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
        public function setError(ZecError $error): void {
            $this->errors[] = $error;
        }
    }
}

