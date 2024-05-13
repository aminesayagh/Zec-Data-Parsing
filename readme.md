# PHP Data Parsing Library (Zod-Like)

The Zod Data Parsing and schema declaration Library is designed to bring the robust schema definition and validation capabilities similar to those in the Zod library (JavaScript) to PHP applications. This library offers flexible, modular, and extensible data validation tools that empower developers to enforce strict data integrity rules easily and reliably.

**Current Version:** v1.0.0

**Note:** This library is currently in development. Official documentation and further resources will be available soon.

**Author Information:**

- [LinkedIn](https://www.linkedin.com/in/mohamedamine-sayagh/)
- [GitHub](https://github.com/aminesayagh/)
- [Portfolio](https://www.masayagh.com/)

## Table of Contents

- [Introduction](#introduction)
- [Installation](#installation)
- [Collaboration](#collaboration)
- [How to Contribute](#how-to-contribute)
- [Usage](#usage)
  - [Basic Parsing](#basic-parsing)
  - [Safe Parsing](#safe-parsing)
  - [Validation](#validation)
  - [Exception Handling with Parsing](#exception-handling-with-parsing)
  - [Parsing of an array of options](#parsing-of-an-array-of-options)
  - [Advanced Parser Configuration](#advanced-parser-configuration)
  - [Complex Example: User Data Validation](#complex-example-user-data-validation)

- [License](#license)

## Introduction

The Zod Data Parsing and schema declaration Library is designed to bring the robust schema definition and validation capabilities similar to those in the Zod library (JavaScript) to PHP applications. This library offers flexible, modular, and extensible data validation tools that empower developers to enforce strict data integrity rules easily and reliably.

### Some other great aspects

- **Robust Schema Validation**: Provides strong typing and validation similar to the Zod library, ensuring accurate data handling.
- **Modular and Extensible**: Easy to extend with custom parsers, accommodating diverse data validation needs.
- **Separation of Concerns**: Keeps data parsing separate from business logic for better security and maintainability.
- **Ease of Testing**: Simplifies testing by enforcing strict data validation.
- **Flexible Configuration**: Supports dynamic parser configurations and customizations without altering the core library.
- **Community-Focused**: Open for contributions, enhancing collective improvement and support.

## Installation

**Note: The PHP Data Parsing Library is currently under active development and is not yet ready for production use.**

We appreciate your interest and encourage you to watch this space for updates. Once the library reaches a stable release, installation instructions will be provided here to help you get started with integrating it into your PHP projects.

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
    use function Zod\z;
    $my_schema = z()->string();
    
    $response_valid = $my_schema->parse("Hello, World!"); // Returns Zod data object
    $value = $response_valid->value; // Returns "Hello, World!"

    $response_invalid = $my_schema->parse(123); // Returns Zod data object
    $errors = $response_invalid->errors; // Returns an array of Zod error object
```

### Safe Parsing

Use safe_parse for error-safe parsing, returning structured success/error responses:

```php
    $response = $my_schema->safe_parse("Hello, World!"); // Returns Zod data object
    $value = $response->value; // Returns "Hello, World!"
    $errors = $response->errors; // Returns null
```

### Validation

Quickly validate data after parsing:

```php
    $is_valid = $my_schema->parse("Hello, World!")->is_valid(); // Returns true
    $is_invalid = $my_schema->parse(123)->is_valid(); // Returns false
```

### Exception Handling with Parsing

Handle exceptions using parse_or_throw for critical data validation:

```php
    try {
        $response = $my_schema->parse_or_throw(123); // Throws ZodError
    } catch (ZodError $error) {
        $error_message = $error->message; // Returns "Invalid type: expected string, received integer"
    }
```

### Parsing of an array of options

You can enhance the schema definition by incorporating additional validation options. This allows for more detailed control over data conformity based on specific requirements.

```php
    use function Zod\z;

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
    if ($valid_user->is_valid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($valid_user->get_errors());
    }

    if ($invalid_user->is_valid()) {
        echo 'User is valid.';
    } else {
        echo 'User is invalid. Errors: ';
        var_dump($invalid_user->get_errors());
    }
```

### Advanced Parser Configuration

The library provides extensive configurability for data validation, allowing users to define complex validation rules with custom messages and conditions. Below is an example of how to configure a detailed schema for an email field with specific validation rules:

```php
    use function Zod\z;

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
    use function Zod\z;

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
            }
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
        ])
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
    ]); // Returns a Zod object

    // Validate the parsed data
    if ($user->is_valid()) {
        echo 'User is valid.';
        var_dump($user->get_value()); // Outputs the validated data
    } else {
        echo 'User is invalid.';
        var_dump($user->get_errors()); // Outputs validation errors
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
    use function Zod\z;
    use function Zod\bundler;
    use Zod\FIELD as FK;
    
    // Define the custom size parser method
    bundler->assign_parser_config('size', [
        FK::IS_INIT_STATE => false, // we can't run parser with only size
        FK::DEFAULT_ARGUMENT => [
            'size' => 0,
            'message' => 'Invalid size'
        ]
        FK::PARSER_ARGUMENTS => function () {
            return z()->options([
                'size' => z()->required()->number(),
                'message' => z()->optional()->string(),
            ]);
        },
        FK::PARSER_FUNCTION => function (array $args): string|bool { // return string if error, bool if success
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
        }
    ], 
        $priority = 4, // priority of the parser, the default priority of parser is 10, parser with 5 as priority will run before parser with 10 as priority if they have the same parser name
    );
```

You can now use the custom `size` parser method in your schema definitions:

```php
    $my_schema = z()->options([
        'username' => z()->string()->size(5),
        'favorite_numbers' => z()->array()->size(5),
        'lucky_number' => z()->number()->size(5)
    ]);

    $user_data = [
        'username' => 'admin',
        'favorite_numbers' => [1, 2, 3, 4, 5],
        'lucky_number' => 5
    ]; // Valid data

    $parsed_data = $my_schema->parse($user_data); // Returns a Zod object

    // Validate the parsed data
    if ($parsed_data->is_valid()) { // Returns true if data is valid
        echo 'All data is valid.';
    } else {
        echo 'Data validation failed. Errors: ';
        var_dump($parsed_data->get_errors()); // Outputs validation errors
    }
```

## Overriding A Parser Method

You can override a parser method by redefining it with the same name, with inferior priority then the default one 10, This allows you to customize the behavior of existing parsers to suit your specific requirements.

Here is an example of overriding the `email` Parser method:

```php
    use function Zod\z;
    use function Zod\bundler;
    use Zod\FIELD as FK;

    // Override the email parser method
    bundler->assign_parser_config('email', [
        FK::IS_INIT_STATE => true, // we can run parser with email as a init state
        FK::DEFAULT_ARGUMENT => [
            'message' => 'Invalid email address',
            'domain' => ['custom_domain.com'],
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', // Custom pattern for email validation
        ],
        FK::PARSER_ARGUMENTS => function () {
            return z()->options([
                'message' => z()->string(),
                'domain' => z()->array(),
                'pattern' => z()->string()
            ]);
        },
        FK::PARSER_FUNCTION => function (array $args): string|bool {
            $value = $args['value'];
            $message = $args['message']; // Custom error exist already from the default parser, we can update it from the argument
            $domain = $args['domain'];

            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $message;
            }

            if ($domain && is_array($domain)) {
                $email_domain = explode('@', $value)[1];
                if (!in_array($email_domain, $domain)) {
                    return $message;
                }
            }

            return true;
        }
    ], 
        $priority = 5, // priority of the parser, the default priority of parser is 10, parser with 5 as priority will run before parser with 10 as priority if they have the same parser name
    );
```

## License

This project is licensed under the MIT License - see the LICENSE.md file for details.

### MIT License
Copyright (c) 2024 Mohamed Amine Sayagh
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