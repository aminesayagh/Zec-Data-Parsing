<?php

use Zod\Bundler;
use Zod\FIELD\KEY as FK;
use Zod\PARSER\KEY as PK; // Parser key


if (!defined('ZOD_DEFAULT_MODEL')) {
    define('ZOD_DEFAULT_MODEL', [
        Bundler::valid_parser_config(PK\EMAIL, [
            FK\ACCEPT => [],
            FK\IS_INIT_STATE => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid email address',
                'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'domain' => 'gmail.com'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid email format";
                $valid = filter_var($value, FILTER_VALIDATE_EMAIL) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\REQUIRED, [
            [FK\ACCEPT] => [
                PK\EMAIL,
                PK\DATE,
                PK\BOOL,
                PK\INPUT,
                PK\NUMBER,
                PK\URL,
                PK\STRING
            ],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'This field is required'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "This field is required";
                if (empty($value)) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\DATE, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid date format',
                'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid date format";
                $pattern = $options['pattern'] ?? '/^(\d{4})-(\d{2})-(\d{2})$/';
                $valid = preg_match($pattern, $value);
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\BOOL, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid boolean value'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid boolean value";
                $valid = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\INPUT, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid input value'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid input value";
                $valid = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\STRING, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid string value'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid string value";
                $valid = is_string($value);
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\URL, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid URL format',
                'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid URL format";
                $pattern = $options['pattern'] ?? '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/';
                $valid = preg_match($pattern, $value);
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ]),
        Bundler::valid_parser_config(PK\NUMBER, [
            [FK\ACCEPT] => [],
            [FK\IS_INIT_STATE] => true,
            [FK\PARSER_ARGUMENTS] => function () {
                return null;
            },
            [FK\DEFAULT_ARGUMENT] => [
                'message' => 'Invalid number format',
                'pattern' => '/^\d+$/'
            ],
            [FK\PARSER] => function (mixed $value, array $options = []): string|bool {
                $errorMessage = $options['message'] ?? "Invalid number format";
                $pattern = $options['pattern'] ?? '/^\d+$/';
                $valid = preg_match($pattern, $value);
                if (!$valid) {
                    return $errorMessage;
                }
                return true;
            }
        ])
    ]);
}