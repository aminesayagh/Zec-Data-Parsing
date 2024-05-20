<?php 
require_once './index.php';

use function Zod\z as z;

echo 'Hello, World!';



$email_schema = z()->email();
$email = 'wrong email';

try {
    $email_schema->parse_or_throw($email);
    echo 'Email is valid' . PHP_EOL;
} catch (Exception $e) {
    echo $e . PHP_EOL;
}
