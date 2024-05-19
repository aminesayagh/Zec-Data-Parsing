<?php

use function Zod\z;
use function Zod\bundler;
use Zod\FIELD as FK; // FK: Field Key
use Zod\PARSER as PK; // PK: Parser Key

require_once ZOD_PATH . '/src/config/Bundler.php';
require_once ZOD_PATH . '/src/Zod.php';


// TODO: Add parser arguments, a zod parser for the argument values;
// Don't forget to parse the argument on instantiation of the parser
// NOTE: in has_to_be, we don't need to specify required of optional, because it is already specified in the parser config
bundler()->assign_parser_config(PK::EMAIL, [
    FK::PRIORITIZE => [
        PK::STRING,
    ],
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
        'domain' => ['gmail.com', 'yahoo.com']
    ],
    FK::PARSER_CALLBACK => function (array $args): string|bool {
        $value = $args['value'];
        $argument = $args['argument'];

        $pattern = $argument['pattern'];
        $message = $argument['message'];
        $domain = $argument['domain'];
        
        if (!preg_match($pattern, $value)) {
            return $message;
        }

        return true;
    }
])->assign_parser_config(PK::REQUIRED, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'This field is required'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (is_null($value) || $value === '') {
            return $par['argument']['message'];
        }

        return true;
    }
])->assign_parser_config(PK::DATE, [
    FK::PRIORITIZE => [
        PK::STRING,
        PK::NUMBER
    ],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string(),
            'format' => z()->optional()->string(),
            'after_date' => z()->optional()->string(),
            'before_date' => z()->optional()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid date format',
        'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::BOOL, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid boolean value'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (!is_bool($value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::STRING, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid string value'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        if (!is_string($value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::URL, [
    FK::PRIORITIZE => [PK::STRING],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid URL format',
        'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::NUMBER, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid number format',
        'pattern' => '/^\d+$/'
    ],
    FK::PARSER_CALLBACK=> function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::OPTIONS, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return Zod\z()->options([
            'message' => z()->required()->string(),
            'options' => z()->required()->array()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid option values',
        'options' => []
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $options = $par['argument']['options'];
        $default_value = $par['default'];

        if (!in_array($value, $options)) {
            return $par['argument']['message'];
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
            return $par['argument']['message'];
        }

        return true;
    }
])->assign_parser_config(PK::EACH, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z()->options([
            'message' => z()->required()->string(),
            'each' => z()->required(),
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
    ],
    FK::PARSER_CALLBACK=> function (array $par): string|bool {
        $values = $par['value'];
        $message = $par['argument']['message'];
        $each = $par['each'];
        $has_error = false;

        foreach ($values as $value) {
            $zod_response = $each->parse($value, $par['default'], $par['owner']);
            if (!$zod_response->is_valid()) {
                $has_error = true;
            }
        }

        if($has_error) {
            return $message;
        }

        return true;
    }
])->assign_parser_config(PK::MIN, [
    FK::PRIORITIZE => [
    ],
    FK::PARSER_ARGUMENTS => function () {
        return z()->min([
            'message' => z()->required()->string(),
            'min' => z()->required()->number()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
        'min' => 0
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $min = $par['argument']['min'];
        $message = $par['argument']['message'];

        $min_value = 0;
        if(is_string($value)) {
            $min_value = strlen($value);
        } else if (is_int($value)) {
            $min_value = $value;
        } else if (is_array($value)) {
            $min_value = count($value);
        }
        if ($min_value < $min) {
            return $message;
        }
        return true;
    }
])->assign_parser_config(PK::MAX, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z()->max([
            'message' => z()->required()->string(),
            'max' => z()->required()->number()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
        'max' => 0
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $max = $par['argument']['max'];
        $message = $par['argument']['message'];
        
        $min_value = 0;
        if(is_string($value)) {
            $min_value = strlen($value);
        } else if (is_int($value)) {
            $min_value = $value;
        } else if (is_array($value)) {
            $min_value = count($value);
        }

        if ($min_value > $max) {
            return $message;
        }
        return true;
    }
])->assign_parser_config(PK::OPTIONAL, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function () {
        return z();
    },
    FK::DEFAULT_ARGUMENT => [],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        // if the value is null, then it is valid
        if (is_null($par['value'])) {
            return false; // end the parsing process
        }
        return true;
    }
]);

// over