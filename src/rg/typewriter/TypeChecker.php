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
        if (substr($type, -2) === '[]' && (is_array($value) || $value instanceof \Traversable)) {
            $type = substr($type, 0, -2);
            foreach ($value as $element) {
                if (!$this->isOfType($type, $element)) {
                    return false;
                }
            }
            return true;
        }

        $predicates = [
            'array'     => 'is_array',
            'int'       => 'is_int',
            'integer'   => 'is_int',
            'float'     => 'is_float',
            'bool'      => 'is_bool',
            'boolean'   => 'is_bool',
            'string'    => 'is_string',
            'object'    => 'is_object',
            'null'      => 'is_null',
            'void'      => 'is_null',
            'callable'  => 'is_callable',
        ];

        $lowerType = strtolower($type);

        if ('mixed' === $lowerType) {
            return true;
        }

        if (isset($predicates[$lowerType])) {
            $predicate = $predicates[$lowerType];
            return $predicate($value);
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

        $resolver = new ClassResolver($this->reflection->getFileName());

        $this->returnTypes = array_map(function ($type) use ($resolver) {
            if (substr($type, -2) === '[]') {
                $type = substr($type, 0, -2);
                $className = $resolver->resolve($type);
                return ($className ?: $type) . '[]';
            }

            $className = $resolver->resolve($type);
            return $className ?: $type;
        }, $types);

        return $this->returnTypes;
    }

    /**
     * @param string $hint
     * @return array
     */
    private function explodeMultipleHints($hint) {
        return strpos($hint, '|') !== false ? explode('|', $hint) : [$hint];
    }

    /**
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    private function getReflectionReference(callable $callable) {
        if (is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        if (is_string($callable) && strpos($callable, '::') !== false) {
            $callable = explode('::', $callable);
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        return new \ReflectionFunction($callable);
    }
}
