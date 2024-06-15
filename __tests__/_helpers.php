<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../index.php';

function get_random_int($min, $max) {
    return random_int($min, $max);
}

trait Mock {
    public static function string($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public static function bool() {
        return (bool)random_int(0, 1);
    }
    public static function bigint() {
        // 64-bit systems have a 63-bit PHP_INT_MAX due to the signed bit.
        return random_int(99999, PHP_INT_MAX);
    }
    public static function symbol() {
        return self::string(1); // 1 character
    }

    public static function number($min = 0, $max = 100) {
        return random_int($min, $max);
    }
    public static function email() {
        return self::string(10) . '@' . self::string(5) . '.com';
    }
    public static function url() {
        return 'https://' . self::string(10) . '.com';
    }
    public static function date() {
        return date('Y-m-d', strtotime('+' . self::number(1, 100) . ' days'));
    }
    public static function stringOptional() {
        return self::bool() ? self::string() : null;
    }  
    public static function stringNullable() {
        return self::bool() ? self::string() : null;
    }
    public static function numberOptional() {
        return self::bool() ? self::number() : null;
    }
    public static function numberNullable() {
        return self::bool() ? self::number() : null;
    }
    public static function boolOptional() {
        return self::bool() ? self::bool() : null;
    }
    public static function boolNullable() {
        return self::bool() ? self::bool() : null;
    }
    public static function emailOptional() {
        return self::bool() ? self::email() : null;
    }
    public static function emailNullable() {
        return self::bool() ? self::email() : null;
    }
}


class UnitTestCase {
    public $name;
    public $validValue;
    public $invalidValue;
    public $parser;
    public function __construct($name) 
    {
        $this->name = $name;
    }
    public function validValue($value) {
        $this->validValue = $value;
        return $this;
    }
    public function invalidValue($value) {
        $this->invalidValue = $value;
        return $this;
    }
    public function setParser($parser) {
        $this->parser = $parser;
        return $this;
    }
}

trait TestCaseHelper {
    private $listParserToTest = [];
    private function addTestCase(UnitTestCase $testCase) {
        $this->listParserToTest[$testCase->name] = $testCase;
    }
}

function t($name) {
    return new UnitTestCase($name);
}