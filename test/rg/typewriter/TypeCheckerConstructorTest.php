<?php
namespace rg\typewriter;

use rg\typewriter\stub\PersonRepository;

class TypeCheckerConstructorTest extends \PHPUnit_Framework_TestCase {

    public function testConstructorMethodBoundToObject() {
        $obj = new PersonRepository();
        $checker = new TypeChecker([$obj, 'find']);
    }

    public function testConstructorMethodBoundToClass() {
        $checker = new TypeChecker([PersonRepository::class, 'find']);
    }

    public function testConstructorStaticMethodAsString() {
        $checker = new TypeChecker(PersonRepository::class . '::create');
    }

    public function testConstructorClosure() {
        /**
         * @return string
         */
        $callback = function() {};
        $checker = new TypeChecker($callback);
    }

    public function testConstructorNamedFunction() {
        $checker = new TypeChecker('rg\typewriter\stub\strlen');
    }
}
