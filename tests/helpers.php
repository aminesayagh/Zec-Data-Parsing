<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../index.php';


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

function t($name) {
    return new UnitTestCase($name);
}
trait TestCaseHelper {
    private $listParserToTest = [];
    private function addTestCase(UnitTestCase $testCase) {
        $this->listParserToTest[$testCase->name] = $testCase;
    }
}