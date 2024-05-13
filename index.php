<?php 

use function Zod\z; // Add this import statement

if (!defined('ZOD_PATH')) {
    define('ZOD_PATH', __DIR__);
}

require_once ZOD_PATH . '/src/const/KEY.php';

require_once ZOD_PATH . '/src/CaretakerParsers.php';

require_once ZOD_PATH . '/src/parsers/Parser.php';

require_once ZOD_PATH . '/src/config/Bundler.php';

require_once ZOD_PATH . '/src/parsers/parsers.php';

require_once ZOD_PATH . '/src/Zod.php';

require_once ZOD_PATH . '/src/config/init.php';


use function Zod\z;

$my_schema = z()->string();

// parsing 
$response_valid = $my_schema->parse('hello'); // This will return a Zod object
$value = $response_valid->value; // This will return the value of the parsed data (hello)

$response_invalid = $my_schema->parse(123); // This will return a Zod object
$errors = $response_invalid->errors; // This will return the errors of the parsed data, a ZodErrors object

// safe parsing
$response = $my_schema->safe_parse('hello'); // { success: true, data: 'hello' }
$response = $my_schema->safe_parse(123); // { success: false, error: 'Invalid data' }

// validation
$valid = $my_schema->is_valid('hello'); // true
$invalid = $my_schema->is_valid(123); // false

// parsing try catch
try {
    $response = $my_schema->parse_or_throw('hello'); // This will return a the value of the parsed data (hello)
} catch (Exception $e) {
    echo $e->getMessage();
}

try {
    $response = $my_schema->parse_or_throw(123); // This will throw an exception
} catch (Exception $e) {
    echo $e->getMessage();
}


$user_parser = z()->options([
    'name' => z()->required()->string()->min(3)->max(50),
    'email' => z()->url(['message' => 'Invalid email address', 'domain' => ['gmail.com']]),
    'age' => z()->number(),
    
    'friends' => z()->each(
        function ($user) {
            return $user->nullable();
        }
    ),
    'password' => z()->optional()->options([
        'password' => z()->string(), // path: 'password.password'
        'confirm_password' => z()->string(),
        'created_at' => z()->date(),
    ])
]);

$user = $user_parser->parse([
    'name' => 'John Doe',
    'email' => 'amine@gmail.com',
    'age' => 25,
    'friends' => [
        [
            'name' => 'Jane Doe',
            'email' => 'john@gmail.com',
            'age' => 30,
        ],
    ],
    'password' => [
        'password' => 'password',
        'confirm_password' => 'password',
        'created_at' => '2021-10-10'
    ]
]); // This will return a Zod object

if ($user->is_valid()) {
    echo 'User is valid';
    var_dump($user->get_value());
} else {
    echo 'User is invalid';
    var_dump($user->get_errors());
}