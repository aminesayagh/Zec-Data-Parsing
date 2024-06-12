<?php 
// Test the argument values set to the parser
require_once __DIR__ . '/helpers.php';
use function Zec\Utils\z;
use PHPUnit\Framework\TestCase;

class ArgumentTest extends TestCase {
    use TestCaseHelper;
    private function setUpMessageError() {
        $this->addTestCase(
            t('Error Message String')
                ->validValue('Message Eroor Error System')
                ->setParser(z()->email([
                    'message' => 'Message Eroor Error System'
                ]))
        );
    }
    protected function setUp(): void {
        $this->setUpMessageError();
    }
}