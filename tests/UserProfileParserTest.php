<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../index.php';
use PHPUnit\Framework\TestCase;
use function Zec\Utils\z;

class UserProfileParserTest extends TestCase
{
    private $userProfileParser;

    protected function setUp(): void
    {
        $this->userProfileParser = z()->options([
            'name' => z()->string()->min(3)->max(50),
            'age' => z()->number()->min(18),
            'email' => z()->email(),
            'address' => z()->options([
                'street' => z()->string(),
                'city' => z()->string()
            ]),
            'hobbies' => z()->each(z()->string()),
            'metadata' => z()->optional()->each(z()->options([
                'key' => z()->string(),
                'value' => z()->string()
            ]))
        ]);
    }

    public function testValidUserData()
    {
        $userData = [
            'name' => 'Jane Doe',
            'age' => 25,
            'email' => 'jane.doe@example.com',
            'address' => [
                'street' => '123 Elm St',
                'city' => 'Somewhere'
            ],
            'hobbies' => ['photography', 'traveling', 'reading'],
            'metadata' => [
                ['key' => 'memberSince', 'value' => '2019'],
                ['key' => 'newsletter', 'value' => 'true']
            ]
        ];


        $this->assertTrue($this->userProfileParser->parse($userData)->isValid());
    }
}
