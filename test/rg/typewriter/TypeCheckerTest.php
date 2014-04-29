<?php
namespace rg\typewriter;

class TypeCheckerTest extends \PHPUnit_Framework_TestCase {

    public function testValidateReturnType() {
        /**
         * @return bool
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType(true));
    }
}
