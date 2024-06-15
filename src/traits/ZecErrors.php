<?php
declare(strict_types=1);

namespace Zec\Traits;

use Zec\Zec;
use Zec\ZecError;
use Exception;


if (!trait_exists('ZecErrors')) {
    trait ZecErrors
    {
        private array $errors = [];
        private function setError(ZecError $error): void
        {
            foreach ($this->errors as $e) {
                if ($e->message === $error->message && $e->path === $error->path) {
                    return;
                }
            }
            $this->errors[] = $error;
        }
        private function getErrors(): array
        {
            return $this->errors;
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
            $this->errors = [];
        }
        private function hasErrors(): bool
        {
            return count($this->errors) > 0;
        }
        // count number of errors
        private function countErrors(): int
        {
            return count($this->errors);
        }
        public function countPublicErrors(): int {
            $count = 0;
            foreach ($this->errors as $error) {
                if ($error->getAdminMeta(ZecError::PARSER_ACCEPT_LOG)) {
                    $count++;
                }
            }
            return $count;
        }
        private function errorsExtend(Zec $zec): void
        {
            $errors = $zec->getErrors();
            foreach ($errors as $error) {
                $this->setError($error);
            }
            $this->sendErrorsToParent();
        }
    }
}