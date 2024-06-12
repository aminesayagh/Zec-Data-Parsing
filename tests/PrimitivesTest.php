<?php
require_once __DIR__ . '/helpers.php';

use function Zec\Utils\z;
use PHPUnit\Framework\TestCase;

class PrimitivesTest extends TestCase {
    use TestCaseHelper;
    protected function setUp(): void {
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
    public function testPrimitives() {
        foreach ($this->listParserToTest as $testCase) {
            foreach ($testCase->validValue as $value) {
                $this->assertTrue($testCase->parser->parse($value)->isValid());
            }
            foreach ($testCase->invalidValue as $value) {
                $this->assertFalse($testCase->parser->parse($value)->isValid());
            }
        }
    }
}