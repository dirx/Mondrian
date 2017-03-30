<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Builder\Compiler;

use PhpParser\NodeTraverser;
use Trismegiste\Mondrian\Builder\Compiler\AbstractTraverser;
use Trismegiste\Mondrian\Builder\Compiler\Director;
use Trismegiste\Mondrian\Parser\PhpFile;
use Trismegiste\Mondrian\Visitor\FqcnHelper;

/**
 * AbstractTraverserTest tests the building a traverser
 */
class AbstractTraverserTest extends \PHPUnit_Framework_TestCase
{

    protected $builder;

    protected function setUp()
    {
        $this->builder = $this->getMockForAbstractClass(AbstractTraverser::class);
    }

    public function testTraverser()
    {
        $obj = $this->builder->buildTraverser($this->createMock(FqcnHelper::class));
        $this->assertInstanceOf(NodeTraverser::class, $obj);
    }

    public function testWithDirector()
    {
        $visitor = $this->createMock(FqcnHelper::class);

        $this->builder
            ->expects($this->once())
            ->method('buildCollectors')
            ->will($this->returnValue([$visitor]));
        $visitor
            ->expects($this->once())
            ->method('enterNode');

        $director = new Director($this->builder);
        $director->compile([new PhpFile('abc', [])]);
    }

}
