<?php
namespace rg\typewriter;

class TypeChecker {

    /**
     * @var \ReflectionFunctionAbstract
     */
    private $reflection;

    /**
     * @var ClassResolver
     */
    private $resolver;

    /**
     * @var string[]
     */
    private $returnTypes;

    /**
     * @var string[]
     */
    private $arguments;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable) {
        $this->reflection = $this->getReflectionReference($callable);
        $this->resolver = new ClassResolver($this->reflection->getFileName());
    }

    /**
     * @param string $argName
     * @param mixed $actual
     * @return bool
     */
    public function isValueValidForArgument($argName, $actual) {
        $arguments = $this->getArgumentTypesFromDocComment();
        $types = $arguments[$argName];
        foreach ($types as $type) {
            if ($this->isOfType($type, $actual)) {
                return true;
            }
        }
        return false;
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
    private function getArgumentTypesFromDocComment() {
        if ($this->arguments !== null) {
            return $this->arguments;
        }

        $matches = [];
        $found = preg_match_all('/@param\s+(?<type>\S+)\s+\\$(?<argName>\S+)/', $this->reflection->getDocComment(), $matches, PREG_SET_ORDER);

        $arguments = [];
        if ($found) {
            foreach ($matches as $match) {
                $argName = $match['argName'];
                $type = $match['type'];
                $arguments[$argName] = $this->explodeMultipleHints($type);
            }
        }

        foreach ($arguments as $argName => $types) {
            $this->arguments[$argName] = $this->resolveTypes($types);
        }

        return $this->arguments;
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
        $types = $found ? $this->explodeMultipleHints($matches[1]) : [];

        $this->returnTypes = $this->resolveTypes($types);

        return $this->returnTypes;
    }

    /**
     * @param string $hint
     * @return array
     */
    private function explodeMultipleHints($hint) {
        return strpos($hint, '|') !== false ? explode('|', $hint) : [$hint];
    }

    private function resolveTypes($types) {
        $resolvedTypes = [];
        foreach ($types as $type) {
            if (substr($type, -2) === '[]') {
                $type = substr($type, 0, -2);
                $className = $this->resolver->resolve($type);
                $resolvedTypes[] = ($className ?: $type) . '[]';
                continue;
            }

            $className = $this->resolver->resolve($type);
            $resolvedTypes[] = $className ?: $type;
        }
        return $resolvedTypes;
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
