<?php 
require_once './index.php';

use function Zec\z as z;
use Zec\ZecError;

echo '------------------- Generate Parser : ' . PHP_EOL . PHP_EOL;
$user_parser = z()->options([
    'name' => z()->string()->min(3)->max(20),
    'email' => z()->string()->email(),
    'age' => z()->number()->min(18)->max(99)
]);

$user = [
    'name' => 'John Doe',
    'email' => 'john@gmail',
    'age' => 20
];

try {
    $user_parser->parse_or_throw($user);
    
    echo 'User is valid';
} catch (ZecError $e) {
    echo PHP_EOL . PHP_EOL;
    echo 'User is invalid: ' . PHP_EOL;
    $e->log();
}

$message = '';