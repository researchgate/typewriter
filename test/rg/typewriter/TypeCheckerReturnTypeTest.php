<?php
namespace rg\typewriter;

use rg\typewriter\stub\Person;

class TypeCheckerReturnTypeTest extends \PHPUnit_Framework_TestCase {

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

    public function testValidateReturnTypeCollection() {
        /**
         * @return int[]
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType([1, 2, 3]));
        $this->assertFalse($checker->isValueValidReturnType([1, 2, 'foo']));
        $this->assertTrue($checker->isValueValidReturnType(new \ArrayIterator([1, 2, 3])));
        $this->assertFalse($checker->isValueValidReturnType(new \ArrayIterator([1, 2, 'foo'])));

        /**
         * @return Person[]
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
        $this->assertTrue($checker->isValueValidReturnType([
            new Person('Jane Doe'),
            new Person('Maximilian Mustermann'),
        ]));
        $this->assertFalse($checker->isValueValidReturnType([
            new Person('Jane Doe'),
            null,
        ]));
    }
}
