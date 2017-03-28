<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Analysis\Strategy;

use Trismegiste\Mondrian\Graph\Digraph;
use Trismegiste\Mondrian\Graph\Edge;
use Trismegiste\Mondrian\Graph\Vertex;
use Trismegiste\Mondrian\Transform\Vertex\ClassVertex;

/**
 * TestTemplate is a unit test template for strategy
 */
abstract class TestTemplate extends \PHPUnit_Framework_TestCase
{

    protected $strategy;
    protected $result;

    protected function setUp()
    {
        $this->result = new Digraph();
        $this->strategy = $this->createStrategy($this->result);
    }

    abstract protected function createStrategy(Digraph $g);

    public function getPath()
    {
        return [[new ClassVertex('A'), new ClassVertex('B')]];
    }

    /**
     * @dataProvider getPath
     */
    public function testEmpty($src, $dst)
    {
        $this->strategy->collapseEdge($src, $dst, []);
        $this->assertCount(0, $this->result->getVertexSet());
    }

    /**
     * @dataProvider getPath
     */
    public function testNowhere($src, $dst)
    {
        $this->strategy->collapseEdge($src, $dst, [new Edge($src, new Vertex('C'))]);
        $this->assertCount(0, $this->result->getVertexSet());
    }

    protected function buildPath()
    {
        $card = func_num_args();
        $path = [];
        for ($k = 0; $k < $card - 1; $k++) {
            $path[$k] = new Edge(func_get_arg($k), func_get_arg($k + 1));
        }

        return $path;
    }

}
