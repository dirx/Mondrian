<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Builder\Compiler;

use PhpParser\NodeTraverser;
use Trismegiste\Mondrian\Builder\Compiler\BuilderInterface;
use Trismegiste\Mondrian\Builder\Compiler\Director;
use Trismegiste\Mondrian\Visitor\FqcnHelper;

/**
 * DirectorTest tests the director that builds the Compiler with the help of the builder
 */
class DirectorTest extends \PHPUnit_Framework_TestCase
{

    protected $director;
    protected $builder;

    protected function setUp()
    {
        $this->builder = $this->createMock(BuilderInterface::class);
        $this->director = new Director($this->builder);
    }

    public function testBuilding()
    {
        $this->builder
            ->expects($this->once())
            ->method('buildContext');
        $this->builder
            ->expects($this->once())
            ->method('buildCollectors')
            ->will($this->returnValue([$this->createMock(FqcnHelper::class)]));
        $this->builder
            ->expects($this->once())
            ->method('buildTraverser')
            ->will($this->returnValue($this->createMock(NodeTraverser::class)));

        $this->director->compile([]);
    }

}
