<?php 
require_once __DIR__ . '/index.php';

use function Zec\Utils\z;


$parser = z()->options([
    'name' => z()->string(),
    'age' => z()->number()->min([
        'min' => 18,
        'mege' => 'Value must be at least 18'
    ]),
    'email' => z()->email(),
    'hobbies' => z()->each(z()->string()->min([
        'min' => 3,
        'message' => 'Value must be at least 3 characters long'
    ]))
]);
$validErrorResponse = [
    [
        'parser' => 'min',
        'min' => 18,
        'value' => 1,
        'message' => 'Value must be at least 18',
        'path' => [
            'age'
        ]
    ],
    [
        'parser' => 'email',
        'value' => 'jane.doe@examplecom',
        'message' => 'Invalid email address',
        'path' => [
            'email'
        ]
    ],
    [
        'parser' => 'string',
        'value' => 5,
        'message' => 'Invalid string value',
        'path' => [
            'hobbies',
            '3'
        ]
    ],
    [
        'parser' => 'string',
        'value' => 6,
        'message' => 'Invalid string value',
        'path' => [
            'hobbies',
            '4'
        ]
    ]
];
try {
    $response = $parser->parseOrThrow([
        'name' => 'Jane Doe',
        'age' => 1,
        'email' => 'jane.doe@examplecom',
        'hobbies' => ['photography', 'traveling', 'reading', 5, 6]
    ]);
} catch (\Zec\ZecError $e) {
    $e->log();
}
