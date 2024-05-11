<?php

use Zod\Bundler;

use Zod\FIELD as FK; // FK: Field Key
use Zod\PARSER as PK; // PK: Parser Key
use Zod\TYPE as ZT; // ZT: Zod Type


require_once ZOD_PATH . '/src/config/Bundler.php';

// TODO:
// Add parser arguments, a zod parser for the argument values;
Zod\bundler()->assign_parser_config(PK\KEY['EMAIL'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid email address',
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'domain' => 'gmail.com'
    ],
    FK\KEY['PARSER'] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid email format";
        $valid = filter_var($value, FILTER_VALIDATE_EMAIL) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['REQUIRED'], [
    FK\KEY['ACCEPT'] => [
        PK\KEY['EMAIL'],
        PK\KEY['DATE'],
        PK\KEY['BOOL'],
        PK\KEY['INPUT'],
        PK\KEY['NUMBER'],
        PK\KEY['URL'],
        PK\KEY['STRING']
    ],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'This field is required'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "This field is required";
        if (empty($value)) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['DATE'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid date format',
        'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid date format";
        $pattern = $args['pattern'] ?? '/^(\d{4})-(\d{2})-(\d{2})$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['BOOL'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid boolean value'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid boolean value";
        $valid = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['INPUT'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid input value'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid input value";
        $valid = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['STRING'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid string value'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid string value";
        $valid = is_string($value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['URL'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid URL format',
        'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid URL format";
        $pattern = $args['pattern'] ?? '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['NUMBER'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid number format',
        'pattern' => '/^\d+$/'
    ],
    FK\KEY['PARSER'] => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid number format";
        $pattern = $args['pattern'] ?? '/^\d+$/';
        $valid = preg_match($pattern, $value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['OPTIONS'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid option value',
        'options' => []
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid option value";
        $valid = in_array($value, $args['options']);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
])->assign_parser_config(PK\KEY['EACH'], [
    FK\KEY['ACCEPT'] => [],
    FK\KEY['IS_INIT_STATE'] => true,
    FK\KEY['PARSER_ARGUMENTS'] => function () {
        return null;
    },
    FK\KEY['DEFAULT_ARGUMENT'] => [
        'message' => 'Invalid value'
    ],
    FK\KEY['PARSER']  => function (mixed $value, array $args = []): string|bool {
        $errorMessage = $args['message'] ?? "Invalid value";
        $valid = is_array($value);
        if (!$valid) {
            return $errorMessage;
        }
        return true;
    }
]);