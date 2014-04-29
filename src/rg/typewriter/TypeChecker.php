<?php
namespace rg\typewriter;

class TypeChecker {

    /**
     * @var \ReflectionFunctionAbstract
     */
    private $reflection;

    /**
     * @var string[]
     */
    private $returnTypes;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable) {
        $this->reflection = $this->getReflectionReference($callable);
    }

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
        $types = $this->getReturnTypesFromDocComment();
        foreach ($types as $type) {
            if ($this->isOfType($type, $actual)) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param string $type
     * @param mixed $value
     * @return bool
     */
    private function isOfType($type, $value) {
        switch (strtolower($type)) {
            case 'array':
                return is_array($value);
            case 'int':
            case 'integer':
                return is_int($value);
            case 'float':
                return is_float($value);
            case 'bool':
            case 'boolean':
                return is_bool($value);
            case 'string':
                return is_string($value);
            case 'object':
                return is_object($value);
            case 'null':
                return is_null($value);
            case 'mixed':
                return true;
            case 'callable':
            case 'closure':
                return is_callable($value);
            case 'void':
                return is_null($value);
        }
        return $value instanceof $type;
    }

    /**
     * @return string[]
     */
    private function getReturnTypesFromDocComment() {
        if ($this->returnTypes !== null) {
            return $this->returnTypes;
        }

        $matches = [];
        $found = preg_match('/@return\s+(\S+)/', $this->reflection->getDocComment(), $matches);
        $types = $found ? $this->explodeMultipleHints($matches[1]) : array();

//        $resolver = new ClassResolver();

        $this->returnTypes = array_map(function ($type) {
            if (substr($type, -2) === '[]') {
                return 'traversable';
            }
//            $className = $resolver->resolve($type);
//            return $className ? : $type;
            return $type;
        }, $types);

        return $this->returnTypes;
    }

    /**
     * @param string $hint
     * @return array
     */
    private function explodeMultipleHints($hint) {
        if (strpos($hint, '|') !== false) {
            return explode('|', $hint);
        } else {
            return array($hint);
        }
    }

    /**
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    private function getReflectionReference(callable $callable) {
        return new \ReflectionFunction($callable);
    }
}
