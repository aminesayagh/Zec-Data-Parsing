<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\ZecError as ZecError;
use Zec\Zec as Zec;

if(!trait_exists('ZecErrorFrom')) {
    trait ZecErrorFrom {
        static function fromMessage(string $message): ZecError {
            return new ZecError($message);
        }
        static function fromErrors(Zec $zec): ZecError {
            $error = ZecError::fromMessage('Multiple errors occurred');
            $errors = $zec->getErrors();
            foreach ($errors as $err) {
                $error->setError($err);
            }
            return $error;
        }
        static function fromMessagePath(string $message, array $path, array $meta = []): ZecError {
            $error = ZecError::fromMessage($message);
            $error->setPath($path);
            foreach ($meta as $key => $value) {
                $error->setMeta($key, $value);
            }
            return $error;
        }
    }
}