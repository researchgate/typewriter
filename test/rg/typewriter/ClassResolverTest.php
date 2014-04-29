<?php
namespace rg\typewriter;

use rg\typewriter\stub\classresolver\a\ClassToResolveInNamespaceA;
use rg\typewriter\stub\classresolver\ClassReferencesClassToResolve;
use rg\typewriter\stub\classresolver\ClassToResolve;

class ClassResolverTest extends \PHPUnit_Framework_TestCase {

    public function testResolveInSameNamespace() {
        $scope = (new \ReflectionClass(ClassReferencesClassToResolve::class))->getFileName();
        $resolver = new ClassResolver($scope);
        $resolved = $resolver->resolve($this->getClassNameWithoutNameSpace(ClassToResolve::class));
        $this->assertEquals(ClassToResolve::class, $resolved);
    }

    public function testResolveReferencedInUseStatement() {
        $scope = (new \ReflectionClass(ClassReferencesClassToResolve::class))->getFileName();
        $resolver = new ClassResolver($scope);
        $resolved = $resolver->resolve($this->getClassNameWithoutNameSpace(ClassToResolveInNamespaceA::class));
        $this->assertEquals(ClassToResolveInNamespaceA::class, $resolved);
    }

    public function testScopeWithoutNameSpace() {
        $scope = __DIR__ . '/stub/classresolver/ClassReferencesClassToResolve_NoNamespace.php';
        $resolver = new ClassResolver($scope);

        $resolved = $resolver->resolve($this->getClassNameWithoutNameSpace(ClassToResolve::class));
        $this->assertEquals(ClassToResolve::class, $resolved);

        $resolved = $resolver->resolve($this->getClassNameWithoutNameSpace(ClassToResolveInNamespaceA::class));
        $this->assertEquals(ClassToResolveInNamespaceA::class, $resolved);
    }

    public function testScopeDoesNotExist() {
        $resolver = new ClassResolver('DoesNotExist');
        $this->assertNull($resolver->resolve($this->getClassNameWithoutNameSpace(ClassToResolve::class)));
    }

    public function testResolveNotExistingClass() {
        $scope = (new \ReflectionClass(ClassReferencesClassToResolve::class))->getFileName();
        $resolver = new ClassResolver($scope);
        $this->assertNull($resolver->resolve('DoesNotExist'));
    }

    /**
     * @param string $fqNs
     * @return string
     */
    private function getClassNameWithoutNameSpace($fqNs) {
        $pos = strrpos($fqNs, '\\');
        if ($pos === false) {
            return $fqNs;
        }
        return substr($fqNs, $pos + 1);
    }
}
