<?php 
require_once './index.php';

use function Zod\z as z;

echo '------------------- Generate Parser : ' . PHP_EOL . PHP_EOL;
$user_parser = z()->options([
    'name' => z()->string()->min(3)->max(20),
    'email' => z()->string()->email(),
    'age' => z()->number()->min(18)->max(99)
]);

$user = [
    'name' => 'John Doe',
    'email' => 'john',
    'age' => 25,
];

echo '------------------- Start Parsing : ' . PHP_EOL . PHP_EOL;
try {
    $user_parser->parse_or_throw($user);
    
    echo 'User is valid';
} catch (\Zod\ZodError $e) {
    echo PHP_EOL . PHP_EOL;
    echo 'User is invalid: ' . PHP_EOL . $e->get_message();
}