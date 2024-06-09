<?php
declare(strict_types=1);

use Zec\CONST\FIELD as FK; // FK: Field Key
use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use function Zec\Utils\z as z;
use function Zec\Bundler\bundler as bundler;

if (!class_exists('ParserBuild')) {
    class ParserBuild {
        private string|null $name = null;
        private int $priority = 10;
        private bool $to_log = true;
        private bool $is_init_state = true;
        private array $prioritize = [];
        private array $parser_arguments = [];
        private mixed $default_argument = [];
        private mixed $parser_callback = null;
        public function __construct() {
            $this->argument('message', 'Invalid value', function (Zec\Zec $z) {
                return $z->required()->string();
            });
        }
        public function name(string $name): ParserBuild {
            if (!is_string($name)) {
                throw new \Exception('Name must be a string');
            }
            $this->name = $name;
            return $this;
        }
        public function prioritize(...$prioritize): ParserBuild {
            if (empty($prioritize)) {
                throw new \Exception('Prioritize must not be empty');
            }
            if (!is_array($prioritize)) {
                throw new \Exception('Prioritize must be an array');
            }
            foreach ($prioritize as $p) {
                if (!is_string($p)) {
                    throw new \Exception('Prioritize must be a string');
                }
                $this->prioritize[] = $p;
            }
            $this->is_init_state = false;
            return $this;
        }
        public function unLog(): ParserBuild { 
            $this->to_log = false;
            return $this;
        }
        public function priority(int $priority): ParserBuild {
            if ($priority < 0) {
                throw new \Exception('Priority must be greater than or equal to 0');
            }
            if ($priority > 100) {
                throw new \Exception('Priority must be less than or equal to 100');
            }
            $this->priority = $priority;
            return $this;
        }
        public function argument(string $name, mixed $default, mixed $parserArgument): ParserBuild {
            if (!is_string($name)) {
                throw new \Exception('Argument name must be a string');
            }
            if (!is_array($default) && !is_string($default) && !is_int($default) && !is_bool($default) && !is_null($default)) {
                throw new \Exception('Default argument must be an array, string, integer, boolean, or null');
            }
            if (!is_callable($parserArgument)) {
                throw new \Exception('Parser argument must be a callable');
            }
            $this->default_argument[$name] = $default;
            $this->parser_arguments[$name] = $parserArgument;
            return $this;
        }
        public function parserCallback(callable $callback): ParserBuild {
            if (!is_callable($callback)) {
                throw new \Exception('Parser callback must be a callable');
            }
            $this->parser_callback = $callback;
            return $this;
        }
        public function build(): array {
            if (is_null($this->name)) {
                throw new \Exception('Name must not be null');
            }
            if (empty($this->parser_arguments)) {
                throw new \Exception('Parser arguments must not be empty');
            }
            if (is_null($this->parser_callback)) {
                throw new \Exception('Parser callback must not be null');
            }
            if (!is_array($this->prioritize)) {
                throw new \Exception('Prioritize must not be empty');
            }
            if (empty($this->default_argument)) {
                throw new \Exception('Default argument must not be empty');
            }
            $build_argument = [];
            $build_argument[FK::NAME] = $this->name;
            $build_argument[FK::PRIORITY] = $this->priority;
            $build_argument[FK::PRIORITIZE] = $this->prioritize;
            $build_argument[FK::PARSER_ARGUMENTS] = function (Zec\Zec $z) use ($build_argument) {
                $args = [];
                foreach ($this->parser_arguments as $name => $parserArgument) {
                    $args[$name] = $parserArgument($z);
                }
                return $z->options($args);
            };
            $build_argument[FK::DEFAULT_ARGUMENT] = $this->default_argument;
            $build_argument[FK::PARSER_CALLBACK] = $this->parser_callback;
            $build_argument[FK::IS_INIT_STATE] = $this->is_init_state;
            $build_argument['signed'] = $this::class;
            $build_argument[FK::LOG_ERROR] = $this->to_log;
            return $build_argument;
        }
    }
}

if (!function_exists('parser_build')) {
    function parser_build(): ParserBuild {
        return new ParserBuild();
    }
}

$emailConfig = parser_build()
    ->name(PK::EMAIL)
    ->prioritize(PK::STRING)
    ->argument('message', 'Invalid email address', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('pattern', '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('domain', ['gmail.com', 'yahoo.com'], function (Zec\Zec $z) {
        return $z->optional()->each($z->string());
    })
    ->parserCallback(function (array $args): string|bool {
        $value = $args['value'];
        $argument = $args['argument'];

        $pattern = $argument['pattern'];
        $message = $argument['message'];
        $domain = $argument['domain'];

        
        if (!preg_match($pattern, $value)) {
            return $message;
        }

        return true;
    })
    ->build();

$requiredConfig = parser_build()
    ->name(PK::REQUIRED)
    ->argument('message', 'This field is required', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        if (is_null($value) || $value === '') {
            return $par['argument']['message'];
        }

        return true;
    })
    ->build();

$dateConfig = parser_build()
    ->name(PK::DATE)
    ->prioritize(PK::STRING, PK::NUMBER)
    ->argument('message', 'Invalid date format', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('pattern', '/^(\d{4})-(\d{2})-(\d{2})$/', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('format', 'Y-m-d', function (Zec\Zec $z) {
        return $z->optional()->string();
    })
    ->argument('after_date', null, function (Zec\Zec $z) {
        return $z->optional()->string();
    })
    ->argument('before_date', null, function (Zec\Zec $z) {
        return $z->optional()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'];
        }
        return true;
    })
    ->build();

$boolConfig = parser_build()
    ->name(PK::BOOL)
    ->argument('message', 'Invalid boolean value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        if (!is_bool($value)) {
            return $par['argument']['message'];
        }
        return true;
    })
    ->build();

$stringConfig = parser_build()
    ->name(PK::STRING)
    ->argument('message', 'Invalid string value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        if (!is_string($value)) {
            return $par['argument']['message'];
        }
        return true;
    })
    ->build();

$urlConfig = parser_build()
    ->name(PK::URL)
    ->prioritize(PK::STRING)
    ->argument('message', 'Invalid URL format', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('pattern', '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, $value)) {
            return $par['argument']['message'];
        }
        return true;
    })
    ->build();

$numberConfig = parser_build()
    ->name(PK::NUMBER)
    ->argument('message', 'Invalid number format', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('pattern', '/^\d+$/', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        $pattern = $par['argument']['pattern'];
        if (!preg_match($pattern, (string) $value)) {
            return $par['argument']['message'];
        }
        return true;
    })
    ->build();

$optionsConfig = parser_build()
    ->name(PK::OPTIONS)
    ->argument('message', 'Invalid option values', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('options', [], function (Zec\Zec $z) {
        return $z->required()->associative([
            'key' => $z->string(),
            'value' => $z->instanceof(Zec\Zec::class)
        ]);
    })
    ->parserCallback(function (array $par): string|bool {
        $options = $par['argument'][PK::OPTIONS];

        $has_error = false;

        Zec\Zec::associativeParse($options, function ($key, $option, $value, $index, $default) use ($par, &$has_error) {
            $zec_response = $option->parse($value);

            if (!$zec_response->isValid()) {
                $has_error = true;
            }
            
        }, $par);

        if ($has_error) {
            return $par['argument']['message'];
        }

        return true;
    })
    ->build();

$eachConfig = parser_build()
    ->name(PK::EACH)
    ->argument('message', 'Invalid each value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument(PK::EACH, null, function (Zec\Zec $z) {
        return $z->required()->instanceof(Zec\Zec::class);
    })
    ->parserCallback(function (array $par): string|bool {
        $values = $par['value'];
        $message = $par['argument']['message'];
        $each = $par['argument'][PK::EACH];
        $default = $par['default'] ?? null;
        $has_error = false;


        if (!($each instanceof Zec\Zec)) {
            throw new \Exception('Each parser must be an instance of Zec');
        }
        $each = $each->default($default);

        Zec\Zec::mapParse($values, function (int $index, Zec\Zec $each, mixed $value) use ($par, &$has_error) {
            $zec_response = $each->parse($value);
            if (!$zec_response->isValid()) {
                $has_error = true;
            }
        }, $par);

        if($has_error) {
            return $message;
        }

        return true;
    })
    ->unLog()
    ->build();

$minConfig = parser_build()
    ->name(PK::MIN)
    ->argument('message', 'Invalid value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('min', 0, function (Zec\Zec $z) {
        return $z->required()->number();
    })
    ->parserCallback(function (array $par): string|bool {
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
    })
    ->build();

$maxConfig = parser_build()
    ->name(PK::MAX)
    ->argument('message', 'Invalid value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('max', 0, function (Zec\Zec $z) {
        return $z->required()->number();
    })
    ->parserCallback(function (array $par): string|bool {
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
    })
    ->build();

$optionalConfig = parser_build()
    ->name(PK::OPTIONAL)
    ->parserCallback(function (array $par): string|bool {
        if (is_null($par['value'])) {
            return false;
        }
        return true;
    })
    ->unLog()
    ->build();

$instanceofConfig = parser_build()
    ->name(PK::INSTANCEOF)
    ->argument('message', 'Invalid instance of', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('instanceof', null, function (Zec\Zec $z) {
        return $z->required()->instanceof(Zec\Zec::class);
    })
    ->parserCallback(function (array $par): string|bool {
        $value = $par['value'];
        $instanceof = $par['argument']['instanceof'];
        $message = $par['argument']['message'];

        if (!($value instanceof $instanceof)) {
            return $message;
        }
        return true;
    })
    ->build();

$associativeConfig = parser_build()
    ->name(PK::ASSOCIATIVE)
    ->argument('message', 'Invalid associative value', function (Zec\Zec $z) {
        return $z->required()->string();
    })
    ->argument('associative', [], function (Zec\Zec $z) {
        return $z->required()->associative([
            'key' => $z->string(),
            'value' => $z->instanceof(Zec\Zec::class)
        ]);
    })
    ->parserCallback(function (array $par): string|bool {
        $associative = $par['argument']['associative'];

        $has_error = false;

        Zec\Zec::associativeParse($associative, function ($key, $option, $value, $index, $default) use ($par, &$has_error) {
            $zec_response = $option->parse($value);

            if (!$zec_response->isValid()) {
                $has_error = true;
            }
            
        }, $par);

        if ($has_error) {
            return $par['argument']['message'];
        }

        return true;
    })
    ->build();

bundler()->assignParserConfig($emailConfig)
    ->assignParserConfig($requiredConfig)
    ->assignParserConfig($dateConfig)
    ->assignParserConfig($boolConfig)
    ->assignParserConfig($stringConfig)
    ->assignParserConfig($urlConfig)
    ->assignParserConfig($numberConfig)
    ->assignParserConfig($optionsConfig)
    ->assignParserConfig($eachConfig)
    ->assignParserConfig($minConfig)
    ->assignParserConfig($maxConfig)
    ->assignParserConfig($optionalConfig)
    ->assignParserConfig($instanceofConfig)
    ->assignParserConfig($associativeConfig);

// bundler()->assignParserConfig(PK::EMAIL, [
//     FK::PRIORITIZE => [
//         PK::STRING,
//     ],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'pattern' => z()->required()->string(),
//             'domain' => z()->optional()->each(z()->string())
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid email address',
//         'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
//         'domain' => ['gmail.com', 'yahoo.com']
//     ],
//     FK::PARSER_CALLBACK => function (array $args): string|bool {
//         $value = $args['value'];
//         $argument = $args['argument'];

//         $pattern = $argument['pattern'];
//         $message = $argument['message'];
//         $domain = $argument['domain'];

        
//         if (!preg_match($pattern, $value)) {
//             return $message;
//         }

//         return true;
//     }
// ])->assignParserConfig(PK::REQUIRED, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'This field is required'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         if (is_null($value) || $value === '') {
//             return $par['argument']['message'];
//         }

//         return true;
//     }
// ])->assignParserConfig(PK::DATE, [
//     FK::PRIORITIZE => [
//         PK::STRING,
//         PK::NUMBER
//     ],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'pattern' => z()->required()->string(),
//             'format' => z()->optional()->string(),
//             'after_date' => z()->optional()->string(),
//             'before_date' => z()->optional()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid date format',
//         'pattern' => '/^(\d{4})-(\d{2})-(\d{2})$/',
//         'format' => 'Y-m-d',
//         'after_date' => null,
//         'before_date' => null
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $pattern = $par['argument']['pattern'];
//         if (!preg_match($pattern, $value)) {
//             return $par['argument']['message'];
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::BOOL, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid boolean value'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         if (!is_bool($value)) {
//             return $par['argument']['message'];
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::STRING, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid string value'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         if (!is_string($value)) {
//             return $par['argument']['message'];
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::URL, [
//     FK::PRIORITIZE => [PK::STRING],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'pattern' => z()->required()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid URL format',
//         'pattern' => '/^(http|https):\/\/[a-zA-Z0-9-\.]+\.[a-zA-Z]{2,}([a-zA-Z0-9\/\.\-\_\?\=\&\%\#]*)$/'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $pattern = $par['argument']['pattern'];
//         if (!preg_match($pattern, $value)) {
//             return $par['argument']['message'];
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::NUMBER, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'pattern' => z()->required()->string()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid number format',
//         'pattern' => '/^\d+$/'
//     ],
//     FK::PARSER_CALLBACK=> function (array $par): string|bool {
//         $value = $par['value'];
//         $pattern = $par['argument']['pattern'];
//         if (!preg_match($pattern, (string) $value)) {
//             return $par['argument']['message'];
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::OPTIONS, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'options' => z()->required()->associative([
//                 'key' => z()->string(),
//                 'value' => z()->instanceof(Zec\Zec::class)
//             ])
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid option values'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $options = $par['argument'][PK::OPTIONS];

//         $has_error = false;

//         Zec\Zec::associativeParse($options, function ($key, $option, $value, $index, $default) use ($par, &$has_error) {
//             $zec_response = $option->parse($value);

//             if (!$zec_response->isValid()) {
//                 $has_error = true;
//             }
            
//         }, $par);

//         if ($has_error) {
//             return $par['argument']['message'];
//         }

//         return true;
//     },
//     FK::LOG_ERROR => false,
// ])->assignParserConfig(PK::EACH, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             PK::EACH => z()->required()->instanceof(Zec\Zec::class)
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid each value',
//     ],
//     FK::PARSER_CALLBACK=> function (array $par): string|bool {
//         $values = $par['value'];
//         $message = $par['argument']['message'];
//         $each = $par['argument'][PK::EACH];
//         $default = $par['default'] ?? null;
//         $has_error = false;


//         if (!($each instanceof Zec\Zec)) {
//             throw new \Exception('Each parser must be an instance of Zec');
//         }
//         $each = $each->default($default);

//         Zec\Zec::mapParse($values, function (int $index, Zec\Zec $each, mixed $value) use ($par, &$has_error) {
//             $zec_response = $each->parse($value);
//             if (!$zec_response->isValid()) {
//                 $has_error = true;
//             }
//         }, $par);

//         if($has_error) {
//             return $message;
//         }

//         return true;
//     }
// ])->assignParserConfig(PK::MIN, [
//     FK::PRIORITIZE => [
//     ],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'min' => z()->required()->number()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid value',
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $min = $par['argument']['min'];
//         $message = $par['argument']['message'];

//         $min_value = 0;
//         if(is_string($value)) {
//             $min_value = strlen($value);
//         } else if (is_int($value)) {
//             $min_value = $value;
//         } else if (is_array($value)) {
//             $min_value = count($value);
//         }
//         if ($min_value < $min) {
//             return $message;
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::MAX, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             'max' => z()->required()->number()
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid value',
//         'max' => 0
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $max = $par['argument']['max'];
//         $message = $par['argument']['message'];
        
//         $min_value = 0;
//         if(is_string($value)) {
//             $min_value = strlen($value);
//         } else if (is_int($value)) {
//             $min_value = $value;
//         } else if (is_array($value)) {
//             $min_value = count($value);
//         }

//         if ($min_value > $max) {
//             return $message;
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::OPTIONAL, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return null;
//     },
//     FK::DEFAULT_ARGUMENT => [],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         if (is_null($par['value'])) {
//             return false;
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::INSTANCEOF, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             PK::INSTANCEOF => z()->required(),
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid instance'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $instanceof = $par['argument'][PK::INSTANCEOF];
//         $message = $par['argument']['message'];

//         if (!($value instanceof $instanceof)) {
//             return $message;
//         }
//         return true;
//     }
// ])->assignParserConfig(PK::ASSOCIATIVE, [
//     FK::PRIORITIZE => [],
//     FK::PARSER_ARGUMENTS => function (Zec\Zec $z) {
//         return $z->options([
//             'message' => z()->required()->string(),
//             PK::ASSOCIATIVE => z()->required()->associative([
//                 'key' => z()->required()->instanceof(Zec\Zec::class),
//                 'value' => z()->required()->instanceof(Zec\Zec::class)
//             ])
//         ]);
//     },
//     FK::DEFAULT_ARGUMENT => [
//         'message' => 'Invalid associative array'
//     ],
//     FK::PARSER_CALLBACK => function (array $par): string|bool {
//         $value = $par['value'];
//         $message = $par['argument']['message'];
//         $key_parser = $par['argument'][PK::ASSOCIATIVE]['key'];
//         $value_parser = $par['argument'][PK::ASSOCIATIVE]['value'];

//         $has_error = false;
//         foreach ($value as $k => $v) {
//             $key_response = $key_parser->parse($k, null, $par['owner']);
//             $value_response = $value_parser->parse($v, null, $par['owner']);
//             if (!$key_response->isValid() || !$value_response->isValid()) {
//                 $has_error = true;
//             }
//         }

//         if ($has_error) {
//             return $message;
//         }

//         return true;
//     }
// ]);