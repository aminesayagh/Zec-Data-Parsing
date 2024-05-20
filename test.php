<?php 
require_once './index.php';

use function Zod\z as z;


$email_schema = z()->email();
$email = 'emial@gmail.com';


$email_parsed = $email_schema->parse($email);

echo 'Email is valid' . PHP_EOL;
