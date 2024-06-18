<?php
require_once __DIR__ . '/_helpers.php';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../index.php';

use function Zec\Utils\z;
use PHPUnit\Framework\TestCase;

class PrimitivesTest extends TestCase {
    use TestCaseHelper, Mock;
    public function setUp() : void {

        parent::setUp();
        $this->addTestCase(
            t('string')
                ->validValue('element')
                ->invalidValue(3)
                ->setParser(z()->string())
        );
        $this->addTestCase(
            t('number')
                ->validValue(3)
                ->invalidValue('element')
                ->setParser(z()->number())
        );
        $this->addTestCase(
            t('bool')
                ->validValue(false)
                ->setParser(z()->bool())
        );
        $this->addTestCase(
            t('date')
                ->validValue('2020-01-01')
                ->invalidValue('element')
                ->setParser(z()->date())
        );
        $this->addTestCase(
            t('email')
                ->validValue('test@gmail.com')
                ->invalidValue('element')
                ->setParser(z()->email())
        );
        $this->addTestCase(
            t('url')
                ->validValue('https://www.google.com')
                ->invalidValue('element')
                ->setParser(z()->url())
        );
    }
    

    public function testPrimitiveValues() {
        echo "Running testPrimitiveValues\n";
        // Test if listParserToTest is not empty
        $this->assertNotEmpty($this->listParserToTest, 'No test cases found in listParserToTest.');

        foreach ($this->listParserToTest as $testCase) {
            if (isset($testCase->validValue)) {
                // Handle both single and array of valid values
                $validValues = is_array($testCase->validValue) ? $testCase->validValue : [$testCase->validValue];
                foreach ($validValues as $value) {
                    $this->assertTrue($testCase->parser->parse($value)->isValid(), "Failed asserting that valid value '{$value}' for '{$testCase->name}' is valid.");
                }
            }
            
            if (isset($testCase->invalidValue)) {
                // Handle both single and array of invalid values
                $invalidValues = is_array($testCase->invalidValue) ? $testCase->invalidValue : [$testCase->invalidValue];
                foreach ($invalidValues as $value) {
                    $this->assertFalse($testCase->parser->parse($value)->isValid(), "Failed asserting that invalid value '{$value}' for '{$testCase->name}' is invalid.");
                }
            }
        }
    }
}