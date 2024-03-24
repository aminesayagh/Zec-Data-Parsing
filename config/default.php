<?php

// TODO: We didn't consider the priority of the rules in the accept array, exemple min has to be an integer or string or array, before using it as a rule,

// if (!function_exists('z')) {
//     throw new Exception("Function z() not found");
// }

$default = [
    'email' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'pattern' => z()->string(),
                'domain' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid email format',
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'domain' => 'gmail.com'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid email format";
            $valid = filter_var($value, FILTER_VALIDATE_EMAIL) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        }
    ],
    'date' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid date format'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['messageFileExist'] ?? "Invalid file exist";
            if (!file_exists($value)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'bool' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid boolean format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid boolean format";
            // is 'true' or 'false' string
            $valid = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'input' => [
        'accept' => ['required', 'bool', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid input format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid input format";
            $sanitized_input = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');

            if ($sanitized_input !== $value) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'integer' => [
        'accept' => ['required', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid number format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid number format";
            $number = $value;

            $valid = filter_var($number, FILTER_VALIDATE_INT) && (int) $number;
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'string' => [
        'accept' => ['required', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid string format'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid string format";

            if (!is_string($value)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'url' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid url format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid url format";
            $valid = filter_var($value, FILTER_VALIDATE_URL) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'html' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid html format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid html format";
            $valid = isset ($value) && is_string($value) && !empty ($value) && htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'regex' => [
        'accept' => ['required', 'integer', 'url', 'input', 'date', 'email', 'string'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'pattern' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid format',
            'pattern' => '/^.*$/'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $pattern = $options['pattern'] ?? '';
            $errorMessage = $options['message'] ?? "Invalid format";

            if (!preg_match($pattern, $value)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'enum' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'values' => z()->each(z()->string())
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid enum format',
            'values' => []
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid enum format";
            $values = $options['values'] ?? [];
            if (!in_array($value, $values)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'min' => [
        'accept' => ['required', 'max', 'length', 'tuple', 'union'],
        'isInitState' => false,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'value' => z()->integer()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid min format',
            'value' => 0
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid min format";
            $min = $options['value'] ?? 0;
            if (is_string($value) && strlen($value) < $min) {
                return $errorMessage;
            }
            if (is_integer($value) && $value < $min) {
                return $errorMessage;
            }
            if (is_array($value) && count($value) < $min) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'max' => [
        'accept' => ['required', 'min', 'length', 'tuple', 'union'],
        'isInitState' => false,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'value' => z()->integer()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid max format',
            'value' => 0
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid max format";
            $max = $options['value'] ?? 0;
            if (is_string($value) && strlen($value) > $max) {
                return $errorMessage;
            }
            if (is_integer($value) && $value > $max) {
                return $errorMessage;
            }
            if (is_array($value) && count($value) > $max) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'length' => [
        'accept' => ['required', 'union'],
        'isInitState' => false,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string(),
                'value' => z()->integer()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid length format',
            'value' => 0
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid length format";
            $length = $options['value'] ?? 0;
            if (is_string($value) && strlen($value) !== $length) {
                return $errorMessage;
            }
            if (is_integer($value) && $value !== $length) {
                return $errorMessage;
            }
            if (is_array($value) && count($value) !== $length) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'array' => [
        'accept' => ['required', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid array format'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid array format";
            if (!is_array($value)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'tuple' => [
        'accept' => ['required', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid tuple format'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid tuple format";
            $length = $options['length'] ?? 0;
            if (count($value) !== $length) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'union' => [
        'accept' => ['required', 'min', 'max', 'length'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid union format'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid union format";
            $types = $options['types'] ?? [];
            $valid = false;
            foreach ($types as $type) {
                if (gettype($value) === $type) {
                    $valid = true;
                    break;
                }
            }
            if (!$valid) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'required' => [
        'accept' => ['email', 'date', 'bool', 'input', 'integer', 'string', 'url', 'html', 'regex', 'enum', 'min', 'max', 'length', 'array', , 'tuple', 'union', 'options'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Field is required'
        ],
        'parser' => function (mixed $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Field is required";
            if (empty ($value)) {
                return $errorMessage;
            }
            return true;
        },
    ],
    'options' => [
        'accept' => [],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid options format'
        ],
    ],
    'each' => [
        'accept' => ['array', 'min', 'max', 'length', 'required'],
        'isInitState' => false,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid each format'
        ],
    ],
    'file' => [
        'accept' => ['required'],
        'isInitState' => true,
        'argumentsParser' => function (): Zod {
            return z()->options([
                'message' => z()->string()
            ]);
        },
        'defaultArgument' => [
            'message' => 'Invalid file format'
        ],
        'parser' => function (string $value, array $options = []): string|bool {
            $errorMessage = $options['message'] ?? "Invalid file format";
            if (!file_exists($value)) {
                return $errorMessage;
            }
            return true;
        },
    ]
];
