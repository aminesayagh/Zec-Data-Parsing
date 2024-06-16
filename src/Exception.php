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
        public const PARSER_ACCEPT_LOG = 'parser_accept_log';
        public const PARSER = 'parser';
        public const PARSER_VERSION = 'parser_version';
        use ZecPath, ZecErrorFrom, Meta;
        private ?string $validation;
        private bool $is_to_log = true;
        private ?array $errors;
        public function __construct(string $message, mixed ...$args) {
            $this->message = $message;
            parent::__construct($this->message, ...$args);
            $this->setMeta(ZecError::PARSER_ACCEPT_LOG, $this->is_to_log, false, 'private');
            $this->setMeta(ZecError::PARSER_VERSION, self::VERSION, true, 'private');
            $this->setMeta(ZecError::PARSER, null);
        }
        public function __get($name) {
            foreach (
                array_filter(get_class_methods($this), function ($method) {
                    return strpos($method, 'traitGet') === 0;
                }) as $method
            ) {
                $value = $this->$method($name);
                if ($value !== null) {
                    return $value;
                }
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

        public function info(bool $asChild = false): array|null {
            $info = [];
            $parser_accept_log = $this->getAdminMeta(ZecError::PARSER_ACCEPT_LOG);
            if (isset($parser_accept_log) && !$parser_accept_log) {
                return null;
            }
            if (isset($this->errors) && $this->errors !== null) {
                foreach ($this->errors as $error) {
                    $infoValue = $error->info(true);
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
            if ($asChild) {
                return $info;
            }
            return [$info];
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

