<?php
declare(strict_types=1);

namespace Zec\Traits;

use Zec\Zec;
use Zec\ZecError;
use Exception;


if (!trait_exists('ZecErrors')) {
    trait ZecErrors
    {
        private array $_errors = [];
        private function setError(ZecError $error): void
        {
            foreach ($this->_errors as $e) {
                if ($e->message === $error->message && $e->path === $error->path) {
                    return;
                }
            }
            $this->_errors[] = $error;
        }
        private function traitGetErrors(string $name) {
            return match ($name) {
                'errors' => $this->errors(),
                'error' => $this->error(),
                'hasErrors' => $this->hasErrors(),
                'countErrors' => $this->countErrors(),
                'countPublicErrors' => $this->countPublicErrors(),
                default => null,
            };
        }
        public function errors(): array
        {
            return $this->_errors;
        }
        public function error(): ZecError|null
        {
            if (!$this->hasErrors()) {
                return null;
            }
            return $this->_errors[0];
        }
        private function throwErrors(): void
        {
            if (!$this->hasErrors()) {
                throw new Exception('No errors found');
            }
            throw ZecError::fromErrors($this);
        }
        private function clearErrors(): void
        {
            $this->_errors = [];
        }
        private function hasErrors(): bool
        {
            return count($this->_errors) > 0;
        }
        // count number of errors
        private function countErrors(): int
        {
            return count($this->_errors);
        }
        public function countPublicErrors(): int {
            $count = 0;
            foreach ($this->_errors as $error) {
                if ($error->getAdminMeta(ZecError::PARSER_ACCEPT_LOG)) {
                    $count++;
                }
            }
            return $count;
        }
        private function errorsExtend(Zec $zec): void
        {
            $_errors = $zec->errors();
            foreach ($_errors as $error) {
                $this->setError($error);
            }
            $this->sendErrorsToParent();
        }
    }
}