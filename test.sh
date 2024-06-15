#!/bin/bash

# Navigate to the root directory of your project
cd "$(dirname "$0")"

# Run PHPUnit tests in the __tests__ folder
vendor/bin/phpunit --configuration phpunit.xml