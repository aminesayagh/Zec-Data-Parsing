<?php 
declare (strict_types = 1);

namespace Zec\Traits;
use Zec\ZecError as ZecError;
use Zec\Zec as Zec;

if(!trait_exists('ZecErrorFrom')) {
    trait ZecErrorFrom {
        static function from_exception(\Exception $e): ZecError {
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
        static function from_message(string $message): ZecError {
            return new ZecError($message);
        }
        static function from_message_pile(string $message, array $pile): ZecError {
            return new ZecError([
                'message' => $message,
                'pile' => $pile
            ]);
        }
        static function from_message_key(string $message, string $key): ZecError {
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
        static function from_errors(Zec $zec): ZecError {
            $error = ZecError::from_message('Multiple errors occurred');
            $error_map = $zec->get_map_errors();
            return $error;
        }
    }
}