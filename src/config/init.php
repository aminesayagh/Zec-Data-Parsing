<?php

use Zod\Bundler;

use Zod\FIELD as FK; // FK: Field Key
use Zod\PARSER as PK; // PK: Parser Key
use Zod\TYPE as ZT; // ZT: Zod Type


require_once ZOD_PATH . '/src/config/Bundler.php';

// TODO:
// Add parser arguments, a zod parser for the argument values;
Zod\bundler()->assign_parser_config(PK::EMAIL, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid email address',
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'domain' => 'gmail.com'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::REQUIRED, [
    FK::ACCEPT => [
        PK::EMAIL,
        PK::DATE,
        PK::BOOL,
        PK::INPUT,
        PK::NUMBER,
        PK::URL,
        PK::STRING,
    ],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'This field is required'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::DATE, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid date format',
        'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::BOOL, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid boolean value'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::INPUT, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid input value'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::STRING, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid string value'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::URL, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid URL format',
        'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::NUMBER, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid number format',
        'pattern' => '/^\d+$/'
    ],
    FK::PARSER_CALLBACK=> function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::OPTIONS, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid option value',
        'options' => []
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        return true;
    }
])->assign_parser_config(PK::EACH, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value'
    ],
    FK::PARSER_CALLBACK=> function (array $args): string|bool {
        return true;
    }
]);