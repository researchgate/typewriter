<?php
namespace rg\typewriter;

class TypeChecker {

    /**
     * @param string $argName
     * @param mixed $actual
     * @return bool
     */
    public function isValueValidForArgument($argName, $actual) {
    }

    /**
     * @param int $index
     * @param mixed $actual
     * @return bool
     */
    public function isValueValidForArgumentAtPos($index, $actual) {
    }

    /**
     * @param mixed $actual
     * @return bool
     */
    public function isValueValidReturnType($actual) {
    }
}
