# Zec: PHP Data Parsing Library

The Zec Data Parsing and schema declaration Library is designed to bring schema definition and validation capabilities to PHP applications.

## Installation

### Composer

```bash
composer require mohamed-amine-sayagh/zec
```

### Requirements

- PHP 8.0 or higher

## Usage Example

Here's an example of how to use the Zec library to define and validate a user profile schema:

### Define a schema for user data validation

```php
    use function Zec\Utils\z;

    // Define the user profile schema
    $userProfileParser = z()->options([
        'name' => z()->string()->min(3)->max(50),  // Name must be a string between 3 and 50 characters
        'age' => z()->number()->min(18),           // Age must be a number and at least 18
        'email' => z()->email(),                   // Email must be a valid email address
        'address' => z()->options([               
            'street' => z()->string(),             
            'city' => z()->string()                
        ]),
        'hobbies' => z()->each(z()->string()),     // Hobbies must be an array of strings
        'metadata' => z()->optional()->each(z()->options([  // Metadata is optional and must be an array of objects
            'key' => z()->string(),                // Each object must have a key as a string
            'value' => z()->string()               // Each object must have a value as a string
        ]))
    ]);
```

### Parse user data and validate it

```php
    // Parse and validate user data
    $userData = [
        'name' => 'Jane Doe',
        'age' => 1, // 1 is not a valid age
        'email' => 'jane.doe@examplecom', // missing dot
        'address' => [
            'street' => '123 Elm St',
            'city' => 3 // city is not a string
        ],
        'hobbies' => ['photography', 'traveling', 'reading', 5], // 5 is not a string     
        'metadata' => [
            ['key' => 'memberSince', 'value' => '2019'],
            ['key' => 'newsletter', 'value' => 'true']
        ]
    ];

    try {
        $userProfileParser->parseOrThrow($userData); // Throws an error
        echo "User data is valid\n";
    } catch (\Zec\ZecError $e) {
        echo "User data is invalid: ";
        $e->log(); // Log the error
    }
```

### Expected Error Response

```plaintext
    User data is invalid: [
        {
            "parser": "min",
            "value": 1,
            "min": 18,
            "message": "Invalid value",
            "path": [
                "age"
            ]
        },
        {
            "parser": "email",
            "value": "jane.doe@examplecom",
            "message": "Invalid email address",
            "path": [
                "email"
            ]
        },
        {
            "parser": "string",
            "value": 5,
            "message": "Invalid string value",
            "path": [
                "hobbies",
                "3"
            ]
        }
    ]
```

**Current Version:** v1.0.0, **Release Date:** 2024-10-01, **Author:** Mohamed Amine SAYAGH, **Release Notes:** Initial release of the Zec PHP Data Parsing Library., **Release Link:** [Zec 1.0.0](

**Note:** This library is currently in development. Official documentation and further resources will be available soon.

**Author Information:**

- [LinkedIn](https://www.linkedin.com/in/mohamedamine-sayagh/)
- [GitHub](https://github.com/aminesayagh/)
- [Portfolio](https://www.masayagh.com/)

## Table of Contents

- [Installation](#installation)
- [Collaboration](#collaboration)
- [How to Contribute](#how-to-contribute)
- [Usage](#usage)
  - [Basic Parsing](#basic-parsing)
  - [Validation](#validation)
  - [Exception Handling with Parsing](#exception-handling-with-parsing)
  - [Parsing of an array of options](#parsing-of-an-array-of-options)
  - [Advanced Parser Configuration](#advanced-parser-configuration)
  - [Complex Example: User Data Validation](#complex-example-user-data-validation)
- [Creating Custom Parser Methods](#creating-custom-parser-methods)
- [License](#license)

### Some other great aspects

- **Robust Schema Validation**: Provides strong typing and validation similar to the Zec library, ensuring accurate data handling.
- **Modular and Extensible**: Easy to extend with custom parsers, accommodating diverse data validation needs.
- **Separation of Concerns**: Keeps data parsing separate from business logic for better security and maintainability.
- **Ease of Testing**: Simplifies testing by enforcing strict data validation.
- **Flexible Configuration**: Supports dynamic parser configurations and customizations without altering the core library.
- **Community-Focused**: Open for contributions, enhancing collective improvement and support.

## Collaboration

**We are fully open to collaboration!** If you're interested in contributing to the PHP Data Parsing Library, we'd love to hear from you. Whether it's through code contributions, documentation improvements, or feature suggestions, your input is highly welcome.

## How to Contribute

1. **Fork the Repository**: Start by forking the repository to your own GitHub account.
2. **Clone Your Fork**: Clone your fork to your local machine and set up the development environment.
3. **Create a New Branch**: Make your changes in a new git branch.
4. **Commit Your Changes**: Commit your modifications with clear and descriptive commit messages.
5. **Push Your Changes**: Push your changes to your fork on GitHub.
6. **Submit a Pull Request**: Open a pull request to the main branch of our repository. Please provide a clear description of the changes and any other information that will help us understand your contributions.

For more details, check our contribution guidelines (link to detailed contribution guidelines if available).

We look forward to building a powerful data parsing tool with a vibrant community of contributors!

## Usage

### Basic Parsing

Define and validate data types using simple schema definitions:

```php
    use function Zec\Utils\z;
    $my_schema = z()->string();
    
    $response_valid = $my_schema->parse("Hello, World!"); // Returns Zec data object
    $value = $response_valid->value; // Returns "Hello, World!"

    $response_invalid = $my_schema->parse(123); // Returns Zec data object
    $errors = $response_invalid->errors; // Returns an array of ZecError object an exception extends class
```

### Validation

Quickly validate data after parsing:

```php
    $is_valid = $my_schema->parse("Hello, World!")->isValid(); // Returns true
    $is_invalid = $my_schema->parse(123)->isValid(); // Returns false
```

### Exception Handling with Parsing

Handle exceptions using parse_or_throw for critical data validation:

```php
    try {
        $response = $my_schema->parseOrThrow(123); // Throws ZecError
    } catch (ZecError $error) {
        $error_message = $error->message; // Returns "Invalid type: expected string, received integer"
        $error->log(); // Logs the error
    }
```

### Parsing of an array of options

You can enhance the schema definition by incorporating additional validation options. This allows for more detailed control over data conformity based on specific requirements.

```php
    use function Zec\z;

    $user_schema = z()->options([
        'name' => z()->string()->min(3)->max(50),
        'email' => z()->email(),
        'age' => z()->number()->min(18),
        'isActive' => z()->boolean(),
        'registrationDate' => z()->date()
    ]);

    // Parsing a valid user object
    $valid_user = $user_schema->parse([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'age' => 30,
        'isActive' => true,
        'registrationDate' => '2021-01-01'
    ]);

    // Parsing an invalid user object
    $invalid_user = $user_schema->parse([
        'name' => 'JD', // Too short
        'email' => 'john.doe@', // Invalid email format
        'age' => 17, // Below minimum age requirement
        'isActive' => 'yes', // Incorrect type (should be boolean)
        'registrationDate' => '01-01-2021' // Wrong date format
    ]);

    // Handling validation results
    if ($valid_user->isValid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($valid_user->errors());
    }

    if ($invalid_user->isValid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($invalid_user->errors());
    }
```

### Advanced Parser Configuration

The library provides extensive Configurability for data validation, allowing users to define complex validation rules with custom messages and conditions. Below is an example of how to configure a detailed schema for an email field with specific validation rules:

```php
    use function Zec\z;

    // Define a configurable email schema
    $my_configurable_email_schema = z()->string([
        'message' => 'Invalid string data'
    ])->min([
        'min' => 3,
        'message' => 'String data must be at least 3 characters long' // Custom message
    ])->max([
        'max' => 10,
        'message' => 'String {{value}} must be at most ((max)) characters long' // Custom message with value interpolation
    ]).email([
        'message' => 'Invalid email',
        'domain' => ['gmail.com', 'yahoo.com'] // Custom domain validation rules
    ]);

    // Define a user schema using the configurable email schema
    $my_user = z()->options([
        'email' => $my_configurable_email_schema->required()
    ]);
```

### Complex Example: User Data Validation

This example demonstrates validating a user data structure that includes nested arrays, optional fields, and union types, showcasing the library's capability to handle complex and realistic data models.

```php
    use function Zec\z;

    // Define a user schema with various data validation rules
    $user_parser = z()->options([
        'name' => z()->required()->string()->min(3)->max(50),
        'email' => z()->url([
            'message' => 'Invalid email address', 
            'domain' => ['gmail.com']
        ]),
        'age' => z()->number(),
        'friends' => z()->each(
            function ($user) {
                return $user->nullable();
            } // Each friend is a user object (nullable)
        ),
        'password' => z()->optional()->options([
            'password' => z()->string(),  // Path: 'password.password'
            'confirm_password' => z()->string(),
            'created_at' => z()->date(),
        ]),
        'created_at' => z()->date(),
        'updated_at' => z()->date(),
        'document' => z()->union([
            z()->options([
                'type' => z()->enum(['student']),
                'content' => z()->options([
                    'school' => z()->string(),
                    'grade' => z()->number(),
                ]),
            ]),
            z()->options([
                'type' => z()->enum(['teacher']),
                'content' => z()->options([
                    'school' => z()->string(),
                    'subject' => z()->string(),
                ]),
            ]),
        ]) // Union type for document, can be student or teacher document
    ]);

    // Parse a valid user object
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
        ],
        'created_at' => '2021-10-10',
        'updated_at' => '2021-10-10',
        'document' => [
            'type' => 'student',
            'content' => [
                'school' => 'School',
                'grade' => 10,
            ]
        ]
    ]); // Returns a Zec object

    // Validate the parsed data
    if ($user->isValid()) {
        echo 'User is valid.';
        var_dump($user->getValue()); // Outputs the validated data
    } else {
        echo 'User is invalid.';
        var_dump($user->errors()); // Outputs validation errors
    }
```

## Creating Custom Parser Methods

To enhance flexibility and cater to specific validation needs, our library allows users to define custom parser methods. This section demonstrates how to create a `size` parser method, which validates that the size of an array, length of a string, or value of a number meets a specific requirement.

The `size` method checks if:

- An array's length equals a specified size.
- A string's length equals a specified number of characters.
- A numeric value is exactly equal to a specified number.

Here is how you can implement this custom parser:

```php
    use function Zec\z;
    use function Zec\bundler;
    use Zec\FIELD as FK;

    $custom_size_parser = parser_build()
        ->name('size')
        ->prioritize('string', 'number', 'array') // Prioritize the parser for string, number, and array types
        ->argument('message', 'Invalid size', function (Zec\Zec $z) {
            return $z->required()->string();
        }) // argument for custom message, default is 'Invalid size'
        ->argument('size', 0, function (Zec\Zec $z) {
            return $z->required()->number();
        }) // argument for size, default is 0
        ->function(function (array $args): string|bool {
            $value = $args['value'];
            $expected_size = $args['size'];
            $message = $args['message'];

            if (is_array($value)) {
                $actual_size = count($value);
            } elseif (is_string($value)) {
                $actual_size = strlen($value);
            } elseif (is_numeric($value)) {
                $actual_size = $value;
            } else {
                return $message ?? 'Invalid data type';
            }

            if ($actual_size === $expected_size) {
                return true;
            }
            return $message ?? 'Invalid size';
        })->priority(4); // priority of the parser, the default priority of parser is 10, parser with 5 as priority will override before parser with 10 as priority if they have the same parser name
        ->build(); // Build the parser

    bundler->addParser($custom_size_parser);
```

You can now use the custom `size` parser method in your schema definitions:

```php
    $my_schema = z()->options([
        'username' => z()->string()->size(5), // Username must be a string of length 5
        'favorite_numbers' => z()->array()->size(5), // Favorite numbers must be an array of length 5
        'lucky_number' => z()->number()->size(5), // Lucky number must be 5
    ]);

    $user_data = [
        'username' => 'admin',
        'favorite_numbers' => [1, 2, 3, 4, 5],
        'lucky_number' => 5
    ]; // Valid data

    $parsed_data = $my_schema->parse($user_data); // Returns a Zec object

    // Validate the parsed data
    if ($parsed_data->isValid()) { // Returns true if data is valid
        echo 'All data is valid.';
    } else {
        echo 'Data validation failed. Errors: ';
        var_dump($parsed_data->errors()); // Outputs validation errors
    }
```

## License

This project is licensed under the MIT License - see the LICENSE.md file for details.

### MIT License

Copyright (c) 2024 Mohamed Amine SAYAGH

All rights reserved.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
