<?php
include_once 'parsers/Parser.php';
include_once 'parsers/Parsers.php';
include_once 'Exception.php';


if (!class_exists('Zod')) {
    /**
     * Parses a field value, setting errors if any.
     *
     * @param mixed $value The value to be parsed.
     * @param mixed $default The default value to set if the parsing fails.
     * @return Zod Returns the Zod object for method chaining.
     * @throws Exception If the parser is not in a valid state.
     */
    $parserOfField = function (mixed $value, mixed $default = null): Zod {
        if (!$this instanceof Zod) {
            throw new Exception("Invalid parser state");
        }

        $this->value = $value;
        $this->setDefault($default);
        $this->exceptions->reset();

        foreach ($this->parsers() as $p) {
            $response = $p->parse($this->value);

            if (is_string($response)) {
                $error = new ZodError($response);
                $this->exceptions->error = $error;
            }
        }
        return $this;
    };

    /**
     * Parses an array of values based on the defined option parsers.
     *
     * @param array $values The associative array of values to be parsed.
     * @param mixed $default The default value to set if the parsing fails.
     * @return Zod Returns the Zod object for method chaining.
     * @throws Exception If the parser is not in a valid state.
     */
    $parserOfOption = function (array $values, mixed $default = null): Zod {
        if (!$this instanceof Zod) {
            throw new Exception("Invalid parser state");
        }

        $this->setDefault($default);

        $optionsParser = $this->parsers->getParser('options');
        $optionsArgument = $optionsParser->getArguments();

        foreach ($optionsArgument as $key => $z) {
            $value = $values[$key] ?? null;
            $response = $z->parse($value);

            if (is_string($response)) {
                $error = new ZodError($response, $key);
                $this->errors->error = $error;
                $z->setDefault($z->default, true);
            }
        }

        return $this;
    };

    $parserOfEach = function (mixed $value, mixed $default = null): Zod {
        if (!($this instanceof Zod)) {
            throw new Exception("Invalid parser state");
        }

        return $this;
    };

    interface IZod {
        public function __construct();
        public function __call($name, $arguments);
        public function __toString();
        public function __get($name);
        public function __set($name, $value);
        public function __invoke();
        public function __clone();
        public function parse(mixed $value, mixed $default = null): Zod;
        public function parseOrThrow(mixed $value, mixed $default = null): Zod;
        public function setDefault(mixed $default, bool $defaultIsValid = false): Zod;
    }

    class Zod implements IZod 
    {
        private Parsers $parsers;
        private ZodErrors $errors;
        private string $type = 'field'; // field | options | each
        private bool $hasValidParserState = false;
        private bool $parserSorted = false;
        private bool $hasValidDefault = false;
        // add a public variable to store a callback function to validate the arguments
        private mixed $parserCallback = null;

        private mixed $default = null;
        private mixed $value = null;

        public function __construct()
        {
            $this->parsers = new Parsers();
            $this->errors = new ZodErrors();
        }
        private function updateType(string $newType)
        {
            $ruleOfConvision = [
                'field' => ['options', 'each'],
                'options' => [],
                'each' => []
            ]; // $curentType => $newType
            if (in_array($newType, $ruleOfConvision[$this->type])) {
                $this->type = $newType;
                if ($newType === 'field') {
                    global $parserOfField;
                    $this->parserCallback = $parserOfField;
                } else if ($newType === 'options') {
                    global $parserOfOption;
                    $this->parserCallback = $parserOfOption;
                } else if ($newType === 'each') {
                    global $parserOfEach;
                    $this->parserCallback = $parserOfEach;
                }
            }
            return $this;
        }
        /**
         * Magic method to handle dynamic calls and parser setting.
         *
         * @param string $name Method name or parser type.
         * @param array $arguments Arguments for the method or parser.
         * @return mixed The result of the method call or $this for chaining.
         * @throws Exception If the method or parser is not found.
         */
        public function __call($name, $arguments)
        {
            // check if the $name is a method of the class Zod
            if (method_exists($this, $name)) {
                // call the method
                return call_user_func_array(array($this, $name), $arguments); // $this->$name($arguments);
            } else if ($name === 'parser') {
                if ($this->parser === null) {
                    throw new Exception("Parser not found");
                }
                return call_user_func_array($this->parser, $arguments);
            } else if ($name === 'options') {
                $this->updateType('options');
            } else if ($name === 'each') {
                $this->updateType('each');
            } else {
                $this->updateType('field');
            }

            if (bundler()->isParser($name)) {
                $parser = new Parser($name);
                $parser->setArguments($arguments);

                // add the parser to the parsers array with priority
                $this->parsers->addParser($parser);
                return $this;
            }
            throw new Exception("Method not found");
        }
        /**
         * Returns the object as a string representation.
         *
         * @return string The string representation of the object.
         * @throws Exception If the value is not a string.
         */
        function __toString()
        {
            // if value is a string then return the value
            if (is_string($this->value)) {
                return $this->value;
            }
            throw new Exception("Value is not a string");
        }
        /**
         * Magic method for getting properties dynamically.
         *
         * @param string $name The name of the property.
         * @return mixed The property value.
         * @throws Exception If the property is not found or accessible.
         */
        function __get($name)
        {
            switch ($name) {
                case 'errors':
                    return $this->errors->errors;
                case 'value':
                    return $this->value;
                default:
                    if (property_exists($this, $name) && (new ReflectionProperty($this, $name))->isPublic()) {
                        return $this->$name;
                    }
                    break;
            }
            throw new Exception("Property not found");
        }
        /**
         * Magic method for setting properties dynamically, restricted to 'errors'.
         *
         * @param string $name The name of the property.
         * @param mixed $value The value to set.
         * @throws Exception If the property is not found or accessible.
         */
        function __set($name, $value)
        {
            if ($name === 'error') {
                $this->errors->error = $value;
            }
            throw new Exception("Property not found");
        } // call the object as a string, ex: $Zod->errors = $errors
        /**
         * Invokes the Zod object as a function, returning itself.
         *
         * @return Zod The instance of this Zod class.
         */
        public function __invoke()
        {
            return $this;
        } // call the class as a function, ex: Zod()
        /**
         * Clones the Zod object, including its parsers and errors.
         */
        public function __clone()
        {
            $parserCopy = new Parsers();
            foreach ($this->parsers as $p) {
                // if parser key is not required
                if ($p->getKey() === 'required') {
                    $parserCopy->addParser($p);
                }
            }
            $this->errors = clone $this->errors;
            $this->parsers = $parserCopy;
        }
        // PUBLIC FUNCTIONS
        public function parse(mixed $value, mixed $default = null): Zod
        {
            if (is_null($this->parserCallback)) {
                throw new Exception("Parser not found");
            }
            $this->errors->reset();

            $this->parserCallback($value, $default);
            return $this;
        }
        /**
         * Parses the given value using the set parser, throwing an exception if errors are found.
         *
         * @param mixed $value The value to parse.
         * @param mixed $default The default value to use if parsing fails.
         * @return Zod The instance of this Zod class for chaining.
         * @throws Exception If parsing fails or no parser is set.
         */
        public function parseOrThrow(mixed $value, mixed $default = null): Zod
        {
            if (is_null($this->parser)) {
                throw new Exception("Parser not found");
            }
            $this->parser($value, $default);
            if ($this->errors->length > 0) {
                $errors = $this->getErrorsList();
                throw new Exception("Error parsing the value " . $errors);
            }
            return $this;
        }
        /**
         * Parses the given value using the set parser, returning the result or default if errors are found.
         *
         * @param mixed $value The value to parse.
         * @param mixed $default The default value to use if parsing fails.
         * @return mixed The parsed value or default if parsing fails.
         */
        public function setDefault(mixed $default, bool $defaultIsValid = false): Zod
        {
            if (is_null($default)) {
                return $this;
            }
            if ($this->hasValidParserState && !$defaultIsValid) {
                $parserDefault = clone $this;
                $parserDefault->parseOrThrow($default);
            }
            $this->default = $default;
            $this->hasValidDefault = true;
            return $this;
        }
    }
}

if (!function_exists('z')) {
    function z(): Zod
    {
        return new Zod();
    }
}

if (!function_exists('is_zod')) {
    function is_zod($value): bool
    {
        return $value instanceof Zod;
    }
}
