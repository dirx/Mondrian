<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Comment;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;

/**
 * PublicCollectorStubTest tests for PublicCollectorStub visitor
 */
class PublicCollectorStubTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $traverser;

    public function testNamespacedClass()
    {
        $node = [
            new Namespace_(new Name('The\Sixteen')),
            new Class_('MenOfTain'),
        ];

        $this->traverser->traverse($node);
    }

    public function testNamespacedInterface()
    {
        $node = [
            new Namespace_(new Name('Wardenclyffe')),
            new Interface_('Tower'),
        ];
        $node[1]->setAttribute('comments', [new Comment(' -noise- @mondrian Oneiric Moor  ')]);

        $this->traverser->traverse($node);
    }

    public function testNamespacedClassMethod()
    {
        $node = [
            new Namespace_(new Name('The\Sixteen')),
            new Class_('MenOfTain'),
        ];
        $node[1]->stmts = [new ClassMethod('eidolon')];

        $this->traverser->traverse($node);
    }

    public function testNamespacedTrait()
    {
        $node = [
            new Namespace_(new Name('All\Our')),
            new Trait_('Yesterdays'),
        ];

        $this->traverser->traverse($node);
    }

    protected function setUp()
    {
        $this->visitor = new PublicCollectorStub($this);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this->visitor);
    }
}
