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
} else {
    echo 'User is invalid';
    var_dump($user->get_errors());
}