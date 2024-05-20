<?php 
require_once './index.php';

use function Zod\z as z;


$email_schema = z()->email();
$email = 'emialil.com';

try{
    $email_parsed = $email_schema->parse_or_throw($email);
    echo 'Email is valid ' . $email_parsed . PHP_EOL;
} catch (\Zod\ZodError $e) {
    echo 'Email is invalid' . PHP_EOL;
    echo $e->get_message() . PHP_EOL;
    exit;
}