<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeTraverser;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Visitor\FqcnHelper;

/**
 * FqcnHelperTest tests helper methods provided by FqcnHelper
 */
class FqcnHelperTest extends \PHPUnit_Framework_TestCase
{

    protected $visitor;

    /**
     * @var NodeTraverser
     */
    protected $traverser;

    protected function setUp()
    {
        $this->visitor = new FqcnHelperStub();
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this->visitor);
    }

    public function testDoubleAlias()
    {
        $node = [
            new UseUse(new Name('Simple\Aliasing'), 'ItFails'),
            new UseUse(new Name('Double\Aliasing'), 'ItFails'),
        ];

        $this->expectException(Error::class);
        $this->traverser->traverse($node);
    }

    public function testResolution()
    {
        $node[0] = new Namespace_(new Name('Wrath\Of'));
        $node[1] = new Class_('TheNorsemen');
        $node[1]->extends = new Name('Khan');

        $this->traverser->traverse($node);
        $this->assertEquals('Wrath\Of\Khan', $node[1]->getAttribute('unit-test'));
    }

    public function testNoResolution()
    {
        $node[0] = new Namespace_(new Name('Wrath\Of'));
        $node[1] = new Class_('TheNorsemen');
        $node[1]->extends = new FullyQualified('Swansong\For\A\Raven');

        $this->traverser->traverse($node);
        $this->assertEquals('Swansong\For\A\Raven', $node[1]->getAttribute('unit-test'));
    }

    public function testResolutionWithAlias()
    {
        $node[0] = new Namespace_(new Name('Wrath\Of'));
        $node[1] = new UseUse(new Name('Medusa\And\Hemlock'), 'Nymphetamine');
        $node[2] = new Class_('TheNorsemen');
        $node[2]->extends = new Name('Nymphetamine');

        $this->traverser->traverse($node);
        $this->assertEquals('Medusa\And\Hemlock', $node[2]->getAttribute('unit-test'));
    }

    public function testNamespacedTransform()
    {
        $node[0] = new Namespace_(new Name('Wrath\Of\The'));
        $node[1] = new Interface_('Norsemen');

        $this->traverser->traverse($node);
        $this->assertEquals('Wrath\Of\The\Norsemen', $node[1]->getAttribute('unit-test'));
    }

    public function testNamespacedTransformFallback()
    {
        $node[0] = new Interface_('Norsemen');

        $this->traverser->traverse($node);
        $this->assertEquals('Norsemen', $node[0]->getAttribute('unit-test'));
    }

    public function testResetAfterNewFile()
    {
        $this->visitor->enterNode(new Namespace_(new Name('Nymphetamine')));
        $this->assertAttributeEquals('Nymphetamine', 'namespace', $this->visitor);
        $this->visitor->enterNode(new PhpFile('a', []));
        $this->assertAttributeEquals(null, 'namespace', $this->visitor);
    }

    public function testReservedKeyword()
    {
        $node = new StaticCall(new Name('parent'), 'calling');
        $this->visitor->enterNode($node);
        $this->assertEquals('parent', $node->getAttribute('unit-test'));
    }

    public function testEnterFile()
    {
        $source = new PhpFile('a', []);
        $this->visitor->enterNode($source);
        $this->assertAttributeEquals($source, 'currentPhpFile', $this->visitor);
    }

}

/**
 * A subclass of FqcnHelper to test internal protected methods
 * (it's better than using ReflectionMethod, IMO )
 */
class FqcnHelperStub extends FqcnHelper
{

    public function enterNode(Node $node)
    {
        parent::enterNode($node);

        switch ($node->getType()) {

            case 'Stmt_Class':
                if (!is_null($node->extends)) {
                    $node->setAttribute('unit-test', (string)$this->resolveClassName($node->extends));
                }
                break;

            case 'Stmt_Interface':
                if (!is_null($node->extends)) {
                    $node->setAttribute('unit-test', $this->getNamespacedName($node));
                }
                break;

            case 'Expr_StaticCall':
                $node->setAttribute('unit-test', (string)$this->resolveClassName($node->class));
                break;
        }
    }

}
