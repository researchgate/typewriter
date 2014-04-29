<?php
namespace rg\typewriter;

class TypeCheckerTest extends \PHPUnit_Framework_TestCase {

    public function testConstructorMethodBoundToObject() {
        $this->markTestIncomplete();
        // stub needed
        $obj = null;
        $checker = new TypeChecker([$obj, 'methodName']);
    }

    public function testConstructorMethodBoundToClass() {
        $this->markTestIncomplete();
        $checker = new TypeChecker(['className', 'methodName']);
    }

    public function testConstructorStaticMethodAsString() {
        $this->markTestIncomplete();
        // stub needed
        $checker = new TypeChecker('SomeClass::methodName');
    }

    public function testConstructorClosure() {
        /**
         * @return string
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
    }

    public function testConstructorNamedFunction() {
        $this->markTestIncomplete();
        // stub file needed
        $checker = new TypeChecker('functionName');
    }

    public function testValidateReturnTypeBool() {
        /**
         * @return bool
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType(true));
        $this->assertFalse($checker->isValueValidReturnType('foo'));
    }

    public function testValidateReturnTypeString() {
        /**
         * @return string
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType('foo'));
        $this->assertFalse($checker->isValueValidReturnType(null));
    }

    public function testValidateCompositeReturnType() {
        /**
         * @return int|bool
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType(0));
        $this->assertTrue($checker->isValueValidReturnType(2));
        $this->assertTrue($checker->isValueValidReturnType(true));
        $this->assertTrue($checker->isValueValidReturnType(false));
        $this->assertFalse($checker->isValueValidReturnType('foo'));
    }
}
