<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Tests\Transform\Vertex;

use Trismegiste\Mondrian\Transform\Vertex\StaticAnalysis;

/**
 * StaticAnalysisTest is a test for StaticAnalysis vertex superclass
 */
class StaticAnalysisTest extends \PHPUnit_Framework_TestCase
{

    protected $vertex;

    protected function setUp()
    {
        $this->vertex = $this
            ->getMockForAbstractClass(StaticAnalysis::class, ['a']);
        $this->vertex->expects($this->any())
            ->method('getSpecific')
            ->will($this->returnValue([]));
    }

    public function testAttribute()
    {
        $this->assertEquals([], $this->vertex->getAttribute());
    }

    public function testCentralityMeta()
    {
        $this->vertex->setMeta('centrality', 5);
        $this->assertArrayHasKey('color', $this->vertex->getAttribute());
    }

}
