<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Trismegiste\Mondrian\Parser\PhpPersistence;
use Trismegiste\Mondrian\Visitor\NewInstanceRefactor;

/**
 * NewInstanceRefactorTest is a test for NewInstanceRefactor
 */
class NewInstanceRefactorTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;
    protected $dumper;

    public function testWithTraverser()
    {
        $classNode = new Class_('Victory', [
            'stmts' => [
                new ClassMethod('holy', [
                    'stmts' => [
                        new New_(new Name('Holy\War')),
                        new New_(new Name('\Hangar18')),
                    ],
                ]),
            ],
        ]);

        $file = new \Trismegiste\Mondrian\Parser\PhpFile('/I/Am/Victory.php', [
            $classNode,
        ]);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this->visitor);

        $this->assertFalse($file->isModified());
        $traverser->traverse([$file]);
        $this->assertTrue($file->isModified());
        $this->assertCount(3, $classNode->stmts);

        $pp = new Standard();
        $flat = $pp->prettyPrint($file->stmts);
        eval($flat);
    }

    protected function setUp()
    {
        $this->dumper = $this->getMockForAbstractClass(PhpPersistence::class);
        $this->visitor = new NewInstanceRefactor($this->dumper);
    }

}
