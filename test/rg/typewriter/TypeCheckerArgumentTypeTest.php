<?php
namespace rg\typewriter;

class TypeCheckerArgumentTypeTest extends \PHPUnit_Framework_TestCase {

    public function testValidateScalarArgumentWithName() {
        /**
         * @param int $number
         * @param bool $enable
         */
        $callback = function($number, $enable) {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidForArgument('number', 42));
        $this->assertFalse($checker->isValueValidForArgument('number', 'foo'));
        $this->assertTrue($checker->isValueValidForArgument('enable', true));
        $this->assertFalse($checker->isValueValidForArgument('enable', 'foo'));
    }
}
