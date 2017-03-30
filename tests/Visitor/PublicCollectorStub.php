<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Trismegiste\Mondrian\Visitor\PublicCollector;

/**
 * PublicCollectorStub is a stub for inner testing PublicCollector
 */
class PublicCollectorStub extends PublicCollector
{

    private $testCase;

    public function __construct(\PHPUnit_Framework_TestCase $track)
    {
        $this->testCase = $track;
    }

    protected function enterClassNode(Class_ $node)
    {
        $this->testCase->assertEquals('The\Sixteen\MenOfTain', $this->currentClass);
    }

    protected function enterInterfaceNode(Interface_ $node)
    {
        $this->testCase->assertEquals('Wardenclyffe\Tower', $this->currentClass);
        $this->extractAnnotation($node);
        $this->testCase->assertEquals(['Moor'], $node->getAttribute('Oneiric'));
    }

    protected function enterPublicMethodNode(ClassMethod $node)
    {
        $this->testCase->assertEquals('eidolon', $this->currentMethod);
        $this->testCase->assertEquals('The\Sixteen\MenOfTain::eidolon', $this->getCurrentMethodIndex());
    }

    protected function enterTraitNode(Trait_ $node)
    {
        $this->testCase->assertEquals('All\Our\Yesterdays', $this->currentClass);
    }

}
