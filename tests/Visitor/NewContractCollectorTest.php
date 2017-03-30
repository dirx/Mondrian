<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Comment;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeTraverser;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Refactor\Refactored;
use Trismegiste\Mondrian\Visitor\NewContractCollector;

/**
 * NewContractCollectorTest is a test for NewContractCollector
 */
class NewContractCollectorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $context;

    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Refactored::class)
            ->getMock();
        $this->visitor = new NewContractCollector($this->context);
    }

    protected function buildFileNode()
    {
        $node[] = new PhpFile('/I/Am/Victory.php', []);
        $node[] = new Class_('Victory');

        return $node;
    }

    public function testEnterClassWithoutComments()
    {
        $node = $this->buildFileNode();

        $this->context->expects($this->never())
            ->method('pushNewContract');

        foreach ($node as $item) {
            $this->visitor->enterNode($item);
        }
    }

    public function testEnterCommentedClassWithoutAnnotations()
    {
        $node = $this->buildFileNode();
        $node[1]->setAttribute('comments', [
            new Comment('Some useless comments'),
        ]);

        $this->context->expects($this->never())
            ->method('pushNewContract');

        foreach ($node as $item) {
            $this->visitor->enterNode($item);
        }
    }

    public function testEnterAnnotedClass()
    {
        $node = $this->buildFileNode();
        $node[1]->setAttribute('comments', [
            new Comment('@mondrian contractor SomeNewContract'),
        ]);

        $this->context->expects($this->once())
            ->method('pushNewContract')
            ->with('Victory', 'SomeNewContract');

        foreach ($node as $item) {
            $this->visitor->enterNode($item);
        }

        $this->assertTrue($node[0]->isModified());
    }

    public function testDoNothingForCC()
    {
        $node = new Interface_('Dummy');
        $stmt = new ClassMethod('dummy');

        $this->context
            ->expects($this->never())
            ->method('pushNewContract');
        $this->visitor->enterNode($node);
        $this->visitor->enterNode($stmt);
    }

    public function testWithTraverser()
    {
        $file = new PhpFile('/I/Am/Victory.php', [
            new Class_('Victory', [], [
                'comments' => [
                    new Comment('@mondrian contractor SomeNewContract'),
                ],
            ]),
        ]);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->visitor);

        $this->assertFalse($file->isModified());
        $traverser->traverse([$file]);
        $this->assertTrue($file->isModified());
    }

}
