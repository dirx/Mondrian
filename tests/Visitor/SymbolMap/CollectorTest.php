<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Visitor\SymbolMap;

use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser\Multiple;
use PhpParser\Parser\Php5;
use PhpParser\Parser\Php7;
use Trismegiste\Mondrian\Parser\PackageParser;
use Trismegiste\Mondrian\Tests\Fixtures\MockSplFileInfo;
use Trismegiste\Mondrian\Transform\ReflectionContext;
use Trismegiste\Mondrian\Visitor\SymbolMap\Collector;

/**
 * CollectorTest is a test for the visitor SymbolMap\Collector
 */
class CollectorTest extends \PHPUnit_Framework_TestCase
{

    protected $symbol = [];
    protected $visitor;
    protected $context;
    protected $parser;
    protected $traverser;

    public function setUp()
    {
        $this->context = new ReflectionContext();
        $mockGraphCtx = $this->getMockBuilder('Trismegiste\Mondrian\Transform\GraphContext')
            ->disableOriginalConstructor()
            ->getMock();
        $mockGraph = $this->createMock('Trismegiste\Mondrian\Graph\Graph');
        $this->visitor = new Collector($this->context, $mockGraphCtx, $mockGraph);
        $this->parser = new PackageParser(
            new Multiple([
                new Php5(new Lexer()),
                new Php7(new Lexer()),
            ])
        );
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor($this->visitor);
    }

    protected function scanFile($fixtures)
    {
        $iter = [];

        foreach ($fixtures as $fch) {
            $path = __DIR__ . '/../../Fixtures/Project/' . $fch;
            $code = file_get_contents($path);
            $iter[] = new MockSplFileInfo($path, $code);
        }

        $stmts = $this->parser->parse(new \ArrayIterator($iter));
        $this->traverser->traverse($stmts);
        $this->visitor->afterTraverse([]);
    }

    public function testSimpleCase()
    {
        $this->scanFile(['Concrete.php']);

        $this->assertAttributeEquals([
            'Project\\Concrete' => [
                'type' => 'c',
                'parent' => [],
                'method' => ['simple' => 'Project\\Concrete'],
                'use' => [],
            ],
        ], 'inheritanceMap', $this->context);
    }

    public function testExternalInterfaceInheritance()
    {
        $this->scanFile(['InheritExtra.php']);

        $this->assertAttributeEquals([
            'Project\\InheritExtra' => [
                'type' => 'c',
                'parent' => [0 => 'IteratorAggregate'],
                'method' => ['getIterator' => 'IteratorAggregate'],
                'use' => [],
            ],
            'IteratorAggregate' => [
                'type' => 'i',
                'parent' => [],
                'method' => [],
                'use' => [],
            ],
        ], 'inheritanceMap', $this->context);
    }

    public function testAliasing()
    {
        $this->scanFile(['Alias1.php', 'Alias2.php']);

        $this->assertAttributeEquals([
            'Project\\Aliasing' => [
                'type' => 'c',
                'parent' => ['Project\Maid', 'Project\Peril'],
                'method' => ['spokes' => 'Project\\Aliasing'],
                'use' => [],
            ],
            'Project\Maid' => [
                'type' => 'c',
                'parent' => [],
                'method' => [],
                'use' => [],
            ],
            'Project\Peril' => [
                'type' => 'i',
                'parent' => [],
                'method' => [],
                'use' => [],
            ],
        ], 'inheritanceMap', $this->context);
    }

    public function testSimpleTrait()
    {
        $this->scanFile(['SimpleTrait.php']);

        $this->assertAttributeEquals([
            'Project\\SimpleTrait' => [
                'type' => 't',
                'parent' => [],
                'method' => ['someService' => 'Project\\SimpleTrait'],
                'use' => [],
            ],
        ], 'inheritanceMap', $this->context);
    }

    public function testImportingMethodFromTrait()
    {
        $this->scanFile([
            'ServiceWrong.php',
            'ServiceTrait.php',
        ]);

        $this->assertAttributeEquals([
            'Project\\ServiceWrong' => [
                'type' => 'c',
                'parent' => [],
                'method' => ['someService' => 'Project\\ServiceWrong'],
                'use' => ['Project\\ServiceTrait'],
            ],
            'Project\\ServiceTrait' => [
                'type' => 't',
                'parent' => [],
                'method' => ['someService' => 'Project\\ServiceTrait'],
                'use' => [],
            ]], 'inheritanceMap', $this->context);
    }

    public function testImportingMethodFromTraitWithInterfaceCollision()
    {
        $this->scanFile([
            'ServiceRight.php',
            'ServiceTrait.php',
            'ServiceInterface.php',
        ]);

        $this->assertAttributeEquals([
            'Project\\ServiceRight' => [
                'type' => 'c',
                'parent' => ['Project\\ServiceInterface'],
                'method' => ['someService' => 'Project\\ServiceInterface'],
                'use' => ['Project\\ServiceTrait'],
            ],
            'Project\\ServiceInterface' => [
                'type' => 'i',
                'parent' => [],
                'method' => ['someService' => 'Project\\ServiceInterface'],
                'use' => [],
            ],
            'Project\\ServiceTrait' => [
                'type' => 't',
                'parent' => [],
                'method' => ['someService' => 'Project\\ServiceTrait'],
                'use' => [],
            ]], 'inheritanceMap', $this->context);
    }

    public function testInterfaceExtends()
    {
        $this->scanFile(['Interface.php']);

        $this->assertAttributeEquals([
            'Project\\IOne' => [
                'type' => 'i',
                'parent' => [],
                'method' => [],
                'use' => [],
            ],
            'Project\\ITwo' => [
                'type' => 'i',
                'parent' => [],
                'method' => [],
                'use' => [],
            ],
            'Project\\IThree' => [
                'type' => 'i',
                'parent' => ['Project\ITwo'],
                'method' => [],
                'use' => [],
            ],
            'Project\\Multiple' => [
                'type' => 'i',
                'parent' => ['Project\IOne', 'Project\ITwo'],
                'method' => [],
                'use' => [],
            ],
        ], 'inheritanceMap', $this->context);
    }

    public function testTraitUsingTrait()
    {
        $this->scanFile([
            'ServiceUsingTrait.php',
            'ServiceTrait.php',
        ]);

        $this->assertAttributeEquals([
            'Project\\ServiceUsingTrait' => [
                'type' => 't',
                'parent' => [],
                //    'method' => array('someService' => 'Project\\ServiceTrait'),
                'method' => [],
                'use' => ['Project\\ServiceTrait'],
            ],
            'Project\\ServiceTrait' => [
                'type' => 't',
                'parent' => [],
                'method' => ['someService' => 'Project\\ServiceTrait'],
                'use' => [],
            ]], 'inheritanceMap', $this->context);

        $this->markTestIncomplete(); // @todo the commented line above must be incommented
        // I will not create vertex for imported implementation from trait in a trait 
        // but a class using ServiceUsingTrait must copy-paste all methods signatures
        // coming from all aggregated traits
    }

}
