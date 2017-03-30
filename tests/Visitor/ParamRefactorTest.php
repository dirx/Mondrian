<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Comment;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Refactor\Refactored;
use Trismegiste\Mondrian\Visitor\ParamRefactor;

/**
 * ParamRefactorTest is a test for ParamRefactor
 */
class ParamRefactorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $context;

    public function testNonTypedParam()
    {
        $node = new Param('obj');
        $this->context->expects($this->never())
            ->method('hasNewContract');
        $this->visitor->enterNode($node);
    }

    public function testTypedParam()
    {
        $node = new Param('obj', null, 'array');
        $this->context->expects($this->never())
            ->method('hasNewContract');
        $this->visitor->enterNode($node);
    }

    public function testClassTypedParamWithName()
    {
        $fileNode = new PhpFile('/I/Am/Victory.php', []);
        $classNode = new Param('obj', null, new Name('SplObjectStorage'));

        $this->context->expects($this->once())
            ->method('hasNewContract')
            ->with('SplObjectStorage')
            ->will($this->returnValue(true));

        $this->visitor->enterNode($fileNode);
        $this->visitor->enterNode($classNode);
        $this->assertTrue($fileNode->isModified());
    }

    public function testClassTypedParamWithFqcn()
    {
        $fileNode = new PhpFile('/I/Am/Victory.php', []);
        $node = new Param('obj', null, new FullyQualified('Pull\Me\Under'));

        $this->context->expects($this->once())
            ->method('hasNewContract')
            ->with('Pull\Me\Under')
            ->will($this->returnValue(true));

        $this->visitor->enterNode($fileNode);
        $this->visitor->enterNode($node);
        $this->assertTrue($fileNode->isModified());
    }

    public function testRefactoring()
    {
        $fileNode = new PhpFile('/I/Am/Victory.php', []);
        $node = new Param('obj', null, new FullyQualified('Pull\Me\Under'));

        $this->context->expects($this->once())
            ->method('hasNewContract')
            ->with('Pull\Me\Under')
            ->will($this->returnValue(true));

        $this->context->expects($this->once())
            ->method('getNewContract')
            ->with('Pull\Me\Under')
            ->will($this->returnValue('Awake'));

        $this->visitor->enterNode($fileNode);
        $this->visitor->enterNode($node);
        $this->assertTrue($fileNode->isModified());
        $this->assertEquals('Awake', $node->type, 'Type Hint changed');
    }

    public function testWithTraverser()
    {
        $this->context->expects($this->once())
            ->method('hasNewContract')
            ->with('Pull\Me\Under')
            ->will($this->returnValue(true));

        $this->context->expects($this->once())
            ->method('getNewContract')
            ->with('Pull\Me\Under')
            ->will($this->returnValue('Awake'));

        $classNode = new Class_('Victory', [
            'stmts' => [
                new ClassMethod('holy', [
                    'params' => [new Param('war', null, new Name('Pull\Me\Under'))],
                ]),
            ]], [
            'comments' => [
                new Comment('@mondrian contractor SomeNewContract'),
            ],
        ]);
        $file = new PhpFile('/I/Am/Victory.php', [
            $classNode,
        ]);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->visitor);

        $this->assertFalse($file->isModified());
        $traverser->traverse([$file]);
        $this->assertTrue($file->isModified());
        $this->assertEquals('Awake', (string)$classNode->stmts[0]->params[0]->type);
    }

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Refactored::class)
            ->getMock();
        $this->visitor = new ParamRefactor($this->context);
    }

}
