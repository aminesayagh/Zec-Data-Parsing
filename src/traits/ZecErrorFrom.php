<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\ZecError as ZecError;
use Zec\Zec as Zec;

if(!trait_exists('ZecErrorFrom')) {
    trait ZecErrorFrom {
        static function fromException(\Exception $e): ZecError {
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
        static function fromMessage(string $message): ZecError {
            return new ZecError($message);
        }
        static function fromMessagePile(string $message, array $pile): ZecError {
            return new ZecError([
                'message' => $message,
                'pile' => $pile
            ]);
        }
        static function fromMessageKey(string $message, string $key): ZecError {
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
        static function fromErrors(Zec $zec): ZecError {
            $error = ZecError::fromMessage('Multiple errors occurred');
            $error_map = $zec->getMapErrors();
            // foreach($error_map as $key => $value) {
            //     $error->set_children($value);
            // }
            return $error;
        }
    }
}