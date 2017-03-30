<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Comment;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Parser\PhpPersistence;
use Trismegiste\Mondrian\Refactor\Refactored;
use Trismegiste\Mondrian\Visitor\InterfaceExtractor;

/**
 * InterfaceExtractorTest tests for InterfaceExtractor
 */
class InterfaceExtractorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $context;
    protected $dumper;

    public function getSimpleClass()
    {
        return [[
            new Class_('Systematic', [], [
                'comments' => [new Comment('@mondrian contractor Chaos')],
            ]),
        ]];
    }

    /**
     * @dataProvider getSimpleClass
     */
    public function testStackingMethod($node)
    {
        $this->assertAttributeEquals(false, 'newInterface', $this->visitor);
        $this->visitor->enterNode($node);
        $this->assertAttributeNotEquals(false, 'newInterface', $this->visitor);
    }

    /**
     * @dataProvider getSimpleClass
     */
    public function testNoGeneration($node)
    {
        $this->dumper->expects($this->never())
            ->method('write')
            ->will($this->returnCallback([$this, 'stubbedTestedWrite']));

        $this->assertAttributeEquals(false, 'newInterface', $this->visitor);
        $this->visitor->beforeTraverse([]);
        $this->assertAttributeEquals([], 'newContent', $this->visitor);
        // start traversing but not enter in class
        $this->visitor->leaveNode($node);
        // does not generate a write
        $this->visitor->afterTraverse([]);
    }

    public function testDoNothingForCC()
    {
        $node = new Interface_('Dummy', [
            'stmts' => new ClassMethod('dummy'),
        ]);
        $this->visitor->beforeTraverse([$node]);
        $this->visitor->enterNode($node);
    }

    /**
     * @dataProvider getSimpleClass
     */
    public function testGeneration($node)
    {
        $this->dumper->expects($this->once())
            ->method('write')
            ->will($this->returnCallback([$this, 'stubbedTestedWrite']));

        $this->visitor->enterNode(new PhpFile('/addicted/to/Systematic.php', []));
        $this->visitor->enterNode($node);
        $this->visitor->enterNode(new ClassMethod('forsaken'));
        $this->visitor->leaveNode($node);
        $this->visitor->afterTraverse([]);

        $this->assertAttributeNotEmpty('newContent', $this->visitor);
    }

    public function stubbedTestedWrite(PhpFile $file)
    {
        $this->assertEquals('/addicted/to/Chaos.php', $file->getRealPath());
        $generated = $file->stmts;
        $this->assertCount(2, $generated);
        $this->assertInstanceOf(Namespace_::class, $generated[0]);
        $this->assertInstanceOf(Interface_::class, $generated[1]);
        $interf = $generated[1]->stmts;
        $this->assertInstanceOf(ClassMethod::class, $interf[0]);
        $this->assertEquals('forsaken', $interf[0]->name);
    }

    /**
     * @dataProvider getSimpleClass
     * @expectedException \RuntimeException
     */
    public function testBadUseOfVisitor($node)
    {
        $this->visitor->enterNode($node);
        $this->visitor->leaveNode($node);
    }

    protected function setUp()
    {
        $this->dumper = $this->getMockForAbstractClass(PhpPersistence::class);
        $this->context = $this->getMockBuilder(Refactored::class)
            ->getMock();
        $this->visitor = new InterfaceExtractor($this->context, $this->dumper);
    }

}
