<?php
namespace rg\typewriter;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node;
use PhpParser\Parser;

class ClassResolver {

    static private $cache = [];

    private $scopeFileName;

    /**
     * @param string $scopeFileName
     */
    public function __construct($scopeFileName) {
        $this->scopeFileName = $scopeFileName;
    }

    /**
     * @param string $class
     * @return null|string
     */
    public function resolve($class) {
        if ($this->exists($class)) {
            return $class;
        }


        $prependedClass = $this->prependWithCurrentNamespace($class);
        if ($this->exists($prependedClass)) {
            return $prependedClass;
        }

        return $this->findAliasedClass($class);
    }

    /**
     * @param string $class
     * @return bool
     */
    private function exists($class) {
        return class_exists($class) || interface_exists($class);
    }

    /**
     * @param string $class
     * @return string
     */
    private function prependWithCurrentNamespace($class) {
        $node = $this->findNamespaceNode($this->parse());
        return $node ? ((string) $node->name) . '\\' . $class : $class;
    }

    /**
     * @param Node[] $stmts
     * @return null|Namespace_
     */
    private function findNamespaceNode($stmts) {
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                return $stmt;
            }
        }
        return null;
    }

    /**
     * @param string $class
     * @return null|string
     */
    private function findAliasedClass($class) {
        $stmts = $this->parse();

        $node = $this->findNamespaceNode($stmts);
        if ($node) {
            $stmts = $node->stmts;
        }

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    if ($use instanceof UseUse && $use->alias == $class) {
                        return (string) $use->name;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return Node[]
     * @throws \Exception
     */
    private function parse() {
        if (!file_exists($this->scopeFileName)) {
            return [];
        }

        if (!array_key_exists($this->scopeFileName, self::$cache)) {
            try {
                $parser = new Parser(new Lexer());
                self::$cache[$this->scopeFileName] = $parser->parse(file_get_contents($this->scopeFileName));
            } catch (Error $e) {
                throw new \Exception("Error while parsing [{$this->scopeFileName}]: " . $e->getMessage());
            }
        }

        return self::$cache[$this->scopeFileName];
    }

}
