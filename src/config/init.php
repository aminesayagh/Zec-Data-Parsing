<?php

use Zod\Bundler;

use Zod\FIELD\KEY as FK; // FK: Field Key
use Zod\PARSER\KEY as PK; // PK: Parser Key
use Zod\Parser\KEY as ZT; // ZT: Zod Type


require_once ZOD_PATH . '/src/config/Bundler.php';

// TODO:
// Add parser arguments, a zod parser for the argument values;
Zod\bundler()->assign_parser_config(PK\EMAIL, [
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
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid email format";
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\REQUIRED, [
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
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "This field is required";
        if (empty($value)) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\DATE, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid date format',
        'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid date format";
        $pattern = $args['pattern'] ?? '/^(\d{4})-(\d{2})-(\d{2})$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\BOOL, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid boolean value'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid boolean value";
        $valid = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\INPUT, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid input value'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid input value";
        $valid = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\STRING, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid string value'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid string value";
        $valid = is_string($value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\URL, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid URL format',
        'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid URL format";
        $pattern = $args['pattern'] ?? '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\NUMBER, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid number format',
        'pattern' => '/^\d+$/'
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid number format";
        $pattern = $args['pattern'] ?? '/^\d+$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\OPTIONS, [
    [FK\ACCEPT] => [],
    [FK\IS_INIT_STATE] => true,
    [FK\PARSER_ARGUMENTS] => function () {
        return null;
    },
    [FK\DEFAULT_ARGUMENT] => [
        'message' => 'Invalid option value',
        'options' => []
    ],
    [FK\PARSER] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid option value";
        $valid = in_array($value, $args['options']);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
]);

Zod\bundler()->assign_zod_type(ZT\FIELD)->assign_zod_type(ZT\OPTIONS)->assign_zod_type(ZT\EACH);