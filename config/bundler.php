<?php

require_once __DIR__ . '/default.php';

if(!class_exists('Bundler')) {
    class Bundler
    {
        private static ?Bundler $instance = null;
        private array $config;
        private array $default;
        private function __construct()
        {
            global $default;
            $this->default = $default;
            $this->validateConfig();
            $this->assignPriority();
        }
        private function validateConfig()
        {
            foreach ($this->default as $key => $value) {
                is_config($value, $key);
            }
        }
        private function assignPriority()
        {
            global $parserRules;
            foreach ($this->default as $key => $value) {
                $parserRules[$key] = $value;
            }
            foreach ($this->default as $key => $value) {
                calcPriority($key, $this->default);
            }
        }
        public function getConfig(string $key): array
        {
            if (!array_key_exists($key, $this->default)) {
                throw new Exception("Key '$key' not found in the config file");
            }
            return $this->default[$key];
        }
        public function isParser(string $key): bool
        {
            return array_key_exists($key, $this->default);
        }
        static function run()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

// check if the value is a valid config

function is_config(array $value, string $key = '') {
    // chech if all the keys are present in the config file
    if (!array_key_exists('accept', $value)) {
        throw new Exception("Key 'accept' is missing in the config file for '$key'");
    }
    if (!array_key_exists('isInitState', $value)) {
        throw new Exception("Key 'isInitState' is missing in the config file for '$key'");
    }
    if (!array_key_exists('argumentsParser', $value)) {
        throw new Exception("Key 'argumentsParser' is missing in the config file for '$key'");
    }
    if (!array_key_exists('defaultArgument', $value)) {
        throw new Exception("Key 'defaultArgument' is missing in the config file for '$key'");
    }

    // check if the values are of the correct type
    if (!is_array($value['accept'])) {
        throw new Exception("Value of 'accept' key must be an array for '$key'");
    }
    if (!is_bool($value['isInitState'])) {
        throw new Exception("Value of 'isInitState' key must be a boolean for '$key'");
    }
    if (!is_callable($value['argumentsParser'])) {
        throw new Exception("Value of 'argumentsParser' key must be an instance of Zod for '$key'");
    }
    if (!is_array($value['defaultArgument'])) {
        throw new Exception("Value of 'defaultArgument' key must be an array for '$key'" . print_r($value));
    }

    return true;
}

function calcPriority(string $key, array &$parserRules, array $parents = []): int|null
{
    if (!isset ($parserRules[$key]['accept'])) {
        return 0;
    }
    
    $priority = 0;
    foreach ($parserRules[$key]['accept'] as $a) {
        // Check if the parser has a priority
        if (isset ($parserRules[$a]['priority'])) {
            $priority = max($priority, $parserRules[$a]['priority']);
        } else {
            if (in_array($a, $parents)) {
                continue; // avoid infinite loop in case of circular dependency
            }
            $parents[] = $key;
            $priorityChild = calcPriority($a, $parserRules, $parents);
            $priority = max($priority, $priorityChild);
        }
    }
    $parserRules[$key]['priority'] = $priority + 1;
    return $parserRules[$key]['priority'];
}

function bundler(): Bundler {
    return Bundler::run();
}

return Bundler::run();
