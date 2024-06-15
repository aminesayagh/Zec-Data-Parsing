<?php
require_once __DIR__ . '/helpers.php';

use function Zec\Utils\z;
use PHPUnit\Framework\TestCase;

class StringTest extends TestCase {
    use TestCaseHelper;
    protected function setUp(): void {
        // string
        $string = z()->string();
        $this->addTestCase(
            t('string_max')
                ->validValue('element') // 7 characters < 10
                ->invalidValue('element element element') // 26 characters
                ->setParser($string->max(10))
        );
        $this->addTestCase(
            t('string_min')
                ->validValue('element') // 7 characters > 3
                ->invalidValue('el') // 2 characters
                ->setParser($string->min(3))
        );
        $this->addTestCase(
            t('string_min_max')
                ->validValue('element') // 7 characters > 3 and < 10
                ->invalidValue('element element element') // 26 characters
                ->invalidValue('el') // 2 characters
                ->setParser($string->min(3)->max(10))
        );
        $this->addTestCase(
            t('string_length')
                ->validValue('element') // 7 characters = 7
                ->invalidValue('element element element') // 26 characters
                ->invalidValue('el') // 2 characters
                ->setParser($string->length(7))
        );
        $this->addTestCase(
            t('string_email')
                ->validValue('test@gmail.com')
                ->invalidValue('element')
                ->setParser($string->email())
        );
        $this->addTestCase(
            t('string_url')
                ->validValue('https://www.google.com')
                ->invalidValue('element')
                ->setParser($string->url())
        );
        $this->addTestCase(
            t('string_date')
                ->validValue('2022-01-12')
                ->invalidValue(
                    new DateTime('2022-01-12') // Replace with a valid date string or a valid instance of the DateTime class
                )
                ->invalidValue('2022')
                ->setParser($string->date())
        );

        // number
        $number = z()->number();
        $this->addTestCase(
            t('number_min')
                ->validValue(3) // 3 > 2
                ->invalidValue(1) // 1 < 2
                ->setParser($number->min(2))
        );

        $this->addTestCase(
            t('number_max')
                ->validValue(3) // 3 < 4
                ->invalidValue(5) // 5 > 4
                ->setParser($number->max(4))
        );

        $this->addTestCase(
            t('number_min_max')
                ->validValue(3) // 3 > 2 and 3 < 4
                ->invalidValue(1) // 1 < 2
                ->invalidValue(5) // 5 > 4
                ->setParser($number->min(2)->max(4))
        );

        // bool
        $bool = z()->bool();
        $this->addTestCase(
            t('bool')
                ->validValue(true)
                ->invalidValue('element')
                ->setParser($bool)
        );

        // date
        $date = z()->date();
        $this->addTestCase(
            t('date')
                ->validValue('2022-01-12')
                ->validValue(
                    new DateTime('2022-01-12') // Replace with a valid date string or a valid instance of the DateTime class
                )
                ->invalidValue('2022')
                ->setParser($date)
        );

        // email
        $email = z()->email();
        $this->addTestCase(
            t('email')
                ->validValue('email@gmail.com')
                ->invalidValue('element')
                ->setParser($email)
        );
        // nfs hautment disponible
        // url
        $url = z()->url();
        $this->addTestCase(
            t('url')
                ->validValue('https://www.google.com')
                ->invalidValue('element')
                ->setParser($url)
        );
    }
    public function testParser() {
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