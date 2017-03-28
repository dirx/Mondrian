<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;

/**
 * PublicCollectorTest tests for PublicCollector visitor
 *
 * @author flo
 */
class PublicCollectorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;

    public function testClassNodeWithoutNS()
    {
        $node = new Class_('Metal');
        $this->visitor->expects($this->once())
            ->method('enterClassNode')
            ->with($node);

        $this->visitor->enterNode($node);
        $this->assertAttributeEquals('Metal', 'currentClass', $this->visitor);
        $this->visitor->leaveNode($node);
        $this->assertAttributeEquals(false, 'currentClass', $this->visitor);
    }

    public function testPublicMethodNode()
    {
        $node = new ClassMethod('fatigue');
        $this->visitor->expects($this->once())
            ->method('enterPublicMethodNode')
            ->with($node);

        $this->visitor->enterNode($node);
        $this->assertAttributeEquals('fatigue', 'currentMethod', $this->visitor);
        $this->visitor->leaveNode($node);
        $this->assertAttributeEquals(false, 'currentMethod', $this->visitor);
    }

    public function testNonPublicMethodNode()
    {
        $node = new ClassMethod('fatigue');
        $node->flags = Class_::MODIFIER_PROTECTED;
        $this->visitor->expects($this->never())
            ->method('enterPublicMethodNode');

        $this->visitor->enterNode($node);
        $this->assertAttributeEquals(false, 'currentMethod', $this->visitor);
        $this->visitor->leaveNode($node);
        $this->assertAttributeEquals(false, 'currentMethod', $this->visitor);
    }

    public function testInterfaceNodeWithoutNS()
    {
        $node = new Interface_('Home');
        $this->visitor->expects($this->once())
            ->method('enterInterfaceNode')
            ->with($node);

        $this->visitor->enterNode($node);
        $this->assertAttributeEquals('Home', 'currentClass', $this->visitor);
        $this->visitor->leaveNode($node);
        $this->assertAttributeEquals(false, 'currentClass', $this->visitor);
    }

    public function testTraitNodeWithoutNS()
    {
        $node = new Trait_('Popipo');
        $this->visitor->expects($this->once())
            ->method('enterTraitNode')
            ->with($node);

        $this->visitor->enterNode($node);
        $this->assertAttributeEquals('Popipo', 'currentClass', $this->visitor);
        $this->visitor->leaveNode($node);
        $this->assertAttributeEquals(false, 'currentClass', $this->visitor);
    }

    protected function setUp()
    {
        $this->visitor = $this->getMockForAbstractClass('Trismegiste\Mondrian\Visitor\PublicCollector');
    }

}
