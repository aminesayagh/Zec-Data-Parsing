<?php
declare(strict_types=1);

use function Zec\z;
use Zec\FIELD as FK; // FK: Field Key
use Zec\PARSERS_KEY as PK; // PK: Parser Key

require_once ZEC_PATH . '/src/CaretakerParsers.php';
require_once ZEC_PATH . '/src/config/Bundler.php';
require_once ZEC_PATH . '/src/Zec.php';

use function Zec\bundler as bundler;
use function Zec\is_zec;

bundler()->assign_parser_config(PK::EMAIL, [
    FK::PRIORITIZE => [
        PK::STRING,
    ],
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string(),
            'domain' => z()->optional()->each(z()->string())
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            'pattern' => z()->required()->string(),
            'format' => z()->optional()->string(),
            'after_date' => z()->optional()->string(),
            'before_date' => z()->optional()->string()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid date format',
        'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/',
        'format' => 'Y-m-d',
        'after_date' => null,
        'before_date' => null
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string()
        ]);
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
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
        if (!preg_match($pattern, (string) $value)) {
            return $par['argument']['message'];
        }
        return true;
    }
])->assign_parser_config(PK::OPTIONS, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            'options' => z()->required()->associative([
                'key' => z()->string(),
                'value' => z()->instanceof(Zec\Zec::class)
            ])
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid option values'
    ],

    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $options = $par['argument'][PK::OPTIONS];
        $default_value = $par['default'];

        $has_error = false;

        Zec\Zec::associative_parse($options, function ($key, $option, $value, $index, $default) use ($par, &$has_error) {
            $zec_response = $option->parse($value, Zec\Zec::proxy_set_arg(
                $default,
                $par['owner']
            ));

            if (!$zec_response->is_valid()) {
                $has_error = true;
            }
            
        }, $par);

        if ($has_error) {
            return $par['argument']['message'];
        }

        return true;
    }
])->assign_parser_config(PK::EACH, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid each value',
    ],
    FK::PARSER_CALLBACK=> function (array $par): string|bool {
        $values = $par['value'];
        $message = $par['argument']['message'];
        $each = $par['argument'][PK::EACH];

        echo 'Each: ' . json_encode($values) . PHP_EOL;
        $has_error = false;

        foreach ($values as $value) {
            $zec_response = $each->parse($value, $par['default'], $par['owner']);
            if (!$zec_response->is_valid()) {
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            'min' => z()->required()->number()
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid value',
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
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
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return null;
    },
    FK::DEFAULT_ARGUMENT => [],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        if (is_null($par['value'])) {
            return false;
        }
        return true;
    }
])->assign_parser_config(PK::INSTANCEOF, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            PK::INSTANCEOF => z()->required(),
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid instance'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $instanceof = $par['argument'][PK::INSTANCEOF];
        $message = $par['argument']['message'];

        if (!($value instanceof $instanceof)) {
            return $message;
        }
        return true;
    }
])->assign_parser_config(PK::ASSOCIATIVE, [
    FK::PRIORITIZE => [],
    FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
        return $z->options([
            'message' => z()->required()->string(),
            PK::ASSOCIATIVE => z()->required()->associative([
                'key' => z()->required()->instanceof(Zec\Zec::class),
                'value' => z()->required()->instanceof(Zec\Zec::class)
            ])
        ]);
    },
    FK::DEFAULT_ARGUMENT => [
        'message' => 'Invalid associative array'
    ],
    FK::PARSER_CALLBACK => function (array $par): string|bool {
        $value = $par['value'];
        $message = $par['argument']['message'];
        $key_parser = $par['argument'][PK::ASSOCIATIVE]['key'];
        $value_parser = $par['argument'][PK::ASSOCIATIVE]['value'];

        $has_error = false;
        foreach ($value as $k => $v) {
            $key_response = $key_parser->parse($k, null, $par['owner']);
            $value_response = $value_parser->parse($v, null, $par['owner']);
            if (!$key_response->is_valid() || !$value_response->is_valid()) {
                $has_error = true;
            }
        }

        if ($has_error) {
            return $message;
        }

        return true;
    }
]);

