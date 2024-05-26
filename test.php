<?php 
require_once './index.php';

use function Zec\z;

$userProfileParser = z()->options([
    'name' => z()->string()->min(3)->max(50),
    'age' => z()->number()->min(18),
    'email' => z()->email(),
    'address' => z()->options([
        'street' => z()->string(),
        'city' => z()->string()
    ]),
    'hobbies' => z()->each(z()->string()),
    // 'metadata' => z()->optional()->each(z()->options([
    //     'key' => z()->string(),
    //     'value' => z()->string()
    // ]))  // Flexible field for additional data
]);

$userData = [
    'name' => 'Jane Doe',
    'age' => 1,
    'email' => 'jane.doe@example.com',
    'address' => [
        'street' => '123 Elm St',
        'city' => 'Somewhere'
    ],
    'hobbies' => ['photography', 'traveling', 'reading', 5],
    // 'metadata' => [
    //     ['key' => 'memberSince', 'value' => '2019'],
    //     ['key' => 'newsletter', 'value' => 'true']
    // ]
];

try {
    $userProfileParser->parse_or_throw($userData);
    echo "User data is valid\n";
} catch (\Zec\ZecError $e) {
    echo "User data is invalid: ";
    $e->log();
}