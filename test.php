<?php 
require_once './index.php';

use function Zod\z as z;


echo '------------------- Generate Parser : ' . PHP_EOL . PHP_EOL;
$user_parser = z()->options([
    'name' => z()->string()->min(3)->max(20),
    'email' => z()->string()->email(),
    'age' => z()->number()->min(18)->max(99),
]);

$user = [
    'name' => 'John Doe',
    'email' => 'john@gmail.com',
    'age' => 25,
];

echo '------------------- State Parsing : ' . PHP_EOL . PHP_EOL;
try {
    $user_parser->parse_or_throw($user);
    echo 'User is valid';
} catch (\Zod\ZodErrors $e) {
    echo 'User is invalid: ' . $e->get_message();
}