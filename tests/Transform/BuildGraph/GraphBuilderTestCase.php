<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\BuildGraph;

use Trismegiste\Mondrian\Builder\Compiler\Director;
use Trismegiste\Mondrian\Parser\BuilderFactory;
use Trismegiste\Mondrian\Transform\GraphBuilder;

/**
 * GraphBuilderTestCase is a generic test for the builder compiler
 */
abstract class GraphBuilderTestCase extends \PHPUnit_Framework_TestCase
{

    protected $builder;
    protected $director;
    protected $logger;
    protected $graph;

    protected function setUp()
    {
        $conf = ['calling' => []];
        $this->graph = $this->createMock('Trismegiste\Mondrian\Graph\Graph');
        $this->logger = $this->createMock('Trismegiste\Mondrian\Transform\Logger\LoggerInterface');
        $this->builder = new GraphBuilder($conf, $this->graph, $this->logger);
        $this->director = new Director($this->builder);
    }

    protected function vertexConstraint($type, $name)
    {
        return $this->logicalAnd($this
            ->isInstanceOf('Trismegiste\Mondrian\Transform\Vertex\\' . ucfirst($type) . 'Vertex'), $this
            ->attributeEqualTo('name', $name));
    }

    protected function expectsAddVertex($idx, $type, $name)
    {
        $this->graph->expects($this->at($idx))
            ->method('addVertex')
            ->with($this->vertexConstraint($type, $name));
    }

    protected function expectsAddEdge($idx, $type1, $name1, $type2, $name2)
    {
        $this->graph->expects($this->at($idx))
            ->method('addEdge')
            ->with($this->vertexConstraint($type1, $name1), $this->vertexConstraint($type2, $name2));
    }

    protected function compile(array $stmt)
    {
        $this->director->compile($stmt);
    }

    protected function createFactory()
    {
        return new BuilderFactory();
    }

}
