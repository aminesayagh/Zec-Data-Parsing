<?php

use function Zod\z;
use Zod\FIELD as FK; // FK: Field Key
use Zod\PARSER as PK; // PK: Parser Key

require_once ZOD_PATH . '/src/config/Bundler.php';
require_once ZOD_PATH . '/src/Zod.php';


// TODO:
// Add parser arguments, a zod parser for the argument values;
Zod\bundler()->assign_parser_config(PK::EMAIL, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string(),
            'domain' => z()->optional()->array()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid email address',
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'domain' => ['gmail.com', 'yahoo.com', 'hotmail.com']
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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (is_null($value) || $value === '') {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }

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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'] ?? $par['default_argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }
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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (!is_bool($value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }
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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (!is_string($value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message']; // TODO: Merge between default argument and argument before running the callback
        }
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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (!is_string($value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }
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
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'] ?? $par['default_argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }
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
    FK::PARSER_CALLBACK=> function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'] ?? $par['default_argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::OPTIONS, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return Zod\z()->options([
            'message' => Zod\z()->required()->string(),
            'options' => Zod\z()->required()->array()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid option values',
        'options' => []
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $options = $par['argument']['options'] ?? $par['default_argument']['options'];
        $default_value = $par['default'];

        if (!in_array($value, $options)) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }

        $has_error = false;

        foreach ($options as $key => $option) {
            if (!($option instanceof Zod\Zod)) {
                throw new Zod\ZodError('The options field must be an array of Zod instances', 'options');
            }
            $default_of_option = null;
            if (is_array($default_value) && array_key_exists($key, $default_value)) {
                $default_of_option = $default_value[$key];
            }
            
            $value_field = array_key_exists($key, $value) ? $value[$key] : null;
            $zod_response = $option->parse($value_field, $default_of_option, $par['owner']);
            if (!$zod_response->is_valid()) {
                $has_error = true;
            }
        }

        if ($has_error) {
            return $par['argument']['message'] ?? $par['default_argument']['message'];
        }

        return true;
    }
])->assign_parser_config(PK::EACH, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return Zod\z()->each([
            'message' => Zod\z()->required()->string(),
            'each' => Zod\z()->required(),
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
        'each' => null
    ],
    FK::PARSER_CALLBACK=> function (array $par): string|bool {
        $value = $par['value'];
        $message = $par['argument']['message'] ?? $par['default_argument']['message'];
        $valid = true;
        
        // TODO: Validation of each value


        return true;
    }
])->assign_parser_config(PK::MIN, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return Zod\z()->min([
            'message' => Zod\z()->required()->string(),
            'min' => Zod\z()->required()->number()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
        'min' => 0
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $min = $par['argument']['min'] ?? $par['default_argument']['min'];
        $message = $par['argument']['message'] ?? $par['default_argument']['message'];
        if ($value < $min) {
            return $message;
        }
        return true;
    }
])->assign_parser_config(PK::MAX, [
    FK::ACCEPT => [],
    FK::IS_INIT_STATE => true,
    FK::PARSER_ARGUMENTS => function () {
        return Zod\z()->max([
            'message' => Zod\z()->required()->string(),
            'max' => Zod\z()->required()->number()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
        'max' => 0
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $max = $par['argument']['max'] ?? $par['default_argument']['max'];
        $message = $par['argument']['message'] ?? $par['default_argument']['message'];
        if ($value > $max) {
            return $message;
        }
        return true;
    }
]);

// over