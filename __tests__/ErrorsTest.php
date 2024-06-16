<?php
require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../index.php';

use function Zec\Utils\z;
use PHPUnit\Framework\TestCase;

class ErrorsTest extends TestCase
{
    use TestCaseHelper, Mock;
    public function testPrimaryErrors()
    {
        $parser = z()->string()->min([
            'min' => 3,
            'message' => 'Value must be at least 3 characters long'
        ])->max([
                    'max' => 10,
                    'message' => 'Value must be at most 10 characters long'
                ]);
        try {
            $parser->parseOrThrow('ab');
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Value must be at least 3 characters long', $e->getMessage(), 'Error message is not equal');
        }

        try {
            $parser->parseOrThrow('abcdefg hijk');
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {
            $this->assertEquals('Value must be at most 10 characters long', $e->getMessage(), 'Error message is not equal');
        }
    }
    // public function testOptionalErrors() {}
    public function testEachErrors()
    {
        $parser = z()->each(z()->string()->min([
            'min' => 3,
            'message' => 'Value must be at least 3 characters long'
        ]));
        $validErrorResponse = [
            [
                'parser' => 'min',
                'value' => 'ab',
                'min' => 3,
                'message' => 'Value must be at least 3 characters long',
                'path' => ['0']
            ]
        ];
        try {
            $parser->parseOrThrow(['ab', 'abc', 'abcd']);
            $this->fail('Expected exception not thrown');
        } catch (\Zec\ZecError $e) {
            $this->assertEquals($e->getMessage(), 'Value must be at least 3 characters long');
            $this->assertEquals($validErrorResponse, $e->info(), 'Error message is not equal');
        }
    }
    public function testOptionsSingleErrors()
    {
        $parser = z()->options([
            'name' => z()->string(),
            'age' => z()->number(),
            'email' => z()->email([
                'message' => 'Invalid email address'
            ]),
        ]);
        $validErrorResponse = [
            [
                'parser' => 'email',
                'value' => 'jane.doe@examplecom',
                'message' => 'Invalid email address',
                'path' => [
                    'email'
                ]
            ]
        ];
        try {
            $parser->parseOrThrow([
                'name' => 'Jane Doe',
                'age' => 1,
                'email' => 'jane.doe@examplecom'
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Zec\ZecError $e) {
            $this->assertEquals('Invalid email address', $e->getMessage(), 'Error message is not equal');
            $this->assertEquals($validErrorResponse, $e->info(), 'Error message is not equal');
        }
    }
    public function testOptionsErrors()
    {
        $parser = z()->options([
            'name' => z()->string(),
            'age' => z()->number()->min([
                'min' => 18,
                'message' => 'Value must be at least 18'
            ]),
            'email' => z()->email([
                'message' => 'Invalid email address'
            ]),
        ]);
        $validErrorResponse = [
            [
                'parser' => 'min',
                'min' => 18,
                'value' => 1,
                'message' => 'Value must be at least 18',
                'path' => [
                    'age'
                ]
            ],
            [
                'parser' => 'email',
                'value' => 'jane.doe@examplecom',
                'message' => 'Invalid email address',
                'path' => [
                    'email'
                ]
            ]
        ];
        try {
            $parser->parseOrThrow([
                'name' => 'Jane Doe',
                'age' => 1,
                'email' => 'jane.doe@examplecom'
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Zec\ZecError $e) {
            $this->assertEquals('Multiple errors occurred', $e->getMessage(), 'Error message is not equal');
            $this->assertEquals($validErrorResponse[1], $e->info()[1], 'Info message are not equal');
        }
    }
    public function testEachOnOptionErrors()
    {
        $parser = z()->options([
            'name' => z()->string(),
            'age' => z()->number()->min([
                'min' => 18,
                'message' => 'Value must be at least 18'
            ]),
            'email' => z()->email(),
            'hobbies' => z()->each(z()->string()->min([
                'min' => 3,
                'message' => 'Value must be at least 3 characters long'
            ]))
        ]);
        $validErrorResponse = [
            [
                'parser' => 'min',
                'min' => 18,
                'value' => 1,
                'message' => 'Value must be at least 18',
                'path' => [
                    'age'
                ]
            ],
            [
                'parser' => 'email',
                'value' => 'jane.doe@examplecom',
                'message' => 'Invalid email address',
                'path' => [
                    'email'
                ]
            ],
            [
                'parser' => 'string',
                'value' => 5,
                'message' => 'Invalid string value',
                'path' => [
                    'hobbies',
                    '3'
                ]
            ],
            [
                'parser' => 'string',
                'value' => 6,
                'message' => 'Invalid string value',
                'path' => [
                    'hobbies',
                    '4'
                ]
            ]
        ];
        try {
            $parser->parseOrThrow([
                'name' => 'Jane Doe',
                'age' => 1,
                'email' => 'jane.doe@examplecom',
                'hobbies' => ['photography', 'traveling', 'reading', 5, 6]
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Zec\ZecError $e) {
            $this->assertEquals('Multiple errors occurred', $e->getMessage(), 'Error message is not equal');
            $this->assertEquals($validErrorResponse, $e->info(), 'Info message are not equal');
        }
    }
    public function testOptionsNestedErrors()
    {
        $parser = z()->options([
            'address' => z()->options([
                'street' => z()->string(),
                'city' => z()->options([
                    'name' => z()->string(),
                    'zip' => z()->number()
                ])
            ])
        ]);
        $validErrorResponse = [
            [
                'parser' => 'string',
                'value' => 5,
                'message' => 'Invalid string value',
                'path' => [
                    'address',
                    'city',
                    'name'
                ]
            ],
            [
                'parser' => 'number',
                'value' => 'abc',
                'message' => 'Invalid number format',
                'path' => [
                    'address',
                    'city',
                    'zip'
                ]
            ]
        ];
        try {
            $parser->parseOrThrow([
                'address' => [
                    'street' => '123 Elm St',
                    'city' => [
                        'name' => 5,
                        'zip' => 'abc'
                    ]
                ]
            ]);
            $this->fail('Expected exception not thrown');
        } catch (\Zec\ZecError $e) {
            $this->assertEquals('Multiple errors occurred', $e->getMessage(), 'Error message is not equal');
            $this->assertEquals($validErrorResponse, $e->info(), 'Info message are not equal');
        }
    }
    public function testOptionsNestedEachNestedOptions()
    {
        $parser = z()->options([
            'area' => z()->string()->min([
                'min' => 30,
                'message' => 'Value must be at least 30 characters long'
            ]),
            'metadata' => z()->optional()->each(z()->options([
                'key' => z()->string(),
                'value' => z()->string()
            ]))
        ]);
        $validErrorResponse = [
            [
                'parser' => 'min',
                'min' => 30,
                'value' => 'Area 51',
                'message' => 'Value must be at least 30 characters long',
                'path' => [
                    'area'
                ]
            ],
            [
                'parser' => 'string',
                'value' => 5,
                'message' => 'Invalid string value',
                'path' => [
                    'metadata',
                    '0',
                    'key'
                ]
            ],
            [
                'parser' => 'string',
                'value' => 4,
                'message' => 'Invalid string value',
                'path' => [
                    'metadata',
                    '0',
                    'value'
                ]
            ]
        ];
        try {
            $parser->parseOrThrow([
                'area' => 'Area 51',
                'metadata' => [
                    ['key' => 5, 'value' => 4]
                ]
            ]);
        } catch (\Zec\ZecError $e) {
            $this->assertEquals('Multiple errors occurred', $e->getMessage(), 'Error message is not equal');
            $this->assertEquals($validErrorResponse, $e->info(), 'Info message are not equal');
        }
    }
    public function testError()
    {
        $parser = z()->string()->min([
            'min' => 3,
            'message' => 'Value must be at least 3 characters long'
        ]);
        $validErrorResponse = [
            [
                'parser' => 'min',
                'value' => 'ab',
                'min' => 3,
                'message' => 'Value must be at least 3 characters long',
            ]
        ];
        $response = $parser->parse('ab');
        if ($response->isValid()) {
            $this->fail('Expected exception');
        } else {
            $this->assertEquals($validErrorResponse, $response->error->info(), 'Error message is not equal');
        }
    }
}