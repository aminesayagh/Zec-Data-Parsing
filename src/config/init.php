<?php
declare(strict_types=1);

use Zec\CONST\PARSERS_KEY as PK; // PK: Parser Key
use function Zec\Parsers\parser_build;
use function Zec\Bundler\bundler;



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
    ->unLog()
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